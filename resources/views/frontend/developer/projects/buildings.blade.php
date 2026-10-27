<x-layouts.dashboard>
    <x-slot name="page-title">{{ $project['name'] }} - Buildings</x-slot>

    <div class="max-w-7xl mx-auto page-enter">
        <div class="mb-4">
            <a href="{{ route('dashboard.projects.index') }}" class="text-sm font-medium text-muted hover:text-muted dark:hover:text-white flex items-center gap-2 transition-colors w-max">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Back to Projects
            </a>
        </div>

        <x-page-header
            :title="$project['name'] . ' - Buildings'"
            description="Manage towers, blocks, and physical structures within this development project."
            icon="building"
            badge="Buildings">
            <x-slot:actions>
                <button class="btn btn-primary">{{ __('Add Building') }}</button>
            </x-slot:actions>
        </x-page-header>

        @include('frontend.developer.projects.partials.tabs')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($buildings as $building)
                <div class="card bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-3xl p-6">
                    <div class="flex justify-between items-start mb-4">
                        <h3 class="text-xl font-bold text-muted dark:text-white">{{ $building['name'] }}</h3>
                        <span class="badge {{ $building['status'] === 'under_construction' ? 'badge-primary' : 'badge-neutral' }} uppercase text-xs">{{ str_replace('_', ' ', $building['status']) }}</span>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div class="card bg-surface dark:bg-surface/50 rounded-xl p-4">
                            <p class="text-xs font-bold text-muted uppercase">{{ __('Floors') }}</p>
                            <p class="text-2xl font-black text-muted dark:text-white mt-1">{{ $building['floors'] }}</p>
                        </div>
                        <div class="card bg-surface dark:bg-surface/50 rounded-xl p-4">
                            <p class="text-xs font-bold text-muted uppercase">{{ __('Units') }}</p>
                            <p class="text-2xl font-black text-muted dark:text-white mt-1">{{ $building['units'] }}</p>
                        </div>
                    </div>

                    <div class="mb-6">
                        <div class="flex justify-between items-center text-xs mb-1">
                            <span class="font-bold text-muted dark:text-dark-subtle">{{ __('Building Progress') }}</span>
                            <span class="text-primary-600 font-bold">{{ $building['progress'] }}%</span>
                        </div>
                        <div class="w-full bg-surface-subtle dark:bg-surface rounded-full h-2">
                            <div class="bg-primary-500 h-2 rounded-full" style="width: {{ $building['progress'] }}%"></div>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <button class="btn btn-secondary flex-1">{{ __('Edit') }}</button>
                        <a href="{{ route('dashboard.projects.units', $project['id']) }}" class="btn btn-primary flex-1 text-center">{{ __('View Units') }}</a>
                    </div>
                </div>
            @endforeach
        </div>

    </div>
</x-layouts.dashboard>
