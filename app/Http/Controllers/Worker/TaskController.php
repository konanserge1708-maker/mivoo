<?php

namespace App\Http\Controllers\Worker;

use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Models\TaskAssignment;
use App\Models\TaskSubmission;
use App\Services\TaskAssignmentService;
use App\Services\TaskValidationService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(Request $request, TaskAssignmentService $assignments): View
    {
        $filter = $request->string('filter', 'all')->toString();
        $user = $request->user();

        $query = TaskAssignment::query()
            ->with('campaign.category')
            ->where('user_id', $user->id)
            ->latest();

        if ($filter === 'in_progress') {
            $query->whereIn('status', [
                TaskStatus::Assigned->value,
                TaskStatus::InProgress->value,
                TaskStatus::UnderReview->value,
            ]);
        } elseif ($filter === 'completed') {
            $query->whereIn('status', [
                TaskStatus::Approved->value,
                TaskStatus::Rejected->value,
            ]);
        } elseif ($filter === 'today') {
            $query->whereDate('assigned_at', now('Africa/Abidjan')->toDateString());
        }

        $mode = (string) SystemSetting::getValue('daily_missions_mode', 'fixed');

        return view('worker.tasks.index', [
            'tasks' => $query->paginate(12),
            'filter' => $filter,
            'hasDrawnToday' => $assignments->hasDrawnToday($user),
            'todaysCount' => $assignments->todaysDrawnCount($user),
            'dailyQuotaPreview' => $mode === 'random'
                ? 'aléatoire (max '.TaskAssignmentService::HARD_DAILY_MAX.')'
                : (string) min(
                    TaskAssignmentService::HARD_DAILY_MAX,
                    max(1, (int) SystemSetting::getValue('daily_missions_count', 10))
                ),
        ]);
    }

    public function claim(Request $request, TaskAssignmentService $assignments): RedirectResponse
    {
        try {
            $batch = $assignments->drawDailyBatch($request->user());
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['task' => $e->getMessage()]);
        }

        $count = $batch->count();

        return redirect()
            ->route('worker.tasks.index', ['filter' => 'today'])
            ->with('status', $count === 1
                ? '1 mission tirée pour aujourd’hui.'
                : "{$count} missions tirées pour aujourd’hui.");
    }

    public function show(Request $request, TaskAssignment $task): View
    {
        abort_unless($task->user_id === $request->user()->id, 403);
        $task->load(['campaign.category', 'submission']);

        $minDwell = (int) SystemSetting::getValue('mission_min_dwell_seconds', 12);
        $openedAtRaw = data_get($task->submission?->meta, 'opened_at');
        $openedAt = $openedAtRaw ? Carbon::parse($openedAtRaw) : null;
        $elapsed = $openedAt ? $openedAt->diffInSeconds(now()) : 0;
        $remaining = $openedAt ? max(0, $minDwell - $elapsed) : $minDwell;
        $canConfirm = $openedAt !== null && $remaining === 0;

        return view('worker.tasks.show', [
            'task' => $task,
            'missionType' => $task->campaign->missionType(),
            'linkOpened' => $openedAt !== null,
            'minDwellSeconds' => $minDwell,
            'remainingSeconds' => $remaining,
            'canConfirm' => $canConfirm,
            'openedAtIso' => $openedAt?->toIso8601String(),
        ]);
    }

    public function start(Request $request, TaskAssignment $task, TaskAssignmentService $assignments): RedirectResponse
    {
        try {
            $assignments->start($task, $request->user());
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['task' => $e->getMessage()]);
        }

        return back()->with('status', 'Mission démarrée.');
    }

    public function open(Request $request, TaskAssignment $task, TaskAssignmentService $assignments): RedirectResponse
    {
        abort_unless($task->user_id === $request->user()->id, 403);

        $campaign = $task->campaign;
        if (! $campaign->target_url) {
            return back()->withErrors(['task' => 'Aucun lien cible configuré pour cette mission.']);
        }

        if (in_array($task->status, [TaskStatus::Assigned, TaskStatus::InProgress], true)) {
            try {
                if ($task->status === TaskStatus::Assigned) {
                    $assignments->start($task, $request->user());
                    $task->refresh();
                }
            } catch (\InvalidArgumentException $e) {
                return back()->withErrors(['task' => $e->getMessage()]);
            }

            $submission = TaskSubmission::query()->firstOrNew([
                'task_assignment_id' => $task->id,
            ]);
            $meta = $submission->meta ?? [];
            // On conserve la 1re ouverture pour ne pas reset le délai.
            $meta['opened_at'] = $meta['opened_at'] ?? now()->toIso8601String();
            $meta['target_url'] = $campaign->target_url;
            $meta['open_count'] = ((int) ($meta['open_count'] ?? 0)) + 1;
            $submission->fill([
                'user_id' => $request->user()->id,
                'meta' => $meta,
            ])->save();
        }

        return redirect()->away($campaign->target_url);
    }

    public function submit(Request $request, TaskAssignment $task, TaskValidationService $validation): RedirectResponse
    {
        abort_unless($task->user_id === $request->user()->id, 403);

        $campaign = $task->campaign()->with('category')->first();
        $missionType = $campaign?->missionType();

        if ($missionType?->isConfirmation()) {
            $request->validate([
                'confirmed' => ['accepted'],
            ], [
                'confirmed.accepted' => 'Cochez la case de confirmation pour valider la mission.',
            ]);

            $existingMeta = $task->submission?->meta ?? [];
            if (empty($existingMeta['opened_at'])) {
                return back()->withErrors(['task' => 'Ouvrez d’abord le lien de la mission, puis confirmez.']);
            }

            $minDwell = (int) SystemSetting::getValue('mission_min_dwell_seconds', 12);
            $openedAt = Carbon::parse($existingMeta['opened_at']);
            $elapsed = $openedAt->diffInSeconds(now());

            if ($elapsed < $minDwell) {
                $wait = $minDwell - $elapsed;

                return back()->withErrors([
                    'task' => "Patientez encore {$wait} s après l’ouverture du lien avant de confirmer.",
                ]);
            }

            $payload = [
                'text_response' => $missionType->confirmationLabel(),
                'meta' => array_merge($existingMeta, [
                    'confirmed' => true,
                    'confirmed_at' => now()->toIso8601String(),
                    'dwell_seconds' => $elapsed,
                    'min_dwell_seconds' => $minDwell,
                    'mission_type' => $missionType->value,
                    'confirmation_label' => $missionType->confirmationLabel(),
                ]),
            ];
        } else {
            $data = $request->validate([
                'text_response' => ['required', 'string', 'min:10', 'max:5000'],
            ]);
            $payload = $data;
        }

        try {
            $validation->submit($task, $request->user(), $payload);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['task' => $e->getMessage()]);
        }

        return redirect()
            ->route('worker.tasks.index')
            ->with('status', 'Mission soumise. En attente de validation.');
    }

    public function dispute(Request $request, TaskAssignment $task, TaskValidationService $validation): RedirectResponse
    {
        $data = $request->validate(['reason' => ['required', 'string', 'min:10', 'max:2000']]);

        try {
            $validation->dispute($task, $request->user(), $data['reason']);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['task' => $e->getMessage()]);
        }

        return back()->with('status', 'Contestation envoyée.');
    }
}
