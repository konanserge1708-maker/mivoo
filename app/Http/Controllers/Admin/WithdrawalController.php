<?php

namespace App\Http\Controllers\Admin;

use App\Enums\WithdrawalStatus;
use App\Http\Controllers\Controller;
use App\Models\WithdrawalRequest;
use App\Services\WithdrawalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WithdrawalController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->string('status')->toString();

        $withdrawals = WithdrawalRequest::query()
            ->with(['user.workerProfile'])
            ->when($filter === 'examination', fn ($q) => $q->where('status', WithdrawalStatus::UnderReview))
            ->when($filter === 'pending', fn ($q) => $q->where('status', WithdrawalStatus::Pending))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.withdrawals.index', [
            'withdrawals' => $withdrawals,
            'filter' => $filter,
        ]);
    }

    public function approve(WithdrawalRequest $withdrawal, Request $request, WithdrawalService $service): RedirectResponse
    {
        try {
            $service->approve($withdrawal, $request->user(), $request->string('notes')->toString() ?: null);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['withdrawal' => $e->getMessage()]);
        }

        return back()->with('status', 'Retrait approuvé. Le traitement ne démarre qu’après cette validation.');
    }

    public function examine(WithdrawalRequest $withdrawal, Request $request, WithdrawalService $service): RedirectResponse
    {
        $data = $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $service->markUnderReview($withdrawal, $request->user(), $data['notes'] ?? null);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['withdrawal' => $e->getMessage()]);
        }

        return back()->with('status', 'Retrait placé en examen.');
    }

    public function reject(WithdrawalRequest $withdrawal, Request $request, WithdrawalService $service): RedirectResponse
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:1000']]);

        try {
            $service->reject($withdrawal, $request->user(), $data['reason']);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['withdrawal' => $e->getMessage()]);
        }

        return back()->with('status', 'Retrait rejeté et solde rétabli.');
    }
}
