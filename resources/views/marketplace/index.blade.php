<x-layouts.app>
    <x-slot name="title">{{ __('Properties for Sale & Rent in Ethiopia | Marketplace Discovery') }}</x-slot>
    <x-slot name="description">{{ __('Browse verified properties for sale, rent, and short stay across Addis Ababa, Hawassa, Bahir Dar, and regional Ethiopia.') }}</x-slot>

    <div class="py-12 page-container min-h-screen space-y-8 pt-28">
        {{-- Discovery Hero / Title Bar --}}
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-slate-200 dark:border-slate-800 pb-6">
            <div>
                <nav class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 mb-2 font-medium">
                    <a href="/" class="hover:text-orange-600 dark:hover:text-orange-400 transition-colors">{{ __('Home') }}</a>
                    <span>/</span>
                    <span class="text-slate-900 dark:text-white font-bold">{{ __('Marketplace') }}</span>
                </nav>
                <h1 class="text-3xl sm:text-4xl font-display font-extrabold text-slate-900 dark:text-white tracking-tight">
                    {{ __('Ethiopian Property Discovery') }}
                </h1>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-1 flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 font-bold border border-emerald-200 dark:border-emerald-800/40 text-[11px]">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        {{ $listings->total() }} {{ __('Verified Listings') }}
                    </span>
                    <span>{{ __('across Addis Ababa & regional economic zones') }}</span>
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard.properties.map') }}" class="btn btn-secondary inline-flex items-center gap-2 px-4 py-2.5 rounded-full text-xs font-bold bg-white dark:bg-[#1E293B] border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white hover:border-orange-500 transition-colors shadow-sm">
                    <svg class="w-4 h-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" /></svg>
                    <span>{{ __('GIS Map Explorer') }}</span>
                </a>
            </div>
        </div>

        {{-- Filter Console --}}
        <div class="card bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-3xl p-5 sm:p-6 shadow-sm space-y-5">
            {{-- Mode Filter Tabs with Purposeful Color Coding --}}
            @php
                $currentPurpose = request('purpose', 'all');
            @endphp
            <div class="flex items-center gap-1.5 overflow-x-auto pb-2 border-b border-[#EBDBC9]/80 dark:border-[#273244]">
                {{-- All Listings --}}
                <a href="{{ request()->fullUrlWithQuery(['purpose' => 'all']) }}" 
                   class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all {{ $currentPurpose === 'all' ? 'bg-[#111827] text-white dark:bg-white dark:text-black shadow-sm' : 'text-[#4B5563] dark:text-[#CBD5E1] hover:text-[#111827] dark:hover:text-white hover:bg-orange-50 dark:hover:bg-white/5' }}">
                    {{ __('All Listings') }}
                </a>
                {{-- For Sale (Orange) --}}
                <a href="{{ request()->fullUrlWithQuery(['purpose' => 'For Sale']) }}" 
                   class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all {{ $currentPurpose === 'For Sale' ? 'bg-orange-500 text-white shadow-md shadow-orange-500/20' : 'text-[#4B5563] dark:text-[#CBD5E1] hover:text-orange-600 dark:hover:text-white hover:bg-orange-50 dark:hover:bg-white/5' }}">
                    {{ __('For Sale') }}
                </a>
                {{-- For Rent (Emerald) --}}
                <a href="{{ request()->fullUrlWithQuery(['purpose' => 'For Rent']) }}" 
                   class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all {{ $currentPurpose === 'For Rent' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20' : 'text-[#4B5563] dark:text-[#CBD5E1] hover:text-emerald-600 dark:hover:text-white hover:bg-emerald-50 dark:hover:bg-white/5' }}">
                    {{ __('For Rent') }}
                </a>
                {{-- Short Stay (Sky) --}}
                <a href="{{ request()->fullUrlWithQuery(['purpose' => 'Short Stay']) }}" 
                   class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all {{ $currentPurpose === 'Short Stay' ? 'bg-sky-600 text-white shadow-md shadow-sky-600/20' : 'text-[#4B5563] dark:text-[#CBD5E1] hover:text-sky-600 dark:hover:text-white hover:bg-sky-50 dark:hover:bg-white/5' }}">
                    {{ __('Short Stay') }}
                </a>
                {{-- Land (Amber) --}}
                <a href="{{ request()->fullUrlWithQuery(['purpose' => 'Land']) }}" 
                   class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all {{ $currentPurpose === 'Land' ? 'bg-amber-600 text-white shadow-md shadow-amber-600/20' : 'text-[#4B5563] dark:text-[#CBD5E1] hover:text-amber-600 dark:hover:text-white hover:bg-amber-50 dark:hover:bg-white/5' }}">
                    {{ __('Land & Plots') }}
                </a>
                {{-- Development (Purple) --}}
                <a href="{{ request()->fullUrlWithQuery(['purpose' => 'Development']) }}" 
                   class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all {{ $currentPurpose === 'Development' ? 'bg-purple-600 text-white shadow-md shadow-purple-600/20' : 'text-[#4B5563] dark:text-[#CBD5E1] hover:text-purple-600 dark:hover:text-white hover:bg-purple-50 dark:hover:bg-white/5' }}">
                    {{ __('New Projects') }}
                </a>
            </div>

            {{-- Multi-Criteria Form Filters --}}
            <form action="{{ route('properties.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-12 gap-3">
                <input type="hidden" name="purpose" value="{{ $currentPurpose }}">

                {{-- Keyword / Location Search --}}
                <div class="md:col-span-4 relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-orange-500">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search neighborhood, title, or code...') }}" class="w-full pl-10 pr-3 py-2.5 text-xs font-bold rounded-xl bg-white dark:bg-[#1E293B] border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white placeholder:text-slate-400 focus:border-orange-500 outline-none transition-colors">
                </div>

                {{-- Bedrooms --}}
                <div class="md:col-span-2">
                    <select name="bedrooms" onchange="this.form.submit()" class="w-full px-3 py-2.5 text-xs font-bold rounded-xl bg-white dark:bg-[#1E293B] border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white focus:border-orange-500 outline-none transition-colors cursor-pointer">
                        <option value="any" class="text-black">{{ __('Any Bedrooms') }}</option>
                        <option value="1" {{ request('bedrooms') == '1' ? 'selected' : '' }} class="text-black">{{ __('1+ Bedroom') }}</option>
                        <option value="2" {{ request('bedrooms') == '2' ? 'selected' : '' }} class="text-black">{{ __('2+ Bedrooms') }}</option>
                        <option value="3" {{ request('bedrooms') == '3' ? 'selected' : '' }} class="text-black">{{ __('3+ Bedrooms') }}</option>
                        <option value="4" {{ request('bedrooms') == '4' ? 'selected' : '' }} class="text-black">{{ __('4+ Bedrooms') }}</option>
                    </select>
                </div>

                {{-- Min Price --}}
                <div class="md:col-span-2">
                    <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="{{ __('Min Price (ETB)') }}" class="w-full px-3 py-2.5 text-xs font-bold rounded-xl bg-white dark:bg-[#1E293B] border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white placeholder:text-slate-400 focus:border-orange-500 outline-none transition-colors">
                </div>

                {{-- Max Price --}}
                <div class="md:col-span-2">
                    <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="{{ __('Max Price (ETB)') }}" class="w-full px-3 py-2.5 text-xs font-bold rounded-xl bg-white dark:bg-[#1E293B] border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white placeholder:text-slate-400 focus:border-orange-500 outline-none transition-colors">
                </div>

                {{-- Sort & Submit --}}
                <div class="md:col-span-2 flex items-center gap-2">
                    <select name="sort" onchange="this.form.submit()" class="w-full px-3 py-2.5 text-xs font-bold rounded-xl bg-white dark:bg-[#1E293B] border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white focus:border-orange-500 outline-none transition-colors cursor-pointer">
                        <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }} class="text-black">{{ __('Newest') }}</option>
                        <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }} class="text-black">{{ __('Price: Low') }}</option>
                        <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }} class="text-black">{{ __('Price: High') }}</option>
                    </select>

                    <button type="submit" class="btn btn-primary px-3 py-2.5 rounded-xl bg-gradient-to-r from-orange-500 to-amber-600 hover:from-orange-600 hover:to-amber-700 text-white font-bold text-xs shadow-md shadow-orange-500/20 transition-all flex items-center justify-center cursor-pointer" title="{{ __('Apply Filters') }}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                    </button>

                    @if(request()->hasAny(['search', 'min_price', 'max_price', 'bedrooms', 'sort']) || $currentPurpose !== 'all')
                        <a href="{{ route('properties.index') }}" class="p-2 text-slate-500 hover:text-rose-500 transition-colors" title="{{ __('Reset Filters') }}">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
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
            <div class="pt-6 flex justify-center">
                {{ $listings->links() }}
            </div>
        @else
            {{-- Empty Discovery State --}}
            <div class="card bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-3xl p-16 text-center shadow-sm max-w-2xl mx-auto">
                <div class="w-16 h-16 rounded-2xl bg-orange-100 dark:bg-orange-950/40 text-orange-600 dark:text-orange-400 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </div>
                <h3 class="text-xl font-display font-black text-slate-900 dark:text-white">{{ __('No Properties Found') }}</h3>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 max-w-md mx-auto mt-2 mb-6">
                    {{ __('No verified listings currently match your selected filters. Try broadening your price range or clearing keyword search.') }}
                </p>
                <a href="{{ route('properties.index') }}" class="btn btn-primary inline-flex items-center gap-2 px-6 py-3 rounded-full text-xs font-bold bg-gradient-to-r from-orange-500 to-amber-600 hover:from-orange-600 hover:to-amber-700 text-white shadow-md shadow-orange-500/25 transition-all">
                    <span>{{ __('Reset All Filters') }}</span>
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
                </a>
            </div>
        @endif
    </div>
</x-layouts.app>
