<x-layouts.partner title="Tableau de bord">
    <h1 class="mb-2 text-3xl font-extrabold text-mivoo-ink">Bonjour, {{ $user->partnerProfile?->company_name ?? $user->display_name }}</h1>
    <p class="mb-8 text-sm text-mivoo-muted">Pilotage de vos campagnes partenaires.</p>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <x-ui.stat-card label="Campagnes actives" :value="$stats['active_campaigns']" tone="lilac" />
        <x-ui.stat-card label="Budget engagé" :value="$stats['budget_committed']" tone="peach" />
        <x-ui.stat-card label="Budget restant" :value="$stats['budget_remaining']" tone="sky" />
        <x-ui.stat-card label="Tâches commandées" :value="$stats['tasks_ordered']" tone="white" />
        <x-ui.stat-card label="Tâches réalisées" :value="$stats['tasks_done']" tone="lilac" />
        <x-ui.stat-card label="Tâches validées" :value="$stats['tasks_validated']" tone="peach" />
    </div>
</x-layouts.partner>
