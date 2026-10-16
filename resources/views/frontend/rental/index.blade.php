<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Rental Dashboard') }}</x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="mb-8 flex justify-between items-end">
            <div>
                <h1 class="text-3xl font-extrabold font-display text-muted dark:text-white tracking-tight">{{ __('Rental & Lease Management') }}</h1>
                <p class="text-muted dark:text-muted mt-2">{{ __('Manage tenants, leases, and applications.') }}</p>
            </div>
            <button class="btn btn-primary">{{ __('New Lease') }}</button>
        </div>

        @include('frontend.rental.partials.tabs')

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
            <x-card-stat
                :label="__('Total Tenants')"
                :value="$stats['total_tenants']"
                :badge="__('Tenants')"
                badgeVariant="info"
                iconColor="blue">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                </x-slot:icon>
            </x-card-stat>
            
            <x-card-stat
                :label="__('Active Leases')"
                :value="$stats['active_leases']"
                :badge="__('Leases')"
                badgeVariant="success"
                iconColor="emerald">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                </x-slot:icon>
            </x-card-stat>
            
            <x-card-stat
                :label="__('Pending Apps')"
                :value="$stats['pending_applications']"
                :badge="__('Pending')"
                badgeVariant="warning"
                iconColor="amber">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </x-slot:icon>
            </x-card-stat>
            
            <x-card-stat
                :label="__('Overdue Rent')"
                :value="$stats['overdue_rent']"
                :badge="__('Alert')"
                badgeVariant="error"
                iconColor="rose">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </x-slot:icon>
            </x-card-stat>
        </div>

        <!-- Recent Applications -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">{{ __('Recent Applications') }}</h2>
                <a href="{{ route('dashboard.rental.applications') }}" class="btn btn-ghost btn-sm text-orange-600 dark:text-orange-400 font-semibold">{{ __('View All') }}</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface dark:bg-surface/50 border-b border-warm dark:border-warm">
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Applicant') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Unit') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Date') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider text-right">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-warm dark:divide-dark-border">
                        @foreach($recentApplications as $app)
                            <tr class="hover:bg-surface dark:hover:bg-surface/30 transition-colors">
                                <td class="p-4 font-bold text-muted dark:text-white">{{ $app['applicant'] }}</td>
                                <td class="p-4 text-muted dark:text-dark-subtle">{{ $app['unit'] }}</td>
                                <td class="p-4 text-muted dark:text-dark-subtle">{{ date('M d, Y', strtotime($app['date'])) }}</td>
                                <td class="p-4">
                                    <span class="badge {{ $app['status'] === 'approved' ? 'badge-success' : ($app['status'] === 'rejected' ? 'badge-error' : 'badge-warning') }} uppercase text-xs">{{ __($app['status']) }}</span>
                                </td>
                                <td class="p-4 text-right">
                                    <button class="btn btn-sm btn-secondary">{{ __('Review') }}</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
