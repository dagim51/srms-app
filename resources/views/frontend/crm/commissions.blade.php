<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Commissions') }}</x-slot>

    <div class="max-w-7xl mx-auto page-enter space-y-6">
        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-sm flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" @click="$el.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800">&times;</button>
            </div>
        @endif

        <x-page-header
            title="Commissions Summary"
            description="Track real estate agent sales commissions, payout statuses, milestone releases, and split accounting."
            icon="cash"
            badge="Commissions" />

        @include('frontend.crm.partials.tabs')

        {{-- Actions & Filter Toolbar --}}
        <div class="card cinematic-fade-up cinematic-stagger-2 bg-white dark:bg-[#1E293B] rounded-xl p-4 border border-warm dark:border-warm space-y-4 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-muted dark:text-white">{{ __('Agent Commission Ledger') }}</h2>
                    <p class="text-xs text-muted dark:text-muted">{{ __('Calculated deal splits, agent earnings, and finance payout approvals') }}</p>
                </div>
            </div>

            {{-- Filter Form --}}
            <form method="GET" action="{{ route('dashboard.crm.commissions') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-12 gap-3 pt-2 border-t border-warm dark:border-warm items-center">
                <div class="md:col-span-6 relative">
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="{{ __('Search commissions by deal ID, type, or agent name...') }}"
                           class="form-input search-input w-full pl-9 pr-8 text-xs">
                    <svg class="w-4 h-4 text-muted absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                    @if(request('search'))
                        <a href="{{ route('dashboard.crm.commissions', request()->except('search')) }}" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200" title="{{ __('Clear search') }}">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </a>
                    @endif
                </div>

                <div class="md:col-span-4">
                    <select name="status" onchange="this.form.submit()" class="form-select w-full text-xs">
                        <option value="">{{ __('All Payout Statuses') }}</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('Pending Review') }}</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>{{ __('Approved for Payout') }}</option>
                        <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>{{ __('Paid & Settled') }}</option>
                    </select>
                </div>

                <div class="md:col-span-2 flex items-center gap-2">
                    <button type="submit" class="btn btn-secondary btn-sm text-xs py-2 px-3 w-full justify-center">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                        <span>{{ __('Filter') }}</span>
                    </button>

                    @if(request()->hasAny(['search', 'status']))
                        <a href="{{ route('dashboard.crm.commissions') }}" class="p-2 text-muted hover:text-orange-600 dark:hover:text-orange-400" title="{{ __('Reset Filters') }}">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <div class="card bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-3xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface dark:bg-surface/50 border-b border-warm dark:border-warm">
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Deal Reference') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Agent') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Commission Amount') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Recorded Date') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider text-right">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-warm dark:divide-dark-border">
                        @forelse($commissions as $commission)
                            <tr class="hover:bg-surface dark:hover:bg-surface/30 transition-colors">
                                <td class="p-4 font-bold text-muted dark:text-white">
                                    <span>{{ $commission['property'] }}</span>
                                    <span class="block text-xs font-mono text-muted">{{ $commission['id'] }}</span>
                                </td>
                                <td class="p-4 text-muted dark:text-dark-subtle font-medium">{{ $commission['client'] }}</td>
                                <td class="p-4 font-black text-muted dark:text-white text-base">{{ $commission['amount'] }}</td>
                                <td class="p-4 text-muted text-xs">{{ $commission['date'] }}</td>
                                <td class="p-4">
                                    @php
                                        $badgeClass = match($commission['status']) {
                                            'paid' => 'badge-success',
                                            'approved' => 'badge-primary',
                                            default => 'badge-warning',
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }} uppercase text-[11px]">
                                        {{ __($commission['status']) }}
                                    </span>
                                </td>
                                <td class="p-4 text-right">
                                    @if($commission['status'] === 'pending')
                                        <form method="POST" action="{{ route('dashboard.crm.commissions.approve', $commission['raw_id'] ?? $commission['id']) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="btn btn-primary btn-sm text-xs py-1 px-3 shadow-sm flex items-center gap-1 justify-end ml-auto">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                <span>{{ __('Approve Payout') }}</span>
                                            </button>
                                        </form>
                                    @elseif($commission['status'] === 'approved')
                                        <span class="text-xs text-primary-600 dark:text-primary-400 font-semibold">{{ __('Ready for Finance Payout') }}</span>
                                    @else
                                        <span class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold flex items-center gap-1 justify-end">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            {{ __('Settled') }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-12 text-center text-muted">
                                    <div class="max-w-sm mx-auto space-y-2">
                                        <p class="font-medium text-slate-700 dark:text-slate-300">{{ __('No commission records found.') }}</p>
                                        <p class="text-xs text-muted">{{ __('Agent deal commissions will appear here upon closing sale and lease transactions.') }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(method_exists($commissions, 'links'))
                <div class="p-4 border-t border-warm dark:border-warm">
                    {{ $commissions->links() }}
                </div>
            @endif
        </div>

    </div>
</x-layouts.dashboard>
