<x-layouts.admin title="Paramètres">
    <h1 class="mb-2 text-3xl font-extrabold text-mivoo-ink">Paramètres</h1>
    <p class="mb-8 text-sm text-mivoo-muted">Retraits, délais mission et quota journalier (max 10).</p>

    @if (session('status'))
        <x-ui.alert type="success" class="mb-4">{{ session('status') }}</x-ui.alert>
    @endif

    <x-ui.card class="max-w-xl">
        <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-4" id="settings-form">
            @csrf
            @method('PUT')

            <label class="flex items-center gap-3 rounded-2xl bg-mivoo-soft px-4 py-3 text-sm text-mivoo-ink">
                <input type="checkbox" name="withdrawals_enabled" value="1" class="h-4 w-4 rounded border-mivoo-line"
                    @checked(old('withdrawals_enabled', $withdrawalsEnabled))>
                <span>
                    <strong>Retraits activés</strong>
                    <span class="block text-mivoo-muted">Décochez pour bloquer tous les retraits sur la plateforme.</span>
                </span>
            </label>

            <x-ui.input name="min_withdrawal_xof" type="number" label="Minimum de retrait (F CFA)" :value="old('min_withdrawal_xof', $minWithdrawal)" required />
            <x-ui.input name="max_withdrawal_xof" type="number" label="Maximum de retrait global (F CFA, 0 = illimité)" :value="old('max_withdrawal_xof', $maxWithdrawal)" />
            <x-ui.input name="withdrawal_fee_xof" type="number" label="Frais de retrait (F CFA)" :value="old('withdrawal_fee_xof', \App\Models\SystemSetting::getValue('withdrawal_fee_xof', 0))" required />

            <div class="border-t border-mivoo-line/50 pt-4">
                <h2 class="mb-3 text-sm font-bold uppercase tracking-wide text-mivoo-muted">Missions du jour</h2>

                <div class="mb-4">
                    <label class="mivoo-label" for="daily_missions_mode">Mode de tirage</label>
                    <select id="daily_missions_mode" name="daily_missions_mode" class="mivoo-input" required>
                        <option value="fixed" @selected(old('daily_missions_mode', $dailyMissionsMode) === 'fixed')>Nombre fixe</option>
                        <option value="random" @selected(old('daily_missions_mode', $dailyMissionsMode) === 'random')>Aléatoire (1 à max)</option>
                    </select>
                </div>

                <div id="fixed-count-wrap">
                    <x-ui.input name="daily_missions_count" type="number" label="Nombre de missions / jour (mode fixe)" :value="old('daily_missions_count', $dailyMissionsCount)" min="1" max="10" required />
                </div>

                <x-ui.input name="max_tasks_per_day" type="number" label="Plafond absolu / jour (max 10)" :value="old('max_tasks_per_day', $maxTasksPerDay)" min="1" max="10" required />
                <p class="!mt-1 text-xs text-mivoo-muted">En mode aléatoire, le tirage reste compris entre 1 et ce plafond (jamais plus de 10).</p>
            </div>

            <x-ui.input name="mission_min_dwell_seconds" type="number" label="Délai min. après ouverture du lien (secondes)" :value="old('mission_min_dwell_seconds', $minDwellSeconds)" required />
            <x-ui.input name="referral_commission_percent" type="number" label="Commission parrainage (%)" :value="old('referral_commission_percent', \App\Models\SystemSetting::getValue('referral_commission_percent', 10))" required />

            <div class="rounded-2xl bg-mivoo-soft px-4 py-3 text-sm text-mivoo-muted">
                Driver paiement actuel : <strong class="text-mivoo-ink">{{ $paymentDriver }}</strong>
                (configurable via <code>PAYMENT_DRIVER</code> / clés Jeko dans `.env`)
            </div>

            <x-ui.button type="submit">Enregistrer</x-ui.button>
        </form>
    </x-ui.card>

    <script>
        (() => {
            const mode = document.getElementById('daily_missions_mode');
            const wrap = document.getElementById('fixed-count-wrap');
            if (!mode || !wrap) return;
            const sync = () => { wrap.style.opacity = mode.value === 'fixed' ? '1' : '0.55'; };
            mode.addEventListener('change', sync);
            sync();
        })();
    </script>
</x-layouts.admin>
