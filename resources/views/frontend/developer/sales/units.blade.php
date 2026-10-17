<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Unit Inventory') }}</x-slot>

    <div class="max-w-7xl mx-auto">
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
            <div class="p-6 border-b border-[#EBDBC9] dark:border-[#273244] flex gap-4 flex-wrap">
                <input type="text" placeholder="{{ __('Search units by number...') }}" class="input w-full max-w-xs">
                <select class="input max-w-xs">
                    <option>{{ __('All Projects') }}</option>
                    <option>{{ __('Skyline Towers') }}</option>
                </select>
                <select class="input max-w-xs">
                    <option>{{ __('All Statuses') }}</option>
                    <option>{{ __('Available') }}</option>
                    <option>{{ __('Reserved') }}</option>
                    <option>{{ __('Contracted') }}</option>
                    <option>{{ __('Sold') }}</option>
                    <option>{{ __('Blocked') }}</option>
                </select>
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
