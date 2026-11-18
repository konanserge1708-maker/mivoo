<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskSubmission extends Model
{
    protected $fillable = [
        'task_assignment_id', 'user_id', 'text_response',
        'screenshot_path', 'questionnaire_answers', 'meta',
    ];

    protected function casts(): array
    {
        return [
            'questionnaire_answers' => 'array',
            'meta' => 'array',
        ];
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(TaskAssignment::class, 'task_assignment_id');
    }
}
