<x-layouts.worker title="Portefeuille" active="wallet">
    <h1 class="mb-2 text-4xl font-extrabold tracking-tight text-mivoo-ink fade-up">Portefeuille</h1>
    <p class="mb-6 text-sm text-mivoo-muted fade-up">
        Minimum de retrait : {{ number_format($minWithdrawal, 0, ',', ' ') }} F
        @if ($maxWithdrawal)
            · Maximum : {{ number_format($maxWithdrawal, 0, ',', ' ') }} F
        @endif
    </p>

    @if (session('status'))
        <x-ui.alert type="success" class="mb-4">{{ session('status') }}</x-ui.alert>
    @endif
    @if ($errors->any())
        <x-ui.alert type="error" class="mb-4">{{ $errors->first() }}</x-ui.alert>
    @endif

    <div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-2 fade-up fade-up-delay-1">
        <x-ui.stat-card label="Disponible" :value="\App\Support\Money::formatShort($user->wallet?->available_balance_xof ?? 0)" tone="peach" />
        <x-ui.stat-card label="En attente" :value="\App\Support\Money::formatShort($user->wallet?->pending_balance_xof ?? 0)" tone="sky" />
    </div>

    <x-ui.card class="mb-6 fade-up fade-up-delay-2">
        <h2 class="mb-3 text-lg font-bold">Demander un retrait</h2>

        @if (! $withdrawalsEnabled)
            <x-ui.alert type="error" class="mb-3">Les retraits sont temporairement désactivés sur la plateforme.</x-ui.alert>
        @elseif ($withdrawalsBlocked)
            <x-ui.alert type="error" class="mb-3">
                Les retraits sont bloqués sur votre compte.
                @if ($withdrawalBlockReason)
                    {{ $withdrawalBlockReason }}
                @endif
            </x-ui.alert>
        @else
            @if ($withdrawalsUnderExamination)
                <x-ui.alert type="warning" class="mb-3">
                    Votre compte est en examen : toute demande de retrait sera vérifiée manuellement avant paiement.
                </x-ui.alert>
            @endif

            <form method="POST" action="{{ route('worker.wallet.withdraw') }}" class="space-y-3">
                @csrf
                <x-ui.input name="amount_xof" type="number" label="Montant (F CFA)" :value="old('amount_xof', $minWithdrawal)" required min="{{ $minWithdrawal }}" @if($maxWithdrawal) max="{{ $maxWithdrawal }}" @endif />
                <div>
                    <label class="mivoo-label" for="method">Moyen</label>
                    <select id="method" name="method" class="mivoo-input" required>
                        @foreach ($methods as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <x-ui.input name="destination_phone" label="Numéro bénéficiaire" :value="old('destination_phone', $user->phone)" required />
                <x-ui.button type="submit" class="w-full">Demander le retrait</x-ui.button>
            </form>
        @endif

        <a href="{{ route('worker.withdrawals') }}" class="mt-3 inline-block text-sm font-semibold text-mivoo-violet">Historique des retraits</a>
    </x-ui.card>

    <h2 class="mb-3 text-lg font-bold fade-up">Mouvements</h2>
    <div class="space-y-2 fade-up fade-up-delay-3">
        @forelse ($transactions as $tx)
            <x-ui.card class="!p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-mivoo-ink">{{ $tx->type->label() }}</p>
                        <p class="text-xs text-mivoo-muted">{{ $tx->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <p class="font-bold {{ $tx->amount_xof >= 0 ? 'text-mivoo-success' : 'text-mivoo-danger' }}">
                        {{ $tx->amount_xof >= 0 ? '+' : '' }}{{ number_format($tx->amount_xof, 0, ',', ' ') }} F
                    </p>
                </div>
            </x-ui.card>
        @empty
            <x-ui.empty-state title="Aucun mouvement" description="Les gains validés apparaîtront ici." />
        @endforelse
    </div>
    <div class="mt-4">{{ $transactions->links() }}</div>
</x-layouts.worker>
