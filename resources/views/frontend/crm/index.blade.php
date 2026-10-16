<x-layouts.dashboard>
    <div class="max-w-7xl mx-auto space-y-6">
        @include('frontend.crm.partials.tabs')

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
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

        <div class="card">
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
