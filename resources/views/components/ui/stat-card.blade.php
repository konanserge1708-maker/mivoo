@props([
    'label',
    'value',
    'tone' => 'lilac',
])

@php
    $toneClass = match ($tone) {
        'peach' => 'bg-mivoo-peach/80',
        'sky' => 'bg-mivoo-sky/80',
        'white' => 'bg-white',
        default => 'bg-mivoo-lilac/70',
    };
@endphp

<div {{ $attributes->class(['mivoo-stat', $toneClass]) }}>
    <p class="text-sm font-medium text-mivoo-muted">{{ $label }}</p>
    <p class="mt-4 text-4xl font-extrabold tracking-tight text-mivoo-ink">{{ $value }}</p>
    @isset($hint)
        <p class="mt-2 text-xs text-mivoo-muted">{{ $hint }}</p>
    @endisset
</div>
