<x-layouts.admin title="{{ $campaign->title }}">
    <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-3xl font-extrabold text-mivoo-ink">{{ $campaign->title }}</h1>
            <p class="mt-1 text-sm text-mivoo-muted">
                {{ $campaign->missionType()?->label() ?? $campaign->category?->name }}
                · {{ $campaign->status->label() }}
                · Pool illimité
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            @if (! $campaign->funding_confirmed)
                <form method="POST" action="{{ route('admin.campaigns.activate', $campaign) }}">
                    @csrf
                    <x-ui.button type="submit">Activer</x-ui.button>
                </form>
            @elseif ($campaign->status->value === 'active')
                <form method="POST" action="{{ route('admin.campaigns.suspend', $campaign) }}">
                    @csrf
                    <x-ui.button type="submit" variant="secondary">Suspendre</x-ui.button>
                </form>
            @endif
            <form method="POST" action="{{ route('admin.campaigns.destroy', $campaign) }}" onsubmit="return confirm('Supprimer cette mission du pool ?');">
                @csrf
                @method('DELETE')
                <x-ui.button type="submit" variant="secondary">Supprimer</x-ui.button>
            </form>
        </div>
    </div>

    @if (session('status'))
        <x-ui.alert type="success" class="mb-4">{{ session('status') }}</x-ui.alert>
    @endif

    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.stat-card label="Récompense" :value="number_format($campaign->reward_per_task_xof, 0, ',', ' ').' F'" tone="peach" />
        <x-ui.stat-card label="Attribuées" :value="$campaign->tasks_assigned" tone="lilac" />
        <x-ui.stat-card label="Approuvées" :value="$campaign->tasks_approved" tone="sky" />
        <x-ui.stat-card label="Lien" :value="\Illuminate\Support\Str::limit($campaign->target_url ?? '—', 28)" tone="white" />
    </div>

    <x-ui.card class="mb-6">
        <h2 class="mb-2 text-lg font-bold">Lien cible</h2>
        @if ($campaign->target_url)
            <a href="{{ $campaign->target_url }}" target="_blank" rel="noopener" class="break-all text-sm font-semibold text-mivoo-violet">{{ $campaign->target_url }}</a>
        @else
            <p class="text-sm text-mivoo-muted">Aucun lien</p>
        @endif
    </x-ui.card>

    <x-ui.card class="mb-6">
        <h2 class="mb-2 text-lg font-bold">Instructions</h2>
        <p class="whitespace-pre-line text-sm text-mivoo-muted">{{ $campaign->instructions }}</p>
    </x-ui.card>
</x-layouts.admin>
