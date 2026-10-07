<x-layouts.dashboard>
    <x-slot name="title">Listing Preview | {{ $listing->property->title ?? 'Listing' }}</x-slot>

    @section('page-title', 'Listing Preview')
    @section('breadcrumb')
        <a href="{{ route('dashboard.listings.index') }}" class="hover:text-gray-700 dark:hover:text-gray-300">Listings</a>
        <span class="mx-1.5 text-gray-300 dark:text-gray-600">/</span>
        <span class="text-gray-700 dark:text-gray-300">Preview</span>
    @endsection

    @php
        $property = $listing->property;
        $primaryMedia = $property && $property->media->first() 
            ? ($property->media->first()->url ?? $property->media->first()->storage_key) 
            : 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=1200&q=80';
        $gallery = $property ? $property->media : collect();
    @endphp

    <div class="max-w-6xl mx-auto page-enter pb-16 space-y-6">
        {{-- Preview Banner & Actions Bar --}}
        <div class="bg-gradient-to-r from-gray-900 to-gray-800 text-white rounded-2xl p-4 md:p-6 shadow-md flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-primary-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded text-[11px] font-bold uppercase tracking-wider bg-primary-500 text-white">Marketplace Preview</span>
                        <span class="text-xs text-gray-300">Status: <strong class="text-white">{{ ucfirst($listing->status) }}</strong></span>
                    </div>
                    <p class="text-xs text-gray-400 mt-0.5">This preview demonstrates how prospective buyers and tenants will see your listing.</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('dashboard.listings.edit', $listing) }}" class="btn btn-outline text-xs bg-white/5 border-white/20 text-white hover:bg-white/10">
                    Edit Listing
                </a>
                <form method="POST" action="{{ route('dashboard.listings.toggle-status', $listing) }}">
                    @csrf
                    @method('PATCH')
                    @if($listing->status === 'published')
                        <button type="submit" class="btn btn-outline text-xs bg-amber-500/20 border-amber-500/30 text-amber-300 hover:bg-amber-500/30">
                            Pause Listing
                        </button>
                    @else
                        <button type="submit" class="btn btn-primary text-xs">
                            Publish to Marketplace
                        </button>
                    @endif
                </form>
            </div>
        </div>

        {{-- Quality Score Header --}}
        <x-listing-quality :listing="$listing" />

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Main Column: Full Marketplace Detail Preview --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Hero Gallery --}}
                <div class="bg-white dark:bg-[#1E293B] border border-gray-200 dark:border-gray-800 rounded-2xl overflow-hidden shadow-sm">
                    <div class="relative aspect-video bg-gray-100 dark:bg-gray-800">
                        <img src="{{ $primaryMedia }}" class="w-full h-full object-cover" alt="{{ $property->title ?? 'Property' }}">
                        
                        <div class="absolute top-4 left-4 flex gap-2">
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-primary-600 text-white shadow-md">
                                {{ $listing->purpose }}
                            </span>
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-black/60 backdrop-blur-sm text-white">
                                {{ $property->property_code ?? 'PROP' }}
                            </span>
                        </div>

                        <div class="absolute bottom-4 right-4 px-3 py-1 rounded-lg bg-black/70 backdrop-blur-sm text-white text-xs font-medium">
                            📷 {{ $gallery->count() }} Photos
                        </div>
                    </div>

                    {{-- Gallery Thumbnails Strip --}}
                    @if($gallery->count() > 1)
                        <div class="p-4 grid grid-cols-4 sm:grid-cols-6 gap-2 border-t border-gray-100 dark:border-gray-800">
                            @foreach($gallery->take(6) as $m)
                                <img src="{{ $m->url ?? $m->storage_key }}" class="h-16 w-full object-cover rounded-lg border border-gray-200 dark:border-gray-700" alt="">
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Property Details & Description --}}
                <div class="bg-white dark:bg-[#1E293B] border border-gray-200 dark:border-gray-800 rounded-2xl p-6 shadow-sm space-y-6">
                    <div>
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h1 class="text-2xl font-display font-extrabold text-gray-900 dark:text-white">
                                    {{ $property->title ?? 'Untitled Property' }}
                                </h1>
                                <p class="text-sm text-gray-500 dark:text-gray-400 flex items-center gap-1.5 mt-1">
                                    <svg class="w-4 h-4 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.242-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    Addis Ababa, Ethiopia
                                </p>
                            </div>
                            <div class="text-right">
                                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Asking Price</span>
                                <p class="text-2xl font-display font-extrabold text-primary-600 dark:text-primary-400">
                                    {{ $listing->formatted_price }}
                                </p>
                            </div>
                        </div>

                        {{-- Core Spec Pills --}}
                        <div class="grid grid-cols-3 sm:grid-cols-4 gap-3 mt-6 pt-6 border-t border-gray-100 dark:border-gray-800 text-center">
                            <div class="bg-gray-50 dark:bg-gray-800/50 p-3 rounded-xl">
                                <span class="text-xs text-gray-500 dark:text-gray-400">Bedrooms</span>
                                <p class="font-bold text-gray-900 dark:text-white text-base mt-0.5">{{ $property->bedrooms ?? '-' }}</p>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-800/50 p-3 rounded-xl">
                                <span class="text-xs text-gray-500 dark:text-gray-400">Bathrooms</span>
                                <p class="font-bold text-gray-900 dark:text-white text-base mt-0.5">{{ $property->bathrooms ?? '-' }}</p>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-800/50 p-3 rounded-xl">
                                <span class="text-xs text-gray-500 dark:text-gray-400">Building Area</span>
                                <p class="font-bold text-gray-900 dark:text-white text-base mt-0.5">{{ $property->building_area ? $property->building_area . ' m²' : '-' }}</p>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-800/50 p-3 rounded-xl">
                                <span class="text-xs text-gray-500 dark:text-gray-400">Land Area</span>
                                <p class="font-bold text-gray-900 dark:text-white text-base mt-0.5">{{ $property->land_area ? $property->land_area . ' m²' : '-' }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Description --}}
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-2">About This Property</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed whitespace-pre-line">
                            {{ $property->description ?? 'No description provided.' }}
                        </p>
                    </div>

                    {{-- Features & Amenities --}}
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-3">Features & Amenities</h3>
                        @if($property->amenities->count() > 0 || $property->features->count() > 0)
                            <div class="flex flex-wrap gap-2">
                                @foreach($property->amenities as $am)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 border border-emerald-100 dark:border-emerald-800/40">
                                        <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                        {{ $am->name }}
                                    </span>
                                @endforeach
                                @foreach($property->features as $ft)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                                        {{ $ft->name }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <p class="text-xs text-gray-400 italic">No amenities specified yet.</p>
                        @endif
                    </div>

                    {{-- Location Map Display --}}
                    @if($property->latitude && $property->longitude)
                        <div>
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-3">Location & Neighborhood</h3>
                            <x-property-location-map :latitude="$property->latitude" :longitude="$property->longitude" :title="$property->title" />
                        </div>
                    @endif
                </div>
            </div>

            {{-- Sidebar Column: Marketplace Card Simulation --}}
            <div class="space-y-6">
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Search Result Card Simulation</h3>
                    
                    {{-- Exact Search Card View --}}
                    <div class="bg-white dark:bg-[#1E293B] border border-gray-200 dark:border-gray-800 rounded-2xl overflow-hidden shadow-md">
                        <div class="relative aspect-video bg-gray-100 dark:bg-gray-800">
                            <img src="{{ $primaryMedia }}" class="w-full h-full object-cover" alt="">
                            <div class="absolute top-3 left-3">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-primary-600 text-white shadow-sm">
                                    {{ $listing->purpose }}
                                </span>
                            </div>
                            <div class="absolute bottom-0 inset-x-0 p-3 bg-gradient-to-t from-gray-900/80 to-transparent">
                                <p class="font-display font-extrabold text-white text-lg">
                                    {{ $listing->formatted_price }}
                                </p>
                            </div>
                        </div>
                        <div class="p-4">
                            <h4 class="font-bold text-gray-900 dark:text-white text-sm line-clamp-1">
                                {{ $property->title }}
                            </h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.242-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                Addis Ababa
                            </p>
                            <div class="flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400 mt-3 pt-3 border-t border-gray-100 dark:border-gray-800">
                                <span>{{ $property->bedrooms ?? 0 }} Beds</span> •
                                <span>{{ $property->bathrooms ?? 0 }} Baths</span> •
                                <span>{{ $property->building_area ?? 0 }} m²</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Canonical Asset Info Box --}}
                <div class="bg-gray-50 dark:bg-gray-800/40 border border-gray-200 dark:border-gray-700/60 rounded-2xl p-5 space-y-3 text-xs">
                    <h4 class="font-bold text-gray-900 dark:text-white">Canonical Asset Relationship</h4>
                    <p class="text-gray-500 dark:text-gray-400 leading-relaxed">
                        This listing does not duplicate the real estate asset. It references canonical property code <span class="font-mono font-bold text-gray-800 dark:text-gray-200">{{ $property->property_code }}</span>.
                    </p>
                    <div class="pt-2 border-t border-gray-200 dark:border-gray-700 flex justify-between text-gray-500">
                        <span>Visibility:</span>
                        <span class="font-bold text-gray-900 dark:text-white capitalize">{{ $listing->visibility }}</span>
                    </div>
                    <div class="flex justify-between text-gray-500">
                        <span>Published Date:</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $listing->published_at ? date('M d, Y', strtotime($listing->published_at)) : 'Not Published' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.dashboard>
