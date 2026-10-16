<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Leases') }}</x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="mb-8 flex justify-between items-end">
            <div>
                <h1 class="text-3xl font-extrabold font-display text-muted dark:text-white tracking-tight">{{ __('Leases') }}</h1>
                <p class="text-muted dark:text-muted mt-2">{{ __('Manage rental agreements and terms.') }}</p>
            </div>
            <button class="btn btn-primary">{{ __('Create Lease') }}</button>
        </div>

        @include('frontend.rental.partials.tabs')

        <div class="card overflow-hidden">
            <div class="p-6 border-b border-[#EBDBC9] dark:border-[#273244] flex gap-4">
                <input type="text" placeholder="{{ __('Search leases...') }}" class="input w-full max-w-md">
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
                        @foreach($leases as $lease)
                            <tr class="hover:bg-surface dark:hover:bg-surface/30 transition-colors">
                                <td class="p-4 font-bold text-muted dark:text-white">
                                    <a href="{{ route('dashboard.rental.leases.show', $lease['id']) }}" class="hover:text-primary-600">{{ $lease['id'] }}</a>
                                </td>
                                <td class="p-4 text-muted dark:text-dark-subtle">{{ $lease['tenant'] }}</td>
                                <td class="p-4 text-muted dark:text-dark-subtle">{{ $lease['unit'] }}</td>
                                <td class="p-4 text-sm">
                                    <p class="font-medium text-gray-900 dark:text-white"><x-ethiopian-date :date="$lease['start_date']" /> &ndash; <x-ethiopian-date :date="$lease['end_date']" /></p>
                                </td>
                                <td class="p-4 font-bold text-muted dark:text-white">{{ $lease['rent'] }}</td>
                                <td class="p-4">
                                    <span class="badge {{ $lease['status'] === 'active' ? 'badge-success' : 'badge-warning' }} uppercase text-xs">{{ __(str_replace('_', ' ', $lease['status'])) }}</span>
                                </td>
                                <td class="p-4 text-right">
                                    <a href="{{ route('dashboard.rental.leases.show', $lease['id']) }}" class="text-primary-600 hover:text-primary-700 font-medium text-sm">{{ __('View Details') }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
