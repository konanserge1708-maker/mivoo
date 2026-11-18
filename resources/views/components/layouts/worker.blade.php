@props([
    'title' => 'Espace worker',
    'active' => 'home',
])

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} — Mivoo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen">
    @if (session('status'))
        <div data-toast data-flash-auto-hide class="fixed right-4 top-4 z-50 max-w-sm rounded-2xl bg-mivoo-ink px-4 py-3 text-sm text-white shadow-lg">
            {{ session('status') }}
        </div>
    @endif

    <div class="mivoo-shell">
        <div class="lg:grid lg:grid-cols-[16rem_minmax(0,1fr)] lg:gap-8">
            <aside class="mivoo-sidebar fade-up">
                <div class="mb-6 px-2">
                    <p class="text-2xl font-extrabold tracking-tight text-mivoo-ink">mivoo</p>
                    <p class="mt-1 text-sm text-mivoo-muted">Micro-tâches · XOF</p>
                </div>

                @php
                    $links = [
                        'home' => ['route' => 'worker.dashboard', 'label' => 'Accueil'],
                        'tasks' => ['route' => 'worker.tasks.index', 'label' => 'Missions'],
                        'progress' => ['route' => 'worker.progress', 'label' => 'Progrès'],
                        'wallet' => ['route' => 'worker.wallet', 'label' => 'Portefeuille'],
                        'subscriptions' => ['route' => 'worker.subscriptions.index', 'label' => 'VIP'],
                        'referrals' => ['route' => 'worker.referrals', 'label' => 'Parrainage'],
                        'support' => ['route' => 'worker.support.index', 'label' => 'Support'],
                        'profile' => ['route' => 'worker.profile', 'label' => 'Profil'],
                    ];
                @endphp

                @foreach ($links as $key => $link)
                    <a href="{{ route($link['route']) }}"
                       class="rounded-2xl px-4 py-3 text-sm font-semibold transition {{ $active === $key ? 'bg-mivoo-ink text-white' : 'text-mivoo-muted hover:bg-mivoo-soft hover:text-mivoo-ink' }}">
                        {{ $link['label'] }}
                    </a>
                @endforeach

                <form method="POST" action="{{ route('logout') }}" class="mt-auto pt-6">
                    @csrf
                    <button type="submit" class="w-full rounded-2xl px-4 py-3 text-left text-sm font-semibold text-mivoo-danger hover:bg-mivoo-danger/10">
                        Déconnexion
                    </button>
                </form>
            </aside>

            <main>
                {{ $slot }}
            </main>
        </div>

        <nav class="mivoo-nav" aria-label="Navigation principale">
            <a href="{{ route('worker.dashboard') }}" class="mivoo-nav-item {{ $active === 'home' ? 'is-active' : '' }}" aria-label="Accueil">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10.5 12 3l9 7.5V21a1 1 0 01-1 1h-5v-6H9v6H4a1 1 0 01-1-1v-10.5z"/></svg>
            </a>
            <a href="{{ route('worker.tasks.index') }}" class="mivoo-nav-item {{ $active === 'tasks' ? 'is-active' : '' }}" aria-label="Missions">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5h11M9 12h11M9 19h11M4 5h.01M4 12h.01M4 19h.01"/></svg>
            </a>
            <a href="{{ route('worker.tasks.index') }}" class="mivoo-nav-add" aria-label="Nouvelle mission">
                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/></svg>
            </a>
            <a href="{{ route('worker.progress') }}" class="mivoo-nav-item {{ $active === 'progress' ? 'is-active' : '' }}" aria-label="Progrès">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 19V9m5 10V5m5 14v-7m5 7V3"/></svg>
            </a>
            <a href="{{ route('worker.profile') }}" class="mivoo-nav-item {{ $active === 'profile' ? 'is-active' : '' }}" aria-label="Profil">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM4 21a8 8 0 1116 0"/></svg>
            </a>
        </nav>
    </div>
</body>
</html>
