<?php

namespace App\Enums;

enum WithdrawalStatus: string
{
    case Pending = 'pending';
    case UnderReview = 'under_review';
    case Approved = 'approved';
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'En attente',
            self::UnderReview => 'En examen',
            self::Approved => 'Approuvé',
            self::Processing => 'En traitement',
            self::Completed => 'Effectué',
            self::Failed => 'Échoué',
            self::Rejected => 'Rejeté',
            self::Cancelled => 'Annulé',
        };
    }
}
