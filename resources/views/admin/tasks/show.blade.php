<x-layouts.admin title="Revue mission">
    <h1 class="mb-2 text-3xl font-extrabold text-mivoo-ink">{{ $task->campaign->title }}</h1>
    <p class="mb-6 text-sm text-mivoo-muted">{{ $task->user->name }} · {{ $task->status->label() }}</p>

    <x-ui.card class="mb-4">
        <h2 class="mb-2 font-bold">Preuve</h2>
        <p class="whitespace-pre-line text-sm text-mivoo-muted">{{ $task->submission?->text_response ?: 'Aucune réponse textuelle.' }}</p>
    </x-ui.card>

    @if (in_array($task->status->value, ['under_review', 'disputed'], true))
        <div class="flex flex-wrap gap-3">
            <form method="POST" action="{{ route('admin.tasks.approve', $task) }}">
                @csrf
                <x-ui.button type="submit">Approuver</x-ui.button>
            </form>
            <form method="POST" action="{{ route('admin.tasks.reject', $task) }}" class="flex flex-wrap gap-2">
                @csrf
                <input type="text" name="reason" class="mivoo-input" placeholder="Motif du rejet" required>
                <x-ui.button type="submit" variant="secondary">Rejeter</x-ui.button>
            </form>
        </div>
    @endif
</x-layouts.admin>
