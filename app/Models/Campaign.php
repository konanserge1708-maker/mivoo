<?php

namespace App\Models;

use App\Enums\CampaignStatus;
use App\Enums\MissionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Campaign extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid', 'created_by', 'partner_user_id', 'campaign_category_id',
        'title', 'description', 'instructions', 'validation_criteria',
        'evidence_type', 'target_url', 'status', 'tasks_ordered',
        'tasks_assigned', 'tasks_completed', 'tasks_approved',
        'reward_per_task_xof', 'budget_total_xof', 'budget_reserved_xof',
        'budget_spent_xof', 'budget_available_xof', 'funding_confirmed',
        'funding_confirmed_at', 'min_vip_level', 'max_participants',
        'allow_repeat', 'completion_minutes', 'country', 'starts_at', 'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => CampaignStatus::class,
            'funding_confirmed' => 'boolean',
            'funding_confirmed_at' => 'datetime',
            'allow_repeat' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Campaign $campaign): void {
            $campaign->uuid ??= (string) Str::uuid();
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CampaignCategory::class, 'campaign_category_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(TaskAssignment::class);
    }

    public function fundings(): HasMany
    {
        return $this->hasMany(CampaignFunding::class);
    }

    public function isAssignable(): bool
    {
        // Pool vrac : une mission active reste disponible jusqu’à suppression admin.
        return $this->status === CampaignStatus::Active
            && $this->funding_confirmed
            && ($this->starts_at === null || $this->starts_at->isPast());
    }

    public function missionType(): ?MissionType
    {
        return MissionType::tryFrom((string) $this->evidence_type);
    }

    public function isConfirmationMission(): bool
    {
        return $this->missionType()?->isConfirmation() ?? false;
    }
}
