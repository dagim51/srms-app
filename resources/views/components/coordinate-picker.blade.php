@props([
    'latitude' => null,
    'longitude' => null,
    'nameLat' => 'latitude',
    'nameLng' => 'longitude',
    'privacyName' => 'is_location_approximate',
    'approximate' => false,
])

@php
    $defaultLat = $latitude ?: 9.0105;
    $defaultLng = $longitude ?: 38.7612;
    $pickerId = 'coord_picker_' . uniqid();
@endphp

<div x-data="{
    lat: {{ $defaultLat }},
    lng: {{ $defaultLng }},
    hasCoords: {{ $latitude ? 'true' : 'false' }},
    isApproximate: {{ $approximate ? 'true' : 'false' }},
    map: null,
    marker: null,
    circle: null,
    mapLoaded: false,
    mapFailed: false,
    presets: [
        { name: 'Bole Atlas', lat: 9.0012, lng: 38.7845 },
        { name: 'Kazanchis', lat: 9.0180, lng: 38.7660 },
        { name: 'Old Airport / Sarbet', lat: 8.9950, lng: 38.7420 },
        { name: 'CMC Michael', lat: 9.0230, lng: 38.8350 },
        { name: 'Piassa / Arada', lat: 9.0350, lng: 38.7530 },
        { name: 'Summit Condominiums', lat: 9.0150, lng: 38.8550 },
        { name: 'Bisrate Gabriel', lat: 8.9850, lng: 38.7350 }
    ],

    init() {
        this.loadLeaflet().then(() => {
            this.initMap();
        }).catch(() => {
            this.mapFailed = true;
        });
    },

    loadLeaflet() {
        return new Promise((resolve, reject) => {
            if (window.L) {
                resolve();
                return;
            }

            // Load CSS
            if (!document.getElementById('leaflet-css')) {
                const link = document.createElement('link');
                link.id = 'leaflet-css';
                link.rel = 'stylesheet';
                link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                document.head.appendChild(link);
            }

            // Load JS
            if (!document.getElementById('leaflet-js')) {
                const script = document.createElement('script');
                script.id = 'leaflet-js';
                script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                script.onload = () => resolve();
                script.onerror = () => reject();
                document.head.appendChild(script);
            } else {
                const existing = document.getElementById('leaflet-js');
                existing.addEventListener('load', () => resolve());
            }
        });
    },

    initMap() {
        try {
            const container = document.getElementById('{{ $pickerId }}_map');
            if (!container) return;

            this.map = L.map(container, {
                center: [this.lat, this.lng],
                zoom: 14,
                zoomControl: true,
                attributionControl: false
            });

            // Modern OpenStreetMap / Carto tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19
            }).addTo(this.map);

            // Custom Orange Pin
            const orangeIcon = L.divIcon({
                className: 'custom-map-pin',
                html: `<div style='background-color: #F97316; width: 28px; height: 28px; border-radius: 50%; border: 3px solid white; box-shadow: 0 4px 10px rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center;'><div style='width: 8px; height: 8px; background-color: white; border-radius: 50%;'></div></div>`,
                iconSize: [28, 28],
                iconAnchor: [14, 14]
            });

            this.marker = L.marker([this.lat, this.lng], {
                draggable: true,
                icon: orangeIcon
            }).addTo(this.map);

            this.updatePrivacyCircle();

            this.marker.on('dragend', (e) => {
                const position = e.target.getLatLng();
                this.updateCoords(position.lat, position.lng);
            });

            this.map.on('click', (e) => {
                this.updateCoords(e.latlng.lat, e.latlng.lng);
            });

            this.mapLoaded = true;

            setTimeout(() => {
                this.map.invalidateSize();
            }, 300);
        } catch (err) {
            this.mapFailed = true;
        }
    },

    updateCoords(newLat, newLng) {
        this.lat = parseFloat(newLat.toFixed(6));
        this.lng = parseFloat(newLng.toFixed(6));
        this.hasCoords = true;

        if (this.marker) {
            this.marker.setLatLng([this.lat, this.lng]);
        }
        if (this.map) {
            this.map.panTo([this.lat, this.lng]);
        }
        this.updatePrivacyCircle();
    },

    setPreset(preset) {
        this.updateCoords(preset.lat, preset.lng);
    },

    updatePrivacyCircle() {
        if (!this.map) return;

        if (this.isApproximate) {
            if (!this.circle) {
                this.circle = L.circle([this.lat, this.lng], {
                    color: '#F97316',
                    fillColor: '#FB923C',
                    fillOpacity: 0.25,
                    radius: 400
                }).addTo(this.map);
            } else {
                this.circle.setLatLng([this.lat, this.lng]);
            }
        } else {
            if (this.circle) {
                this.map.removeLayer(this.circle);
                this.circle = null;
            }
        }
    },

    togglePrivacy() {
        this.isApproximate = !this.isApproximate;
        this.updatePrivacyCircle();
    },

    useCurrentLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    this.updateCoords(pos.coords.latitude, pos.coords.longitude);
                },
                (err) => {
                    alert('Could not access current location: ' + err.message);
                }
            );
        } else {
            alert('Geolocation is not supported by your browser.');
        }
    }
}" class="space-y-4 font-sans">

    {{-- Hidden Inputs for Form Submission --}}
    <input type="hidden" name="{{ $nameLat }}" :value="hasCoords ? lat : ''">
    <input type="hidden" name="{{ $nameLng }}" :value="hasCoords ? lng : ''">
    <input type="hidden" name="{{ $privacyName }}" :value="isApproximate ? '1' : '0'">

    {{-- Presets & Geolocation Toolbar --}}
    <div class="card flex flex-wrap items-center justify-between gap-3 bg-surface dark:bg-[#172033] p-3 rounded-xl border border-warm dark:border-[#273244]">
        <div class="flex flex-wrap items-center gap-1.5">
            <span class="text-xs font-semibold text-muted dark:text-muted mr-1">Quick Presets:</span>
            <template x-for="p in presets" :key="p.name">
                <button 
                    type="button" 
                    @click="setPreset(p)" 
                    class="text-xs px-2.5 py-1 rounded-lg border border-warm dark:border-warm bg-white dark:bg-[#111827] text-muted dark:text-dark-subtle hover:border-orange-500 hover:text-orange-600 dark:hover:text-orange-400 transition-colors cursor-pointer"
                    x-text="p.name"
                ></button>
            </template>
        </div>

        <div>
            <button 
                type="button" 
                @click="useCurrentLocation()" 
                class="inline-flex items-center gap-1.5 text-xs font-medium text-orange-600 dark:text-orange-400 hover:text-orange-700 px-2.5 py-1 rounded-lg border border-orange-200 dark:border-orange-800/60 bg-orange-50 dark:bg-orange-950/30 transition-colors cursor-pointer"
            >
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                </svg>
                <span>{{ __('My Location') }}</span>
            </button>
        </div>
    </div>

    {{-- Map Container with Touch Support --}}
    <div class="relative w-full h-80 rounded-2xl overflow-hidden border border-warm dark:border-[#273244] shadow-xs">
        {{-- Interactive Map --}}
        <div id="{{ $pickerId }}_map" class="w-full h-full z-10" x-show="!mapFailed"></div>

        {{-- Fallback Map Display if Tiles / Network Fail --}}
        <div x-show="mapFailed" class="w-full h-full flex flex-col items-center justify-center p-6 bg-surface-subtle dark:bg-surface text-center" x-cloak>
            <div class="w-12 h-12 rounded-full bg-orange-100 dark:bg-orange-950/40 text-orange-600 dark:text-orange-400 flex items-center justify-center mb-2">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" /></svg>
            </div>
            <h4 class="text-sm font-semibold text-muted dark:text-white">Coordinate Pin Mode (Offline / Fallback)</h4>
            <p class="text-xs text-muted dark:text-muted max-w-sm mt-1">{{ __('Map tiles could not load. You can enter precise GPS coordinates using the numeric input fields below.') }}</p>
        </div>

        {{-- Live Pin Coordinate Floating Badge --}}
        <div class="card absolute bottom-3 left-3 z-20 bg-white/95 dark:bg-[#111827]/95 backdrop-blur-xs px-3 py-1.5 rounded-lg border border-warm dark:border-warm text-xs font-mono text-muted dark:text-muted flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-orange-500 animate-pulse"></span>
            <span><span x-text="lat"></span>° N, <span x-text="lng"></span>° E</span>
        </div>
    </div>

    {{-- Coordinate Readout Inputs & Privacy Switch --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-center">
        <div>
            <x-label for="{{ $pickerId }}_lat">Latitude</x-label>
            <input 
                type="number" 
                id="{{ $pickerId }}_lat" 
                step="0.000001" 
                x-model.number="lat" 
                @input="updateCoords(lat, lng)" 
                class="form-input w-full font-mono text-xs" 
                placeholder="{{ __('e.g. 9.001200') }}"
            >
        </div>

        <div>
            <x-label for="{{ $pickerId }}_lng">Longitude</x-label>
            <input 
                type="number" 
                id="{{ $pickerId }}_lng" 
                step="0.000001" 
                x-model.number="lng" 
                @input="updateCoords(lat, lng)" 
                class="form-input w-full font-mono text-xs" 
                placeholder="{{ __('e.g. 38.784500') }}"
            >
        </div>

        {{-- Privacy Obfuscation Switch --}}
        <div class="sm:pt-5">
            <label class="flex items-center gap-2.5 cursor-pointer select-none">
                <input 
                    type="checkbox" 
                    x-model="isApproximate" 
                    @change="updatePrivacyCircle()"
                    class="rounded border-warm text-orange-600 focus:ring-orange-500 w-4 h-4 cursor-pointer"
                >
                <span class="text-xs text-muted dark:text-dark-subtle">
                    <strong class="font-medium text-muted dark:text-white">Obfuscate Pin</strong> (Show ~400m neighborhood area for privacy)
                </span>
            </label>
        </div>
    </div>

</div>
