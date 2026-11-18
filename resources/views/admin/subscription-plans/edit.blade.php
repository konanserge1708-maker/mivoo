<x-layouts.admin title="Modifier {{ $plan->name }}">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-extrabold text-mivoo-ink">{{ $plan->name }}</h1>
            <p class="mt-1 text-sm text-mivoo-muted">slug : {{ $plan->slug }} · modèle : missions × gain = plafond / jour</p>
        </div>
        <a href="{{ route('admin.subscription-plans.index') }}" class="text-sm font-semibold text-mivoo-violet">Retour</a>
    </div>

    @if (session('status'))
        <x-ui.alert type="success" class="mb-4">{{ session('status') }}</x-ui.alert>
    @endif
    @if ($errors->any())
        <x-ui.alert type="error" class="mb-4">{{ $errors->first() }}</x-ui.alert>
    @endif

    <div class="grid gap-6 lg:grid-cols-[1.4fr_1fr]">
        <x-ui.card>
            <form method="POST" action="{{ route('admin.subscription-plans.update', $plan) }}" class="space-y-4" id="plan-form">
                @csrf
                @method('PUT')
                <x-ui.input name="name" label="Nom" :value="$plan->name" required />
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.input name="price_xof" type="number" label="Prix (F CFA)" :value="$plan->price_xof" required />
                    <x-ui.input name="duration_days" type="number" label="Durée (jours)" :value="$plan->duration_days" required />
                    <x-ui.input name="indicative_task_value_xof" type="number" label="Gain / mission (F)" :value="old('indicative_task_value_xof', $plan->indicative_task_value_xof)" required />
                    <x-ui.input name="max_tasks_per_day" type="number" label="Missions / jour (max {{ $hardMaxTasks }})" :value="old('max_tasks_per_day', $plan->max_tasks_per_day)" min="1" max="{{ $hardMaxTasks }}" required />
                    <x-ui.input name="sort_order" type="number" label="Ordre" :value="$plan->sort_order" required />
                </div>

                <input type="hidden" name="daily_cap_xof" id="daily_cap_xof" value="{{ $plan->alignedDailyCap() }}">

                <div class="rounded-2xl bg-mivoo-soft px-4 py-3 text-sm text-mivoo-muted">
                    Plafond journalier calculé :
                    <strong id="cap-preview" class="text-mivoo-ink">{{ number_format($plan->alignedDailyCap(), 0, ',', ' ') }} F</strong>
                    <span class="block mt-1 text-xs">= missions / jour × gain / mission (aligné pour tous les VIP)</span>
                </div>

                <div>
                    <label class="mivoo-label" for="benefits_text">Avantages (1 ligne = 1 avantage, vide = auto)</label>
                    <textarea id="benefits_text" name="benefits_text" rows="5" class="mivoo-input">{{ old('benefits_text', implode("\n", $plan->benefits ?? [])) }}</textarea>
                </div>
                <label class="flex items-center gap-2 text-sm text-mivoo-ink">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $plan->is_active)) class="rounded text-mivoo-violet focus:ring-mivoo-violet">
                    Plan actif
                </label>
                <x-ui.button type="submit">Enregistrer</x-ui.button>
            </form>
        </x-ui.card>

        <x-ui.card>
            <h2 class="text-lg font-bold text-mivoo-ink">Historique des modifications</h2>
            <div class="mt-4 space-y-3">
                @forelse ($audits as $audit)
                    <div class="rounded-2xl bg-mivoo-soft px-3 py-3 text-xs text-mivoo-muted">
                        <p class="font-semibold text-mivoo-ink">{{ $audit->user?->name ?? 'Système' }} · {{ $audit->created_at->format('d/m/Y H:i') }}</p>
                        <p class="mt-1">Prix {{ $audit->old_values['price_xof'] ?? '?' }} → {{ $audit->new_values['price_xof'] ?? '?' }} F</p>
                    </div>
                @empty
                    <p class="text-sm text-mivoo-muted">Aucune modification pour l’instant.</p>
                @endforelse
            </div>
        </x-ui.card>
    </div>

    <script>
        (() => {
            const reward = document.querySelector('[name="indicative_task_value_xof"]');
            const tasks = document.querySelector('[name="max_tasks_per_day"]');
            const hidden = document.getElementById('daily_cap_xof');
            const preview = document.getElementById('cap-preview');
            if (!reward || !tasks || !hidden || !preview) return;
            const sync = () => {
                const cap = Math.max(0, Number(tasks.value || 0) * Number(reward.value || 0));
                hidden.value = String(cap);
                preview.textContent = new Intl.NumberFormat('fr-FR').format(cap) + ' F';
            };
            reward.addEventListener('input', sync);
            tasks.addEventListener('input', sync);
            sync();
        })();
    </script>
</x-layouts.admin>
