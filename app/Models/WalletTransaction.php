<?php

namespace App\Models;

use App\Enums\WalletTransactionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class WalletTransaction extends Model
{
    protected $fillable = [
        'uuid',
        'wallet_id',
        'user_id',
        'type',
        'amount_xof',
        'currency',
        'status',
        'reference',
        'idempotency_key',
        'related_type',
        'related_id',
        'description',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'type' => WalletTransactionType::class,
            'meta' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (WalletTransaction $transaction): void {
            $transaction->uuid ??= (string) Str::uuid();
        });
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function related(): MorphTo
    {
        return $this->morphTo();
    }
}
