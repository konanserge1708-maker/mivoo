<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class PaymentTransaction extends Model
{
    protected $fillable = [
        'uuid',
        'reference',
        'user_id',
        'payable_type',
        'payable_id',
        'provider',
        'payment_method',
        'status',
        'amount_xof',
        'currency',
        'provider_payment_id',
        'provider_transaction_id',
        'redirect_url',
        'idempotency_key',
        'request_payload',
        'response_payload',
        'meta',
        'paid_at',
        'failed_at',
        'failure_reason',
    ];

    protected function casts(): array
    {
        return [
            'status' => PaymentStatus::class,
            'request_payload' => 'array',
            'response_payload' => 'array',
            'meta' => 'array',
            'paid_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (PaymentTransaction $transaction): void {
            $transaction->uuid ??= (string) Str::uuid();
            $transaction->reference ??= 'PAY-'.strtoupper(Str::random(12));
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }
}
