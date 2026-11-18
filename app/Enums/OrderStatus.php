<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case AwaitingPayment = 'awaiting_payment';
    case Paid = 'paid';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'En attente',
            self::AwaitingPayment => 'Paiement en cours',
            self::Paid => 'Payée',
            self::Failed => 'Échouée',
            self::Cancelled => 'Annulée',
            self::Expired => 'Expirée',
        };
    }
}
