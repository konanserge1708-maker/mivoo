@props([
    'title' => 'Rien à afficher',
    'description' => null,
])

<div {{ $attributes->class(['mivoo-card flex flex-col items-center px-6 py-12 text-center']) }}>
    <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-mivoo-lilac/60 text-mivoo-violet">
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m6-2a10 10 0 11-20 0 10 10 0 0120 0z" />
        </svg>
    </div>
    <h3 class="text-lg font-bold text-mivoo-ink">{{ $title }}</h3>
    @if ($description)
        <p class="mt-2 max-w-sm text-sm text-mivoo-muted">{{ $description }}</p>
    @endif
    @isset($action)
        <div class="mt-5">{{ $action }}</div>
    @endisset
</div>
