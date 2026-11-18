<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Models\WalletTransaction;
use App\Models\WithdrawalRequest;
use App\Services\WithdrawalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WalletController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user()->load(['wallet', 'workerProfile']);
        $profile = $user->workerProfile;
        $globalMax = (int) SystemSetting::getValue('max_withdrawal_xof', 0);
        $userMax = $profile?->max_withdrawal_xof;
        $effectiveMax = null;

        if ($userMax && $userMax > 0) {
            $effectiveMax = (int) $userMax;
        } elseif ($globalMax > 0) {
            $effectiveMax = $globalMax;
        }

        $transactions = WalletTransaction::query()
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(15);

        return view('worker.wallet', [
            'user' => $user,
            'transactions' => $transactions,
            'minWithdrawal' => (int) SystemSetting::getValue('min_withdrawal_xof', 1000),
            'maxWithdrawal' => $effectiveMax,
            'withdrawalsEnabled' => (bool) SystemSetting::getValue('withdrawals_enabled', true),
            'withdrawalsBlocked' => (bool) ($profile?->withdrawals_blocked),
            'withdrawalsUnderExamination' => (bool) ($profile?->withdrawals_under_examination),
            'withdrawalBlockReason' => $profile?->withdrawal_block_reason,
            'methods' => config('payments.methods'),
        ]);
    }

    public function withdraw(Request $request, WithdrawalService $withdrawals): RedirectResponse
    {
        $data = $request->validate([
            'amount_xof' => ['required', 'integer', 'min:1000'],
            'method' => ['required', 'in:'.implode(',', array_keys(config('payments.methods')))],
            'destination_phone' => ['required', 'string', 'max:30'],
        ]);

        try {
            $requestItem = $withdrawals->request(
                $request->user(),
                (int) $data['amount_xof'],
                $data['method'],
                $data['destination_phone'],
            );
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['amount_xof' => $e->getMessage()]);
        }

        $message = $requestItem->status->value === 'under_review'
            ? 'Demande enregistrée et placée en examen. Un administrateur la traitera manuellement.'
            : 'Demande de retrait enregistrée.';

        return back()->with('status', $message);
    }

    public function withdrawals(Request $request): View
    {
        $items = WithdrawalRequest::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        return view('worker.withdrawals', compact('items'));
    }
}
