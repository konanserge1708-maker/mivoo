<x-layouts.admin title="Nouvelle mission">
    <h1 class="mb-2 text-3xl font-extrabold text-mivoo-ink">Nouvelle mission</h1>
    <p class="mb-6 text-sm text-mivoo-muted">3 champs seulement : titre, type et lien.</p>

    @if ($errors->any())
        <x-ui.alert type="error" class="mb-4">{{ $errors->first() }}</x-ui.alert>
    @endif

    <x-ui.card class="max-w-lg">
        <form method="POST" action="{{ route('admin.campaigns.store') }}" class="space-y-4">
            @csrf

            <x-ui.input
                name="title"
                label="Titre de la mission"
                :value="old('title', $defaultTitle)"
                placeholder="{{ $defaultTitle }}"
            />
            <p class="!mt-1 text-xs text-mivoo-muted">Laissez le titre proposé ou modifiez-le. Vide = génération auto.</p>

            <div>
                <label class="mivoo-label" for="mission_type">Type de mission</label>
                <select id="mission_type" name="mission_type" class="mivoo-input" required>
                    @foreach ($missionTypes as $type)
                        <option value="{{ $type->value }}" @selected(old('mission_type', 'visit_website') === $type->value)>
                            {{ $type->label() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <x-ui.input
                name="target_url"
                type="url"
                label="Lien"
                required
                :value="old('target_url')"
                placeholder="https://..."
            />

            <x-ui.button type="submit" class="w-full">Créer la mission</x-ui.button>
        </form>
    </x-ui.card>

    <script>
        (() => {
            const select = document.getElementById('mission_type');
            const title = document.querySelector('input[name="title"]');
            if (!select || !title) return;

            const prefixes = {
                visit_website: 'Mission',
                watch_video: 'Tâche',
                like_post: 'Job',
            };

            const codeMatch = (title.value || '').match(/#(\d+)\s*$/);
            const code = codeMatch ? codeMatch[1] : '001';

            const syncTitle = () => {
                const current = (title.value || '').trim();
                const isAuto = /^(Mission|Tâche|Job)\s*#\d+$/i.test(current) || current === '';
                if (!isAuto) return;
                const prefix = prefixes[select.value] || 'Mission';
                title.value = `${prefix} #${code}`;
            };

            select.addEventListener('change', syncTitle);
        })();
    </script>
</x-layouts.admin>
