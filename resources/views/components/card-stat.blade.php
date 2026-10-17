@props([
    'label' => '',
    'value' => '0',
    'caption' => null,
    'badge' => null,
    'badgeVariant' => 'neutral', // 'brand', 'success', 'warning', 'error', 'info', 'neutral'
    'iconColor' => 'orange', // 'orange', 'emerald', 'blue', 'purple', 'rose', 'amber', 'indigo', 'teal', 'gray'
    'icon' => null,
    'href' => null,
    'xTextValue' => null,
])

@php
    $iconThemes = [
        'orange'  => 'bg-gradient-to-tr from-orange-500 to-amber-400 text-white shadow-md shadow-orange-500/30 ring-1 ring-orange-300/40',
        'emerald' => 'bg-gradient-to-tr from-emerald-500 to-teal-400 text-white shadow-md shadow-emerald-500/30 ring-1 ring-emerald-300/40',
        'blue'    => 'bg-gradient-to-tr from-blue-600 to-sky-400 text-white shadow-md shadow-blue-500/30 ring-1 ring-sky-300/40',
        'purple'  => 'bg-gradient-to-tr from-purple-600 to-pink-500 text-white shadow-md shadow-purple-500/30 ring-1 ring-purple-300/40',
        'rose'    => 'bg-gradient-to-tr from-rose-500 to-red-400 text-white shadow-md shadow-rose-500/30 ring-1 ring-rose-300/40',
        'amber'   => 'bg-gradient-to-tr from-amber-500 to-yellow-400 text-white shadow-md shadow-amber-500/30 ring-1 ring-amber-300/40',
        'indigo'  => 'bg-gradient-to-tr from-indigo-600 to-violet-400 text-white shadow-md shadow-indigo-500/30 ring-1 ring-indigo-300/40',
        'teal'    => 'bg-gradient-to-tr from-teal-500 to-cyan-400 text-white shadow-md shadow-teal-500/30 ring-1 ring-teal-300/40',
        'gray'    => 'bg-gradient-to-tr from-slate-600 to-slate-400 text-white shadow-md shadow-slate-500/25 ring-1 ring-slate-400/40',
    ];

    $glowMap = [
        'orange'  => 'card-glow-orange',
        'emerald' => 'card-glow-emerald',
        'blue'    => 'card-glow-sky',
        'purple'  => 'card-glow-violet',
        'rose'    => 'card-glow-rose',
        'amber'   => 'card-glow-amber',
        'indigo'  => 'card-glow-violet',
        'teal'    => 'card-glow-emerald',
        'gray'    => '',
    ];

    $iconClasses = $iconThemes[$iconColor] ?? $iconThemes['orange'];
    $glowClass = $glowMap[$iconColor] ?? 'card-glow-orange';

    // Standardized Colorful Icons Library
    $iconMap = [
        'banknotes' => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6H2.25m0 0H3m0 0h18m0 0h.75A.75.75 0 0122 6.75v.75m0 0h-.75m0 0a60.07 60.07 0 00-15.797-2.101c-.727-.198-1.453.342-1.453 1.096V4.5M3.75 4.5v11.25c0 .621.504 1.125 1.125 1.125h14.25c.621 0 1.125-.504 1.125-1.125V4.5M12 12.75a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z" /></svg>',
        'chart-pie' => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 107.5 7.5h-7.5V6z" /><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0013.5 3v7.5z" /></svg>',
        'arrow-trending-up' => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" /></svg>',
        'users' => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>',
        'building' => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.75c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12l3 3.75H21" /></svg>',
        'document' => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>',
        'check' => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
        'clock' => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
        'sparkles' => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z" /></svg>',
        'wrench' => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.07a4.5 4.5 0 004.486-6.32l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.32 4.486c.118.58.094 1.193-.07 1.743" /></svg>',
        'database' => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 5.625c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" /></svg>',
        'heart' => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" /></svg>',
        'calendar' => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" /></svg>',
        'tag' => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.386l5.429-3.194c.828-.487 1.09-1.536.577-2.302L12.44 3.914A2.25 2.25 0 0010.84 3H9.568z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" /></svg>',
        'cube' => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" /></svg>',
        'layers' => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.429 9.75L2.25 12l9.75 5.25 9.75-5.25-4.179-2.25m-11.321 4.5L2.25 16.5l9.75 5.25 9.75-5.25-4.179-2.25m-11.321-9L12 2.25l9.75 5.25-9.75 5.25L2.25 7.5z" /></svg>',
        'key' => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" /></svg>',
        'eye' => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>',
        'shield-check' => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg>',
        'map-pin' => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>',
    ];

    $fallbackByColor = [
        'orange'  => $iconMap['building'],
        'emerald' => $iconMap['check'],
        'blue'    => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5m.75-9l3-3 2.143 2.143L15.75 6" /></svg>',
        'purple'  => $iconMap['calendar'],
        'rose'    => '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>',
        'amber'   => $iconMap['clock'],
        'indigo'  => $iconMap['chart-pie'],
        'teal'    => $iconMap['shield-check'],
        'gray'    => $iconMap['document'],
    ];

    $hasSlot = isset($icon) && (
        (is_object($icon) && method_exists($icon, 'isNotEmpty') && $icon->isNotEmpty()) ||
        ($icon instanceof \Illuminate\Support\HtmlString && !empty((string) $icon))
    );

    $renderedSvg = null;
    if (!$hasSlot) {
        if (is_string($icon) && !empty($icon)) {
            $trimmed = trim($icon);
            if (str_starts_with($trimmed, '<svg')) {
                $renderedSvg = $trimmed;
            } else {
                $normalized = str_replace('heroicon-o-', '', $trimmed);
                $renderedSvg = $iconMap[$normalized] ?? $iconMap[$trimmed] ?? ($fallbackByColor[$iconColor] ?? $fallbackByColor['orange']);
            }
        } else {
            $renderedSvg = $fallbackByColor[$iconColor] ?? $fallbackByColor['orange'];
        }
    }
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => "card card-stat stat-card-luxury {$glowClass} block text-left cursor-pointer group active:scale-[0.98] transition-all duration-200"]) }}>
@else
    <div {{ $attributes->merge(['class' => "card card-stat stat-card-luxury {$glowClass} block text-left group transition-all duration-200"]) }}>
