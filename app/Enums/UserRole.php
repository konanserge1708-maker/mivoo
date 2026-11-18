<?php

namespace App\Enums;

enum UserRole: string
{
    case Worker = 'worker';
    case Partner = 'partner';
    case Admin = 'admin';
    case SuperAdmin = 'super_admin';
    case FinancialManager = 'financial_manager';
    case CampaignManager = 'campaign_manager';
    case SupportAgent = 'support_agent';

    public function label(): string
    {
        return match ($this) {
            self::Worker => 'Worker',
            self::Partner => 'Partenaire',
            self::Admin => 'Administrateur',
            self::SuperAdmin => 'Super-administrateur',
            self::FinancialManager => 'Gestionnaire financier',
            self::CampaignManager => 'Gestionnaire de campagnes',
            self::SupportAgent => 'Agent de support',
        };
    }

    public function isAdminPanel(): bool
    {
        return in_array($this, [
            self::Admin,
            self::SuperAdmin,
            self::FinancialManager,
            self::CampaignManager,
            self::SupportAgent,
        ], true);
    }
}
