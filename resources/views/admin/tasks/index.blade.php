<x-layouts.admin title="Validations">
    <h1 class="mb-6 text-3xl font-extrabold text-mivoo-ink">Missions à valider</h1>
    <div class="space-y-3">
        @forelse ($tasks as $task)
            <x-ui.card class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="font-bold text-mivoo-ink">{{ $task->campaign->title }}</p>
                    <p class="text-sm text-mivoo-muted">{{ $task->user->name }} · {{ $task->status->label() }} · {{ $task->reward_xof }} F</p>
                </div>
                <a href="{{ route('admin.tasks.show', $task) }}" class="font-semibold text-mivoo-violet">Examiner</a>
            </x-ui.card>
        @empty
            <x-ui.empty-state title="File vide" description="Aucune mission en attente de validation." />
        @endforelse
    </div>
    <div class="mt-4">{{ $tasks->links() }}</div>
</x-layouts.admin>
