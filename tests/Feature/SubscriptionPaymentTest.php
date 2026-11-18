<?php

namespace Tests\Feature;

use App\Enums\SubscriptionStatus;
use App\Enums\UserRole;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\SubscriptionService;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\SubscriptionPlanSeeder::class);
        $this->seed(\Database\Seeders\SystemSettingSeeder::class);
    }

    public function test_worker_can_subscribe_via_sandbox_and_activate_vip(): void
    {
        config(['payments.default' => 'sandbox']);

        $user = User::factory()->create([
            'phone' => '+2250700999888',
        ]);
        $user->assignRole(UserRole::Worker);
        $user->workerProfile()->create(['country' => 'CI']);
        app(WalletService::class)->ensureWallet($user);
        app(SubscriptionService::class)->ensureFreePlan($user);

        $plan = SubscriptionPlan::query()->where('slug', 'vip-1')->firstOrFail();

        $this->actingAs($user)
            ->post(route('worker.subscriptions.store', $plan), [
                'payment_method' => 'wave',
            ])
            ->assertRedirect();

        $orderModel = \App\Models\SubscriptionOrder::query()->where('user_id', $user->id)->latest('id')->first();
        $this->assertNotNull($orderModel);

        $transaction = \App\Models\PaymentTransaction::query()
            ->where('payable_id', $orderModel->id)
            ->firstOrFail();

        $this->actingAs($user)
            ->post(route('payments.sandbox.complete', $transaction->uuid), [
                'action' => 'success',
            ])
            ->assertRedirect();

        $active = $user->fresh()->activeSubscription;
        $this->assertNotNull($active);
        $this->assertSame(SubscriptionStatus::Active, $active->status);
        $this->assertSame($plan->id, $active->subscription_plan_id);
        $this->assertSame(1000, $active->price_paid_xof);
    }

    public function test_admin_can_update_vip_plan_price(): void
    {
        $admin = User::factory()->create(['phone' => '+2250700111222']);
        $admin->assignRole(UserRole::SuperAdmin);

        $plan = SubscriptionPlan::query()->where('slug', 'vip-2')->firstOrFail();

        $this->actingAs($admin)
            ->put(route('admin.subscription-plans.update', $plan), [
                'name' => 'VIP 2',
                'price_xof' => 5500,
                'duration_days' => 90,
                'daily_cap_xof' => 500,
                'indicative_task_value_xof' => 50,
                'max_tasks_per_day' => 10,
                'sort_order' => 2,
                'is_active' => 1,
                'benefits_text' => "Plafond journalier\nSupport prioritaire",
            ])
            ->assertRedirect();

        $this->assertSame(5500, $plan->fresh()->price_xof);
        $this->assertDatabaseHas('subscription_plan_audits', [
            'subscription_plan_id' => $plan->id,
            'action' => 'updated',
        ]);
    }

    public function test_min_withdrawal_cannot_be_below_1000(): void
    {
        $admin = User::factory()->create(['phone' => '+2250700333444']);
        $admin->assignRole(UserRole::SuperAdmin);

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), [
                'min_withdrawal_xof' => 500,
                'withdrawal_fee_xof' => 0,
                'max_tasks_per_day' => 10,
                'referral_commission_percent' => 10,
            ])
            ->assertSessionHasErrors('min_withdrawal_xof');
    }
}
