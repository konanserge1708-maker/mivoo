<x-layouts.admin title="Support">
    <h1 class="mb-6 text-3xl font-extrabold text-mivoo-ink">Tickets support</h1>
    <div class="space-y-3">
        @forelse ($tickets as $ticket)
            <x-ui.card class="flex items-center justify-between gap-3">
                <div>
                    <p class="font-bold">{{ $ticket->subject }}</p>
                    <p class="text-sm text-mivoo-muted">{{ $ticket->user->name }} · {{ $ticket->status->label() }} · {{ $ticket->reference }}</p>
                </div>
                <a href="{{ route('admin.support.show', $ticket) }}" class="font-semibold text-mivoo-violet">Ouvrir</a>
            </x-ui.card>
        @empty
            <x-ui.empty-state title="Aucun ticket" />
        @endforelse
    </div>
    <div class="mt-4">{{ $tickets->links() }}</div>
</x-layouts.admin>
