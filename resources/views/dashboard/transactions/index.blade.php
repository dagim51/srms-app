<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Property Deals & Escrow Pipeline') }}</x-slot>
    <x-slot:breadcrumb>
        <x-breadcrumb :items="[
            ['label' => __('Dashboard'), 'url' => route('dashboard')],
            ['label' => __('Transactions & Pipeline')]
        ]" />
    </x-slot:breadcrumb>

    <div class="space-y-6">
        {{-- Standardized Page Header Card --}}
        <x-page-header
            title="Property Deals & Escrow Pipeline"
            description="Oversee verified real estate purchases, commercial bank escrow holdings, cadastral title deed transfers, and milestone disbursements."
            icon="banknotes"
            badge="Bank Escrow Protected">
            <x-slot name="actions">
                <a href="{{ route('dashboard.finance.transactions') }}" class="btn btn-secondary inline-flex items-center gap-2 text-xs font-bold py-2.5 px-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-white hover:border-orange-500 transition-all shadow-xs">
                    <svg class="w-4 h-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                    <span>{{ __('Financial Ledger') }}</span>
                </a>
                <a href="{{ route('properties.index') }}" class="btn btn-primary inline-flex items-center gap-2 text-xs font-bold py-2.5 px-4 rounded-xl bg-gradient-to-r from-orange-500 to-amber-600 hover:from-orange-600 hover:to-amber-700 text-white shadow-md shadow-orange-500/20 active:scale-95 transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    <span>{{ __('Initiate Property Deal') }}</span>
                </a>
            </x-slot>
        </x-page-header>

        {{-- Top KPI Stat Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="cinematic-fade-up cinematic-stagger-1">
                <x-card-stat
                    :label="__('Total Pipeline Volume')"
                    :value="$metrics['total_volume']"
                    iconColor="orange"
                    icon="banknotes"
                    :badge="__('Active Deals')"
                    badgeVariant="brand"
                    caption="{{ __('Gross agreed deal values') }}"
                />
            </div>
            <div class="cinematic-fade-up cinematic-stagger-2">
                <x-card-stat
                    :label="__('Secured in Escrow')"
                    :value="$metrics['escrow_locked']"
                    iconColor="emerald"
                    icon="check"
                    :badge="__('Locked in Bank')"
                    badgeVariant="success"
                    caption="{{ __('CBE and Awash Escrow vaults') }}"
                />
            </div>
            <div class="cinematic-fade-up cinematic-stagger-3">
                <x-card-stat
                    :label="__('Cadastral & Title Reviews')"
                    :value="$metrics['active_verifications']"
                    iconColor="amber"
                    icon="clock"
                    :badge="__('In Verification')"
                    badgeVariant="warning"
                    caption="{{ __('Land Bureau deed checks') }}"
                />
            </div>
            <div class="cinematic-fade-up cinematic-stagger-4">
                <x-card-stat
                    :label="__('Deeds Transferred & Closed')"
                    :value="$metrics['closed_deals']"
                    iconColor="blue"
                    icon="sparkles"
                    :badge="__('Completed')"
                    badgeVariant="info"
                    caption="{{ __('Successful property handovers') }}"
                />
            </div>
        </div>

        {{-- Filter Console & Search Form --}}
        <div class="cinematic-fade-up cinematic-stagger-2 bg-white dark:bg-[#1E293B] rounded-xl p-4 border border-warm dark:border-warm shadow-sm space-y-4 mb-6">
            {{-- Deal Stage Status Tabs --}}
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 p-1 bg-slate-100 dark:bg-slate-900 rounded-xl border border-warm dark:border-[#273244] text-xs">
                @php
                    $statusTabs = [
                        'all' => __('All Deals'),
                        'escrow_locked' => __('Escrow Funds Locked'),
                        'cadastral_review' => __('Cadastral & Title Review'),
                        'tax_clearance' => __('Tax & Legal Clearance'),
                        'completed' => __('Deed Transferred & Closed'),
                    ];
                @endphp
                @foreach($statusTabs as $tabKey => $tabLabel)
                    <a href="{{ request()->fullUrlWithQuery(['status' => $tabKey === 'all' ? null : $tabKey]) }}"
                       class="px-3.5 py-1.5 rounded-lg whitespace-nowrap transition-all duration-200 font-bold {{ ($currentStatus === $tabKey || (!$currentStatus && $tabKey === 'all')) ? 'bg-[#C2410C] text-white shadow-md shadow-orange-500/25' : 'text-slate-600 hover:text-[#C2410C] hover:bg-orange-50/50 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800/50 font-semibold' }}">
                        {{ $tabLabel }}
                    </a>
                @endforeach
            </div>

            {{-- Multi-Criteria Form --}}
            <form action="{{ route('dashboard.transactions') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-12 gap-3 pt-2 border-t border-warm dark:border-warm items-center">
                @if($currentStatus && $currentStatus !== 'all')
                    <input type="hidden" name="status" value="{{ $currentStatus }}">
                @endif

                {{-- Keyword Input --}}
                <div class="md:col-span-6 relative">
                    <input type="text" name="q" value="{{ $search }}" placeholder="{{ __('Search by Deal ID (TRX-...), Title Deed No, Buyer, or Property...') }}"
                           class="form-input search-input w-full pl-9 pr-8 text-xs">
                    <svg class="w-4 h-4 text-muted absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                    @if(!empty($search))
                        <a href="{{ route('dashboard.transactions', request()->except('q')) }}" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200" title="{{ __('Clear search') }}">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </a>
                    @endif
                </div>

                {{-- Deal Type Selector --}}
                <div class="md:col-span-4">
                    <select name="type" onchange="this.form.submit()" class="form-select w-full text-xs">
                        <option value="all" {{ $currentType === 'all' ? 'selected' : '' }}>{{ __('All Deal Types') }}</option>
                        <option value="Purchase Escrow" {{ $currentType === 'Purchase Escrow' ? 'selected' : '' }}>{{ __('Outright Purchase Escrow') }}</option>
                        <option value="Off-Plan Milestone" {{ $currentType === 'Off-Plan Milestone' ? 'selected' : '' }}>{{ __('Off-Plan Milestone Construction') }}</option>
                        <option value="Land Title Transfer" {{ $currentType === 'Land Title Transfer' ? 'selected' : '' }}>{{ __('Land Title & Parcel Transfer') }}</option>
                    </select>
                </div>

                {{-- Submit & Clear --}}
                <div class="md:col-span-2 flex items-center gap-2">
                    <button type="submit" class="btn btn-secondary btn-sm text-xs py-2 px-3 w-full justify-center">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                        <span>{{ __('Filter') }}</span>
                    </button>
                    @if(!empty($search) || ($currentStatus && $currentStatus !== 'all') || ($currentType && $currentType !== 'all'))
                        <a href="{{ route('dashboard.transactions') }}" class="p-2 text-muted hover:text-orange-600 dark:hover:text-orange-400" title="{{ __('Reset Filters') }}">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Transactions & Deals Pipeline Cards --}}
        @if($deals->isEmpty())
            <x-empty-state
                icon="banknotes"
                title="{{ __('No pipeline deals found') }}"
                description="{{ __('No transaction deals match the selected criteria or search filter. Create a formal offer to initiate a deal.') }}"
                action="{{ route('dashboard.transactions') }}"
                actionText="{{ __('Reset Filters') }}"
            />
        @else
            <div class="space-y-5">
                @foreach($deals as $deal)
                    <div class="bg-white/95 dark:bg-slate-900/95 backdrop-blur-xl border border-slate-200/90 dark:border-slate-800/90 rounded-3xl p-5 sm:p-6 shadow-xl shadow-slate-900/5 hover:border-orange-500/40 transition-all duration-300">
                        {{-- Top Header Row --}}
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 pb-4 border-b border-slate-100 dark:border-slate-800/80">
                            <div class="flex items-center flex-wrap gap-2.5">
                                <span class="font-mono text-sm font-black text-slate-950 dark:text-white px-3 py-1 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80">
                                    {{ $deal['id'] }}
                                </span>
                                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-orange-50 dark:bg-orange-950/40 text-orange-600 dark:text-orange-400 border border-orange-200/60 dark:border-orange-800/40">
                                    {{ $deal['type'] }}
                                </span>
                                <span class="text-xs text-slate-400 dark:text-slate-500 font-medium">
                                    {{ __('Initiated:') }} {{ date('M d, Y', strtotime($deal['initiated_at'])) }}
                                </span>
                            </div>

                            <div class="flex items-center gap-3">
                                @php
                                    $badgeClasses = [
                                        'escrow_locked' => 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800/40',
                                        'cadastral_review' => 'bg-orange-50 dark:bg-orange-950/40 text-orange-700 dark:text-orange-300 border-orange-200 dark:border-orange-800/40',
                                        'tax_clearance' => 'bg-sky-50 dark:bg-sky-950/40 text-sky-700 dark:text-sky-300 border-sky-200 dark:border-sky-800/40',
                                        'completed' => 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/40',
                                    ];
                                    $currentBadge = $badgeClasses[$deal['status']] ?? 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300';
                                @endphp
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border {{ $currentBadge }}">
                                    @if($deal['status'] === 'completed')
                                        <svg class="w-3.5 h-3.5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                                    @else
                                        <span class="w-2 h-2 rounded-full bg-orange-500 animate-pulse"></span>
                                    @endif
                                    <span>{{ $deal['status_label'] }}</span>
                                </span>

                                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium hidden sm:inline">
                                    {{ __('Est. Close:') }} <strong class="text-slate-900 dark:text-white">{{ date('M d, Y', strtotime($deal['est_completion'])) }}</strong>
                                </span>
                            </div>
                        </div>

                        {{-- Property & Financial Details Grid --}}
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 py-5 border-b border-slate-100 dark:border-slate-800/80">
                            {{-- Property Summary (md:col-span-5) --}}
                            <div class="md:col-span-5 space-y-2">
                                <p class="text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">{{ __('Property & Cadastral Certificate') }}</p>
                                <h3 class="text-base sm:text-lg font-display font-extrabold text-slate-950 dark:text-white leading-tight">
                                    {{ $deal['property_title'] }}
                                </h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-1.5 font-medium">
                                    <svg class="w-3.5 h-3.5 text-orange-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.242-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    <span>{{ $deal['property_location'] }}</span>
                                    <span class="text-slate-300 dark:text-slate-700">&bull;</span>
                                    <span class="font-mono text-slate-600 dark:text-slate-300 font-bold">{{ $deal['property_code'] }}</span>
                                </p>
                                <div class="pt-1 flex items-center flex-wrap gap-2 text-[11px]">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-mono font-bold">
                                        <svg class="w-3 h-3 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                        {{ __('Deed:') }} {{ $deal['title_deed_no'] }}
                                    </span>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-mono font-bold">
                                        <svg class="w-3 h-3 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.242-4.243a8 8 0 1111.314 0z" /></svg>
                                        {{ $deal['cadastral_code'] }}
                                    </span>
                                </div>
                            </div>

                            {{-- Financial & Escrow Account (md:col-span-4) --}}
                            <div class="md:col-span-4 space-y-2 border-l border-slate-100 dark:border-slate-800/80 md:pl-6">
                                <p class="text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">{{ __('Deal Value & Escrow Hold') }}</p>
                                <div>
                                    <p class="text-xl sm:text-2xl font-black font-display text-slate-950 dark:text-white tracking-tight">
                                        {{ $deal['total_amount_formatted'] }}
                                    </p>
                                    <p class="text-xs font-extrabold text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5 mt-0.5">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>
                                        <span>{{ $deal['escrow_amount_formatted'] }}</span>
                                    </p>
                                </div>
                                <div class="pt-1 text-xs text-slate-600 dark:text-slate-400">
                                    <span class="text-[11px] block font-medium">{{ __('Vault Partner:') }}</span>
                                    <span class="font-bold text-slate-900 dark:text-white flex items-center gap-1.5 mt-0.5">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                        {{ $deal['bank_partner'] }}
                                    </span>
                                    <span class="font-mono text-[11px] text-slate-500 dark:text-slate-400 block mt-0.5">{{ $deal['escrow_account'] }}</span>
                                </div>
                            </div>

                            {{-- Parties In Deal (md:col-span-3) --}}
                            <div class="md:col-span-3 space-y-3 border-l border-slate-100 dark:border-slate-800/80 md:pl-6">
                                <p class="text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">{{ __('Verified Parties') }}</p>
                                
                                {{-- Buyer --}}
                                <div class="flex items-center gap-2.5">
                                    <img src="{{ $deal['buyer_avatar'] }}" alt="{{ $deal['buyer_name'] }}" class="w-8 h-8 rounded-full object-cover border border-slate-200 dark:border-slate-700">
                                    <div class="min-w-0 flex-1">
                                        <p class="text-[10px] uppercase font-bold text-slate-400 leading-tight">{{ __('Buyer') }}</p>
                                        <p class="text-xs font-bold text-slate-950 dark:text-white truncate">{{ $deal['buyer_name'] }}</p>
                                    </div>
                                </div>

                                {{-- Seller --}}
                                <div class="flex items-center gap-2.5">
                                    <img src="{{ $deal['seller_avatar'] }}" alt="{{ $deal['seller_name'] }}" class="w-8 h-8 rounded-full object-cover border border-slate-200 dark:border-slate-700">
                                    <div class="min-w-0 flex-1">
                                        <p class="text-[10px] uppercase font-bold text-slate-400 leading-tight">{{ __('Seller / Developer') }}</p>
                                        <p class="text-xs font-bold text-slate-950 dark:text-white truncate">{{ $deal['seller_name'] }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 5-Stage Escrow Lifecycle Stepper --}}
                        <div class="py-5">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-xs font-extrabold uppercase tracking-wider text-slate-900 dark:text-white flex items-center gap-2">
                                    <span>{{ __('Deal Lifecycle & Escrow Progression') }}</span>
                                    <span class="text-orange-600 dark:text-orange-400 font-bold">({{ __('Stage') }} {{ $deal['current_stage'] }} / 5)</span>
                                </span>
                                <span class="text-xs text-slate-500 dark:text-slate-400 font-bold">
                                    {{ round(($deal['current_stage'] / 5) * 100) }}% {{ __('Completed') }}
                                </span>
                            </div>

                            {{-- Progress Bar --}}
                            <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 overflow-hidden mb-4">
                                <div class="bg-gradient-to-r from-orange-500 via-amber-500 to-emerald-500 h-2 rounded-full transition-all duration-700"
                                     style="width: {{ ($deal['current_stage'] / 5) * 100 }}%"></div>
                            </div>

                            {{-- Milestone Checkpoints --}}
                            <div class="grid grid-cols-1 sm:grid-cols-5 gap-2.5">
                                @foreach($deal['milestones'] as $index => $ms)
                                    @php
                                        $stepNum = $index + 1;
                                        $isPast = $stepNum < $deal['current_stage'];
                                        $isCurrent = $stepNum === $deal['current_stage'];
                                        $isFuture = $stepNum > $deal['current_stage'];
                                    @endphp
                                    <div class="p-2.5 rounded-2xl border transition-all {{ $isCurrent ? 'bg-orange-50/80 dark:bg-orange-950/20 border-orange-500/40 ring-1 ring-orange-500/20' : ($isPast ? 'bg-slate-50/80 dark:bg-slate-850/50 border-slate-200/80 dark:border-slate-800' : 'bg-transparent border-slate-100 dark:border-slate-850 opacity-60') }}">
                                        <div class="flex items-center gap-2 mb-1">
                                            @if($isPast || $ms['status'] === 'completed')
                                                <div class="w-5 h-5 rounded-full bg-emerald-500 text-white flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                                                </div>
                                            @elseif($isCurrent)
                                                <div class="w-5 h-5 rounded-full bg-orange-500 text-white flex items-center justify-center font-black text-[10px] flex-shrink-0 animate-pulse">
                                                    {{ $stepNum }}
                                                </div>
                                            @else
                                                <div class="w-5 h-5 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 flex items-center justify-center font-bold text-[10px] flex-shrink-0">
                                                    {{ $stepNum }}
                                                </div>
                                            @endif
                                            <span class="text-[11px] font-bold text-slate-900 dark:text-white truncate">{{ $ms['title'] }}</span>
                                        </div>
                                        <p class="text-[10px] text-slate-500 dark:text-slate-400 line-clamp-1 leading-tight">{{ $ms['note'] }}</p>
                                        <p class="text-[10px] font-medium text-slate-400 mt-1">{{ $ms['date'] }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Card Actions Footer --}}
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-4 border-t border-slate-100 dark:border-slate-800/80">
                            <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                                <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg>
                                <span>{{ __('Authenticated by SRMS Cadastral Deed & Legal Escrow Protection') }}</span>
                            </div>

                            <div class="flex items-center gap-2.5">
                                <a href="{{ route('dashboard.finance.transactions.show', $deal['id']) }}" class="px-4 py-2 rounded-xl text-xs font-bold bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-900 dark:text-white transition-colors flex items-center gap-1.5 active:scale-95">
                                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                                    <span>{{ __('Bank Receipt') }}</span>
                                </a>

                                <a href="{{ route('dashboard.transactions.show', $deal['id']) }}" class="px-5 py-2 rounded-xl text-xs font-extrabold bg-gradient-to-r from-orange-500 to-amber-600 hover:from-orange-600 hover:to-amber-700 text-white shadow-md shadow-orange-500/25 flex items-center gap-1.5 active:scale-95 transition-all">
                                    <span>{{ __('Inspect Escrow & Deed') }}</span>
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.dashboard>
