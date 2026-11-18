<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSubscriptionPlanRequest;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionPlanAudit;
use App\Services\TaskAssignmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class SubscriptionPlanController extends Controller
{
    public function index(): View
    {
        $plans = SubscriptionPlan::query()->orderBy('sort_order')->get();

        return view('admin.subscription-plans.index', compact('plans'));
    }

    public function edit(SubscriptionPlan $plan): View
    {
        $audits = SubscriptionPlanAudit::query()
            ->with('user')
            ->where('subscription_plan_id', $plan->id)
            ->latest()
            ->limit(20)
            ->get();

        return view('admin.subscription-plans.edit', [
            'plan' => $plan,
            'audits' => $audits,
            'hardMaxTasks' => TaskAssignmentService::HARD_DAILY_MAX,
        ]);
    }

    public function update(
        UpdateSubscriptionPlanRequest $request,
        SubscriptionPlan $plan,
    ): RedirectResponse {
        $data = $request->validated();
        $old = $plan->only([
            'name', 'price_xof', 'duration_days', 'daily_cap_xof',
            'indicative_task_value_xof', 'max_tasks_per_day', 'is_active', 'sort_order', 'benefits',
        ]);

        $benefits = collect(preg_split('/\r\n|\r|\n/', (string) ($data['benefits_text'] ?? '')))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();

        $maxTasks = min(
            TaskAssignmentService::HARD_DAILY_MAX,
            max(1, (int) $data['max_tasks_per_day'])
        );
        $reward = max(0, (int) $data['indicative_task_value_xof']);
        $dailyCap = $maxTasks * $reward;

        if ($benefits === []) {
            $benefits = [
                $dailyCap.' F CFA max / jour',
                $maxTasks.' missions à '.$reward.' F',
                'Accès aux missions du pool',
            ];
        }

        $plan->fill([
            'name' => $data['name'],
            'price_xof' => $data['price_xof'],
            'duration_days' => $data['duration_days'],
            'indicative_task_value_xof' => $reward,
            'max_tasks_per_day' => $maxTasks,
            'daily_cap_xof' => $dailyCap,
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $data['sort_order'],
            'benefits' => $benefits,
        ]);

        $plan->save();

        SubscriptionPlanAudit::query()->create([
            'subscription_plan_id' => $plan->id,
            'user_id' => $request->user()->id,
            'old_values' => $old,
            'new_values' => $plan->only(array_keys($old)),
            'action' => 'updated',
        ]);

        Cache::flush();

        return redirect()
            ->route('admin.subscription-plans.edit', $plan)
            ->with('status', 'Plan aligné : '.$dailyCap.' F/jour = '.$maxTasks.' × '.$reward.' F. Les abonnements déjà actifs conservent leur snapshot.');
    }
}
