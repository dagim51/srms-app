<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('AI Search') }}</x-slot>

    <div class="max-w-7xl mx-auto page-enter">
        <x-page-header
            :title="__('AI Property Search')"
            :description="__('Search for properties using conversational natural language, semantic queries, and automated preference extraction.')"
            icon="search"
            badge="Semantic Search"
        />

        @include('frontend.ai.partials.tabs')

        <!-- Search Bar -->
        <div class="bg-gradient-to-r from-primary-600 to-indigo-700 p-1 rounded-2xl shadow-lg mb-8">
            <form action="{{ route('dashboard.ai.search') }}" method="GET" class="bg-white dark:bg-surface rounded-[12px] p-2 flex flex-col md:flex-row gap-2">
                <div class="flex-1 relative">
                    <input type="text" name="q" value="{{ $query }}" 
                           placeholder="{{ __('e.g. Find a 4-bedroom villa...') }}" 
                           class="w-full bg-transparent border-none outline-none focus:ring-0 py-3 px-4 text-[#111827] dark:text-white placeholder-[#9CA3AF] dark:placeholder-[#64748B] font-medium text-base md:text-lg">
                </div>
                <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-bold py-3 px-6 md:px-8 rounded-xl transition-colors flex items-center justify-center gap-2">
                    <svg class="w-5 h-5 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    Search
                </button>
            </form>
        </div>

        @if($parsed)
            <!-- AI Interpretation -->
            <div class="bg-primary-50 dark:bg-primary-900/20 border border-primary-100 dark:border-primary-800 rounded-2xl p-6 mb-8 flex flex-col md:flex-row gap-6 justify-between items-start md:items-center">
                <div>
                    <h3 class="text-sm font-bold text-primary-700 dark:text-primary-400 uppercase tracking-wider mb-3 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                        AI Interpretation
                    </h3>
                    <div class="flex flex-wrap gap-2">
                        @if(isset($parsed['type']))
                            <span class="badge bg-white dark:bg-surface text-primary-700 border border-primary-200">Type: {{ $parsed['type'] }}</span>
                        @endif
                        @if(isset($parsed['bedrooms']))
                            <span class="badge bg-white dark:bg-surface text-primary-700 border border-primary-200">Beds: {{ $parsed['bedrooms'] }}+</span>
                        @endif
                        @if(isset($parsed['location']))
                            <span class="badge bg-white dark:bg-surface text-primary-700 border border-primary-200">Location: {{ $parsed['location'] }}</span>
                        @endif
                        @if(isset($parsed['max_price']))
                            <span class="badge bg-white dark:bg-surface text-primary-700 border border-primary-200">Max Price: {{ $parsed['max_price'] }}</span>
                        @endif
                        @if(isset($parsed['features']))
                            @foreach($parsed['features'] as $feature)
                                <span class="badge bg-white dark:bg-surface text-primary-700 border border-primary-200">Feature: {{ $feature }}</span>
                            @endforeach
                        @endif
                    </div>
                </div>
                <button class="text-sm font-bold text-primary-600 hover:text-primary-800 underline">{{ __('Edit Filters Manually') }}</button>
            </div>

            <!-- Results -->
            <div class="space-y-4">
                <h2 class="text-xl font-bold text-[#0F172A] dark:text-white mb-4">Results ({{ count($results) }})</h2>
                @foreach($results as $item)
                    <div class="card bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-2xl p-6 flex flex-col md:flex-row gap-6 items-center hover: transition-">
                        <div class="card w-full md:w-48 h-32 bg-surface-subtle dark:bg-surface rounded-xl flex-shrink-0 relative overflow-hidden">
                            <!-- Placeholder image -->
                            <div class="absolute inset-0 bg-surface-subtle dark:bg-surface-subtle"></div>
                            <span class="absolute top-2 left-2 badge badge-neutral bg-white/90 backdrop-blur">{{ $parsed['type'] ?? 'Property' }}</span>
                        </div>
                        <div class="flex-1 w-full">
                            <h3 class="font-bold text-xl text-[#0F172A] dark:text-white">{{ $item['title'] }}</h3>
                            <p class="text-primary-600 font-black text-lg my-1">{{ $item['price'] }}</p>
                            <p class="text-[#4B5563] dark:text-[#CBD5E1] text-sm mb-3 flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                {{ $item['location'] }}
                            </p>
                            <p class="text-[#4B5563] dark:text-[#CBD5E1] text-sm font-medium bg-surface dark:bg-surface px-3 py-2 rounded-lg inline-block">{{ $item['features'] }}</p>
                        </div>
                        <div class="w-full md:w-auto">
                            <button class="btn btn-secondary w-full md:w-auto">{{ __('View Details') }}</button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    </div>
</x-layouts.dashboard>
