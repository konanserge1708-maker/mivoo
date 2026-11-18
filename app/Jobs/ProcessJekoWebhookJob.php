<?php

namespace App\Jobs;

use App\Models\PaymentWebhookEvent;
use App\Services\PaymentService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessJekoWebhookJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $webhookEventId) {}

    public function handle(PaymentService $paymentService): void
    {
        $event = PaymentWebhookEvent::query()->find($this->webhookEventId);

        if (! $event) {
            return;
        }

        $paymentService->handleJekoWebhook($event);
    }
}
