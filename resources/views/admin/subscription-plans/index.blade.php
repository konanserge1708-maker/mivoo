<x-layouts.admin title="Plans VIP">
    <h1 class="mb-2 text-3xl font-extrabold text-mivoo-ink">Plans VIP</h1>
    <p class="mb-8 text-sm text-mivoo-muted">Modèle unique : plafond / jour = missions × gain / mission (max 10 missions).</p>

    <div class="overflow-hidden rounded-[1.75rem] bg-white/90 shadow-mivoo">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-mivoo-soft text-mivoo-muted">
                <tr>
                    <th class="px-4 py-3 font-semibold">Niveau</th>
                    <th class="px-4 py-3 font-semibold">Prix</th>
                    <th class="px-4 py-3 font-semibold">Gain / mission</th>
                    <th class="px-4 py-3 font-semibold">Plafond / j</th>
                    <th class="px-4 py-3 font-semibold">Durée</th>
                    <th class="px-4 py-3 font-semibold">Statut</th>
                    <th class="px-4 py-3 font-semibold"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($plans as $plan)
                    <tr class="border-t border-mivoo-soft">
                        <td class="px-4 py-4 font-semibold text-mivoo-ink">{{ $plan->name }}</td>
                        <td class="px-4 py-4">{{ $plan->formattedPrice() }}</td>
                        <td class="px-4 py-4">{{ number_format($plan->indicative_task_value_xof, 0, ',', ' ') }} F × {{ $plan->max_tasks_per_day }}</td>
                        <td class="px-4 py-4">{{ number_format($plan->alignedDailyCap(), 0, ',', ' ') }} F</td>
                        <td class="px-4 py-4">{{ $plan->duration_days }} j</td>
                        <td class="px-4 py-4">
                            <x-ui.badge :tone="$plan->is_active ? 'success' : 'soft'">
                                {{ $plan->is_active ? 'Actif' : 'Inactif' }}
                            </x-ui.badge>
                        </td>
                        <td class="px-4 py-4 text-right">
                            <a href="{{ route('admin.subscription-plans.edit', $plan) }}" class="font-semibold text-mivoo-violet">Modifier</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-layouts.admin>
