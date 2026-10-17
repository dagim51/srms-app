<x-layouts.dashboard>
    <x-slot name="title">{{ __('Dashboard') }}</x-slot>

    @section('page-title', __('Dashboard'))

    <div x-data="dashboardAnalytics({
        initialData: {
            metrics: {{ Js::from($metrics) }},
            trends: {{ Js::from($trends) }},
            purposeDist: {{ Js::from($purposeDist) }},
            offerOutcomes: {{ Js::from($offerOutcomes) }}
        },
        currentPeriod: '{{ $filters['period'] ?? 'all' }}',
        currentPurpose: '{{ $filters['purpose'] ?? 'all' }}'
    })" x-init="init()" class="space-y-6">

        {{-- Standardized Page Header Card with Primary Accent #C2410C & Interactive Toolbar --}}
        <div class="cinematic-fade-up">
            <x-page-header
                title="Dashboard"
                description="Monitor key performance indicators, track recent activity, and review important system statistics."
                icon="chart"
                badge="Operational Overview & Analytics">
                <x-slot:actions>
                    {{-- Time Period Selector Pills --}}
                    <div class="inline-flex items-center bg-slate-100 dark:bg-slate-900/80 p-1 rounded-xl border border-slate-200/80 dark:border-slate-800 text-xs font-medium" role="group" aria-label="Time period selector">
                        <template x-for="p in [
                            { id: 'today', label: '{{ __('Today') }}' },
                            { id: 'week', label: '{{ __('7D') }}' },
                            { id: 'month', label: '{{ __('30D') }}' },
                            { id: 'quarter', label: '{{ __('90D') }}' },
                            { id: 'all', label: '{{ __('All') }}' }
                        ]" :key="p.id">
                            <button 
                                type="button"
                                @click="setPeriod(p.id)"
                                :class="currentPeriod === p.id 
                                    ? 'bg-white dark:bg-slate-800 text-orange-600 dark:text-orange-400 shadow-xs font-bold' 
                                    : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                                class="px-2.5 py-1 rounded-lg transition-all"
                                x-text="p.label">
                            </button>
                        </template>
                    </div>

                    {{-- Purpose Filter Dropdown --}}
                    <div class="relative">
                        <select 
                            x-model="currentPurpose" 
                            @change="fetchAnalytics()"
                            class="text-xs bg-slate-100 dark:bg-slate-900/80 text-slate-700 dark:text-slate-200 font-medium rounded-xl border border-slate-200/80 dark:border-slate-800 px-3 py-1.5 pr-7 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none transition-colors cursor-pointer"
                            aria-label="Filter by listing purpose">
                            <option value="all">{{ __('All Purposes') }}</option>
                            <option value="sale">{{ __('For Sale') }}</option>
                            <option value="rent">{{ __('For Rent') }}</option>
                            <option value="stay">{{ __('Short Stay') }}</option>
                            <option value="development">{{ __('Development') }}</option>
                        </select>
                    </div>

                    {{-- Loading Indicator Spinner --}}
                    <div x-show="isLoading" x-cloak class="flex items-center text-orange-500" title="{{ __('Refreshing analytics...') }}">
                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>

                    {{-- Action Button: New Listing --}}
                    <a href="/dashboard/listings/create" class="btn btn-primary btn-sm shadow-sm shadow-orange-500/20" id="dash-create-listing">
                        <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        <span>{{ __('New Listing') }}</span>
                    </a>
                </x-slot:actions>
            </x-page-header>
        </div>

        {{-- Aura Estates Signature Welcome Banner --}}
        <div class="cinematic-fade-up relative overflow-hidden rounded-2xl bg-gradient-to-r from-orange-500/[0.08] via-amber-500/[0.04] to-transparent border border-orange-200/70 dark:border-orange-900/40 p-5 sm:p-6 backdrop-blur-md">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 relative z-10">
                <div class="space-y-1.5">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-orange-100/90 dark:bg-orange-950/70 border border-orange-200/80 dark:border-orange-800/80 text-orange-700 dark:text-orange-300 text-[11px] font-semibold tracking-wide uppercase">
                        <span class="w-2 h-2 rounded-full bg-orange-500 animate-ping"></span>
                        <span>{{ __('Aura Pro Executive Intelligence') }}</span>
                    </div>
                    <h2 class="font-serif text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                        {{ __('Curating Capital Where') }} <span class="italic font-normal text-transparent bg-clip-text bg-gradient-to-r from-orange-600 via-amber-500 to-orange-400 dark:from-orange-400 dark:to-amber-300">{{ __('Architecture') }}</span> {{ __('Meets Liquidity.') }}
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 max-w-2xl leading-relaxed">
                        {{ __('Unified operations overview: tracking active property inventory, authenticated Diaspora inquiries, and automated escrow milestones across Ethiopia.') }}
                    </p>
                </div>
                <div class="flex items-center gap-2.5 sm:self-center flex-shrink-0">
                    <a href="/dashboard/properties/map" class="px-4 py-2 rounded-xl text-xs font-semibold bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 hover:text-orange-600 dark:hover:text-orange-400 border border-slate-200/80 dark:border-slate-700 shadow-xs hover:shadow-md transition-all active:scale-[0.98]">
                        {{ __('Explore GIS Map') }}
                    </a>
                    <a href="/dashboard/properties/create" class="px-4 py-2 rounded-xl text-xs font-semibold bg-gradient-to-r from-orange-600 to-orange-500 hover:from-orange-500 hover:to-orange-600 text-white shadow-md shadow-orange-500/20 hover:shadow-lg hover:shadow-orange-500/30 transition-all active:scale-[0.98]">
                        {{ __('Register Property') }}
                    </a>
                </div>
            </div>
        </div>

        {{-- Primary Metric KPI Cards Grid (Balanced 3-Column Executive Grid) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5" aria-label="Key Performance Indicators">
            
            {{-- 1. Active Listings --}}
            <div class="cinematic-fade-up cinematic-stagger-1">
                <x-card-stat
                    :label="__('Active Listings')"
                    x-text-value="metrics.active_listings.value"
                    :value="$metrics['active_listings']['value']"
                    :caption="$metrics['active_listings']['caption']"
                    :badge="$metrics['active_listings']['badge']"
                    :badgeVariant="$metrics['active_listings']['badgeVariant']"
                    iconColor="orange"
                    href="/dashboard/listings?status=published"
                    role="region"
                    aria-label="Active listings">
                    <x-slot:icon>
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                        </svg>
                    </x-slot:icon>
                </x-card-stat>
            </div>

            {{-- 2. Portfolio Value (ETB) --}}
            <div class="cinematic-fade-up cinematic-stagger-2">
                <x-card-stat
                    :label="__('Portfolio Value')"
                    x-text-value="metrics.portfolio_value.formatted"
                    :value="$metrics['portfolio_value']['formatted']"
                    :caption="$metrics['portfolio_value']['caption']"
                    :badge="$metrics['portfolio_value']['badge']"
                    badgeVariant="brand"
                    iconColor="emerald"
                    href="/properties"
                    role="region"
                    aria-label="Portfolio valuation">
                    <x-slot:icon>
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6H2.25m0 0H3m0 0h18m0 0h.75A.75.75 0 0122 6.75v.75m0 0h-.75m0 0a60.07 60.07 0 00-15.797-2.101c-.727-.198-1.453.342-1.453 1.096V4.5M3.75 4.5v11.25c0 .621.504 1.125 1.125 1.125h14.25c.621 0 1.125-.504 1.125-1.125V4.5M12 12.75a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z" />
                        </svg>
                    </x-slot:icon>
                </x-card-stat>
            </div>

            {{-- 3. Inquiries & Leads --}}
            <div class="cinematic-fade-up cinematic-stagger-3">
                <x-card-stat
                    :label="__('Inquiries & Leads')"
                    x-text-value="metrics.inquiries.total"
                    :value="$metrics['inquiries']['total']"
                    :caption="$metrics['inquiries']['caption']"
                    :badge="$metrics['inquiries']['badge']"
                    :badgeVariant="$metrics['inquiries']['badgeVariant']"
                    iconColor="blue"
                    href="/dashboard/messages"
                    role="region"
                    aria-label="Inquiries and leads">
                    <x-slot:icon>
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" />
                        </svg>
                    </x-slot:icon>
                </x-card-stat>
            </div>

            {{-- 4. Scheduled Viewings --}}
            <div class="cinematic-fade-up cinematic-stagger-4">
                <x-card-stat
                    :label="__('Tours & Viewings')"
                    x-text-value="metrics.viewings.scheduled"
                    :value="$metrics['viewings']['scheduled']"
                    :caption="$metrics['viewings']['caption']"
                    :badge="$metrics['viewings']['badge']"
                    badgeVariant="neutral"
                    iconColor="purple"
                    href="/dashboard/viewings"
                    role="region"
                    aria-label="Scheduled viewings">
                    <x-slot:icon>
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                        </svg>
                    </x-slot:icon>
                </x-card-stat>
            </div>

            {{-- 5. Active Offers in Pipeline --}}
            <div class="cinematic-fade-up cinematic-stagger-5">
                <x-card-stat
                    :label="__('Negotiations')"
                    x-text-value="metrics.offers.count"
                    :value="$metrics['offers']['count']"
                    :caption="$metrics['offers']['caption']"
                    :badge="$metrics['offers']['badge']"
                    :badgeVariant="$metrics['offers']['badgeVariant']"
                    iconColor="amber"
                    href="/dashboard/offers"
                    role="region"
                    aria-label="Active offers">
                    <x-slot:icon>
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </x-slot:icon>
                </x-card-stat>
            </div>

            {{-- 6. Revenue Collected --}}
            <div class="cinematic-fade-up cinematic-stagger-6">
                <x-card-stat
                    :label="__('Settled Revenue')"
                    x-text-value="metrics.revenue.formatted"
                    :value="$metrics['revenue']['formatted']"
                    :caption="$metrics['revenue']['caption']"
                    :badge="$metrics['revenue']['badge']"
                    badgeVariant="success"
                    iconColor="rose"
                    href="/dashboard/finance/ledger"
                    role="region"
                    aria-label="Settled revenue">
                    <x-slot:icon>
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                        </svg>
                    </x-slot:icon>
                </x-card-stat>
            </div>

        </div>

        {{-- Interactive Visualization Charts Section --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 cinematic-fade-up cinematic-stagger-2">

            {{-- 1. Main Trend Chart (Spans 2 cols) --}}
            <div class="luxury-dashboard-card p-6 lg:col-span-2 flex flex-col justify-between">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800/80">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-orange-500/10 dark:bg-orange-500/20 text-orange-600 dark:text-orange-400 flex items-center justify-center font-bold text-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" /></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white font-display">
                                {{ __('Market Velocity & Engagement Trends') }}
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                {{ __('Tracking inventory growth, client inquiries, and formal offers over time.') }}
                            </p>
                        </div>
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700/60 font-mono">
                        {{ __('Monthly Velocity') }}
                    </span>
                </div>
                
                {{-- Chart Canvas Container --}}
                <div class="relative w-full h-72 pt-4">
                    <canvas id="dashboardTrendChart"></canvas>
                </div>
            </div>

            {{-- 2. Purpose Distribution (Doughnut) --}}
            <div class="luxury-dashboard-card p-6 flex flex-col justify-between">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100 dark:border-slate-800/80">
                    <div class="w-9 h-9 rounded-xl bg-sky-500/10 dark:bg-sky-500/20 text-sky-600 dark:text-sky-400 flex items-center justify-center font-bold text-sm">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 107.5 7.5h-7.5V6z" /><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0013.5 3v7.5z" /></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white font-display">
                            {{ __('Inventory by Purpose') }}
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            {{ __('Proportions across sales, rentals, stays, and projects.') }}
                        </p>
                    </div>
                </div>

                {{-- Doughnut Canvas Container --}}
                <div class="relative w-full h-72 pt-4 flex items-center justify-center">
                    <canvas id="dashboardPurposeChart"></canvas>
                </div>
            </div>

        </div>

        {{-- Workflow Flowchart: Real Estate Transaction & Moderation Lifecycle --}}
        <div class="luxury-dashboard-card p-6 cinematic-fade-up cinematic-stagger-3">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100 dark:border-slate-800/80">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-orange-500/10 dark:bg-orange-500/20 text-orange-600 dark:text-orange-400 flex items-center justify-center font-bold text-sm">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white font-display">
                            {{ __('SRMS Property Transaction & Moderation Lifecycle') }}
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            {{ __('Live operational workflow pipeline: from listing draft through verification, discovery, negotiation, and settlement.') }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 text-xs font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 px-3 py-1 rounded-full border border-emerald-200 dark:border-emerald-800/50">
                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>{{ __('Active Pipeline Tracking') }}</span>
                </div>
            </div>

            {{-- Flowchart Interactive Steps --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3.5 pt-5 relative">
                @foreach ($lifecycleStages as $stage)
                    <div class="relative flex flex-col p-4 rounded-xl border transition-all duration-200 
                        {{ $stage['count'] > 0 
                            ? 'bg-gradient-to-b from-orange-50/70 to-amber-50/40 dark:from-orange-950/30 dark:to-slate-900/60 border-orange-200/90 dark:border-orange-900/60 shadow-xs' 
                            : 'bg-slate-50/70 dark:bg-slate-900/40 border-slate-200/60 dark:border-slate-800/60 opacity-85' }}">
                        
                        {{-- Step Header & Badge --}}
                        <div class="flex items-center justify-between gap-1 mb-2.5">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-lg text-xs font-bold font-mono bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 shadow-xs">
                                {{ $stage['step'] }}
                            </span>
                            <span class="text-xs font-bold font-mono px-2 py-0.5 rounded-full {{ $stage['count'] > 0 ? 'bg-orange-500 text-white shadow-xs' : 'bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }}">
                                {{ $stage['count'] }}
                            </span>
                        </div>

                        {{-- Step Title --}}
                        <h4 class="text-xs font-bold text-slate-900 dark:text-white font-display mb-1 truncate">
                            {{ $stage['name'] }}
                        </h4>

                        {{-- Step Description --}}
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed mb-3 flex-grow">
                            {{ $stage['description'] }}
                        </p>

                        {{-- Step Link --}}
                        <a href="{{ $stage['link'] }}" class="text-[11px] font-semibold text-orange-600 dark:text-orange-400 hover:text-orange-700 dark:hover:text-orange-300 inline-flex items-center gap-1 group mt-auto">
                            <span>{{ __('View records') }}</span>
                            <svg class="w-3 h-3 group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Operational Breakdowns: Offer Outcomes & Recent Activity Feed --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 cinematic-fade-up cinematic-stagger-4">

            {{-- Offer Negotiation Outcomes (Bar chart) --}}
            <div class="luxury-dashboard-card p-6 flex flex-col justify-between">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100 dark:border-slate-800/80">
                    <div class="w-9 h-9 rounded-xl bg-amber-500/10 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-sm">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white font-display">
                            {{ __('Deal Negotiation Velocity') }}
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            {{ __('Breakdown of accepted, countered, and pending transactions.') }}
                        </p>
                    </div>
                </div>
                <div class="relative w-full h-56 pt-3">
                    <canvas id="dashboardOutcomeChart"></canvas>
                </div>
            </div>

            {{-- Recent Activity & Audit Trail (Spans 2 cols) --}}
            <div class="luxury-dashboard-card p-6 lg:col-span-2">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800/80">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-emerald-500/10 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.745 3.745 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" /></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white font-display">
                                {{ __('Recent Operational Activity') }}
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                {{ __('Audit trail of new listings, incoming inquiries, and offer interactions.') }}
                            </p>
                        </div>
                    </div>
                    <a href="/dashboard/listings" class="text-xs font-semibold text-orange-600 dark:text-orange-400 hover:text-orange-700 dark:hover:text-orange-300 inline-flex items-center gap-1 group">
                        <span>{{ __('All activity') }}</span>
                        <svg class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                    </a>
                </div>

                <div class="pt-3">
                    @if (count($recentActivities) > 0)
                        <div class="divide-y divide-slate-100 dark:divide-slate-800/60">
                            @foreach ($recentActivities as $act)
                                <div class="py-3 flex items-center justify-between gap-4 first:pt-1 last:pb-1">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0 
                                            {{ $act['type'] === 'listing' ? 'bg-orange-50 dark:bg-orange-950/40 text-orange-600' : '' }}
                                            {{ $act['type'] === 'inquiry' ? 'bg-blue-50 dark:bg-blue-950/40 text-blue-600' : '' }}
                                            {{ $act['type'] === 'offer' ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600' : '' }}">
                                            @if ($act['type'] === 'listing')
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12" /></svg>
                                            @elseif ($act['type'] === 'inquiry')
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12" /></svg>
                                            @else
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659" /></svg>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <a href="{{ $act['link'] }}" class="text-xs font-bold text-slate-900 dark:text-white hover:text-orange-600 dark:hover:text-orange-400 truncate block">
                                                {{ $act['title'] }}
                                            </a>
                                            <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate mt-0.5">
                                                {{ $act['caption'] }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2.5 flex-shrink-0">
                                        <span class="text-[10px] font-bold font-mono px-2 py-0.5 rounded-full 
                                            {{ $act['badgeVariant'] === 'success' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300' : '' }}
                                            {{ $act['badgeVariant'] === 'warning' ? 'bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300' : '' }}
                                            {{ $act['badgeVariant'] === 'brand' ? 'bg-orange-50 text-orange-700 dark:bg-orange-950/50 dark:text-orange-300' : '' }}
                                            {{ $act['badgeVariant'] === 'info' ? 'bg-sky-50 text-sky-700 dark:bg-sky-950/50 dark:text-sky-300' : '' }}
                                            {{ $act['badgeVariant'] === 'error' ? 'bg-rose-50 text-rose-700 dark:bg-rose-950/50 dark:text-rose-300' : '' }}
                                            {{ $act['badgeVariant'] === 'neutral' ? 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300' : '' }}">
                                            {{ $act['badge'] }}
                                        </span>
                                        <span class="text-[11px] text-slate-400 font-mono hidden sm:inline">
                                            {{ $act['timestamp'] }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="py-8 text-center text-slate-500 dark:text-slate-400 text-xs">
                            <p>{{ __('No recent activity recorded yet.') }}</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>

    </div>

    @push('scripts')
    <script>
        function dashboardAnalytics(config) {
            return {
                metrics: config.initialData.metrics,
                currentPeriod: config.currentPeriod || 'all',
                currentPurpose: config.currentPurpose || 'all',
                isLoading: false,

                init() {
                    this.$nextTick(() => {
                        if (window.initDashboardCharts) {
                            window.initDashboardCharts(config.initialData);
                        }
                    });
                },

                setPeriod(period) {
                    if (this.currentPeriod === period) return;
                    this.currentPeriod = period;
                    this.fetchAnalytics();
                },

                async fetchAnalytics() {
                    this.isLoading = true;
                    try {
                        const params = new URLSearchParams({
                            period: this.currentPeriod,
                            purpose: this.currentPurpose
                        });

                        const res = await fetch(`/dashboard/analytics-data?${params.toString()}`, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        if (!res.ok) throw new Error('Network response was not ok');
                        const data = await res.json();

                        if (data.status === 'success') {
                            this.metrics = data.metrics;
                            if (window.updateDashboardCharts) {
                                window.updateDashboardCharts(data);
                            }
                        }
                    } catch (e) {
                        console.error('Failed to update dashboard analytics:', e);
                    } finally {
                        this.isLoading = false;
                    }
                }
            };
        }
    </script>
    @endpush

</x-layouts.dashboard>
