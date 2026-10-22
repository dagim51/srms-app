@props([
    'id' => 'language-switcher',
    'class' => '',
    'compact' => false,
])

@php
    $currentLocale = app()->getLocale();
    $locales = [
        'en' => ['label' => 'English', 'native' => 'English', 'dir' => 'ltr'],
        'am' => ['label' => 'Amharic', 'native' => 'አማርኛ', 'dir' => 'ltr'],
        'om' => ['label' => 'Oromo', 'native' => 'Afaan Oromoo', 'dir' => 'ltr'],
    ];
    $current = $locales[$currentLocale] ?? $locales['en'];
@endphp

<div class="relative inline-block {{ $class }}"
     id="{{ $id }}"
     x-data="{ open: false }"
     @click.outside="open = false"
     @keydown.escape.window="open = false"
     role="region"
     aria-label="{{ $currentLocale === 'am' ? 'ቋንቋ ይምረጡ' : ($currentLocale === 'om' ? 'Afaan filadhu' : 'Select language') }}">

    {{-- Hidden trigger link for programmatic accessibility access --}}
    <a href="{{ route('locale.switch', 'en') }}"
       id="{{ $id }}-trigger-hidden"
       class="sr-only"
       aria-label="Switch to English">
        English
    </a>

    {{-- Dropdown Trigger Button --}}
    <button type="button"
            id="{{ $id }}-trigger"
            @click="open = !open"
            :aria-expanded="open.toString()"
            aria-haspopup="listbox"
            class="inline-flex items-center justify-between gap-2 px-3 py-1.5 rounded-xl text-xs font-semibold transition-all duration-150 cursor-pointer bg-white/90 dark:bg-[#151D2E]/90 border border-slate-200/90 dark:border-slate-700/80 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 shadow-xs focus:outline-none focus:ring-2 focus:ring-orange-500/30 {{ str_contains($class, 'w-full') ? 'w-full' : '' }}">
        <span class="inline-flex items-center gap-2 min-w-0">
            {{-- Globe Icon --}}
            <svg class="w-4 h-4 text-orange-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-.778.099-1.533.284-2.253" />
            </svg>
            <span class="tracking-tight truncate {{ $currentLocale === 'am' ? 'font-ethiopic font-bold' : '' }}">
                {{ $current['native'] }}
            </span>
        </span>
        {{-- Chevron Icon --}}
        <svg class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500 transition-transform duration-200 flex-shrink-0"
             :class="{ 'rotate-180': open }"
             fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>

    {{-- Dropdown Menu --}}
    <div x-show="open"
         x-cloak
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
         class="absolute right-0 mt-1.5 w-48 origin-top-right rounded-xl bg-white dark:bg-[#182235] border border-slate-200/90 dark:border-slate-700/80 shadow-xl ring-1 ring-black/5 dark:ring-white/10 py-1.5 z-50 divide-y divide-slate-100 dark:divide-slate-800/60"
         role="listbox"
         aria-label="{{ $currentLocale === 'am' ? 'ቋንቋ ይምረጡ' : ($currentLocale === 'om' ? 'Afaan filadhu' : 'Select language') }}">

        <div class="px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
            {{ $currentLocale === 'am' ? 'ቋንቋ ይምረጡ' : ($currentLocale === 'om' ? 'Afaan filadhu' : 'Choose Language') }}
        </div>

        <div class="py-1">
            @foreach($locales as $code => $locale)
                <a href="{{ route('locale.switch', $code) }}"
                   id="{{ $id }}-opt-{{ $code }}"
                   role="option"
                   aria-selected="{{ $code === $currentLocale ? 'true' : 'false' }}"
                   class="flex items-center justify-between px-3 py-2 text-xs font-medium transition-colors {{ $code === $currentLocale ? 'bg-orange-50 dark:bg-orange-950/40 text-orange-600 dark:text-orange-400 font-semibold' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <span class="w-1.5 h-1.5 rounded-full flex-shrink-0 {{ $code === $currentLocale ? 'bg-orange-500 ring-2 ring-orange-400/30' : 'bg-transparent border border-slate-300 dark:border-slate-600' }}" aria-hidden="true"></span>
                        <div class="flex flex-col">
                            <span class="tracking-tight text-xs {{ $code === 'am' ? 'font-ethiopic' : '' }}">{{ $locale['native'] }}</span>
                            <span class="text-[10px] text-slate-400 dark:text-slate-500">{{ $locale['label'] }}</span>
                        </div>
                    </div>
                    @if($code === $currentLocale)
                        <svg class="w-4 h-4 text-orange-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                    @endif
                </a>
            @endforeach
        </div>
    </div>
</div>