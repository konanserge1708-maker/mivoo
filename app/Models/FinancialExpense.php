<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialExpense extends Model
{
    protected $fillable = [
        'label', 'amount_xof', 'category', 'incurred_on', 'recorded_by', 'notes',
    ];

    protected function casts(): array
    {
        return ['incurred_on' => 'date'];
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
