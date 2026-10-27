<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Deal & Escrow Audit: ') . $deal['id'] }}</x-slot>
    <x-slot:breadcrumb>
        <x-breadcrumb :items="[
            ['label' => __('Dashboard'), 'url' => route('dashboard')],
            ['label' => __('Transactions & Pipeline'), 'url' => route('dashboard.transactions')],
            ['label' => $deal['id']]
        ]" />
    </x-slot:breadcrumb>

    <div class="space-y-6 page-enter">
        <x-page-header
            :title="__('Deal & Escrow Audit: ') . $deal['id']"
            description="Complete transactional audit trail, escrow milestones, participant legal verification, and disbursement records."
            icon="cash"
            badge="Escrow Deal" />

        {{-- Back Navigation & Action Bar --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <a href="{{ route('dashboard.transactions') }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white transition-colors w-max">
                <svg class="w-4 h-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                <span>{{ __('Back to Deals Pipeline') }}</span>
            </a>

            <div class="flex items-center gap-2">
                <a href="{{ route('dashboard.finance.transactions.show', $deal['id']) }}" class="px-4 py-2 rounded-xl text-xs font-bold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-white hover:border-orange-500 transition-all shadow-xs flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                    <span>{{ __('Financial Receipt') }}</span>
                </a>
                <button type="button" onclick="window.print()" class="px-4 py-2 rounded-xl text-xs font-extrabold bg-gradient-to-r from-orange-500 to-amber-600 hover:from-orange-600 hover:to-amber-700 text-white shadow-md shadow-orange-500/25 active:scale-95 transition-all flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-1.076-.672-2.14-1.272-3.072M17.28 13.829c.24-1.076.672-2.14 1.272-3.072M9 17h6m-6 3h6m-9-6v5a2 2 0 002 2h10a2 2 0 002-2v-5M7 7V4a1 1 0 011-1h8a1 1 0 011 1v3" /></svg>
                    <span>{{ __('Print Escrow Certificate') }}</span>
                </button>
            </div>
        </div>

        {{-- Deal Main Banner --}}
        <div class="card bg-white/95 dark:bg-slate-900/95 backdrop-blur-xl border border-slate-200/90 dark:border-slate-800/90 rounded-3xl p-6 sm:p-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 border-b border-slate-100 dark:border-slate-800/80">
                <div>
                    <div class="flex items-center flex-wrap gap-2.5 mb-2">
                        <span class="font-mono text-sm font-black text-slate-950 dark:text-white px-3 py-1 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80">
                            {{ $deal['id'] }}
                        </span>
                        <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-orange-50 dark:bg-orange-950/40 text-orange-600 dark:text-orange-400 border border-orange-200/60 dark:border-orange-800/40">
                            {{ $deal['type'] }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/40">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            {{ $deal['status_label'] }}
                        </span>
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-display font-extrabold text-slate-950 dark:text-white tracking-tight">
                        {{ $deal['property_title'] }}
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 flex items-center gap-1.5 mt-1">
                        <svg class="w-4 h-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.242-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        <span>{{ $deal['property_location'] }}</span>
                        <span class="text-slate-300 dark:text-slate-700">&bull;</span>
                        <span class="font-mono text-slate-600 dark:text-slate-300 font-bold">{{ $deal['property_code'] }}</span>
                    </p>
                </div>

                <div class="text-left md:text-right bg-slate-50 dark:bg-slate-900/60 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-700/80">
                    <p class="text-[11px] font-black uppercase tracking-wider text-slate-400">{{ __('Total Transaction Value') }}</p>
                    <p class="text-2xl sm:text-3xl font-black font-display text-slate-950 dark:text-white tracking-tight mt-0.5">
                        {{ $deal['total_amount_formatted'] }}
                    </p>
                    <p class="text-xs font-bold text-emerald-600 dark:text-emerald-400 mt-1 flex items-center md:justify-end gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>
                        <span>{{ $deal['escrow_amount_formatted'] }}</span>
                    </p>
                </div>
            </div>

            {{-- 3-Column Escrow & Verification Breakdown --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6">
                {{-- Escrow Account Details --}}
                <div class="space-y-3">
                    <p class="text-xs font-black uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z" /></svg>
                        <span>{{ __('Bank Escrow Custody') }}</span>
                    </p>
                    <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-700/80 space-y-1.5 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-500">{{ __('Partner Bank:') }}</span>
                            <span class="font-bold text-slate-900 dark:text-white">{{ $deal['bank_partner'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">{{ __('Vault Account:') }}</span>
                            <span class="font-mono font-bold text-orange-600 dark:text-orange-400">{{ $deal['escrow_account'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">{{ __('Escrow Status:') }}</span>
                            <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ __('Funds Legally Held') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Title Deed & Cadastral Mapping --}}
                <div class="space-y-3">
                    <p class="text-xs font-black uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        <span>{{ __('Cadastral Registry Info') }}</span>
                    </p>
                    <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-700/80 space-y-1.5 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-500">{{ __('Deed Cert #:') }}</span>
                            <span class="font-mono font-bold text-slate-900 dark:text-white">{{ $deal['title_deed_no'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">{{ __('Cadastral Parcel:') }}</span>
                            <span class="font-mono font-bold text-slate-900 dark:text-white">{{ $deal['cadastral_code'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">{{ __('Survey Audit:') }}</span>
                            <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ __('PostGIS Boundary Clear') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Parties Involved --}}
                <div class="space-y-3">
                    <p class="text-xs font-black uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>
                        <span>{{ __('Contractual Parties') }}</span>
                    </p>
                    <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-700/80 space-y-2 text-xs">
                        <div class="flex items-center gap-2">
                            <img src="{{ $deal['buyer_avatar'] }}" alt="" class="w-5 h-5 rounded-full object-cover">
                            <span class="text-slate-500">{{ __('Buyer:') }}</span>
                            <span class="font-bold text-slate-900 dark:text-white truncate">{{ $deal['buyer_name'] }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <img src="{{ $deal['seller_avatar'] }}" alt="" class="w-5 h-5 rounded-full object-cover">
                            <span class="text-slate-500">{{ __('Seller:') }}</span>
                            <span class="font-bold text-slate-900 dark:text-white truncate">{{ $deal['seller_name'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Detailed Milestone Progress Log --}}
        <div class="card bg-white/95 dark:bg-slate-900/95 backdrop-blur-xl border border-slate-200/90 dark:border-slate-800/90 rounded-3xl p-6 sm:p-8 space-y-6">
            <h2 class="text-lg font-display font-extrabold text-slate-950 dark:text-white">
                {{ __('Official Deal Milestones & Escrow Release Sequence') }}
            </h2>

            <div class="relative pl-6 space-y-8 before:absolute before:left-2.5 before:top-3 before:bottom-3 before:w-0.5 before:bg-slate-200 dark:before:bg-slate-800">
                @foreach($deal['milestones'] as $idx => $m)
                    @php
                        $isDone = $m['status'] === 'completed';
                        $isCur = $m['status'] === 'in_progress';
                    @endphp
                    <div class="relative flex items-start gap-4">
                        {{-- Step Circle Marker --}}
                        <div class="absolute -left-6 top-1 w-5 h-5 rounded-full flex items-center justify-center {{ $isDone ? 'bg-emerald-500 text-white' : ($isCur ? 'bg-orange-500 text-white ring-4 ring-orange-500/20 animate-pulse' : 'bg-slate-200 dark:bg-slate-800 text-slate-500') }}">
                            @if($isDone)
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                            @else
                                <span class="text-[10px] font-black">{{ $idx + 1 }}</span>
                            @endif
                        </div>

                        <div class="flex-1 bg-slate-50 dark:bg-slate-900/60 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-700/80">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 mb-1">
                                <h3 class="text-sm font-bold text-slate-950 dark:text-white">{{ $m['title'] }}</h3>
                                <span class="text-xs font-mono text-slate-400 font-semibold">{{ $m['date'] }}</span>
                            </div>
                            <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">{{ $m['note'] }}</p>
                            
                            <div class="mt-2 pt-2 border-t border-slate-200/60 dark:border-slate-800/60 flex items-center justify-between text-[11px]">
                                <span class="text-slate-400">{{ __('Milestone Status:') }}</span>
                                <span class="font-bold uppercase tracking-wider {{ $isDone ? 'text-emerald-600 dark:text-emerald-400' : ($isCur ? 'text-orange-600 dark:text-orange-400' : 'text-slate-400') }}">
                                    {{ str_replace('_', ' ', $m['status']) }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-layouts.dashboard>
