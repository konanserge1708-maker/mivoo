<x-layouts.worker title="Profil" active="profile">
    <header class="mb-6 fade-up">
        <h1 class="text-4xl font-extrabold tracking-tight text-mivoo-ink">Profil</h1>
        <p class="mt-2 text-sm text-mivoo-muted">Compte, VIP et accès rapides.</p>
    </header>

    <x-ui.card class="mb-5 overflow-hidden !p-0 fade-up fade-up-delay-1">
        <div class="bg-gradient-to-br from-mivoo-lilac/80 via-white to-mivoo-peach/40 px-5 pb-5 pt-6">
            <div class="flex items-start gap-4">
                <div class="relative">
                    <div class="flex h-20 w-20 items-center justify-center rounded-full bg-white text-3xl font-extrabold text-mivoo-violet-deep shadow-mivoo">
                        {{ strtoupper(substr($user->first_name, 0, 1)) }}{{ strtoupper(substr($user->last_name, 0, 1)) }}
                    </div>
                    <span class="absolute -bottom-1 -right-1 rounded-full bg-mivoo-success px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-white">
                        {{ $user->status->label() }}
                    </span>
                </div>
                <div class="min-w-0 flex-1">
                    <h2 class="truncate text-2xl font-extrabold text-mivoo-ink">{{ $user->name }}</h2>
                    <p class="mt-1 truncate text-sm text-mivoo-muted">{{ $user->email }}</p>
                    <p class="mt-0.5 text-sm text-mivoo-muted">{{ $user->phone }}</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <x-ui.badge size="lg">{{ $plan?->name ?? 'Gratuit' }}</x-ui.badge>
                        @if ($subscription?->ends_at && ($plan?->level ?? 0) > 0)
                            <x-ui.badge tone="soft" size="lg">
                                Expire {{ $subscription->ends_at->timezone('Africa/Abidjan')->format('d/m/Y') }}
                            </x-ui.badge>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </x-ui.card>

    <section class="mb-5 grid grid-cols-2 gap-3 fade-up fade-up-delay-2">
        <x-ui.stat-card label="Solde" :value="$stats['available']" tone="peach">
            <x-slot:hint>disponible</x-slot:hint>
        </x-ui.stat-card>
        <x-ui.stat-card label="Gains totaux" :value="$stats['lifetime']" tone="sky">
            <x-slot:hint>cumulés</x-slot:hint>
        </x-ui.stat-card>
        <x-ui.stat-card label="Missions" :value="$stats['tasks_total']" tone="lilac">
            <x-slot:hint>{{ $stats['tasks_today'] }} aujourd’hui</x-slot:hint>
        </x-ui.stat-card>
        <x-ui.stat-card label="Qualité" :value="$stats['quality'].'%'" tone="white">
            <x-slot:hint>{{ $stats['referrals'] }} filleul(s)</x-slot:hint>
        </x-ui.stat-card>
    </section>

    <x-ui.card class="mb-5 fade-up fade-up-delay-3">
        <h3 class="mb-4 text-lg font-bold text-mivoo-ink">Informations</h3>
        <dl class="space-y-3 text-sm">
            <div class="flex items-center justify-between gap-3 border-b border-mivoo-soft pb-3">
                <dt class="text-mivoo-muted">Ville</dt>
                <dd class="font-semibold text-mivoo-ink">{{ $user->workerProfile?->city ?: 'Non renseignée' }}</dd>
            </div>
            <div class="flex items-center justify-between gap-3 border-b border-mivoo-soft pb-3">
                <dt class="text-mivoo-muted">Pays</dt>
                <dd class="font-semibold text-mivoo-ink">{{ $user->workerProfile?->country ?? 'CI' }}</dd>
            </div>
            <div class="flex items-center justify-between gap-3 border-b border-mivoo-soft pb-3">
                <dt class="text-mivoo-muted">Code parrainage</dt>
                <dd class="font-semibold tracking-wider text-mivoo-ink">{{ $user->referral_code }}</dd>
            </div>
            <div class="flex items-center justify-between gap-3 border-b border-mivoo-soft pb-3">
                <dt class="text-mivoo-muted">Membre depuis</dt>
                <dd class="font-semibold text-mivoo-ink">{{ $user->created_at?->timezone('Africa/Abidjan')->format('d/m/Y') }}</dd>
            </div>
            <div class="flex items-center justify-between gap-3">
                <dt class="text-mivoo-muted">Dernière connexion</dt>
                <dd class="font-semibold text-mivoo-ink">
                    {{ $user->last_login_at?->timezone('Africa/Abidjan')->format('d/m/Y H:i') ?? '—' }}
                </dd>
            </div>
        </dl>
    </x-ui.card>

    <x-ui.card class="mb-5 fade-up fade-up-delay-3">
        <h3 class="mb-3 text-lg font-bold text-mivoo-ink">Raccourcis</h3>
        <div class="grid gap-2">
            <a href="{{ route('worker.wallet') }}" class="flex items-center justify-between rounded-2xl bg-mivoo-soft px-4 py-3 text-sm font-semibold text-mivoo-ink transition hover:bg-mivoo-lilac/50">
                Portefeuille
                <span class="text-mivoo-muted">{{ $stats['available'] }}</span>
            </a>
            <a href="{{ route('worker.subscriptions.index') }}" class="flex items-center justify-between rounded-2xl bg-mivoo-soft px-4 py-3 text-sm font-semibold text-mivoo-ink transition hover:bg-mivoo-lilac/50">
                Abonnement VIP
                <span class="text-mivoo-muted">{{ $plan?->name ?? 'Gratuit' }}</span>
            </a>
            <a href="{{ route('worker.referrals') }}" class="flex items-center justify-between rounded-2xl bg-mivoo-soft px-4 py-3 text-sm font-semibold text-mivoo-ink transition hover:bg-mivoo-lilac/50">
                Parrainage
                <span class="text-mivoo-muted">{{ $stats['referrals'] }} filleul(s)</span>
            </a>
            <a href="{{ route('worker.progress') }}" class="flex items-center justify-between rounded-2xl bg-mivoo-soft px-4 py-3 text-sm font-semibold text-mivoo-ink transition hover:bg-mivoo-lilac/50">
                Progression
                <span class="text-mivoo-muted">Voir</span>
            </a>
        </div>
    </x-ui.card>

    <form method="POST" action="{{ route('logout') }}" class="fade-up fade-up-delay-4 lg:hidden">
        @csrf
        <x-ui.button type="submit" variant="secondary" class="w-full !text-mivoo-danger">
            Déconnexion
        </x-ui.button>
    </form>
</x-layouts.worker>
