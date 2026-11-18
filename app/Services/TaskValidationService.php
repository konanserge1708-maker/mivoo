<?php

namespace App\Services;

use App\Enums\TaskStatus;
use App\Models\TaskAssignment;
use App\Models\TaskDispute;
use App\Models\TaskSubmission;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TaskValidationService
{
    public function __construct(
        private readonly EarningsService $earnings,
        private readonly TaskAssignmentService $assignments,
    ) {}

    public function submit(TaskAssignment $assignment, User $user, array $data): TaskAssignment
    {
        if ($assignment->user_id !== $user->id) {
            abort(403);
        }

        if (! in_array($assignment->status, [TaskStatus::Assigned, TaskStatus::InProgress], true)) {
            throw new InvalidArgumentException('Soumission impossible pour ce statut.');
        }

        return DB::transaction(function () use ($assignment, $user, $data) {
            TaskSubmission::query()->updateOrCreate(
                ['task_assignment_id' => $assignment->id],
                [
                    'user_id' => $user->id,
                    'text_response' => $data['text_response'] ?? null,
                    'screenshot_path' => $data['screenshot_path'] ?? null,
                    'questionnaire_answers' => $data['questionnaire_answers'] ?? null,
                    'meta' => $data['meta'] ?? null,
                ]
            );

            $assignment->forceFill([
                'status' => TaskStatus::UnderReview,
                'submitted_at' => now(),
                'started_at' => $assignment->started_at ?? now(),
            ])->save();

            return $assignment->fresh(['campaign', 'submission']);
        });
    }

    public function approve(TaskAssignment $assignment, User $admin, ?string $notes = null): TaskAssignment
    {
        return DB::transaction(function () use ($assignment, $admin, $notes) {
            $locked = TaskAssignment::query()->whereKey($assignment->id)->lockForUpdate()->firstOrFail();

            if ($locked->status === TaskStatus::Approved) {
                return $locked;
            }

            if (! in_array($locked->status, [TaskStatus::UnderReview, TaskStatus::Submitted, TaskStatus::Disputed], true)) {
                throw new InvalidArgumentException('Cette mission ne peut pas être approuvée.');
            }

            $locked->forceFill([
                'status' => TaskStatus::Approved,
                'reviewed_at' => now(),
                'reviewed_by' => $admin->id,
                'review_notes' => $notes,
            ])->save();

            $this->earnings->creditForApprovedTask($locked);

            return $locked->fresh(['campaign', 'user', 'submission']);
        });
    }

    public function reject(TaskAssignment $assignment, User $admin, string $reason): TaskAssignment
    {
        return DB::transaction(function () use ($assignment, $admin, $reason) {
            $locked = TaskAssignment::query()->whereKey($assignment->id)->lockForUpdate()->firstOrFail();

            if (! in_array($locked->status, [TaskStatus::UnderReview, TaskStatus::Submitted, TaskStatus::Disputed], true)) {
                throw new InvalidArgumentException('Cette mission ne peut pas être rejetée.');
            }

            $locked->forceFill([
                'status' => TaskStatus::Rejected,
                'reviewed_at' => now(),
                'reviewed_by' => $admin->id,
                'rejection_reason' => $reason,
            ])->save();

            $this->assignments->releaseReservation($locked);

            return $locked->fresh();
        });
    }

    public function dispute(TaskAssignment $assignment, User $user, string $reason): TaskDispute
    {
        if ($assignment->user_id !== $user->id) {
            abort(403);
        }

        if ($assignment->status !== TaskStatus::Rejected) {
            throw new InvalidArgumentException('Seules les missions rejetées peuvent être contestées.');
        }

        return DB::transaction(function () use ($assignment, $user, $reason) {
            $dispute = TaskDispute::query()->create([
                'task_assignment_id' => $assignment->id,
                'user_id' => $user->id,
                'reason' => $reason,
                'status' => 'open',
            ]);

            $assignment->forceFill(['status' => TaskStatus::Disputed])->save();

            return $dispute;
        });
    }
}
