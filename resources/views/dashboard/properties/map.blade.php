<x-layouts.dashboard>
    <x-slot name="title">{{ __('GIS Map Explorer') }}</x-slot>

    @section('page-title', 'GIS Map Explorer')
    @section('breadcrumb')
        <a href="/dashboard/properties" class="hover:text-muted dark:hover:text-muted">{{ __('Properties') }}</a>
        <span class="mx-1.5 text-muted dark:text-muted">/</span>
        <span class="text-muted dark:text-dark-subtle">{{ __('Map Explorer') }}</span>
    @endsection

    @push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <style>
        .gis-marker-pin {
            transition: transform 160ms cubic-bezier(0.23, 1, 0.32, 1), box-shadow 160ms ease;
        }
        .gis-marker-pin:hover, .gis-marker-pin.active-pin {
            transform: scale(1.1) translateY(-2px);
            z-index: 1000 !important;
        }
        .gis-marker-bubble {
            background-color: #F97316;
            color: #FFFFFF;
            font-weight: 700;
            font-size: 11px;
            padding: 3px 8px;
            border-radius: 9999px;
            border: 2px solid #FFFFFF;
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.4), 0 2px 4px rgba(0,0,0,0.2);
            white-space: nowrap;
            cursor: pointer;
            text-align: center;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1.2;
        }
        .gis-marker-bubble.active-bubble {
            background-color: #EA580C;
            border-color: #FED7AA;
            box-shadow: 0 6px 18px rgba(234, 88, 12, 0.6);
        }
        .leaflet-popup-content-wrapper {
            border-radius: 1rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            padding: 0;
            overflow: hidden;
        }
        .leaflet-popup-content {
            margin: 0;
            line-height: 1.4;
        }
        .dark .leaflet-popup-content-wrapper {
            background: #111827;
            color: #F3F4F6;
            border: 1px solid #374151;
        }
        .dark .leaflet-popup-tip {
            background: #111827;
        }
    </style>
    @endpush

    <div x-data="gisMapExplorer" class="space-y-4 font-sans h-[calc(100vh-8rem)] flex flex-col">

        {{-- Top Header & Preset Filters --}}
        <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3 bg-white dark:bg-[#111827] p-3.5 rounded-2xl border border-warm dark:border-[#273244] shadow-xs flex-shrink-0">
            {{-- Quick Presets --}}
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 lg:pb-0 scrollbar-thin">
                <span class="text-xs font-semibold text-muted dark:text-muted mr-1 flex-shrink-0">Urban Areas:</span>
                <template x-for="p in presets" :key="p.name">
                    <button 
                        type="button" 
                        @click="applyPreset(p)" 
                        class="text-xs px-2.5 py-1 rounded-lg border border-warm dark:border-warm bg-surface dark:bg-surface text-muted dark:text-dark-subtle hover:border-orange-500 hover:text-orange-600 dark:hover:text-orange-400 transition-colors whitespace-nowrap cursor-pointer"
                        :class="selectedPreset === p.name ? 'border-orange-500 bg-orange-50 dark:bg-orange-950/30 text-orange-600 dark:text-orange-400 font-bold' : ''"
                        x-text="p.name"
                    ></button>
                </template>
            </div>

            {{-- Radius Slider & View Toggle --}}
            <div class="flex flex-wrap items-center gap-3">
                {{-- Interactive Radius Slider --}}
                <div class="flex items-center gap-2 bg-surface dark:bg-surface px-3 py-1 rounded-xl border border-warm dark:border-warm">
                    <span class="text-xs font-medium text-muted dark:text-muted">Radius:</span>
                    <input 
                        type="range" 
                        min="1" 
                        max="50" 
                        step="1" 
                        x-model.number="radiusKm" 
                        @input="drawRadiusCircle()"
                        @change="fetchProperties()"
                        class="w-24 accent-orange-500 cursor-pointer"
                    >
                    <span class="text-xs font-bold font-mono text-orange-600 dark:text-orange-400 w-12 text-right" x-text="radiusKm + ' km'"></span>
                </div>

                {{-- View Mode Toggle --}}
                <div class="inline-flex rounded-xl p-0.5 bg-surface-subtle dark:bg-surface border border-warm dark:border-warm text-xs">
                    <button 
                        type="button" 
                        @click="viewMode = 'split'; setTimeout(() => map && map.invalidateSize(), 200)"
                        class="px-2.5 py-1 rounded-lg font-medium transition-colors cursor-pointer"
                        :class="viewMode === 'split' ? 'bg-white dark:bg-[#111827] text-muted dark:text-white shadow-xs' : 'text-muted hover:text-muted dark:hover:text-white'"
                    >
                        Split
                    </button>
                    <button 
                        type="button" 
                        @click="viewMode = 'map'; setTimeout(() => map && map.invalidateSize(), 200)"
                        class="px-2.5 py-1 rounded-lg font-medium transition-colors cursor-pointer"
                        :class="viewMode === 'map' ? 'bg-white dark:bg-[#111827] text-muted dark:text-white shadow-xs' : 'text-muted hover:text-muted dark:hover:text-white'"
                    >
                        Map Only
                    </button>
                    <button 
                        type="button" 
                        @click="viewMode = 'list'"
                        class="px-2.5 py-1 rounded-lg font-medium transition-colors cursor-pointer"
                        :class="viewMode === 'list' ? 'bg-white dark:bg-[#111827] text-muted dark:text-white shadow-xs' : 'text-muted hover:text-muted dark:hover:text-white'"
                    >{{ __('List Only') }}</button>
                </div>
            </div>
        </div>

        {{-- Main Interactive Explorer Body --}}
        <div class="flex-1 min-h-0 flex flex-col lg:flex-row gap-4">

            {{-- Left: Leaflet Interactive Map Viewport --}}
            <div 
                x-show="viewMode === 'map' || viewMode === 'split'" 
                class="relative rounded-2xl overflow-hidden border border-warm dark:border-[#273244] shadow-xs flex-1 min-h-0"
                :class="viewMode === 'split' ? 'lg:w-3/5' : 'w-full'"
            >
                <div id="gis_explorer_map" class="w-full h-full z-10" x-show="!mapFailed"></div>

                {{-- Offline / Tile Failure Fallback --}}
                <div x-show="mapFailed" class="w-full h-full flex flex-col items-center justify-center p-8 bg-surface-subtle dark:bg-surface text-center" x-cloak>
                    <div class="w-12 h-12 rounded-full bg-orange-100 dark:bg-orange-950/40 text-orange-600 dark:text-orange-400 flex items-center justify-center mb-2">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" /></svg>
                    </div>
                    <h4 class="text-sm font-semibold text-muted dark:text-white">{{ __('Map Tile Display Fallback') }}</h4>
                    <p class="text-xs text-muted dark:text-muted max-w-sm mt-1">{{ __('Map tiles could not load directly. You can browse all radius search results in List View on the right.') }}</p>
                </div>

                {{-- Map Floating Info Overlay --}}
                <div class="absolute top-3 left-3 z-20 bg-white/95 dark:bg-[#111827]/95 backdrop-blur-xs px-3 py-1.5 rounded-xl border border-warm dark:border-warm shadow-md text-xs font-mono text-muted dark:text-muted flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-orange-500 animate-pulse"></span>
                    <span>Center: <span x-text="centerLat.toFixed(4)"></span>° N, <span x-text="centerLng.toFixed(4)"></span>° E</span>
                    <span class="text-muted">·</span>
                    <span class="font-sans font-medium" x-text="properties.length + ' properties in radius'"></span>
                </div>

                {{-- Hint for map click recentering --}}
                <div class="absolute bottom-3 left-3 z-20 bg-black/60 backdrop-blur-xs text-white text-xs px-2.5 py-1 rounded-lg pointer-events-none hidden sm:block">
                    Click anywhere on map to re-center search
                </div>
            </div>

            {{-- Right: Property Cards List with Distance --}}
            <div 
                x-show="viewMode === 'list' || viewMode === 'split'" 
                class="flex flex-col bg-white dark:bg-[#111827] rounded-2xl border border-warm dark:border-[#273244] shadow-xs overflow-hidden"
                :class="viewMode === 'split' ? 'lg:w-2/5' : 'w-full'"
            >
                <div class="p-3.5 border-b border-warm dark:border-[#273244] flex items-center justify-between bg-surface/50 dark:bg-[#172033]/50">
                    <h3 class="font-display font-semibold text-sm text-muted dark:text-white">
                        Nearby Properties (<span x-text="properties.length"></span>)
                    </h3>
                    <div class="flex items-center gap-2">
                        <span x-show="loading" class="text-xs text-orange-500 animate-pulse font-medium">{{ __('Updating...') }}</span>
                        <span class="text-xs text-muted">{{ __('Sorted by distance') }}</span>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto p-3 space-y-2.5">
                    {{-- Skeleton state when initial loading --}}
                    <template x-if="loading && properties.length === 0">
                        <div class="space-y-2.5">
                            <div class="p-3 rounded-xl border border-warm dark:border-warm animate-pulse flex gap-3">
                                <div class="w-16 h-16 bg-surface-subtle dark:bg-surface rounded-lg"></div>
                                <div class="flex-1 space-y-2 py-1">
                                    <div class="h-3 bg-surface-subtle dark:bg-surface rounded w-3/4"></div>
                                    <div class="h-3 bg-surface-subtle dark:bg-surface rounded w-1/2"></div>
                                </div>
                            </div>
                            <div class="p-3 rounded-xl border border-warm dark:border-warm animate-pulse flex gap-3">
                                <div class="w-16 h-16 bg-surface-subtle dark:bg-surface rounded-lg"></div>
                                <div class="flex-1 space-y-2 py-1">
                                    <div class="h-3 bg-surface-subtle dark:bg-surface rounded w-2/3"></div>
                                    <div class="h-3 bg-surface-subtle dark:bg-surface rounded w-1/3"></div>
                                </div>
                            </div>
                        </div>
                    </template>

                    {{-- Properties cards --}}
                    <template x-for="prop in properties" :key="prop.id">
                        <div 
                            :id="'prop-card-' + prop.id"
                            @click="focusProperty(prop)" 
                            class="card card-interactive flex items-center gap-3 p-2.5 rounded-xl border cursor-pointer transition-all duration-150 group"
                            :class="selectedProperty?.id === prop.id 
                                ? 'bg-orange-50/70 dark:bg-orange-950/25 border-orange-500 ring-2 ring-orange-500/30' 
                                : 'bg-white dark:bg-[#111827] border-warm dark:border-[#273244] hover:border-orange-400 dark:hover:border-orange-500/50'"
                        >
                            {{-- Thumbnail --}}
                            <div class="w-16 h-16 rounded-lg bg-surface-subtle dark:bg-surface overflow-hidden flex-shrink-0 relative">
                                <template x-if="prop.cover_image">
                                    <img :src="prop.cover_image" :alt="prop.title" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!prop.cover_image">
                                    <div class="w-full h-full flex items-center justify-center text-muted">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" /></svg>
                                    </div>
                                </template>
                            </div>

                            {{-- Details --}}
                            <div class="flex-1 min-w-0">
                                <h4 class="font-semibold text-xs text-muted dark:text-white truncate group-hover:text-orange-600 transition-colors" x-text="prop.title"></h4>
                                <div class="flex items-center gap-2 mt-0.5 text-xs text-muted dark:text-muted">
                                    <span class="font-bold text-orange-600 dark:text-orange-400" x-text="formatPrice(prop.price)"></span>
                                    <span>·</span>
                                    <span class="font-mono text-muted" x-text="prop.distance ? prop.distance + ' km away' : ''"></span>
                                </div>
                                <div class="text-xs text-muted mt-1 flex items-center gap-2">
                                    <span x-show="prop.bedrooms" x-text="prop.bedrooms + ' Beds'"></span>
                                    <span x-show="prop.bathrooms" x-text="prop.bathrooms + ' Baths'"></span>
                                    <span x-show="prop.building_area" x-text="prop.building_area + ' m²'"></span>
                                </div>
                            </div>

                            <a :href="'/dashboard/properties/' + prop.id + '/media'" class="btn btn-ghost btn-sm p-1.5 text-muted hover:text-orange-600 dark:hover:text-orange-400 rounded-lg flex-shrink-0" title="Media Gallery">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                            </a>
                        </div>
                    </template>

                    <div x-show="properties.length === 0 && !loading" class="py-12 text-center text-muted" x-cloak>
                        <p class="text-xs">No properties found within <span x-text="radiusKm"></span> km of the selected center point.</p>
                        <p class="text-xs text-muted mt-1">{{ __('Try expanding the search radius slider above or picking an urban area preset.') }}</p>
                    </div>
                </div>
            </div>

        </div>

    </div>

    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
    (function() {
        function createGisExplorer() {
            return {
                centerLat: 9.0105,
                centerLng: 38.7612,
                radiusKm: 15,
                viewMode: 'split',
                selectedPreset: 'All Addis Ababa',
                properties: [],
                selectedProperty: null,
                loading: false,
                map: null,
                circle: null,
                markersLayer: null,
                markersMap: {},
                mapFailed: false,
                presets: [
                    { name: 'All Addis Ababa', lat: 9.0105, lng: 38.7612, radius: 20 },
                    { name: 'Bole Atlas', lat: 9.0012, lng: 38.7845, radius: 5 },
                    { name: 'Kazanchis', lat: 9.0180, lng: 38.7660, radius: 4 },
                    { name: 'Old Airport', lat: 8.9950, lng: 38.7420, radius: 5 },
                    { name: 'CMC Michael', lat: 9.0230, lng: 38.8350, radius: 6 },
                    { name: 'Piassa', lat: 9.0350, lng: 38.7530, radius: 5 }
                ],

                init() {
                    this.$nextTick(() => {
                        this.ensureLeaflet().then(ok => {
                            if (ok) {
                                this.initMap();
                            } else {
                                this.mapFailed = true;
                            }
                            this.fetchProperties();
                        });
                    });
                },

                ensureLeaflet() {
                    return new Promise((resolve) => {
                        if (window.L) {
                            resolve(true);
                            return;
                        }
                        let attempts = 0;
                        const check = setInterval(() => {
                            attempts++;
                            if (window.L) {
                                clearInterval(check);
                                resolve(true);
                            } else if (attempts >= 40) {
                                clearInterval(check);
                                resolve(false);
                            }
                        }, 100);
                    });
                },

                initMap() {
                    try {
                        const el = document.getElementById('gis_explorer_map');
                        if (!el || !window.L) return;

                        this.map = L.map(el, {
                            center: [this.centerLat, this.centerLng],
                            zoom: 12,
                            zoomControl: true,
                            attributionControl: false
                        });

                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19
                        }).addTo(this.map);

                        this.markersLayer = L.layerGroup().addTo(this.map);
                        this.drawRadiusCircle();

                        this.map.on('click', (e) => {
                            this.centerLat = parseFloat(e.latlng.lat.toFixed(4));
                            this.centerLng = parseFloat(e.latlng.lng.toFixed(4));
                            this.selectedPreset = null;
                            this.drawRadiusCircle();
                            this.fetchProperties();
                        });

                        setTimeout(() => {
                            if (this.map) this.map.invalidateSize();
                        }, 300);
                    } catch (err) {
                        console.error('Map init error:', err);
                        this.mapFailed = true;
                    }
                },

                drawRadiusCircle() {
                    if (!this.map || !window.L) return;
                    if (this.circle) {
                        this.map.removeLayer(this.circle);
                    }
                    this.circle = L.circle([this.centerLat, this.centerLng], {
                        color: '#EA580C',
                        fillColor: '#F97316',
                        fillOpacity: 0.12,
                        weight: 2,
                        radius: this.radiusKm * 1000
                    }).addTo(this.map);
                },

                fetchProperties() {
                    this.loading = true;
                    const url = '/api/v1/properties/search/radius?latitude=' + this.centerLat + '&longitude=' + this.centerLng + '&radius=' + this.radiusKm;
                    fetch(url)
                        .then(r => r.json())
                        .then(res => {
                            this.properties = res.data || [];
                            this.loading = false;
                            this.updateMarkers();
                        })
                        .catch(err => {
                            console.error('Fetch error:', err);
                            this.loading = false;
                        });
                },

                formatPrice(price) {
                    if (!price) return 'Contact';
                    if (price >= 1000000) {
                        return (price / 1000000).toFixed(1) + 'M ETB';
                    }
                    if (price >= 1000) {
                        return (price / 1000).toFixed(0) + 'K ETB';
                    }
                    return price + ' ETB';
                },

                updateMarkers() {
                    if (!this.map || !this.markersLayer || !window.L) return;
                    this.markersLayer.clearLayers();
                    this.markersMap = {};

                    this.properties.forEach(prop => {
                        if (!prop.latitude || !prop.longitude) return;

                        const priceFormatted = this.formatPrice(prop.price);
                        const isSelected = this.selectedProperty && this.selectedProperty.id === prop.id;

                        const icon = L.divIcon({
                            className: 'gis-marker-pin',
                            html: '<div class="gis-marker-bubble ' + (isSelected ? 'active-bubble' : '') + '">' + priceFormatted + '</div>',
                            iconSize: [68, 26],
                            iconAnchor: [34, 13]
                        });

                        const marker = L.marker([prop.latitude, prop.longitude], { icon: icon });

                        const popupHtml = `
                            <div style="width:230px;font-family:inherit;padding:8px 10px;">
                                ${prop.cover_image ? `<img src="${prop.cover_image}" alt="${prop.title}" style="width:100%;height:115px;object-fit:cover;border-radius:10px;margin-bottom:8px;">` : ''}
                                <h4 style="font-size:13px;font-weight:700;margin:0 0 4px;color:currentColor;">${prop.title}</h4>
                                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                                    <span style="font-size:13px;font-weight:800;color:#EA580C;">${priceFormatted}</span>
                                    <span style="font-size:11px;color:#9CA3AF;">${prop.distance ? prop.distance + ' km away' : ''}</span>
                                </div>
                                <div style="font-size:11px;color:#6B7280;margin-bottom:8px;">
                                    ${prop.bedrooms ? prop.bedrooms + ' Beds · ' : ''}${prop.bathrooms ? prop.bathrooms + ' Baths · ' : ''}${prop.building_area ? prop.building_area + ' m²' : ''}
                                </div>
                                <a href="/dashboard/properties/${prop.id}/media" style="display:block;text-align:center;background:#F97316;color:#ffffff;font-size:11px;font-weight:700;padding:6px 10px;border-radius:8px;text-decoration:none;">{{ __('Manage Media') }}</a>
                            </div>
                        `;

                        marker.bindPopup(popupHtml);
                        marker.on('click', () => {
                            this.selectedProperty = prop;
                            const cardEl = document.getElementById('prop-card-' + prop.id);
                            if (cardEl) {
                                cardEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                            }
                        });

                        this.markersLayer.addLayer(marker);
                        this.markersMap[prop.id] = marker;
                    });
                },

                focusProperty(prop) {
                    this.selectedProperty = prop;
                    if (this.map && prop.latitude && prop.longitude) {
                        this.map.flyTo([prop.latitude, prop.longitude], 15, { duration: 0.8 });
                        const marker = this.markersMap[prop.id];
                        if (marker) {
                            marker.openPopup();
                        }
                    }
                },

                applyPreset(p) {
                    this.selectedPreset = p.name;
                    this.centerLat = p.lat;
                    this.centerLng = p.lng;
                    this.radiusKm = p.radius;
                    if (this.map) {
                        this.map.flyTo([p.lat, p.lng], 13, { duration: 0.8 });
                        this.drawRadiusCircle();
                    }
                    this.fetchProperties();
                }
            };
        }

        function register() {
            if (window.Alpine) {
                window.Alpine.data('gisMapExplorer', createGisExplorer);
            }
        }

        if (window.Alpine) {
            register();
        } else {
            document.addEventListener('alpine:init', register);
        }
    })();
    </script>
    @endpush
</x-layouts.dashboard>
