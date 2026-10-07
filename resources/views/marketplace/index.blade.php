<x-layouts.app>
    <x-slot name="title">Properties for Sale & Rent in Ethiopia | Marketplace Discovery</x-slot>
    <x-slot name="description">Browse verified properties for sale, rent, and short stay across Addis Ababa, Hawassa, Bahir Dar, and regional Ethiopia.</x-slot>

    <div class="py-12 page-container min-h-screen space-y-8 pt-24">
        {{-- Discovery Hero / Title Bar --}}
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-gray-200 dark:border-gray-800 pb-6">
            <div>
                <nav class="flex items-center gap-2 text-xs text-gray-500 mb-2">
                    <a href="/" class="hover:text-primary-600 transition-colors">Home</a>
                    <span>/</span>
                    <span class="text-gray-900 dark:text-white font-medium">Properties</span>
                </nav>
                <h1 class="text-3xl sm:text-4xl font-display font-extrabold text-gray-900 dark:text-white tracking-tight">
                    Ethiopian Property Discovery
                </h1>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Showing <span class="font-bold text-gray-900 dark:text-white">{{ $listings->total() }}</span> verified marketplace listings
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard.properties.map') }}" class="btn btn-outline text-xs text-primary-600 dark:text-primary-400 border-primary-500/30 hover:bg-primary-50 dark:hover:bg-primary-950/30 flex items-center gap-2 font-bold shadow-sm">
                    <svg class="w-4 h-4 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" /></svg>
                    <span>GIS Map Explorer</span>
                </a>
            </div>
        </div>

        {{-- Filter Console --}}
        <div class="bg-white dark:bg-[#1E293B] border border-gray-200 dark:border-gray-800 rounded-2xl p-5 shadow-sm space-y-4">
            {{-- Mode Filter Tabs --}}
            @php
                $currentPurpose = request('purpose', 'all');
            @endphp
            <div class="flex items-center gap-1 overflow-x-auto pb-1 border-b border-gray-100 dark:border-gray-800">
                <a href="{{ request()->fullUrlWithQuery(['purpose' => 'all']) }}" 
                   class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all {{ $currentPurpose === 'all' ? 'bg-primary-500 text-white shadow-sm' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                    All Listings
                </a>
                <a href="{{ request()->fullUrlWithQuery(['purpose' => 'For Sale']) }}" 
                   class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all {{ $currentPurpose === 'For Sale' ? 'bg-primary-500 text-white shadow-sm' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                    For Sale
                </a>
                <a href="{{ request()->fullUrlWithQuery(['purpose' => 'For Rent']) }}" 
                   class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all {{ $currentPurpose === 'For Rent' ? 'bg-primary-500 text-white shadow-sm' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                    For Rent
                </a>
                <a href="{{ request()->fullUrlWithQuery(['purpose' => 'Short Stay']) }}" 
                   class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all {{ $currentPurpose === 'Short Stay' ? 'bg-primary-500 text-white shadow-sm' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                    Short Stay
                </a>
                <a href="{{ request()->fullUrlWithQuery(['purpose' => 'Land']) }}" 
                   class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all {{ $currentPurpose === 'Land' ? 'bg-primary-500 text-white shadow-sm' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                    Land
                </a>
                <a href="{{ request()->fullUrlWithQuery(['purpose' => 'Development']) }}" 
                   class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all {{ $currentPurpose === 'Development' ? 'bg-primary-500 text-white shadow-sm' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                    New Projects
                </a>
            </div>

            {{-- Multi-Criteria Form Filters --}}
            <form action="{{ route('properties.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-12 gap-3">
                <input type="hidden" name="purpose" value="{{ $currentPurpose }}">

                {{-- Keyword / Location Search --}}
                <div class="md:col-span-4 relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search title, neighborhood..." class="form-input w-full pl-9 text-xs">
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </div>

                {{-- Bedrooms --}}
                <div class="md:col-span-2">
                    <select name="bedrooms" onchange="this.form.submit()" class="form-input w-full text-xs font-medium">
                        <option value="any">Any Bedrooms</option>
                        <option value="1" {{ request('bedrooms') == '1' ? 'selected' : '' }}>1+ Bed</option>
                        <option value="2" {{ request('bedrooms') == '2' ? 'selected' : '' }}>2+ Beds</option>
                        <option value="3" {{ request('bedrooms') == '3' ? 'selected' : '' }}>3+ Beds</option>
                        <option value="4" {{ request('bedrooms') == '4' ? 'selected' : '' }}>4+ Beds</option>
                    </select>
                </div>

                {{-- Min Price --}}
                <div class="md:col-span-2">
                    <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="Min Price" class="form-input w-full text-xs">
                </div>

                {{-- Max Price --}}
                <div class="md:col-span-2">
                    <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="Max Price" class="form-input w-full text-xs">
                </div>

                {{-- Sort --}}
                <div class="md:col-span-2 flex items-center gap-2">
                    <select name="sort" onchange="this.form.submit()" class="form-input w-full text-xs font-medium">
                        <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>Newest</option>
                        <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>Price: Low-High</option>
                        <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Price: High-Low</option>
                    </select>

                    <button type="submit" class="btn btn-primary btn-sm text-xs px-3" title="Apply Filters">
                        Go
                    </button>

                    @if(request()->hasAny(['search', 'min_price', 'max_price', 'bedrooms', 'sort']) || $currentPurpose !== 'all')
                        <a href="{{ route('properties.index') }}" class="p-2 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200" title="Reset Filters">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Property Discovery Grid --}}
        @if($listings->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($listings as $listing)
                    <x-property-card :listing="$listing" />
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="pt-6">
                {{ $listings->links() }}
            </div>
        @else
            {{-- Empty Discovery State --}}
            <div class="bg-white dark:bg-[#1E293B] border border-gray-200 dark:border-gray-800 rounded-3xl p-16 text-center shadow-sm">
                <div class="w-16 h-16 rounded-full bg-orange-100 dark:bg-orange-950/40 text-primary-600 dark:text-primary-400 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </div>
                <h3 class="text-lg font-display font-bold text-gray-900 dark:text-white">No Properties Found</h3>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 max-w-md mx-auto mt-1 mb-6">
                    No verified listings currently match your selected filters. Try broadening your price range or clearing keyword search.
                </p>
                <a href="{{ route('properties.index') }}" class="btn btn-primary text-xs font-bold">
                    Reset All Filters
                </a>
            </div>
        @endif
    </div>
</x-layouts.app>
