<x-layouts.admin title="Missions">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-3xl font-extrabold text-mivoo-ink">Missions</h1>
            <p class="mt-1 text-sm text-mivoo-muted">Pool vrac : tirage aléatoire jusqu’à suppression.</p>
        </div>
        <x-ui.button :href="route('admin.campaigns.create')">Nouvelle mission</x-ui.button>
    </div>

    @if (session('status'))
        <x-ui.alert type="success" class="mb-4">{{ session('status') }}</x-ui.alert>
    @endif

    <div class="overflow-hidden rounded-[1.75rem] bg-white/90 shadow-mivoo">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-mivoo-soft text-mivoo-muted">
                <tr>
                    <th class="px-4 py-3">Titre</th>
                    <th class="px-4 py-3">Type</th>
                    <th class="px-4 py-3">Statut</th>
                    <th class="px-4 py-3">Attributions</th>
                    <th class="px-4 py-3">Récompense</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($campaigns as $campaign)
                    <tr class="border-t border-mivoo-soft">
                        <td class="px-4 py-4 font-semibold text-mivoo-ink">{{ $campaign->title }}</td>
                        <td class="px-4 py-4">{{ $campaign->missionType()?->label() ?? $campaign->category?->name }}</td>
                        <td class="px-4 py-4">{{ $campaign->status->label() }}</td>
                        <td class="px-4 py-4">{{ $campaign->tasks_assigned }}</td>
                        <td class="px-4 py-4">{{ number_format($campaign->reward_per_task_xof, 0, ',', ' ') }} F</td>
                        <td class="px-4 py-4 text-right">
                            <a href="{{ route('admin.campaigns.show', $campaign) }}" class="font-semibold text-mivoo-violet">Voir</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-mivoo-muted">Aucune mission.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $campaigns->links() }}</div>
</x-layouts.admin>
