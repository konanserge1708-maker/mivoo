<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PaymentWebhookEvent extends Model
{
    protected $fillable = [
        'uuid',
        'provider',
        'event_type',
        'provider_event_id',
        'signature',
        'signature_valid',
        'status',
        'payload',
        'processing_error',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'signature_valid' => 'boolean',
            'processed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (PaymentWebhookEvent $event): void {
            $event->uuid ??= (string) Str::uuid();
        });
    }
}
