<?php

namespace App\Services;

use App\Enums\WithdrawalStatus;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\WithdrawalRequest;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class WithdrawalService
{
    public function __construct(private readonly WalletService $wallets) {}

    public function request(User $user, int $amount, string $method, string $phone): WithdrawalRequest
    {
        if (! SystemSetting::getValue('withdrawals_enabled', true)) {
            throw new InvalidArgumentException('Les retraits sont temporairement désactivés sur la plateforme.');
        }

        $user->loadMissing('workerProfile');
        $profile = $user->workerProfile;
        if ($profile?->withdrawals_blocked) {
            $reason = $profile->withdrawal_block_reason
                ? ' '.$profile->withdrawal_block_reason
                : '';
            throw new InvalidArgumentException('Les retraits sont bloqués sur votre compte.'.$reason);
        }

        $min = (int) SystemSetting::getValue('min_withdrawal_xof', 1000);
        $fee = (int) SystemSetting::getValue('withdrawal_fee_xof', 0);
        $globalMax = (int) SystemSetting::getValue('max_withdrawal_xof', 0);
        $userMax = $profile?->max_withdrawal_xof;

        if ($amount < $min) {
            throw new InvalidArgumentException("Le minimum de retrait est de {$min} F.");
        }

        $effectiveMax = null;
        if ($userMax && $userMax > 0) {
            $effectiveMax = $userMax;
        } elseif ($globalMax > 0) {
            $effectiveMax = $globalMax;
        }

        if ($effectiveMax !== null && $amount > $effectiveMax) {
            throw new InvalidArgumentException("Le maximum de retrait autorisé est de {$effectiveMax} F.");
        }

        return DB::transaction(function () use ($user, $amount, $method, $phone, $fee, $profile) {
            $wallet = $this->wallets->ensureWallet($user);
            $wallet = $wallet->newQuery()->whereKey($wallet->id)->lockForUpdate()->firstOrFail();

            if ($wallet->available_balance_xof < $amount) {
                throw new InvalidArgumentException('Solde insuffisant.');
            }

            // Toujours en attente / examen — aucun paiement sans approbation admin.
            $underExamination = (bool) ($profile?->withdrawals_under_examination);
            $status = $underExamination
                ? WithdrawalStatus::UnderReview
                : WithdrawalStatus::Pending;

            $request = WithdrawalRequest::query()->create([
                'user_id' => $user->id,
                'amount_xof' => $amount,
                'fee_xof' => $fee,
                'net_amount_xof' => max(0, $amount - $fee),
                'method' => $method,
                'destination_phone' => $phone,
                'status' => $status,
                'provider' => config('payments.default'),
                'admin_notes' => $underExamination
                    ? 'Compte placé en examen — validation manuelle requise.'
                    : null,
            ]);

            $this->wallets->holdForWithdrawal(
                $user,
                $amount,
                'withdraw-hold-'.$request->id,
                $request
            );

            return $request;
        });
    }

    /**
     * Seule porte d’entrée vers le traitement : validation admin obligatoire.
     */
    public function approve(WithdrawalRequest $request, User $admin, ?string $notes = null): WithdrawalRequest
    {
        return DB::transaction(function () use ($request, $admin, $notes) {
            $locked = WithdrawalRequest::query()->whereKey($request->id)->lockForUpdate()->firstOrFail();

            if (! in_array($locked->status, [WithdrawalStatus::Pending, WithdrawalStatus::UnderReview], true)) {
                throw new InvalidArgumentException('Demande non approuvable.');
            }

            $locked->forceFill([
                'status' => WithdrawalStatus::Approved,
                'reviewed_by' => $admin->id,
                'admin_notes' => $notes ?: $locked->admin_notes,
            ])->save();

            // Après approbation uniquement : sandbox finalise, sinon passage en traitement.
            if (config('payments.default') === 'sandbox') {
                return $this->complete($locked->fresh(), 'sandbox_transfer_'.$locked->id);
            }

            $locked->forceFill(['status' => WithdrawalStatus::Processing])->save();

            return $locked->fresh();
        });
    }

    public function markUnderReview(WithdrawalRequest $request, User $admin, ?string $notes = null): WithdrawalRequest
    {
        $locked = WithdrawalRequest::query()->whereKey($request->id)->firstOrFail();

        if (! in_array($locked->status, [WithdrawalStatus::Pending, WithdrawalStatus::UnderReview], true)) {
            throw new InvalidArgumentException('Cette demande ne peut plus passer en examen.');
        }

        $locked->forceFill([
            'status' => WithdrawalStatus::UnderReview,
            'reviewed_by' => $admin->id,
            'admin_notes' => $notes ?: ($locked->admin_notes ?: 'Placé en examen par l’administration.'),
        ])->save();

        return $locked->fresh();
    }

    public function complete(WithdrawalRequest $request, string $providerTransferId): WithdrawalRequest
    {
        return DB::transaction(function () use ($request, $providerTransferId) {
            $locked = WithdrawalRequest::query()->whereKey($request->id)->lockForUpdate()->firstOrFail();

            if ($locked->status === WithdrawalStatus::Completed) {
                return $locked;
            }

            // Interdit de finaliser sans passage par l’approbation admin.
            if (! in_array($locked->status, [WithdrawalStatus::Approved, WithdrawalStatus::Processing], true)) {
                throw new InvalidArgumentException(
                    'Retrait non finalisable : une approbation administrateur est obligatoire avant traitement.'
                );
            }

            $this->wallets->completeWithdrawal(
                $locked->user,
                $locked->amount_xof,
                'withdraw-complete-'.$locked->id,
                $locked
            );

            $locked->forceFill([
                'status' => WithdrawalStatus::Completed,
                'provider_transfer_id' => $providerTransferId,
                'processed_at' => now(),
            ])->save();

            return $locked->fresh();
        });
    }

    public function reject(WithdrawalRequest $request, User $admin, string $reason): WithdrawalRequest
    {
        return DB::transaction(function () use ($request, $admin, $reason) {
            $locked = WithdrawalRequest::query()->whereKey($request->id)->lockForUpdate()->firstOrFail();

            if (in_array($locked->status, [WithdrawalStatus::Completed, WithdrawalStatus::Rejected], true)) {
                throw new InvalidArgumentException('Demande déjà finalisée.');
            }

            $this->wallets->refundWithdrawal(
                $locked->user,
                $locked->amount_xof,
                'withdraw-refund-'.$locked->id,
                $locked,
                $reason
            );

            $locked->forceFill([
                'status' => WithdrawalStatus::Rejected,
                'reviewed_by' => $admin->id,
                'admin_notes' => $reason,
                'processed_at' => now(),
            ])->save();

            return $locked->fresh();
        });
    }
}
