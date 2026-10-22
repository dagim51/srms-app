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

    $iconMap = [
        'home' => '<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" /></svg>',
        'calendar' => '<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" /></svg>',
        'document' => '<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>',
        'key' => '<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" /></svg>',
        'search' => '<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>',
    ];
@endphp

<div class="empty-state {{ $sizeClasses[$size] ?? $sizeClasses['default'] }}">
    @if($icon)
        <div class="w-14 h-14 rounded-2xl bg-orange-50 dark:bg-orange-950/40 text-orange-600 dark:text-orange-400 border border-orange-200/50 dark:border-orange-800/40 shadow-xs shadow-orange-500/10 flex items-center justify-center mx-auto mb-4">
            {!! $iconMap[$icon] ?? $iconMap['home'] !!}
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