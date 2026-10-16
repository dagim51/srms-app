<x-layouts.app>
    <x-slot name="title">{{ __('Property Search Engine | Ethiopian Real Estate Marketplace') }}</x-slot>
    <x-slot name="description">{{ __('Search verified properties for sale, rent, and short stay in Addis Ababa and across Ethiopia with advanced multi-criteria filters and interactive map.') }}</x-slot>

    @push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <style>
        .custom-price-pin {
            background: linear-gradient(135deg, #F97316 0%, #EA580C 100%);
            color: #FFFFFF;
            font-family: var(--font-sans);
            font-size: 11px;
            font-weight: 800;
            padding: 4px 10px;
            border-radius: 9999px;
            box-shadow: 0 4px 14px rgba(249, 115, 22, 0.4), 0 1px 3px rgba(0,0,0,0.2);
            white-space: nowrap;
            border: 2px solid #FFFFFF;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            text-align: center;
            cursor: pointer;
        }
        .custom-price-pin:hover, .custom-price-pin.active-pin {
            background: linear-gradient(135deg, #EA580C 0%, #C2410C 100%);
            transform: scale(1.15) translateY(-2px);
            box-shadow: 0 8px 22px rgba(234, 88, 12, 0.6);
            z-index: 1000 !important;
        }
        .leaflet-popup-content-wrapper {
            border-radius: 1.25rem;
            padding: 0;
            overflow: hidden;
            box-shadow: 0 20px 30px -5px rgba(0, 0, 0, 0.25), 0 8px 10px -6px rgba(0, 0, 0, 0.2);
            border: 1px solid #EBDBC9;
        }
        .leaflet-popup-content {
            margin: 0;
            line-height: 1.4;
        }
        .dark .leaflet-popup-content-wrapper {
            background: #111827;
            color: #F9FAFB;
            border-color: #273244;
        }
    </style>
    @endpush

    <div class="min-h-screen pt-28 pb-16 bg-[#FFF5EB] dark:bg-[#0B0F19]"
         x-data="searchPageController()">

        <div class="page-container space-y-6">

            {{-- SEARCH ENGINE TOP BAR --}}
            <div class="bg-white dark:bg-[#111827] border border-[#EBDBC9] dark:border-[#273244] rounded-3xl p-5 sm:p-6 shadow-sm space-y-4">
                
                {{-- Breadcrumbs & Results Eyebrow --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-[#EBDBC9]/80 dark:border-[#273244] pb-3">
                    <nav class="flex items-center gap-2 text-xs text-[#6B7280] dark:text-[#94A3B8] font-medium">
                        <a href="/" class="hover:text-orange-600 dark:hover:text-orange-400 transition-colors">{{ __('Home') }}</a>
                        <span>/</span>
                        <a href="/properties" class="hover:text-orange-600 dark:hover:text-orange-400 transition-colors">{{ __('Marketplace') }}</a>
                        <span>/</span>
                        <span class="text-[#111827] dark:text-white font-bold">{{ __('Search Engine') }}</span>
                    </nav>

                    <div class="flex items-center gap-2 text-xs">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 font-bold border border-emerald-200 dark:border-emerald-800/40">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            {{ $listings->total() }} {{ __('Verified Properties') }}
                        </span>
                    </div>
                </div>

                {{-- Primary Keyword Search Input --}}
                <form action="{{ route('search') }}" method="GET" class="space-y-3" id="primary-search-form">
                    {{-- Preserve existing filters in hidden inputs --}}
                    @if($purpose && $purpose !== 'all') <input type="hidden" name="purpose" value="{{ $purpose }}"> @endif
                    @if($propertyType && $propertyType !== 'all') <input type="hidden" name="property_type" value="{{ $propertyType }}"> @endif
                    @if($minPrice) <input type="hidden" name="min_price" value="{{ $minPrice }}"> @endif
                    @if($maxPrice) <input type="hidden" name="max_price" value="{{ $maxPrice }}"> @endif
                    @if($bedrooms && $bedrooms !== 'any') <input type="hidden" name="bedrooms" value="{{ $bedrooms }}"> @endif
                    @if($bathrooms && $bathrooms !== 'any') <input type="hidden" name="bathrooms" value="{{ $bathrooms }}"> @endif
                    @if($minArea) <input type="hidden" name="min_area" value="{{ $minArea }}"> @endif
                    @if($maxArea) <input type="hidden" name="max_area" value="{{ $maxArea }}"> @endif
                    @if($parking) <input type="hidden" name="parking" value="1"> @endif
                    @if($verified) <input type="hidden" name="verified" value="1"> @endif
                    @if($sort) <input type="hidden" name="sort" value="{{ $sort }}"> @endif
                    <input type="hidden" name="view" :value="viewMode">
                    @foreach((array)$amenities as $am)
                        <input type="hidden" name="amenities[]" value="{{ $am }}">
                    @endforeach

                    <div class="flex flex-col sm:flex-row items-stretch gap-2">
                        <div class="relative flex-1">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-orange-500">
                                <svg class="w-5 h-5 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                            </div>
                            <input
                                type="text"
                                name="q"
                                id="search-keyword-input"
                                value="{{ $q }}"
                                placeholder="{{ __('Search by neighborhood (Bole, Kazanchis, Hawassa...), title, or property code...') }}"
                                class="w-full pl-11 pr-10 py-3 text-xs sm:text-sm font-bold rounded-2xl bg-white dark:bg-[#1E293B] border border-[#EBDBC9] dark:border-[#273244] text-[#111827] dark:text-white placeholder:text-[#9CA3AF] focus:border-orange-500 outline-none transition-all shadow-sm"
                            />
                            
                            @if(!empty($q))
                                <a href="{{ request()->fullUrlWithQuery(['q' => null]) }}" 
                                   class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-[#9CA3AF] hover:text-rose-500 transition-colors"
                                   title="{{ __('Clear search keyword') }}">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                </a>
                            @endif
                        </div>

                        <div class="flex items-center gap-2">
                            {{-- Search Button --}}
                            <button type="submit" class="px-6 py-3 rounded-2xl text-xs sm:text-sm font-extrabold flex items-center gap-2 bg-gradient-to-r from-orange-500 to-amber-600 hover:from-orange-600 hover:to-amber-700 text-white shadow-md shadow-orange-500/25 transition-all duration-200 active:scale-95 cursor-pointer" id="search-submit-btn">
                                <span>{{ __('Search') }}</span>
                            </button>

                            {{-- Filter Drawer Toggle Button --}}
                            <button type="button" 
                                    @click="drawerOpen = true"
                                    class="px-4 py-3 rounded-2xl text-xs sm:text-sm font-bold flex items-center gap-2 bg-white dark:bg-[#1E293B] border border-[#EBDBC9] dark:border-[#273244] text-[#111827] dark:text-white hover:border-orange-500 transition-all shadow-sm cursor-pointer"
                                    id="filter-drawer-toggle">
                                <svg class="w-4 h-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75" /></svg>
                                <span>{{ __('Filters') }}</span>
                                @if($activeCount > 0)
                                    <span class="w-5 h-5 rounded-full bg-orange-500 text-white text-[11px] font-black flex items-center justify-center">
                                        {{ $activeCount }}
                                    </span>
                                @endif
                            </button>
                        </div>
                    </div>

                    {{-- Quick Neighborhood Suggestions --}}
                    <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs pt-1">
                        <span class="text-[#111827] dark:text-white font-bold whitespace-nowrap">{{ __('Popular Areas:') }}</span>
                        @foreach(['Bole', 'Kazanchis', 'Old Airport', 'Ayat', 'Hawassa', 'Bishoftu'] as $city)
                            <a href="{{ request()->fullUrlWithQuery(['q' => $city]) }}" 
                               class="px-3 py-1 rounded-full bg-[#FFF5EB] dark:bg-[#1E293B] border border-[#EBDBC9] dark:border-[#273244] hover:border-orange-500 text-[#111827] dark:text-white font-bold transition-all whitespace-nowrap hover:scale-105">
                                {{ __($city) }}
                            </a>
                        @endforeach
                    </div>
                </form>

                {{-- Mode Switcher Tabs with Purposeful Colors --}}
                <div class="flex items-center gap-1.5 overflow-x-auto pb-1 pt-2 border-t border-[#EBDBC9]/80 dark:border-[#273244]">
                    @php
                        $tabPurposes = [
                            'all' => ['label' => __('All Properties'), 'color' => 'bg-[#111827] text-white dark:bg-white dark:text-black'],
                            'For Sale' => ['label' => __('For Sale'), 'color' => 'bg-orange-500 text-white shadow-sm shadow-orange-500/20'],
                            'For Rent' => ['label' => __('For Rent'), 'color' => 'bg-emerald-600 text-white shadow-sm shadow-emerald-600/20'],
                            'Short Stay' => ['label' => __('Short Stay'), 'color' => 'bg-sky-600 text-white shadow-sm shadow-sky-600/20'],
                            'Land' => ['label' => __('Land & Plots'), 'color' => 'bg-amber-600 text-white shadow-sm shadow-amber-600/20'],
                            'Development' => ['label' => __('New Projects'), 'color' => 'bg-purple-600 text-white shadow-sm shadow-purple-600/20'],
                        ];
                    @endphp
                    @foreach($tabPurposes as $pVal => $pInfo)
                        @php
                            $isActive = ($purpose === $pVal || (!$purpose && $pVal === 'all'));
                        @endphp
                        <a href="{{ request()->fullUrlWithQuery(['purpose' => $pVal === 'all' ? null : $pVal]) }}" 
                           class="px-3.5 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition-all {{ $isActive ? $pInfo['color'] : 'text-[#4B5563] dark:text-[#CBD5E1] hover:bg-orange-50 dark:hover:bg-white/5' }}">
                            {{ $pInfo['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- APPLIED FILTER CHIPS & CONTROLS ROW --}}
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                
                {{-- Active Filter Chips --}}
                <div class="flex-1 flex flex-wrap items-center gap-2">
                    <span class="text-xs font-bold text-[#111827] dark:text-white">
                        {{ $listings->total() }} {{ __('results') }}
                    </span>

                    @if(!empty($appliedFilters))
                        <div class="h-4 w-px bg-[#EBDBC9] dark:bg-[#273244] hidden sm:block"></div>

                        @foreach($appliedFilters as $chip)
                            @php
                                $removalUrl = request()->fullUrlWithQuery(
                                    isset($chip['remove_params']) 
                                        ? array_fill_keys($chip['remove_params'], null)
                                        : (isset($chip['remove_amenity'])
                                            ? ['amenities' => array_values(array_diff((array)$amenities, [$chip['remove_amenity']]))]
                                            : [$chip['remove_param'] => null])
                                );
                            @endphp
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-white dark:bg-[#1E293B] border border-[#EBDBC9] dark:border-[#273244] text-[#111827] dark:text-white shadow-sm">
                                <span>{{ $chip['label'] }}</span>
                                <a href="{{ $removalUrl }}" class="text-[#9CA3AF] hover:text-rose-500 transition-colors p-0.5" title="{{ __('Remove filter') }}" aria-label="{{ __('Remove filter') }}">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                </a>
                            </span>
                        @endforeach

                        {{-- Clear All Action --}}
                        <a href="{{ route('search', ['view' => $viewMode]) }}" class="text-xs font-bold text-orange-600 dark:text-orange-400 hover:underline flex items-center gap-1 ml-1" id="clear-all-filters-link">
                            <span>{{ __('Clear all') }}</span>
                        </a>
                    @endif
                </div>

                {{-- View Layout Mode Switchers & Sorting Menu --}}
                <div class="flex items-center gap-3 self-end lg:self-auto">
                    {{-- Sort Dropdown --}}
                    <div class="flex items-center gap-2 text-xs">
                        <label for="search-sort-select" class="text-[#111827] dark:text-white font-bold hidden sm:inline">{{ __('Sort by:') }}</label>
                        <select id="search-sort-select" 
                                onchange="window.location.href = this.value"
                                class="py-1.5 px-3 text-xs rounded-xl bg-white dark:bg-[#1E293B] border border-[#EBDBC9] dark:border-[#273244] text-[#111827] dark:text-white font-bold cursor-pointer outline-none">
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'newest']) }}" {{ ($sort ?? 'newest') === 'newest' ? 'selected' : '' }} class="text-black">{{ __('Newest Listed') }}</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'price_asc']) }}" {{ ($sort ?? '') === 'price_asc' ? 'selected' : '' }} class="text-black">{{ __('Price: Low to High') }}</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'price_desc']) }}" {{ ($sort ?? '') === 'price_desc' ? 'selected' : '' }} class="text-black">{{ __('Price: High to Low') }}</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'area_desc']) }}" {{ ($sort ?? '') === 'area_desc' ? 'selected' : '' }} class="text-black">{{ __('Largest Area (m²)') }}</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'bedrooms_desc']) }}" {{ ($sort ?? '') === 'bedrooms_desc' ? 'selected' : '' }} class="text-black">{{ __('Most Bedrooms') }}</option>
                        </select>
                    </div>

                    {{-- Layout View Toggles --}}
                    <div class="flex items-center bg-white dark:bg-[#1E293B] p-1 rounded-xl border border-[#EBDBC9] dark:border-[#273244] shadow-sm">
                        {{-- Grid View Button --}}
                        <button type="button" 
                                @click="setView('grid')"
                                :class="viewMode === 'grid' ? 'bg-orange-500 text-white shadow-sm' : 'text-[#6B7280] hover:text-[#111827] dark:text-[#94A3B8] dark:hover:text-white'"
                                class="p-1.5 rounded-lg transition-all cursor-pointer"
                                title="{{ __('Grid View') }}"
                                id="view-mode-grid">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" /></svg>
                        </button>

                        {{-- List View Button --}}
                        <button type="button" 
                                @click="setView('list')"
                                :class="viewMode === 'list' ? 'bg-orange-500 text-white shadow-sm' : 'text-[#6B7280] hover:text-[#111827] dark:text-[#94A3B8] dark:hover:text-white'"
                                class="p-1.5 rounded-lg transition-all cursor-pointer"
                                title="{{ __('List View') }}"
                                id="view-mode-list">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
                        </button>

                        {{-- Split Map View Button --}}
                        <button type="button" 
                                @click="setView('map')"
                                :class="viewMode === 'map' ? 'bg-orange-500 text-white shadow-sm' : 'text-[#6B7280] hover:text-[#111827] dark:text-[#94A3B8] dark:hover:text-white'"
                                class="p-1.5 rounded-lg transition-all cursor-pointer"
                                title="{{ __('Split Map View') }}"
                                id="view-mode-map">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" /></svg>
                        </button>
                    </div>
                </div>
            </div>

            {{-- SEARCH RESULTS PRESENTATION --}}
            @if($listings->isEmpty())
                {{-- NO RESULTS STATE --}}
                <div class="bg-white dark:bg-[#111827] border border-[#EBDBC9] dark:border-[#273244] rounded-3xl p-16 text-center shadow-sm max-w-2xl mx-auto">
                    <div class="w-16 h-16 rounded-2xl bg-orange-100 dark:bg-orange-950/40 text-orange-600 dark:text-orange-400 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </div>
                    <h3 class="text-xl font-display font-black text-[#000000] dark:text-white">{{ __('No Matching Properties Found') }}</h3>
                    <p class="text-xs sm:text-sm text-[#4B5563] dark:text-[#CBD5E1] max-w-md mx-auto mt-2 mb-6">
                        {{ __('We could not find any verified listings matching your exact criteria. Try widening your price range, relaxing bedrooms, or clearing filters.') }}
                    </p>
                    <div class="flex items-center justify-center gap-3">
                        <a href="{{ route('search') }}" class="px-5 py-2.5 rounded-full text-xs font-bold bg-gradient-to-r from-orange-500 to-amber-600 text-white shadow-md shadow-orange-500/20 transition-all">
                            {{ __('Reset All Filters') }}
                        </a>
                        <a href="{{ route('properties.index') }}" class="px-5 py-2.5 rounded-full text-xs font-bold bg-[#FFF5EB] dark:bg-[#1E293B] border border-[#EBDBC9] dark:border-[#273244] text-[#111827] dark:text-white hover:border-orange-500 transition-colors">
                            {{ __('Browse All Marketplace') }}
                        </a>
                    </div>
                </div>
            @else
                {{-- RESULTS CONTAINER --}}
                
                {{-- 1. GRID VIEW --}}
                <div x-show="viewMode === 'grid'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($listings as $item)
                        <div class="transition-transform duration-200">
                            <x-property-card :listing="$item" />
                        </div>
                    @endforeach
                </div>

                {{-- 2. LIST VIEW --}}
                <div x-show="viewMode === 'list'" class="space-y-4">
                    @foreach($listings as $item)
                        <x-property-list-card :listing="$item" />
                    @endforeach
                </div>

                {{-- 3. SPLIT MAP VIEW --}}
                <div x-show="viewMode === 'map'" class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                    {{-- Left column: Listings List --}}
                    <div class="lg:col-span-6 space-y-4 max-h-[calc(100vh-14rem)] overflow-y-auto pr-2 custom-scrollbar">
                        @foreach($listings as $item)
                            <div @mouseenter="highlightMarker('{{ $item->id }}')"
                                 @mouseleave="unhighlightMarker('{{ $item->id }}')"
                                 @click="focusMarker('{{ $item->id }}', {{ $item->property->latitude ?? 'null' }}, {{ $item->property->longitude ?? 'null' }})"
                                 class="cursor-pointer transition-all duration-150"
                                 :class="hoveredListingId === '{{ $item->id }}' ? 'ring-2 ring-orange-500 rounded-2xl' : ''">
                                <x-property-list-card :listing="$item" />
                            </div>
                        @endforeach

                        {{-- Pagination inside left list in map mode --}}
                        <div class="pt-4 flex justify-center">
                            {{ $listings->links() }}
                        </div>
                    </div>

                    {{-- Right column: Interactive Leaflet Map --}}
                    <div class="lg:col-span-6 sticky top-28 bg-white dark:bg-[#111827] rounded-3xl overflow-hidden border border-[#EBDBC9] dark:border-[#273244] shadow-md h-full min-h-[26rem] lg:h-[calc(100vh-14rem)]">
                        <div id="search-map-container" class="w-full h-full z-10"></div>
                    </div>
                </div>

                {{-- Standard Pagination for Grid and List modes --}}
                <div x-show="viewMode !== 'map'" class="mt-8 pt-6 border-t border-[#EBDBC9] dark:border-[#273244] flex justify-center">
                    {{ $listings->links() }}
                </div>
            @endif

        </div>

        {{-- FILTER DRAWER / SLIDE-OVER MODAL --}}
        <div x-show="drawerOpen"
             class="fixed inset-0 z-50 overflow-hidden" 
             aria-labelledby="filter-drawer-title" 
             role="dialog" 
             aria-modal="true"
             x-cloak>
            
            {{-- Backdrop Scrim --}}
            <div x-show="drawerOpen"
                 x-transition:enter="ease-in-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in-out duration-300"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="drawerOpen = false"
                 class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity"></div>

            <div class="fixed inset-y-0 right-0 pl-10 max-w-full flex">
                <div x-show="drawerOpen"
                     x-transition:enter="transform transition ease-in-out duration-300 sm:duration-500"
                     x-transition:enter-start="translate-x-full"
                     x-transition:enter-end="translate-x-0"
                     x-transition:leave="transform transition ease-in-out duration-300 sm:duration-500"
                     x-transition:leave-start="translate-x-0"
                     x-transition:leave-end="translate-x-full"
                     class="w-screen max-w-md bg-white dark:bg-[#111827] shadow-2xl flex flex-col justify-between">
                    
                    {{-- Drawer Header --}}
                    <div class="px-6 py-5 border-b border-[#EBDBC9] dark:border-[#273244] flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-orange-100 dark:bg-orange-950/60 text-orange-600 dark:text-orange-400 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75" /></svg>
                            </div>
                            <h2 id="filter-drawer-title" class="font-display font-extrabold text-lg text-[#111827] dark:text-white">
                                {{ __('Filter Properties') }}
                            </h2>
                        </div>

                        <div class="flex items-center gap-3">
                            <a href="{{ route('search', ['view' => $viewMode]) }}" class="text-xs font-bold text-orange-600 dark:text-orange-400 hover:underline">
                                {{ __('Reset All') }}
                            </a>
                            <button type="button" 
                                    class="p-1.5 rounded-lg text-[#6B7280] hover:text-[#111827] dark:text-[#94A3B8] dark:hover:text-white"
                                    aria-label="{{ __('Close filters') }}"
                                    @click="drawerOpen = false">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                    </div>

                    {{-- Drawer Body: Form Inputs --}}
                    <form action="{{ route('search') }}" method="GET" id="drawer-filter-form" class="flex-1 overflow-y-auto px-6 py-6 space-y-6">
                        @if(!empty($q)) <input type="hidden" name="q" value="{{ $q }}"> @endif
                        <input type="hidden" name="view" :value="viewMode">
                        @if($sort) <input type="hidden" name="sort" value="{{ $sort }}"> @endif

                        {{-- 1. Purpose / Listing Type --}}
                        <div class="space-y-2">
                            <label class="block font-extrabold text-xs uppercase tracking-wider text-[#111827] dark:text-white">{{ __('Listing Purpose') }}</label>
                            <div class="grid grid-cols-2 gap-2">
                                @php
                                    $drawerPurposes = [
                                        'all' => __('Any Purpose'),
                                        'For Sale' => __('For Sale'),
                                        'For Rent' => __('For Rent'),
                                        'Short Stay' => __('Short Stay'),
                                        'Land' => __('Land & Plots'),
                                        'Development' => __('New Projects'),
                                    ];
                                @endphp
                                @foreach($drawerPurposes as $pValue => $pText)
                                    <label class="flex items-center gap-2 p-2.5 rounded-xl border border-[#EBDBC9] dark:border-[#273244] cursor-pointer hover:bg-orange-50/50 dark:hover:bg-white/5 transition-colors has-checked:border-orange-500 has-checked:bg-orange-50 dark:has-checked:bg-orange-950/20">
                                        <input type="radio" name="purpose" value="{{ $pValue }}" class="text-orange-500 focus:ring-orange-500" {{ ($purpose === $pValue || (!$purpose && $pValue === 'all')) ? 'checked' : '' }}>
                                        <span class="text-xs font-bold text-[#111827] dark:text-white">{{ $pText }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- 2. Property Type --}}
                        <div class="space-y-2">
                            <label class="block font-extrabold text-xs uppercase tracking-wider text-[#111827] dark:text-white">{{ __('Property Category') }}</label>
                            <select name="property_type" class="w-full px-3 py-2.5 text-xs font-bold rounded-xl bg-white dark:bg-[#1E293B] border border-[#EBDBC9] dark:border-[#273244] text-[#111827] dark:text-white outline-none focus:border-orange-500 cursor-pointer">
                                <option value="all" class="text-black">{{ __('All Property Types') }}</option>
                                @foreach($propertyTypes as $pType)
                                    <option value="{{ $pType->code }}" {{ $propertyType === $pType->code ? 'selected' : '' }} class="text-black">
                                        {{ app()->getLocale() === 'am' && !empty($pType->name_am) ? $pType->name_am : __($pType->name_en) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- 3. Price Range (Min & Max in ETB) --}}
                        <div class="space-y-2">
                            <label class="block font-extrabold text-xs uppercase tracking-wider text-[#111827] dark:text-white">{{ __('Price Range (ETB)') }}</label>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <input type="number" name="min_price" value="{{ $minPrice }}" placeholder="{{ __('Min Price') }}" class="w-full px-3 py-2.5 text-xs font-bold rounded-xl bg-white dark:bg-[#1E293B] border border-[#EBDBC9] dark:border-[#273244] text-[#111827] dark:text-white placeholder:text-[#9CA3AF] focus:border-orange-500 outline-none" min="0" step="50000">
                                </div>
                                <div>
                                    <input type="number" name="max_price" value="{{ $maxPrice }}" placeholder="{{ __('Max Price') }}" class="w-full px-3 py-2.5 text-xs font-bold rounded-xl bg-white dark:bg-[#1E293B] border border-[#EBDBC9] dark:border-[#273244] text-[#111827] dark:text-white placeholder:text-[#9CA3AF] focus:border-orange-500 outline-none" min="0" step="50000">
                                </div>
                            </div>
                            {{-- Quick price presets --}}
                            <div class="flex flex-wrap gap-1.5 pt-1">
                                <button type="button" @click="$el.closest('form').querySelector('[name=min_price]').value = ''; $el.closest('form').querySelector('[name=max_price]').value = '5000000'" class="px-2.5 py-1 rounded-lg text-xs font-bold bg-[#FFF5EB] dark:bg-[#1E293B] border border-[#EBDBC9] dark:border-[#273244] text-[#111827] dark:text-white hover:border-orange-500 cursor-pointer">&lt; 5M ETB</button>
                                <button type="button" @click="$el.closest('form').querySelector('[name=min_price]').value = '5000000'; $el.closest('form').querySelector('[name=max_price]').value = '15000000'" class="px-2.5 py-1 rounded-lg text-xs font-bold bg-[#FFF5EB] dark:bg-[#1E293B] border border-[#EBDBC9] dark:border-[#273244] text-[#111827] dark:text-white hover:border-orange-500 cursor-pointer">{{ __('5M - 15M') }}</button>
                                <button type="button" @click="$el.closest('form').querySelector('[name=min_price]').value = '15000000'; $el.closest('form').querySelector('[name=max_price]').value = '35000000'" class="px-2.5 py-1 rounded-lg text-xs font-bold bg-[#FFF5EB] dark:bg-[#1E293B] border border-[#EBDBC9] dark:border-[#273244] text-[#111827] dark:text-white hover:border-orange-500 cursor-pointer">{{ __('15M - 35M') }}</button>
                                <button type="button" @click="$el.closest('form').querySelector('[name=min_price]').value = '35000000'; $el.closest('form').querySelector('[name=max_price]').value = ''" class="px-2.5 py-1 rounded-lg text-xs font-bold bg-[#FFF5EB] dark:bg-[#1E293B] border border-[#EBDBC9] dark:border-[#273244] text-[#111827] dark:text-white hover:border-orange-500 cursor-pointer">{{ __('35M+ ETB') }}</button>
                            </div>
                        </div>

                        {{-- 4. Bedrooms --}}
                        <div class="space-y-2">
                            <label class="block font-extrabold text-xs uppercase tracking-wider text-[#111827] dark:text-white">{{ __('Bedrooms') }}</label>
                            <div class="flex items-center gap-1.5 overflow-x-auto pb-1">
                                @php
                                    $bedroomOptions = [
                                        'any' => __('Any'),
                                        '1' => '1+',
                                        '2' => '2+',
                                        '3' => '3+',
                                        '4' => '4+',
                                        '5' => '5+',
                                    ];
                                @endphp
                                @foreach($bedroomOptions as $bVal => $bText)
                                    <label class="flex-1 text-center py-2 px-2.5 rounded-xl border border-[#EBDBC9] dark:border-[#273244] cursor-pointer text-xs font-extrabold transition-all has-checked:bg-orange-500 has-checked:text-white has-checked:border-orange-500 hover:border-orange-500">
                                        <input type="radio" name="bedrooms" value="{{ $bVal }}" class="sr-only" {{ ($bedrooms == $bVal || (!$bedrooms && $bVal === 'any')) ? 'checked' : '' }}>
                                        <span>{{ $bText }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- 5. Bathrooms --}}
                        <div class="space-y-2">
                            <label class="block font-extrabold text-xs uppercase tracking-wider text-[#111827] dark:text-white">{{ __('Bathrooms') }}</label>
                            <div class="flex items-center gap-1.5 overflow-x-auto pb-1">
                                @php
                                    $bathroomOptions = [
                                        'any' => __('Any'),
                                        '1' => '1+',
                                        '2' => '2+',
                                        '3' => '3+',
                                        '4' => '4+',
                                    ];
                                @endphp
                                @foreach($bathroomOptions as $bVal => $bText)
                                    <label class="flex-1 text-center py-2 px-2.5 rounded-xl border border-[#EBDBC9] dark:border-[#273244] cursor-pointer text-xs font-extrabold transition-all has-checked:bg-orange-500 has-checked:text-white has-checked:border-orange-500 hover:border-orange-500">
                                        <input type="radio" name="bathrooms" value="{{ $bVal }}" class="sr-only" {{ ($bathrooms == $bVal || (!$bathrooms && $bVal === 'any')) ? 'checked' : '' }}>
                                        <span>{{ $bText }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- 6. Area Range (m²) --}}
                        <div class="space-y-2">
                            <label class="block font-extrabold text-xs uppercase tracking-wider text-[#111827] dark:text-white">{{ __('Building / Plot Area (m²)') }}</label>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <input type="number" name="min_area" value="{{ $minArea }}" placeholder="{{ __('Min Area (m²)') }}" class="w-full px-3 py-2.5 text-xs font-bold rounded-xl bg-white dark:bg-[#1E293B] border border-[#EBDBC9] dark:border-[#273244] text-[#111827] dark:text-white placeholder:text-[#9CA3AF] focus:border-orange-500 outline-none" min="0">
                                </div>
                                <div>
                                    <input type="number" name="max_area" value="{{ $maxArea }}" placeholder="{{ __('Max Area (m²)') }}" class="w-full px-3 py-2.5 text-xs font-bold rounded-xl bg-white dark:bg-[#1E293B] border border-[#EBDBC9] dark:border-[#273244] text-[#111827] dark:text-white placeholder:text-[#9CA3AF] focus:border-orange-500 outline-none" min="0">
                                </div>
                            </div>
                        </div>

                        {{-- 7. Parking & Verification Toggles --}}
                        <div class="space-y-3 pt-2 border-t border-[#EBDBC9] dark:border-[#273244]">
                            {{-- Parking --}}
                            <label class="flex items-center justify-between p-3 rounded-xl border border-[#EBDBC9] dark:border-[#273244] bg-white dark:bg-[#1E293B] cursor-pointer hover:border-orange-500 transition-colors">
                                <span class="text-xs font-bold text-[#111827] dark:text-white flex items-center gap-2">
                                    <svg class="w-4 h-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h4a3 3 0 013 3v0a3 3 0 01-3 3H8V7zm0 6v5" /></svg>
                                    {{ __('Dedicated Parking Slot') }}
                                </span>
                                <input type="checkbox" name="parking" value="1" {{ $parking ? 'checked' : '' }} class="rounded text-orange-500 focus:ring-orange-500 w-4 h-4">
                            </label>

                            {{-- Verification --}}
                            <label class="flex items-center justify-between p-3 rounded-xl border border-emerald-300 dark:border-emerald-800/40 bg-emerald-50/50 dark:bg-emerald-950/20 cursor-pointer hover:border-emerald-500 transition-colors">
                                <span class="text-xs font-bold text-emerald-800 dark:text-emerald-300 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.745 3.745 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" /></svg>
                                    {{ __('Verified Deeds Only') }}
                                </span>
                                <input type="checkbox" name="verified" value="1" {{ $verified ? 'checked' : '' }} class="rounded text-emerald-600 focus:ring-emerald-500 w-4 h-4">
                            </label>
                        </div>

                        {{-- 8. Amenities Checkboxes --}}
                        <div class="space-y-2 pt-2 border-t border-[#EBDBC9] dark:border-[#273244]">
                            <label class="block font-extrabold text-xs uppercase tracking-wider text-[#111827] dark:text-white">{{ __('Key Amenities') }}</label>
                            <div class="grid grid-cols-1 gap-2 max-h-48 overflow-y-auto pr-1 custom-scrollbar">
                                @foreach($availableAmenities as $amenity)
                                    <label class="flex items-center gap-2.5 p-2 rounded-lg hover:bg-orange-50/50 dark:hover:bg-white/5 cursor-pointer text-xs font-bold text-[#111827] dark:text-white">
                                        <input type="checkbox" 
                                               name="amenities[]" 
                                               value="{{ $amenity->code }}" 
                                               {{ in_array($amenity->code, (array)$amenities) ? 'checked' : '' }}
                                               class="rounded text-orange-500 focus:ring-orange-500 w-4 h-4">
                                        <span>{{ app()->getLocale() === 'am' && !empty($amenity->name_am) ? $amenity->name_am : __($amenity->name_en) }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                    </form>

                    {{-- Drawer Footer --}}
                    <div class="p-6 border-t border-[#EBDBC9] dark:border-[#273244] bg-white dark:bg-[#111827] flex items-center gap-3">
                        <button type="submit" 
                                form="drawer-filter-form"
                                class="flex-1 py-3 rounded-2xl text-xs sm:text-sm font-extrabold bg-gradient-to-r from-orange-500 to-amber-600 hover:from-orange-600 hover:to-amber-700 text-white shadow-md shadow-orange-500/25 transition-all cursor-pointer"
                                id="apply-filters-btn">
                            {{ __('Apply Filters') }}
                        </button>
                        <button type="button" 
                                class="px-5 py-3 rounded-2xl text-xs sm:text-sm font-bold bg-[#FFF5EB] dark:bg-[#1E293B] border border-[#EBDBC9] dark:border-[#273244] text-[#111827] dark:text-white hover:border-orange-500 cursor-pointer"
                                @click="drawerOpen = false">
                            {{ __('Cancel') }}
                        </button>
                    </div>

                </div>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('searchPageController', () => ({
                drawerOpen: false,
                viewMode: '{{ $viewMode }}',
                hoveredListingId: null,
                activeTab: '{{ $purpose ?: 'all' }}',
                activeMap: null,
                markers: {},
                mapLoaded: false,
                mapData: @json($mapMarkers),

                init() {
                    if (this.viewMode === 'map') {
                        this.$nextTick(() => this.initMap());
                    }
                },

                setView(mode) {
                    this.viewMode = mode;
                    const url = new URL(window.location.href);
                    url.searchParams.set('view', mode);
                    window.history.replaceState({}, '', url);
                    if (mode === 'map' && !this.mapLoaded) {
                        this.$nextTick(() => this.initMap());
                    }
                },

                initMap() {
                    if (this.mapLoaded || !document.getElementById('search-map-container')) return;
                    
                    const loadLeafletScript = () => {
                        return new Promise((resolve) => {
                            if (window.L) {
                                resolve();
                                return;
                            }
                            const script = document.createElement('script');
                            script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                            script.onload = () => resolve();
                            document.head.appendChild(script);
                        });
                    };

                    loadLeafletScript().then(() => {
                        const mapEl = document.getElementById('search-map-container');
                        if (!mapEl) return;

                        // Default center: Addis Ababa (9.010, 38.761)
                        const map = L.map('search-map-container', {
                            zoomControl: true,
                            scrollWheelZoom: true
                        }).setView([9.010, 38.761], 12);

                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '&copy; OpenStreetMap contributors'
                        }).addTo(map);

                        const bounds = [];

                        this.mapData.forEach(item => {
                            if (!item.lat || !item.lng) return;
                            bounds.push([item.lat, item.lng]);

                            const priceHtml = `<div class='custom-price-pin' id='pin-${item.id}'>${item.short_price}</div>`;
                            const customIcon = L.divIcon({
                                className: 'custom-pin-wrapper',
                                html: priceHtml,
                                iconSize: [60, 24],
                                iconAnchor: [30, 12]
                            });

                            const marker = L.marker([item.lat, item.lng], { icon: customIcon }).addTo(map);
                            this.markers[item.id] = marker;

                            const popupContent = `
                                <div class='w-64 bg-white dark:bg-[#111827] text-black dark:text-white rounded-2xl overflow-hidden font-sans border border-[#EBDBC9] dark:border-[#273244]'>
                                    <img src='${item.image}' alt='${item.title}' class='w-full h-32 object-cover'>
                                    <div class='p-3.5 space-y-1.5'>
                                        <span class='inline-block text-[10px] font-extrabold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-orange-100 dark:bg-orange-950/60 text-orange-600 dark:text-orange-400'>
                                            ${item.purpose}
                                        </span>
                                        <h4 class='font-extrabold text-xs line-clamp-1 text-black dark:text-white'>${item.title}</h4>
                                        <p class='font-black text-orange-600 dark:text-orange-400 text-sm'>${item.price_formatted}</p>
                                        <div class='flex items-center gap-2 text-[11px] font-bold text-black dark:text-[#94A3B8] pt-1 border-t border-[#EBDBC9] dark:border-[#273244]'>
                                            ${item.bedrooms ? `<span>${item.bedrooms} Beds</span> &bull;` : ''}
                                            ${item.bathrooms ? `<span>${item.bathrooms} Baths</span> &bull;` : ''}
                                            ${item.area ? `<span>${item.area} m²</span>` : ''}
                                        </div>
                                        <a href='${item.url}' class='block mt-2 text-center text-xs font-bold py-2 px-3 rounded-xl bg-orange-500 hover:bg-orange-600 text-white transition-colors shadow-sm'>{{ __('View Details') }}</a>
                                    </div>
                                </div>
                            `;

                            marker.bindPopup(popupContent, { maxWidth: 280, closeButton: false });
                        });

                        if (bounds.length > 0) {
                            map.fitBounds(bounds, { padding: [50, 50], maxZoom: 14 });
                        }

                        this.activeMap = map;
                        this.mapLoaded = true;
                    });
                },

                highlightMarker(id) {
                    this.hoveredListingId = id;
                    const pinEl = document.getElementById('pin-' + id);
                    if (pinEl) pinEl.classList.add('active-pin');
                },

                unhighlightMarker(id) {
                    this.hoveredListingId = null;
                    const pinEl = document.getElementById('pin-' + id);
                    if (pinEl) pinEl.classList.remove('active-pin');
                },

                focusMarker(id, lat, lng) {
                    if (this.activeMap && lat && lng) {
                        this.activeMap.setView([lat, lng], 14, { animate: true });
                        if (this.markers[id]) {
                            this.markers[id].openPopup();
                        }
                    }
                }
            }));
        });
    </script>
    @endpush
</x-layouts.app>
