<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Maintenance Tickets') }}</x-slot>

    <div class="max-w-7xl mx-auto page-enter space-y-6" x-data="{ showCreateTicket: false }">
        <x-page-header
            title="Maintenance Tickets"
            description="Log, assign, and track property repairs, plumbing, electrical, and facility service work orders."
            icon="ticket"
            badge="Maintenance">
            <x-slot:actions>
                <button @click="showCreateTicket = true" class="btn btn-primary">{{ __('New Ticket') }}</button>
            </x-slot:actions>
        </x-page-header>

        @if(session('success'))
            <div class="px-4 py-3 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 text-sm border border-emerald-200 dark:border-emerald-800 flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ session('success') }}
            </div>
        @endif

        @include('frontend.operations.partials.tabs')

        <div class="card overflow-hidden">
            <div class="p-6 border-b border-[#EBDBC9] dark:border-[#273244]">
                <form method="GET" action="{{ route('dashboard.operations.tickets') }}" class="flex gap-4 flex-wrap items-end">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-xs font-medium text-muted dark:text-muted mb-1">{{ __('Search') }}</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                            </div>
                            <input type="text" name="search" value="{{ request('search') }}"
                                   placeholder="{{ __('Search by ID, issue, unit...') }}"
                                   class="form-input pl-9 w-full text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-muted dark:text-muted mb-1">{{ __('Status') }}</label>
                        <select name="status" class="input">
                            <option value="">{{ __('All Statuses') }}</option>
                            <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>{{ __('Open') }}</option>
                            <option value="assigned" {{ request('status') === 'assigned' ? 'selected' : '' }}>{{ __('Assigned') }}</option>
                            <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>{{ __('In Progress') }}</option>
                            <option value="waiting" {{ request('status') === 'waiting' ? 'selected' : '' }}>{{ __('Waiting') }}</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>{{ __('Completed') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-muted dark:text-muted mb-1">{{ __('Priority') }}</label>
                        <select name="priority" class="input">
                            <option value="">{{ __('All Priorities') }}</option>
                            <option value="critical" {{ request('priority') === 'critical' ? 'selected' : '' }}>{{ __('Critical') }}</option>
                            <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>{{ __('High') }}</option>
                            <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>{{ __('Medium') }}</option>
                            <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>{{ __('Low') }}</option>
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="btn btn-primary text-sm">{{ __('Filter') }}</button>
                        @if(request()->hasAny(['search', 'status', 'priority']))
                            <a href="{{ route('dashboard.operations.tickets') }}" class="btn btn-secondary text-sm">{{ __('Reset') }}</a>
                        @endif
                    </div>
                </form>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-warm-50/50 dark:bg-[#1E293B]/50 border-b border-[#EBDBC9] dark:border-[#273244]">
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Ticket ID &amp; Date') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Unit') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Issue') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Priority') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Assigned To') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EBDBC9]/50 dark:divide-[#273244]">
                        @forelse($tickets as $ticket)
                            <tr class="hover:bg-surface dark:hover:bg-surface/30 transition-colors">
                                <td class="p-4">
                                    <p class="font-bold text-muted dark:text-white">
                                        <a href="{{ route('dashboard.operations.tickets.show', $ticket['id']) }}" class="hover:text-primary-600">{{ $ticket['label'] }}</a>
                                    </p>
                                    <p class="text-xs text-muted">{{ date('M d, Y', strtotime($ticket['date'])) }}</p>
                                </td>
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
                                    @php
                                        $sClass = match($ticket['status']) {
                                            'completed' => 'badge-success',
                                            'in_progress' => 'badge-primary',
                                            'assigned' => 'badge-warning',
                                            default => 'badge-neutral',
                                        };
                                    @endphp
                                    <span class="badge {{ $sClass }} uppercase text-xs">{{ __(str_replace('_', ' ', $ticket['status'])) }}</span>
                                </td>
                                <td class="p-4 text-muted dark:text-dark-subtle">
                                    @if($ticket['assigned'] && $ticket['assigned'] !== 'Unassigned')
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
                        @empty
                            <tr>
                                <td colspan="7" class="p-8 text-center text-muted">{{ __('No maintenance tickets found.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(method_exists($tickets, 'links'))
                <div class="p-4 border-t border-[#EBDBC9] dark:border-[#273244]">
                    {{ $tickets->links() }}
                </div>
            @endif
        </div>

        {{-- Create Ticket Modal --}}
        <div x-show="showCreateTicket" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="absolute inset-0 bg-black/50" @click="showCreateTicket = false"></div>
            <div class="relative bg-white dark:bg-dark-card rounded-2xl shadow-2xl w-full max-w-lg p-6 z-10">
                <div class="flex justify-between items-center mb-5">
                    <h3 class="text-lg font-bold text-muted dark:text-white">{{ __('New Maintenance Ticket') }}</h3>
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
