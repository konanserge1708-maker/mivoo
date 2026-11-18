<?php

namespace App\Enums;

enum CampaignStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Suspended = 'suspended';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Brouillon',
            self::Active => 'Active',
            self::Suspended => 'Suspendue',
            self::Completed => 'Terminée',
            self::Cancelled => 'Annulée',
            self::Expired => 'Expirée',
        };
    }
}
