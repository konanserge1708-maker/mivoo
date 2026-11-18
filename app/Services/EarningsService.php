<?php

namespace App\Services;

use App\Enums\WalletTransactionType;
use App\Models\Campaign;
use App\Models\CampaignBudgetReservation;
use App\Models\SystemSetting;
use App\Models\TaskAssignment;
use Illuminate\Support\Facades\DB;

class EarningsService
{
    public function __construct(
        private readonly WalletService $wallets,
        private readonly ReferralService $referrals,
        private readonly TaskAssignmentService $assignments,
        private readonly LedgerService $ledger,
    ) {}

    public function creditForApprovedTask(TaskAssignment $assignment): int
    {
        return DB::transaction(function () use ($assignment) {
            $assignment = TaskAssignment::query()->whereKey($assignment->id)->lockForUpdate()->firstOrFail();
            $user = $assignment->user()->lockForUpdate()->firstOrFail();
            $campaign = Campaign::query()->whereKey($assignment->campaign_id)->lockForUpdate()->firstOrFail();
            $profile = $this->assignments->refreshDailyCounters($user);
            $profile = $profile->newQuery()->whereKey($profile->id)->lockForUpdate()->firstOrFail();

            $plan = $user->activeSubscription?->plan;
            $dailyCap = $plan?->alignedDailyCap() ?: ($plan?->daily_cap_xof ?? 100);
            $perTask = $plan?->rewardPerTask() ?: max(1, (int) SystemSetting::getValue('default_reward_per_task_xof', 10));
            $remainingCap = max(0, $dailyCap - $profile->daily_earnings_xof);

            // Gain = min(récompense plan, reste du plafond journalier).
            $earned = min($assignment->reward_xof, $perTask, $remainingCap);

            $assignment->earned_xof = $earned;
            $assignment->save();

            if ($earned > 0) {
                $this->wallets->creditAvailable(
                    user: $user,
                    amount: $earned,
                    type: WalletTransactionType::TaskEarning,
                    idempotencyKey: 'task-earning-'.$assignment->id,
                    description: 'Gain mission #'.$assignment->id,
                    related: $assignment,
                );
            }

            $commission = $this->referrals->creditForTask($assignment, $earned);

            $spent = $earned + $commission;
            $reserved = CampaignBudgetReservation::query()
                ->where('task_assignment_id', $assignment->id)
                ->where('status', 'reserved')
                ->lockForUpdate()
                ->first();

            if ($reserved) {
                $reserved->forceFill(['status' => 'consumed'])->save();
                $campaign->budget_reserved_xof = max(0, $campaign->budget_reserved_xof - $reserved->amount_xof);
                $release = max(0, $reserved->amount_xof - $spent);
                if ($release > 0) {
                    $campaign->budget_available_xof += $release;
                }
            }

            $campaign->budget_spent_xof += $spent;
            $campaign->tasks_completed += 1;
            $campaign->tasks_approved += 1;
            $campaign->save();

            $this->ledger->post(
                idempotencyKey: 'escrow-consume-'.$assignment->id,
                debitCode: 'campaign_escrow',
                creditCode: 'platform_cash',
                amountXof: $spent,
                description: 'Consommation budget mission #'.$assignment->id,
                related: $assignment,
            );

            $profile->tasks_completed_today += 1;
            $profile->tasks_completed_total += 1;
            $profile->daily_earnings_xof += $earned;
            $profile->save();

            return $earned;
        });
    }
}
