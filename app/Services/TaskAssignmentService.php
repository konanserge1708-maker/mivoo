<?php

namespace App\Services;

use App\Enums\CampaignStatus;
use App\Enums\TaskStatus;
use App\Models\Campaign;
use App\Models\CampaignBudgetReservation;
use App\Models\SystemSetting;
use App\Models\TaskAssignment;
use App\Models\User;
use App\Models\WorkerProfile;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TaskAssignmentService
{
    public const HARD_DAILY_MAX = 10;

    /**
     * Tirage journalier : attribue d’un coup le quota du jour (fixe ou aléatoire, max 10).
     *
     * @return Collection<int, TaskAssignment>
     */
    public function drawDailyBatch(User $user): Collection
    {
        return DB::transaction(function () use ($user) {
            $user = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $profile = $this->refreshDailyCounters($user);
            $today = Carbon::now('Africa/Abidjan')->toDateString();

            if ($this->hasDrawnToday($user, $today)) {
                throw new InvalidArgumentException('Vous avez déjà tiré vos missions aujourd’hui. Revenez demain.');
            }

            $quota = $this->resolveDailyQuota($user);

            if ($quota < 1) {
                throw new InvalidArgumentException('Aucune mission à tirer pour aujourd’hui.');
            }

            $plan = $user->activeSubscription?->plan;
            $vipLevel = $plan?->level ?? 0;
            $referralRate = (int) SystemSetting::getValue('referral_commission_percent', 10);

            $candidateIds = Campaign::query()
                ->where('status', CampaignStatus::Active->value)
                ->where('funding_confirmed', true)
                ->where('min_vip_level', '<=', $vipLevel)
                ->where(function ($q) {
                    $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
                })
                ->whereDoesntHave('assignments', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->pluck('id')
                ->shuffle();

            if ($candidateIds->isEmpty()) {
                throw new InvalidArgumentException('Aucune nouvelle mission disponible pour le moment.');
            }

            $selectedIds = $candidateIds->take($quota);
            $assignments = collect();

            foreach ($selectedIds as $campaignId) {
                $campaign = Campaign::query()->whereKey($campaignId)->lockForUpdate()->first();
                if (! $campaign || $campaign->status !== CampaignStatus::Active) {
                    continue;
                }

                $assignments->push($this->createAssignment($user, $campaign, $referralRate));
            }

            if ($assignments->isEmpty()) {
                throw new InvalidArgumentException('Aucune nouvelle mission disponible pour le moment.');
            }

            $profile->forceFill([
                'tasks_counter_date' => $today,
            ])->save();

            return $assignments->each->load('campaign.category');
        });
    }

    /** @deprecated Utiliser drawDailyBatch — conservé pour compat tests / appels unitaires. */
    public function assignNext(User $user): TaskAssignment
    {
        $batch = $this->drawDailyBatch($user);

        return $batch->first();
    }

    public function resolveDailyQuota(User $user): int
    {
        $hardMax = min(
            self::HARD_DAILY_MAX,
            max(1, (int) SystemSetting::getValue('max_tasks_per_day', self::HARD_DAILY_MAX))
        );

        $planMax = (int) ($user->activeSubscription?->plan?->max_tasks_per_day ?? $hardMax);
        $cap = max(1, min($hardMax, $planMax));

        $mode = (string) SystemSetting::getValue('daily_missions_mode', 'fixed');

        if ($mode === 'random') {
            return random_int(1, $cap);
        }

        $fixed = (int) SystemSetting::getValue('daily_missions_count', $cap);

        return max(1, min($fixed, $cap));
    }

    public function hasDrawnToday(User $user, ?string $today = null): bool
    {
        $today ??= Carbon::now('Africa/Abidjan')->toDateString();

        return TaskAssignment::query()
            ->where('user_id', $user->id)
            ->whereDate('assigned_at', $today)
            ->exists();
    }

    public function todaysDrawnCount(User $user, ?string $today = null): int
    {
        $today ??= Carbon::now('Africa/Abidjan')->toDateString();

        return TaskAssignment::query()
            ->where('user_id', $user->id)
            ->whereDate('assigned_at', $today)
            ->count();
    }

    public function start(TaskAssignment $assignment, User $user): TaskAssignment
    {
        if ($assignment->user_id !== $user->id) {
            abort(403);
        }

        if ($assignment->status !== TaskStatus::Assigned) {
            throw new InvalidArgumentException('Cette mission ne peut pas être démarrée.');
        }

        if ($assignment->expires_at && $assignment->expires_at->isPast()) {
            $assignment->forceFill(['status' => TaskStatus::Expired])->save();
            throw new InvalidArgumentException('Cette mission a expiré.');
        }

        $assignment->forceFill([
            'status' => TaskStatus::InProgress,
            'started_at' => now(),
        ])->save();

        return $assignment->fresh('campaign');
    }

    public function refreshDailyCounters(User $user): WorkerProfile
    {
        $today = Carbon::now('Africa/Abidjan')->toDateString();
        $profile = $user->workerProfile()->firstOrCreate(['user_id' => $user->id], ['country' => 'CI']);

        if ($profile->tasks_counter_date?->toDateString() !== $today) {
            $profile->forceFill([
                'tasks_counter_date' => $today,
                'tasks_completed_today' => 0,
                'daily_earnings_xof' => 0,
            ])->save();
        }

        return $profile->fresh();
    }

    public function expireOverdue(): int
    {
        $count = 0;

        TaskAssignment::query()
            ->whereIn('status', [TaskStatus::Assigned->value, TaskStatus::InProgress->value])
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->orderBy('id')
            ->chunkById(50, function ($items) use (&$count) {
                foreach ($items as $assignment) {
                    DB::transaction(function () use ($assignment, &$count) {
                        $locked = TaskAssignment::query()->whereKey($assignment->id)->lockForUpdate()->first();
                        if (! $locked || ! in_array($locked->status, [TaskStatus::Assigned, TaskStatus::InProgress], true)) {
                            return;
                        }
                        $locked->forceFill(['status' => TaskStatus::Expired])->save();
                        $this->releaseReservation($locked);
                        $count++;
                    });
                }
            });

        return $count;
    }

    public function releaseReservation(TaskAssignment $assignment): void
    {
        $campaign = Campaign::query()->whereKey($assignment->campaign_id)->lockForUpdate()->first();
        if (! $campaign) {
            return;
        }

        $reservation = CampaignBudgetReservation::query()
            ->where('task_assignment_id', $assignment->id)
            ->where('status', 'reserved')
            ->lockForUpdate()
            ->first();

        if (! $reservation) {
            return;
        }

        $reservation->forceFill(['status' => 'released'])->save();
        $campaign->budget_reserved_xof = max(0, $campaign->budget_reserved_xof - $reservation->amount_xof);
        $campaign->budget_available_xof += $reservation->amount_xof;
        $campaign->save();
    }

    public function rewardForUser(User $user): int
    {
        $user->loadMissing('activeSubscription.plan');
        $plan = $user->activeSubscription?->plan;

        if ($plan && $plan->rewardPerTask() > 0) {
            return $plan->rewardPerTask();
        }

        return max(1, (int) SystemSetting::getValue('default_reward_per_task_xof', 10));
    }

    private function createAssignment(User $user, Campaign $campaign, int $referralRate): TaskAssignment
    {
        // Tous les plans : gain / mission du VIP (Gratuit = 10 F → 100 F/jour).
        $reserveReward = $this->rewardForUser($user);
        $reserveReferral = (int) floor($reserveReward * $referralRate / 100);
        $totalReserve = $reserveReward + $reserveReferral;

        $this->ensurePoolBudget($campaign, $totalReserve);

        $assignment = TaskAssignment::query()->create([
            'campaign_id' => $campaign->id,
            'user_id' => $user->id,
            'status' => TaskStatus::Assigned,
            'reward_xof' => $reserveReward,
            'referral_reserve_xof' => $reserveReferral,
            'assigned_at' => now(),
            'expires_at' => now()->addMinutes($campaign->completion_minutes),
        ]);

        CampaignBudgetReservation::query()->create([
            'campaign_id' => $campaign->id,
            'task_assignment_id' => $assignment->id,
            'amount_xof' => $totalReserve,
            'status' => 'reserved',
            'purpose' => 'task_and_referral',
        ]);

        $campaign->tasks_assigned += 1;
        $campaign->budget_reserved_xof += $totalReserve;
        $campaign->budget_available_xof -= $totalReserve;
        $campaign->save();

        return $assignment;
    }

    private function ensurePoolBudget(Campaign $campaign, int $needed): void
    {
        if ($campaign->budget_available_xof >= $needed) {
            return;
        }

        $topUp = $needed - $campaign->budget_available_xof;
        $campaign->budget_total_xof += $topUp;
        $campaign->budget_available_xof += $topUp;
        $campaign->save();
    }
}
