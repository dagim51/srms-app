<x-layouts.dashboard>
    <x-slot name="page-title">{{ $project['name'] }} - Dashboard</x-slot>

    <div class="max-w-7xl mx-auto page-enter">
        <div class="mb-4">
            <a href="{{ route('dashboard.projects.index') }}" class="text-sm font-medium text-muted hover:text-muted dark:hover:text-white flex items-center gap-2 transition-colors w-max">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Back to Projects
            </a>
        </div>

        <x-page-header
            :title="$project['name']"
            :description="$project['location'] . ' • Status: ' . str_replace('_', ' ', $project['status'])"
            icon="building"
            :badge="str_replace('_', ' ', strtoupper($project['status']))">
            <x-slot:actions>
                <div class="flex gap-2">
                    <a href="{{ route('projects.show', $project['id']) }}" target="_blank" class="btn btn-secondary">{{ __('View Public Page') }}</a>
                    <button class="btn btn-primary">{{ __('Edit Project') }}</button>
                </div>
            </x-slot:actions>
        </x-page-header>

        @include('frontend.developer.projects.partials.tabs')

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
            <x-card-stat
                :label="__('Total Units')"
                :value="$project['total_units']"
                iconColor="orange"
                icon="building"
                :badge="__('Inventory')"
                badgeVariant="neutral"
            />
            <x-card-stat
                :label="__('Units Sold')"
                :value="$project['sold_units']"
                iconColor="emerald"
                icon="check"
                :badge="__('Sold')"
                badgeVariant="success"
                :caption="round(($project['sold_units'] / $project['total_units']) * 100) . '% of inventory'"
            />
            <x-card-stat
                :label="__('Available')"
                :value="$project['total_units'] - $project['sold_units']"
                iconColor="blue"
                icon="building"
                :badge="__('Ready')"
                badgeVariant="info"
            />
            <x-card-stat
                :label="__('Completion Date')"
                :value="date('M Y', strtotime($project['completion_date']))"
                iconColor="purple"
                icon="calendar"
                :badge="__('Target')"
                badgeVariant="brand"
            />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2">
                <div class="card p-6 md:p-8 mb-8">
                    <h2 class="card-title text-lg mb-4">{{ __('Project Overview') }}</h2>
                    <div class="aspect-video rounded-2xl overflow-hidden mb-6">
                        <img src="{{ $project['image'] }}" alt="Project cover" class="w-full h-full object-cover">
                    </div>
                    <p class="text-muted dark:text-dark-subtle">
                        A premium residential development located in the heart of {{ $project['location'] }}. Featuring state-of-the-art amenities, sustainable design, and breathtaking views of the city.
                    </p>
                </div>
            </div>
            <div>
                <div class="card p-6">
                    <h2 class="card-title text-lg mb-4">{{ __('Quick Actions') }}</h2>
                    <div class="space-y-3">
                        <a href="{{ route('dashboard.projects.units', $project['id']) }}" class="card-nested flex items-center justify-between p-4 hover:border-primary-500 transition-colors">
                            <span class="font-bold text-muted dark:text-white">{{ __('Add New Unit') }}</span>
                            <svg class="w-5 h-5 text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </a>
                        <a href="{{ route('dashboard.projects.progress', $project['id']) }}" class="card-nested flex items-center justify-between p-4 hover:border-primary-500 transition-colors">
                            <span class="font-bold text-muted dark:text-white">{{ __('Update Progress') }}</span>
                            <svg class="w-5 h-5 text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </a>
                        <a href="{{ route('dashboard.projects.documents', $project['id']) }}" class="card-nested flex items-center justify-between p-4 hover:border-primary-500 transition-colors">
                            <span class="font-bold text-muted dark:text-white">{{ __('Upload Document') }}</span>
                            <svg class="w-5 h-5 text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
