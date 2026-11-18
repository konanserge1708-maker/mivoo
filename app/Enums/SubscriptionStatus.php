<?php

namespace App\Enums;

enum SubscriptionStatus: string
{
    case Active = 'active';
    case Expired = 'expired';
    case Cancelled = 'cancelled';
    case PendingPayment = 'pending_payment';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Actif',
            self::Expired => 'Expiré',
            self::Cancelled => 'Annulé',
            self::PendingPayment => 'Paiement en attente',
        };
    }
}
