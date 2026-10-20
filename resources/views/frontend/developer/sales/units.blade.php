<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Unit Inventory') }}</x-slot>

    <div class="max-w-7xl mx-auto page-enter">
        <x-page-header
            title="Sales & Inventory"
            description="Manage unit statuses, reservations, and sales."
            icon="building"
            badge="Inventory">
            <x-slot:actions>
                <button class="btn btn-primary">{{ __('Export Report') }}</button>
            </x-slot:actions>
        </x-page-header>

        @include('frontend.developer.sales.partials.tabs')

        <div class="card overflow-hidden">
            <div class="p-6 border-b border-[#EBDBC9] dark:border-[#273244]">
                <form method="GET" action="{{ route('dashboard.sales.units') }}" class="flex flex-wrap items-center gap-3">
                    <div class="relative flex-1 min-w-56">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </div>
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="{{ __('Search units by number, type, or buyer...') }}"
                               class="form-input search-input pl-9 pr-8 w-full text-sm">
                        @if(request('search'))
                            <a href="{{ route('dashboard.sales.units') }}" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </a>
                        @endif
                    </div>
                    <select name="status" onchange="this.form.submit()" class="form-select max-w-xs text-sm">
                        <option value="">{{ __('All Statuses') }}</option>
                        <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>{{ __('Available') }}</option>
                        <option value="reserved" {{ request('status') === 'reserved' ? 'selected' : '' }}>{{ __('Reserved') }}</option>
                        <option value="contracted" {{ request('status') === 'contracted' ? 'selected' : '' }}>{{ __('Contracted') }}</option>
                        <option value="sold" {{ request('status') === 'sold' ? 'selected' : '' }}>{{ __('Sold') }}</option>
                        <option value="blocked" {{ request('status') === 'blocked' ? 'selected' : '' }}>{{ __('Blocked') }}</option>
                    </select>
                    @if(request('search') || request('status'))
                        <a href="{{ route('dashboard.sales.units') }}" class="text-xs text-orange-600 hover:text-orange-700 dark:text-orange-400 font-medium px-2 py-1.5">{{ __('Reset') }}</a>
                    @endif
                </form>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-warm-50/50 dark:bg-[#1E293B]/50 border-b border-[#EBDBC9] dark:border-[#273244]">
                            <th class="p-4 text-xs font-semibold text-muted uppercase tracking-wider">{{ __('Unit Number') }}</th>
                            <th class="p-4 text-xs font-semibold text-muted uppercase tracking-wider">{{ __('Project & Building') }}</th>
                            <th class="p-4 text-xs font-semibold text-muted uppercase tracking-wider">{{ __('Type') }}</th>
                            <th class="p-4 text-xs font-semibold text-muted uppercase tracking-wider">{{ __('Price') }}</th>
                            <th class="p-4 text-xs font-semibold text-muted uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="p-4 text-xs font-semibold text-muted uppercase tracking-wider">{{ __('Buyer') }}</th>
                            <th class="p-4 text-xs font-semibold text-muted uppercase tracking-wider text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EBDBC9]/50 dark:divide-[#273244]/60">
                        @foreach($units as $unit)
                            <tr class="hover:bg-surface dark:hover:bg-surface/30 transition-colors">
                                <td class="p-4 font-bold text-muted dark:text-white">
                                    <a href="{{ route('dashboard.sales.units.show', $unit['id']) }}" class="hover:text-primary-600 dark:hover:text-primary-400">{{ $unit['number'] }}</a>
                                </td>
                                <td class="p-4">
                                    <p class="text-sm font-medium text-muted dark:text-white">{{ $unit['project'] }}</p>
                                    <p class="text-xs text-muted dark:text-muted">{{ $unit['building'] }}</p>
                                </td>
                                <td class="p-4 text-muted dark:text-dark-subtle">{{ $unit['type'] }}</td>
                                <td class="p-4 font-bold text-muted dark:text-white">{{ $unit['price'] }}</td>
                                <td class="p-4">
                                    @php
                                        $badgeClass = 'badge-neutral';
                                        if ($unit['status'] === 'available') $badgeClass = 'badge-success';
                                        if ($unit['status'] === 'reserved') $badgeClass = 'badge-warning';
                                        if ($unit['status'] === 'contracted') $badgeClass = 'badge-primary';
                                        if ($unit['status'] === 'sold') $badgeClass = 'badge-primary';
                                        if ($unit['status'] === 'blocked') $badgeClass = 'badge-error';
                                    @endphp
                                    <span class="badge {{ $badgeClass }} uppercase text-xs">{{ $unit['status'] }}</span>
                                </td>
                                <td class="p-4 text-muted dark:text-dark-subtle">{{ $unit['buyer'] ?? '-' }}</td>
                                <td class="p-4 text-right">
                                    <a href="{{ route('dashboard.sales.units.show', $unit['id']) }}" class="text-primary-600 dark:text-primary-400 hover:text-primary-700 font-medium text-sm">{{ __('View') }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
