<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Models\TaskAssignment;
use App\Services\TaskValidationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskReviewController extends Controller
{
    public function index(): View
    {
        $tasks = TaskAssignment::query()
            ->with(['campaign', 'user', 'submission'])
            ->whereIn('status', [TaskStatus::UnderReview->value, TaskStatus::Disputed->value])
            ->latest('submitted_at')
            ->paginate(20);

        return view('admin.tasks.index', compact('tasks'));
    }

    public function show(TaskAssignment $task): View
    {
        $task->load(['campaign', 'user', 'submission', 'dispute']);

        return view('admin.tasks.show', compact('task'));
    }

    public function approve(TaskAssignment $task, Request $request, TaskValidationService $validation): RedirectResponse
    {
        try {
            $validation->approve($task, $request->user(), $request->string('notes')->toString() ?: null);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['task' => $e->getMessage()]);
        }

        return back()->with('status', 'Mission approuvée et gains crédités.');
    }

    public function reject(TaskAssignment $task, Request $request, TaskValidationService $validation): RedirectResponse
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:1000']]);

        try {
            $validation->reject($task, $request->user(), $data['reason']);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['task' => $e->getMessage()]);
        }

        return back()->with('status', 'Mission rejetée.');
    }
}
