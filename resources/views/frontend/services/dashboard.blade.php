<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Provider Dashboard') }}</x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
            <div>
                <h1 class="text-3xl font-extrabold font-display text-muted dark:text-white tracking-tight">{{ __('Provider Dashboard') }}</h1>
                <p class="text-muted dark:text-muted mt-2">{{ __('Manage your service requests and monitor your performance.') }}</p>
            </div>
            <a href="{{ route('dashboard.services.index') }}" class="btn btn-secondary border-primary-200 text-primary-700 hover:bg-primary-50">{{ __('Back to Marketplace') }}</a>
        </div>

        <!-- Performance Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6 mb-8">
            <x-card-stat
                :label="__('Active Requests')"
                :value="$stats['active_requests']"
                :badge="__('Active')"
                badgeVariant="brand"
                iconColor="orange">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </x-slot:icon>
            </x-card-stat>
            <x-card-stat
                :label="__('Completed Jobs')"
                :value="$stats['completed_jobs']"
                :badge="__('Completed')"
                badgeVariant="success"
                iconColor="emerald">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </x-slot:icon>
            </x-card-stat>
            <x-card-stat
                :label="__('Average Rating')"
                :value="$stats['rating']"
                :badge="__('Rating')"
                badgeVariant="warning"
                iconColor="amber">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" /></svg>
                </x-slot:icon>
            </x-card-stat>
            <x-card-stat
                :label="__('Earnings (30 Days)')"
                :value="$stats['monthly_earnings']"
                :badge="__('Monthly')"
                badgeVariant="brand"
                iconColor="orange">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6H2.25m0 0H3m0 0h18m0 0h.75A.75.75 0 0122 6.75v.75m0 0h-.75m0 0a60.07 60.07 0 00-15.797-2.101c-.727-.198-1.453.342-1.453 1.096V4.5" /></svg>
                </x-slot:icon>
            </x-card-stat>
        </div>

        <!-- Service Requests -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">{{ __('Recent Service Requests') }}</h2>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-warm dark:border-warm text-sm font-bold text-muted uppercase tracking-wider">
                            <th class="py-4 font-bold">{{ __('Request ID') }}</th>
                            <th class="py-4 font-bold">{{ __('Client') }}</th>
                            <th class="py-4 font-bold">{{ __('Service') }}</th>
                            <th class="py-4 font-bold">{{ __('Date') }}</th>
                            <th class="py-4 font-bold">{{ __('Status') }}</th>
                            <th class="py-4 font-bold text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-warm dark:divide-dark-border">
                        @foreach($requests as $req)
                            <tr class="hover:bg-surface dark:hover:bg-surface/50 transition-colors">
                                <td class="py-4 font-medium text-muted">{{ $req['id'] }}</td>
                                <td class="py-4 font-bold text-muted dark:text-white">{{ $req['client'] }}</td>
                                <td class="py-4 text-muted dark:text-white">{{ $req['service'] }}</td>
                                <td class="py-4 text-muted">{{ $req['date'] }}</td>
                                <td class="py-4">
                                    @if($req['status'] === 'pending')
                                        <span class="badge badge-warning uppercase text-xs">{{ __('Pending') }}</span>
                                    @elseif($req['status'] === 'in-progress')
                                        <span class="badge badge-primary uppercase text-xs">{{ __('In Progress') }}</span>
                                    @else
                                        <span class="badge badge-success uppercase text-xs">{{ __('Completed') }}</span>
                                    @endif
                                </td>
                                <td class="py-4 text-right">
                                    @if($req['status'] === 'pending')
                                        <button class="text-primary-600 hover:text-primary-800 font-bold text-xs mr-3">{{ __('Accept') }}</button>
                                        <button class="text-error-600 hover:text-error-800 font-bold text-xs">{{ __('Decline') }}</button>
                                    @else
                                        <button class="text-muted hover:text-muted dark:hover:text-white font-bold text-xs">{{ __('View Details') }}</button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
