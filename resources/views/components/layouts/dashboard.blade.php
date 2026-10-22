@php
    $currentPath = trim(request()->path(), '/');
    $catalogTitles = [
        'dashboard' => 'Dashboard',
        'dashboard/properties' => 'Property Inventory',
        'dashboard/properties/map' => 'GIS Map Explorer',
        'dashboard/properties/create' => 'Register Property',
        'dashboard/listings' => 'Listings Management',
        'dashboard/moderation/listings' => 'Moderation Queue',
        'dashboard/amenities' => 'Amenities',
        'dashboard/features' => 'Features',
        'dashboard/reservations' => 'Reservations',
        'dashboard/accommodations' => 'Hosting & Stays',
        'dashboard/viewings' => 'Viewings',
        'dashboard/offers' => 'Offers',
        'dashboard/rental' => 'Rental & Leases',
        'dashboard/crm' => 'CRM & Pipeline',
        'dashboard/crm/leads' => 'CRM Leads',
        'dashboard/crm/pipeline' => 'Deal Pipeline',
        'dashboard/crm/clients' => 'CRM Clients',
        'dashboard/crm/tasks' => 'CRM Tasks',
        'dashboard/crm/commissions' => 'Commissions',
        'dashboard/projects' => 'Developer Projects',
        'dashboard/sales' => 'Developer Sales',
        'dashboard/transactions' => 'Transactions',
        'dashboard/finance' => 'Financial Core',
        'dashboard/documents' => 'Documents & KYC',
        'dashboard/trust' => 'Trust & Safety',
        'dashboard/map' => 'Advanced Map',
        'dashboard/analytics' => 'Analytics',
        'dashboard/alerts' => 'Alerts & For You',
        'dashboard/organization' => 'Organization',
        'dashboard/admin' => 'Administration',
        'dashboard/settings' => 'Settings',
        'dashboard/profile' => 'Profile',
    ];

    $matchedNavTitle = null;
    if (isset($catalogTitles[$currentPath])) {
        $matchedNavTitle = $catalogTitles[$currentPath];
    } else {
        foreach ($catalogTitles as $p => $t) {
            if (str_starts_with($currentPath, $p)) {
                $matchedNavTitle = $t;
                break;
            }
        }
    }

    $topbarTitle = trim((string) ($title ?? (trim($__env->yieldContent('page-title')) ?: ($matchedNavTitle ?: 'Dashboard'))));
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data="{
          darkMode: document.documentElement.classList.contains('dark'),
          sidebarOpen: false,
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
    <title>{{ $topbarTitle }} - SRMS</title>

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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Ethiopic:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,400;1,600&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="dashboard-shell bg-slate-50 dark:bg-[#070B14] text-slate-800 dark:text-slate-100 antialiased selection:bg-orange-500 selection:text-white">

    <div class="flex h-dvh overflow-hidden">

        {{-- ═══ SIDEBAR OVERLAY (Mobile) ═══ --}}
        <div x-show="sidebarOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-out duration-180"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="sidebarOpen = false"
             class="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm lg:hidden"
             x-cloak></div>

        {{-- ═══ SIDEBAR ═══ --}}
        <aside id="dashboard-sidebar"
               class="fixed lg:static inset-y-0 left-0 z-40 w-68 flex-shrink-0 bg-white/95 dark:bg-[#0A101D]/95 backdrop-blur-xl border-r border-slate-200/80 dark:border-slate-800/70 flex flex-col transition-transform duration-[240ms] [transition-timing-function:cubic-bezier(0.32,0.72,0,1)] shadow-xl lg:shadow-none"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">

            {{-- Sidebar Header: Aura Estates & Apple Design Synergy --}}
            <div class="flex items-center gap-3 px-5 h-17 border-b border-slate-200/80 dark:border-slate-800/70 flex-shrink-0 bg-white/40 dark:bg-slate-950/40 backdrop-blur-md">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-orange-500 via-[#C2410C] to-amber-500 flex items-center justify-center text-white font-serif font-bold text-xl shadow-md shadow-orange-500/25 ring-1 ring-orange-400/30">
                    A
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-serif font-bold text-lg tracking-wider block leading-none text-slate-900 dark:text-white">AURA</span>
                        <span class="text-[9px] font-bold uppercase tracking-widest px-1.5 py-0.5 rounded-full bg-orange-100 dark:bg-orange-950/60 text-orange-700 dark:text-orange-300 border border-orange-300/40 dark:border-orange-500/30">PRO</span>
                    </div>
                    <span class="text-[10px] uppercase tracking-[0.25em] text-orange-600 dark:text-orange-400 font-semibold block mt-0.5">SRMS Estates</span>
                </div>
            </div>

            {{-- Organization Workspace Selector (Replaces duplicate user profile) --}}
            <div class="px-3 py-3 border-b border-slate-200/80 dark:border-slate-800/70">
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="w-full flex items-center justify-between bg-slate-100/70 dark:bg-slate-900/60 hover:bg-orange-50/50 dark:hover:bg-slate-800/80 p-2.5 rounded-xl transition-all border border-slate-200/80 dark:border-slate-800/80 group text-left cursor-pointer" aria-haspopup="true" :aria-expanded="open" id="tenant-switcher">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-slate-800 to-slate-950 dark:from-slate-700 dark:to-slate-900 text-orange-400 border border-slate-700/50 flex items-center justify-center font-bold text-xs flex-shrink-0 shadow-xs">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" /></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-bold text-slate-900 dark:text-white truncate group-hover:text-orange-600 dark:group-hover:text-orange-400 transition-colors">{{ __('Acme Real Estate Group') }}</p>
                                <div class="flex items-center gap-1.5 mt-0.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    <p class="text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider truncate">{{ __('Verified Workspace') }}</p>
                                </div>
                            </div>
                        </div>
                        <svg class="w-4 h-4 text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-200 flex-shrink-0 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" /></svg>
                    </button>
                    <div x-show="open" @click.outside="open = false" class="dropdown absolute top-full left-0 right-0 mt-1.5 bg-white dark:bg-[#0D1527] border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl shadow-slate-950/20 p-1.5 z-50" x-cloak>
                        <div class="px-3 py-1.5 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">{{ __('Active Workspaces') }}</div>
                        <a href="#" class="dropdown-item rounded-lg flex items-center justify-between bg-orange-50/50 dark:bg-orange-950/20">
                            <div class="flex items-center gap-2 min-w-0">
                                <div class="w-6 h-6 rounded-md bg-orange-500 text-white flex items-center justify-center font-bold text-xs flex-shrink-0">A</div>
                                <span class="truncate text-xs font-semibold text-slate-900 dark:text-white">{{ __('Acme Real Estate Group') }}</span>
                            </div>
                            <svg class="w-4 h-4 text-orange-500 ml-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                        </a>
                        <a href="#" class="dropdown-item rounded-lg flex items-center justify-between">
                            <div class="flex items-center gap-2 min-w-0">
                                <div class="w-6 h-6 rounded-md bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 flex items-center justify-center font-bold text-xs flex-shrink-0">P</div>
                                <span class="truncate text-xs text-slate-700 dark:text-slate-300">{{ __('Personal Portfolio') }}</span>
                            </div>
                        </a>
                        <div class="divider my-1 border-slate-100 dark:border-slate-800/80"></div>
                        <a href="/dashboard/organization/create" class="dropdown-item rounded-lg text-orange-600 dark:text-orange-400 font-semibold text-xs flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                            <span>{{ __('Add Workspace') }}</span>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Sidebar Nav --}}
            <nav class="flex-1 overflow-y-auto py-3 px-3 space-y-4 dashboard-sidebar-scroll" aria-label="Dashboard navigation">

                {{-- Group 1: Core & Inventory --}}
                <div class="space-y-1">
                    <p class="px-3 text-xs font-bold uppercase tracking-wider text-muted dark:text-muted">{{ __('Core & Inventory') }}</p>
                    <a href="/dashboard" id="sidebar-dashboard"
                       class="sidebar-link {{ request()->is('dashboard') ? 'active' : '' }}">
                        <svg class="w-4.5 h-4.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" /></svg>
                        <span>{{ __('Dashboard') }}</span>
                    </a>

                    <a href="/dashboard/properties" id="sidebar-properties"
                       class="sidebar-link {{ (request()->is('dashboard/properties') || request()->is('dashboard/properties/create')) ? 'active' : '' }}">
                        <svg class="w-4.5 h-4.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" /></svg>
                        <span>{{ __('Properties') }}</span>
                    </a>

                    <a href="/dashboard/properties/map" id="sidebar-map"
                       class="sidebar-link {{ request()->is('dashboard/properties/map*') ? 'active' : '' }}">
                        <svg class="w-4.5 h-4.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" /></svg>
                        <span>{{ __('Map Explorer') }}</span>
                    </a>

                    <a href="/dashboard/listings" id="sidebar-listings"
                       class="sidebar-link {{ (request()->is('dashboard/listings*') && !request()->is('dashboard/moderation*')) ? 'active' : '' }}">
                        <svg class="w-4.5 h-4.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg>
                        <span>{{ __('Listings') }}</span>
                    </a>

                    <a href="/dashboard/moderation/listings" id="sidebar-moderation"
                       class="sidebar-link {{ request()->is('dashboard/moderation*') ? 'active' : '' }}">
                        <svg class="w-4.5 h-4.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg>
                        <span>{{ __('Moderation Queue') }}</span>
                    </a>
                </div>

                {{-- Group 2: Catalogs & Operations --}}
                <div class="space-y-1">
                    <p class="px-3 text-xs font-bold uppercase tracking-wider text-muted dark:text-muted">{{ __('Catalogs & Operations') }}</p>
                    <a href="/dashboard/amenities" id="sidebar-amenities"
                       class="sidebar-link {{ request()->is('dashboard/amenities*') ? 'active' : '' }}">
                        <svg class="w-4.5 h-4.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" /></svg>
                        <span>{{ __('Amenities') }}</span>
                    </a>

                    <a href="/dashboard/features" id="sidebar-features"
                       class="sidebar-link {{ request()->is('dashboard/features*') ? 'active' : '' }}">
                        <svg class="w-4.5 h-4.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                        <span>{{ __('Features') }}</span>
                    </a>

                    <a href="/dashboard/locations" id="sidebar-locations"
                       class="sidebar-link {{ request()->is('dashboard/locations*') ? 'active' : '' }}">
                        <svg class="w-4.5 h-4.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                        <span>{{ __('Locations') }}</span>
                    </a>

                    <a href="/dashboard/accommodations" id="sidebar-accommodations"
                       class="sidebar-link {{ request()->is('dashboard/accommodations*') ? 'active' : '' }}">
                        <svg class="w-4.5 h-4.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                        <span>{{ __('Hosting & Stays') }}</span>
                    </a>

                    <a href="/dashboard/operations" id="sidebar-operations"
                       class="sidebar-link {{ request()->is('dashboard/operations*') ? 'active' : '' }}">
                        <svg class="w-4.5 h-4.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z" /></svg>
                        <span>{{ __('Operations') }}</span>
                    </a>
                </div>

                {{-- Group 3: Transactions & Pipeline --}}
                <div class="space-y-1">
                    <p class="px-3 text-xs font-bold uppercase tracking-wider text-muted dark:text-muted">{{ __('Transactions & Pipeline') }}</p>
                    <a href="/dashboard/reservations" id="sidebar-reservations"
                       class="sidebar-link {{ request()->is('dashboard/reservations*') ? 'active' : '' }}">
                        <svg class="w-4.5 h-4.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" /></svg>
                        <span>{{ __('Reservations') }}</span>
                    </a>

                    <a href="/dashboard/viewings" id="sidebar-viewings"
                       class="sidebar-link {{ request()->is('dashboard/viewings*') ? 'active' : '' }}">
                        <svg class="w-4.5 h-4.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                        <span>{{ __('Viewings') }}</span>
                    </a>

                    <a href="/dashboard/offers" id="sidebar-offers"
                       class="sidebar-link {{ request()->is('dashboard/offers*') ? 'active' : '' }}">
                        <svg class="w-4.5 h-4.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span>{{ __('Offers') }}</span>
                    </a>

                    <a href="/dashboard/rental" id="sidebar-rental"
                       class="sidebar-link {{ request()->is('dashboard/rental*') ? 'active' : '' }}">
                        <svg class="w-4.5 h-4.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                        <span>{{ __('Rental & Leases') }}</span>
                    </a>

                    <a href="/dashboard/crm" id="sidebar-crm"
                       class="sidebar-link {{ request()->is('dashboard/crm*') ? 'active' : '' }}">
                        <svg class="w-4.5 h-4.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                        <span>{{ __('CRM & Leads') }}</span>
                    </a>

                    <a href="/dashboard/projects" id="sidebar-projects"
                       class="sidebar-link {{ request()->is('dashboard/projects*') ? 'active' : '' }}">
                        <svg class="w-4.5 h-4.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" /></svg>
                        <span>{{ __('Developer Projects') }}</span>
                    </a>

                    <a href="/dashboard/sales" id="sidebar-sales"
                       class="sidebar-link {{ request()->is('dashboard/sales*') ? 'active' : '' }}">
                        <svg class="w-4.5 h-4.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" /></svg>
                        <span>{{ __('Developer Sales') }}</span>
                    </a>

                    <a href="/dashboard/transactions" id="sidebar-transactions"
                       class="sidebar-link {{ request()->is('dashboard/transactions*') ? 'active' : '' }}">
                        <svg class="w-4.5 h-4.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" /></svg>
                        <span>{{ __('Transactions') }}</span>
                    </a>
                </div>

                {{-- Group 4: Finance, Trust & Intelligence --}}
                <div class="space-y-1">
                    <p class="px-3 text-xs font-bold uppercase tracking-wider text-muted dark:text-muted">{{ __('Finance & Intelligence') }}</p>
                    <a href="/dashboard/finance" id="sidebar-finance"
                       class="sidebar-link {{ request()->is('dashboard/finance*') ? 'active' : '' }}">
                        <svg class="w-4.5 h-4.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span>{{ __('Financial Core') }}</span>
                    </a>

                    <a href="/dashboard/documents" id="sidebar-documents"
                       class="sidebar-link {{ request()->is('dashboard/documents*') ? 'active' : '' }}">
                        <svg class="w-4.5 h-4.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        <span>{{ __('Documents & KYC') }}</span>
                    </a>

                    <a href="/dashboard/trust" id="sidebar-trust"
                       class="sidebar-link {{ request()->is('dashboard/trust*') ? 'active' : '' }}">
                        <svg class="w-4.5 h-4.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                        <span>{{ __('Trust & Safety') }}</span>
                    </a>

                    <a href="/dashboard/map" id="sidebar-advanced-gis"
                       class="sidebar-link {{ request()->is('dashboard/map*') ? 'active' : '' }}">
                        <svg class="w-4.5 h-4.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" /></svg>
                        <span>{{ __('Advanced GIS') }}</span>
                    </a>

                    <a href="/dashboard/intelligence/valuation" id="sidebar-intelligence"
                       class="sidebar-link {{ request()->is('dashboard/intelligence*') ? 'active' : '' }}">
                        <svg class="w-4.5 h-4.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                        <span>{{ __('Intelligence & ROI') }}</span>
                    </a>

                    <a href="/dashboard/services" id="sidebar-services"
                       class="sidebar-link {{ request()->is('dashboard/services*') ? 'active' : '' }}">
                        <svg class="w-4.5 h-4.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                        <span>{{ __('Pro Services') }}</span>
                    </a>

                    <a href="/dashboard/ai/search" id="sidebar-ai"
                       class="sidebar-link {{ request()->is('dashboard/ai*') ? 'active' : '' }}">
                        <svg class="w-4.5 h-4.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                        <span>{{ __('AI Assistant') }}</span>
                    </a>

                    <a href="/dashboard/integrations" id="sidebar-integrations"
                       class="sidebar-link {{ request()->is('dashboard/integrations*') ? 'active' : '' }}">
                        <svg class="w-4.5 h-4.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z" /></svg>
                        <span>{{ __('Integrations') }}</span>
                    </a>
                </div>

                {{-- Group 5: Workspace & Administration --}}
                <div class="space-y-1">
                    <p class="px-3 text-xs font-bold uppercase tracking-wider text-muted dark:text-muted">{{ __('Workspace & Admin') }}</p>
                    <a href="/dashboard/messages" id="sidebar-messages"
                       class="sidebar-link {{ request()->is('dashboard/messages*') ? 'active' : '' }}">
                        <svg class="w-4.5 h-4.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" /></svg>
                        <span>{{ __('Messages') }}</span>
                    </a>

                    <a href="/dashboard/favorites" id="sidebar-favorites"
                       class="sidebar-link {{ request()->is('dashboard/favorites*') ? 'active' : '' }}">
                        <svg class="w-4.5 h-4.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" /></svg>
                        <span>{{ __('Favorites') }}</span>
                    </a>

                    <a href="/dashboard/collections" id="sidebar-collections"
                       class="sidebar-link {{ request()->is('dashboard/collections*') ? 'active' : '' }}">
                        <svg class="w-4.5 h-4.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121 12v.75m-8.625-1.5v3.75m-3.75-3.75v3.75m6-3.75v3.75M3 18.75V18a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 18v.75" /></svg>
                        <span>{{ __('Collections') }}</span>
                    </a>

                    <a href="/dashboard/analytics" id="sidebar-analytics"
                       class="sidebar-link {{ request()->is('dashboard/analytics*') ? 'active' : '' }}">
                        <svg class="w-4.5 h-4.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" /></svg>
                        <span>{{ __('Analytics') }}</span>
                    </a>

                    <a href="/dashboard/alerts" id="sidebar-alerts"
                       class="sidebar-link {{ request()->is('dashboard/alerts*') || request()->is('dashboard/recommendations*') ? 'active' : '' }}">
                        <svg class="w-4.5 h-4.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                        <span>{{ __('Alerts & For You') }}</span>
                    </a>

                    <a href="/dashboard/organization" id="sidebar-organization"
                       class="sidebar-link {{ request()->is('dashboard/organization*') ? 'active' : '' }}">
                        <svg class="w-4.5 h-4.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" /></svg>
                        <span>{{ __('Organization') }}</span>
                    </a>

                    <a href="/dashboard/admin/users" id="sidebar-users"
                       class="sidebar-link {{ (request()->is('dashboard/admin/users*') || request()->is('dashboard/users*')) ? 'active' : '' }}">
                        <svg class="w-4.5 h-4.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>
                        <span>{{ __('Users & Teams') }}</span>
                    </a>

                    <a href="/dashboard/admin" id="sidebar-admin"
                       class="sidebar-link {{ request()->is('dashboard/admin*') ? 'active' : '' }}">
                        <svg class="w-4.5 h-4.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        <span>{{ __('Administration') }}</span>
                    </a>

                    <a href="/dashboard/settings" id="sidebar-settings"
                       class="sidebar-link {{ request()->is('dashboard/settings*') ? 'active' : '' }}">
                        <svg class="w-4.5 h-4.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        <span>{{ __('Settings') }}</span>
                    </a>
                </div>
            </nav>

            {{-- Sidebar User --}}
            <div class="border-t border-slate-200/80 dark:border-slate-800/80 p-3">
                @auth
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open"
                            class="w-full flex items-center gap-2.5 p-2 rounded-xl hover:bg-slate-100/80 dark:hover:bg-slate-800/60 transition-colors cursor-pointer"
                            aria-haspopup="true" :aria-expanded="open"
                            id="sidebar-user-menu-trigger">
                        <div class="avatar avatar-md avatar-brand flex-shrink-0">
                            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0 text-left">
                            <p class="text-xs font-semibold text-slate-900 dark:text-white truncate">{{ auth()->user()->name ?? 'User' }}</p>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <span class="badge badge-brand text-[10px] px-1.5 py-0 border-none bg-orange-100 dark:bg-orange-500/20 font-bold">{{ __('Admin') }}</span>
                                <p class="text-[11px] text-slate-400 dark:text-slate-500 truncate">{{ auth()->user()->email ?? '' }}</p>
                            </div>
                        </div>
                        <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" /></svg>
                    </button>
                    <div x-show="open" @click.outside="open = false"
                         class="dropdown absolute bottom-full left-0 right-0 mb-2 bg-white dark:bg-[#0D1322] border border-slate-200 dark:border-slate-800 rounded-xl shadow-xl shadow-slate-950/10 p-1"
                         x-cloak>
                        <a href="/dashboard/profile" class="dropdown-item rounded-lg" id="sidebar-user-profile">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                            {{ __('My Profile') }}
                        </a>
                        <form method="POST" action="/logout">
                            @csrf
                            <button type="submit" class="dropdown-item danger w-full text-left rounded-lg" id="sidebar-logout">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" /></svg>
                                {{ __('Sign Out') }}
                            </button>
                        </form>
                    </div>
                </div>
                @endauth
            </div>
        </aside>

        {{-- ═══ MAIN AREA ═══ --}}
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-slate-50 dark:bg-[#070B14] relative">

            {{-- Aura Estates Ambient Light Orbs --}}
            <div class="absolute -top-32 -left-32 w-96 h-96 bg-orange-500/10 dark:bg-orange-500/5 rounded-full blur-3xl pointer-events-none" aria-hidden="true"></div>
            <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-amber-500/10 dark:bg-amber-600/5 rounded-full blur-3xl pointer-events-none" aria-hidden="true"></div>

            {{-- Topbar --}}
            <header class="flex-shrink-0 h-16 bg-white/80 dark:bg-[#0A101D]/80 backdrop-blur-md border-b border-slate-200/80 dark:border-slate-800/70 border-t border-t-white/60 dark:border-t-white/10 flex items-center justify-between gap-4 px-4 lg:px-8 shadow-xs relative z-10" id="dashboard-topbar">
                {{-- Mobile sidebar toggle & Page Title --}}
                <div class="flex items-center gap-3 min-w-0">
                    <button @click="sidebarOpen = !sidebarOpen"
                            class="btn btn-ghost btn-icon lg:hidden flex-shrink-0"
                            aria-label="Toggle sidebar"
                            id="sidebar-toggle">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
                    </button>

                    <div class="min-w-0">
                        <h1 class="text-base font-bold text-slate-900 dark:text-white truncate font-display tracking-tight flex items-center gap-2">
                            <span>{{ __($topbarTitle) }}</span>
                        </h1>
                        @if(isset($breadcrumb))
                            <nav aria-label="Breadcrumb" class="breadcrumb-nav mt-0.5">
                                {{ $breadcrumb }}
                            </nav>
                        @elseif(View::hasSection('breadcrumb'))
                            <nav aria-label="Breadcrumb" class="breadcrumb-nav mt-0.5">
                                @yield('breadcrumb')
                            </nav>
                        @elseif($topbarTitle !== 'Dashboard' && !request()->is('dashboard'))
                            <nav aria-label="Breadcrumb" class="breadcrumb-nav mt-0.5">
                                <a href="/dashboard" class="breadcrumb-link">{{ __('Dashboard') }}</a>
                                <svg class="w-3 h-3 breadcrumb-separator" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                <span class="breadcrumb-current">{{ __($topbarTitle) }}</span>
                            </nav>
                        @endif
                    </div>
                </div>

                {{-- Center: Global Quick Command Search (Desktop) --}}
                <div class="hidden md:flex items-center flex-1 max-w-md mx-4">
                    <form action="{{ route('search') }}" method="GET" class="w-full relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </div>
                        <input type="text"
                               name="q"
                               id="dashboard-global-search-input"
                               value="{{ request('q') }}"
                               placeholder="{{ __('Search properties, transactions, clients... (⌘K)') }}"
                               class="w-full pl-9 pr-12 py-1.5 text-xs bg-slate-100/80 dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-800/80 rounded-xl text-slate-800 dark:text-slate-200 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all"
                               aria-label="Global search">
                        <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none">
                            <kbd class="px-1.5 py-0.5 text-[10px] font-mono font-semibold text-slate-400 dark:text-slate-500 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-md">⌘K</kbd>
                        </div>
                    </form>
                </div>

                {{-- Topbar Right --}}
                <div class="flex items-center gap-2.5 flex-shrink-0">
                    {{-- Language Switcher --}}
                    <x-language-switcher id="topbar-language-switcher" />

                    {{-- Dark Mode --}}
                    <x-theme-toggle id="topbar-dark-toggle" />

                    {{-- Notifications --}}
                    <button class="btn btn-ghost btn-icon rounded-xl relative hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer" aria-label="Notifications" id="topbar-notifications">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" /></svg>
                        <span class="absolute top-1 right-1 w-2 h-2 bg-orange-500 rounded-full ring-2 ring-white dark:ring-[#0A101D]" aria-hidden="true"></span>
                    </button>
                </div>
            </header>

            {{-- Page Content --}}
            <main class="flex-1 overflow-y-auto p-4 lg:p-7 bg-slate-50/50 dark:bg-[#070B14]" role="main">
                @if (session('success'))
                    <div class="mb-5 alert alert-success" role="alert">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.745 3.745 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" /></svg>
                        <div class="flex-1">
                            <p class="font-medium text-sm">{{ session('success') }}</p>
                        </div>
                    </div>
                @endif

                @if (isset($errors) && $errors->any())
                    <div class="mb-5 alert alert-error" role="alert">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <div class="flex-1">
                            <h3 class="font-semibold text-sm mb-1">
                                {{ __('There were :count errors with your submission', ['count' => $errors->count()]) }}
                            </h3>
                            <ul class="list-disc pl-5 space-y-1 text-xs opacity-90">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                {{-- Page heading is provided by the topbar (title + breadcrumb); no duplicated hero. --}}
                <div class="page-content-wrapper w-full">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    {{-- Toast Container --}}
    <div id="toast-container" class="fixed bottom-6 right-6 z-[80] flex flex-col gap-3" aria-live="polite"></div>

    <script>
        document.addEventListener('keydown', function(e) {
            if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
                const searchInput = document.getElementById('dashboard-global-search-input');
                if (searchInput) {
                    e.preventDefault();
                    searchInput.focus();
                    searchInput.select();
                }
            }
        });
    </script>

    @stack('scripts')
</body>
</html>

