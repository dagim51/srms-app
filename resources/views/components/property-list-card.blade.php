@props([
    'listing' => null,
    'property' => null,
    'isFeatured' => false,
])

@php
    $targetListing = $listing;
    $targetProperty = $property ?? ($targetListing ? $targetListing->property : null);

    if (!$targetListing && $targetProperty && $targetProperty->relationLoaded('listings') && $targetProperty->listings->count() > 0) {
        $targetListing = $targetProperty->listings->first();
    }

    $title = $targetProperty ? $targetProperty->title : 'Verified Property in Addis Ababa';
    $code = $targetProperty ? $targetProperty->property_code : 'PROP';
    $propId = $targetListing ? $targetListing->id : ($targetProperty ? $targetProperty->id : 1);
    
    $priceFormatted = $targetListing ? $targetListing->formatted_price : 'ETB ' . number_format($targetProperty->price ?? 15000000);
    $purpose = $targetListing ? $targetListing->purpose : ($targetProperty->purpose ?? 'For Sale');

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

    $badgeColors = match($purpose) {
        'For Sale' => 'bg-primary-600 text-white',
        'For Rent' => 'bg-blue-600 text-white',
        'Short Stay' => 'bg-purple-600 text-white',
        'Land' => 'bg-emerald-600 text-white',
        'Development' => 'bg-amber-600 text-white',
        default => 'bg-gray-800 text-white',
    };

    $priceSuffix = match($purpose) {
        'For Rent' => '/month',
        'Short Stay' => '/night',
        default => '',
    };

    $bedrooms = $targetProperty ? ($targetProperty->bedrooms ?? null) : null;
    $bathrooms = $targetProperty ? ($targetProperty->bathrooms ?? null) : null;
    $area = $targetProperty ? ($targetProperty->building_area ?? $targetProperty->land_area ?? null) : null;
    $isVerified = $targetProperty && $targetProperty->status === 'verified';
@endphp

<article class="bg-white dark:bg-[#1E293B] border border-gray-200 dark:border-gray-800 rounded-2xl overflow-hidden shadow-sm hover:shadow-lg hover:border-primary-500/50 transition-all duration-200 group flex flex-col md:flex-row relative"
         data-listing-id="{{ $propId }}"
         data-lat="{{ $targetProperty->latitude ?? '' }}"
         data-lng="{{ $targetProperty->longitude ?? '' }}">

    {{-- Property Thumbnail --}}
    <div class="relative md:w-72 lg:w-80 h-52 md:h-auto flex-shrink-0 overflow-hidden bg-gray-100 dark:bg-gray-800">
        <img src="{{ $cover }}" 
             alt="{{ $title }}" 
             loading="lazy"
             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out">

        {{-- Badges on image --}}
        <div class="absolute top-3 left-3 z-10 flex flex-wrap gap-1.5">
            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold shadow-md {{ $badgeColors }}">
                {{ $purpose }}
            </span>
            @if($isVerified)
                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold shadow-md bg-emerald-600 text-white flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                    Verified
                </span>
            @endif
        </div>

        <span class="absolute bottom-3 left-3 text-[10px] font-mono font-bold bg-black/70 backdrop-blur-sm text-gray-200 px-2 py-0.5 rounded-md">
            {{ $code }}
        </span>
    </div>

    {{-- Details & Actions --}}
    <div class="p-5 sm:p-6 flex-1 flex flex-col justify-between">
        <div>
            {{-- Top Row: Location & Price --}}
            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-2 mb-2">
                <div>
                    <p class="text-xs font-semibold text-primary-600 dark:text-primary-400 flex items-center gap-1 mb-1">
                        <svg class="w-3.5 h-3.5 text-primary-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                        <span>Addis Ababa, Ethiopia</span>
                    </p>
                    <h3 class="font-display font-bold text-gray-900 dark:text-white text-lg line-clamp-1 group-hover:text-primary-600 transition-colors">
                        <a href="{{ route('properties.show', $propId) }}" class="focus:outline-none">
                            {{ $title }}
                        </a>
                    </h3>
                </div>

                <div class="text-left sm:text-right flex-shrink-0">
                    <p class="font-display font-black text-gray-900 dark:text-white text-xl sm:text-2xl tracking-tight leading-none text-primary-600 dark:text-primary-400">
                        {{ $priceFormatted }}
                    </p>
                    @if($priceSuffix)
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $priceSuffix }}</span>
                    @endif
                </div>
            </div>

            {{-- Description --}}
            @if($targetProperty && $targetProperty->description)
                <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 line-clamp-2 mb-4 leading-relaxed">
                    {{ $targetProperty->description }}
                </p>
            @endif

            {{-- Specs Pill Grid --}}
            <div class="flex flex-wrap items-center gap-4 text-xs text-gray-700 dark:text-gray-300">
                @if($bedrooms)
                    <div class="flex items-center gap-1.5 bg-gray-50 dark:bg-gray-800/80 px-2.5 py-1.5 rounded-lg border border-gray-100 dark:border-gray-800">
                        <svg class="w-4 h-4 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                        <span><strong>{{ $bedrooms }}</strong> Bedrooms</span>
                    </div>
                @endif

                @if($bathrooms)
                    <div class="flex items-center gap-1.5 bg-gray-50 dark:bg-gray-800/80 px-2.5 py-1.5 rounded-lg border border-gray-100 dark:border-gray-800">
                        <svg class="w-4 h-4 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        <span><strong>{{ $bathrooms }}</strong> Bathrooms</span>
                    </div>
                @endif

                @if($area)
                    <div class="flex items-center gap-1.5 bg-gray-50 dark:bg-gray-800/80 px-2.5 py-1.5 rounded-lg border border-gray-100 dark:border-gray-800">
                        <svg class="w-4 h-4 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" /></svg>
                        <span><strong>{{ $area }}</strong> m² Total Area</span>
                    </div>
                @endif

                @if($targetProperty && $targetProperty->amenities->contains('code', 'ample_parking'))
                    <div class="flex items-center gap-1.5 bg-gray-50 dark:bg-gray-800/80 px-2.5 py-1.5 rounded-lg border border-gray-100 dark:border-gray-800">
                        <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h4a3 3 0 013 3v0a3 3 0 01-3 3H8V7zm0 6v5" /></svg>
                        <span>Parking</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Bottom Action Row --}}
        <div class="mt-5 pt-4 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
            <div class="flex items-center gap-2">
                @if($targetProperty && $targetProperty->amenities->count() > 0)
                    <span class="text-[11px] text-gray-500 dark:text-gray-400">
                        Includes: {{ $targetProperty->amenities->take(2)->pluck('name_en')->implode(', ') }}
                        @if($targetProperty->amenities->count() > 2)
                            +{{ $targetProperty->amenities->count() - 2 }} more
                        @endif
                    </span>
                @endif
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('properties.show', $propId) }}" class="btn btn-primary btn-sm flex items-center gap-1.5">
                    <span>View Property</span>
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                </a>
            </div>
        </div>
    </div>
</article>
