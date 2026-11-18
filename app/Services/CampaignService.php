<?php

namespace App\Services;

use App\Enums\CampaignStatus;
use App\Models\Campaign;
use App\Models\CampaignFunding;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CampaignService
{
    public function __construct(private readonly LedgerService $ledger) {}

    public function create(User $admin, array $data): Campaign
    {
        $referralRate = (int) SystemSetting::getValue('referral_commission_percent', 10);
        $reward = (int) $data['reward_per_task_xof'];
        // Pool vrac : pas de quota ni de date de fin ; budget initial pour ~1000 attributions.
        $tasksOrdered = (int) ($data['tasks_ordered'] ?? 1_000_000);
        $seedUnits = 1000;
        $seedBudget = (int) ceil($seedUnits * $reward * (1 + ($referralRate / 100)));
        $budget = max((int) ($data['budget_total_xof'] ?? 0), $seedBudget);

        return Campaign::query()->create([
            'created_by' => $admin->id,
            'campaign_category_id' => $data['campaign_category_id'],
            'title' => $data['title'],
            'description' => $data['description'],
            'instructions' => $data['instructions'],
            'validation_criteria' => $data['validation_criteria'] ?? null,
            'evidence_type' => $data['evidence_type'] ?? 'text',
            'target_url' => $data['target_url'] ?? null,
            'status' => CampaignStatus::Draft,
            'tasks_ordered' => $tasksOrdered,
            'reward_per_task_xof' => $reward,
            'budget_total_xof' => $budget,
            'budget_available_xof' => $budget,
            'min_vip_level' => (int) ($data['min_vip_level'] ?? 0),
            'max_participants' => null,
            'allow_repeat' => false,
            'completion_minutes' => (int) ($data['completion_minutes'] ?? 30),
            'country' => $data['country'] ?? 'CI',
            'starts_at' => $data['starts_at'] ?? now(),
            'ends_at' => null,
        ]);
    }

    public function delete(Campaign $campaign): void
    {
        DB::transaction(function () use ($campaign) {
            $locked = Campaign::query()->whereKey($campaign->id)->lockForUpdate()->firstOrFail();
            $locked->forceFill(['status' => CampaignStatus::Cancelled])->save();
            $locked->delete();
        });
    }

    public function fundAndActivate(Campaign $campaign, User $admin, ?string $reference = null): Campaign
    {
        return DB::transaction(function () use ($campaign, $admin, $reference) {
            $locked = Campaign::query()->whereKey($campaign->id)->lockForUpdate()->firstOrFail();

            if ($locked->funding_confirmed && $locked->status === CampaignStatus::Active) {
                return $locked;
            }

            CampaignFunding::query()->create([
                'campaign_id' => $locked->id,
                'recorded_by' => $admin->id,
                'amount_xof' => $locked->budget_total_xof,
                'method' => 'admin_manual',
                'reference' => $reference ?? 'ADMIN-FUND-'.$locked->id,
                'notes' => 'Financement confirmé par administration',
            ]);

            $locked->forceFill([
                'funding_confirmed' => true,
                'funding_confirmed_at' => now(),
                'status' => CampaignStatus::Active,
                'budget_available_xof' => $locked->budget_total_xof - $locked->budget_reserved_xof - $locked->budget_spent_xof,
            ])->save();

            $this->ledger->post(
                idempotencyKey: 'campaign-fund-'.$locked->id,
                debitCode: 'platform_cash',
                creditCode: 'campaign_escrow',
                amountXof: $locked->budget_total_xof,
                description: 'Financement campagne #'.$locked->id,
                user: $admin,
                related: $locked,
            );

            return $locked->fresh();
        });
    }

    public function suspend(Campaign $campaign): Campaign
    {
        $campaign->forceFill(['status' => CampaignStatus::Suspended])->save();

        return $campaign;
    }

    public function update(Campaign $campaign, array $data): Campaign
    {
        if ($campaign->status === CampaignStatus::Active && $campaign->tasks_assigned > 0) {
            $campaign->fill([
                'title' => $data['title'] ?? $campaign->title,
                'description' => $data['description'] ?? $campaign->description,
                'instructions' => $data['instructions'] ?? $campaign->instructions,
                'validation_criteria' => $data['validation_criteria'] ?? $campaign->validation_criteria,
                'ends_at' => $data['ends_at'] ?? $campaign->ends_at,
            ])->save();

            return $campaign->fresh();
        }

        $campaign->update($data);

        return $campaign->fresh();
    }
}
