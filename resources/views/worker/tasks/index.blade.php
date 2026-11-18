<x-layouts.worker title="Vos missions" active="tasks">
    <header class="mb-6 flex flex-wrap items-center justify-between gap-3 fade-up">
        <div>
            <h1 class="text-4xl font-extrabold tracking-tight text-mivoo-ink">Vos missions</h1>
            <p class="mt-1 text-sm text-mivoo-muted">
                Quota journalier : {{ $dailyQuotaPreview }}
                @if ($hasDrawnToday)
                    · {{ $todaysCount }} tirée{{ $todaysCount > 1 ? 's' : '' }} aujourd’hui
                @endif
            </p>
        </div>
        @if (! $hasDrawnToday)
            <form method="POST" action="{{ route('worker.tasks.claim') }}">
                @csrf
                <x-ui.button type="submit">Tirer mes missions du jour</x-ui.button>
            </form>
        @endif
    </header>

    @if (session('status'))
        <x-ui.alert type="success" class="mb-4">{{ session('status') }}</x-ui.alert>
    @endif
    @if ($errors->any())
        <x-ui.alert type="error" class="mb-4">{{ $errors->first() }}</x-ui.alert>
    @endif

    <div class="mb-6 flex flex-wrap gap-2 fade-up fade-up-delay-1">
        <a href="{{ route('worker.tasks.index', ['filter' => 'today']) }}" class="mivoo-chip {{ $filter === 'today' ? 'mivoo-chip-active' : 'mivoo-chip-idle' }}">Aujourd’hui</a>
        <a href="{{ route('worker.tasks.index', ['filter' => 'all']) }}" class="mivoo-chip {{ $filter === 'all' ? 'mivoo-chip-active' : 'mivoo-chip-idle' }}">Toutes</a>
        <a href="{{ route('worker.tasks.index', ['filter' => 'in_progress']) }}" class="mivoo-chip {{ $filter === 'in_progress' ? 'mivoo-chip-active' : 'mivoo-chip-idle' }}">En cours</a>
        <a href="{{ route('worker.tasks.index', ['filter' => 'completed']) }}" class="mivoo-chip {{ $filter === 'completed' ? 'mivoo-chip-active' : 'mivoo-chip-idle' }}">Terminées</a>
    </div>

    <div class="space-y-4 fade-up fade-up-delay-2">
        @forelse ($tasks as $task)
            <x-ui.card>
                <p class="text-xs font-semibold uppercase tracking-wide text-mivoo-muted">{{ $task->status->label() }}</p>
                <h2 class="mt-2 text-lg font-bold text-mivoo-ink">{{ $task->campaign->title }}</h2>
                <p class="mt-2 text-sm text-mivoo-muted">{{ \Illuminate\Support\Str::limit($task->campaign->description, 120) }}</p>
                <div class="mt-4 flex flex-wrap items-center gap-2">
                    <x-ui.badge :tone="$task->campaign->missionType()?->badgeTone() ?? 'dark'">
                        {{ $task->campaign->missionType()?->label() ?? $task->campaign->category?->name ?? 'Mission' }}
                    </x-ui.badge>
                    <x-ui.badge tone="violet">{{ $task->reward_xof }} F</x-ui.badge>
                </div>
                <div class="mt-5 flex justify-end">
                    <a href="{{ route('worker.tasks.show', $task) }}" class="mivoo-icon-btn" aria-label="Ouvrir">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </x-ui.card>
        @empty
            <x-ui.empty-state
                title="{{ $hasDrawnToday ? 'Aucune mission dans ce filtre' : 'Pas encore de tirage' }}"
                description="{{ $hasDrawnToday ? 'Changez de filtre pour voir vos missions.' : 'Appuyez sur « Tirer mes missions du jour » pour recevoir votre lot.' }}"
            />
        @endforelse
    </div>
    <div class="mt-6">{{ $tasks->links() }}</div>
</x-layouts.worker>
