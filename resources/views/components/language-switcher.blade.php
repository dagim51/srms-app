@props([
    'id' => 'language-switcher',
    'class' => '',
    'compact' => false,
])

@php
    $currentLocale = app()->getLocale();
    $locales = config('app.supported_locales', [
        'en' => ['name' => 'English', 'native' => 'English', 'flag' => '🇺🇸'],
        'am' => ['name' => 'Amharic', 'native' => 'አማርኛ', 'flag' => '🇪🇹'],
    ]);
    $activeInfo = $locales[$currentLocale] ?? $locales['en'];
@endphp

<div x-data="{ open: false }"
     @click.outside="open = false"
     @keydown.escape.window="open = false"
     class="relative inline-block text-left {{ $class }}"
     id="{{ $id }}">

    {{-- Dropdown Trigger Button --}}
    <button type="button"
            @click="open = !open"
            id="{{ $id }}-trigger"
            aria-haspopup="true"
            :aria-expanded="open"
            aria-label="{{ $currentLocale === 'am' ? 'ቋንቋ ይምረጡ' : 'Select language' }}"
            class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-medium text-muted dark:text-dark-subtle hover:text-muted dark:hover:text-white hover:bg-surface-subtle dark:hover:bg-[#172033] border border-warm/80 dark:border-[#273244] transition-colors focus:outline-none focus:ring-2 focus:ring-orange-500/30 active:scale-95 cursor-pointer">
        
        {{-- Globe / Translate Icon --}}
        <svg class="w-4 h-4 text-muted dark:text-muted flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-.778.099-1.533.284-2.253" />
        </svg>

        {{-- Active Language Label --}}
        <span class="font-medium tracking-tight">
            {{ $compact ? ($currentLocale === 'am' ? 'አማ' : 'EN') : $activeInfo['native'] }}
        </span>

        {{-- Dropdown Chevron --}}
        <svg class="w-3.5 h-3.5 text-muted dark:text-muted transition-transform duration-200"
             :class="{ 'rotate-180': open }"
             fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
        </svg>
    </button>

    {{-- Dropdown Menu --}}
    <div x-show="open"
         x-transition:enter="transition duration-150 [transition-timing-function:cubic-bezier(0.23,1,0.32,1)]"
         x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
         class="absolute right-0 mt-1.5 w-44 origin-top-right rounded-xl bg-white dark:bg-[#111827] border border-warm dark:border-[#273244] shadow-xl shadow-black/10 dark:shadow-black/50 z-50 py-1.5 overflow-hidden"
         role="menu"
         aria-orientation="vertical"
         aria-labelledby="{{ $id }}-trigger"
         x-cloak>

        <div class="px-3 py-1 text-xs font-bold uppercase tracking-wider text-muted dark:text-muted border-b border-warm dark:border-[#273244] mb-1">
            {{ $currentLocale === 'am' ? 'ቋንቋ ይምረጡ' : 'Select Language' }}
        </div>

        @foreach($locales as $code => $info)
            @php $isSelected = ($currentLocale === $code); @endphp
            <a href="{{ route('locale.switch', $code) }}"
               role="menuitem"
               id="{{ $id }}-opt-{{ $code }}"
               class="w-full flex items-center justify-between px-3 py-2 text-xs font-medium transition-colors cursor-pointer text-left {{ $isSelected ? 'bg-orange-50 text-orange-600 dark:bg-orange-500/10 dark:text-orange-400 font-semibold' : 'text-muted dark:text-muted hover:bg-surface dark:hover:bg-[#172033]' }}">
                <div class="flex items-center gap-2">
                    <span class="text-sm leading-none" aria-hidden="true">{{ $info['flag'] }}</span>
                    <span>{{ $info['native'] }}</span>
                    @if($info['native'] !== $info['name'])
                        <span class="text-xs text-muted dark:text-muted">({{ $info['name'] }})</span>
                    @endif
                </div>
                @if($isSelected)
                    <svg class="w-4 h-4 text-orange-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                @endif
            </a>
        @endforeach
    </div>
</div>
