@props([
    'listing' => null,
    'property' => null,
    'isFeatured' => false,
])

@php
    // Normalize listing vs property input
    $targetListing = $listing;
    $targetProperty = $property ?? ($targetListing ? $targetListing->property : null);
    
    // Fallback if targetProperty itself is passed with an active listing
    if (!$targetListing && $targetProperty && $targetProperty->relationLoaded('listings') && $targetProperty->listings->count() > 0) {
        $targetListing = $targetProperty->listings->first();
    }

    $title = $targetProperty ? $targetProperty->title : 'Luxury Property in Addis Ababa';
    $code = $targetProperty ? $targetProperty->property_code : 'PROP';
    $propId = $targetListing ? $targetListing->id : ($targetProperty ? $targetProperty->id : 1);
    
    // Price calculation
    $priceFormatted = $targetListing ? $targetListing->formatted_price : 'ETB ' . number_format($targetProperty->price ?? 15000000);
    $purpose = $targetListing ? $targetListing->purpose : ($targetProperty->purpose ?? 'For Sale');

    // Cover Image
    $cover = null;
    if ($targetProperty) {
        $mediaItem = $targetProperty->relationLoaded('media') 
            ? $targetProperty->media->first() 
            : $targetProperty->media()->first();
        if ($mediaItem) {
            $cover = $mediaItem->url ?? $mediaItem->storage_key;
        }
    }
    if (!$cover) {
        $cover = 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=800&q=80';
    }

    // Purpose badge styling
    $badgeColors = match($purpose) {
        'For Sale' => 'bg-primary-600 text-white',
        'For Rent' => 'bg-blue-600 text-white',
        'Short Stay' => 'bg-purple-600 text-white',
        'Land' => 'bg-emerald-600 text-white',
        'Development' => 'bg-amber-600 text-white',
        default => 'bg-gray-800 text-white',
    };

    $priceSuffix = match($purpose) {
        'For Rent' => '/mo',
        'Short Stay' => '/night',
        default => '',
    };

    $bedrooms = $targetProperty ? ($targetProperty->bedrooms ?? null) : null;
    $bathrooms = $targetProperty ? ($targetProperty->bathrooms ?? null) : null;
    $area = $targetProperty ? ($targetProperty->building_area ?? $targetProperty->land_area ?? null) : null;
@endphp

<div class="bg-white dark:bg-[#1E293B] border border-gray-200 dark:border-gray-800 rounded-2xl overflow-hidden shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group flex flex-col h-full relative"
     x-data="{ favorited: false }">

    {{-- Badges --}}
    <div class="absolute top-3.5 left-3.5 z-10 flex flex-wrap gap-1.5 pointer-events-none">
        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold shadow-md {{ $badgeColors }}">
            {{ $purpose }}
        </span>
        @if($isFeatured)
            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold shadow-md bg-white/95 dark:bg-gray-900/90 text-primary-600 dark:text-primary-400 backdrop-blur-sm border border-primary-500/30">
                ★ Featured
            </span>
        @endif
    </div>

    {{-- Save / Favorite Heart Button --}}
    <button @click.prevent="favorited = !favorited" 
            type="button" 
            class="absolute top-3.5 right-3.5 z-20 w-8 h-8 rounded-full bg-black/40 hover:bg-black/60 backdrop-blur-md text-white flex items-center justify-center transition-all shadow-md focus:outline-none" 
            aria-label="Save to favorites">
        <svg class="w-4 h-4 transition-colors" :class="favorited ? 'text-rose-500 fill-rose-500' : 'text-white'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
        </svg>
    </button>

    {{-- Property Image Container --}}
    <div class="relative aspect-[16/10] overflow-hidden bg-gray-100 dark:bg-gray-800">
        <img src="{{ $cover }}" 
             alt="{{ $title }}" 
             loading="lazy"
             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out">
        
        {{-- Price Gradient Overlay --}}
        <div class="absolute inset-x-0 bottom-0 p-4 bg-gradient-to-t from-gray-950/90 via-gray-950/50 to-transparent flex items-end justify-between">
            <div>
                <p class="font-display font-black text-white text-xl tracking-tight leading-none drop-shadow-sm">
                    {{ $priceFormatted }}
                    @if($priceSuffix)
                        <span class="text-xs font-normal text-gray-200">{{ $priceSuffix }}</span>
                    @endif
                </p>
            </div>
            <span class="text-[10px] font-mono font-bold bg-black/60 backdrop-blur-sm text-gray-300 px-2 py-0.5 rounded-md">
                {{ $code }}
            </span>
        </div>
    </div>

    {{-- Card Content --}}
    <div class="p-5 flex-1 flex flex-col justify-between">
        <div>
            {{-- Location --}}
            <p class="text-xs font-semibold text-primary-600 dark:text-primary-400 flex items-center gap-1 mb-1.5">
                <svg class="w-3.5 h-3.5 text-primary-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                <span>Addis Ababa, Ethiopia</span>
            </p>

            {{-- Title --}}
            <h3 class="font-display font-bold text-gray-900 dark:text-white text-base line-clamp-1 group-hover:text-primary-600 transition-colors">
                <a href="/properties/{{ $propId }}" class="focus:outline-none">
                    {{ $title }}
                </a>
            </h3>

            {{-- Short description preview --}}
            @if($targetProperty && $targetProperty->description)
                <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2 mt-1.5 leading-relaxed">
                    {{ $targetProperty->description }}
                </p>
            @endif
        </div>

        {{-- Spec Pills (Bedrooms, Bathrooms, Area) --}}
        <div class="mt-4 pt-3.5 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between text-xs text-gray-600 dark:text-gray-300">
            <div class="flex items-center gap-3">
                @if($bedrooms)
                    <span class="flex items-center gap-1" title="Bedrooms">
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                        <strong>{{ $bedrooms }}</strong> <span class="text-gray-400">bd</span>
                    </span>
                @endif
                @if($bathrooms)
                    <span class="flex items-center gap-1" title="Bathrooms">
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        <strong>{{ $bathrooms }}</strong> <span class="text-gray-400">ba</span>
                    </span>
                @endif
                @if($area)
                    <span class="flex items-center gap-1" title="Area">
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" /></svg>
                        <strong>{{ $area }}</strong> <span class="text-gray-400">m²</span>
                    </span>
                @endif
            </div>

            <a href="/properties/{{ $propId }}" class="text-[11px] font-bold text-primary-600 dark:text-primary-400 hover:underline flex items-center gap-0.5">
                Details
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            </a>
        </div>
    </div>
</div>
