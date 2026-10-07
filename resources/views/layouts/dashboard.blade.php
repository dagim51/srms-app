<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ darkMode: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches), sidebarOpen: false }" x-bind:class="{ 'dark': darkMode }" x-init="$watch('darkMode', v => localStorage.setItem('theme', v ? 'dark' : 'light'))">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} - SRMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-gray-50 dark:bg-[#0B0F19] text-gray-900 dark:text-gray-100 antialiased">

    <div class="flex h-dvh overflow-hidden">

        {{-- ═══ SIDEBAR OVERLAY (Mobile) ═══ --}}
        <div x-show="sidebarOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="sidebarOpen = false"
             class="fixed inset-0 z-40 bg-black/50 backdrop-blur-sm lg:hidden"
             x-cloak></div>

        {{-- ═══ SIDEBAR ═══ --}}
        <aside id="dashboard-sidebar"
               class="fixed lg:static inset-y-0 left-0 z-40 w-64 flex-shrink-0 bg-white dark:bg-[#111827] border-r border-gray-200 dark:border-[#273244] flex flex-col transition-transform duration-300"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">

            {{-- Sidebar Header --}}
            <div class="flex items-center gap-2.5 px-5 h-16 border-b border-gray-200 dark:border-[#273244] flex-shrink-0">
                <div class="w-8 h-8 bg-orange-500 rounded-lg flex items-center justify-center shadow-sm shadow-orange-500/30">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" /></svg>
                </div>
                <span class="font-display font-bold text-gray-900 dark:text-white text-lg tracking-tight">SRMS</span>
            </div>

            {{-- Sidebar Nav --}}
            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1" aria-label="Dashboard navigation">

                <a href="/dashboard" id="sidebar-dashboard"
                   class="sidebar-link {{ request()->is('dashboard') ? 'active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" /></svg>
                    <span>Dashboard</span>
                </a>

                <a href="/dashboard/properties" id="sidebar-properties"
                   class="sidebar-link {{ request()->is('dashboard/properties*') ? 'active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" /></svg>
                    <span>Properties</span>
                </a>

                <a href="/dashboard/listings" id="sidebar-listings"
                   class="sidebar-link {{ request()->is('dashboard/listings*') ? 'active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg>
                    <span>Listings</span>
                </a>

                <a href="/dashboard/bookings" id="sidebar-bookings"
                   class="sidebar-link {{ request()->is('dashboard/bookings*') ? 'active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" /></svg>
                    <span>Bookings</span>
                </a>

                <a href="/dashboard/messages" id="sidebar-messages"
                   class="sidebar-link {{ request()->is('dashboard/messages*') ? 'active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" /></svg>
                    <span>Messages</span>
                </a>

                <a href="/dashboard/favorites" id="sidebar-favorites"
                   class="sidebar-link {{ request()->is('dashboard/favorites*') ? 'active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" /></svg>
                    <span>Favorites</span>
                </a>

                <a href="/dashboard/transactions" id="sidebar-transactions"
                   class="sidebar-link {{ request()->is('dashboard/transactions*') ? 'active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" /></svg>
                    <span>Transactions</span>
                </a>

                <div class="divider my-2"></div>

                <a href="/dashboard/settings" id="sidebar-settings"
                   class="sidebar-link {{ request()->is('dashboard/settings*') ? 'active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    <span>Settings</span>
                </a>
            </nav>

            {{-- Sidebar User --}}
            <div class="border-t border-gray-200 dark:border-[#273244] p-3">
                @auth
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open"
                            class="w-full flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-[#172033] transition-colors cursor-pointer"
                            aria-haspopup="true" :aria-expanded="open"
                            id="sidebar-user-menu-trigger">
                        <div class="avatar avatar-md avatar-brand flex-shrink-0">
                            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0 text-left">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ auth()->user()->name ?? 'User' }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ auth()->user()->email ?? '' }}</p>
                        </div>
                        <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" /></svg>
                    </button>
                    <div x-show="open" @click.outside="open = false"
                         class="dropdown absolute bottom-full left-0 right-0 mb-2"
                         x-cloak>
                        <a href="/dashboard/profile" class="dropdown-item" id="sidebar-user-profile">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                            My Profile
                        </a>
                        <form method="POST" action="/logout">
                            @csrf
                            <button type="submit" class="dropdown-item danger w-full text-left" id="sidebar-logout">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" /></svg>
                                Sign Out
                            </button>
                        </form>
                    </div>
                </div>
                @endauth
            </div>
        </aside>

        {{-- ═══ MAIN AREA ═══ --}}
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

            {{-- Topbar --}}
            <header class="flex-shrink-0 h-16 bg-white dark:bg-[#111827] border-b border-gray-200 dark:border-[#273244] flex items-center gap-4 px-4 lg:px-6" id="dashboard-topbar">
                {{-- Mobile sidebar toggle --}}
                <button @click="sidebarOpen = !sidebarOpen"
                        class="btn btn-ghost btn-icon lg:hidden flex-shrink-0"
                        aria-label="Toggle sidebar"
                        id="sidebar-toggle">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
                </button>

                {{-- Page Title --}}
                <div class="flex-1 min-w-0">
                    <h1 class="text-base font-semibold text-gray-900 dark:text-white truncate font-display">
                        @yield('page-title', 'Dashboard')
                    </h1>
                    @hasSection('breadcrumb')
                        <nav aria-label="Breadcrumb" class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            @yield('breadcrumb')
                        </nav>
                    @endif
                </div>

                {{-- Topbar Right --}}
                <div class="flex items-center gap-2 flex-shrink-0">
                    {{-- Dark Mode --}}
                    <button @click="darkMode = !darkMode" class="btn btn-ghost btn-icon rounded-lg" aria-label="Toggle dark mode" id="topbar-dark-toggle">
                        <svg x-show="!darkMode" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" /></svg>
                        <svg x-show="darkMode" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" x-cloak><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" /></svg>
                    </button>

                    {{-- Notifications --}}
                    <button class="btn btn-ghost btn-icon rounded-lg relative" aria-label="Notifications" id="topbar-notifications">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" /></svg>
                        <span class="absolute top-1 right-1 w-2 h-2 bg-orange-500 rounded-full" aria-hidden="true"></span>
                    </button>
                </div>
            </header>

            {{-- Page Content --}}
            <main class="flex-1 overflow-y-auto p-4 lg:p-6" role="main">
                {{ $slot }}
            </main>
        </div>
    </div>

    {{-- Toast Container --}}
    <div id="toast-container" class="fixed bottom-6 right-6 z-[80] flex flex-col gap-3" aria-live="polite"></div>

    @stack('scripts')
</body>
</html>
