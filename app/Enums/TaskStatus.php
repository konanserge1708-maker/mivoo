<?php

namespace App\Enums;

enum TaskStatus: string
{
    case Available = 'available';
    case Assigned = 'assigned';
    case InProgress = 'in_progress';
    case Submitted = 'submitted';
    case UnderReview = 'under_review';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Expired = 'expired';
    case Disputed = 'disputed';

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Disponible',
            self::Assigned => 'Attribuée',
            self::InProgress => 'En cours',
            self::Submitted => 'Soumise',
            self::UnderReview => 'En revue',
            self::Approved => 'Approuvée',
            self::Rejected => 'Rejetée',
            self::Expired => 'Expirée',
            self::Disputed => 'Contestée',
        };
    }
}
