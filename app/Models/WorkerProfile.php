<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkerProfile extends Model
{
    protected $fillable = [
        'user_id',
        'tasks_completed_total',
        'tasks_completed_today',
        'tasks_counter_date',
        'daily_earnings_xof',
        'quality_score',
        'qualifications',
        'city',
        'country',
        'withdrawals_blocked',
        'withdrawals_under_examination',
        'max_withdrawal_xof',
        'withdrawal_block_reason',
    ];

    protected function casts(): array
    {
        return [
            'tasks_counter_date' => 'date',
            'qualifications' => 'array',
            'withdrawals_blocked' => 'boolean',
            'withdrawals_under_examination' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
