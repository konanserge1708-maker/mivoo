<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class LedgerEntry extends Model
{
    protected $fillable = [
        'uuid', 'idempotency_key', 'debit_account_id', 'credit_account_id',
        'amount_xof', 'currency', 'description', 'related_type', 'related_id',
        'user_id', 'meta',
    ];

    protected function casts(): array
    {
        return ['meta' => 'array'];
    }

    protected static function booted(): void
    {
        static::creating(function (LedgerEntry $entry): void {
            $entry->uuid ??= (string) Str::uuid();
        });
    }

    public function related(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
