<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Saved Searches & Alerts') }}</x-slot>

    <div class="max-w-7xl mx-auto page-enter">
        <x-page-header
            :title="__('Saved Searches & Alerts')"
            :description="__('Configure automated property watchlists, instant notifications, and market price-drop monitors.')"
            icon="sparkles"
            badge="Watchlists"
        >
            <x-slot:actions>
                <button class="btn btn-primary shadow-xs hover:shadow transition-all inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    <span>{{ __('Create Alert') }}</span>
                </button>
            </x-slot:actions>
        </x-page-header>

        @include('frontend.alerts.partials.tabs')

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($alerts as $alert)
                <div class="card bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-3xl p-6 flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-start mb-4">
                            <span class="badge {{ $alert['type'] === 'Price Alert' ? 'badge-warning' : 'badge-neutral' }} uppercase text-xs">{{ $alert['type'] }}</span>
                            @if($alert['status'] === 'active')
                                <span class="flex items-center gap-1.5 text-xs font-bold text-success-600">
                                    <span class="w-2 h-2 rounded-full bg-success-500 animate-pulse"></span> Active
                                </span>
                            @else
                                <span class="text-xs font-bold text-muted">{{ __('Paused') }}</span>
                            @endif
                        </div>
                        <h3 class="font-bold text-lg text-muted dark:text-white mb-2">{{ $alert['name'] }}</h3>
                        <p class="text-sm text-muted mb-4">{{ $alert['criteria'] }}</p>
                    </div>
                    
                    <div>
                        <div class="card flex items-center justify-between text-sm text-muted dark:text-dark-subtle mb-4 bg-surface dark:bg-surface/50 p-3 rounded-xl border border-warm dark:border-warm">
                            <div>
                                <span class="block text-xs font-bold text-muted uppercase">{{ __('Frequency') }}</span>
                                <span class="font-medium">{{ $alert['frequency'] }}</span>
                            </div>
                            <div class="text-right">
                                <span class="block text-xs font-bold text-muted uppercase">{{ __('New Matches') }}</span>
                                <span class="font-bold text-primary-600">{{ $alert['results'] }}</span>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('dashboard.alerts.show', $alert['id']) }}" class="btn btn-secondary flex-1 justify-center">{{ __('Manage') }}</a>
                            <button class="btn btn-secondary text-muted hover:text-muted px-3">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z" /></svg>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

    </div>
</x-layouts.dashboard>
