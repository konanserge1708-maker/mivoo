<x-layouts.admin title="Ticket {{ $ticket->reference }}">
    <h1 class="mb-2 text-3xl font-extrabold text-mivoo-ink">{{ $ticket->subject }}</h1>
    <p class="mb-6 text-sm text-mivoo-muted">{{ $ticket->reference }} · {{ $ticket->status->label() }}</p>

    <div class="mb-6 space-y-3">
        @foreach ($ticket->messages as $message)
            <div class="rounded-2xl px-4 py-3 {{ $message->is_staff ? 'bg-mivoo-lilac/40' : 'bg-white shadow-sm' }}">
                <p class="text-xs font-semibold text-mivoo-muted">{{ $message->user->name }} · {{ $message->created_at->format('d/m H:i') }}</p>
                <p class="mt-1 whitespace-pre-line text-sm">{{ $message->body }}</p>
            </div>
        @endforeach
    </div>

    @if ($ticket->status->value !== 'closed')
        <x-ui.card>
            <form method="POST" action="{{ route('admin.support.reply', $ticket) }}" class="space-y-3">
                @csrf
                <textarea name="body" rows="4" class="mivoo-input" required placeholder="Votre réponse…"></textarea>
                <div class="flex gap-2">
                    <x-ui.button type="submit">Répondre</x-ui.button>
                </div>
            </form>
            <form method="POST" action="{{ route('admin.support.close', $ticket) }}" class="mt-3">
                @csrf
                <x-ui.button type="submit" variant="secondary">Fermer le ticket</x-ui.button>
            </form>
        </x-ui.card>
    @endif
</x-layouts.admin>
