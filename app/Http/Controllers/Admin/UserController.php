<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AccountStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WorkerProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->with(['roles', 'activeSubscription.plan', 'wallet', 'workerProfile'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->string('q').'%';
                $q->where(function ($inner) use ($term) {
                    $inner->where('email', 'like', $term)
                        ->orWhere('phone', 'like', $term)
                        ->orWhere('first_name', 'like', $term)
                        ->orWhere('last_name', 'like', $term);
                });
            })
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user): View
    {
        $user->load(['roles', 'wallet', 'workerProfile', 'partnerProfile', 'activeSubscription.plan', 'subscriptions.plan']);

        return view('admin.users.show', compact('user'));
    }

    public function suspend(User $user): RedirectResponse
    {
        $user->forceFill(['status' => AccountStatus::Suspended])->save();

        return back()->with('status', 'Compte suspendu.');
    }

    public function reactivate(User $user): RedirectResponse
    {
        $user->forceFill(['status' => AccountStatus::Active])->save();

        return back()->with('status', 'Compte réactivé.');
    }

    public function blockWithdrawals(User $user, Request $request): RedirectResponse
    {
        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $profile = $this->ensureWorkerProfile($user);
        $profile->forceFill([
            'withdrawals_blocked' => true,
            'withdrawal_block_reason' => $data['reason'] ?? null,
        ])->save();

        return back()->with('status', 'Retraits bloqués pour ce compte.');
    }

    public function unblockWithdrawals(User $user): RedirectResponse
    {
        $profile = $this->ensureWorkerProfile($user);
        $profile->forceFill([
            'withdrawals_blocked' => false,
            'withdrawal_block_reason' => null,
        ])->save();

        return back()->with('status', 'Retraits débloqués pour ce compte.');
    }

    public function setWithdrawalsExamination(User $user, Request $request): RedirectResponse
    {
        $data = $request->validate([
            'enabled' => ['required', 'boolean'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $profile = $this->ensureWorkerProfile($user);
        $enabled = $request->boolean('enabled');

        $profile->forceFill([
            'withdrawals_under_examination' => $enabled,
            'withdrawal_block_reason' => $enabled
                ? ($data['reason'] ?? $profile->withdrawal_block_reason)
                : ($profile->withdrawals_blocked ? $profile->withdrawal_block_reason : null),
        ])->save();

        return back()->with(
            'status',
            $enabled
                ? 'Compte placé en examen : les prochains retraits nécessiteront une validation manuelle.'
                : 'Examen des retraits levé pour ce compte.'
        );
    }

    public function updateWithdrawalLimit(User $user, Request $request): RedirectResponse
    {
        $data = $request->validate([
            'max_withdrawal_xof' => ['nullable', 'integer', 'min:0'],
        ]);

        $profile = $this->ensureWorkerProfile($user);
        $max = $data['max_withdrawal_xof'] ?? null;
        $profile->forceFill([
            'max_withdrawal_xof' => ($max !== null && (int) $max > 0) ? (int) $max : null,
        ])->save();

        return back()->with(
            'status',
            $profile->max_withdrawal_xof
                ? 'Limite de retrait fixée à '.number_format($profile->max_withdrawal_xof, 0, ',', ' ').' F.'
                : 'Limite de retrait spécifique retirée.'
        );
    }

    private function ensureWorkerProfile(User $user): WorkerProfile
    {
        return WorkerProfile::query()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'country' => 'CI',
                'tasks_completed_total' => 0,
                'tasks_completed_today' => 0,
                'daily_earnings_xof' => 0,
                'quality_score' => 100,
            ]
        );
    }
}
