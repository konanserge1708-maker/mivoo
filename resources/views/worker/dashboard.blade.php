<x-layouts.worker title="Tableau de bord" active="home">
    <header class="mb-8 flex items-start justify-between gap-4 fade-up">
        <div class="flex items-center gap-3">
            <div class="flex h-12 w-12 items-center justify-center overflow-hidden rounded-full bg-mivoo-lilac text-lg font-bold text-mivoo-violet-deep">
                {{ strtoupper(substr($user->first_name, 0, 1)) }}
            </div>
            <div>
                <p class="text-sm text-mivoo-muted">Bonjour,</p>
                <h1 class="text-2xl font-extrabold tracking-tight text-mivoo-ink">{{ $user->display_name }}</h1>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button
                type="button"
                class="flex h-11 w-11 items-center justify-center rounded-full bg-white/80 text-mivoo-ink shadow-sm transition hover:bg-mivoo-ink hover:text-white"
                aria-label="Partager mon lien de parrainage"
                data-share-referral
                data-share-url="{{ url('/register?ref=' . $user->referral_code) }}"
                data-share-title="Rejoins Mivoo"
                data-share-text="Inscris-toi sur Mivoo avec mon lien et commence les micro-tâches : "
            >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <circle cx="18" cy="5" r="2.5"/>
                    <circle cx="6" cy="12" r="2.5"/>
                    <circle cx="18" cy="19" r="2.5"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.5 13.5l7 4M15.5 6.5l-7 4"/>
                </svg>
            </button>
            <a href="{{ route('worker.notifications') }}" class="relative flex h-11 w-11 items-center justify-center rounded-full bg-white/80 text-mivoo-ink shadow-sm" aria-label="Notifications">
                <span class="absolute right-2.5 top-2.5 h-2 w-2 rounded-full bg-mivoo-danger"></span>
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0a3 3 0 11-6 0"/></svg>
            </a>
        </div>
    </header>

    <section class="mb-8 fade-up fade-up-delay-1">
        <p class="text-sm font-medium text-mivoo-muted">Niveau actuel</p>
        <h2 class="mt-1 max-w-xl text-4xl font-extrabold leading-tight tracking-tight text-mivoo-ink sm:text-5xl">
            Commencez les missions du jour
        </h2>
        <div class="mt-4 flex flex-wrap items-center gap-3">
            <div class="inline-flex items-center gap-2 rounded-full bg-white/80 px-4 py-2 shadow-sm">
                <span class="text-xs font-semibold uppercase tracking-wide text-mivoo-muted">VIP</span>
                <span class="text-sm font-extrabold text-mivoo-ink">{{ $stats['vip_label'] }}</span>
                @if ($stats['vip_expires'] && ($plan?->level ?? 0) > 0)
                    <span class="text-xs text-mivoo-muted">· expire {{ $stats['vip_expires']->timezone('Africa/Abidjan')->format('d/m/Y') }}</span>
                @endif
            </div>
            <a href="{{ route('worker.subscriptions.index') }}"
               class="inline-flex items-center gap-1.5 rounded-full bg-mivoo-ink px-4 py-2 text-sm font-semibold text-white transition duration-200 hover:bg-black hover:scale-[1.03]">
                {{ ($plan?->level ?? 0) > 0 ? 'Changer de niveau' : 'Upgrade VIP' }}
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
    </section>

    <section class="mb-8 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4 fade-up fade-up-delay-2">
        <x-ui.stat-card label="Aujourd’hui" :value="sprintf('%02d', $stats['today_tasks'])" tone="lilac">
            <x-slot:hint>tâches faites</x-slot:hint>
        </x-ui.stat-card>
        <x-ui.stat-card label="Restantes" :value="sprintf('%02d', $stats['remaining_tasks'])" tone="white">
            <x-slot:hint>sur 10 max</x-slot:hint>
        </x-ui.stat-card>
        <x-ui.stat-card label="Solde" :value="$stats['available_balance']" tone="peach">
            <x-slot:hint>disponible</x-slot:hint>
        </x-ui.stat-card>
        <x-ui.stat-card label="Gains du jour" :value="$stats['daily_earnings']" tone="sky">
            <x-slot:hint>en attente / validés</x-slot:hint>
        </x-ui.stat-card>
    </section>

    <section class="fade-up fade-up-delay-3">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-xl font-bold text-mivoo-ink">Missions du jour</h3>
            <a href="{{ route('worker.tasks.index') }}" class="text-sm font-semibold text-mivoo-violet">Voir tout</a>
        </div>

        <div class="space-y-3">
            @forelse ($upcomingTasks as $task)
                <x-ui.card class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-mivoo-muted">{{ $task['time'] }}</p>
                        <h4 class="mt-1 text-lg font-bold text-mivoo-ink">{{ $task['title'] }}</h4>
                        <p class="mt-1 text-sm text-mivoo-muted">{{ $task['meta'] }} · {{ $task['reward'] }} F</p>
                        <div class="mt-3">
                            <x-ui.badge size="lg">{{ $task['tag'] }}</x-ui.badge>
                        </div>
                    </div>
                    <a href="{{ route('worker.tasks.show', $task['uuid']) }}" class="mivoo-icon-btn" aria-label="Ouvrir">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </x-ui.card>
            @empty
                <x-ui.card>
                    @if ($hasDrawnToday)
                        <p class="text-sm text-mivoo-muted">Aucune mission en attente. Consultez l’historique ou revenez demain.</p>
                        <a href="{{ route('worker.tasks.index', ['filter' => 'today']) }}" class="mt-4 inline-flex text-sm font-semibold text-mivoo-violet">Voir mes missions du jour</a>
                    @else
                        <p class="text-sm text-mivoo-muted">Tirez votre lot de missions pour aujourd’hui.</p>
                        <form method="POST" action="{{ route('worker.tasks.claim') }}" class="mt-4">
                            @csrf
                            <x-ui.button type="submit">Tirer mes missions du jour</x-ui.button>
                        </form>
                    @endif
                </x-ui.card>
            @endforelse
        </div>
    </section>
</x-layouts.worker>
