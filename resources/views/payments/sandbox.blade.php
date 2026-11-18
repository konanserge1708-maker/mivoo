@extends('layouts.app')

@section('title', 'Paiement sandbox')

@section('body')
<div class="mx-auto flex min-h-screen max-w-md flex-col justify-center px-4 py-10">
    <div class="mb-6 text-center">
        <p class="text-3xl font-extrabold text-mivoo-ink">mivoo</p>
        <p class="mt-2 rounded-full bg-mivoo-peach/50 px-3 py-1 text-xs font-semibold inline-block">SIMULATION SANDBOX</p>
    </div>

    <x-ui.card>
        <h1 class="text-2xl font-extrabold text-mivoo-ink">Paiement test</h1>
        <p class="mt-2 text-sm text-mivoo-muted">
            Aucun appel à l’API Jeko. Utilisez cette page uniquement en développement.
        </p>
        <p class="mt-4 text-sm text-mivoo-ink">
            Référence : <strong>{{ $transaction->reference }}</strong><br>
            Montant : <strong>{{ number_format($transaction->amount_xof, 0, ',', ' ') }} F</strong><br>
            Méthode : <strong>{{ strtoupper($transaction->payment_method) }}</strong>
        </p>

        <form method="POST" action="{{ route('payments.sandbox.complete', $transaction->uuid) }}" class="mt-6 grid gap-3">
            @csrf
            <x-ui.button type="submit" name="action" value="success" class="w-full">Simuler succès</x-ui.button>
            <x-ui.button type="submit" name="action" value="fail" variant="secondary" class="w-full">Simuler échec</x-ui.button>
        </form>
    </x-ui.card>
</div>
@endsection
