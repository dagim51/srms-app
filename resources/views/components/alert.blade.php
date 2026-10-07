@props([''type'' => ''info'', ''title'' => null, ''message'' => null])

@php
    $baseClasses = ''alert p-4 rounded-xl flex items-start gap-3 border '';
    $typeClasses = match($type) {
        ''success'' => ''bg-green-50/50 border-green-200 text-green-800 dark:bg-green-900/10 dark:border-green-800/30 dark:text-green-400'',
        ''error'' => ''bg-red-50/50 border-red-200 text-red-800 dark:bg-red-900/10 dark:border-red-800/30 dark:text-red-400'',
        ''warning'' => ''bg-orange-50/50 border-orange-200 text-orange-800 dark:bg-orange-900/10 dark:border-orange-800/30 dark:text-orange-400'',
        default => ''bg-blue-50/50 border-blue-200 text-blue-800 dark:bg-blue-900/10 dark:border-blue-800/30 dark:text-blue-400'',
    };

    $icon = match($type) {
        ''success'' => ''<svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>'',
        ''error'' => ''<svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>'',
        ''warning'' => ''<svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>'',
        default => ''<svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" /></svg>'',
    };
@endphp

<div {{ $attributes->merge([''class'' => $baseClasses . $typeClasses]) }} role="alert">
    {!! $icon !!}
    <div>
        @if($title)
            <h4 class="font-semibold mb-0.5">{{ $title }}</h4>
        @endif
        <div class="text-sm opacity-90">
            {{ $message ?? $slot }}
        </div>
    </div>
</div>
