<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\PaymentTransaction;
use App\Models\PaymentWebhookEvent;
use App\Models\SubscriptionOrder;
use App\Models\SubscriptionPayment;
use App\Models\User;
use App\Services\Payments\JekoPaymentGateway;
use App\Services\Payments\PaymentGatewayInterface;
use App\Services\Payments\SandboxPaymentGateway;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class PaymentService
{
    public function __construct(
        private readonly SubscriptionService $subscriptionService,
    ) {}

    public function gateway(?string $driver = null): PaymentGatewayInterface
    {
        $driver ??= config('payments.default', 'sandbox');

        return match ($driver) {
            'jeko' => app(JekoPaymentGateway::class),
            'sandbox' => app(SandboxPaymentGateway::class),
            default => throw new RuntimeException("Driver de paiement inconnu : {$driver}"),
        };
    }

    public function startSubscriptionPayment(
        User $user,
        SubscriptionOrder $order,
        string $paymentMethod,
    ): PaymentTransaction {
        return DB::transaction(function () use ($user, $order, $paymentMethod) {
            $gateway = $this->gateway();

            $transaction = PaymentTransaction::query()->create([
                'user_id' => $user->id,
                'payable_type' => SubscriptionOrder::class,
                'payable_id' => $order->id,
                'provider' => $gateway->name(),
                'payment_method' => $paymentMethod,
                'status' => PaymentStatus::Pending,
                'amount_xof' => $order->amount_xof,
                'currency' => $order->currency,
                'idempotency_key' => 'sub-order-'.$order->id.'-'.Str::uuid(),
            ]);

            SubscriptionPayment::query()->create([
                'subscription_order_id' => $order->id,
                'payment_transaction_id' => $transaction->id,
                'user_id' => $user->id,
                'amount_xof' => $order->amount_xof,
                'status' => PaymentStatus::Pending,
            ]);

            $result = $gateway->initiate($transaction, [
                'payment_method' => $paymentMethod,
                'success_url' => route('worker.subscriptions.payment.success', [
                    'order' => $order->uuid,
                ]),
                'error_url' => route('worker.subscriptions.payment.error', [
                    'order' => $order->uuid,
                ]),
                'description' => 'Abonnement '.($order->plan_snapshot['name'] ?? 'VIP'),
            ]);

            $transaction->forceFill([
                'provider_payment_id' => $result['provider_payment_id'],
                'redirect_url' => $result['redirect_url'],
                'request_payload' => $result['raw']['request'] ?? $result['raw'],
                'response_payload' => $result['raw']['response'] ?? $result['raw'],
                'status' => PaymentStatus::Processing,
            ])->save();

            $order->forceFill([
                'status' => 'awaiting_payment',
                'payment_method' => $paymentMethod,
            ])->save();

            return $transaction->fresh();
        });
    }

    public function markCompleted(PaymentTransaction $transaction, array $meta = []): void
    {
        DB::transaction(function () use ($transaction, $meta) {
            $locked = PaymentTransaction::query()
                ->whereKey($transaction->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status === PaymentStatus::Completed) {
                return;
            }

            $locked->forceFill([
                'status' => PaymentStatus::Completed,
                'paid_at' => now(),
                'provider_transaction_id' => $meta['provider_transaction_id'] ?? $locked->provider_transaction_id,
                'meta' => array_merge($locked->meta ?? [], $meta),
            ])->save();

            $subscriptionPayment = SubscriptionPayment::query()
                ->where('payment_transaction_id', $locked->id)
                ->lockForUpdate()
                ->first();

            if ($subscriptionPayment) {
                $subscriptionPayment->forceFill([
                    'status' => PaymentStatus::Completed,
                    'paid_at' => now(),
                    'receipt_number' => $subscriptionPayment->receipt_number
                        ?? $subscriptionPayment->generateReceiptNumber(),
                ])->save();
            }

            $payable = $locked->payable;

            if ($payable instanceof SubscriptionOrder) {
                $this->subscriptionService->activatePaidOrder($payable, $locked);
            }
        });
    }

    public function markFailed(PaymentTransaction $transaction, ?string $reason = null): void
    {
        DB::transaction(function () use ($transaction, $reason) {
            $locked = PaymentTransaction::query()
                ->whereKey($transaction->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status === PaymentStatus::Completed) {
                return;
            }

            $locked->forceFill([
                'status' => PaymentStatus::Failed,
                'failed_at' => now(),
                'failure_reason' => $reason,
            ])->save();

            SubscriptionPayment::query()
                ->where('payment_transaction_id', $locked->id)
                ->update([
                    'status' => PaymentStatus::Failed->value,
                ]);

            if ($locked->payable instanceof SubscriptionOrder) {
                $locked->payable->forceFill([
                    'status' => 'failed',
                ])->save();
            }
        });
    }

    public function recordWebhook(
        string $provider,
        array $payload,
        ?string $eventType,
        ?string $signature,
        bool $signatureValid,
        ?string $providerEventId = null,
    ): PaymentWebhookEvent {
        return PaymentWebhookEvent::query()->create([
            'provider' => $provider,
            'event_type' => $eventType,
            'provider_event_id' => $providerEventId,
            'signature' => $signature,
            'signature_valid' => $signatureValid,
            'status' => 'received',
            'payload' => $payload,
        ]);
    }

    public function handleJekoWebhook(PaymentWebhookEvent $event): void
    {
        if (! $event->signature_valid) {
            $event->forceFill([
                'status' => 'rejected',
                'processing_error' => 'Signature invalide',
                'processed_at' => now(),
            ])->save();

            return;
        }

        $payload = $event->payload;
        $reference = data_get($payload, 'transactionDetails.reference')
            ?? data_get($payload, 'reference');

        $status = data_get($payload, 'status');

        $transaction = PaymentTransaction::query()
            ->where('reference', $reference)
            ->orWhere('provider_payment_id', data_get($payload, 'transactionDetails.id'))
            ->first();

        if (! $transaction) {
            $event->forceFill([
                'status' => 'ignored',
                'processing_error' => 'Transaction introuvable',
                'processed_at' => now(),
            ])->save();

            return;
        }

        if ($status === 'success') {
            $this->markCompleted($transaction, [
                'provider_transaction_id' => data_get($payload, 'id'),
                'webhook_event_id' => $event->id,
                'payment_method' => data_get($payload, 'paymentMethod'),
            ]);
        } elseif ($status === 'error') {
            $this->markFailed($transaction, 'Webhook Jeko : error');
        }

        $event->forceFill([
            'status' => 'processed',
            'processed_at' => now(),
        ])->save();
    }
}
