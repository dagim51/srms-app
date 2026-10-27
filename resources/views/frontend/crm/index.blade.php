<x-layouts.dashboard>
    <div class="max-w-7xl mx-auto space-y-6">
        <x-page-header
            title="Real Estate CRM & Pipeline"
            description="Capture prospective leads, guide client negotiations through pipeline stages, and track agent commissions."
            icon="chart"
            badge="CRM Pipeline">
            <x-slot:actions>
                <div class="flex items-center gap-2">
                    <a href="{{ route('dashboard.crm.pipeline') }}" class="btn btn-secondary text-xs flex items-center gap-1.5 shadow-sm">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        <span>{{ __('Pipeline Board') }}</span>
                    </a>
                    <a href="{{ route('dashboard.crm.leads') }}" class="btn btn-primary text-xs flex items-center gap-1.5 shadow-sm">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>{{ __('Manage Leads') }}</span>
                    </a>
                </div>
            </x-slot:actions>
        </x-page-header>

        @include('frontend.crm.partials.tabs')

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
            <div class="cinematic-fade-up cinematic-stagger-1">
                <x-card-stat
                    :label="__('Total Leads')"
                    :value="number_format($stats['total_leads'])"
                    iconColor="orange"
                    :badge="__('Total')"
                    badgeVariant="brand">
                    <x-slot:icon>
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    </x-slot:icon>
                </x-card-stat>
            </div>

            <div class="cinematic-fade-up cinematic-stagger-2">
                <x-card-stat
                    :label="__('Active Pipeline')"
                    :value="number_format($stats['active_pipeline'])"
                    iconColor="amber"
                    :badge="__('Active')"
                    badgeVariant="warning">
                    <x-slot:icon>
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                    </x-slot:icon>
                </x-card-stat>
            </div>

            <div class="cinematic-fade-up cinematic-stagger-3">
                <x-card-stat
                    :label="__('Closed (This Month)')"
                    :value="number_format($stats['closed_this_month'])"
                    iconColor="emerald"
                    :badge="__('Closed')"
                    badgeVariant="success">
                    <x-slot:icon>
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </x-slot:icon>
                </x-card-stat>
            </div>

            <div class="cinematic-fade-up cinematic-stagger-4">
                <x-card-stat
                    :label="__('Tasks Due')"
                    :value="number_format($stats['tasks_due'])"
                    iconColor="rose"
                    :badge="__('Due')"
                    badgeVariant="error">
                    <x-slot:icon>
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </x-slot:icon>
                </x-card-stat>
            </div>
        </div>

        <div class="cinematic-fade-up cinematic-stagger-2 card">
            <div class="card-header">
                <h2 class="card-title">{{ __('Recent Leads') }}</h2>
                <a href="{{ route('dashboard.crm.leads') }}" class="btn btn-ghost btn-sm text-orange-600 dark:text-orange-400 font-semibold">{{ __('View All') }}</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface dark:bg-surface/50 border-b border-warm dark:border-warm">
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Lead Name') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Property/Interest') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Value') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-warm dark:divide-dark-border">
                        @foreach($leads as $lead)
                            <tr class="hover:bg-surface dark:hover:bg-surface/30 transition-colors cursor-pointer" onclick="window.location='{{ route('dashboard.crm.leads.show', $lead['id']) }}'">
                                <td class="p-4 font-bold text-muted dark:text-white">{{ $lead['name'] }}</td>
                                <td class="p-4 text-muted dark:text-dark-subtle">{{ $lead['property'] }}</td>
                                <td class="p-4 text-muted dark:text-dark-subtle">{{ $lead['value'] }}</td>
                                <td class="p-4">
                                    <span class="badge badge-neutral uppercase">{{ __($lead['status']) }}</span>
                                </td>
                                <td class="p-4 text-right">
                                    <a href="{{ route('dashboard.crm.leads.show', $lead['id']) }}" class="text-primary-600 hover:text-primary-700 font-medium text-sm">{{ __('View') }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
