<?php

namespace Tests\Feature;

use App\Enums\TaskStatus;
use App\Models\Campaign;
use App\Models\TaskAssignment;
use App\Models\User;
use App\Services\TaskAssignmentService;
use App\Services\TaskValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskEarningsFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_worker_can_complete_task_and_receive_earnings(): void
    {
        $worker = User::query()->where('email', 'worker@mivoo.test')->firstOrFail();
        $admin = User::query()->where('email', 'admin@mivoo.test')->firstOrFail();

        $this->assertTrue(Campaign::query()->where('status', 'active')->exists());

        $batch = app(TaskAssignmentService::class)->drawDailyBatch($worker);
        $this->assertNotEmpty($batch);

        $assignment = $batch->first();
        $this->assertSame(TaskStatus::Assigned, $assignment->status);

        app(TaskAssignmentService::class)->start($assignment, $worker);

        $missionType = $assignment->campaign->missionType();
        if ($missionType) {
            \App\Models\TaskSubmission::query()->updateOrCreate(
                ['task_assignment_id' => $assignment->id],
                [
                    'user_id' => $worker->id,
                    'meta' => [
                        'opened_at' => now()->subMinutes(1)->toIso8601String(),
                    ],
                ]
            );
            app(TaskValidationService::class)->submit($assignment->fresh(), $worker, [
                'text_response' => $missionType->confirmationLabel(),
                'meta' => [
                    'opened_at' => now()->subMinutes(1)->toIso8601String(),
                    'confirmed' => true,
                ],
            ]);
        } else {
            app(TaskValidationService::class)->submit($assignment->fresh(), $worker, [
                'text_response' => 'J’ai testé la page et vérifié les niveaux VIP affichés.',
            ]);
        }

        app(TaskValidationService::class)->approve($assignment->fresh(), $admin, 'OK');

        $assignment->refresh();
        $this->assertSame(TaskStatus::Approved, $assignment->status);
        $this->assertGreaterThan(0, $assignment->earned_xof);
        $this->assertGreaterThan(0, $worker->fresh()->wallet->available_balance_xof);
    }

    public function test_daily_draw_can_only_happen_once(): void
    {
        $worker = User::query()->where('email', 'worker@mivoo.test')->firstOrFail();
        $service = app(TaskAssignmentService::class);

        $first = $service->drawDailyBatch($worker);
        $this->assertNotEmpty($first);

        $this->expectException(\InvalidArgumentException::class);
        $service->drawDailyBatch($worker->fresh());
    }
}
