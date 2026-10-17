@props([
    'id' => 'language-switcher',
    'class' => '',
    'compact' => false,
])

@php
    $currentLocale = app()->getLocale();
    $isAm = ($currentLocale === 'am');
    $oppositeLocale = $isAm ? 'en' : 'am';
@endphp

<div class="language-switcher-pill inline-flex items-center p-1 rounded-xl bg-slate-100/90 dark:bg-slate-900/80 border border-slate-200/80 dark:border-slate-800/80 shadow-xs transition-colors {{ $class }}"
     id="{{ $id }}"
     role="group"
     aria-label="{{ $isAm ? 'ቋንቋ ይምረጡ' : 'Select language' }}">

    {{-- Programmatic trigger fallback link for test and automation compatibility --}}
    <a href="{{ route('locale.switch', $oppositeLocale) }}"
       id="{{ $id }}-trigger"
       class="sr-only"
       aria-label="{{ $isAm ? 'ወደ እንግሊዝኛ ቀይር' : 'Switch to Amharic' }}">
        {{ $isAm ? 'English' : 'አማርኛ' }}
    </a>

    {{-- English Option / Segment --}}
    <a href="{{ route('locale.switch', 'en') }}"
       id="{{ $id }}-opt-en"
       role="button"
       aria-pressed="{{ !$isAm ? 'true' : 'false' }}"
       title="Switch to English"
       class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-semibold transition-all duration-200 active:scale-[0.98] cursor-pointer {{ !$isAm ? 'bg-white dark:bg-[#151D2E] text-orange-600 dark:text-orange-400 shadow-xs font-bold ring-1 ring-black/5 dark:ring-white/10' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white' }}">
        <span class="w-1.5 h-1.5 rounded-full {{ !$isAm ? 'bg-orange-500' : 'bg-transparent' }}" aria-hidden="true"></span>
        <span class="tracking-tight">English</span>
    </a>

    {{-- Amharic Option / Segment --}}
    <a href="{{ route('locale.switch', 'am') }}"
       id="{{ $id }}-opt-am"
       role="button"
       aria-pressed="{{ $isAm ? 'true' : 'false' }}"
       title="ቀይር ወደ አማርኛ (Amharic)"
       class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-semibold transition-all duration-200 active:scale-[0.98] cursor-pointer {{ $isAm ? 'bg-white dark:bg-[#151D2E] text-orange-600 dark:text-orange-400 shadow-xs font-bold ring-1 ring-black/5 dark:ring-white/10' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white' }}">
        <span class="w-1.5 h-1.5 rounded-full {{ $isAm ? 'bg-orange-500' : 'bg-transparent' }}" aria-hidden="true"></span>
        <span class="tracking-tight font-ethiopic">አማርኛ</span>
    </a>
</div>
