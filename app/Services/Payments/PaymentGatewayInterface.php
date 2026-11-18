<?php

namespace App\Services\Payments;

use App\Models\PaymentTransaction;

interface PaymentGatewayInterface
{
    public function name(): string;

    /**
     * @param  array{success_url:string,error_url:string,payment_method:string,description?:string}  $options
     * @return array{provider_payment_id:?string,redirect_url:string,raw:array}
     */
    public function initiate(PaymentTransaction $transaction, array $options): array;

    public function verifyWebhookSignature(string $rawBody, ?string $signature): bool;
}
