<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Ticket') }} {{ $ticket['id'] }}</x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="mb-8">
            <a href="{{ route('dashboard.operations.tickets') }}" class="text-sm font-medium text-muted hover:text-muted dark:hover:text-white flex items-center gap-2 mb-4 transition-colors w-max">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                {{ __('Back to Tickets') }}
            </a>
            
            <div class="flex flex-col sm:flex-row justify-between sm:items-end gap-4">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <span class="badge {{ $ticket['status'] === 'in_progress' ? 'badge-primary' : 'badge-neutral' }} uppercase">{{ __(str_replace('_', ' ', $ticket['status'])) }}</span>
                        <span class="badge {{ $ticket['priority'] === 'high' ? 'badge-error' : 'badge-warning' }} uppercase">{{ __($ticket['priority']) }} {{ __('Priority') }}</span>
                    </div>
                    <h1 class="text-3xl font-extrabold font-display text-muted dark:text-white tracking-tight">{{ $ticket['issue'] }}</h1>
                    <div class="flex items-center gap-3 mt-2 text-sm text-muted">
                        <span>{{ __('Ticket') }} {{ $ticket['id'] }}</span>
                        <span>&bull;</span>
                        <span>{{ __('Created') }} {{ $ticket['created_at'] }}</span>
                    </div>
                </div>
                <div class="flex gap-2 flex-wrap">
                    <button class="btn btn-secondary">{{ __('Reassign') }}</button>
                    <button class="btn btn-primary">{{ __('Mark Completed') }}</button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-8">
                <!-- Details -->
                <div class="card p-6 md:p-8">
                    <h2 class="card-title text-xl mb-4">{{ __('Description') }}</h2>
                    <p class="text-muted dark:text-dark-subtle leading-relaxed mb-6">{{ $ticket['description'] }}</p>
                    
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
                            <p class="card-kv-value font-medium text-primary-600 dark:text-primary-400 mt-1">{{ $ticket['assigned'] ? $ticket['assigned'] : __('Unassigned') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Update Work Order / Notes -->
                <div class="card p-6 md:p-8">
                    <h2 class="card-title text-xl mb-6">{{ __('Add Update / Note') }}</h2>
                    <form>
                        <textarea class="input w-full h-32 mb-4" placeholder="{{ __('Enter work performed, parts used, or notes...') }}"></textarea>
                        <div class="flex justify-between items-center">
                            <label class="flex items-center gap-2 text-sm text-muted dark:text-dark-subtle cursor-pointer">
                                <input type="checkbox" class="rounded border-warm text-primary-600 focus:ring-primary-600">
                                {{ __('Visible to tenant') }}
                            </label>
                            <button type="button" class="btn btn-secondary">{{ __('Post Update') }}</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Timeline -->
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
