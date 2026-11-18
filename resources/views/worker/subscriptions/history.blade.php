<x-layouts.worker title="Historique VIP" active="subscriptions">
    <div class="mb-6 flex items-center justify-between fade-up">
        <h1 class="text-4xl font-extrabold tracking-tight text-mivoo-ink">Historique</h1>
        <a href="{{ route('worker.subscriptions.index') }}" class="text-sm font-semibold text-mivoo-violet">Retour</a>
    </div>

    <div class="space-y-3 fade-up fade-up-delay-1">
        @forelse ($orders as $order)
            <x-ui.card>
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-lg font-bold text-mivoo-ink">{{ $order->plan_snapshot['name'] ?? $order->plan?->name }}</p>
                        <p class="mt-1 text-sm text-mivoo-muted">{{ $order->reference }} · {{ $order->created_at->format('d/m/Y H:i') }}</p>
                        <p class="mt-2 text-sm font-semibold text-mivoo-ink">{{ number_format($order->amount_xof, 0, ',', ' ') }} F</p>
                    </div>
                    <x-ui.badge :tone="$order->status->value === 'paid' ? 'success' : 'soft'">
                        {{ $order->status->label() }}
                    </x-ui.badge>
                </div>
            </x-ui.card>
        @empty
            <x-ui.empty-state title="Aucun paiement" description="Vos souscriptions VIP apparaîtront ici." />
        @endforelse
    </div>

    <div class="mt-6">{{ $orders->links() }}</div>
</x-layouts.worker>
