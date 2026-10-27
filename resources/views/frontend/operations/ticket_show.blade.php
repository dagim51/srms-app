<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Ticket') }} {{ $ticket['label'] }}</x-slot>

    <div class="max-w-7xl mx-auto page-enter space-y-6" x-data="{ showStatusModal: false, showReassignModal: false }">
        <x-page-header
            :title="$ticket['issue']"
            :description="__('Ticket') . ' ' . $ticket['label'] . ' • ' . __('Priority:') . ' ' . __($ticket['priority']) . ' • ' . __('Created') . ' ' . $ticket['created_at']"
            icon="ticket"
            :badge="$ticket['priority']">
            <x-slot:actions>
                <div class="flex items-center gap-2 flex-wrap">
                    <a href="{{ route('dashboard.operations.tickets') }}" class="btn btn-secondary text-xs flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                        {{ __('Back to Tickets') }}
                    </a>
                    <button @click="showReassignModal = true" class="btn btn-secondary text-xs">{{ __('Reassign') }}</button>
                    @if($ticket['status'] !== 'completed')
                        <form method="POST" action="{{ route('dashboard.operations.tickets.status', $ticket['id']) }}">
                            @csrf
                            <input type="hidden" name="status" value="completed">
                            <button type="submit" class="btn btn-primary text-xs">{{ __('Mark Completed') }}</button>
                        </form>
                    @else
                        <span class="badge badge-success text-xs px-3 py-1.5">{{ __('Completed') }}</span>
                    @endif
                </div>
            </x-slot:actions>
        </x-page-header>

        @if(session('success'))
            <div class="px-4 py-3 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 text-sm border border-emerald-200 dark:border-emerald-800 flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-8">
                {{-- Details --}}
                <div class="card p-6 md:p-8">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="card-title text-xl">{{ __('Description') }}</h2>
                        @php
                            $sClass = match($ticket['status']) {
                                'completed' => 'badge-success',
                                'in_progress' => 'badge-primary',
                                'assigned' => 'badge-warning',
                                default => 'badge-neutral',
                            };
                        @endphp
                        <span class="badge {{ $sClass }} uppercase text-xs">{{ __(str_replace('_', ' ', $ticket['status'])) }}</span>
                    </div>
                    <p class="text-muted dark:text-dark-subtle leading-relaxed mb-6 whitespace-pre-line">{{ $ticket['description'] }}</p>
                    
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-6 pt-6 border-t border-[#EBDBC9] dark:border-[#273244]">
                        <div>
                            <p class="card-kv-label">{{ __('Unit') }}</p>
                            <p class="card-kv-value mt-1">{{ $ticket['unit'] }}</p>
                        </div>
                        <div>
                            <p class="card-kv-label">{{ __('Reporter') }}</p>
                            <p class="card-kv-value font-medium mt-1">{{ $ticket['reporter'] }}</p>
                            <p class="text-xs text-muted mt-0.5">{{ $ticket['reporter_phone'] }}</p>
                        </div>
                        <div>
                            <p class="card-kv-label">{{ __('Assigned To') }}</p>
                            <p class="card-kv-value font-medium text-primary-600 dark:text-primary-400 mt-1">{{ $ticket['assigned'] ?: __('Unassigned') }}</p>
                        </div>
                    </div>
                </div>

                {{-- Update Status --}}
                <div class="card p-6 md:p-8">
                    <h2 class="card-title text-xl mb-4">{{ __('Update Status') }}</h2>
                    <form method="POST" action="{{ route('dashboard.operations.tickets.status', $ticket['id']) }}" class="flex flex-wrap gap-3 items-end">
                        @csrf
                        <div class="flex-1 min-w-[180px]">
                            <label class="block text-sm font-medium text-muted dark:text-dark-subtle mb-1">{{ __('Status') }}</label>
                            <select name="status" class="input w-full">
                                @foreach(['open', 'assigned', 'in_progress', 'waiting', 'completed', 'closed'] as $s)
                                    <option value="{{ $s }}" {{ $ticket['status'] === $s ? 'selected' : '' }}>{{ __(ucfirst(str_replace('_', ' ', $s))) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex-1 min-w-[180px]">
                            <label class="block text-sm font-medium text-muted dark:text-dark-subtle mb-1">{{ __('Assigned To') }}</label>
                            <select name="assigned_to" class="input w-full">
                                <option value="">{{ __('— Unassigned —') }}</option>
                                @foreach($staffUsers as $user)
                                    <option value="{{ $user->id }}" {{ $ticket['assigned_to'] === $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">{{ __('Update Status') }}</button>
                    </form>
                </div>

                {{-- Post Note --}}
                <div class="card p-6 md:p-8">
                    <h2 class="card-title text-xl mb-6">{{ __('Add Update / Note') }}</h2>
                    <form method="POST" action="{{ route('dashboard.operations.tickets.note', $ticket['id']) }}">
                        @csrf
                        <textarea name="note" class="input w-full h-32 mb-4" placeholder="{{ __('Enter work performed, parts used, or notes...') }}" required></textarea>
                        <div class="flex justify-between items-center">
                            <label class="flex items-center gap-2 text-sm text-muted dark:text-dark-subtle cursor-pointer">
                                <input type="checkbox" name="visible_tenant" value="1" class="rounded border-warm text-primary-600 focus:ring-primary-600">
                                {{ __('Visible to tenant') }}
                            </label>
                            <button type="submit" class="btn btn-secondary">{{ __('Post Update') }}</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Timeline --}}
            <div>
                <div class="card p-6 sticky top-6">
                    <h3 class="card-title text-base mb-6">{{ __('Activity Timeline') }}</h3>
                    <div class="space-y-6 relative before:absolute before:inset-0 before:ml-5 before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-gradient-to-b before:from-transparent before:via-[#EBDBC9] dark:before:via-[#273244] before:to-transparent">
                        @foreach($timeline as $event)
                            <div class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
                                <div class="flex items-center justify-center w-10 h-10 rounded-full border-4 border-white dark:border-[#111827] bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 shadow-xs z-10">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                                <div class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] card-nested p-4">
                                    <p class="font-bold text-sm text-muted dark:text-white">{{ __($event['status']) }}</p>
                                    <div class="flex justify-between items-center mt-2 text-xs text-muted">
                                        <span>{{ $event['user'] }}</span>
                                        <span>{{ $event['date'] }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
