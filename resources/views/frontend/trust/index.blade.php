<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Safety Center') }}</x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="mb-8 flex justify-between items-end">
            <div>
                <h1 class="text-3xl font-extrabold font-display text-muted dark:text-white tracking-tight">{{ __('Trust & Safety') }}</h1>
                <p class="text-muted dark:text-muted mt-2">{{ __('Manage fraud reports, moderation, and user disputes.') }}</p>
            </div>
            <div class="flex gap-2">
                <button class="btn btn-secondary text-error-600 border-error-200 hover:bg-error-50">{{ __('Create Case') }}</button>
            </div>
        </div>

        @include('frontend.trust.partials.tabs')

        <!-- Stats Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6 mb-8">
            <x-card-stat
                :label="__('Active Cases')"
                :value="$stats['active_cases']"
                :badge="__('Cases')"
                badgeVariant="brand"
                iconColor="orange">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg>
                </x-slot:icon>
            </x-card-stat>
            
            <x-card-stat
                :label="__('New Reports')"
                :value="$stats['new_reports']"
                :badge="__('Reports')"
                badgeVariant="error"
                iconColor="rose">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
                </x-slot:icon>
            </x-card-stat>
            
            <x-card-stat
                :label="__('Open Disputes')"
                :value="$stats['open_disputes']"
                :badge="__('Disputes')"
                badgeVariant="warning"
                iconColor="amber">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </x-slot:icon>
            </x-card-stat>
            
            <x-card-stat
                :label="__('Avg Resolution Time')"
                :value="$stats['avg_resolution']"
                :badge="__('Resolution')"
                badgeVariant="info"
                iconColor="blue">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" /></svg>
                </x-slot:icon>
            </x-card-stat>
        </div>

        <!-- Recent Activity -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">{{ __('Recent Activity') }}</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface dark:bg-surface/50 border-b border-warm dark:border-warm">
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Case ID') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Type') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Subject') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Date') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider text-right">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-warm dark:divide-dark-border">
                        @foreach($recentActivity as $activity)
                            <tr class="hover:bg-surface dark:hover:bg-surface/30 transition-colors">
                                <td class="p-4 font-bold text-muted dark:text-white">{{ $activity['id'] }}</td>
                                <td class="p-4 text-sm font-medium text-muted dark:text-dark-subtle">{{ $activity['type'] }}</td>
                                <td class="p-4 text-sm text-muted dark:text-white">{{ $activity['subject'] }}</td>
                                <td class="p-4">
                                    @php
                                        $sClass = 'badge-neutral';
                                        if ($activity['status'] === 'investigating' || $activity['status'] === 'escalated') $sClass = 'badge-warning';
                                        if ($activity['status'] === 'resolved' || $activity['status'] === 'action_taken') $sClass = 'badge-success';
                                    @endphp
                                    <span class="badge {{ $sClass }} uppercase text-xs">{{ str_replace('_', ' ', $activity['status']) }}</span>
                                </td>
                                <td class="p-4 text-sm text-muted dark:text-dark-subtle">{{ date('M d, Y', strtotime($activity['date'])) }}</td>
                                <td class="p-4 text-right">
                                    <a href="{{ route('dashboard.trust.cases.show', $activity['id']) }}" class="btn btn-sm btn-secondary">{{ __('Review') }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
