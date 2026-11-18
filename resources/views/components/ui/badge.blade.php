@props([
    'tone' => 'dark',
    'size' => 'md',
])

@php
    $toneClasses = match ($tone) {
        'soft' => 'bg-mivoo-soft text-mivoo-muted',
        'violet' => 'bg-mivoo-violet/15 text-mivoo-violet-deep',
        'success' => 'bg-mivoo-success/15 text-mivoo-success',
        'red' => 'text-white',
        'blue' => 'text-white',
        default => 'bg-mivoo-ink text-white',
    };

    $toneStyle = match ($tone) {
        'red' => 'background-color:#e45757;color:#fff;',
        'blue' => 'background-color:#2563eb;color:#fff;',
        default => null,
    };

    $sizeClasses = match ($size) {
        'lg' => 'px-4 py-2 text-sm',
        'sm' => 'px-2.5 py-0.5 text-[11px]',
        default => 'px-3 py-1.5 text-[13px]',
    };
@endphp

<span
    @if ($toneStyle) style="{{ $toneStyle }}" @endif
    {{ $attributes->class(['inline-flex items-center rounded-full font-semibold', $toneClasses, $sizeClasses]) }}
>
    {{ $slot }}
</span>
