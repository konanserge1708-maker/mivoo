<?php

namespace Database\Seeders;

use App\Enums\AccountStatus;
use App\Enums\UserRole;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use App\Services\WalletService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $walletService = app(WalletService::class);
        $freePlan = SubscriptionPlan::query()->where('slug', 'gratuit')->first();

        $worker = User::query()->updateOrCreate(
            ['email' => 'worker@mivoo.test'],
            [
                'last_name' => 'Kouassi',
                'first_name' => 'Awa',
                'phone' => '+2250700000001',
                'password' => Hash::make('password'),
                'status' => AccountStatus::Active,
                'terms_accepted' => true,
                'terms_accepted_at' => now(),
                'email_verified_at' => now(),
            ]
        );
        $worker->assignRole(UserRole::Worker);
        $worker->workerProfile()->firstOrCreate(['user_id' => $worker->id], ['country' => 'CI', 'city' => 'Abidjan']);
        $walletService->ensureWallet($worker);

        if ($freePlan && ! $worker->subscriptions()->where('status', 'active')->exists()) {
            UserSubscription::query()->create([
                'user_id' => $worker->id,
                'subscription_plan_id' => $freePlan->id,
                'status' => 'active',
                'starts_at' => now(),
                'ends_at' => now()->addYears(10),
                'price_paid_xof' => 0,
                'plan_snapshot' => $freePlan->toArray(),
            ]);
        }

        $partner = User::query()->updateOrCreate(
            ['email' => 'partner@mivoo.test'],
            [
                'last_name' => 'Traoré',
                'first_name' => 'Jean',
                'phone' => '+2250700000002',
                'password' => Hash::make('password'),
                'status' => AccountStatus::Active,
                'terms_accepted' => true,
                'terms_accepted_at' => now(),
                'email_verified_at' => now(),
            ]
        );
        $partner->assignRole(UserRole::Partner);
        $partner->partnerProfile()->firstOrCreate(
            ['user_id' => $partner->id],
            ['company_name' => 'Demo Partner CI', 'city' => 'Abidjan', 'country' => 'CI', 'is_verified' => true]
        );
        $walletService->ensureWallet($partner);

        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@mivoo.test'],
            [
                'last_name' => 'Admin',
                'first_name' => 'Mivoo',
                'phone' => '+2250700000003',
                'password' => Hash::make('password'),
                'status' => AccountStatus::Active,
                'terms_accepted' => true,
                'terms_accepted_at' => now(),
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole(UserRole::SuperAdmin);
        $walletService->ensureWallet($admin);
    }
}
