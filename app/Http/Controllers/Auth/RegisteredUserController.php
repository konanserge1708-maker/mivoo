<?php

namespace App\Http\Controllers\Auth;

use App\Enums\AccountStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterWorkerRequest;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use App\Services\ReferralService;
use App\Services\WalletService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(
        RegisterWorkerRequest $request,
        ReferralService $referralService,
        WalletService $walletService,
    ): RedirectResponse {
        $data = $request->validated();
        $referrer = $referralService->findReferrer($data['referral_code'] ?? null);

        $user = DB::transaction(function () use ($data, $referrer, $walletService) {
            $user = User::query()->create([
                'last_name' => $data['last_name'],
                'first_name' => $data['first_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => $data['password'],
                'referred_by' => $referrer?->id,
                'status' => AccountStatus::Active,
                'terms_accepted' => true,
                'terms_accepted_at' => now(),
            ]);

            $user->assignRole(UserRole::Worker);

            $user->workerProfile()->create([
                'country' => 'CI',
            ]);

            $walletService->createForUser($user);

            $freePlan = SubscriptionPlan::query()
                ->where(function ($query) {
                    $query->where('is_default', true)
                        ->orWhere('slug', 'gratuit');
                })
                ->first();

            if ($freePlan) {
                UserSubscription::query()->create([
                    'user_id' => $user->id,
                    'subscription_plan_id' => $freePlan->id,
                    'status' => 'active',
                    'starts_at' => now(),
                    'ends_at' => now()->addYears(10),
                    'price_paid_xof' => 0,
                    'plan_snapshot' => $freePlan->toArray(),
                ]);
            }

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('worker.dashboard');
    }
}
