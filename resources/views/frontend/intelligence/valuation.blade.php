<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Valuation Tool') }}</x-slot>

    <div class="max-w-7xl mx-auto page-enter">
        <x-page-header
            :title="__('Valuation Tool')"
            :description="__('Estimate accurate market property valuations using recent comparable sales, spatial proximity, and algorithmic intelligence.')"
            icon="sparkles"
            badge="Valuation Model"
        />

        @include('frontend.intelligence.partials.tabs')

        <!-- Estimate Banner -->
        <div class="card p-6 md:p-8 mb-8 bg-gradient-to-br from-primary-600 to-indigo-700 text-white relative overflow-hidden border-none shadow-md">
            <div class="absolute inset-0 opacity-10 bg-[radial-gradient(circle_at_top_right,_var(--tw-gradient-stops))] from-white via-transparent to-transparent"></div>
            
            <div class="relative z-10 flex flex-col md:flex-row justify-between items-center gap-6">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="badge bg-white/20 text-white border-none uppercase text-xs backdrop-blur-md">{{ __('Estimate') }}</span>
                        <span class="text-sm font-medium text-primary-100 flex items-center gap-1" title="Confidence Score">
                            <svg class="w-4 h-4 text-success-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            {{ $estimate['confidence'] }} Confidence
                        </span>
                    </div>
                    <h2 class="text-4xl md:text-5xl font-black font-display tracking-tight">{{ $estimate['value'] }}</h2>
                    <p class="text-primary-100 mt-2 font-medium">Estimated Market Range: {{ $estimate['range'] }}</p>
                </div>
                <div class="text-left md:text-right w-full md:w-auto">
                    <p class="text-sm text-primary-200 italic max-w-xs mb-4 text-left">{{ __('This automated valuation is an estimate only and does not constitute a formal appraisal.') }}</p>
                    <button class="btn bg-white text-primary-700 hover:bg-surface w-full md:w-auto">{{ __('Recalculate') }}</button>
                </div>
            </div>
        </div>

        <!-- Comparables -->
        <div class="card p-6 md:p-8">
            <h3 class="card-title text-xl mb-6">{{ __('Comparable Properties') }}</h3>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-warm dark:border-warm text-xs font-semibold text-muted uppercase tracking-wider">
                            <th class="py-3 font-semibold">{{ __('Property Address') }}</th>
                            <th class="py-3 font-semibold">{{ __('Sold Price') }}</th>
                            <th class="py-3 font-semibold">{{ __('Specs') }}</th>
                            <th class="py-3 font-semibold">{{ __('Sold Date') }}</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-warm dark:divide-dark-border">
                        @foreach($estimate['comparables'] as $comp)
                            <tr class="hover:bg-surface dark:hover:bg-surface/50 transition-colors">
                                <td class="py-4 font-medium text-muted dark:text-white">{{ $comp['address'] }}</td>
                                <td class="py-4 font-bold text-primary-600 dark:text-primary-400">{{ $comp['price'] }}</td>
                                <td class="py-4 text-muted dark:text-muted">{{ $comp['beds'] }} Bed, {{ $comp['baths'] }} Bath, {{ $comp['sqm'] }} m²</td>
                                <td class="py-4 text-muted dark:text-muted">{{ $comp['date'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
