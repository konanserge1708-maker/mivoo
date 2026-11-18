<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessJekoWebhookJob;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class JekoWebhookController extends Controller
{
    public function __invoke(Request $request, PaymentService $paymentService): Response
    {
        $rawBody = $request->getContent();
        $signature = $request->header('Jeko-Signature');
        $eventType = $request->header('Jeko-Event');
        $gateway = $paymentService->gateway('jeko');
        $valid = $gateway->verifyWebhookSignature($rawBody, $signature);
        $payload = json_decode($rawBody, true) ?? [];

        $event = $paymentService->recordWebhook(
            provider: 'jeko',
            payload: $payload,
            eventType: $eventType,
            signature: $signature,
            signatureValid: $valid,
            providerEventId: data_get($payload, 'id'),
        );

        if ($valid) {
            ProcessJekoWebhookJob::dispatch($event->id);
        }

        return response('OK', 200);
    }
}
