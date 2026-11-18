<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignBudgetReservation extends Model
{
    protected $fillable = [
        'campaign_id', 'task_assignment_id', 'amount_xof', 'status', 'purpose',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }
}
