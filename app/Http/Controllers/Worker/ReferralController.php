<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use App\Models\ReferralCommission;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReferralController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user()->loadCount('referrals');
        $commissions = ReferralCommission::query()
            ->where('referrer_id', $user->id)
            ->latest()
            ->paginate(15);

        $total = (int) ReferralCommission::query()
            ->where('referrer_id', $user->id)
            ->sum('commission_xof');

        return view('worker.referrals', [
            'user' => $user,
            'commissions' => $commissions,
            'total' => Money::formatShort($total),
        ]);
    }
}
