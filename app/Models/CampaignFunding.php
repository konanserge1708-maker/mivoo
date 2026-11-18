<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignFunding extends Model
{
    protected $fillable = [
        'campaign_id', 'recorded_by', 'amount_xof', 'method', 'reference', 'notes',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }
}
