<?php

namespace App\Models;

use App\Enums\SubscriptionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class UserSubscription extends Model
{
    protected $fillable = [
        'uuid',
        'user_id',
        'subscription_plan_id',
        'status',
        'starts_at',
        'ends_at',
        'price_paid_xof',
        'plan_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'status' => SubscriptionStatus::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'plan_snapshot' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (UserSubscription $subscription): void {
            $subscription->uuid ??= (string) Str::uuid();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function isActive(): bool
    {
        return $this->status === SubscriptionStatus::Active
            && $this->ends_at !== null
            && $this->ends_at->isFuture();
    }
}
