<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\SubscriptionStatus;
use App\Models\PaymentTransaction;
use App\Models\SubscriptionOrder;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SubscriptionService
{
    public function createOrder(User $user, SubscriptionPlan $plan): SubscriptionOrder
    {
        if (! $plan->is_active) {
            throw new InvalidArgumentException('Ce niveau VIP n’est plus disponible.');
        }

        if ($plan->price_xof <= 0) {
            throw new InvalidArgumentException('Le niveau gratuit ne nécessite pas de paiement.');
        }

        return SubscriptionOrder::query()->create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'status' => OrderStatus::Pending,
            'amount_xof' => $plan->price_xof,
            'currency' => 'XOF',
            'duration_days' => $plan->duration_days,
            'plan_snapshot' => $plan->toArray(),
        ]);
    }

    public function activatePaidOrder(SubscriptionOrder $order, PaymentTransaction $transaction): UserSubscription
    {
        return DB::transaction(function () use ($order, $transaction) {
            $order = SubscriptionOrder::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($order->status === OrderStatus::Paid && $order->user_subscription_id) {
                return UserSubscription::query()->findOrFail($order->user_subscription_id);
            }

            if ($transaction->amount_xof !== $order->amount_xof) {
                throw new InvalidArgumentException('Montant payé incohérent avec la commande.');
            }

            $user = User::query()->whereKey($order->user_id)->lockForUpdate()->firstOrFail();

            UserSubscription::query()
                ->where('user_id', $user->id)
                ->where('status', SubscriptionStatus::Active->value)
                ->update([
                    'status' => SubscriptionStatus::Cancelled->value,
                ]);

            $startsAt = now();
            $endsAt = $startsAt->copy()->addDays($order->duration_days);

            $subscription = UserSubscription::query()->create([
                'user_id' => $user->id,
                'subscription_plan_id' => $order->subscription_plan_id,
                'status' => SubscriptionStatus::Active,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'price_paid_xof' => $order->amount_xof,
                'plan_snapshot' => $order->plan_snapshot,
            ]);

            $order->forceFill([
                'status' => OrderStatus::Paid,
                'paid_at' => now(),
                'user_subscription_id' => $subscription->id,
            ])->save();

            return $subscription;
        });
    }

    public function expireDueSubscriptions(): int
    {
        $expired = 0;

        UserSubscription::query()
            ->where('status', SubscriptionStatus::Active->value)
            ->whereNotNull('ends_at')
            ->where('ends_at', '<=', now())
            ->orderBy('id')
            ->chunkById(100, function ($subscriptions) use (&$expired) {
                foreach ($subscriptions as $subscription) {
                    DB::transaction(function () use ($subscription, &$expired) {
                        $locked = UserSubscription::query()
                            ->whereKey($subscription->id)
                            ->lockForUpdate()
                            ->first();

                        if (! $locked || $locked->status !== SubscriptionStatus::Active) {
                            return;
                        }

                        $locked->forceFill([
                            'status' => SubscriptionStatus::Expired,
                        ])->save();

                        $this->ensureFreePlan($locked->user);
                        $expired++;
                    });
                }
            });

        return $expired;
    }

    public function ensureFreePlan(User $user): UserSubscription
    {
        $active = $user->activeSubscription()->first();

        if ($active && $active->isActive()) {
            return $active;
        }

        $freePlan = SubscriptionPlan::query()
            ->where(function ($query) {
                $query->where('is_default', true)
                    ->orWhere('slug', 'gratuit');
            })
            ->firstOrFail();

        return UserSubscription::query()->create([
            'user_id' => $user->id,
            'subscription_plan_id' => $freePlan->id,
            'status' => SubscriptionStatus::Active,
            'starts_at' => now(),
            'ends_at' => now()->addYears(10),
            'price_paid_xof' => 0,
            'plan_snapshot' => $freePlan->toArray(),
        ]);
    }
}
