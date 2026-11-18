<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Mivoo') — Micro-tâches rémunérées</title>
    <meta name="description" content="@yield('meta_description', 'Mivoo : réalisez des micro-tâches numériques et gagnez en Franc CFA.')">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-screen">
    @if (session('status'))
        <div data-toast data-flash-auto-hide class="fixed right-4 top-4 z-50 max-w-sm rounded-2xl bg-mivoo-ink px-4 py-3 text-sm text-white shadow-lg">
            <div class="flex items-start justify-between gap-3">
                <p>{{ session('status') }}</p>
                <button type="button" data-toast-dismiss class="opacity-70 hover:opacity-100" aria-label="Fermer">×</button>
            </div>
        </div>
    @endif

    @yield('body')

    @stack('scripts')
</body>
</html>
