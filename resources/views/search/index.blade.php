<x-layouts.app>
    <x-slot name="title">Property Search Engine | Ethiopian Real Estate Marketplace</x-slot>
    <x-slot name="description">Search verified properties for sale, rent, and short stay in Addis Ababa and across Ethiopia with advanced multi-criteria filters and interactive map.</x-slot>

    @push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <style>
        .custom-price-pin {
            background-color: #F97316;
            color: #FFFFFF;
            font-family: var(--font-sans);
            font-size: 11px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 9999px;
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.4), 0 1px 3px rgba(0,0,0,0.2);
            white-space: nowrap;
            border: 2px solid #FFFFFF;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            text-align: center;
            cursor: pointer;
        }
        .custom-price-pin:hover, .custom-price-pin.active-pin {
            background-color: #EA580C;
            transform: scale(1.15) translateY(-2px);
            box-shadow: 0 8px 20px rgba(234, 88, 12, 0.6);
            z-index: 1000 !important;
        }
        .leaflet-popup-content-wrapper {
            border-radius: 1rem;
            padding: 0;
            overflow: hidden;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2), 0 8px 10px -6px rgba(0, 0, 0, 0.2);
        }
        .leaflet-popup-content {
            margin: 0;
            line-height: 1.4;
        }
        .dark .leaflet-popup-content-wrapper {
            background: #1E293B;
            color: #F1F5F9;
        }
    </style>
    @endpush

    <div class="min-h-screen pt-24 pb-16 bg-[#FAFAFA] dark:bg-[#0B0F19]"
         x-data="{
             drawerOpen: false,
             viewMode: '{{ $viewMode }}',
             hoveredListingId: null,
             activeTab: '{{ $purpose ?: 'all' }}',
             activeMap: null,
             markers: {},
             mapLoaded: false,

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

                     const mapData = @json($mapMarkers);
                     const bounds = [];

                     mapData.forEach(item => {
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
                             <div class='w-64 bg-white dark:bg-[#1E293B] text-gray-900 dark:text-white rounded-2xl overflow-hidden font-sans'>
                                 <img src='${item.image}' alt='${item.title}' class='w-full h-32 object-cover'>
                                 <div class='p-3.5 space-y-1.5'>
                                     <span class='inline-block text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-orange-100 dark:bg-orange-950/60 text-orange-600 dark:text-orange-400'>
                                         ${item.purpose}
                                     </span>
                                     <h4 class='font-bold text-xs line-clamp-1'>${item.title}</h4>
                                     <p class='font-black text-orange-600 dark:text-orange-400 text-sm'>${item.price_formatted}</p>
                                     <div class='flex items-center gap-2 text-[10px] text-gray-500 dark:text-gray-400 pt-1 border-t border-gray-100 dark:border-gray-800'>
                                         ${item.bedrooms ? `<span>${item.bedrooms} Beds</span> &bull;` : ''}
                                         ${item.bathrooms ? `<span>${item.bathrooms} Baths</span> &bull;` : ''}
                                         ${item.area ? `<span>${item.area} m²</span>` : ''}
                                     </div>
                                     <a href='${item.url}' class='block mt-2 text-center text-xs font-bold py-1.5 px-3 rounded-lg bg-orange-500 hover:bg-orange-600 text-white transition-colors'>
                                         View Details
                                     </a>
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
         }">

        <div class="page-container space-y-6">

            {{-- SEARCH ENGINE TOP BAR --}}
            <div class="bg-white dark:bg-[#111827] border border-gray-200 dark:border-gray-800 rounded-3xl p-4 sm:p-6 shadow-sm space-y-4">
                
                {{-- Breadcrumbs & Results Eyebrow --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-gray-100 dark:border-gray-800/80 pb-3">
                    <nav class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                        <a href="/" class="hover:text-primary-600 transition-colors">Home</a>
                        <span>/</span>
                        <a href="/properties" class="hover:text-primary-600 transition-colors">Marketplace</a>
                        <span>/</span>
                        <span class="text-gray-900 dark:text-white font-semibold">Search Engine</span>
                    </nav>

                    <div class="flex items-center gap-2 text-xs">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-orange-50 dark:bg-orange-950/40 text-primary-600 dark:text-primary-400 font-bold border border-orange-200 dark:border-orange-800/50">
                            <span class="w-2 h-2 rounded-full bg-primary-500 animate-pulse"></span>
                            {{ $listings->total() }} Properties Found
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
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-5 h-5 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                            </div>
                            <input type="text" 
                                   name="q" 
                                   id="search-keyword-input"
                                   value="{{ $q }}" 
                                   placeholder="Search by neighborhood (Bole, Kazanchis, Ayat...), title, or property code..." 
                                   class="form-input w-full pl-11 pr-10 py-3 text-sm rounded-2xl bg-gray-50 dark:bg-gray-800/60 border-gray-200 dark:border-gray-700 focus:bg-white dark:focus:bg-gray-900 transition-all">
                            
                            @if(!empty($q))
                                <a href="{{ request()->fullUrlWithQuery(['q' => null]) }}" 
                                   class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                                   title="Clear search keyword">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                </a>
                            @endif
                        </div>

                        <div class="flex items-center gap-2">
                            {{-- Search Button --}}
                            <button type="submit" class="btn btn-primary px-6 py-3 rounded-2xl text-sm font-bold flex items-center gap-2 shadow-md shadow-orange-500/20" id="search-submit-btn">
                                <span>Search</span>
                            </button>

                            {{-- Filter Drawer Toggle Button --}}
                            <button type="button" 
                                    @click="drawerOpen = true"
                                    class="btn btn-secondary px-4 py-3 rounded-2xl text-sm font-bold flex items-center gap-2 border-gray-200 dark:border-gray-700 hover:border-primary-500 transition-all"
                                    id="filter-drawer-toggle">
                                <svg class="w-4 h-4 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75" /></svg>
                                <span>Filters</span>
                                @if($activeCount > 0)
                                    <span class="w-5 h-5 rounded-full bg-primary-500 text-white text-[10px] font-black flex items-center justify-center">
                                        {{ $activeCount }}
                                    </span>
                                @endif
                            </button>
                        </div>
                    </div>

                    {{-- Quick Neighborhood Suggestions --}}
                    <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs pt-1">
                        <span class="text-gray-400 font-medium whitespace-nowrap">Popular Areas:</span>
                        @foreach(['Bole', 'Kazanchis', 'Old Airport', 'Ayat', 'Hawassa', 'Bishoftu'] as $city)
                            <a href="{{ request()->fullUrlWithQuery(['q' => $city]) }}" 
                               class="px-2.5 py-1 rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-orange-50 dark:hover:bg-orange-950/30 text-gray-700 dark:text-gray-300 hover:text-primary-600 transition-colors whitespace-nowrap">
                                {{ $city }}
                            </a>
                        @endforeach
                    </div>
                </form>

                {{-- Mode Switcher Tabs --}}
                <div class="flex items-center gap-1.5 overflow-x-auto pb-1 pt-2 border-t border-gray-100 dark:border-gray-800">
                    @php
                        $tabPurposes = [
                            'all' => 'All Properties',
                            'For Sale' => 'For Sale',
                            'For Rent' => 'For Rent',
                            'Short Stay' => 'Short Stay',
                            'Land' => 'Land',
                            'Development' => 'New Projects',
                        ];
                    @endphp
                    @foreach($tabPurposes as $pVal => $pLabel)
                        <a href="{{ request()->fullUrlWithQuery(['purpose' => $pVal === 'all' ? null : $pVal]) }}" 
                           class="px-3.5 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition-all {{ ($purpose === $pVal || (!$purpose && $pVal === 'all')) ? 'bg-primary-500 text-white shadow-sm' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                            {{ $pLabel }}
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- APPLIED FILTER CHIPS & CONTROLS ROW --}}
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                
                {{-- Active Filter Chips --}}
                <div class="flex-1 flex flex-wrap items-center gap-2">
                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400">
                        {{ $listings->total() }} results
                    </span>

                    @if(!empty($appliedFilters))
                        <div class="h-4 w-px bg-gray-300 dark:bg-gray-700 hidden sm:block"></div>

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
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-200 shadow-xs">
                                <span>{{ $chip['label'] }}</span>
                                <a href="{{ $removalUrl }}" class="text-gray-400 hover:text-rose-500 transition-colors p-0.5 rounded-full" title="Remove filter">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                </a>
                            </span>
                        @endforeach

                        {{-- Clear All Action --}}
                        <a href="{{ route('search', ['view' => $viewMode]) }}" class="text-xs font-bold text-primary-600 dark:text-primary-400 hover:underline flex items-center gap-1 ml-1" id="clear-all-filters-link">
                            <span>Clear all</span>
                        </a>
                    @endif
                </div>

                {{-- View Layout Mode Switchers & Sorting Menu --}}
                <div class="flex items-center gap-3 self-end lg:self-auto">
                    {{-- Sort Dropdown --}}
                    <div class="flex items-center gap-2 text-xs">
                        <label for="search-sort-select" class="text-gray-500 dark:text-gray-400 font-medium hidden sm:inline">Sort by:</label>
                        <select id="search-sort-select" 
                                onchange="window.location.href = this.value"
                                class="form-input py-1.5 px-3 text-xs rounded-xl bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 font-medium cursor-pointer">
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'newest']) }}" {{ $sort === 'newest' ? 'selected' : '' }}>Newest Listed</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'price_asc']) }}" {{ $sort === 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'price_desc']) }}" {{ $sort === 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'area_desc']) }}" {{ $sort === 'area_desc' ? 'selected' : '' }}>Largest Area (m²)</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'bedrooms_desc']) }}" {{ $sort === 'bedrooms_desc' ? 'selected' : '' }}>Most Bedrooms</option>
                        </select>
                    </div>

                    {{-- Layout View Toggles --}}
                    <div class="flex items-center bg-gray-200/80 dark:bg-gray-800 p-0.5 rounded-xl border border-gray-200 dark:border-gray-700">
                        {{-- Grid View Button --}}
                        <button type="button" 
                                @click="setView('grid')"
                                :class="viewMode === 'grid' ? 'bg-white dark:bg-gray-700 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white'"
                                class="p-1.5 rounded-lg transition-all"
                                title="Grid View"
                                id="view-mode-grid">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" /></svg>
                        </button>

                        {{-- List View Button --}}
                        <button type="button" 
                                @click="setView('list')"
                                :class="viewMode === 'list' ? 'bg-white dark:bg-gray-700 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white'"
                                class="p-1.5 rounded-lg transition-all"
                                title="List View"
                                id="view-mode-list">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
                        </button>

                        {{-- Split Map View Button --}}
                        <button type="button" 
                                @click="setView('map')"
                                :class="viewMode === 'map' ? 'bg-white dark:bg-gray-700 text-primary-600 dark:text-primary-400 shadow-sm' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white'"
                                class="p-1.5 rounded-lg transition-all"
                                title="Split Map View"
                                id="view-mode-map">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" /></svg>
                        </button>
                    </div>
                </div>
            </div>

            {{-- SEARCH RESULTS PRESENTATION --}}
            @if($listings->isEmpty())
                {{-- NO RESULTS STATE --}}
                <div class="bg-white dark:bg-[#111827] border border-gray-200 dark:border-gray-800 rounded-3xl p-12 text-center max-w-2xl mx-auto my-12 shadow-sm">
                    <div class="w-20 h-20 rounded-full bg-orange-100 dark:bg-orange-950/50 text-primary-600 dark:text-primary-400 mx-auto flex items-center justify-center mb-5">
                        <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                    </div>

                    <h3 class="font-display font-extrabold text-2xl text-gray-900 dark:text-white mb-2">
                        No Matching Properties Found
                    </h3>

                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6 max-w-md mx-auto leading-relaxed">
                        We couldn't find any verified listings matching your exact criteria. Try widening your price range, relaxing bedrooms, or clearing filters.
                    </p>

                    <div class="flex flex-wrap items-center justify-center gap-3">
                        <a href="{{ route('search') }}" class="btn btn-primary px-6 py-2.5 rounded-xl text-sm font-bold shadow-md shadow-orange-500/20">
                            Reset All Filters
                        </a>
                        <a href="{{ route('properties.index') }}" class="btn btn-secondary px-6 py-2.5 rounded-xl text-sm font-bold">
                            Browse All Marketplace
                        </a>
                    </div>

                    <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-800 text-xs text-gray-500">
                        <p class="font-semibold mb-2">Try one of these popular searches:</p>
                        <div class="flex flex-wrap justify-center gap-2">
                            <a href="{{ route('search', ['purpose' => 'For Sale']) }}" class="text-primary-600 dark:text-primary-400 hover:underline">Properties For Sale</a>
                            <span>&bull;</span>
                            <a href="{{ route('search', ['purpose' => 'For Rent']) }}" class="text-primary-600 dark:text-primary-400 hover:underline">Homes For Rent</a>
                            <span>&bull;</span>
                            <a href="{{ route('search', ['purpose' => 'Short Stay']) }}" class="text-primary-600 dark:text-primary-400 hover:underline">Executive Short Stays</a>
                        </div>
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
                                 :class="hoveredListingId === '{{ $item->id }}' ? 'ring-2 ring-primary-500 rounded-2xl' : ''">
                                <x-property-list-card :listing="$item" />
                            </div>
                        @endforeach

                        {{-- Pagination inside left list in map mode --}}
                        <div class="pt-4">
                            {{ $listings->links() }}
                        </div>
                    </div>

                    {{-- Right column: Interactive Leaflet Map --}}
                    <div class="lg:col-span-6 sticky top-24 bg-white dark:bg-gray-800 rounded-3xl overflow-hidden border border-gray-200 dark:border-gray-700 shadow-sm h-[600px] lg:h-[calc(100vh-14rem)]">
                        <div id="search-map-container" class="w-full h-full z-10"></div>
                    </div>
                </div>

                {{-- Standard Pagination for Grid and List modes --}}
                <div x-show="viewMode !== 'map'" class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-800 flex justify-center">
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
            
            {{-- Backdrop --}}
            <div x-show="drawerOpen"
                 x-transition:enter="ease-in-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in-out duration-300"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="drawerOpen = false"
                 class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity"></div>

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
                    <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-orange-100 dark:bg-orange-950/60 text-primary-600 dark:text-primary-400 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75" /></svg>
                            </div>
                            <h2 id="filter-drawer-title" class="font-display font-bold text-lg text-gray-900 dark:text-white">
                                Filter Properties
                            </h2>
                        </div>

                        <div class="flex items-center gap-2">
                            <a href="{{ route('search', ['view' => $viewMode]) }}" class="text-xs font-semibold text-gray-500 hover:text-primary-600 transition-colors">
                                Reset All
                            </a>
                            <button type="button" 
                                    @click="drawerOpen = false"
                                    class="p-2 rounded-xl text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                                    aria-label="Close filters">
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
                            <label class="form-label font-bold text-xs uppercase tracking-wider text-gray-500">Listing Purpose</label>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach(['all' => 'Any Purpose', 'For Sale' => 'For Sale', 'For Rent' => 'For Rent', 'Short Stay' => 'Short Stay', 'Land' => 'Land', 'Development' => 'New Projects'] as $pValue => $pText)
                                    <label class="flex items-center gap-2 p-2 rounded-xl border border-gray-200 dark:border-gray-700 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors has-checked:border-primary-500 has-checked:bg-orange-50/50 dark:has-checked:bg-orange-950/20">
                                        <input type="radio" name="purpose" value="{{ $pValue }}" class="text-primary-500 focus:ring-primary-500" {{ ($purpose === $pValue || (!$purpose && $pValue === 'all')) ? 'checked' : '' }}>
                                        <span class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ $pText }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- 2. Property Type --}}
                        <div class="space-y-2">
                            <label class="form-label font-bold text-xs uppercase tracking-wider text-gray-500">Property Type</label>
                            <select name="property_type" class="form-input text-xs font-medium rounded-xl">
                                <option value="all">All Property Types</option>
                                @foreach($propertyTypes as $pType)
                                    <option value="{{ $pType->code }}" {{ $propertyType === $pType->code ? 'selected' : '' }}>
                                        {{ $pType->name_en }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- 3. Price Range (Min & Max in ETB) --}}
                        <div class="space-y-2">
                            <label class="form-label font-bold text-xs uppercase tracking-wider text-gray-500">Price Range (ETB)</label>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <input type="number" name="min_price" value="{{ $minPrice }}" placeholder="Min Price" class="form-input text-xs rounded-xl" min="0" step="50000">
                                </div>
                                <div>
                                    <input type="number" name="max_price" value="{{ $maxPrice }}" placeholder="Max Price" class="form-input text-xs rounded-xl" min="0" step="50000">
                                </div>
                            </div>
                            {{-- Quick price presets --}}
                            <div class="flex flex-wrap gap-1.5 pt-1">
                                <button type="button" @click="$el.closest('form').querySelector('[name=min_price]').value = ''; $el.closest('form').querySelector('[name=max_price]').value = '5000000'" class="px-2 py-1 rounded-md text-[10px] font-bold bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-orange-100 dark:hover:bg-orange-950/40 hover:text-primary-600">&lt; 5M ETB</button>
                                <button type="button" @click="$el.closest('form').querySelector('[name=min_price]').value = '5000000'; $el.closest('form').querySelector('[name=max_price]').value = '15000000'" class="px-2 py-1 rounded-md text-[10px] font-bold bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-orange-100 dark:hover:bg-orange-950/40 hover:text-primary-600">5M - 15M</button>
                                <button type="button" @click="$el.closest('form').querySelector('[name=min_price]').value = '15000000'; $el.closest('form').querySelector('[name=max_price]').value = '35000000'" class="px-2 py-1 rounded-md text-[10px] font-bold bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-orange-100 dark:hover:bg-orange-950/40 hover:text-primary-600">15M - 35M</button>
                                <button type="button" @click="$el.closest('form').querySelector('[name=min_price]').value = '35000000'; $el.closest('form').querySelector('[name=max_price]').value = ''" class="px-2 py-1 rounded-md text-[10px] font-bold bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-orange-100 dark:hover:bg-orange-950/40 hover:text-primary-600">35M+ ETB</button>
                            </div>
                        </div>

                        {{-- 4. Bedrooms --}}
                        <div class="space-y-2">
                            <label class="form-label font-bold text-xs uppercase tracking-wider text-gray-500">Bedrooms</label>
                            <div class="flex items-center gap-1.5 overflow-x-auto pb-1">
                                @foreach(['any' => 'Any', '1' => '1+', '2' => '2+', '3' => '3+', '4' => '4+', '5' => '5+'] as $bVal => $bText)
                                    <label class="flex-1 text-center py-2 px-2.5 rounded-xl border border-gray-200 dark:border-gray-700 cursor-pointer text-xs font-bold transition-all has-checked:bg-primary-500 has-checked:text-white has-checked:border-primary-500">
                                        <input type="radio" name="bedrooms" value="{{ $bVal }}" class="sr-only" {{ ($bedrooms == $bVal || (!$bedrooms && $bVal === 'any')) ? 'checked' : '' }}>
                                        <span>{{ $bText }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- 5. Bathrooms --}}
                        <div class="space-y-2">
                            <label class="form-label font-bold text-xs uppercase tracking-wider text-gray-500">Bathrooms</label>
                            <div class="flex items-center gap-1.5 overflow-x-auto pb-1">
                                @foreach(['any' => 'Any', '1' => '1+', '2' => '2+', '3' => '3+', '4' => '4+'] as $bVal => $bText)
                                    <label class="flex-1 text-center py-2 px-2.5 rounded-xl border border-gray-200 dark:border-gray-700 cursor-pointer text-xs font-bold transition-all has-checked:bg-primary-500 has-checked:text-white has-checked:border-primary-500">
                                        <input type="radio" name="bathrooms" value="{{ $bVal }}" class="sr-only" {{ ($bathrooms == $bVal || (!$bathrooms && $bVal === 'any')) ? 'checked' : '' }}>
                                        <span>{{ $bText }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- 6. Area Range (m²) --}}
                        <div class="space-y-2">
                            <label class="form-label font-bold text-xs uppercase tracking-wider text-gray-500">Area (m²)</label>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <input type="number" name="min_area" value="{{ $minArea }}" placeholder="Min m²" class="form-input text-xs rounded-xl" min="0">
                                </div>
                                <div>
                                    <input type="number" name="max_area" value="{{ $maxArea }}" placeholder="Max m²" class="form-input text-xs rounded-xl" min="0">
                                </div>
                            </div>
                        </div>

                        {{-- 7. Parking & Verification Toggles --}}
                        <div class="space-y-3 pt-2 border-t border-gray-100 dark:border-gray-800">
                            {{-- Parking --}}
                            <label class="flex items-center justify-between p-3 rounded-xl border border-gray-200 dark:border-gray-700 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                <span class="text-xs font-bold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h4a3 3 0 013 3v0a3 3 0 01-3 3H8V7zm0 6v5" /></svg>
                                    Reserved Parking Available
                                </span>
                                <input type="checkbox" name="parking" value="1" {{ $parking ? 'checked' : '' }} class="rounded text-primary-500 focus:ring-primary-500 w-4 h-4">
                            </label>

                            {{-- Verification --}}
                            <label class="flex items-center justify-between p-3 rounded-xl border border-emerald-200 dark:border-emerald-800/40 bg-emerald-50/40 dark:bg-emerald-950/20 cursor-pointer hover:bg-emerald-50 dark:hover:bg-emerald-950/30 transition-colors">
                                <span class="text-xs font-bold text-emerald-800 dark:text-emerald-300 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.745 3.745 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" /></svg>
                                    Verified Properties Only
                                </span>
                                <input type="checkbox" name="verified" value="1" {{ $verified ? 'checked' : '' }} class="rounded text-emerald-600 focus:ring-emerald-500 w-4 h-4">
                            </label>
                        </div>

                        {{-- 8. Amenities Checkboxes --}}
                        <div class="space-y-2 pt-2 border-t border-gray-100 dark:border-gray-800">
                            <label class="form-label font-bold text-xs uppercase tracking-wider text-gray-500">Key Amenities</label>
                            <div class="grid grid-cols-1 gap-2 max-h-48 overflow-y-auto pr-1 custom-scrollbar">
                                @foreach($availableAmenities as $amenity)
                                    <label class="flex items-center gap-2.5 p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800/80 cursor-pointer text-xs">
                                        <input type="checkbox" 
                                               name="amenities[]" 
                                               value="{{ $amenity->code }}" 
                                               {{ in_array($amenity->code, (array)$amenities) ? 'checked' : '' }}
                                               class="rounded text-primary-500 focus:ring-primary-500 w-4 h-4">
                                        <span class="text-gray-700 dark:text-gray-300 font-medium">{{ $amenity->name_en }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                    </form>

                    {{-- Drawer Footer --}}
                    <div class="p-6 border-t border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/60 flex items-center gap-3">
                        <button type="submit" 
                                form="drawer-filter-form"
                                class="btn btn-primary flex-1 py-3 rounded-xl text-sm font-bold shadow-md shadow-orange-500/20"
                                id="apply-filters-btn">
                            Apply Filters
                        </button>
                        <button type="button" 
                                @click="drawerOpen = false"
                                class="btn btn-secondary px-4 py-3 rounded-xl text-sm font-semibold">
                            Cancel
                        </button>
                    </div>

                </div>
            </div>
        </div>

    </div>
</x-layouts.app>
