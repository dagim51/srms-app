<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Leases') }}</x-slot>

    <div class="max-w-7xl mx-auto page-enter space-y-6" x-data="{ showCreateLease: false }">
        <x-page-header
            title="Lease Agreements"
            description="Draft, execute, and monitor long-term tenancy contracts, security deposits, and renewal terms."
            icon="document"
            badge="Leases">
            <x-slot:actions>
                <button @click="showCreateLease = true" class="btn btn-primary text-xs shadow-sm shadow-orange-500/20">
                    <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    {{ __('Create Lease') }}
                </button>
            </x-slot:actions>
        </x-page-header>

        @if(session('success'))
            <div class="px-4 py-3 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 text-sm border border-emerald-200 dark:border-emerald-800 flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ session('success') }}
            </div>
        @endif

        @include('frontend.rental.partials.tabs')

        <div class="card overflow-hidden">
            <div class="p-6 border-b border-[#EBDBC9] dark:border-[#273244] flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <form method="GET" action="{{ route('dashboard.rental.leases') }}" class="flex items-center gap-3 max-w-md w-full">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </div>
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="{{ __('Search leases by ID, tenant, or unit...') }}"
                               class="form-input search-input pl-9 pr-8 w-full text-sm">
                        @if(request('search'))
                            <a href="{{ route('dashboard.rental.leases') }}" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </a>
                        @endif
                    </div>
                    @if(request('search'))
                        <a href="{{ route('dashboard.rental.leases') }}" class="text-xs text-orange-600 hover:text-orange-700 dark:text-orange-400 font-medium whitespace-nowrap">{{ __('Reset') }}</a>
                    @endif
                </form>

                <button @click="showCreateLease = true" class="btn btn-primary btn-sm text-xs self-start sm:self-auto">
                    <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    {{ __('New Agreement') }}
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-warm-50/50 dark:bg-[#1E293B]/50 border-b border-[#EBDBC9] dark:border-[#273244]">
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Lease ID') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Tenant') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Unit') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Term') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Rent') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EBDBC9]/50 dark:divide-[#273244]">
                        @forelse($leases as $lease)
                            <tr class="hover:bg-surface dark:hover:bg-surface/30 transition-colors">
                                <td class="p-4 font-bold text-muted dark:text-white">
                                    <a href="{{ route('dashboard.rental.leases.show', $lease['id']) }}" class="hover:text-primary-600 text-xs font-mono font-bold">{{ $lease['id'] }}</a>
                                </td>
                                <td class="p-4 text-muted dark:text-dark-subtle">{{ $lease['tenant'] }}</td>
                                <td class="p-4 text-muted dark:text-dark-subtle">{{ $lease['unit'] }}</td>
                                <td class="p-4 text-sm">
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $lease['start_date'] }} &ndash; {{ $lease['end_date'] }}</p>
                                </td>
                                <td class="p-4 font-bold text-muted dark:text-white">{{ $lease['rent'] }}</td>
                                <td class="p-4">
                                    <span class="badge {{ $lease['status'] === 'active' ? 'badge-success' : ($lease['status'] === 'terminated' ? 'badge-error' : 'badge-warning') }} uppercase text-xs">{{ __(str_replace('_', ' ', $lease['status'])) }}</span>
                                </td>
                                <td class="p-4 text-right">
                                    <a href="{{ route('dashboard.rental.leases.show', $lease['id']) }}" class="btn btn-secondary btn-sm text-xs">{{ __('View Agreement') }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-8 text-center text-muted">{{ __('No lease agreements found.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($leases->hasPages())
                <div class="p-4 border-t border-warm dark:border-warm">
                    {{ $leases->links() }}
                </div>
            @endif
        </div>

        {{-- Create Lease Modal --}}
        <div x-show="showCreateLease" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="absolute inset-0 bg-black/50" @click="showCreateLease = false"></div>
            <div class="relative bg-white dark:bg-dark-card rounded-2xl shadow-2xl w-full max-w-lg p-6 z-10">
                <div class="flex justify-between items-center mb-5">
                    <h3 class="text-lg font-bold text-muted dark:text-white">{{ __('Create Formal Lease Agreement') }}</h3>
                    <button @click="showCreateLease = false" class="text-muted hover:text-muted/70">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form method="POST" action="{{ route('dashboard.rental.leases.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-muted dark:text-dark-subtle mb-1">{{ __('Property Unit') }} <span class="text-red-500">*</span></label>
                        <select name="property_id" class="input w-full" required>
                            <option value="">{{ __('— Select Rental Property —') }}</option>
                            @foreach($properties as $prop)
                                <option value="{{ $prop->id }}">{{ $prop->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-muted dark:text-dark-subtle mb-1">{{ __('Tenant User') }} <span class="text-red-500">*</span></label>
                        <select name="user_id" class="input w-full" required>
                            <option value="">{{ __('— Select Tenant —') }}</option>
                            @foreach($tenants as $t)
                                <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->phone ?? 'No Phone' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-muted dark:text-dark-subtle mb-1">{{ __('Start Date') }} <span class="text-red-500">*</span></label>
                            <input type="date" name="start_date" value="{{ date('Y-m-d') }}" class="input w-full" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-muted dark:text-dark-subtle mb-1">{{ __('End Date') }} <span class="text-red-500">*</span></label>
                            <input type="date" name="end_date" value="{{ date('Y-m-d', strtotime('+1 year')) }}" class="input w-full" required>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-muted dark:text-dark-subtle mb-1">{{ __('Monthly Rent (ETB)') }} <span class="text-red-500">*</span></label>
                            <input type="number" name="rent_amount" step="0.01" class="input w-full" placeholder="e.g. 25000" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-muted dark:text-dark-subtle mb-1">{{ __('Security Deposit (ETB)') }}</label>
                            <input type="number" name="deposit_amount" step="0.01" class="input w-full" placeholder="e.g. 50000">
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-3">
                        <button type="button" @click="showCreateLease = false" class="btn btn-secondary">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Create & Generate Schedules') }}</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
