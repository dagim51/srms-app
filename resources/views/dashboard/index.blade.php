<x-layouts.dashboard>
    <x-slot name="title">{{ __('Dashboard') }}</x-slot>

    @section('page-title', __('Dashboard'))

    <div class="space-y-6">

        {{-- Operational Status & Quick Action Strip --}}
        <div class="card p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-l-4 border-l-orange-500 bg-white dark:bg-[#0D1322] border-slate-200/80 dark:border-slate-800/80 shadow-xs">
            <div class="flex items-center gap-3.5 min-w-0">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-orange-500 to-amber-500 text-white flex items-center justify-center font-bold flex-shrink-0 shadow-sm shadow-orange-500/25">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" /></svg>
                </div>
                <div class="min-w-0">
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2 flex-wrap font-display">
                        <span>{{ __('Operational Overview') }}</span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800/60">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> {{ __('Live Marketplace') }}
                        </span>
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 truncate mt-0.5">{{ __('Real-time status for Ethiopian real estate inventory, active inquiries, and client operations.') }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2 self-start sm:self-center flex-shrink-0 flex-wrap">
                <a href="/dashboard/listings/create" class="btn btn-primary btn-sm flex-shrink-0 shadow-sm shadow-orange-500/20" id="dash-create-listing">
                    <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    <span>{{ __('New Listing') }}</span>
                </a>
                <a href="/dashboard/admin/users/create" class="btn btn-outline btn-sm flex-shrink-0 border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800" id="dash-create-user">
                    <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
                    <span>{{ __('Add User') }}</span>
                </a>
                <a href="/dashboard/organization/create" class="btn btn-outline btn-sm flex-shrink-0 border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800" id="dash-create-org">
                    <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                    <span>{{ __('New Org') }}</span>
                </a>
            </div>
        </div>

        {{-- Key Metrics --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4" aria-label="Key metrics">
            <x-card-stat
                :label="__('Active Listings')"
                value="0"
                :caption="__('Live in marketplace')"
                :badge="__('Active')"
                badgeVariant="success"
                iconColor="orange"
                role="region"
                aria-label="Active listings">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg>
                </x-slot:icon>
            </x-card-stat>

            <x-card-stat
                :label="__('New Inquiries')"
                value="0"
                :caption="__('Awaiting review')"
                :badge="__('Pending')"
                badgeVariant="warning"
                iconColor="blue"
                role="region"
                aria-label="Pending inquiries">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" /></svg>
                </x-slot:icon>
            </x-card-stat>

            <x-card-stat
                :label="__('Active Bookings')"
                value="0"
                :caption="__('Stays & viewings')"
                :badge="__('Confirmed')"
                badgeVariant="neutral"
                iconColor="purple"
                role="region"
                aria-label="Bookings">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" /></svg>
                </x-slot:icon>
            </x-card-stat>

            <x-card-stat
                :label="__('Saved Properties')"
                value="0"
                :caption="__('Client collections')"
                :badge="__('Saved')"
                badgeVariant="neutral"
                iconColor="rose"
                role="region"
                aria-label="Saved properties">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" /></svg>
                </x-slot:icon>
            </x-card-stat>
        </div>

        {{-- Empty State: My Listings --}}
        <div class="card bg-white dark:bg-[#0D1322] border-slate-200/80 dark:border-slate-800/80">
            <div class="card-header border-b border-slate-100 dark:border-slate-800/80 px-6 py-4 flex items-center justify-between">
                <h3 class="card-title text-slate-900 dark:text-white font-bold font-display text-base">{{ __('My Listings') }}</h3>
                <a href="/dashboard/listings" class="btn btn-ghost btn-sm text-orange-600 hover:text-orange-700 dark:text-orange-400 hover:bg-orange-50 dark:hover:bg-orange-500/10 font-medium">
                    {{ __('View all') }}
                    <svg class="w-3.5 h-3.5 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                </a>
            </div>
            <div class="card-body p-6">
                <div class="empty-state py-8 text-center">
                    <div class="w-14 h-14 rounded-2xl bg-orange-50 dark:bg-orange-950/40 text-orange-600 dark:text-orange-400 flex items-center justify-center mx-auto mb-4 border border-orange-200/50 dark:border-orange-800/40 shadow-xs shadow-orange-500/10">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg>
                    </div>
                    <h4 class="font-display font-bold text-slate-900 dark:text-white text-base mb-1">{{ __('No listings yet') }}</h4>
                    <p class="text-slate-500 dark:text-slate-400 text-xs sm:text-sm mb-5 max-w-sm mx-auto">{{ __('Create your first property listing to reach potential buyers and tenants across Ethiopia.') }}</p>
                    <a href="/dashboard/listings/create" class="btn btn-primary btn-sm shadow-sm shadow-orange-500/20" id="dash-empty-create-listing">
                        <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        {{ __('Create First Listing') }}
                    </a>
                </div>
            </div>
        </div>

        {{-- Recent Activity (placeholder) --}}
        <div class="card bg-white dark:bg-[#0D1322] border-slate-200/80 dark:border-slate-800/80">
            <div class="card-header border-b border-slate-100 dark:border-slate-800/80 px-6 py-4">
                <h3 class="card-title text-slate-900 dark:text-white font-bold font-display text-base">{{ __('Recent Activity') }}</h3>
            </div>
            <div class="card-body p-6">
                <div class="empty-state py-6 text-center">
                    <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <p class="text-slate-500 dark:text-slate-400 text-xs sm:text-sm">{{ __('Real estate activity and client audit trails will appear here') }}</p>
                </div>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
