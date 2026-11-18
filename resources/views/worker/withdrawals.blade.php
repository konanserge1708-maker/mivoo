<x-layouts.worker title="Retraits" active="wallet">
    <h1 class="mb-6 text-4xl font-extrabold text-mivoo-ink">Retraits</h1>
    <div class="space-y-3">
        @forelse ($items as $item)
            <x-ui.card>
                <p class="font-bold">{{ number_format($item->amount_xof, 0, ',', ' ') }} F · {{ $item->status->label() }}</p>
                <p class="text-sm text-mivoo-muted">{{ $item->reference }} · {{ strtoupper($item->method) }} · {{ $item->created_at->format('d/m/Y H:i') }}</p>
            </x-ui.card>
        @empty
            <x-ui.empty-state title="Aucun retrait" />
        @endforelse
    </div>
    <div class="mt-4">{{ $items->links() }}</div>
</x-layouts.worker>
