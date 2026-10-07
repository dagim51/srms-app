<x-layouts.dashboard>
    <x-slot name="title">GIS Property Explorer</x-slot>

    @section('page-title', 'GIS Map Explorer')
    @section('breadcrumb')
        <a href="/dashboard/properties" class="hover:text-gray-700 dark:hover:text-gray-300">Properties</a>
        <span class="mx-1.5 text-gray-300 dark:text-gray-600">/</span>
        <span class="text-gray-700 dark:text-gray-300">Map Explorer</span>
    @endsection

    <div x-data="{
        centerLat: 9.0105,
        centerLng: 38.7612,
        radiusKm: 15,
        viewMode: 'split', // 'map', 'split', 'list'
        properties: [],
        selectedProperty: null,
        loading: false,
        map: null,
        circle: null,
        markersLayer: null,
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
            this.loadLeaflet().then(() => {
                this.initMap();
                this.fetchProperties();
            }).catch(() => {
                this.mapFailed = true;
                this.fetchProperties();
            });
        },

        loadLeaflet() {
            return new Promise((resolve, reject) => {
                if (window.L) {
                    resolve();
                    return;
                }
                if (!document.getElementById('leaflet-css')) {
                    const link = document.createElement('link');
                    link.id = 'leaflet-css';
                    link.rel = 'stylesheet';
                    link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                    document.head.appendChild(link);
                }
                if (!document.getElementById('leaflet-js')) {
                    const script = document.createElement('script');
                    script.id = 'leaflet-js';
                    script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                    script.onload = () => resolve();
                    script.onerror = () => reject();
                    document.head.appendChild(script);
                } else {
                    document.getElementById('leaflet-js').addEventListener('load', () => resolve());
                }
            });
        },

        initMap() {
            try {
                const el = document.getElementById('gis_explorer_map');
                if (!el) return;

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
                    this.drawRadiusCircle();
                    this.fetchProperties();
                });

                setTimeout(() => this.map.invalidateSize(), 300);
            } catch (err) {
                this.mapFailed = true;
            }
        },

        drawRadiusCircle() {
            if (!this.map) return;
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
                    this.loading = false;
                });
        },

        updateMarkers() {
            if (!this.map || !this.markersLayer) return;
            this.markersLayer.clearLayers();

            this.properties.forEach(prop => {
                if (!prop.latitude || !prop.longitude) return;

                const priceText = prop.price 
                    ? (prop.price >= 1000000 ? (prop.price / 1000000).toFixed(1) + 'M' : (prop.price / 1000).toFixed(0) + 'K') + ' ETB'
                    : 'View';

                const icon = L.divIcon({
                    className: 'gis-marker-pin',
                    html: '<div style=\"background:#F97316;color:white;font-weight:700;font-size:11px;padding:3px 8px;border-radius:20px;border:2px solid white;box-shadow:0 3px 8px rgba(0,0,0,0.3);white-space:nowrap;cursor:pointer;\">' + priceText + '</div>',
                    iconSize: [60, 24],
                    iconAnchor: [30, 12]
                });

                const marker = L.marker([prop.latitude, prop.longitude], { icon: icon });

                const popupHtml = `
                    <div style=\"width:220px;font-family:sans-serif;padding:2px;\">
                        ${prop.cover_image ? `<img src=\"${prop.cover_image}\" style=\"width:100%;height:110px;object-fit:cover;border-radius:8px;margin-bottom:6px;\">` : ''}
                        <h4 style=\"font-size:12px;font-weight:700;margin:0 0 4px;color:#111827;\">${prop.title}</h4>
                        <div style=\"display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;\">
                            <span style=\"font-size:12px;font-weight:700;color:#EA580C;\">${priceText}</span>
                            <span style=\"font-size:10px;color:#6B7280;\">${prop.distance ? prop.distance + ' km away' : ''}</span>
                        </div>
                        <div style=\"font-size:11px;color:#4B5563;margin-bottom:8px;\">
                            ${prop.bedrooms ? prop.bedrooms + ' Beds · ' : ''}${prop.bathrooms ? prop.bathrooms + ' Baths · ' : ''}${prop.building_area ? prop.building_area + ' m²' : ''}
                        </div>
                        <a href=\"/dashboard/properties/${prop.id}/media\" style=\"display:block;text-align:center;background:#F97316;color:white;font-size:11px;font-weight:600;padding:5px;border-radius:6px;text-decoration:none;\">Manage Media</a>
                    </div>
                `;

                marker.bindPopup(popupHtml);
                marker.on('click', () => {
                    this.selectedProperty = prop;
                });

                this.markersLayer.addLayer(marker);
            });
        },

        focusProperty(prop) {
            this.selectedProperty = prop;
            if (this.map && prop.latitude && prop.longitude) {
                this.map.flyTo([prop.latitude, prop.longitude], 15, { duration: 1 });
            }
        },

        applyPreset(p) {
            this.centerLat = p.lat;
            this.centerLng = p.lng;
            this.radiusKm = p.radius;
            if (this.map) {
                this.map.flyTo([p.lat, p.lng], 13);
                this.drawRadiusCircle();
            }
            this.fetchProperties();
        }
    }" class="space-y-4 font-sans h-[calc(100vh-8rem)] flex flex-col">

        {{-- Top Header & Preset Filters --}}
        <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3 bg-white dark:bg-[#111827] p-3.5 rounded-2xl border border-gray-200 dark:border-[#273244] shadow-xs flex-shrink-0">
            {{-- Quick Presets --}}
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 lg:pb-0 scrollbar-thin">
                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 mr-1 flex-shrink-0">Urban Areas:</span>
                <template x-for="p in presets" :key="p.name">
                    <button 
                        type="button" 
                        @click="applyPreset(p)" 
                        class="text-xs px-2.5 py-1 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:border-orange-500 hover:text-orange-600 dark:hover:text-orange-400 transition-colors whitespace-nowrap cursor-pointer"
                        x-text="p.name"
                    ></button>
                </template>
            </div>

            {{-- Radius Slider & View Toggle --}}
            <div class="flex flex-wrap items-center gap-3">
                {{-- Interactive Radius Slider --}}
                <div class="flex items-center gap-2 bg-gray-50 dark:bg-gray-800 px-3 py-1 rounded-xl border border-gray-200 dark:border-gray-700">
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Radius:</span>
                    <input 
                        type="range" 
                        min="1" 
                        max="30" 
                        step="1" 
                        x-model.number="radiusKm" 
                        @input="drawRadiusCircle()"
                        @change="fetchProperties()"
                        class="w-24 accent-orange-500 cursor-pointer"
                    >
                    <span class="text-xs font-bold font-mono text-orange-600 dark:text-orange-400 w-12 text-right" x-text="radiusKm + ' km'"></span>
                </div>

                {{-- View Mode Toggle --}}
                <div class="inline-flex rounded-xl p-0.5 bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-xs">
                    <button 
                        type="button" 
                        @click="viewMode = 'split'; setTimeout(() => map && map.invalidateSize(), 200)"
                        class="px-2.5 py-1 rounded-lg font-medium transition-colors cursor-pointer"
                        :class="viewMode === 'split' ? 'bg-white dark:bg-[#111827] text-gray-900 dark:text-white shadow-xs' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white'"
                    >
                        Split
                    </button>
                    <button 
                        type="button" 
                        @click="viewMode = 'map'; setTimeout(() => map && map.invalidateSize(), 200)"
                        class="px-2.5 py-1 rounded-lg font-medium transition-colors cursor-pointer"
                        :class="viewMode === 'map' ? 'bg-white dark:bg-[#111827] text-gray-900 dark:text-white shadow-xs' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white'"
                    >
                        Map Only
                    </button>
                    <button 
                        type="button" 
                        @click="viewMode = 'list'"
                        class="px-2.5 py-1 rounded-lg font-medium transition-colors cursor-pointer"
                        :class="viewMode === 'list' ? 'bg-white dark:bg-[#111827] text-gray-900 dark:text-white shadow-xs' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white'"
                    >
                        List Only
                    </button>
                </div>
            </div>
        </div>

        {{-- Main Interactive Explorer Body --}}
        <div class="flex-1 min-h-0 flex flex-col lg:flex-row gap-4">

            {{-- Left: Leaflet Interactive Map Viewport --}}
            <div 
                x-show="viewMode === 'map' || viewMode === 'split'" 
                class="relative rounded-2xl overflow-hidden border border-gray-200 dark:border-[#273244] shadow-xs flex-1 min-h-[300px]"
                :class="viewMode === 'split' ? 'lg:w-3/5' : 'w-full'"
            >
                <div id="gis_explorer_map" class="w-full h-full z-10" x-show="!mapFailed"></div>

                {{-- Offline / Tile Failure Fallback --}}
                <div x-show="mapFailed" class="w-full h-full flex flex-col items-center justify-center p-8 bg-gray-100 dark:bg-gray-800 text-center" x-cloak>
                    <div class="w-12 h-12 rounded-full bg-orange-100 dark:bg-orange-950/40 text-orange-600 dark:text-orange-400 flex items-center justify-center mb-2">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" /></svg>
                    </div>
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Map Tile Display Fallback</h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400 max-w-sm mt-1">
                        Map tiles could not load directly. You can browse all radius search results in List View on the right.
                    </p>
                </div>

                {{-- Map Floating Info Overlay --}}
                <div class="absolute top-3 left-3 z-20 bg-white/95 dark:bg-[#111827]/95 backdrop-blur-xs px-3 py-1.5 rounded-xl border border-gray-200 dark:border-gray-700 shadow-md text-xs font-mono text-gray-800 dark:text-gray-200 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-orange-500 animate-pulse"></span>
                    <span>Center: <span x-text="centerLat"></span>° N, <span x-text="centerLng"></span>° E</span>
                    <span class="text-gray-400">·</span>
                    <span class="font-sans font-medium" x-text="properties.length + ' properties in radius'"></span>
                </div>
            </div>

            {{-- Right: Property Cards List with Distance --}}
            <div 
                x-show="viewMode === 'list' || viewMode === 'split'" 
                class="flex flex-col bg-white dark:bg-[#111827] rounded-2xl border border-gray-200 dark:border-[#273244] shadow-xs overflow-hidden"
                :class="viewMode === 'split' ? 'lg:w-2/5' : 'w-full'"
            >
                <div class="p-3.5 border-b border-gray-100 dark:border-[#273244] flex items-center justify-between bg-gray-50/50 dark:bg-[#172033]/50">
                    <h3 class="font-display font-semibold text-sm text-gray-900 dark:text-white">
                        Nearby Properties (<span x-text="properties.length"></span>)
                    </h3>
                    <span class="text-[11px] text-gray-400">Sorted by distance</span>
                </div>

                <div class="flex-1 overflow-y-auto p-3 space-y-2.5">
                    <template x-for="prop in properties" :key="prop.id">
                        <div 
                            @click="focusProperty(prop)" 
                            class="flex items-center gap-3 p-2.5 rounded-xl border cursor-pointer transition-all duration-150 hover:border-orange-400 dark:hover:border-orange-500/50 group"
                            :class="selectedProperty?.id === prop.id 
                                ? 'bg-orange-50/60 dark:bg-orange-950/20 border-orange-500' 
                                : 'bg-white dark:bg-[#111827] border-gray-200 dark:border-[#273244]'"
                        >
                            {{-- Thumbnail --}}
                            <div class="w-16 h-16 rounded-lg bg-gray-100 dark:bg-gray-800 overflow-hidden flex-shrink-0 relative">
                                <template x-if="prop.cover_image">
                                    <img :src="prop.cover_image" :alt="prop.title" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!prop.cover_image">
                                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" /></svg>
                                    </div>
                                </template>
                            </div>

                            {{-- Details --}}
                            <div class="flex-1 min-w-0">
                                <h4 class="font-semibold text-xs text-gray-900 dark:text-white truncate group-hover:text-orange-600 transition-colors" x-text="prop.title"></h4>
                                <div class="flex items-center gap-2 mt-0.5 text-[11px] text-gray-500 dark:text-gray-400">
                                    <span class="font-bold text-orange-600 dark:text-orange-400" x-text="prop.price ? (prop.price >= 1000000 ? (prop.price / 1000000).toFixed(1) + 'M' : (prop.price / 1000).toFixed(0) + 'K') + ' ETB' : 'Contact'"></span>
                                    <span>·</span>
                                    <span class="font-mono text-gray-400" x-text="prop.distance + ' km away'"></span>
                                </div>
                                <div class="text-[10px] text-gray-400 mt-1 flex items-center gap-2">
                                    <span x-show="prop.bedrooms" x-text="prop.bedrooms + ' Beds'"></span>
                                    <span x-show="prop.bathrooms" x-text="prop.bathrooms + ' Baths'"></span>
                                    <span x-show="prop.building_area" x-text="prop.building_area + ' m²'"></span>
                                </div>
                            </div>

                            <a :href="'/dashboard/properties/' + prop.id + '/media'" class="p-1.5 text-gray-400 hover:text-orange-600 dark:hover:text-orange-400 rounded-lg flex-shrink-0" title="Media Gallery">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                            </a>
                        </div>
                    </template>

                    <div x-show="properties.length === 0 && !loading" class="py-12 text-center text-gray-400" x-cloak>
                        <p class="text-xs">No properties found within <span x-text="radiusKm"></span> km of the selected center point.</p>
                        <p class="text-[11px] text-gray-500 mt-1">Try expanding the search radius slider above.</p>
                    </div>
                </div>
            </div>

        </div>

    </div>
</x-layouts.dashboard>
