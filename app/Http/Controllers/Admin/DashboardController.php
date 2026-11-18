<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CampaignStatus;
use App\Enums\TaskStatus;
use App\Enums\UserRole;
use App\Enums\WithdrawalStatus;
use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Role;
use App\Models\SubscriptionOrder;
use App\Models\TaskAssignment;
use App\Models\User;
use App\Models\UserSubscription;
use App\Models\WithdrawalRequest;
use App\Support\Money;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $workerRoleId = Role::query()->where('slug', UserRole::Worker->value)->value('id');

        $workersCount = $workerRoleId
            ? User::query()->whereHas('roles', fn ($q) => $q->where('roles.id', $workerRoleId))->count()
            : 0;

        $subscriptionRevenue = (int) SubscriptionOrder::query()->where('status', 'paid')->sum('amount_xof');
        $payouts = (int) WithdrawalRequest::query()->where('status', WithdrawalStatus::Completed->value)->sum('amount_xof');
        $taskSpend = (int) TaskAssignment::query()->where('status', TaskStatus::Approved->value)->sum('earned_xof');

        return view('admin.dashboard', [
            'stats' => [
                'workers' => $workersCount,
                'active_subscriptions' => UserSubscription::query()->where('status', 'active')->count(),
                'subscription_revenue' => Money::formatShort($subscriptionRevenue),
                'campaign_revenue' => Money::formatShort((int) Campaign::query()->where('funding_confirmed', true)->sum('budget_total_xof')),
                'payouts' => Money::formatShort($payouts),
                'pending_withdrawals' => WithdrawalRequest::query()->whereIn('status', [
                    WithdrawalStatus::Pending->value,
                    WithdrawalStatus::UnderReview->value,
                ])->count(),
                'active_campaigns' => Campaign::query()->where('status', CampaignStatus::Active->value)->count(),
                'tasks_pending_review' => TaskAssignment::query()->where('status', TaskStatus::UnderReview->value)->count(),
                'task_spend' => Money::formatShort($taskSpend),
            ],
        ]);
    }
}
