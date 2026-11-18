@props([
    'padding' => true,
])

<div {{ $attributes->class(['mivoo-card', 'p-5' => $padding]) }}>
    {{ $slot }}
</div>
