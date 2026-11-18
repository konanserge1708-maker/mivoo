@props([
    'title' => 'Administration',
])

@php
    $nav = [
        ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'match' => 'admin.dashboard'],
        ['label' => 'Missions', 'route' => 'admin.campaigns.index', 'match' => 'admin.campaigns.*'],
        ['label' => 'Validations', 'route' => 'admin.tasks.index', 'match' => 'admin.tasks.*'],
        ['label' => 'Utilisateurs', 'route' => 'admin.users.index', 'match' => 'admin.users.*'],
        ['label' => 'Retraits', 'route' => 'admin.withdrawals.index', 'match' => 'admin.withdrawals.*'],
        ['label' => 'Support', 'route' => 'admin.support.index', 'match' => 'admin.support.*'],
        ['label' => 'Plans VIP', 'route' => 'admin.subscription-plans.index', 'match' => 'admin.subscription-plans.*'],
        ['label' => 'Paramètres', 'route' => 'admin.settings.edit', 'match' => 'admin.settings.*'],
    ];
@endphp

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} — Mivoo Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .admin-drawer {
            position: fixed;
            inset: 0 auto 0 0;
            z-index: 50;
            width: min(18rem, 86vw);
            padding: 1rem;
            transform: translateX(-110%);
            transition: transform 320ms cubic-bezier(0.22, 1, 0.36, 1);
            pointer-events: none;
        }
        .admin-drawer.is-open {
            transform: translateX(0);
            pointer-events: auto;
        }
        .admin-drawer-panel {
            display: flex;
            height: 100%;
            flex-direction: column;
            border-radius: 1.75rem;
            background: #fff;
            padding: 1.25rem;
            box-shadow: 0 18px 40px rgba(83, 69, 127, 0.16);
        }
        .admin-drawer-overlay {
            position: fixed;
            inset: 0;
            z-index: 40;
            background: rgba(20, 19, 26, 0.42);
            backdrop-filter: blur(2px);
            opacity: 0;
            visibility: hidden;
            transition: opacity 240ms ease, visibility 240ms ease;
        }
        .admin-drawer-overlay.is-open {
            opacity: 1;
            visibility: visible;
        }
        @media (min-width: 1024px) {
            .admin-drawer,
            .admin-drawer-overlay,
            .admin-menu-btn {
                display: none !important;
            }
        }
    </style>
