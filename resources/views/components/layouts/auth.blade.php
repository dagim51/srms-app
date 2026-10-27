<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
      x-data="{ darkMode: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches) }" 
      x-bind:class="{ 'dark': darkMode }" 
      x-init="$watch('darkMode', v => localStorage.setItem('theme', v ? 'dark' : 'light'))">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Sign In' }} - SRMS Ethiopia</title>

    {{-- Immediate FOUC Prevention --}}
    <script>
        (function() {
            try {
                const theme = localStorage.getItem('theme');
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (theme === 'dark' || (!theme && prefersDark)) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            } catch (e) {}
        })();
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Ethiopic:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 dark:bg-[#0F172A] text-slate-900 dark:text-[#F1F5F9] antialiased min-h-screen flex flex-col justify-between selection:bg-orange-500 selection:text-white relative overflow-x-hidden">

    {{-- Ambient Multicolored Background Meshes --}}
    <div class="absolute -top-32 -left-32 w-96 h-96 bg-gradient-to-br from-orange-500/10 via-amber-400/10 to-transparent rounded-full blur-3xl pointer-events-none" aria-hidden="true"></div>
    <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-gradient-to-tl from-violet-500/10 via-sky-400/10 to-transparent rounded-full blur-3xl pointer-events-none" aria-hidden="true"></div>

    {{-- Auth Header --}}
    <header class="w-full py-5 px-4 sm:px-8 relative z-10">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <a href="/" class="flex items-center gap-2.5 group focus-brand rounded-lg" id="auth-logo">
                <div class="w-9 h-9 bg-gradient-to-tr from-orange-600 to-amber-500 rounded-xl flex items-center justify-center shadow-md shadow-orange-500/30 group-hover:scale-105 transition-transform flex-shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                </div>
                <div class="flex flex-col">
                    <span class="font-display font-black text-slate-900 dark:text-white text-lg tracking-tight leading-none">SRMS</span>
                    <span class="text-[10px] font-bold text-orange-600 dark:text-orange-400 tracking-wider uppercase leading-none mt-0.5">{{ __('Ethiopia') }}</span>
                </div>
            </a>
            
            <div class="flex items-center gap-2">
                <x-language-switcher id="auth-language-switcher" />
                <button @click="darkMode = !darkMode" class="p-2 rounded-xl text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white hover:bg-white/60 dark:hover:bg-white/5 transition-colors cursor-pointer" aria-label="Toggle dark mode" id="auth-dark-toggle">
                    <svg x-show="!darkMode" class="w-5 h-5 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" /></svg>
                    <svg x-show="darkMode" class="w-5 h-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" x-cloak><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" /></svg>
                </button>
            </div>
        </div>
    </header>

    {{-- Auth Content --}}
    <main class="flex-1 flex items-center justify-center py-10 px-4 sm:px-6 relative z-10" role="main">
        <div class="w-full max-w-md">
            {{ $slot }}
        </div>
    </main>

    {{-- Auth Footer --}}
    <footer class="w-full py-6 text-center text-xs text-slate-500 dark:text-slate-400 relative z-10">
        <div class="flex items-center justify-center gap-2 mb-1">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            <span class="font-bold text-slate-900 dark:text-white">{{ __('Verified Municipal Land Title Compliance') }}</span>
        </div>
        <p>&copy; {{ date('Y') }} SRMS Real Estate Platform. {{ __('All rights reserved.') }}</p>
    </footer>

</body>
</html>
