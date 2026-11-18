<?php

namespace App\Services;

use App\Enums\WalletTransactionType;
use App\Models\ReferralCommission;
use App\Models\SystemSetting;
use App\Models\TaskAssignment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReferralService
{
    public function __construct(private readonly WalletService $wallets) {}

    public function findReferrer(?string $code): ?User
    {
        if (! $code) {
            return null;
        }

        return User::query()
            ->where('referral_code', strtoupper(trim($code)))
            ->where('status', 'active')
            ->first();
    }

    public function creditForTask(TaskAssignment $assignment, int $baseAmount): int
    {
        if ($baseAmount <= 0) {
            return 0;
        }

        return DB::transaction(function () use ($assignment, $baseAmount) {
            if (ReferralCommission::query()->where('task_assignment_id', $assignment->id)->exists()) {
                return (int) ReferralCommission::query()
                    ->where('task_assignment_id', $assignment->id)
                    ->value('commission_xof');
            }

            $worker = $assignment->user()->first();
            if (! $worker?->referred_by) {
                return 0;
            }

            $referrer = User::query()->whereKey($worker->referred_by)->lockForUpdate()->first();
            if (! $referrer || $referrer->id === $worker->id) {
                return 0;
            }

            $rate = (int) SystemSetting::getValue('referral_commission_percent', 10);
            $commission = (int) floor($baseAmount * $rate / 100);

            if ($commission <= 0) {
                return 0;
            }

            ReferralCommission::query()->create([
                'referrer_id' => $referrer->id,
                'referred_id' => $worker->id,
                'task_assignment_id' => $assignment->id,
                'base_amount_xof' => $baseAmount,
                'commission_xof' => $commission,
                'rate_percent' => $rate,
                'status' => 'credited',
            ]);

            $this->wallets->creditAvailable(
                user: $referrer,
                amount: $commission,
                type: WalletTransactionType::ReferralBonus,
                idempotencyKey: 'referral-'.$assignment->id,
                description: 'Commission parrainage mission #'.$assignment->id,
                related: $assignment,
            );

            return $commission;
        });
    }
}
