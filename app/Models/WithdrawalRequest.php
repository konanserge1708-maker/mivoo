<?php

namespace App\Models;

use App\Enums\WithdrawalStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class WithdrawalRequest extends Model
{
    protected $fillable = [
        'uuid', 'reference', 'user_id', 'amount_xof', 'fee_xof', 'net_amount_xof',
        'method', 'destination_phone', 'status', 'provider', 'provider_transfer_id',
        'reviewed_by', 'admin_notes', 'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => WithdrawalStatus::class,
            'processed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (WithdrawalRequest $request): void {
            $request->uuid ??= (string) Str::uuid();
            $request->reference ??= 'WDR-'.strtoupper(Str::random(10));
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
