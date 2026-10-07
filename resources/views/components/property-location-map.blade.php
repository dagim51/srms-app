@props([
    'latitude' => 9.0012,
    'longitude' => 38.7845,
    'title' => 'Property Location',
    'address' => '',
    'approximate' => false,
    'height' => 'h-72',
])

@php
    $mapId = 'prop_map_' . uniqid();
@endphp

<div x-data="{
    lat: {{ $latitude }},
    lng: {{ $longitude }},
    approximate: {{ $approximate ? 'true' : 'false' }},
    map: null,
    mapFailed: false,

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
                const existing = document.getElementById('leaflet-js');
                existing.addEventListener('load', () => resolve());
            }
        });
    },

    initMap() {
        try {
            const container = document.getElementById('{{ $mapId }}');
            if (!container) return;

            this.map = L.map(container, {
                center: [this.lat, this.lng],
                zoom: this.approximate ? 13 : 15,
                zoomControl: true,
                attributionControl: false
            });

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19
            }).addTo(this.map);

            if (this.approximate) {
                // Approximate Circle for privacy
                L.circle([this.lat, this.lng], {
                    color: '#F97316',
                    fillColor: '#FB923C',
                    fillOpacity: 0.25,
                    radius: 500
                }).addTo(this.map);
            } else {
                // Exact Orange Pin
                const icon = L.divIcon({
                    className: 'custom-map-pin',
                    html: '<div style=\"background-color: #F97316; width: 30px; height: 30px; border-radius: 50%; border: 3px solid white; box-shadow: 0 4px 12px rgba(0,0,0,0.35); display: flex; align-items: center; justify-content: center;\"><div style=\"width: 8px; height: 8px; background-color: white; border-radius: 50%;\"></div></div>',
                    iconSize: [30, 30],
                    iconAnchor: [15, 15]
                });

                L.marker([this.lat, this.lng], { icon: icon })
                    .addTo(this.map)
                    .bindPopup('<strong>{{ addslashes($title) }}</strong><br><span style=\"font-size: 11px;\">{{ addslashes($address) }}</span>');
            }

            setTimeout(() => {
                this.map.invalidateSize();
            }, 300);
        } catch (e) {
            this.mapFailed = true;
        }
    }
}" class="space-y-3 font-sans">

    {{-- Map Frame --}}
    <div class="relative w-full {{ $height }} rounded-2xl overflow-hidden border border-gray-200 dark:border-[#273244] shadow-xs">
        <div id="{{ $mapId }}" class="w-full h-full z-10" x-show="!mapFailed"></div>

        <div x-show="mapFailed" class="w-full h-full flex flex-col items-center justify-center p-6 bg-gray-100 dark:bg-gray-800 text-center" x-cloak>
            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $title }}</p>
            <p class="text-xs text-gray-500 font-mono mt-1">{{ $latitude }}° N, {{ $longitude }}° E</p>
            @if($address)
                <p class="text-xs text-gray-400 mt-1">{{ $address }}</p>
            @endif
        </div>

        {{-- Approximate Privacy Badge if active --}}
        @if($approximate)
            <div class="absolute top-3 left-3 z-20 bg-amber-50 dark:bg-amber-950/80 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800/60 px-2.5 py-1 rounded-full text-[11px] font-medium shadow-xs flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                Approximate Neighborhood Area
            </div>
        @endif
    </div>

    {{-- Directions & Coordinates Info --}}
    <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 px-1">
        <div class="font-mono text-[11px]">
            <span>{{ $latitude }}° N, {{ $longitude }}° E</span>
        </div>
        <div>
            <a 
                href="https://www.google.com/maps/dir/?api=1&destination={{ $latitude }},{{ $longitude }}" 
                target="_blank" 
                rel="noopener"
                class="text-orange-600 hover:text-orange-700 dark:text-orange-400 font-medium inline-flex items-center gap-1"
            >
                <span>Get Directions</span>
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
            </a>
        </div>
    </div>

</div>
