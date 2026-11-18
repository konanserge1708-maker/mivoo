<x-layouts.worker title="Payer {{ $plan->name }}" active="subscriptions">
    <a href="{{ route('worker.subscriptions.index') }}" class="mb-6 inline-flex h-11 w-11 items-center justify-center rounded-full bg-white/80 shadow-sm" aria-label="Retour">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
    </a>

    <h1 class="mb-2 text-4xl font-extrabold tracking-tight text-mivoo-ink fade-up">Payer {{ $plan->name }}</h1>
    <p class="mb-6 text-sm text-mivoo-muted fade-up">
        {{ number_format($plan->price_xof, 0, ',', ' ') }} F CFA · {{ $plan->duration_days }} jours · paiement via Jeko
        @if ($driver === 'sandbox') (simulation) @endif
    </p>

    @if ($errors->any())
        <x-ui.alert type="error" class="mb-4">{{ $errors->first() }}</x-ui.alert>
    @endif

    <x-ui.card class="fade-up fade-up-delay-1">
        <form method="POST" action="{{ route('worker.subscriptions.store', $plan) }}" class="space-y-4">
            @csrf
            <p class="text-sm font-semibold text-mivoo-ink">Moyen de paiement</p>
            <div class="grid gap-3 sm:grid-cols-2">
                @foreach ($methods as $value => $label)
                    <label class="flex cursor-pointer items-center gap-3 rounded-2xl bg-mivoo-soft px-4 py-3">
                        <input type="radio" name="payment_method" value="{{ $value }}" class="text-mivoo-violet focus:ring-mivoo-violet" @checked(old('payment_method', 'wave') === $value) required>
                        <span class="text-sm font-semibold text-mivoo-ink">{{ $label }}</span>
                    </label>
                @endforeach
            </div>

            <p class="text-xs text-mivoo-muted">
                L’activation VIP n’intervient qu’après confirmation serveur (webhook Jeko). La page de retour navigateur ne suffit pas.
            </p>

            <x-ui.button type="submit" class="w-full">
                Payer {{ number_format($plan->price_xof, 0, ',', ' ') }} F
            </x-ui.button>
        </form>
    </x-ui.card>
</x-layouts.worker>
