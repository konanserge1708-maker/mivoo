<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['group' => 'general', 'key' => 'app_name', 'value' => 'Mivoo', 'type' => 'string', 'description' => 'Nom de la plateforme'],
            ['group' => 'general', 'key' => 'currency', 'value' => 'XOF', 'type' => 'string', 'description' => 'Devise'],
            ['group' => 'general', 'key' => 'timezone', 'value' => 'Africa/Abidjan', 'type' => 'string', 'description' => 'Fuseau horaire'],
            ['group' => 'tasks', 'key' => 'max_tasks_per_day', 'value' => '10', 'type' => 'integer', 'description' => 'Plafond absolu de missions / jour (max 10)'],
            ['group' => 'tasks', 'key' => 'daily_missions_mode', 'value' => 'fixed', 'type' => 'string', 'description' => 'Mode tirage : fixed ou random'],
            ['group' => 'tasks', 'key' => 'daily_missions_count', 'value' => '10', 'type' => 'integer', 'description' => 'Nombre fixe de missions / jour'],
            ['group' => 'tasks', 'key' => 'default_reward_per_task_xof', 'value' => '10', 'type' => 'integer', 'description' => 'Récompense de base / mission (Gratuit = 10 F)'],
            ['group' => 'tasks', 'key' => 'mission_min_dwell_seconds', 'value' => '12', 'type' => 'integer', 'description' => 'Délai minimum après ouverture du lien avant confirmation'],
            ['group' => 'referrals', 'key' => 'referral_commission_percent', 'value' => '10', 'type' => 'integer', 'description' => 'Commission de parrainage (%)'],
            ['group' => 'withdrawals', 'key' => 'withdrawals_enabled', 'value' => '1', 'type' => 'boolean', 'description' => 'Activer les retraits'],
            ['group' => 'withdrawals', 'key' => 'min_withdrawal_xof', 'value' => '1000', 'type' => 'integer', 'description' => 'Seuil minimal de retrait'],
            ['group' => 'withdrawals', 'key' => 'max_withdrawal_xof', 'value' => '0', 'type' => 'integer', 'description' => 'Seuil maximal de retrait (0 = illimité)'],
            ['group' => 'withdrawals', 'key' => 'withdrawal_fee_xof', 'value' => '0', 'type' => 'integer', 'description' => 'Frais de retrait'],
            ['group' => 'subscriptions', 'key' => 'subscription_duration_days', 'value' => '90', 'type' => 'integer', 'description' => 'Durée VIP'],
        ];

        foreach ($settings as $setting) {
            SystemSetting::query()->updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
