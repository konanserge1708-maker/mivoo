<x-layouts.admin title="{{ $user->name }}">
    <h1 class="mb-2 text-3xl font-extrabold text-mivoo-ink">{{ $user->name }}</h1>
    <p class="mb-6 text-sm text-mivoo-muted">{{ $user->email }} · {{ $user->phone }} · {{ $user->status->label() }}</p>

    @if (session('status'))
        <x-ui.alert type="success" class="mb-4">{{ session('status') }}</x-ui.alert>
    @endif
    @if ($errors->any())
        <x-ui.alert type="error" class="mb-4">{{ $errors->first() }}</x-ui.alert>
    @endif

    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        <x-ui.stat-card label="VIP" :value="$user->activeSubscription?->plan?->name ?? 'Gratuit'" tone="lilac" />
        <x-ui.stat-card label="Solde" :value="number_format($user->wallet?->available_balance_xof ?? 0, 0, ',', ' ').' F'" tone="peach" />
        <x-ui.stat-card label="Gains cumulés" :value="number_format($user->wallet?->lifetime_earned_xof ?? 0, 0, ',', ' ').' F'" tone="sky" />
    </div>

    <div class="mb-6 flex flex-wrap gap-2">
        @if ($user->status->value === 'active')
            <form method="POST" action="{{ route('admin.users.suspend', $user) }}">@csrf<x-ui.button type="submit" variant="secondary">Suspendre</x-ui.button></form>
        @else
            <form method="POST" action="{{ route('admin.users.reactivate', $user) }}">@csrf<x-ui.button type="submit">Réactiver</x-ui.button></form>
        @endif
    </div>

    @php
        $profile = $user->workerProfile;
        $blocked = (bool) ($profile?->withdrawals_blocked);
        $exam = (bool) ($profile?->withdrawals_under_examination);
    @endphp

    <x-ui.card class="mb-6 max-w-2xl">
        <h2 class="mb-1 text-lg font-bold text-mivoo-ink">Contrôle des retraits</h2>
        <p class="mb-4 text-sm text-mivoo-muted">Bloquer ce compte, le placer en examen, ou fixer une limite personnalisée.</p>

        <div class="mb-4 flex flex-wrap gap-2">
            @if ($blocked)
                <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">Retraits bloqués</span>
            @else
                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Retraits autorisés</span>
            @endif
            @if ($exam)
                <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">En examen</span>
            @endif
            @if ($profile?->max_withdrawal_xof)
                <span class="rounded-full bg-mivoo-soft px-3 py-1 text-xs font-semibold text-mivoo-ink">
                    Max {{ number_format($profile->max_withdrawal_xof, 0, ',', ' ') }} F
                </span>
            @endif
        </div>

        @if ($profile?->withdrawal_block_reason)
            <p class="mb-4 rounded-2xl bg-mivoo-soft px-3 py-2 text-sm text-mivoo-muted">
                Motif : {{ $profile->withdrawal_block_reason }}
            </p>
        @endif

        <div class="space-y-4">
            @if ($blocked)
                <form method="POST" action="{{ route('admin.users.withdrawals.unblock', $user) }}">
                    @csrf
                    <x-ui.button type="submit">Débloquer les retraits</x-ui.button>
                </form>
            @else
                <form method="POST" action="{{ route('admin.users.withdrawals.block', $user) }}" class="space-y-3">
                    @csrf
                    <x-ui.input name="reason" label="Motif du blocage (optionnel)" :value="old('reason')" />
                    <x-ui.button type="submit" variant="secondary">Bloquer les retraits</x-ui.button>
                </form>
            @endif

            <div class="border-t border-mivoo-line/60 pt-4">
                @if ($exam)
                    <form method="POST" action="{{ route('admin.users.withdrawals.examination', $user) }}">
                        @csrf
                        <input type="hidden" name="enabled" value="0">
                        <p class="mb-2 text-sm text-mivoo-muted">Les retraits de ce compte sont placés automatiquement en examen.</p>
                        <x-ui.button type="submit">Lever l’examen</x-ui.button>
                    </form>
                @else
                    <form method="POST" action="{{ route('admin.users.withdrawals.examination', $user) }}" class="space-y-3">
                        @csrf
                        <input type="hidden" name="enabled" value="1">
                        <x-ui.input name="reason" label="Motif d’examen (optionnel)" :value="old('reason')" />
                        <x-ui.button type="submit" variant="secondary">Passer en examen</x-ui.button>
                    </form>
                @endif
            </div>

            <div class="border-t border-mivoo-line/60 pt-4">
                <form method="POST" action="{{ route('admin.users.withdrawals.limit', $user) }}" class="space-y-3">
                    @csrf
                    <x-ui.input
                        name="max_withdrawal_xof"
                        type="number"
                        label="Limite max de retrait (F CFA, vide = aucune limite perso)"
                        :value="old('max_withdrawal_xof', $profile?->max_withdrawal_xof)"
                        min="0"
                    />
                    <x-ui.button type="submit">Enregistrer la limite</x-ui.button>
                </form>
            </div>
        </div>
    </x-ui.card>
</x-layouts.admin>
