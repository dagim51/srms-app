<x-layouts.dashboard>
    <x-slot name="page-title">Buyer: {{ $buyer['name'] }}</x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="mb-8">
            <a href="{{ route('dashboard.sales.buyers') }}" class="text-sm font-medium text-muted hover:text-muted dark:hover:text-white flex items-center gap-2 mb-4 transition-colors w-max">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Back to Buyers
            </a>
            
            <div class="flex flex-col sm:flex-row justify-between sm:items-end gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 flex items-center justify-center font-bold text-2xl">
                        {{ substr($buyer['name'], 0, 1) }}
                    </div>
                    <div>
                        <h1 class="text-3xl font-extrabold font-display text-muted dark:text-white tracking-tight">{{ $buyer['name'] }}</h1>
                        <div class="flex items-center gap-3 mt-1 text-sm text-muted">
                            <span>{{ $buyer['email'] }}</span>
                            <span>&bull;</span>
                            <span>{{ $buyer['phone'] }}</span>
                        </div>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button class="btn btn-secondary">{{ __('Edit Profile') }}</button>
                    <button class="btn btn-primary">{{ __('New Reservation') }}</button>
                </div>
            </div>
        </div>

        <div class="card p-6 md:p-8">
            <h2 class="card-title text-xl mb-6">{{ __('Purchased Units Portfolio') }}</h2>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                @foreach($purchased_units as $unit)
                    <div class="card-nested p-6 hover:border-primary-500 transition-colors">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="font-bold text-lg text-muted dark:text-white">Unit {{ $unit['number'] }}</h3>
                                <p class="text-sm text-muted">{{ $unit['project'] }} &mdash; {{ $unit['building'] }}</p>
                            </div>
                            <span class="badge badge-warning uppercase text-xs">{{ $unit['status'] }}</span>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4 mb-4 text-sm">
                            <div>
                                <p class="card-kv-label">{{ __('Purchase Price') }}</p>
                                <p class="card-kv-value mt-1">{{ $unit['price'] }}</p>
                            </div>
                            <div>
                                <p class="card-kv-label">{{ __('Type') }}</p>
                                <p class="card-kv-value mt-1">{{ $unit['type'] }}</p>
                            </div>
                        </div>
                        
                        <a href="{{ route('dashboard.sales.units.show', $unit['id']) }}" class="btn btn-sm btn-secondary w-full justify-center">{{ __('View Unit Details & Payments') }}</a>
                    </div>
                @endforeach
            </div>
        </div>

    </div>
</x-layouts.dashboard>
