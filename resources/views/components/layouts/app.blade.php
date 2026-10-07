<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ darkMode: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches) }" x-bind:class="{ 'dark': darkMode }" x-init="$watch('darkMode', v => localStorage.setItem('theme', v ? 'dark' : 'light'))">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'SRMS') }} - Real Estate Platform</title>
    <meta name="description" content="{{ $description ?? 'Discover, buy, rent, and manage properties across Ethiopia. The modern real estate marketplace.' }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-[#FAFAFA] dark:bg-[#0B0F19] text-gray-900 dark:text-gray-100 antialiased page-enter">

    {{-- PUBLIC NAV --}}
    <header id="site-header" class="fixed top-0 inset-x-0 z-50 transition-all duration-300"
        x-data="{ scrolled: false }"
        x-init="window.addEventListener('scroll', () => scrolled = window.scrollY > 20)">
        <div x-bind:class="scrolled ? 'glass border-b border-gray-200 dark:border-gray-700/50 shadow-sm' : 'bg-transparent'"
             class="transition-all duration-300">
            <div class="page-container">
                <div class="flex items-center justify-between h-16 md:h-18">
                    {{-- Logo --}}
                    <a href="/" class="flex items-center gap-2.5 flex-shrink-0 focus-brand rounded-lg" id="nav-logo">
                        <div class="w-8 h-8 bg-orange-500 rounded-lg flex items-center justify-center shadow-sm shadow-orange-500/30 flex-shrink-0">
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                            </svg>
                        </div>
                        <span class="font-display font-800 text-gray-900 dark:text-white text-lg tracking-tight">SRMS</span>
                    </a>

                    {{-- Desktop Nav --}}
                    <nav class="hidden md:flex items-center gap-1" aria-label="Main navigation">
                        <a href="/properties" class="btn btn-ghost text-sm px-3 py-2" id="nav-properties">Properties</a>
                        <a href="/short-stay" class="btn btn-ghost text-sm px-3 py-2" id="nav-short-stay">Short Stay</a>
                        <a href="/developers" class="btn btn-ghost text-sm px-3 py-2" id="nav-developers">Developers</a>
                        <a href="/services" class="btn btn-ghost text-sm px-3 py-2" id="nav-services">Services</a>
                    </nav>

                    {{-- Right Controls --}}
                    <div class="flex items-center gap-2">
                        {{-- Dark Mode Toggle --}}
                        <button @click="darkMode = !darkMode"
                                class="btn btn-ghost btn-icon rounded-lg"
                                aria-label="Toggle dark mode"
                                id="dark-mode-toggle">
                            <svg x-show="!darkMode" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                            </svg>
                            <svg x-show="darkMode" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true" x-cloak>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                            </svg>
                        </button>

                        @auth
                            <a href="/dashboard" class="btn btn-secondary btn-sm hidden md:inline-flex" id="nav-dashboard">Dashboard</a>
                        @else
                            <a href="/login" class="btn btn-ghost btn-sm hidden md:inline-flex" id="nav-login">Sign In</a>
                            <a href="/register" class="btn btn-primary btn-sm hidden md:inline-flex" id="nav-register">Get Started</a>
                        @endauth

                        {{-- Mobile menu button --}}
                        <button x-data x-on:click="$dispatch('toggle-mobile-nav')"
                                class="btn btn-ghost btn-icon md:hidden"
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

        {{-- Mobile Nav Drawer --}}
        <div x-data="{ open: false }"
             x-on:toggle-mobile-nav.window="open = !open"
             x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="glass border-b border-gray-200 dark:border-gray-700/50 md:hidden"
             x-cloak>
            <nav class="page-container py-4 flex flex-col gap-1" aria-label="Mobile navigation">
                <a href="/properties" class="sidebar-link" id="mobile-nav-properties">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" /></svg>
                    Properties
                </a>
                <a href="/short-stay" class="sidebar-link" id="mobile-nav-short-stay">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m2.25-18v18m13.5-18v18m2.25-18v18M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12M3 3v18m9-18v18" /></svg>
                    Short Stay
                </a>
                <a href="/developers" class="sidebar-link" id="mobile-nav-developers">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" /></svg>
                    Developers
                </a>
                <a href="/services" class="sidebar-link" id="mobile-nav-services">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z" /></svg>
                    Services
                </a>
                <div class="divider my-2"></div>
                @auth
                    <a href="/dashboard" class="btn btn-primary w-full" id="mobile-nav-dashboard">Dashboard</a>
                @else
                    <a href="/login" class="btn btn-secondary w-full mb-2" id="mobile-nav-login">Sign In</a>
                    <a href="/register" class="btn btn-primary w-full" id="mobile-nav-register">Get Started</a>
                @endauth
            </nav>
        </div>
    </header>

    {{-- Main Content --}}
    <main role="main">
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="bg-gray-900 dark:bg-[#0B0F19] border-t border-gray-800 text-gray-400 mt-auto" role="contentinfo">
        <div class="page-container py-14">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10">
                {{-- Brand --}}
                <div class="lg:col-span-1">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-8 h-8 bg-orange-500 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" /></svg>
                        </div>
                        <span class="font-display font-bold text-white text-lg">SRMS</span>
                    </div>
                    <p class="text-sm leading-relaxed">Ethiopia's modern property marketplace. Buy, rent, manage, and discover real estate with ease.</p>
                </div>
                {{-- Platform --}}
                <div>
                    <h3 class="text-white font-semibold text-sm mb-4">Platform</h3>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="/properties" class="hover:text-orange-400 transition-colors">Browse Properties</a></li>
                        <li><a href="/short-stay" class="hover:text-orange-400 transition-colors">Short Stay</a></li>
                        <li><a href="/developers" class="hover:text-orange-400 transition-colors">Developer Projects</a></li>
                        <li><a href="/services" class="hover:text-orange-400 transition-colors">Professional Services</a></li>
                    </ul>
                </div>
                {{-- Company --}}
                <div>
                    <h3 class="text-white font-semibold text-sm mb-4">Company</h3>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="/about" class="hover:text-orange-400 transition-colors">About Us</a></li>
                        <li><a href="/contact" class="hover:text-orange-400 transition-colors">Contact</a></li>
                        <li><a href="/careers" class="hover:text-orange-400 transition-colors">Careers</a></li>
                        <li><a href="/blog" class="hover:text-orange-400 transition-colors">Blog</a></li>
                    </ul>
                </div>
                {{-- Legal --}}
                <div>
                    <h3 class="text-white font-semibold text-sm mb-4">Legal</h3>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="/privacy" class="hover:text-orange-400 transition-colors">Privacy Policy</a></li>
                        <li><a href="/terms" class="hover:text-orange-400 transition-colors">Terms of Service</a></li>
                        <li><a href="/cookie-policy" class="hover:text-orange-400 transition-colors">Cookie Policy</a></li>
                    </ul>
                </div>
            </div>
            <div class="divider mt-12 mb-8"></div>
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 text-sm">
                <p>&copy; {{ date('Y') }} SRMS. All rights reserved.</p>
                <p>Built for Ethiopia's real estate future.</p>
            </div>
        </div>
    </footer>

    {{-- Toast Container --}}
    <div id="toast-container" class="fixed bottom-6 right-6 z-[80] flex flex-col gap-3" aria-live="polite" aria-atomic="true"></div>

    @stack('scripts')
</body>
</html>
