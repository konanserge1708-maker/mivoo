<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use App\Http\Requests\Worker\SubscribeRequest;
use App\Models\SubscriptionOrder;
use App\Models\SubscriptionPlan;
use App\Services\PaymentService;
use App\Services\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user()->load(['activeSubscription.plan']);

        $plans = SubscriptionPlan::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('worker.subscriptions.index', [
            'user' => $user,
            'plans' => $plans,
            'current' => $user->activeSubscription,
            'driver' => config('payments.default'),
        ]);
    }

    public function history(Request $request): View
    {
        $orders = SubscriptionOrder::query()
            ->with(['plan', 'latestPayment.transaction'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(12);

        return view('worker.subscriptions.history', compact('orders'));
    }

    public function checkout(SubscriptionPlan $plan): View|RedirectResponse
    {
        if (! $plan->is_active || $plan->price_xof <= 0) {
            return redirect()
                ->route('worker.subscriptions.index')
                ->withErrors(['plan' => 'Ce niveau ne peut pas être souscrit.']);
        }

        return view('worker.subscriptions.checkout', [
            'plan' => $plan,
            'methods' => config('payments.methods'),
            'driver' => config('payments.default'),
        ]);
    }

    public function store(
        SubscribeRequest $request,
        SubscriptionPlan $plan,
        SubscriptionService $subscriptionService,
        PaymentService $paymentService,
    ): RedirectResponse {
        $order = $subscriptionService->createOrder($request->user(), $plan);
        $transaction = $paymentService->startSubscriptionPayment(
            $request->user(),
            $order,
            $request->validated('payment_method'),
        );

        return redirect()->away($transaction->redirect_url);
    }

    public function paymentSuccess(Request $request, string $order): View
    {
        $orderModel = SubscriptionOrder::query()
            ->with(['plan', 'subscription'])
            ->where('uuid', $order)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        return view('worker.subscriptions.result', [
            'order' => $orderModel,
            'success' => true,
            'message' => 'Si le paiement est confirmé par Jeko, votre VIP sera activé automatiquement. Ne vous fiez pas uniquement à cette page.',
        ]);
    }

    public function paymentError(Request $request, string $order): View
    {
        $orderModel = SubscriptionOrder::query()
            ->with('plan')
            ->where('uuid', $order)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        return view('worker.subscriptions.result', [
            'order' => $orderModel,
            'success' => false,
            'message' => 'Le paiement semble avoir échoué ou a été annulé. Vous pouvez réessayer.',
        ]);
    }
}
