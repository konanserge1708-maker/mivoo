@props([
    'label' => null,
    'name',
    'type' => 'text',
    'value' => null,
])

<div {{ $attributes->only('class')->class(['w-full']) }}>
    @if ($label)
        <label for="{{ $name }}" class="mivoo-label">{{ $label }}</label>
    @endif

    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ old($name, $value) }}"
        {{ $attributes->except('class')->class(['mivoo-input', 'border-mivoo-danger/40' => $errors->has($name)]) }}
    >

    @error($name)
        <p class="mt-2 text-xs text-mivoo-danger">{{ $message }}</p>
    @enderror
</div>
