@props([
    'disabled' => false,
    'error' => false,
])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'form-input ' . ($error ? 'input-error' : '')]) !!}>
