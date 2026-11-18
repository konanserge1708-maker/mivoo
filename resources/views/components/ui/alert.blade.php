@props([
    'type' => 'info',
])

@php
    $classes = match ($type) {
        'error' => 'bg-mivoo-danger/10 text-mivoo-danger',
        'success' => 'bg-mivoo-success/10 text-mivoo-success',
        'warning' => 'bg-amber-100 text-amber-900',
        default => 'bg-mivoo-violet/10 text-mivoo-violet-deep',
    };
@endphp

<div {{ $attributes->class(['rounded-2xl px-4 py-3 text-sm', $classes]) }}>
    {{ $slot }}
</div>