</head>
<body class="min-h-screen">
    <div id="admin-sidebar-overlay" class="admin-drawer-overlay lg:hidden" aria-hidden="true"></div>

    <aside id="admin-sidebar-drawer" class="admin-drawer lg:hidden" aria-hidden="true">
        <div class="admin-drawer-panel">
            <div class="mb-6 flex items-center justify-between gap-3">
                <div>
                    <p class="text-2xl font-extrabold tracking-tight text-mivoo-ink">mivoo</p>
                    <p class="mt-1 text-xs font-semibold uppercase tracking-[0.18em] text-mivoo-muted">Admin</p>
                </div>
                <button type="button" id="admin-sidebar-close" class="flex h-10 w-10 items-center justify-center rounded-full bg-mivoo-soft text-mivoo-ink" aria-label="Fermer le menu">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <nav class="space-y-1.5 overflow-y-auto">
                @foreach ($nav as $item)
                    @php $active = request()->routeIs($item['match']); @endphp
                    <a href="{{ route($item['route']) }}"
                       class="flex items-center rounded-2xl px-4 py-3 text-sm font-semibold transition {{ $active ? 'bg-mivoo-ink text-white' : 'text-mivoo-muted hover:bg-mivoo-soft hover:text-mivoo-ink' }}">
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>

            <div class="mt-auto border-t border-mivoo-soft pt-4">
                <p class="truncate text-sm font-semibold text-mivoo-ink">{{ auth()->user()?->name }}</p>
                <p class="truncate text-xs text-mivoo-muted">{{ auth()->user()?->email }}</p>
                <form method="POST" action="{{ route('logout') }}" class="mt-3">
                    @csrf
                    <button type="submit" class="mivoo-btn mivoo-btn-secondary w-full !py-2.5 text-xs">Déconnexion</button>
                </form>
            </div>
        </div>
    </aside>

    <div class="mx-auto flex min-h-screen max-w-[1400px] gap-0 lg:gap-8 lg:px-6 lg:py-6">
        <aside class="hidden w-64 shrink-0 lg:flex lg:flex-col">
            <div class="rounded-[1.75rem] bg-white/90 p-5 shadow-mivoo backdrop-blur-sm">
                <p class="text-2xl font-extrabold tracking-tight text-mivoo-ink">mivoo</p>
                <p class="mt-1 text-xs font-semibold uppercase tracking-[0.18em] text-mivoo-muted">Admin</p>

                <nav class="mt-8 space-y-1.5">
                    @foreach ($nav as $item)
                        @php $active = request()->routeIs($item['match']); @endphp
                        <a href="{{ route($item['route']) }}"
                           class="flex items-center rounded-2xl px-4 py-3 text-sm font-semibold transition {{ $active ? 'bg-mivoo-ink text-white' : 'text-mivoo-muted hover:bg-mivoo-soft hover:text-mivoo-ink' }}">
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </nav>

                <div class="mt-8 border-t border-mivoo-soft pt-4">
                    <p class="truncate text-sm font-semibold text-mivoo-ink">{{ auth()->user()?->name }}</p>
                    <p class="truncate text-xs text-mivoo-muted">{{ auth()->user()?->email }}</p>
                    <form method="POST" action="{{ route('logout') }}" class="mt-3">
                        @csrf
                        <button type="submit" class="mivoo-btn mivoo-btn-secondary w-full !py-2.5 text-xs">Déconnexion</button>
                    </form>
                </div>
            </div>
        </aside>

        <div class="min-w-0 flex-1 px-4 py-6 lg:px-0 lg:py-0">
            <header class="mb-6 flex items-center justify-between gap-3 lg:hidden">
                <div class="flex items-center gap-3">
                    <button type="button"
                            id="admin-sidebar-open"
                            class="admin-menu-btn flex h-11 w-11 items-center justify-center rounded-full bg-white/90 text-mivoo-ink shadow-sm"
                            aria-label="Ouvrir le menu"
                            aria-expanded="false"
                            aria-controls="admin-sidebar-drawer">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16"/></svg>
                    </button>
                    <div>
                        <p class="text-xl font-extrabold text-mivoo-ink">mivoo admin</p>
                        <p class="text-xs text-mivoo-muted">{{ $title }}</p>
                    </div>
                </div>
            </header>

            @if (session('status'))
                <x-ui.alert type="success" class="mb-5">{{ session('status') }}</x-ui.alert>
            @endif
            @if ($errors->any())
                <x-ui.alert type="error" class="mb-5">{{ $errors->first() }}</x-ui.alert>
            @endif

            {{ $slot }}
        </div>
    </div>

    <script>
        (() => {
            const openBtn = document.getElementById('admin-sidebar-open');
            const closeBtn = document.getElementById('admin-sidebar-close');
            const drawer = document.getElementById('admin-sidebar-drawer');
            const overlay = document.getElementById('admin-sidebar-overlay');
            if (!openBtn || !drawer || !overlay) return;

            const setOpen = (open) => {
                drawer.classList.toggle('is-open', open);
                overlay.classList.toggle('is-open', open);
                drawer.setAttribute('aria-hidden', open ? 'false' : 'true');
                overlay.setAttribute('aria-hidden', open ? 'false' : 'true');
                openBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
                document.body.classList.toggle('overflow-hidden', open);
            };

            setOpen(false);
            openBtn.addEventListener('click', () => setOpen(true));
            closeBtn?.addEventListener('click', () => setOpen(false));
            overlay.addEventListener('click', () => setOpen(false));
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') setOpen(false);
            });
        })();
    </script>
</body>
</html>
