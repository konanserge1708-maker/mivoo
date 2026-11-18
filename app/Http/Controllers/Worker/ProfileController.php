<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user()->load([
            'wallet',
            'workerProfile',
            'activeSubscription.plan',
            'referrals',
        ]);

        $plan = $user->activeSubscription?->plan;
        $subscription = $user->activeSubscription;

        return view('worker.profile', [
            'user' => $user,
            'plan' => $plan,
            'subscription' => $subscription,
            'stats' => [
                'available' => Money::formatShort($user->wallet?->available_balance_xof ?? 0),
                'pending' => Money::formatShort($user->wallet?->pending_balance_xof ?? 0),
                'lifetime' => Money::formatShort($user->wallet?->lifetime_earned_xof ?? 0),
                'tasks_total' => $user->workerProfile?->tasks_completed_total ?? 0,
                'tasks_today' => $user->workerProfile?->tasks_completed_today ?? 0,
                'quality' => $user->workerProfile?->quality_score ?? 100,
                'referrals' => $user->referrals->count(),
            ],
        ]);
    }
}
