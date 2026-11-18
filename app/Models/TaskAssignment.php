<?php

namespace App\Models;

use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class TaskAssignment extends Model
{
    protected $fillable = [
        'uuid', 'campaign_id', 'user_id', 'status', 'reward_xof',
        'referral_reserve_xof', 'earned_xof', 'assigned_at', 'started_at',
        'submitted_at', 'reviewed_at', 'expires_at', 'reviewed_by',
        'review_notes', 'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'status' => TaskStatus::class,
            'assigned_at' => 'datetime',
            'started_at' => 'datetime',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (TaskAssignment $assignment): void {
            $assignment->uuid ??= (string) Str::uuid();
        });
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function submission(): HasOne
    {
        return $this->hasOne(TaskSubmission::class);
    }

    public function dispute(): HasOne
    {
        return $this->hasOne(TaskDispute::class);
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
