<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data="{
          darkMode: document.documentElement.classList.contains('dark'),
          toggleTheme() {
              this.darkMode = !this.darkMode;
              if (this.darkMode) {
                  document.documentElement.classList.add('dark');
                  localStorage.setItem('theme', 'dark');
              } else {
                  document.documentElement.classList.remove('dark');
                  localStorage.setItem('theme', 'light');
              }
              if (window.Alpine && Alpine.store('theme')) {
                  Alpine.store('theme').dark = this.darkMode;
              }
              window.dispatchEvent(new CustomEvent('theme-changed', { detail: { theme: this.darkMode ? 'dark' : 'light' } }));
          }
      }"
      x-bind:class="{ 'dark': darkMode }"
      @theme-changed.window="darkMode = ($event.detail.theme === 'dark')">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'SRMS') }} - Real Estate Platform</title>
    <meta name="description" content="{{ $description ?? 'Discover, buy, rent, and manage properties across Ethiopia. The modern real estate marketplace.' }}">

    {{-- Multilingual SEO & Alternates --}}
    <link rel="alternate" hreflang="en" href="{{ request()->fullUrlWithQuery(['locale' => 'en']) }}">
    <link rel="alternate" hreflang="am" href="{{ request()->fullUrlWithQuery(['locale' => 'am']) }}">
    <link rel="alternate" hreflang="om" href="{{ request()->fullUrlWithQuery(['locale' => 'om']) }}">
    <meta property="og:locale" content="{{ app()->getLocale() === 'am' ? 'am_ET' : (app()->getLocale() === 'om' ? 'om_ET' : 'en_US') }}">

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

        {{-- Client-Side Translation Bridge for Alpine.js & Dynamic Scripts --}}
        window.__locale = '{{ app()->getLocale() }}';
        window.__ = function(key, replace = {}) {
            let translation = (window.__translations && window.__translations[key]) ? window.__translations[key] : key;
            for (const [k, v] of Object.entries(replace)) {
                translation = translation.replace(new RegExp(':' + k, 'g'), v);
            }
            return translation;
        };
        @php
            $amTranslations = [];
            if (app()->getLocale() === 'am') {
                $amTranslations = cache()->remember('am_translations_js', 3600, function () {
                    $p = base_path('lang/am.json');
                    return file_exists($p) ? json_decode(file_get_contents($p), true) : [];
                });
            }
        @endphp
        window.__translations = @json($amTranslations);
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Ethiopic:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-sans antialiased min-h-screen flex flex-col selection:bg-orange-600 selection:text-white transition-colors duration-300">

    {{-- PUBLIC STICKY NAV (AURA ESTATES FLOATING PILL) --}}
    <header id="site-header" class="fixed top-0 inset-x-0 z-50 pt-3 sm:pt-4 px-3 sm:px-6 pointer-events-none transition-all duration-300"
        x-data="{ scrolled: false }"
        x-init="window.addEventListener('scroll', () => scrolled = window.scrollY > 20)">
        <div class="max-w-7xl mx-auto pointer-events-auto relative z-50">
            <div x-bind:class="scrolled 
                    ? 'bg-white/95 dark:bg-slate-900/95 border-slate-200 dark:border-slate-800 shadow-xl shadow-slate-900/5 dark:shadow-black/25' 
                    : 'bg-white/85 dark:bg-slate-900/85 border-slate-200/80 dark:border-slate-800/80 shadow-lg shadow-slate-900/5 dark:shadow-black/20'"
                 class="backdrop-blur-md border rounded-2xl px-4 sm:px-6 py-2.5 transition-all duration-300">
                <div class="flex items-center justify-between h-12 md:h-13">
                    {{-- Aura Brand Logo --}}
                    <a href="/" class="flex items-center gap-3 flex-shrink-0 group focus-brand rounded-xl" id="nav-logo">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-orange-500 to-orange-600 flex items-center justify-center text-white font-serif font-bold text-xl shadow-md shadow-orange-500/25 group-hover:scale-105 transition-transform flex-shrink-0">
                            A
                        </div>
                        <div class="flex flex-col">
                            <span class="font-serif font-bold text-lg tracking-wider block leading-none text-slate-900 dark:text-white">AURA <span class="text-orange-600 dark:text-orange-400">SRMS</span></span>
                            <span class="text-[10px] uppercase tracking-[0.25em] text-orange-600 dark:text-orange-400 font-semibold block mt-0.5">{{ __('Estates • Ethiopia') }}</span>
                        </div>
                    </a>

                    {{-- Desktop Nav Links --}}
                    <nav class="hidden md:flex items-center gap-7 text-sm font-medium text-slate-600 dark:text-slate-300" aria-label="Main navigation">
                        <a href="{{ route('home') }}" class="hover:text-orange-600 dark:hover:text-orange-400 transition-colors flex items-center gap-1.5 {{ request()->routeIs('home') ? 'text-orange-600 dark:text-orange-400 font-bold' : '' }}" id="nav-home">
                            <svg class="w-4 h-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
                            <span>{{ __('Home') }}</span>
                        </a>
                        <a href="/search" class="hover:text-orange-600 dark:hover:text-orange-400 transition-colors flex items-center gap-1.5 {{ request()->routeIs('search') ? 'text-orange-600 dark:text-orange-400 font-bold' : '' }}" id="nav-search">
                            <svg class="w-4 h-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                            <span>{{ __('Search') }}</span>
                        </a>
                        <a href="/properties" class="hover:text-orange-600 dark:hover:text-orange-400 transition-colors {{ request()->routeIs('properties.index') ? 'text-orange-600 dark:text-orange-400 font-bold' : '' }}" id="nav-properties">{{ __('Properties') }}</a>
                        <a href="/developers" class="hover:text-orange-600 dark:hover:text-orange-400 transition-colors {{ request()->routeIs('developers.index') ? 'text-orange-600 dark:text-orange-400 font-bold' : '' }}" id="nav-developers">{{ __('Developers') }}</a>
                        <a href="/services" class="hover:text-orange-600 dark:hover:text-orange-400 transition-colors {{ request()->routeIs('services.*') ? 'text-orange-600 dark:text-orange-400 font-bold' : '' }}" id="nav-services">{{ __('Services') }}</a>
                    </nav>

                    {{-- Right Controls --}}
                    <div class="flex items-center gap-2 sm:gap-3">
                        {{-- Language Switcher --}}
                        <x-language-switcher id="nav-language-switcher" />

                        {{-- Dark Mode Toggle --}}
                        <x-theme-toggle id="dark-mode-toggle" />

                        @auth
                            <a href="/dashboard" class="btn btn-secondary inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold border border-slate-200 dark:border-slate-700 hover:border-orange-500 transition-colors shadow-sm" id="nav-dashboard">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span>{{ __('Dashboard') }}</span>
                            </a>
                        @else
                            <a href="/login" class="px-3 py-2 text-xs font-semibold text-slate-700 hover:text-orange-600 dark:text-slate-300 dark:hover:text-orange-400 transition-colors hidden md:inline-flex" id="nav-login">{{ __('Sign In') }}</a>
                            <a href="/register" class="btn btn-primary inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl text-xs font-semibold bg-gradient-to-r from-orange-600 to-orange-500 hover:from-orange-500 hover:to-orange-600 text-white shadow-md shadow-orange-500/20 hover:shadow-lg hover:shadow-orange-500/30 transition-all duration-300 transform hover:-translate-y-0.5 hidden md:inline-flex" id="nav-register">
                                <span>{{ __('Book Private Tour') }}</span>
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                            </a>
                        @endauth

                        {{-- Mobile menu button --}}
                        <button x-data x-on:click="$dispatch('toggle-mobile-nav')"
                                class="btn btn-ghost btn-icon md:hidden text-slate-900 dark:text-white p-2 rounded-xl bg-slate-100 dark:bg-slate-800"
                                aria-label="Open menu"
                                id="mobile-menu-toggle">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Mobile Nav Backdrop Scrim --}}
        <div x-data="{ open: false }"
             x-on:toggle-mobile-nav.window="open = !open"
             x-show="open"
             x-transition.opacity.duration.200ms
             class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 pointer-events-auto md:hidden"
             @click="$dispatch('toggle-mobile-nav')"
             x-cloak></div>

        {{-- Mobile Nav Drawer --}}
        <div x-data="{ open: false }"
             x-on:toggle-mobile-nav.window="open = !open"
             x-show="open"
             @click.away="open = false"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="max-w-7xl mx-auto px-4 mt-2 md:hidden relative z-50 pointer-events-auto"
             x-cloak>
            <div class="card bg-white/95 dark:bg-slate-900/95 backdrop-blur-xl border border-slate-200 dark:border-slate-800 rounded-2xl p-5 pointer-events-auto space-y-3">
                <nav class="flex flex-col gap-1 text-sm font-medium" aria-label="Mobile navigation">
                    <a href="{{ route('home') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-orange-50 dark:hover:bg-slate-800 hover:text-orange-600 transition-colors {{ request()->routeIs('home') ? 'bg-orange-50 dark:bg-slate-800 text-orange-600 font-bold' : '' }}" id="mobile-nav-home">
                        <svg class="w-4 h-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
                        <span>{{ __('Home') }}</span>
                    </a>
                    <a href="/search" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-orange-50 dark:hover:bg-slate-800 hover:text-orange-600 transition-colors {{ request()->routeIs('search') ? 'bg-orange-50 dark:bg-slate-800 text-orange-600 font-bold' : '' }}" id="mobile-nav-search">
                        <svg class="w-4 h-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                        <span>{{ __('Search Engine') }}</span>
                    </a>
                    <a href="/properties" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-orange-50 dark:hover:bg-slate-800 hover:text-orange-600 transition-colors {{ request()->routeIs('properties.index') ? 'bg-orange-50 dark:bg-slate-800 text-orange-600 font-bold' : '' }}" id="mobile-nav-properties">
                        <svg class="w-4 h-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
                        <span>{{ __('Browse Properties') }}</span>
                    </a>
                    <a href="/developers" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-orange-50 dark:hover:bg-slate-800 hover:text-orange-600 transition-colors {{ request()->routeIs('developers.index') ? 'bg-orange-50 dark:bg-slate-800 text-orange-600 font-bold' : '' }}" id="mobile-nav-developers">
                        <svg class="w-4 h-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" /></svg>
                        <span>{{ __('Developer Projects') }}</span>
                    </a>
                    <a href="/services" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-orange-50 dark:hover:bg-slate-800 hover:text-orange-600 transition-colors {{ request()->routeIs('services.*') ? 'bg-orange-50 dark:bg-slate-800 text-orange-600 font-bold' : '' }}" id="mobile-nav-services">
                        <svg class="w-4 h-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z" /></svg>
                        <span>{{ __('Professional Services') }}</span>
                    </a>
                    
                    <div class="h-px bg-slate-200 dark:bg-slate-800 my-2"></div>
                    <div class="py-1">
                        <x-language-switcher id="mobile-nav-language-switcher" class="w-full" />
                    </div>
                    @auth
                        <a href="/dashboard" class="btn btn-primary w-full py-2.5 rounded-xl bg-gradient-to-r from-orange-600 to-orange-500 text-white font-semibold text-center" id="mobile-nav-dashboard">{{ __('Go to Dashboard') }}</a>
                    @else
                        <div class="grid grid-cols-2 gap-2 mt-2">
                            <a href="/login" class="btn btn-secondary text-center text-xs py-2.5 rounded-xl border border-slate-200 dark:border-slate-700" id="mobile-nav-login">{{ __('Sign In') }}</a>
                            <a href="/register" class="btn btn-primary text-center text-xs py-2.5 rounded-xl bg-gradient-to-r from-orange-600 to-orange-500 text-white font-semibold" id="mobile-nav-register">{{ __('Get Started') }}</a>
                        </div>
                    @endauth
                </nav>
            </div>
        </div>
    </header>

    {{-- Main Content Slot --}}
    <main role="main" class="flex-grow">
        {{ $slot }}
    </main>

    {{-- AURA ESTATES LUXURY OBSIDIAN FOOTER --}}
    <footer class="bg-slate-950 text-slate-400 py-16 border-t border-slate-800 mt-auto" role="contentinfo">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-12">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-10 pb-12 border-b border-slate-800">
                <div class="lg:col-span-2 space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-orange-500 to-orange-600 flex items-center justify-center text-white font-serif font-bold text-xl shadow-md shadow-orange-500/25">
                            A
                        </div>
                        <div>
                            <span class="font-serif font-bold text-xl tracking-wider text-white">AURA ESTATES</span>
                            <span class="text-[10px] uppercase tracking-[0.25em] text-orange-400 font-semibold block">SRMS Ethiopia</span>
                        </div>
                    </div>
                    <p class="text-sm text-slate-400 max-w-sm leading-relaxed">
                        {{ __("Curating the nation's finest residential architecture and verified real estate registry for collectors and discerning investors.") }}
                    </p>
                    <div class="flex items-center gap-2 flex-wrap text-xs pt-1">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-950/60 text-emerald-400 font-semibold border border-emerald-800/50">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            {{ __('Verified Land Titles') }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-orange-950/60 text-orange-400 font-semibold border border-orange-800/50">
                            <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>
                            {{ __('PostGIS Geocoded') }}
                        </span>
                    </div>
                </div>

                {{-- Destinations --}}
                <div>
                    <h3 class="text-white font-serif font-bold text-sm uppercase tracking-wider mb-4">{{ __('Destinations') }}</h3>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="/properties?location=Addis+Ababa" class="hover:text-orange-400 transition-colors">{{ __('Bole & Old Airport') }}</a></li>
                        <li><a href="/properties?location=Kazanchis" class="hover:text-orange-400 transition-colors">{{ __('Kazanchis Skyline') }}</a></li>
                        <li><a href="/properties?location=Hawassa" class="hover:text-orange-400 transition-colors">{{ __('Hawassa Lakefront') }}</a></li>
                        <li><a href="/properties?location=Bahir+Dar" class="hover:text-orange-400 transition-colors">{{ __('Bahir Dar Resorts') }}</a></li>
                        <li><a href="/properties?location=Bishoftu" class="hover:text-orange-400 transition-colors">{{ __('Bishoftu Lakeside') }}</a></li>
                    </ul>
                </div>

                {{-- Portfolio --}}
                <div>
                    <h3 class="text-white font-serif font-bold text-sm uppercase tracking-wider mb-4">{{ __('Portfolio') }}</h3>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="/properties?purpose=For+Sale" class="hover:text-orange-400 transition-colors">{{ __('Curated Residences') }}</a></li>
                        <li><a href="/properties?purpose=For+Rent" class="hover:text-orange-400 transition-colors">{{ __('Luxury Rentals') }}</a></li>
                        <li><a href="/developers" class="hover:text-orange-400 transition-colors">{{ __('Masterplan Developments') }}</a></li>
                        <li><a href="/services" class="hover:text-orange-400 transition-colors">{{ __('Private Advisory') }}</a></li>
                    </ul>
                </div>

                {{-- Private Dossier Journal --}}
                <div>
                    <h3 class="text-white font-serif font-bold text-sm uppercase tracking-wider mb-4">{{ __('Private Journal') }}</h3>
                    <p class="text-xs text-slate-400 mb-3">{{ __('Receive quarterly off-market dossiers and architectural market reports across Ethiopia.') }}</p>
                    <form onsubmit="event.preventDefault(); alert('Subscribed to Aura Private Dossier');" class="flex gap-2">
                        <input type="email" required placeholder="{{ __('Email address') }}" class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-800 text-xs text-white placeholder:text-slate-500 focus:outline-none focus:border-orange-500 transition-colors">
                        <button type="submit" class="px-3.5 py-2 rounded-xl bg-orange-600 text-white font-semibold text-xs hover:bg-orange-500 transition-colors cursor-pointer">{{ __('Join') }}</button>
                    </form>
                </div>
            </div>

            <div class="pt-8 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-4">
                <p>&copy; {{ date('Y') }} Aura Estates • SRMS International LLC. {{ __('All rights reserved. Equal Housing Opportunity.') }}</p>
                <div class="flex items-center gap-6">
                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        {{ __('Registry Operational') }}
                    </span>
                    <span>•</span>
                    <a href="/privacy" class="hover:text-slate-300 transition-colors">{{ __('Privacy Charter') }}</a>
                    <a href="/terms" class="hover:text-slate-300 transition-colors">{{ __('Terms of Advisory') }}</a>
                </div>
            </div>
        </div>
    </footer>

    {{-- Toast Container --}}
    <div id="toast-container" class="fixed bottom-6 right-6 z-[80] flex flex-col gap-3" aria-live="polite" aria-atomic="true"></div>

    @stack('scripts')
</body>
</html>
