@props([
    'variant' => 'primary',
    'size' => 'md',
    'icon' => null,
    'type' => 'button',
    'href' => null,
    'loading' => false,
    'block' => false,
])

@php
    $variantClasses = match ($variant) {
        'primary' => 'btn-primary',
        'secondary' => 'btn-secondary',
        'ghost' => 'btn-ghost',
        'danger' => 'btn-danger',
        'outline' => 'btn-outline',
        'link' => 'btn-link',
        default => 'btn-primary',
    };

    $sizeClasses = match ($size) {
        'sm' => 'btn-sm',
        'md' => '',
        'lg' => 'btn-lg',
        'xl' => 'btn-xl',
        'icon' => 'btn-icon',
        default => '',
    };

    $iconSizes = [
        'sm' => 'w-3.5 h-3.5',
        'md' => 'w-4 h-4',
        'lg' => 'w-5 h-5',
        'xl' => 'w-5 h-5',
        'icon' => 'w-5 h-5',
    ];

    $classes = 'btn ' . $variantClasses . ' ' . $sizeClasses . ($block ? ' w-full' : '');
    $iconClass = $iconSizes[$size] ?? 'w-4 h-4';
@endphp

@if ($href)
    <a href="{{ $href }}"
       {{ $attributes->merge(['class' => $classes, 'aria-disabled' => $loading ? 'true' : 'false']) }}>
        @if ($loading)
            <svg class="{{ $iconClass }} mr-2 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>{{ __('Loading...') }}</span>
        @else
            @if ($icon)
                @svg($icon, $iconClass . ' mr-2')
            @endif
            {{ $slot }}
        @endif
    </a>
@else
    <button type="{{ $type }}"
            {{ $attributes->merge(['class' => $classes, 'disabled' => $loading ? 'disabled' : '']) }}>
        @if ($loading)
            <svg class="{{ $iconClass }} mr-2 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>{{ __('Loading...') }}</span>
        @else
            @if ($icon)
                @svg($icon, $iconClass . ' mr-2')
            @endif
            {{ $slot }}
        @endif
    </button>
@endif