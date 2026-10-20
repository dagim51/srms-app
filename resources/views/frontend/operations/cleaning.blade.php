<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Cleaning & Readiness') }}</x-slot>

    <div class="max-w-7xl mx-auto page-enter space-y-6">
        <x-page-header
            title="Housekeeping & Turnover Readiness"
            description="Manage unit turnover inspections, cleaning rosters, ready-for-occupancy verification, and sanitization logs."
            icon="ticket"
            badge="Housekeeping">
            <x-slot:actions>
                <button class="btn btn-primary">{{ __('Schedule Cleaning') }}</button>
            </x-slot:actions>
        </x-page-header>

        @include('frontend.operations.partials.tabs')

        <!-- Workflow Status Overview -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6 mb-8">
            <x-card-stat
                :label="__('Pending Checkouts')"
                value="5"
                iconColor="amber"
                :badge="__('Pending')"
                badgeVariant="warning"
                icon="clock"
            />
            <x-card-stat
                :label="__('Active Cleaning')"
                value="8"
                iconColor="blue"
                :badge="__('Active')"
                badgeVariant="info"
                icon="sparkles"
            />
            <x-card-stat
                :label="__('Awaiting Inspection')"
                value="3"
                iconColor="orange"
                :badge="__('Inspection')"
                badgeVariant="brand"
                icon="wrench"
            />
            <x-card-stat
                :label="__('Units Ready')"
                value="12"
                iconColor="emerald"
                :badge="__('Ready')"
                badgeVariant="success"
                icon="check"
            />
        </div>

        <div class="card overflow-hidden">
            <div class="p-6 border-b border-slate-200/80 dark:border-slate-800/80 flex justify-between items-center">
                <h2 class="card-title text-xl">{{ __('Active Tasks') }}</h2>
                <div class="flex gap-2">
                    <select class="input max-w-xs py-1.5 text-sm">
                        <option>{{ __('All Statuses') }}</option>
                        <option>{{ __('Pending') }}</option>
                        <option>{{ __('In Progress') }}</option>
                        <option>{{ __('Inspection') }}</option>
                    </select>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-warm-50/50 dark:bg-[#1E293B]/50 border-b border-[#EBDBC9] dark:border-[#273244]">
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Task ID') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Unit') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Type') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Scheduled Date') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Assigned') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EBDBC9]/50 dark:divide-[#273244]">
                        @foreach($tasks as $task)
                            <tr class="hover:bg-surface dark:hover:bg-surface/30 transition-colors">
                                <td class="p-4 font-bold text-muted dark:text-white">{{ $task['id'] }}</td>
                                <td class="p-4 font-medium text-muted dark:text-white">{{ $task['unit'] }}</td>
                                <td class="p-4 text-muted dark:text-dark-subtle">{{ $task['type'] }}</td>
                                <td class="p-4 text-muted dark:text-dark-subtle">{{ date('M d, Y', strtotime($task['date'])) }}</td>
                                <td class="p-4 text-muted dark:text-dark-subtle">{{ $task['assigned'] }}</td>
                                <td class="p-4">
                                    @php
                                        $sClass = 'badge-neutral';
                                        if ($task['status'] === 'in_progress') $sClass = 'badge-primary';
                                        if ($task['status'] === 'inspection') $sClass = 'badge-warning';
                                        if ($task['status'] === 'ready') $sClass = 'badge-success';
                                    @endphp
                                    <span class="badge {{ $sClass }} uppercase text-xs">{{ __(str_replace('_', ' ', $task['status'])) }}</span>
                                </td>
                                <td class="p-4 text-right">
                                    @if($task['status'] === 'inspection')
                                        <button class="text-warning-600 hover:text-warning-700 font-bold text-sm">{{ __('Perform Inspection') }}</button>
                                    @elseif($task['status'] === 'ready')
                                        <span class="text-success-600 font-medium text-sm">{{ __('Verified') }}</span>
                                    @else
                                        <button class="text-primary-600 hover:text-primary-700 font-medium text-sm">{{ __('Update') }}</button>
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
