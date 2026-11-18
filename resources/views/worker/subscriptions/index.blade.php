<x-layouts.worker title="Abonnements VIP" active="subscriptions">
    <div class="mb-6 flex items-start justify-between gap-4 fade-up">
        <div>
            <h1 class="text-4xl font-extrabold tracking-tight text-mivoo-ink">Abonnements VIP</h1>
            <p class="mt-2 text-sm text-mivoo-muted">
                Les VIP élèvent le plafond journalier. Aucun revenu n’est garanti.
            </p>
        </div>
        <a href="{{ route('worker.subscriptions.history') }}" class="text-sm font-semibold text-mivoo-violet">Historique</a>
    </div>

    <x-ui.card class="mb-6 fade-up fade-up-delay-1">
        <p class="text-sm text-mivoo-muted">Niveau actuel</p>
        <p class="mt-2 text-2xl font-extrabold text-mivoo-ink">{{ $current?->plan?->name ?? 'Gratuit' }}</p>
        @if ($current?->ends_at && ($current->plan?->level ?? 0) > 0)
            <p class="mt-2 text-sm text-mivoo-muted">
                Expire le {{ $current->ends_at->timezone('Africa/Abidjan')->format('d/m/Y H:i') }}
            </p>
        @endif
        @if ($driver === 'sandbox')
            <p class="mt-4 rounded-2xl bg-mivoo-peach/40 px-3 py-2 text-xs font-medium text-mivoo-ink">
                Mode paiement : simulation sandbox (aucun débit réel).
            </p>
        @endif
    </x-ui.card>

    <div class="space-y-4 fade-up fade-up-delay-2">
        @foreach ($plans as $plan)
            <div class="vip-card {{ $plan->toneClass() }}">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <span class="vip-material">{{ $plan->materialLabel() }}</span>
                        <p class="vip-title text-sm font-semibold">
                            {{ $plan->name }}
                        </p>
                        <p class="vip-price mt-2 text-3xl font-extrabold">
                            {{ $plan->formattedPrice() }}
                        </p>
                        <p class="vip-muted mt-2 text-sm">
                            Plafond / jour : {{ number_format($plan->daily_cap_xof, 0, ',', ' ') }} F
                            · {{ $plan->duration_days }} jours
                        </p>
                        @if (!empty($plan->benefits))
                            <ul class="vip-muted mt-3 space-y-1 text-sm">
                                @foreach ($plan->benefits as $benefit)
                                    <li>• {{ $benefit }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                    <div>
                        @if ($plan->price_xof <= 0)
                            <x-ui.badge tone="soft">Inclus</x-ui.badge>
                        @elseif ($current?->subscription_plan_id === $plan->id && $current?->isActive())
                            <x-ui.badge tone="success">Actif</x-ui.badge>
                        @else
                            <x-ui.button :href="route('worker.subscriptions.checkout', $plan)" variant="primary">
                                Souscrire
                            </x-ui.button>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</x-layouts.worker>
