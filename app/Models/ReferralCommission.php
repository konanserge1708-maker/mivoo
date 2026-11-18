<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ReferralCommission extends Model
{
    protected $fillable = [
        'uuid', 'referrer_id', 'referred_id', 'task_assignment_id',
        'base_amount_xof', 'commission_xof', 'rate_percent', 'status',
    ];

    protected static function booted(): void
    {
        static::creating(function (ReferralCommission $commission): void {
            $commission->uuid ??= (string) Str::uuid();
        });
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referred(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_id');
    }
}
