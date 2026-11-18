@props([
    'title' => 'Espace partenaire',
])

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} — Mivoo Partenaire</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen">
    <div class="mx-auto max-w-6xl px-4 py-6 lg:px-8">
        <header class="mb-8 flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-2xl font-extrabold text-mivoo-ink">mivoo partenaire</p>
                <p class="text-sm text-mivoo-muted">Campagnes & prestations</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <x-ui.button type="submit" variant="secondary">Déconnexion</x-ui.button>
            </form>
        </header>
        {{ $slot }}
    </div>
</body>
</html>
