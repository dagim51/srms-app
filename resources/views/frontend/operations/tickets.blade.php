<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Maintenance Tickets') }}</x-slot>

    <div class="max-w-7xl mx-auto page-enter space-y-6">
        <x-page-header
            title="Maintenance Tickets"
            description="Log, assign, and track property repairs, plumbing, electrical, and facility service work orders."
            icon="ticket"
            badge="Maintenance">
            <x-slot:actions>
                <button class="btn btn-primary">{{ __('New Ticket') }}</button>
            </x-slot:actions>
        </x-page-header>

        @include('frontend.operations.partials.tabs')

        <div class="card overflow-hidden">
            <div class="p-6 border-b border-[#EBDBC9] dark:border-[#273244] flex gap-4 flex-wrap items-center">
                <form method="GET" action="{{ route('dashboard.operations.tickets') }}" class="flex items-center gap-2 flex-1 max-w-sm">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </div>
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="{{ __('Search tickets by ID, issue, or assignee...') }}"
                               class="form-input search-input pl-9 pr-8 w-full text-sm">
                        @if(request('search'))
                            <a href="{{ route('dashboard.operations.tickets') }}" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </a>
                        @endif
                    </div>
                    @if(request('search'))
                        <a href="{{ route('dashboard.operations.tickets') }}" class="text-xs text-orange-600 hover:text-orange-700 dark:text-orange-400 font-medium whitespace-nowrap">{{ __('Reset') }}</a>
                    @endif
                </form>
                <select class="input max-w-xs">
                    <option>{{ __('All Statuses') }}</option>
                    <option>{{ __('Open') }}</option>
                    <option>{{ __('Assigned') }}</option>
                    <option>{{ __('In Progress') }}</option>
                    <option>{{ __('Waiting') }}</option>
                    <option>{{ __('Completed') }}</option>
                </select>
                <select class="input max-w-xs">
                    <option>{{ __('All Priorities') }}</option>
                    <option>{{ __('Critical') }}</option>
                    <option>{{ __('High') }}</option>
                    <option>{{ __('Medium') }}</option>
                    <option>{{ __('Low') }}</option>
                </select>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-warm-50/50 dark:bg-[#1E293B]/50 border-b border-[#EBDBC9] dark:border-[#273244]">
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Ticket ID & Date') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Unit') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Issue') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Priority') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Assigned To') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EBDBC9]/50 dark:divide-[#273244]">
                        @foreach($tickets as $ticket)
                            <tr class="hover:bg-surface dark:hover:bg-surface/30 transition-colors">
                                <td class="p-4">
                                    <p class="font-bold text-muted dark:text-white">
                                        <a href="{{ route('dashboard.operations.tickets.show', $ticket['id']) }}" class="hover:text-primary-600">{{ $ticket['id'] }}</a>
                                    </p>
                                    <p class="text-xs text-muted">{{ date('M d, Y', strtotime($ticket['date'])) }}</p>
                                </td>
                                <td class="p-4 text-muted dark:text-dark-subtle">{{ $ticket['unit'] }}</td>
                                <td class="p-4 font-medium text-muted dark:text-white">{{ $ticket['issue'] }}</td>
                                <td class="p-4">
                                    @php
                                        $pClass = 'badge-neutral';
                                        if ($ticket['priority'] === 'critical') $pClass = 'badge-error';
                                        if ($ticket['priority'] === 'high') $pClass = 'badge-error opacity-80';
                                        if ($ticket['priority'] === 'medium') $pClass = 'badge-warning';
                                    @endphp
                                    <span class="badge {{ $pClass }} uppercase text-xs">{{ __($ticket['priority']) }}</span>
                                </td>
                                <td class="p-4">
                                    @php
                                        $sClass = 'badge-neutral';
                                        if ($ticket['status'] === 'completed') $sClass = 'badge-success';
                                        if ($ticket['status'] === 'in_progress') $sClass = 'badge-primary';
                                        if ($ticket['status'] === 'assigned') $sClass = 'badge-warning';
                                    @endphp
                                    <span class="badge {{ $sClass }} uppercase text-xs">{{ __(str_replace('_', ' ', $ticket['status'])) }}</span>
                                </td>
                                <td class="p-4 text-muted dark:text-dark-subtle">
                                    @if($ticket['assigned'])
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-full bg-surface-subtle dark:bg-surface flex items-center justify-center text-xs font-bold">{{ substr($ticket['assigned'], 0, 1) }}</div>
                                            <span class="text-sm">{{ $ticket['assigned'] }}</span>
                                        </div>
                                    @else
                                        <span class="text-muted italic">{{ __('Unassigned') }}</span>
                                    @endif
                                </td>
                                <td class="p-4 text-right">
                                    <a href="{{ route('dashboard.operations.tickets.show', $ticket['id']) }}" class="text-primary-600 hover:text-primary-700 font-medium text-sm">{{ __('Update') }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
