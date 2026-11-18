<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'price_xof',
        'duration_days',
        'daily_cap_xof',
        'indicative_task_value_xof',
        'max_tasks_per_day',
        'level',
        'benefits',
        'accessible_categories',
        'is_active',
        'is_default',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'benefits' => 'array',
            'accessible_categories' => 'array',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class);
    }

    public function audits(): HasMany
    {
        return $this->hasMany(SubscriptionPlanAudit::class);
    }

    public function formattedPrice(): string
    {
        return number_format($this->price_xof, 0, ',', ' ').' F';
    }

    /**
     * Gain par mission pour ce plan (modèle : plafond = missions × gain).
     */
    public function rewardPerTask(): int
    {
        return max(0, (int) $this->indicative_task_value_xof);
    }

    /**
     * Plafond journalier aligné : max tâches × gain / mission.
     */
    public function alignedDailyCap(): int
    {
        return max(0, (int) $this->max_tasks_per_day) * $this->rewardPerTask();
    }

    public function syncAlignedDailyCap(): void
    {
        $this->daily_cap_xof = $this->alignedDailyCap();
    }

    public function toneClass(): string
    {
        return match ((int) $this->level) {
            0 => 'vip-tone-free',
            1 => 'vip-tone-1',
            2 => 'vip-tone-2',
            3 => 'vip-tone-3',
            4 => 'vip-tone-4',
            5 => 'vip-tone-5',
            6 => 'vip-tone-6',
            default => 'vip-tone-7',
        };
    }

    public function materialLabel(): string
    {
        return match ((int) $this->level) {
            0 => 'Argent',
            1 => 'Bronze',
            2 => 'Bois',
            3 => 'Silver',
            4 => 'Or',
            5 => 'Platinum',
            6 => 'Diamant',
            default => 'Prestige',
        };
    }
}
