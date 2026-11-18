<?php

namespace App\Services;

use App\Enums\WalletTransactionType;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class WalletService
{
    public function __construct(private readonly LedgerService $ledger) {}

    public function ensureWallet(User $user): Wallet
    {
        return $user->wallet()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'available_balance_xof' => 0,
                'pending_balance_xof' => 0,
                'lifetime_earned_xof' => 0,
                'lifetime_withdrawn_xof' => 0,
                'currency' => 'XOF',
            ]
        );
    }

    public function createForUser(User $user): Wallet
    {
        return DB::transaction(fn () => $this->ensureWallet($user));
    }

    public function creditAvailable(
        User $user,
        int $amount,
        WalletTransactionType $type,
        string $idempotencyKey,
        string $description,
        ?Model $related = null,
        array $meta = [],
    ): WalletTransaction {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Montant invalide.');
        }

        return DB::transaction(function () use ($user, $amount, $type, $idempotencyKey, $description, $related, $meta) {
            $existing = WalletTransaction::query()->where('idempotency_key', $idempotencyKey)->first();
            if ($existing) {
                return $existing;
            }

            $wallet = Wallet::query()->where('user_id', $user->id)->lockForUpdate()->first()
                ?? $this->ensureWallet($user)->fresh();

            $wallet = Wallet::query()->whereKey($wallet->id)->lockForUpdate()->firstOrFail();
            $wallet->available_balance_xof += $amount;
            $wallet->lifetime_earned_xof += $amount;
            $wallet->save();

            $tx = WalletTransaction::query()->create([
                'wallet_id' => $wallet->id,
                'user_id' => $user->id,
                'type' => $type,
                'amount_xof' => $amount,
                'currency' => 'XOF',
                'status' => 'completed',
                'reference' => 'WTX-'.strtoupper(Str::random(10)),
                'idempotency_key' => $idempotencyKey,
                'related_type' => $related ? $related::class : null,
                'related_id' => $related?->getKey(),
                'description' => $description,
                'meta' => $meta,
            ]);

            $expenseCode = $type === WalletTransactionType::ReferralBonus
                ? 'referral_expense'
                : 'task_expense';
            $payableCode = $type === WalletTransactionType::ReferralBonus
                ? 'referral_payable'
                : 'worker_payable';

            $this->ledger->post(
                idempotencyKey: 'ledger-'.$idempotencyKey,
                debitCode: $expenseCode,
                creditCode: $payableCode,
                amountXof: $amount,
                description: $description,
                user: $user,
                related: $related,
            );

            return $tx;
        });
    }

    public function holdForWithdrawal(User $user, int $amount, string $idempotencyKey, Model $related): WalletTransaction
    {
        return DB::transaction(function () use ($user, $amount, $idempotencyKey, $related) {
            $existing = WalletTransaction::query()->where('idempotency_key', $idempotencyKey)->first();
            if ($existing) {
                return $existing;
            }

            $wallet = Wallet::query()->where('user_id', $user->id)->lockForUpdate()->firstOrFail();

            if ($wallet->available_balance_xof < $amount) {
                throw new InvalidArgumentException('Solde insuffisant.');
            }

            $wallet->available_balance_xof -= $amount;
            $wallet->pending_balance_xof += $amount;
            $wallet->save();

            return WalletTransaction::query()->create([
                'wallet_id' => $wallet->id,
                'user_id' => $user->id,
                'type' => WalletTransactionType::Withdrawal,
                'amount_xof' => -$amount,
                'currency' => 'XOF',
                'status' => 'pending',
                'reference' => 'WTX-'.strtoupper(Str::random(10)),
                'idempotency_key' => $idempotencyKey,
                'related_type' => $related::class,
                'related_id' => $related->getKey(),
                'description' => 'Retrait en attente',
            ]);
        });
    }

    public function completeWithdrawal(User $user, int $amount, string $idempotencyKey, Model $related): void
    {
        DB::transaction(function () use ($user, $amount, $idempotencyKey, $related) {
            if (WalletTransaction::query()->where('idempotency_key', $idempotencyKey)->exists()) {
                return;
            }

            $wallet = Wallet::query()->where('user_id', $user->id)->lockForUpdate()->firstOrFail();
            $wallet->pending_balance_xof = max(0, $wallet->pending_balance_xof - $amount);
            $wallet->lifetime_withdrawn_xof += $amount;
            $wallet->save();

            WalletTransaction::query()->create([
                'wallet_id' => $wallet->id,
                'user_id' => $user->id,
                'type' => WalletTransactionType::Withdrawal,
                'amount_xof' => -$amount,
                'currency' => 'XOF',
                'status' => 'completed',
                'reference' => 'WTX-'.strtoupper(Str::random(10)),
                'idempotency_key' => $idempotencyKey,
                'related_type' => $related::class,
                'related_id' => $related->getKey(),
                'description' => 'Retrait effectué',
            ]);

            $this->ledger->post(
                idempotencyKey: 'ledger-'.$idempotencyKey,
                debitCode: 'worker_payable',
                creditCode: 'platform_cash',
                amountXof: $amount,
                description: 'Retrait worker',
                user: $user,
                related: $related,
            );
        });
    }

    public function refundWithdrawal(User $user, int $amount, string $idempotencyKey, Model $related, string $reason): void
    {
        DB::transaction(function () use ($user, $amount, $idempotencyKey, $related, $reason) {
            if (WalletTransaction::query()->where('idempotency_key', $idempotencyKey)->exists()) {
                return;
            }

            $wallet = Wallet::query()->where('user_id', $user->id)->lockForUpdate()->firstOrFail();
            $wallet->pending_balance_xof = max(0, $wallet->pending_balance_xof - $amount);
            $wallet->available_balance_xof += $amount;
            $wallet->save();

            WalletTransaction::query()->create([
                'wallet_id' => $wallet->id,
                'user_id' => $user->id,
                'type' => WalletTransactionType::WithdrawalRefund,
                'amount_xof' => $amount,
                'currency' => 'XOF',
                'status' => 'completed',
                'reference' => 'WTX-'.strtoupper(Str::random(10)),
                'idempotency_key' => $idempotencyKey,
                'related_type' => $related::class,
                'related_id' => $related->getKey(),
                'description' => $reason,
            ]);
        });
    }
}
