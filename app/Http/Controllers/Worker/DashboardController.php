<?php

namespace App\Http\Controllers\Worker;

use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Models\TaskAssignment;
use App\Services\TaskAssignmentService;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, TaskAssignmentService $assignmentService): View
    {
        $user = $request->user()->load(['wallet', 'workerProfile', 'activeSubscription.plan']);
        $profile = $assignmentService->refreshDailyCounters($user);
        $hasDrawnToday = $assignmentService->hasDrawnToday($user);
        $todaysCount = $assignmentService->todaysDrawnCount($user);
        $quotaPreview = $assignmentService->resolveDailyQuota($user);
        $completedToday = $profile->tasks_completed_today;
        $wallet = $user->wallet;
        $subscription = $user->activeSubscription;
        $plan = $subscription?->plan;

        $upcomingTasks = TaskAssignment::query()
            ->with('campaign.category')
            ->where('user_id', $user->id)
            ->whereIn('status', [
                TaskStatus::Assigned->value,
                TaskStatus::InProgress->value,
                TaskStatus::UnderReview->value,
            ])
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn (TaskAssignment $task) => [
                'id' => $task->id,
                'uuid' => $task->uuid,
                'title' => $task->campaign->title,
                'time' => $task->assigned_at?->timezone('Africa/Abidjan')->format('H:i') ?? '—',
                'meta' => $task->campaign->missionType()?->label() ?? $task->campaign->category->name ?? 'Mission',
                'reward' => $task->reward_xof,
                'tag' => $task->status->label(),
            ]);

        return view('worker.dashboard', [
            'user' => $user,
            'plan' => $plan,
            'subscription' => $subscription,
            'wallet' => $wallet,
            'hasDrawnToday' => $hasDrawnToday,
            'stats' => [
                'today_tasks' => $completedToday,
                'remaining_tasks' => $hasDrawnToday
                    ? max(0, $todaysCount - $completedToday)
                    : $quotaPreview,
                'todays_drawn' => $todaysCount,
                'available_balance' => Money::formatShort($wallet?->available_balance_xof ?? 0),
                'pending_balance' => Money::formatShort($wallet?->pending_balance_xof ?? 0),
                'daily_earnings' => Money::formatShort($profile->daily_earnings_xof ?? 0),
                'lifetime_earned' => Money::formatShort($wallet?->lifetime_earned_xof ?? 0),
                'vip_label' => $plan?->name ?? 'Gratuit',
                'vip_expires' => $subscription?->ends_at,
            ],
            'upcomingTasks' => $upcomingTasks,
        ]);
    }
}
