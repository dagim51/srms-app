@props([
    'icon' => null,
    'title' => '',
    'description' => '',
    'action' => null,
    'actionText' => null,
    'actionVariant' => 'primary',
    'size' => 'default',
])

@php
    $sizeClasses = [
        'sm' => 'py-4 px-3',
        'default' => 'py-8 px-4',
        'lg' => 'py-12 px-6',
    ];
    $iconSizes = [
        'sm' => 'w-8 h-8',
        'default' => 'w-12 h-12',
        'lg' => 'w-16 h-16',
    ];
    $titleSizes = [
        'sm' => 'text-sm',
        'default' => 'text-base',
        'lg' => 'text-lg',
    ];
    $descSizes = [
        'sm' => 'text-xs',
        'default' => 'text-sm',
        'lg' => 'text-base',
    ];
@endphp

<div class="empty-state {{ $sizeClasses[$size] ?? $sizeClasses['default'] }}">
    @if($icon)
        <div class="w-14 h-14 rounded-2xl bg-orange-50 dark:bg-orange-950/40 text-orange-600 dark:text-orange-400 border border-orange-200/50 dark:border-orange-800/40 shadow-xs shadow-orange-500/10 flex items-center justify-center mx-auto mb-4">
            @svg($icon, $iconSizes[$size] ?? $iconSizes['default'])
        </div>
    @endif
    @if($title)
        <h4 class="font-display font-bold text-slate-900 dark:text-white {{ $titleSizes[$size] ?? $titleSizes['default'] }} mb-1">
            {{ $title }}
        </h4>
    @endif
    @if($description)
        <p class="text-slate-500 dark:text-slate-400 {{ $descSizes[$size] ?? $descSizes['default'] }} mb-5 max-w-xs mx-auto">
            {{ $description }}
        </p>
    @endif

    {{-- Support both legacy props and slots --}}
    @if(isset($actions) && $actions->isNotEmpty())
        <div class="flex flex-wrap items-center justify-center gap-3">
            {{ $actions }}
        </div>
    @elseif($action && $actionText)
        <x-button variant="{{ $actionVariant }}" size="sm" href="{{ $action }}">
            {{ $actionText }}
        </x-button>
    @endif

    {{-- Allow arbitrary additional content after actions --}}
    {{ $slot }}
</div>