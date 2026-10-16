<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Lead') }}: {{ $lead['name'] }}</x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="mb-8">
            <a href="{{ route('dashboard.crm.leads') }}" class="text-sm font-medium text-muted hover:text-muted dark:hover:text-white flex items-center gap-2 mb-4 transition-colors w-max">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                {{ __('Back to Leads') }}
            </a>
            
            <div class="flex flex-col sm:flex-row justify-between sm:items-end gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold font-display text-muted dark:text-white tracking-tight">{{ $lead['name'] }}</h1>
                    <div class="flex items-center gap-3 mt-2">
                        <span class="badge badge-neutral uppercase">{{ __($lead['status']) }}</span>
                        <span class="text-muted">&bull;</span>
                        <span class="text-muted dark:text-dark-subtle font-medium">{{ $lead['value'] }}</span>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button class="btn btn-secondary">{{ __('Log Activity') }}</button>
                    <button class="btn btn-primary">{{ __('Convert to Client') }}</button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-8">
                <div class="card p-6 sm:p-8">
                    <h2 class="card-title text-xl mb-6">{{ __('Activity Timeline') }}</h2>
                    
                    <div class="space-y-6 relative before:absolute before:inset-0 before:ml-5 before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-gradient-to-b before:from-transparent before:via-[#EBDBC9] dark:before:via-[#273244] before:to-transparent">
                        @foreach($timeline as $item)
                            <div class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
                                <!-- Icon -->
                                <div class="flex items-center justify-center w-10 h-10 rounded-full border border-white dark:border-[#111827] bg-orange-100 dark:bg-orange-950/40 text-orange-600 dark:text-orange-400 shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 shadow-sm z-10">
                                    @if($item['type'] === 'call')
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                                    @elseif($item['type'] === 'email')
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                    @elseif($item['type'] === 'viewing')
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    @else
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    @endif
                                </div>
                                
                                <!-- Card -->
                                <div class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] card-nested">
                                    <div class="flex items-center justify-between mb-1">
                                        <h3 class="font-bold text-muted dark:text-white text-sm">{{ $item['title'] }}</h3>
                                    </div>
                                    <div class="text-xs text-muted dark:text-muted flex justify-between">
                                        <span>{{ $item['user'] }}</span>
                                        <span>{{ $item['date'] }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="card p-6">
                    <h3 class="card-title text-base mb-4">{{ __('Lead Details') }}</h3>
                    <ul class="space-y-4">
                        <li>
                            <p class="card-kv-label">{{ __('Property of Interest') }}</p>
                            <p class="card-kv-value">{{ $lead['property'] }}</p>
                        </li>
                        <li>
                            <p class="card-kv-label">{{ __('Expected Value') }}</p>
                            <p class="card-kv-value">{{ $lead['value'] }}</p>
                        </li>
                        <li>
                            <p class="card-kv-label">{{ __('Assigned Agent') }}</p>
                            <p class="card-kv-value text-orange-600 dark:text-orange-400">{{ $lead['assigned_to'] }}</p>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
