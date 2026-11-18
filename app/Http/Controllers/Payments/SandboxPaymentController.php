<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Models\PaymentTransaction;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SandboxPaymentController extends Controller
{
    public function show(string $uuid): View
    {
        $transaction = PaymentTransaction::query()
            ->with('payable')
            ->where('uuid', $uuid)
            ->where('provider', 'sandbox')
            ->firstOrFail();

        return view('payments.sandbox', compact('transaction'));
    }

    public function complete(Request $request, string $uuid, PaymentService $paymentService): RedirectResponse
    {
        $transaction = PaymentTransaction::query()
            ->where('uuid', $uuid)
            ->where('provider', 'sandbox')
            ->firstOrFail();

        $action = $request->string('action')->toString();

        if ($action === 'success') {
            $paymentService->markCompleted($transaction, [
                'provider_transaction_id' => 'sandbox_txn_'.$transaction->id,
                'simulated' => true,
            ]);

            return redirect()
                ->route('worker.subscriptions.payment.success', [
                    'order' => $transaction->payable->uuid,
                ])
                ->with('status', 'Paiement sandbox simulé avec succès. VIP activé.');
        }

        $paymentService->markFailed($transaction, 'Échec simulé (sandbox)');

        return redirect()
            ->route('worker.subscriptions.payment.error', [
                'order' => $transaction->payable->uuid,
            ])
            ->withErrors(['payment' => 'Échec de paiement simulé.']);
    }
}
