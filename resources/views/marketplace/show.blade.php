<x-layouts.app>
    <x-slot name="title">{{ $listing->property->title ?? 'Property Detail' }} | SRMS Ethiopia</x-slot>
    <x-slot name="description">{{ Str::limit($listing->property->description ?? 'Discover this verified property on SRMS Ethiopia.', 150) }}</x-slot>

    @php
        $property = $listing->property;
        $mediaList = $property ? $property->media : collect();
        $primaryImg = $mediaList->first() 
            ? ($mediaList->first()->url ?? $mediaList->first()->storage_key) 
            : 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=1200&q=80';
        
        $purposeColor = match($listing->purpose) {
            'For Sale' => 'bg-primary-600 text-white',
            'For Rent' => 'bg-blue-600 text-white',
            'Short Stay' => 'bg-purple-600 text-white',
            'Land' => 'bg-emerald-600 text-white',
            'Development' => 'bg-amber-600 text-white',
            default => 'bg-gray-800 text-white',
        };

        $priceSuffix = match($listing->purpose) {
            'For Rent' => '/mo',
            'Short Stay' => '/night',
            default => '',
        };
    @endphp

    <div class="py-10 page-container min-h-screen space-y-8 pt-24" x-data="{ activeMedia: '{{ $primaryImg }}', inquirySent: false }">
        {{-- Breadcrumbs --}}
        <nav class="flex items-center gap-2 text-xs text-gray-500">
            <a href="/" class="hover:text-primary-600 transition-colors">Home</a>
            <span>/</span>
            <a href="{{ route('properties.index') }}" class="hover:text-primary-600 transition-colors">Properties</a>
            <span>/</span>
            <a href="{{ route('properties.index', ['purpose' => $listing->purpose]) }}" class="hover:text-primary-600 transition-colors">{{ $listing->purpose }}</a>
            <span>/</span>
            <span class="text-gray-900 dark:text-white font-medium truncate max-w-xs">{{ $property->title ?? 'Details' }}</span>
        </nav>

        {{-- 1. HERO PHOTO GALLERY & LIGHTBOX --}}
        <div class="space-y-3">
            <div class="relative aspect-[16/9] md:aspect-[21/9] rounded-3xl overflow-hidden bg-gray-900 shadow-xl border border-gray-200 dark:border-gray-800">
                <img :src="activeMedia" alt="{{ $property->title ?? 'Property' }}" class="w-full h-full object-cover transition-all duration-500">
                
                {{-- Badges on Gallery --}}
                <div class="absolute top-4 left-4 flex gap-2">
                    <span class="px-3.5 py-1.5 rounded-full text-xs font-bold shadow-lg {{ $purposeColor }}">
                        {{ $listing->purpose }}
                    </span>
                    <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-black/60 backdrop-blur-md text-white border border-white/20">
                        {{ $property->property_code ?? 'PROP' }}
                    </span>
                    <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-500/90 backdrop-blur-md text-white flex items-center gap-1 shadow-sm">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        Verified Title
                    </span>
                </div>

                {{-- Photo Count Pill --}}
                <div class="absolute bottom-4 right-4 px-3.5 py-1.5 rounded-xl bg-black/70 backdrop-blur-md text-white text-xs font-bold flex items-center gap-1.5 shadow-md">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    <span>{{ $mediaList->count() }} Photos</span>
                </div>
            </div>

            {{-- Thumbnails Strip --}}
            @if($mediaList->count() > 1)
                <div class="grid grid-cols-4 sm:grid-cols-6 lg:grid-cols-8 gap-2.5">
                    @foreach($mediaList as $m)
                        @php
                            $imgUrl = $m->url ?? $m->storage_key;
                        @endphp
                        <button type="button" @click="activeMedia = '{{ $imgUrl }}'" 
                                class="relative aspect-video rounded-xl overflow-hidden border-2 transition-all cursor-pointer focus:outline-none"
                                :class="activeMedia === '{{ $imgUrl }}' ? 'border-primary-500 ring-2 ring-primary-500/20' : 'border-gray-200 dark:border-gray-800 opacity-70 hover:opacity-100'">
                            <img src="{{ $imgUrl }}" class="w-full h-full object-cover" alt="">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- 2. MAIN CONTENT & STICKY INQUIRY SIDEBAR --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            {{-- Left Column: Property Full Description & Technical Details --}}
            <div class="lg:col-span-8 space-y-8">
                {{-- Title & Price Banner --}}
                <div class="bg-white dark:bg-[#1E293B] border border-gray-200 dark:border-gray-800 rounded-3xl p-6 sm:p-8 shadow-sm space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold text-primary-600 dark:text-primary-400 flex items-center gap-1.5 mb-2">
                                <svg class="w-4 h-4 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.242-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                Addis Ababa, Ethiopia
                            </p>
                            <h1 class="text-2xl sm:text-3xl font-display font-extrabold text-gray-900 dark:text-white tracking-tight">
                                {{ $property->title ?? 'Untitled Property' }}
                            </h1>
                        </div>

                        <div class="sm:text-right">
                            <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Asking Price</span>
                            <p class="text-2xl sm:text-3xl font-display font-black text-primary-600 dark:text-primary-400 leading-tight">
                                {{ $listing->formatted_price }}
                                @if($priceSuffix)
                                    <span class="text-sm font-normal text-gray-400">{{ $priceSuffix }}</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    {{-- Spec Pills Grid --}}
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-6 border-t border-gray-100 dark:border-gray-800 text-center">
                        <div class="bg-gray-50 dark:bg-gray-800/50 p-4 rounded-2xl">
                            <span class="text-xs text-gray-400 font-medium">Bedrooms</span>
                            <p class="text-xl font-bold font-display text-gray-900 dark:text-white mt-0.5">{{ $property->bedrooms ?? '-' }}</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-800/50 p-4 rounded-2xl">
                            <span class="text-xs text-gray-400 font-medium">Bathrooms</span>
                            <p class="text-xl font-bold font-display text-gray-900 dark:text-white mt-0.5">{{ $property->bathrooms ?? '-' }}</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-800/50 p-4 rounded-2xl">
                            <span class="text-xs text-gray-400 font-medium">Building Area</span>
                            <p class="text-xl font-bold font-display text-gray-900 dark:text-white mt-0.5">{{ $property->building_area ? $property->building_area . ' m²' : '-' }}</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-800/50 p-4 rounded-2xl">
                            <span class="text-xs text-gray-400 font-medium">Land Area</span>
                            <p class="text-xl font-bold font-display text-gray-900 dark:text-white mt-0.5">{{ $property->land_area ? $property->land_area . ' m²' : '-' }}</p>
                        </div>
                    </div>
                </div>

                {{-- Architectural Description --}}
                <div class="bg-white dark:bg-[#1E293B] border border-gray-200 dark:border-gray-800 rounded-3xl p-6 sm:p-8 shadow-sm space-y-4">
                    <h2 class="text-lg font-bold font-display text-gray-900 dark:text-white">
                        About This Property
                    </h2>
                    <div class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed whitespace-pre-line">
                        {{ $property->description ?? 'No description provided.' }}
                    </div>
                </div>

                {{-- Verified Features & Amenities --}}
                <div class="bg-white dark:bg-[#1E293B] border border-gray-200 dark:border-gray-800 rounded-3xl p-6 sm:p-8 shadow-sm space-y-4">
                    <h2 class="text-lg font-bold font-display text-gray-900 dark:text-white">
                        Features & Amenities
                    </h2>
                    @if($property->amenities->count() > 0 || $property->features->count() > 0)
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs">
                            @foreach($property->amenities as $am)
                                <div class="p-3 rounded-xl bg-emerald-50/50 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-900/40 text-emerald-900 dark:text-emerald-300 flex items-center gap-2 font-medium">
                                    <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                                    <span>{{ $am->name }}</span>
                                </div>
                            @endforeach
                            @foreach($property->features as $ft)
                                <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 flex items-center gap-2 font-medium">
                                    <span class="text-primary-500 font-bold">•</span>
                                    <span>{{ $ft->name }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs">
                            <div class="p-3 rounded-xl bg-emerald-50/50 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-900/40 text-emerald-900 dark:text-emerald-300 flex items-center gap-2 font-medium">
                                <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                                <span>Backup Generator</span>
                            </div>
                            <div class="p-3 rounded-xl bg-emerald-50/50 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-900/40 text-emerald-900 dark:text-emerald-300 flex items-center gap-2 font-medium">
                                <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                                <span>Reserve Water Tank</span>
                            </div>
                            <div class="p-3 rounded-xl bg-emerald-50/50 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-900/40 text-emerald-900 dark:text-emerald-300 flex items-center gap-2 font-medium">
                                <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                                <span>24/7 Gated Security</span>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Location Map --}}
                @if($property->latitude && $property->longitude)
                    <div class="bg-white dark:bg-[#1E293B] border border-gray-200 dark:border-gray-800 rounded-3xl p-6 sm:p-8 shadow-sm space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-lg font-bold font-display text-gray-900 dark:text-white">Location & Neighborhood</h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Approximate privacy circle protected location</p>
                            </div>
                            <a href="https://maps.google.com/?q={{ $property->latitude }},{{ $property->longitude }}" target="_blank" class="btn btn-outline text-xs font-bold">
                                Open in Google Maps
                            </a>
                        </div>
                        <x-property-location-map :latitude="$property->latitude" :longitude="$property->longitude" :title="$property->title" />
                    </div>
                @endif
            </div>

            {{-- Right Column: Sticky Contact & Inquiry Console --}}
            <div class="lg:col-span-4 space-y-6 sticky top-24">
                <div class="bg-white dark:bg-[#1E293B] border border-gray-200 dark:border-gray-800 rounded-3xl p-6 shadow-xl space-y-6">
                    {{-- Verified Agent Badge --}}
                    <div class="flex items-center gap-3 pb-5 border-b border-gray-100 dark:border-gray-800">
                        <div class="w-12 h-12 rounded-2xl bg-orange-100 dark:bg-orange-950/40 text-primary-600 dark:text-primary-400 flex items-center justify-center font-bold text-base flex-shrink-0">
                            SR
                        </div>
                        <div>
                            <div class="flex items-center gap-1.5">
                                <h3 class="font-bold text-sm text-gray-900 dark:text-white">SRMS Certified Agent</h3>
                                <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Response time: ~15 mins</p>
                        </div>
                    </div>

                    {{-- Inquiry Success Notice --}}
                    <div x-show="inquirySent" x-cloak class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 text-xs text-emerald-800 dark:text-emerald-300 space-y-1">
                        <p class="font-bold">✓ Inquiry Dispatched!</p>
                        <p class="leading-relaxed">The dedicated agent has received your request and will contact you via phone or WhatsApp.</p>
                    </div>

                    {{-- Fast Inquiry Form --}}
                    <form @submit.prevent="inquirySent = true" x-show="!inquirySent" class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Your Full Name</label>
                            <input type="text" required placeholder="Abebe Bikila" class="form-input w-full text-xs">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Phone Number (Ethiopia)</label>
                            <input type="tel" required placeholder="+251 91 123 4567" class="form-input w-full text-xs">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Message / Request</label>
                            <textarea rows="3" class="form-input w-full text-xs leading-relaxed" 
                                      placeholder="I am interested in {{ $property->title ?? 'this property' }} and would like to schedule an inspection viewing."></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-full text-xs font-bold py-3 rounded-xl shadow-md">
                            Send Inquiry to Agent
                        </button>
                    </form>

                    {{-- WhatsApp / Direct Call Buttons --}}
                    <div class="space-y-2 pt-2 border-t border-gray-100 dark:border-gray-800">
                        <a href="tel:+251911000000" class="btn btn-outline w-full text-xs font-bold flex items-center justify-center gap-2">
                            <svg class="w-4 h-4 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                            <span>Call Agent (+251 91 100 0000)</span>
                        </a>
                        <a href="https://wa.me/251911000000" target="_blank" class="btn btn-outline w-full text-xs font-bold flex items-center justify-center gap-2 bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800">
                            <span>Chat on WhatsApp</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. SIMILAR / NEARBY PROPERTIES SECTION --}}
        @if($similarListings->count() > 0)
            <div class="pt-12 border-t border-gray-200 dark:border-gray-800 space-y-6">
                <div>
                    <h2 class="text-2xl font-display font-extrabold text-gray-900 dark:text-white">
                        Similar {{ $listing->purpose }} Properties
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Explore related verified properties in the same category</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($similarListings as $sim)
                        <x-property-card :listing="$sim" />
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>
