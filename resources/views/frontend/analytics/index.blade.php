<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Market Intelligence') }}</x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="mb-8 flex justify-between items-end">
            <div>
                <h1 class="text-3xl font-extrabold font-display text-[#0F172A] dark:text-white tracking-tight">{{ __('Reporting & Analytics') }}</h1>
                <p class="text-[#4B5563] dark:text-[#CBD5E1] mt-2">{{ __('Insights, custom reports, and market intelligence.') }}</p>
            </div>
            <div class="flex gap-4">
                <input type="date" class="input">
                <button class="btn btn-primary">{{ __('Refresh Data') }}</button>
            </div>
        </div>

        @include('frontend.analytics.partials.tabs')

        <!-- Core Metrics -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6 mb-8">
            <x-card-stat
                :label="__('Active Listings')"
                :value="$metrics['active_listings']"
                :badge="__('Active')"
                badgeVariant="brand"
                iconColor="orange">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg>
                </x-slot:icon>
            </x-card-stat>
            <x-card-stat
                :label="__('Occupancy Rate')"
                :value="$metrics['occupancy_rate']"
                :badge="__('Occupancy')"
                badgeVariant="success"
                iconColor="emerald">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
                </x-slot:icon>
            </x-card-stat>
            <x-card-stat
                :label="__('Avg Daily Rate (ADR)')"
                :value="$metrics['avg_daily_rate']"
                :badge="__('ADR')"
                badgeVariant="info"
                iconColor="blue">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </x-slot:icon>
            </x-card-stat>
            <x-card-stat
                :label="__('Total Revenue')"
                :value="$metrics['total_revenue']"
                :badge="__('Revenue')"
                badgeVariant="success"
                iconColor="emerald">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5m.75-9l3-3 2.143 2.143L15.75 6" /></svg>
                </x-slot:icon>
            </x-card-stat>
        </div>

        <!-- Market Intelligence -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8 shadow-sm text-white mb-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold flex items-center gap-2">
                    <svg class="w-6 h-6 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                    {{ __('Market Intelligence') }}
                </h2>
                <span class="badge badge-neutral bg-white/10 text-slate-300 border-0 text-xs">{{ __('Addis Ababa') }}</span>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div>
                    <p class="text-slate-400 text-sm font-medium mb-1">{{ __('Avg Price per m²') }}</p>
                    <p class="text-2xl font-black text-white">{{ $marketIntelligence['price_per_sqm'] }}</p>
                    <p class="text-xs text-slate-400 mt-1">{{ __('Based on verified transactions') }}</p>
                </div>
                <div>
                    <p class="text-slate-400 text-sm font-medium mb-1">{{ __('Demand Trend') }}</p>
                    <p class="text-2xl font-black text-success-400">{{ $marketIntelligence['demand_trend'] }}</p>
                    <p class="text-xs text-slate-400 mt-1">{{ __('vs same period last year') }}</p>
                </div>
                <div>
                    <p class="text-slate-400 text-sm font-medium mb-1">{{ __('Top Neighborhood') }}</p>
                    <p class="text-2xl font-black text-white">{{ $marketIntelligence['popular_neighborhood'] }}</p>
                    <p class="text-xs text-slate-400 mt-1">{{ __('Highest search volume') }}</p>
                </div>
                <div>
                    <p class="text-slate-400 text-sm font-medium mb-1">{{ __('Avg Time on Market') }}</p>
                    <p class="text-2xl font-black text-white">{{ $marketIntelligence['avg_time_on_market'] }}</p>
                    <p class="text-xs text-slate-400 mt-1">{{ __('Before contract signing') }}</p>
                </div>
            </div>
        </div>

        <!-- Chart Placeholder -->
        <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-3xl p-6 shadow-sm min-h-[16rem] flex items-center justify-center">
            <div class="text-center text-[#4B5563] dark:text-[#CBD5E1]">
                <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" /></svg>
                <p class="font-bold text-[#0F172A] dark:text-white">{{ __('Revenue & Occupancy Chart') }}</p>
                <p class="text-xs text-muted">({{ __('Chart component initialized') }})</p>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
