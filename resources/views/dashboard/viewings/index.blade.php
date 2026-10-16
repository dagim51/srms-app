<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('My Viewings') }}</x-slot>
    <x-slot name="breadcrumb">
        <a href="{{ route('dashboard') }}" class="hover:text-primary-600 transition-colors">{{ __('Dashboard') }}</a>
        <span>/</span>
        <span class="text-muted dark:text-white font-medium">{{ __('Viewings') }}</span>
    </x-slot>

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold font-display text-muted dark:text-white">{{ __('Scheduled Viewings') }}</h2>
            <div class="flex items-center gap-2">
                {{-- Toggle View (Calendar / List) --}}
                <div class="bg-surface-subtle dark:bg-[#1E293B] p-1 rounded-xl flex">
                    <button class="px-3 py-1.5 rounded-lg text-sm font-medium bg-white dark:bg-surface shadow-sm text-muted dark:text-white">{{ __('List') }}</button>
                    <button class="px-3 py-1.5 rounded-lg text-sm font-medium text-muted hover:text-muted dark:hover:text-white">{{ __('Calendar') }}</button>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 text-emerald-800 text-sm font-medium border border-emerald-200">
                {{ session('success') }}
            </div>
        @endif

        @if($viewings->isEmpty())
            <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-[#273244] rounded-2xl shadow-sm overflow-hidden p-12 text-center">
                <div class="w-16 h-16 bg-surface dark:bg-surface rounded-full flex items-center justify-center mx-auto mb-4 text-primary-500">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" /></svg>
                </div>
                <h3 class="text-lg font-bold text-muted dark:text-white mb-2">{{ __('No upcoming viewings') }}</h3>
                <p class="text-muted max-w-sm mx-auto mb-6 text-sm">{{ __('When you request to view a property or someone requests to view yours, they will appear here.') }}</p>
                <a href="{{ route('properties.index') }}" class="btn btn-primary shadow-md">{{ __('Explore Properties') }}</a>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($viewings as $viewing)
                    @php
                        $isAgent = $viewing->agent_id === auth()->id();
                        $statusColors = [
                            'requested' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300',
                            'confirmed' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300',
                            'rescheduled' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300',
                            'completed' => 'bg-surface-subtle text-muted dark:bg-surface dark:text-dark-subtle',
                            'cancelled' => 'bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-300',
                        ];
                        $badgeColor = $statusColors[$viewing->status] ?? $statusColors['requested'];
                    @endphp
                    
                    <a href="{{ route('dashboard.viewings.show', $viewing) }}" class="block bg-white dark:bg-[#1E293B] border border-warm dark:border-[#273244] rounded-2xl shadow-sm hover:shadow-md transition-shadow group overflow-hidden">
                        <div class="h-2 w-full {{ $viewing->status === 'confirmed' ? 'bg-emerald-500' : ($viewing->status === 'requested' ? 'bg-amber-500' : 'bg-surface-subtle dark:bg-surface') }}"></div>
                        
                        <div class="p-5">
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <span class="inline-block px-2.5 py-1 rounded-md text-xs font-bold uppercase tracking-wider {{ $badgeColor }}">
                                        {{ __($viewing->status) }}
                                    </span>
                                </div>
                                <div class="text-right">
                                    <div class="text-2xl font-bold font-display text-muted dark:text-white leading-none">{{ $viewing->scheduled_at->format('d') }}</div>
                                    <div class="text-xs font-bold text-muted uppercase tracking-wide">{{ $viewing->scheduled_at->format('M, Y') }}</div>
                                </div>
                            </div>

                            <h3 class="font-bold text-muted dark:text-white text-base mb-1 group-hover:text-primary-600 transition-colors line-clamp-1">
                                {{ $viewing->listing->property->title ?? __('Property Details') }}
                            </h3>
                            
                            <div class="flex items-center text-sm text-muted dark:text-muted mb-4 gap-2">
                                <svg class="w-4 h-4 text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                {{ $viewing->scheduled_at->format('g:i A') }}
                            </div>
                            
                            <div class="pt-4 border-t border-warm dark:border-warm flex items-center justify-between text-xs">
                                <span class="text-muted font-medium">
                                    {{ $isAgent ? __('Requested by') : __('Agent') }}:
                                    <span class="text-muted dark:text-white font-bold ml-1">
                                        {{ $isAgent ? ($viewing->requester->name ?? __('User')) : ($viewing->agent->name ?? __('Agent')) }}
                                    </span>
                                </span>
                                
                                <span class="text-primary-600 font-bold group-hover:underline flex items-center gap-1">
                                    {{ __('Details') }}
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                </span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.dashboard>
