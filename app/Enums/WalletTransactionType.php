<?php

namespace App\Enums;

enum WalletTransactionType: string
{
    case TaskEarning = 'task_earning';
    case ReferralBonus = 'referral_bonus';
    case Withdrawal = 'withdrawal';
    case WithdrawalRefund = 'withdrawal_refund';
    case Adjustment = 'adjustment';
    case Reversal = 'reversal';

    public function label(): string
    {
        return match ($this) {
            self::TaskEarning => 'Gain de tâche',
            self::ReferralBonus => 'Commission de parrainage',
            self::Withdrawal => 'Retrait',
            self::WithdrawalRefund => 'Remboursement de retrait',
            self::Adjustment => 'Ajustement',
            self::Reversal => 'Contre-écriture',
        };
    }
}
