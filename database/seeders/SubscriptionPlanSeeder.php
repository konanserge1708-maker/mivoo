<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use App\Services\TaskAssignmentService;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $maxTasks = TaskAssignmentService::HARD_DAILY_MAX;

        // Même modèle pour tous : plafond / jour = missions / jour × gain / mission.
        $plans = [
            ['slug' => 'gratuit', 'name' => 'Gratuit', 'price_xof' => 0, 'reward' => 10, 'level' => 0, 'is_default' => true, 'sort_order' => 0],
            ['slug' => 'vip-1', 'name' => 'VIP 1', 'price_xof' => 1000, 'reward' => 20, 'level' => 1, 'sort_order' => 1],
            ['slug' => 'vip-2', 'name' => 'VIP 2', 'price_xof' => 5000, 'reward' => 50, 'level' => 2, 'sort_order' => 2],
            ['slug' => 'vip-3', 'name' => 'VIP 3', 'price_xof' => 10000, 'reward' => 100, 'level' => 3, 'sort_order' => 3],
            ['slug' => 'vip-4', 'name' => 'VIP 4', 'price_xof' => 25000, 'reward' => 200, 'level' => 4, 'sort_order' => 4],
            ['slug' => 'vip-5', 'name' => 'VIP 5', 'price_xof' => 50000, 'reward' => 350, 'level' => 5, 'sort_order' => 5],
            ['slug' => 'vip-6', 'name' => 'VIP 6', 'price_xof' => 100000, 'reward' => 600, 'level' => 6, 'sort_order' => 6],
            ['slug' => 'vip-7', 'name' => 'VIP 7', 'price_xof' => 250000, 'reward' => 1200, 'level' => 7, 'sort_order' => 7],
        ];

        foreach ($plans as $plan) {
            $reward = (int) $plan['reward'];
            $dailyCap = $maxTasks * $reward;

            SubscriptionPlan::query()->updateOrCreate(
                ['slug' => $plan['slug']],
                [
                    'name' => $plan['name'],
                    'price_xof' => $plan['price_xof'],
                    'duration_days' => 90,
                    'daily_cap_xof' => $dailyCap,
                    'indicative_task_value_xof' => $reward,
                    'max_tasks_per_day' => $maxTasks,
                    'level' => $plan['level'],
                    'is_active' => true,
                    'is_default' => $plan['is_default'] ?? false,
                    'sort_order' => $plan['sort_order'],
                    'benefits' => [
                        $dailyCap.' F CFA max / jour',
                        $maxTasks.' missions à '.$reward.' F',
                        'Accès aux missions du pool',
                    ],
                ]
            );
        }
    }
}
