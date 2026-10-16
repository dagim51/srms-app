@props([
    'id' => 'dark-mode-toggle',
    'class' => '',
])

<button type="button"
        id="{{ $id }}"
        @click="if (window.Alpine && Alpine.store('theme')) { Alpine.store('theme').toggle(); } else if (typeof toggleTheme === 'function') { toggleTheme(); } else { darkMode = !darkMode; if (darkMode) { document.documentElement.classList.add('dark'); localStorage.setItem('theme', 'dark'); } else { document.documentElement.classList.remove('dark'); localStorage.setItem('theme', 'light'); } }"
        class="btn btn-ghost btn-icon rounded-lg transition-transform active:scale-95 text-muted hover:text-muted dark:text-muted dark:hover:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 {{ $class }}"
        aria-label="Toggle dark mode"
        :aria-pressed="darkMode ? 'true' : 'false'"
        {{ $attributes->except(['id', 'class']) }}>
    {{-- Sun icon (shown in dark mode to switch to light mode) --}}
    <svg x-show="darkMode" class="w-5 h-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true" x-cloak>
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
    </svg>
    {{-- Moon icon (shown in light mode to switch to dark mode) --}}
    <svg x-show="!darkMode" class="w-5 h-5 text-muted hover:text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
    </svg>
</button>
