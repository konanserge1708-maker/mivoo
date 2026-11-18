<x-layouts.worker title="Parrainage" active="referrals">
    <h1 class="mb-2 text-4xl font-extrabold tracking-tight text-mivoo-ink fade-up">Parrainage</h1>
    <p class="mb-6 text-sm text-mivoo-muted fade-up">10 % sur les gains validés de vos filleuls.</p>

    <x-ui.card class="mb-5 fade-up fade-up-delay-1">
        <p class="text-sm text-mivoo-muted">Votre code</p>
        <p class="mt-2 text-3xl font-extrabold tracking-widest text-mivoo-ink">{{ $user->referral_code }}</p>
        <p class="mt-4 text-sm text-mivoo-muted">Lien d’invitation</p>
        <p class="mt-1 break-all rounded-2xl bg-mivoo-soft px-4 py-3 text-sm font-medium">
            {{ url('/register?ref=' . $user->referral_code) }}
        </p>
        <div class="mt-4 grid grid-cols-2 gap-3">
            <div class="rounded-2xl bg-mivoo-lilac/50 px-3 py-3">
                <p class="text-xs text-mivoo-muted">Filleuls</p>
                <p class="text-xl font-extrabold">{{ $user->referrals_count }}</p>
            </div>
            <div class="rounded-2xl bg-mivoo-peach/50 px-3 py-3">
                <p class="text-xs text-mivoo-muted">Commissions</p>
                <p class="text-xl font-extrabold">{{ $total }}</p>
            </div>
        </div>
    </x-ui.card>

    <div class="space-y-2">
        @forelse ($commissions as $commission)
            <x-ui.card class="!p-4">
                <div class="flex justify-between gap-3">
                    <p class="text-sm">Mission #{{ $commission->task_assignment_id }}</p>
                    <p class="font-bold text-mivoo-success">+{{ number_format($commission->commission_xof, 0, ',', ' ') }} F</p>
                </div>
            </x-ui.card>
        @empty
            <x-ui.empty-state title="Pas encore de commissions" />
        @endforelse
    </div>
    <div class="mt-4">{{ $commissions->links() }}</div>
</x-layouts.worker>
