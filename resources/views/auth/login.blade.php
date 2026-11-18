@extends('layouts.app')

@section('title', 'Connexion')

@section('body')
<div class="mx-auto flex min-h-screen max-w-md flex-col justify-center px-4 py-10">
    <div class="mb-8 text-center fade-up">
        <p class="text-3xl font-extrabold tracking-tight text-mivoo-ink">mivoo</p>
        <h1 class="mt-4 text-3xl font-extrabold text-mivoo-ink">Bon retour</h1>
        <p class="mt-2 text-sm text-mivoo-muted">Connectez-vous pour continuer vos missions.</p>
    </div>

    <x-ui.card class="fade-up fade-up-delay-1">
        @if ($errors->any())
            <x-ui.alert type="error" class="mb-4">{{ $errors->first() }}</x-ui.alert>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf
            <x-ui.input name="email" type="email" label="Adresse email" :value="old('email')" required autocomplete="email" />
            <x-ui.input name="password" type="password" label="Mot de passe" required autocomplete="current-password" />

            <label class="flex items-center gap-2 text-sm text-mivoo-muted">
                <input type="checkbox" name="remember" value="1" class="rounded border-mivoo-lilac text-mivoo-violet focus:ring-mivoo-violet">
                Se souvenir de moi
            </label>

            <x-ui.button type="submit" class="w-full">Se connecter</x-ui.button>
        </form>
    </x-ui.card>

    <p class="mt-6 text-center text-sm text-mivoo-muted fade-up fade-up-delay-2">
        Pas encore de compte ?
        <a href="{{ route('register') }}" class="font-semibold text-mivoo-violet">Créer un compte</a>
    </p>
</div>
@endsection