@endif
    <div>
        <div class="flex items-center justify-between gap-2 mb-3">
            <div class="stat-icon-wrapper w-10 h-10 rounded-xl flex items-center justify-center shrink-0 transition-transform duration-200 ease-out group-hover:scale-105 {{ $iconClasses }}">
                @if ($hasSlot)
                    {{ $icon }}
                @else
                    {!! $renderedSvg !!}
                @endif
            </div>

            @if ($badge)
                <span class="badge badge-{{ $badgeVariant }} text-[11px] font-semibold ml-auto px-2 py-0.5 rounded-full">
                    {{ __($badge) }}
                </span>
            @endif
        </div>

        <p class="card-stat-label text-slate-500 dark:text-slate-400 text-xs font-semibold uppercase tracking-wider">
            {{ __($label) }}
        </p>
        <p class="card-stat-value text-slate-900 dark:text-white font-display font-extrabold text-2xl sm:text-3xl tracking-[-0.02em] leading-tight my-1 truncate"
           @if($xTextValue) x-text="{{ $xTextValue }}" @endif>
            {{ $value }}
        </p>
    </div>

    @if ($caption)
        <p class="card-stat-caption text-slate-400 dark:text-slate-500 mt-2.5 pt-2 border-t border-slate-100 dark:border-slate-800/80 text-[11px] flex items-center justify-between">
            <span>{{ __($caption) }}</span>
        </p>
    @endif

@if ($href)
    </a>
@else
    </div>
@endif
