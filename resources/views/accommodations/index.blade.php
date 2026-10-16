<x-layouts.app>
    <x-slot name="title">{{ __('Search Accommodations') }}</x-slot>

    {{-- Subtle ambient background --}}
    <div class="fixed inset-0 pointer-events-none overflow-hidden -z-10">
        <div class="absolute top-0 left-1/2 w-full max-w-3xl h-full max-h-3xl bg-primary-400/5 rounded-full blur-[120px] -translate-x-1/2"></div>
    </div>

    <div class="page-container pt-28 pb-24">
        
        {{-- Search Header --}}
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-12">
            <div class="text-center mb-8">
                <h1 class="text-3xl md:text-4xl font-extrabold font-display text-muted dark:text-white tracking-tight mb-4">
                    {{ __('Find your perfect stay') }}
                </h1>
                <p class="text-lg text-muted dark:text-muted max-w-2xl mx-auto">
                    {{ __('Search verified accommodations across Ethiopia') }}
                </p>
            </div>

            {{-- Search Form --}}
            <div class="rounded-2xl border border-warm dark:border-warm bg-white dark:bg-surface/50 overflow-hidden shadow-xl">
                <form action="{{ route('accommodations.search') }}" method="GET" class="p-6 space-y-6">
                    
                    {{-- Location & Dates --}}
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-black uppercase tracking-widest text-muted dark:text-muted mb-2">
                                {{ __('Where') }}
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-muted">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                </div>
                                <input type="text" name="location" value="{{ request('location') }}" placeholder="{{ __('City, neighborhood, or landmark...') }}" class="w-full pl-10 pr-4 py-4 text-muted dark:text-white bg-transparent border-0 focus:ring-0 text-lg">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-muted dark:text-muted mb-2">
                                {{ __('Check-in') }}
                            </label>
                            <input type="date" name="check_in" value="{{ request('check_in', now()->addDay()->format('Y-m-d')) }}" class="w-full px-4 py-4 text-muted dark:text-white bg-transparent border-0 focus:ring-0 text-lg" required min="{{ now()->format('Y-m-d') }}">
                        </div>
                        
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-muted dark:text-muted mb-2">
                                {{ __('Check-out') }}
                            </label>
                            <input type="date" name="check_out" value="{{ request('check_out', now()->addDays(3)->format('Y-m-d')) }}" class="w-full px-4 py-4 text-muted dark:text-white bg-transparent border-0 focus:ring-0 text-lg" required min="{{ now()->addDays(2)->format('Y-m-d') }}">
                        </div>
                    </div>

                    {{-- Guests --}}
                    <div class="flex items-end gap-4">
                        <div class="flex-1">
                            <label class="block text-xs font-black uppercase tracking-widest text-muted dark:text-muted mb-2">
                                {{ __('Guests') }}
                            </label>
                            <select name="guests" class="w-full px-4 py-4 text-muted dark:text-white bg-transparent border-0 focus:ring-0 text-lg" required>
                                @for($i = 1; $i <= 16; $i++)
                                    <option value="{{ $i }}" {{ request('guests') == $i ? 'selected' : '' }} class="dark:bg-surface">
                                        {{ $i }} guest{{ $i > 1 ? 's' : '' }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        
                        <button type="submit" class="relative overflow-hidden group w-full sm:w-auto py-4 px-8 rounded-xl bg-surface dark:bg-primary-600 text-white font-bold text-lg shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all duration-200 overflow-hidden">
                            <span class="relative z-10">{{ __('Search') }}</span>
                            <svg class="w-5 h-5 relative z-10 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                            <div class="absolute inset-0 bg-gradient-to-r from-primary-600 to-indigo-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Results --}}
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-8">
                <h2 class="text-xl font-bold text-muted dark:text-white">
                    {{ $accommodations->count() }} {{ $accommodations->count() === 1 ? 'result' : 'results' }}
                </h2>
            </div>

            @if($accommodations->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" x-data="accommodationSearch()" x-init="init()">
                    @foreach($accommodations as $accommodation)
                        @include('components.accommodation-card', ['accommodation' => $accommodation])
                    @endforeach
                </div>
                
                {{-- Pagination --}}
                <div class="mt-10">
                    {{ $accommodations->links() }}
                </div>
            @else
                <div class="text-center py-20">
                    <svg class="w-24 h-24 text-muted dark:text-muted mx-auto mb-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    <h3 class="text-xl font-bold text-muted dark:text-white mb-2">{{ __('No accommodations found') }}</h3>
                    <p class="text-muted dark:text-muted max-w-md mx-auto">{{ __('Try adjusting your search criteria or dates') }}</p>
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>