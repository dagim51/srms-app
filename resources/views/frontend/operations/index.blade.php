<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Operations Dashboard') }}</x-slot>

    <div class="max-w-7xl mx-auto" x-data="{ showCreateTicket: false }">
        <x-page-header
            title="Operations & Facilities"
            description="Coordinate property maintenance requests, cleaning schedules, service staff assignments, and work orders."
            icon="ticket"
            badge="Operations">
            <x-slot:actions>
                <button @click="showCreateTicket = true" class="btn btn-primary text-xs shadow-sm shadow-orange-500/20">{{ __('Create Work Order') }}</button>
            </x-slot:actions>
        </x-page-header>

        @if(session('success'))
            <div class="mb-4 px-4 py-3 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 text-sm border border-emerald-200 dark:border-emerald-800 flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ session('success') }}
            </div>
        @endif

        @include('frontend.operations.partials.tabs')

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
            <div class="cinematic-fade-up cinematic-stagger-1">
                <x-card-stat :label="__('Open Tickets')" :value="$stats['open_tickets']" :badge="__('Urgent')" badgeVariant="error" iconColor="rose">
                    <x-slot:icon><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg></x-slot:icon>
                </x-card-stat>
            </div>
            <div class="cinematic-fade-up cinematic-stagger-2">
                <x-card-stat :label="__('Overdue Tasks')" :value="$stats['overdue_tasks']" :badge="__('Overdue')" badgeVariant="warning" iconColor="amber">
                    <x-slot:icon><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></x-slot:icon>
                </x-card-stat>
            </div>
            <div class="cinematic-fade-up cinematic-stagger-3">
                <x-card-stat :label="__('Cleaning Pending')" :value="$stats['cleaning_pending']" :badge="__('Pending')" badgeVariant="info" iconColor="blue">
                    <x-slot:icon><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" /></svg></x-slot:icon>
                </x-card-stat>
            </div>
            <div class="cinematic-fade-up cinematic-stagger-4">
                <x-card-stat :label="__('Staff Active')" :value="$stats['staff_active']" :badge="__('Active')" badgeVariant="success" iconColor="emerald">
                    <x-slot:icon><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg></x-slot:icon>
                </x-card-stat>
            </div>
        </div>

        <div class="cinematic-fade-up cinematic-stagger-2 card">
            <div class="card-header">
                <h2 class="card-title">{{ __('Recent Active Tickets') }}</h2>
                <a href="{{ route('dashboard.operations.tickets') }}" class="btn btn-ghost btn-sm text-orange-600 dark:text-orange-400 font-semibold">{{ __('View All') }}</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface dark:bg-surface/50 border-b border-warm dark:border-warm">
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Ticket ID') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Unit') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Issue') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Priority') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Assigned') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider text-right">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-warm dark:divide-dark-border">
                        @forelse($recentTickets as $ticket)
                            <tr class="hover:bg-surface dark:hover:bg-surface/30 transition-colors">
                                <td class="p-4 font-bold text-muted dark:text-white">{{ $ticket['label'] }}</td>
                                <td class="p-4 text-muted dark:text-dark-subtle">{{ $ticket['unit'] }}</td>
                                <td class="p-4 font-medium text-muted dark:text-white">{{ $ticket['issue'] }}</td>
                                <td class="p-4">
                                    @php
                                        $pClass = match($ticket['priority']) {
                                            'critical' => 'badge-error',
                                            'high' => 'badge-error opacity-80',
                                            'medium' => 'badge-warning',
                                            default => 'badge-neutral',
                                        };
                                    @endphp
                                    <span class="badge {{ $pClass }} uppercase text-xs">{{ __($ticket['priority']) }}</span>
                                </td>
                                <td class="p-4">
                                    <span class="badge badge-neutral uppercase text-xs">{{ __(str_replace('_', ' ', $ticket['status'])) }}</span>
                                </td>
                                <td class="p-4 text-muted dark:text-dark-subtle">{{ $ticket['assigned'] ?: __('Unassigned') }}</td>
                                <td class="p-4 text-right">
                                    <a href="{{ route('dashboard.operations.tickets.show', $ticket['id']) }}" class="btn btn-sm btn-secondary">{{ __('Review') }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-8 text-center text-muted">{{ __('No active tickets.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Create Work Order Modal --}}
        <div x-show="showCreateTicket" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="absolute inset-0 bg-black/50" @click="showCreateTicket = false"></div>
            <div class="relative bg-white dark:bg-dark-card rounded-2xl shadow-2xl w-full max-w-lg p-6 z-10">
                <div class="flex justify-between items-center mb-5">
                    <h3 class="text-lg font-bold text-muted dark:text-white">{{ __('Create Work Order / Ticket') }}</h3>
                    <button @click="showCreateTicket = false" class="text-muted hover:text-muted/70">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form method="POST" action="{{ route('dashboard.operations.tickets.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-muted dark:text-dark-subtle mb-1">{{ __('Issue Title') }} <span class="text-red-500">*</span></label>
                        <input type="text" name="title" class="input w-full" placeholder="{{ __('e.g. Leaking pipe in bathroom') }}" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-muted dark:text-dark-subtle mb-1">{{ __('Description') }}</label>
                        <textarea name="description" rows="3" class="input w-full" placeholder="{{ __('Describe the issue in detail...') }}"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-muted dark:text-dark-subtle mb-1">{{ __('Priority') }} <span class="text-red-500">*</span></label>
                            <select name="priority" class="input w-full" required>
                                <option value="low">{{ __('Low') }}</option>
                                <option value="medium" selected>{{ __('Medium') }}</option>
                                <option value="high">{{ __('High') }}</option>
                                <option value="critical">{{ __('Critical') }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-muted dark:text-dark-subtle mb-1">{{ __('Property') }}</label>
                            <select name="property_id" class="input w-full">
                                <option value="">{{ __('— Select Property —') }}</option>
                                @foreach($properties as $prop)
                                    <option value="{{ $prop->id }}">{{ $prop->title }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="showCreateTicket = false" class="btn btn-secondary">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Create Ticket') }}</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
