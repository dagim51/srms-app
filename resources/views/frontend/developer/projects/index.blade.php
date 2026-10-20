<x-layouts.dashboard :title="__('Developer Projects')">
    <div class="max-w-7xl mx-auto space-y-6 page-enter">
        <x-page-header
            :title="__('Developer Projects')"
            :description="__('Manage multi-unit residential developments, tower phases, sales inventory, and construction milestones.')"
            icon="building"
            badge="Developments"
        >
            <x-slot:actions>
                <button class="btn btn-primary shadow-xs hover:shadow transition-all inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    <span>{{ __('Add Project') }}</span>
                </button>
            </x-slot:actions>
        </x-page-header>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($projects as $project)
                <a href="{{ route('dashboard.projects.show', $project['id']) }}" class="block group">
                    <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-3xl overflow-hidden shadow-sm group-hover:border-primary-500 transition-colors">
                        <div class="h-48 overflow-hidden relative">
                            <img src="{{ $project['image'] }}" alt="{{ $project['name'] }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                            <div class="absolute bottom-4 left-4 right-4 flex justify-between items-end">
                                <div>
                                    <h3 class="text-xl font-bold text-white">{{ $project['name'] }}</h3>
                                    <p class="text-white/80 text-sm flex items-center gap-1 mt-1">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                        {{ $project['location'] }}
                                    </p>
                                </div>
                                <span class="badge {{ $project['status'] === 'under_construction' ? 'badge-primary' : 'badge-neutral' }} uppercase text-xs">{{ __(str_replace('_', ' ', $project['status'])) }}</span>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div>
                                    <p class="text-xs text-muted font-bold uppercase">{{ __('Total Units') }}</p>
                                    <p class="font-medium text-muted dark:text-white">{{ $project['total_units'] }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-muted font-bold uppercase">{{ __('Sold') }}</p>
                                    <p class="font-medium text-muted dark:text-white">{{ $project['sold_units'] }}</p>
                                </div>
                            </div>
                            
                            <div>
                                <div class="flex justify-between items-center text-xs mb-1">
                                    <span class="font-bold text-muted dark:text-dark-subtle">{{ __('Construction Progress') }}</span>
                                    <span class="text-primary-600 font-bold">{{ $project['progress'] }}%</span>
                                </div>
                                <div class="w-full bg-surface-subtle dark:bg-surface rounded-full h-2">
                                    <div class="bg-primary-500 h-2 rounded-full" style="width: {{ $project['progress'] }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

    </div>
</x-layouts.dashboard>
