<x-layouts.worker title="Résultat paiement" active="subscriptions">
    <x-ui.card class="fade-up text-center">
        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full {{ $success ? 'bg-mivoo-success/15 text-mivoo-success' : 'bg-mivoo-danger/15 text-mivoo-danger' }}">
            @if ($success)
                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            @else
                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            @endif
        </div>
        <h1 class="text-2xl font-extrabold text-mivoo-ink">
            {{ $success ? 'Retour de paiement' : 'Paiement non confirmé' }}
        </h1>
        <p class="mt-3 text-sm text-mivoo-muted">{{ $message }}</p>
        <p class="mt-2 text-sm text-mivoo-muted">Commande {{ $order->reference }} · {{ $order->status->label() }}</p>
        <div class="mt-6 flex flex-wrap justify-center gap-3">
            <x-ui.button :href="route('worker.subscriptions.index')">Mes VIP</x-ui.button>
            <x-ui.button :href="route('worker.subscriptions.history')" variant="secondary">Historique</x-ui.button>
        </div>
    </x-ui.card>
</x-layouts.worker>
