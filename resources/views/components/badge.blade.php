@props([
    'variant' => 'neutral',
    'dot' => false,
    'icon' => null,
    'uppercase' => true,
    'size' => 'default',
])

@php
    $variantClasses = [
        'brand' => 'badge-brand',
        'success' => 'badge-success',
        'warning' => 'badge-warning',
        'error' => 'badge-error',
        'danger' => 'badge-error',
        'info' => 'badge-info',
        'neutral' => 'badge-neutral',
        'buy' => 'badge-cat-buy',
        'rent' => 'badge-cat-rent',
        'stay' => 'badge-cat-stay',
        'land' => 'badge-cat-land',
        'project' => 'badge-cat-project',
        'service' => 'badge-cat-service',
    ];

    $sizeClasses = [
        'sm' => 'text-xs px-2 py-0.5',
        'default' => 'text-xs px-2.5 py-0.5',
        'lg' => 'text-sm px-3 py-1',
    ];

    $classes = 'badge ' . ($variantClasses[$variant] ?? $variantClasses['neutral']) . ' ' . ($sizeClasses[$size] ?? $sizeClasses['default']);
    if ($uppercase) {
        $classes .= ' uppercase tracking-wider';
    }
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    @if($dot)
        <span class="w-1.5 h-1.5 rounded-full bg-current mr-1.5 flex-shrink-0"></span>
    @endif
    @if($icon)
        @svg($icon, 'w-3 h-3 mr-1.5')
    @endif
    {{ $slot }}
</span>