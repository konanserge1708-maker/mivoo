<?php

namespace App\Services\Payments;

use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class JekoPaymentGateway implements PaymentGatewayInterface
{
    public function name(): string
    {
        return 'jeko';
    }

    public function initiate(PaymentTransaction $transaction, array $options): array
    {
        $config = config('payments.drivers.jeko');

        foreach (['api_key', 'api_key_id', 'store_id'] as $key) {
            if (empty($config[$key])) {
                throw new RuntimeException("Configuration Jeko manquante : {$key}.");
            }
        }

        $amountCents = $transaction->amount_xof * (int) $config['amount_multiplier'];

        if ($amountCents < 100 || $amountCents % 100 !== 0) {
            throw new RuntimeException('Montant Jeko invalide (amountCents).');
        }

        $payload = [
            'storeId' => $config['store_id'],
            'amountCents' => $amountCents,
            'currency' => $transaction->currency,
            'reference' => $transaction->reference,
            'paymentDetails' => [
                'type' => 'redirect',
                'data' => [
                    'paymentMethod' => $options['payment_method'],
                    'successUrl' => $options['success_url'],
                    'errorUrl' => $options['error_url'],
                ],
            ],
        ];

        $response = Http::baseUrl(rtrim($config['base_url'], '/'))
            ->withHeaders([
                'X-API-KEY' => $config['api_key'],
                'X-API-KEY-ID' => $config['api_key_id'],
                'Accept' => 'application/json',
            ])
            ->timeout(30)
            ->post('/partner_api/payment_requests', $payload);

        if (! $response->successful()) {
            throw new RuntimeException('Échec Jeko : '.$response->body());
        }

        $data = $response->json();

        if (empty($data['redirectUrl'])) {
            throw new RuntimeException('Réponse Jeko sans redirectUrl.');
        }

        return [
            'provider_payment_id' => $data['id'] ?? null,
            'redirect_url' => $data['redirectUrl'],
            'raw' => [
                'request' => $payload,
                'response' => $data,
            ],
        ];
    }

    public function verifyWebhookSignature(string $rawBody, ?string $signature): bool
    {
        $secret = (string) config('payments.drivers.jeko.webhook_secret');

        if ($secret === '' || $signature === null || $signature === '') {
            return false;
        }

        $expected = hash_hmac('sha256', $rawBody, $secret);

        return hash_equals($expected, strtolower($signature));
    }
}
