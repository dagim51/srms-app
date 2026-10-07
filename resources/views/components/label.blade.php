@props([''value'', ''required'' => false])

<label {{ $attributes->merge([''class'' => ''form-label '' . ($required ? ''form-label-required'' : '''')]) }}>
    {{ $value ?? $slot }}
</label>
