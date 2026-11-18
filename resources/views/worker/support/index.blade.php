<x-layouts.worker title="Support" active="profile">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-4xl font-extrabold text-mivoo-ink">Support</h1>
    </div>

    <x-ui.card class="mb-6">
        <form method="POST" action="{{ route('worker.support.store') }}" class="space-y-3">
            @csrf
            <x-ui.input name="subject" label="Sujet" required />
            <div>
                <label class="mivoo-label" for="category">Catégorie</label>
                <select id="category" name="category" class="mivoo-input">
                    <option value="general">Général</option>
                    <option value="payment">Paiement</option>
                    <option value="task">Mission</option>
                    <option value="account">Compte</option>
                </select>
            </div>
            <textarea name="body" rows="4" class="mivoo-input" required placeholder="Décrivez votre problème…"></textarea>
            <x-ui.button type="submit">Créer un ticket</x-ui.button>
        </form>
    </x-ui.card>

    <div class="space-y-3">
        @foreach ($tickets as $ticket)
            <a href="{{ route('worker.support.show', $ticket) }}" class="mivoo-card block p-4">
                <p class="font-bold">{{ $ticket->subject }}</p>
                <p class="text-sm text-mivoo-muted">{{ $ticket->status->label() }} · {{ $ticket->reference }}</p>
            </a>
        @endforeach
    </div>
    <div class="mt-4">{{ $tickets->links() }}</div>
</x-layouts.worker>
