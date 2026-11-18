<x-layouts.worker title="Ticket" active="profile">
    <h1 class="mb-2 text-3xl font-extrabold text-mivoo-ink">{{ $ticket->subject }}</h1>
    <p class="mb-6 text-sm text-mivoo-muted">{{ $ticket->reference }} · {{ $ticket->status->label() }}</p>
    <div class="space-y-3">
        @foreach ($ticket->messages as $message)
            <div class="rounded-2xl bg-white/90 px-4 py-3 shadow-sm">
                <p class="text-xs text-mivoo-muted">{{ $message->is_staff ? 'Support' : 'Vous' }} · {{ $message->created_at->format('d/m H:i') }}</p>
                <p class="mt-1 whitespace-pre-line text-sm">{{ $message->body }}</p>
            </div>
        @endforeach
    </div>
</x-layouts.worker>
