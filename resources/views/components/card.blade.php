@props([
    'variant' => 'default', // 'default', 'stat', 'nested', 'interactive', 'elevated'
    'padding' => null,
    'header' => null,
    'footer' => null,
])

@php
    $variantClasses = [
        'default' => 'card',
        'stat' => 'card-stat',
        'nested' => 'card-nested',
        'interactive' => 'card card-interactive',
        'elevated' => 'card card-hover',
    ];

    $defaultPadding = [
        'default' => 'p-6',
        'stat' => 'p-5 sm:p-6',
        'nested' => 'p-4 sm:p-5',
        'interactive' => 'p-6',
        'elevated' => 'p-6',
    ];

    $baseClass = $variantClasses[$variant] ?? $variantClasses['default'];
    $padding = $padding ?? $defaultPadding[$variant] ?? $defaultPadding['default'];
    $classes = $baseClass . ' ' . $padding;
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    @if ($header)
        <div class="card-header -mx-6 -mt-6 mb-6">
            {{ $header }}
        </div>
    @endif

    {{ $slot }}

    @if ($footer)
        <div class="card-footer -mx-6 -mb-6 mt-6 px-6 py-4 border-t border-slate-200/80 dark:border-slate-800/80 bg-slate-50/70 dark:bg-slate-900/40">
            {{ $footer }}
        </div>
    @endif
</div>