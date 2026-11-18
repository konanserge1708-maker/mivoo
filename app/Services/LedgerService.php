<?php

namespace App\Services;

use App\Models\LedgerAccount;
use App\Models\LedgerEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class LedgerService
{
    public function ensureSystemAccounts(): void
    {
        $accounts = [
            ['code' => 'platform_cash', 'name' => 'Trésorerie plateforme', 'type' => 'asset'],
            ['code' => 'campaign_escrow', 'name' => 'Escrow campagnes', 'type' => 'liability'],
            ['code' => 'worker_payable', 'name' => 'Dettes workers', 'type' => 'liability'],
            ['code' => 'referral_payable', 'name' => 'Dettes parrainage', 'type' => 'liability'],
            ['code' => 'subscription_revenue', 'name' => 'CA abonnements', 'type' => 'revenue'],
            ['code' => 'campaign_revenue', 'name' => 'CA campagnes', 'type' => 'revenue'],
            ['code' => 'task_expense', 'name' => 'Charges missions', 'type' => 'expense'],
            ['code' => 'referral_expense', 'name' => 'Charges parrainage', 'type' => 'expense'],
            ['code' => 'ops_expense', 'name' => 'Charges exploitation', 'type' => 'expense'],
        ];

        foreach ($accounts as $account) {
            LedgerAccount::query()->firstOrCreate(
                ['code' => $account['code']],
                $account + ['currency' => 'XOF']
            );
        }
    }

    public function account(string $code): LedgerAccount
    {
        return LedgerAccount::query()->where('code', $code)->firstOrFail();
    }

    public function post(
        string $idempotencyKey,
        string $debitCode,
        string $creditCode,
        int $amountXof,
        string $description,
        ?User $user = null,
        ?Model $related = null,
        array $meta = [],
    ): ?LedgerEntry {
        if ($amountXof <= 0) {
            return null;
        }

        return DB::transaction(function () use ($idempotencyKey, $debitCode, $creditCode, $amountXof, $description, $user, $related, $meta) {
            $existing = LedgerEntry::query()->where('idempotency_key', $idempotencyKey)->first();
            if ($existing) {
                return $existing;
            }

            $this->ensureSystemAccounts();

            return LedgerEntry::query()->create([
                'idempotency_key' => $idempotencyKey,
                'debit_account_id' => $this->account($debitCode)->id,
                'credit_account_id' => $this->account($creditCode)->id,
                'amount_xof' => $amountXof,
                'currency' => 'XOF',
                'description' => $description,
                'related_type' => $related ? $related::class : null,
                'related_id' => $related?->getKey(),
                'user_id' => $user?->id,
                'meta' => $meta,
            ]);
        });
    }
}
