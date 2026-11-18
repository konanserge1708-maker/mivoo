<?php

namespace App\Services\Payments;

use App\Models\PaymentTransaction;
use Illuminate\Support\Str;

/**
 * Simulateur local clairement séparé de Jeko live.
 * Jeko n'offre pas de sandbox officiel.
 */
class SandboxPaymentGateway implements PaymentGatewayInterface
{
    public function name(): string
    {
        return 'sandbox';
    }

    public function initiate(PaymentTransaction $transaction, array $options): array
    {
        $providerPaymentId = 'sandbox_'.Str::uuid()->toString();

        return [
            'provider_payment_id' => $providerPaymentId,
            'redirect_url' => route('payments.sandbox.show', $transaction->uuid),
            'raw' => [
                'mode' => 'sandbox',
                'notice' => 'Simulation locale — aucun appel Jeko.',
                'options' => $options,
            ],
        ];
    }

    public function verifyWebhookSignature(string $rawBody, ?string $signature): bool
    {
        $expected = hash_hmac('sha256', $rawBody, (string) config('app.key'));

        return $signature !== null && hash_equals($expected, $signature);
    }
}
