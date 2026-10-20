<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('All Leads') }}</x-slot>

    <div class="max-w-7xl mx-auto page-enter space-y-6">
        <x-page-header
            title="Lead Pipeline Management"
            description="Capture, score, and nurture inbound prospective buyers, tenants, and property inquiries."
            icon="chart"
            badge="Leads">
            <x-slot:actions>
                <button class="btn btn-primary">{{ __('Add Lead') }}</button>
            </x-slot:actions>
        </x-page-header>

        @include('frontend.crm.partials.tabs')

        <div class="card">
            <div class="card-header flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
                <form method="GET" action="{{ route('dashboard.crm.leads') }}" class="flex flex-wrap items-center gap-3 flex-1">
                    <div class="relative flex-1 min-w-56">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </div>
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="{{ __('Search leads by name, property, or assignee...') }}"
                               class="form-input search-input pl-9 pr-8 w-full text-sm">
                        @if(request('search'))
                            <a href="{{ route('dashboard.crm.leads') }}" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </a>
                        @endif
                    </div>
                    <select name="status" onchange="this.form.submit()" class="form-input max-w-xs text-sm">
                        <option value="">{{ __('All Statuses') }}</option>
                        <option value="New Lead" {{ request('status') === 'New Lead' ? 'selected' : '' }}>{{ __('New Lead') }}</option>
                        <option value="Contacted" {{ request('status') === 'Contacted' ? 'selected' : '' }}>{{ __('Contacted') }}</option>
                        <option value="Proposal" {{ request('status') === 'Proposal' ? 'selected' : '' }}>{{ __('Proposal') }}</option>
                    </select>
                    @if(request('search') || request('status'))
                        <a href="{{ route('dashboard.crm.leads') }}" class="text-xs text-orange-600 hover:text-orange-700 dark:text-orange-400 font-medium px-2 py-1.5">{{ __('Reset') }}</a>
                    @endif
                </form>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#FFF5EB]/60 dark:bg-[#172033]/60 border-b border-[#EBDBC9] dark:border-[#273244]">
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Name') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Property/Interest') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Value') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Assigned To') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Last Contact') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EBDBC9]/60 dark:divide-[#273244]">
                        @foreach($leads as $lead)
                            <tr class="hover:bg-surface dark:hover:bg-surface/30 transition-colors">
                                <td class="p-4 font-bold text-muted dark:text-white">
                                    <a href="{{ route('dashboard.crm.leads.show', $lead['id']) }}" class="hover:text-primary-600">{{ $lead['name'] }}</a>
                                </td>
                                <td class="p-4 text-muted dark:text-dark-subtle">{{ $lead['property'] }}</td>
                                <td class="p-4 font-medium text-muted dark:text-white">{{ $lead['value'] }}</td>
                                <td class="p-4">
                                    <span class="badge badge-neutral uppercase">{{ __($lead['status']) }}</span>
                                </td>
                                <td class="p-4 text-muted dark:text-dark-subtle">{{ $lead['assigned_to'] }}</td>
                                <td class="p-4 text-muted dark:text-dark-subtle">{{ $lead['last_contact'] }}</td>
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
