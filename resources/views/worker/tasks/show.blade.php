<x-layouts.worker title="Mission" active="tasks">
    <a href="{{ route('worker.tasks.index') }}" class="mb-6 inline-flex h-11 w-11 items-center justify-center rounded-full bg-white/80 shadow-sm" aria-label="Retour">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
    </a>

    <div class="mb-2 flex flex-wrap items-center gap-2">
        @if ($missionType)
            <x-ui.badge :tone="$missionType->badgeTone()">{{ $missionType->label() }}</x-ui.badge>
        @endif
        <x-ui.badge>{{ $task->reward_xof }} F</x-ui.badge>
    </div>

    <h1 class="mb-2 text-3xl font-extrabold text-mivoo-ink">{{ $task->campaign->title }}</h1>
    <p class="mb-4 text-sm text-mivoo-muted">{{ $task->status->label() }}</p>

    @if (session('status'))
        <x-ui.alert type="success" class="mb-4">{{ session('status') }}</x-ui.alert>
    @endif
    @if ($errors->any())
        <x-ui.alert type="error" class="mb-4">{{ $errors->first() }}</x-ui.alert>
    @endif

    <x-ui.card class="mb-4">
        <h2 class="mb-2 font-bold">Description</h2>
        <p class="whitespace-pre-line text-sm text-mivoo-muted">{{ $task->campaign->description }}</p>
    </x-ui.card>

    <x-ui.card class="mb-4">
        <h2 class="mb-2 font-bold">Instructions</h2>
        <p class="whitespace-pre-line text-sm text-mivoo-muted">{{ $task->campaign->instructions }}</p>
    </x-ui.card>

    @if (in_array($task->status->value, ['assigned', 'in_progress'], true))
        @if ($missionType)
            <x-ui.card class="mb-4">
                <h2 class="mb-3 font-bold">Étape 1 — Ouvrir le lien</h2>
                <a href="{{ route('worker.tasks.open', $task) }}"
                   id="open-mission-link"
                   target="_blank"
                   class="inline-flex w-full items-center justify-center rounded-2xl bg-mivoo-violet px-4 py-3 text-sm font-bold text-white shadow-sm transition hover:opacity-95"
                   rel="noopener">
                    {{ $missionType->openButtonLabel() }}
                </a>
                @if ($linkOpened)
                    <p class="mt-3 text-sm font-semibold text-mivoo-success">Lien ouvert. Délai minimum : {{ $minDwellSeconds }} s.</p>
                @else
                    <p class="mt-3 text-sm text-mivoo-muted">Ouvrez le lien, restez au moins {{ $minDwellSeconds }} s, puis revenez confirmer.</p>
                @endif
            </x-ui.card>

            <x-ui.card>
                <h2 class="mb-3 font-bold">Étape 2 — Confirmer</h2>

                <p id="dwell-message" class="mb-3 text-sm {{ $canConfirm ? 'font-semibold text-mivoo-success' : 'text-mivoo-muted' }}"
                   data-remaining="{{ $remainingSeconds }}"
                   data-opened="{{ $linkOpened ? '1' : '0' }}"
                   data-opened-at="{{ $openedAtIso }}"
                   data-min-dwell="{{ $minDwellSeconds }}">
                    @if (! $linkOpened)
                        Ouvrez d’abord le lien pour démarrer le délai.
                    @elseif ($canConfirm)
                        Délai atteint — vous pouvez confirmer.
                    @else
                        Encore <strong id="dwell-count">{{ $remainingSeconds }}</strong> s avant confirmation…
                    @endif
                </p>

                <form method="POST" action="{{ route('worker.tasks.submit', $task) }}" class="space-y-4" id="confirm-form">
                    @csrf
                    <label id="confirm-label" class="flex items-start gap-3 rounded-2xl bg-mivoo-soft px-4 py-3 text-sm text-mivoo-ink {{ $canConfirm ? '' : 'opacity-60' }}">
                        <input
                            id="confirm-checkbox"
                            type="checkbox"
                            name="confirmed"
                            value="1"
                            class="mt-1 h-4 w-4 rounded border-mivoo-line text-mivoo-violet"
                            @disabled(! $canConfirm)
                            required
                        >
                        <span>
                            <strong>{{ $missionType->confirmationLabel() }}</strong>
                            <span class="mt-1 block text-mivoo-muted">Cochez uniquement après avoir effectué l’action demandée.</span>
                        </span>
                    </label>
                    <button id="confirm-submit" type="submit" class="mivoo-btn mivoo-btn-primary w-full" @disabled(! $canConfirm)>
                        Valider la mission
                    </button>
                </form>
            </x-ui.card>

            <script>
                (() => {
                    const openLink = document.getElementById('open-mission-link');
                    const message = document.getElementById('dwell-message');
                    const checkbox = document.getElementById('confirm-checkbox');
                    const submit = document.getElementById('confirm-submit');
                    const label = document.getElementById('confirm-label');
                    if (!message || !checkbox || !submit) return;

                    // Après ouverture dans un nouvel onglet, on recharge pour démarrer le délai serveur.
                    openLink?.addEventListener('click', () => {
                        if (message.dataset.opened === '1') return;
                        window.setTimeout(() => window.location.reload(), 900);
                    });

                    const opened = message.dataset.opened === '1';
                    const openedAt = message.dataset.openedAt ? Date.parse(message.dataset.openedAt) : null;
                    const minDwell = Number(message.dataset.minDwell || 12);

                    if (!opened || !openedAt) return;

                    const unlock = () => {
                        checkbox.disabled = false;
                        submit.disabled = false;
                        label.classList.remove('opacity-60');
                        message.className = 'mb-3 text-sm font-semibold text-mivoo-success';
                        message.textContent = 'Délai atteint — vous pouvez confirmer.';
                    };

                    const tick = () => {
                        const elapsed = Math.floor((Date.now() - openedAt) / 1000);
                        const remaining = Math.max(0, minDwell - elapsed);
                        if (remaining <= 0) {
                            unlock();
                            return;
                        }
                        message.className = 'mb-3 text-sm text-mivoo-muted';
                        message.innerHTML = `Encore <strong>${remaining}</strong> s avant confirmation…`;
                        window.setTimeout(tick, 250);
                    };

                    if (Number(message.dataset.remaining || 0) <= 0) {
                        unlock();
                    } else {
                        tick();
                    }
                })();
            </script>
        @else
            @if ($task->status->value === 'assigned')
                <form method="POST" action="{{ route('worker.tasks.start', $task) }}" class="mb-4">
                    @csrf
                    <x-ui.button type="submit" class="w-full">Démarrer</x-ui.button>
                </form>
            @endif

            <x-ui.card>
                <form method="POST" action="{{ route('worker.tasks.submit', $task) }}" class="space-y-3">
                    @csrf
                    <label class="mivoo-label" for="text_response">Preuve / compte-rendu</label>
                    <textarea id="text_response" name="text_response" rows="5" class="mivoo-input" required minlength="10">{{ old('text_response') }}</textarea>
                    <x-ui.button type="submit" class="w-full">Soumettre</x-ui.button>
                </form>
            </x-ui.card>
        @endif
    @endif

    @if ($task->status->value === 'under_review')
        <x-ui.alert type="warning">Mission envoyée — en attente de validation.</x-ui.alert>
    @endif

    @if ($task->status->value === 'rejected')
        <x-ui.alert type="error" class="mb-4">{{ $task->rejection_reason }}</x-ui.alert>
        <x-ui.card>
            <form method="POST" action="{{ route('worker.tasks.dispute', $task) }}" class="space-y-3">
                @csrf
                <textarea name="reason" rows="4" class="mivoo-input" required placeholder="Pourquoi contester ?"></textarea>
                <x-ui.button type="submit" variant="secondary">Contester</x-ui.button>
            </form>
        </x-ui.card>
    @endif

    @if ($task->status->value === 'approved')
        <x-ui.alert type="success">Mission approuvée · gain {{ $task->earned_xof }} F</x-ui.alert>
    @endif
</x-layouts.worker>
