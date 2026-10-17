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

    // Purpose badge styling (Modern Multicolored System)
    $badgeColors = match($purpose) {
        'For Sale' => 'bg-gradient-to-r from-orange-500 to-amber-500 text-white shadow-xs shadow-orange-500/30',
        'For Rent' => 'bg-gradient-to-r from-emerald-600 to-teal-500 text-white shadow-xs shadow-emerald-500/30',
        'Short Stay' => 'bg-gradient-to-r from-violet-600 to-indigo-600 text-white shadow-xs shadow-violet-500/30',
        'Land' => 'bg-gradient-to-r from-amber-500 to-yellow-600 text-white shadow-xs shadow-amber-500/30',
        'Development' => 'bg-gradient-to-r from-sky-500 to-blue-600 text-white shadow-xs shadow-sky-500/30',
        default => 'bg-slate-800 text-white',
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

<article class="property-card card card-hover bg-white dark:bg-slate-900 rounded-3xl overflow-hidden border border-slate-200/80 dark:border-slate-800 hover:border-orange-500/40 shadow-sm hover:shadow-2xl transition-all duration-500 flex flex-col justify-between group h-full relative"
         x-data="{ favorited: false, loading: false }">

    <div>
        {{-- Image Container with Badges & Favorite Button --}}
        <div class="relative aspect-[16/10] overflow-hidden bg-slate-900">
            <a href="/properties/{{ $propId }}" class="block w-full h-full">
                <img src="{{ $cover }}" 
                     alt="{{ $title }}" 
                     loading="lazy"
                     onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=800&q=80';"
                     class="property-img w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
            </a>
            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/85 via-slate-950/20 to-transparent pointer-events-none"></div>

            {{-- Status & Type Badges --}}
            <div class="absolute top-4 left-4 z-10 flex items-center gap-2">
                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider {{ $purpose === 'For Sale' ? 'bg-orange-600 text-white' : 'bg-amber-500 text-slate-950' }} shadow-md">
                    {{ __($purpose) }}
                </span>
                @if($isFeatured)
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-white/90 dark:bg-slate-900/90 text-orange-600 dark:text-orange-400 backdrop-blur-md shadow-sm">
                        ★ {{ __('Featured') }}
                    </span>
                @endif
            </div>

            {{-- Save / Favorite Heart Button --}}
            <button @click.prevent="
                        if (loading) return;
                        loading = true;
                        favorited = !favorited;
                        fetch('/favorites/toggle/{{ $targetProperty->id ?? 1 }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']')?.getAttribute('content') || '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        })
                        .then(r => r.json())
                        .then(d => { favorited = d.favorited; loading = false; })
                        .catch(() => { loading = false; })
                    " 
                    type="button" 
                    class="absolute top-4 right-4 z-20 w-9 h-9 rounded-full bg-white/85 dark:bg-slate-900/85 text-slate-700 dark:text-slate-200 hover:text-orange-600 backdrop-blur-md flex items-center justify-center hover:scale-110 transition-all shadow-md cursor-pointer" 
                    aria-label="Save Property">
                <svg class="w-4 h-4 transition-colors duration-200" :class="favorited ? 'text-rose-500 fill-rose-500' : 'text-slate-700 dark:text-slate-200'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                </svg>
            </button>

            {{-- Price Overlay --}}
            <div class="absolute bottom-4 left-4 right-4 flex items-end justify-between text-white pointer-events-none">
                <div>
                    <span class="font-serif text-2xl font-bold tracking-tight drop-shadow-md text-white">
                        {{ $priceFormatted }}
                        @if($priceSuffix)
                            <span class="text-xs font-normal text-slate-200">{{ $priceSuffix }}</span>
                        @endif
                    </span>
                </div>
                <span class="text-xs font-bold bg-orange-500/90 text-white px-2.5 py-1 rounded-lg shadow-sm">
                    {{ $code }}
                </span>
            </div>
        </div>

        {{-- Card Content --}}
        <div class="p-6">
            <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 mb-2">
                <svg class="w-3.5 h-3.5 text-orange-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                <span>{{ __('Addis Ababa, Ethiopia') }}</span>
            </div>

            <h3 class="font-serif text-xl font-bold text-slate-900 dark:text-white group-hover:text-orange-600 dark:group-hover:text-orange-400 transition-colors line-clamp-1">
                <a href="/properties/{{ $propId }}">
                    {{ $title }}
                </a>
            </h3>

            @if($targetProperty && $targetProperty->description)
                <p class="text-slate-500 dark:text-slate-400 text-xs mt-2 line-clamp-2 leading-relaxed">
                    {{ $targetProperty->description }}
                </p>
            @endif
        </div>
    </div>

    {{-- Specs & Details Footer --}}
    <div class="px-6 pb-6 pt-4 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between text-xs text-slate-600 dark:text-slate-300">
        <div class="flex items-center gap-4">
            @if($bedrooms)
                <span class="flex items-center gap-1.5" title="{{ __('Bedrooms') }}">
                    <strong class="text-slate-900 dark:text-white font-bold">{{ $bedrooms }}</strong> {{ __('Beds') }}
                </span>
            @endif
            @if($bathrooms)
                <span class="flex items-center gap-1.5" title="{{ __('Bathrooms') }}">
                    <strong class="text-slate-900 dark:text-white font-bold">{{ $bathrooms }}</strong> {{ __('Baths') }}
                </span>
            @endif
            @if($area)
                <span class="flex items-center gap-1.5" title="{{ __('Area') }}">
                    <strong class="text-slate-900 dark:text-white font-bold">{{ $area }}</strong> m²
                </span>
            @endif
        </div>

        <a href="/properties/{{ $propId }}" class="text-orange-600 dark:text-orange-400 font-semibold hover:underline flex items-center gap-1">
            <span>{{ __('Details') }}</span>
            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
        </a>
    </div>
</article>
