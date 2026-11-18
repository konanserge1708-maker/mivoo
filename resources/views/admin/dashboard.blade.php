<x-layouts.admin title="Tableau de bord">
    <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold tracking-tight text-mivoo-ink sm:text-4xl">Vue d’ensemble</h1>
            <p class="mt-2 text-sm text-mivoo-muted">Pilotage financier, missions et file d’attente opérationnelle.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.campaigns.create') }}" class="mivoo-btn mivoo-btn-primary !py-2.5">Nouvelle mission</a>
            <a href="{{ route('admin.withdrawals.index') }}" class="mivoo-btn mivoo-btn-secondary !py-2.5">Retraits</a>
        </div>
    </div>

    @if ($stats['pending_withdrawals'] > 0 || $stats['tasks_pending_review'] > 0)
        <div class="mb-6 grid gap-3 sm:grid-cols-2">
            @if ($stats['pending_withdrawals'] > 0)
                <a href="{{ route('admin.withdrawals.index') }}" class="block rounded-[1.5rem] bg-amber-50 px-5 py-4 transition hover:bg-amber-100">
                    <p class="text-xs font-semibold uppercase tracking-wide text-amber-800">Action requise</p>
                    <p class="mt-1 text-lg font-extrabold text-amber-950">{{ $stats['pending_withdrawals'] }} retrait(s) à approuver</p>
                    <p class="mt-1 text-sm text-amber-800/80">Aucun paiement n’est envoyé sans validation admin.</p>
                </a>
            @endif
            @if ($stats['tasks_pending_review'] > 0)
                <a href="{{ route('admin.tasks.index') }}" class="block rounded-[1.5rem] bg-mivoo-lilac/50 px-5 py-4 transition hover:bg-mivoo-lilac">
                    <p class="text-xs font-semibold uppercase tracking-wide text-mivoo-violet-deep">Validations</p>
                    <p class="mt-1 text-lg font-extrabold text-mivoo-ink">{{ $stats['tasks_pending_review'] }} mission(s) à revoir</p>
                    <p class="mt-1 text-sm text-mivoo-muted">File de preuves en attente.</p>
                </a>
            @endif
        </div>
    @endif

    <section class="mb-8">
        <h2 class="mb-3 text-sm font-bold uppercase tracking-wide text-mivoo-muted">Activité</h2>
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-ui.stat-card label="Workers" :value="$stats['workers']" tone="lilac" />
            <x-ui.stat-card label="Abonnements actifs" :value="$stats['active_subscriptions']" tone="peach" />
            <x-ui.stat-card label="Campagnes actives" :value="$stats['active_campaigns']" tone="sky" />
            <x-ui.stat-card label="À valider" :value="$stats['tasks_pending_review']" tone="white" />
        </div>
    </section>

    <section class="mb-8">
        <h2 class="mb-3 text-sm font-bold uppercase tracking-wide text-mivoo-muted">Finance</h2>
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-ui.stat-card label="CA abonnements" :value="$stats['subscription_revenue']" tone="peach" />
            <x-ui.stat-card label="Budgets campagnes" :value="$stats['campaign_revenue']" tone="lilac" />
            <x-ui.stat-card label="Gains missions" :value="$stats['task_spend']" tone="sky" />
            <x-ui.stat-card label="Retraits effectués" :value="$stats['payouts']" tone="white" />
        </div>
    </section>

    <section>
        <h2 class="mb-3 text-sm font-bold uppercase tracking-wide text-mivoo-muted">Accès rapides</h2>
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <a href="{{ route('admin.campaigns.index') }}" class="rounded-[1.5rem] bg-white/90 px-5 py-4 shadow-mivoo transition hover:-translate-y-0.5">
                <p class="font-bold text-mivoo-ink">Missions</p>
                <p class="mt-1 text-sm text-mivoo-muted">Pool vrac, création rapide</p>
            </a>
            <a href="{{ route('admin.users.index') }}" class="rounded-[1.5rem] bg-white/90 px-5 py-4 shadow-mivoo transition hover:-translate-y-0.5">
                <p class="font-bold text-mivoo-ink">Utilisateurs</p>
                <p class="mt-1 text-sm text-mivoo-muted">Blocage retraits, examen</p>
            </a>
            <a href="{{ route('admin.withdrawals.index') }}" class="rounded-[1.5rem] bg-white/90 px-5 py-4 shadow-mivoo transition hover:-translate-y-0.5">
                <p class="font-bold text-mivoo-ink">Retraits</p>
                <p class="mt-1 text-sm text-mivoo-muted">{{ $stats['pending_withdrawals'] }} en attente d’approbation</p>
            </a>
            <a href="{{ route('admin.settings.edit') }}" class="rounded-[1.5rem] bg-white/90 px-5 py-4 shadow-mivoo transition hover:-translate-y-0.5">
                <p class="font-bold text-mivoo-ink">Paramètres</p>
                <p class="mt-1 text-sm text-mivoo-muted">Quota jour, délais, retraits</p>
            </a>
        </div>
    </section>
</x-layouts.admin>
