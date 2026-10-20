<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Recommendations') }}</x-slot>

    <div class="max-w-7xl mx-auto page-enter">
        <x-page-header
            title="Personalized Recommendations"
            description="Personalized property recommendations based on your preferences, criteria, and saved searches."
            icon="sparkles"
            badge="For You" />

        @include('frontend.alerts.partials.tabs')

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($recommendations as $rec)
                <div class="bg-white dark:bg-[#1E293B] border border-primary-200 dark:border-primary-900/30 rounded-3xl overflow-hidden shadow-sm hover:shadow-lg transition-all group">
                    <div class="relative h-48 overflow-hidden bg-surface-subtle dark:bg-surface">
                        <!-- Simulated Image -->
                        <div class="absolute inset-0 bg-cover bg-center transition-transform duration-500 group-hover:scale-110" style="background-image: url('{{ $rec['image'] }}')"></div>
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                        
                        <!-- Recommendation Badge -->
                        <div class="absolute top-4 left-4">
                            <span class="badge badge-primary bg-primary-600 border-none shadow-sm flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" /></svg>
                                {{ $rec['match_score'] }}% Match
                            </span>
                        </div>
                    </div>
                    
                    <div class="p-6 relative">
                        <h3 class="font-bold text-lg text-muted dark:text-white mb-1 line-clamp-1" title="{{ $rec['title'] }}">{{ $rec['title'] }}</h3>
                        <p class="font-black text-primary-600 mb-4">{{ $rec['price'] }}</p>
                        
                        <div class="bg-primary-50 dark:bg-primary-900/10 rounded-xl p-3 mb-6 border border-primary-100 dark:border-primary-900/20 text-sm text-primary-700 dark:text-primary-300">
                            <strong>Why we recommend this:</strong><br>
                            {{ $rec['reason'] }}
                        </div>
                        
                        <button class="btn btn-primary w-full">{{ __('View Details') }}</button>
                    </div>
                </div>
            @endforeach
        </div>

    </div>
</x-layouts.dashboard>
