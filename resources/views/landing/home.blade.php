@extends('layouts.app')

@section('title', 'Accueil')
@section('meta_description', 'Mivoo — plate-forme de micro-tâches rémunérées en Franc CFA pour la Côte d’Ivoire.')

@section('body')
<div class="relative overflow-hidden">
    <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(239,199,168,0.35),transparent_35%),radial-gradient(circle_at_80%_0%,rgba(107,92,158,0.22),transparent_40%)]"></div>

    <header class="relative mx-auto flex max-w-6xl items-center justify-between px-4 py-6 lg:px-8">
        <p class="text-2xl font-extrabold tracking-tight text-mivoo-ink">mivoo</p>
        <div class="flex items-center gap-2">
            <x-ui.button href="{{ route('login') }}" variant="secondary">Connexion</x-ui.button>
            <x-ui.button href="{{ route('register') }}">S’inscrire</x-ui.button>
        </div>
    </header>

    <main class="relative mx-auto max-w-6xl px-4 pb-20 pt-10 lg:px-8 lg:pt-16">
        <section class="fade-up max-w-3xl">
            <p class="mb-4 text-sm font-semibold uppercase tracking-[0.2em] text-mivoo-violet">Côte d’Ivoire · XOF</p>
            <h1 class="text-5xl font-extrabold leading-[1.05] tracking-tight text-mivoo-ink sm:text-6xl lg:text-7xl">
                mivoo
            </h1>
            <p class="mt-5 max-w-xl text-lg text-mivoo-muted">
                Réalisez des micro-tâches numériques financées par des partenaires et suivez vos gains dans un portefeuille clair.
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <x-ui.button href="{{ route('register') }}">Commencer gratuitement</x-ui.button>
                <x-ui.button href="#vip" variant="secondary">Voir les VIP</x-ui.button>
            </div>
        </section>

        <section class="mt-16 grid gap-4 sm:grid-cols-3 fade-up fade-up-delay-1">
            <x-ui.stat-card label="Missions / jour" value="10" tone="lilac">
                <x-slot:hint>plafond configurable</x-slot:hint>
            </x-ui.stat-card>
            <x-ui.stat-card label="Parrainage" value="10%" tone="peach">
                <x-slot:hint>sur gains validés</x-slot:hint>
            </x-ui.stat-card>
            <x-ui.stat-card label="VIP" value="90 j" tone="sky">
                <x-slot:hint>durée d’abonnement</x-slot:hint>
            </x-ui.stat-card>
        </section>

        <section id="vip" class="mt-20 fade-up fade-up-delay-2">
            <h2 class="text-3xl font-extrabold text-mivoo-ink">Niveaux VIP</h2>
            <p class="mt-2 max-w-2xl text-mivoo-muted">
                Les abonnements élèvent le plafond journalier. Ils ne garantissent ni disponibilité des tâches ni revenus.
            </p>

            <div class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                @foreach ($plans as $plan)
                    <div class="vip-card {{ $plan->toneClass() }}">
                        <span class="vip-material">{{ $plan->materialLabel() }}</span>
                        <p class="vip-title text-sm font-semibold">{{ $plan->name }}</p>
                        <p class="vip-price mt-3 text-3xl font-extrabold">{{ $plan->formattedPrice() }}</p>
                        <p class="vip-muted mt-2 text-sm">Plafond / jour : {{ number_format($plan->daily_cap_xof, 0, ',', ' ') }} F</p>
                        <p class="vip-muted mt-1 text-sm">Valeur indicative / tâche : {{ number_format($plan->indicative_task_value_xof, 0, ',', ' ') }} F</p>
                    </div>
                @endforeach
            </div>
        </section>
    </main>
</div>
@endsection
