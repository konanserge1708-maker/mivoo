<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user()->load('partnerProfile');

        return view('partner.dashboard', [
            'user' => $user,
            'stats' => [
                'active_campaigns' => 0,
                'budget_committed' => '0 F',
                'budget_remaining' => '0 F',
                'tasks_ordered' => 0,
                'tasks_done' => 0,
                'tasks_validated' => 0,
            ],
        ]);
    }
}
