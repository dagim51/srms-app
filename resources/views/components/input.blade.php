@props([
    'type' => 'text',
    'disabled' => false,
    'error' => false,
    'label' => null,
    'helper' => null,
    'required' => false,
    'icon' => null,
])

@php
    $inputId = $attributes->get('id') ?? 'input-' . uniqid();
    $classes = 'form-input ' . ($icon ? 'pl-10 ' : '') . ($error ? 'input-error' : '');
@endphp

<div class="w-full">
    @if($label)
        <label for="{{ $inputId }}" class="form-label {{ $required ? 'form-label-required' : '' }}">
            {{ $label }}
        </label>
    @endif

    <div class="relative">
        @if($icon)
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                @svg($icon, 'w-4 h-4')
            </div>
        @endif

        <input type="{{ $type }}"
               id="{{ $inputId }}"
               {{ $disabled ? 'disabled' : '' }}
               {{ $attributes->merge(['class' => $classes]) }}
               @if($required) required @endif>
    </div>

    @if($error)
        <p class="form-error" role="alert">
            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" /></svg>
            {{ $error }}
        </p>
    @elseif($helper)
        <p class="form-helper">{{ $helper }}</p>
    @endif
</div>