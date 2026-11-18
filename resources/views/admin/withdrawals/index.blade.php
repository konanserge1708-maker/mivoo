<x-layouts.admin title="Retraits">
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold tracking-tight text-mivoo-ink">Retraits</h1>
            <p class="mt-2 max-w-2xl text-sm text-mivoo-muted">
                Chaque demande reste bloquée jusqu’à votre approbation. Aucun paiement n’est déclenché avant validation admin.
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.withdrawals.index') }}"
               class="rounded-full px-3.5 py-1.5 text-sm font-semibold {{ $filter === '' ? 'bg-mivoo-ink text-white' : 'bg-white text-mivoo-muted shadow-sm' }}">Tous</a>
            <a href="{{ route('admin.withdrawals.index', ['status' => 'pending']) }}"
               class="rounded-full px-3.5 py-1.5 text-sm font-semibold {{ $filter === 'pending' ? 'bg-mivoo-violet text-white' : 'bg-white text-mivoo-muted shadow-sm' }}">En attente</a>
            <a href="{{ route('admin.withdrawals.index', ['status' => 'examination']) }}"
               class="rounded-full px-3.5 py-1.5 text-sm font-semibold {{ $filter === 'examination' ? 'bg-amber-500 text-white' : 'bg-amber-50 text-amber-900' }}">En examen</a>
        </div>
    </div>

    <div class="mb-5 rounded-[1.5rem] border border-mivoo-lilac/60 bg-white/70 px-4 py-3 text-sm text-mivoo-muted">
        Flux : <strong class="text-mivoo-ink">Demande worker</strong>
        → <strong class="text-mivoo-ink">Approbation admin</strong>
        → <strong class="text-mivoo-ink">Traitement / paiement</strong>
    </div>

    <div class="space-y-3">
        @forelse ($withdrawals as $item)
            @php
                $profile = $item->user?->workerProfile;
                $accountExam = (bool) ($profile?->withdrawals_under_examination);
                $accountBlocked = (bool) ($profile?->withdrawals_blocked);
                $needsAction = in_array($item->status->value, ['pending', 'under_review'], true);
            @endphp
            <x-ui.card class="!p-5">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="text-lg font-extrabold text-mivoo-ink">
                                {{ number_format($item->amount_xof, 0, ',', ' ') }} F
                            </p>
                            @php
                                $statusTone = match ($item->status->value) {
                                    'under_review' => 'bg-amber-100 text-amber-900',
                                    'pending' => 'bg-mivoo-soft text-mivoo-ink',
                                    'completed' => 'bg-emerald-100 text-emerald-800',
                                    'rejected' => 'bg-red-100 text-red-700',
                                    default => 'bg-mivoo-sky/70 text-mivoo-ink',
                                };
                            @endphp
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusTone }}">
                                {{ $item->status->label() }}
                            </span>
                        </div>

                        <p class="mt-2 text-sm font-semibold text-mivoo-ink">
                            <a href="{{ route('admin.users.show', $item->user) }}" class="hover:text-mivoo-violet">
                                {{ $item->user->name }}
                            </a>
                        </p>
                        <p class="mt-1 text-sm text-mivoo-muted">
                            {{ $item->reference }} · {{ strtoupper($item->method) }} · {{ $item->destination_phone }}
                            · Net {{ number_format($item->net_amount_xof, 0, ',', ' ') }} F
                        </p>
                        @if ($item->admin_notes)
                            <p class="mt-2 text-xs text-mivoo-muted">Note : {{ $item->admin_notes }}</p>
                        @endif
                        <div class="mt-3 flex flex-wrap gap-2">
                            @if ($accountExam)
                                <span class="rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-700">Compte en examen</span>
                            @endif
                            @if ($accountBlocked)
                                <span class="rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-700">Retraits bloqués</span>
                            @endif
                            @if ($needsAction)
                                <span class="rounded-full bg-mivoo-ink/5 px-2.5 py-0.5 text-xs font-semibold text-mivoo-ink">En attente d’approbation</span>
                            @endif
                        </div>
                    </div>

                    @if ($needsAction)
                        <div class="flex w-full flex-col gap-2 sm:w-auto sm:min-w-[220px]">
                            <form method="POST" action="{{ route('admin.withdrawals.approve', $item) }}">
                                @csrf
                                <button type="submit" class="mivoo-btn mivoo-btn-primary w-full !py-2.5">Approuver & traiter</button>
                            </form>
                            @if ($item->status->value !== 'under_review')
                                <form method="POST" action="{{ route('admin.withdrawals.examine', $item) }}">
                                    @csrf
                                    <button type="submit" class="mivoo-btn mivoo-btn-secondary w-full !py-2.5">Passer en examen</button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('admin.withdrawals.reject', $item) }}" class="space-y-2">
                                @csrf
                                <input type="text" name="reason" class="mivoo-input !py-2.5" placeholder="Motif du rejet" required>
                                <button type="submit" class="mivoo-btn mivoo-btn-secondary w-full !py-2.5 text-mivoo-danger">Rejeter</button>
                            </form>
                        </div>
                    @endif
                </div>
            </x-ui.card>
        @empty
            <x-ui.empty-state title="Aucun retrait" description="Les demandes des workers apparaîtront ici pour approbation." />
        @endforelse
    </div>
    <div class="mt-4">{{ $withdrawals->links() }}</div>
</x-layouts.admin>
