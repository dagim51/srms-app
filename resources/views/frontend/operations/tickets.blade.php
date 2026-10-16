<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Maintenance Tickets') }}</x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="mb-8 flex justify-between items-end">
            <div>
                <h1 class="text-3xl font-extrabold font-display text-muted dark:text-white tracking-tight">{{ __('Maintenance Tickets') }}</h1>
                <p class="text-muted dark:text-muted mt-2">{{ __('Manage property maintenance requests.') }}</p>
            </div>
            <button class="btn btn-primary">{{ __('New Ticket') }}</button>
        </div>

        @include('frontend.operations.partials.tabs')

        <div class="card overflow-hidden">
            <div class="p-6 border-b border-[#EBDBC9] dark:border-[#273244] flex gap-4 flex-wrap">
                <input type="text" placeholder="{{ __('Search tickets...') }}" class="input w-full max-w-xs">
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
