@extends('layouts.app')

@section('title', 'Inscription')

@section('body')
<div class="mx-auto flex min-h-screen max-w-md flex-col justify-center px-4 py-10">
    <div class="mb-8 text-center fade-up">
        <p class="text-3xl font-extrabold tracking-tight text-mivoo-ink">mivoo</p>
        <h1 class="mt-4 text-3xl font-extrabold text-mivoo-ink">Créer un compte</h1>
        <p class="mt-2 text-sm text-mivoo-muted">Rejoignez la plateforme de micro-tâches en Côte d’Ivoire.</p>
    </div>

    <x-ui.card class="fade-up fade-up-delay-1">
        @if ($errors->any())
            <x-ui.alert type="error" class="mb-4">
                <ul class="list-disc space-y-1 pl-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>
        @endif

        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-ui.input name="last_name" label="Nom" required />
                <x-ui.input name="first_name" label="Prénoms" required />
            </div>
            <x-ui.input name="email" type="email" label="Adresse email" required autocomplete="email" />
            <x-ui.input name="phone" type="tel" label="Téléphone" required placeholder="+225..." />
            <x-ui.input name="password" type="password" label="Mot de passe" required autocomplete="new-password" />
            <x-ui.input name="password_confirmation" type="password" label="Confirmation" required autocomplete="new-password" />
            <x-ui.input name="referral_code" label="Code de parrainage (optionnel)" :value="request('ref')" />

            <label class="flex items-start gap-2 text-sm text-mivoo-muted">
                <input type="checkbox" name="terms" value="1" class="mt-1 rounded border-mivoo-lilac text-mivoo-violet focus:ring-mivoo-violet" required>
                <span>J’accepte les conditions générales d’utilisation de Mivoo.</span>
            </label>

            <x-ui.button type="submit" class="w-full">Créer mon compte</x-ui.button>
        </form>
    </x-ui.card>

    <p class="mt-6 text-center text-sm text-mivoo-muted fade-up fade-up-delay-2">
        Déjà inscrit ?
        <a href="{{ route('login') }}" class="font-semibold text-mivoo-violet">Se connecter</a>
    </p>
</div>
@endsection
