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
        'For Sale' => 'bg-gradient-to-r from-orange-500 to-amber-500 text-white shadow-xs',
        'For Rent' => 'bg-gradient-to-r from-emerald-600 to-teal-500 text-white shadow-xs',
        'Short Stay' => 'bg-gradient-to-r from-violet-600 to-indigo-600 text-white shadow-xs',
        'Land' => 'bg-gradient-to-r from-amber-500 to-yellow-600 text-white shadow-xs',
        'Development' => 'bg-gradient-to-r from-sky-500 to-blue-600 text-white shadow-xs',
        default => 'bg-slate-800 text-white',
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

<article class="property-card card card-hover bg-white dark:bg-[#0D1322] border border-slate-200/80 dark:border-slate-800/80 rounded-xl overflow-hidden group flex flex-col md:flex-row relative"
         x-data="{ favorited: false, loading: false }"
         data-listing-id="{{ $propId }}"
         data-lat="{{ $targetProperty->latitude ?? '' }}"
         data-lng="{{ $targetProperty->longitude ?? '' }}">

    {{-- Property Thumbnail --}}
    <div class="relative md:w-72 lg:w-80 h-52 md:h-auto flex-shrink-0 overflow-hidden bg-slate-100 dark:bg-slate-900">
        <img src="{{ $cover }}" 
             alt="{{ $title }}" 
             loading="lazy"
             class="w-full h-full object-cover">

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
                class="absolute top-3 right-3 z-20 w-8 h-8 rounded-full bg-black/40 hover:bg-black/60 backdrop-blur-md text-white flex items-center justify-center transition-[transform,background-color] duration-140 active:scale-85 shadow-md focus:outline-none cursor-pointer" 
                aria-label="Save to favorites">
            <svg class="w-4 h-4 transition-colors duration-150" :class="favorited ? 'text-rose-500 fill-rose-500 heart-active' : 'text-white'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
            </svg>
        </button>

        {{-- Badges on image --}}
        <div class="absolute top-3 left-3 z-10 flex flex-wrap gap-1.5">
            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold shadow-md {{ $badgeColors }}">
                {{ __($purpose) }}
            </span>
            @if($isVerified)
                <span class="px-2 py-0.5 rounded-full text-xs font-bold shadow-md bg-emerald-600 text-white flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                    {{ __('Verified') }}
                </span>
            @endif
        </div>

        <span class="absolute bottom-3 left-3 text-xs font-mono font-bold bg-black/70 backdrop-blur-sm text-white/90 px-2 py-0.5 rounded-md">
            {{ $code }}
        </span>
    </div>

    {{-- Details & Actions --}}
    <div class="p-5 sm:p-6 flex-1 flex flex-col justify-between">
        <div>
            {{-- Top Row: Location & Price --}}
            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-2 mb-2">
                <div>
                    <p class="text-xs font-semibold text-orange-600 dark:text-orange-400 flex items-center gap-1 mb-1">
                        <svg class="w-3.5 h-3.5 text-orange-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                        <span>{{ __('Addis Ababa, Ethiopia') }}</span>
                    </p>
                    <h3 class="font-display font-bold text-slate-900 dark:text-white text-lg line-clamp-1">
                        <a href="{{ route('properties.show', $propId) }}" class="focus:outline-none">
                            {{ $title }}
                        </a>
                    </h3>
                </div>

                <div class="text-left sm:text-right flex-shrink-0">
                    <p class="font-display font-black text-xl sm:text-2xl tracking-tight leading-none text-orange-600 dark:text-orange-400">
                        {{ $priceFormatted }}
                    </p>
                    @if($priceSuffix)
                        <span class="text-xs text-slate-400 dark:text-slate-500">{{ $priceSuffix }}</span>
                    @endif
                </div>
            </div>

            {{-- Description --}}
            @if($targetProperty && $targetProperty->description)
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 line-clamp-2 mb-4 leading-relaxed">
                    {{ $targetProperty->description }}
                </p>
            @endif

            {{-- Specs Pill Grid --}}
            <div class="flex flex-wrap items-center gap-3 text-xs text-slate-600 dark:text-slate-300">
                @if($bedrooms)
                    <div class="card flex items-center gap-1.5 bg-slate-50 dark:bg-slate-900/60 px-3 py-1.5 rounded-xl border border-slate-200/80 dark:border-slate-800/80">
                        <svg class="w-4 h-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                        <span><strong class="text-slate-900 dark:text-white font-semibold">{{ $bedrooms }}</strong> {{ __('Bedrooms') }}</span>
                    </div>
                @endif

                @if($bathrooms)
                    <div class="card flex items-center gap-1.5 bg-slate-50 dark:bg-slate-900/60 px-3 py-1.5 rounded-xl border border-slate-200/80 dark:border-slate-800/80">
                        <svg class="w-4 h-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        <span><strong class="text-slate-900 dark:text-white font-semibold">{{ $bathrooms }}</strong> {{ __('Bathrooms') }}</span>
                    </div>
                @endif

                @if($area)
                    <div class="card flex items-center gap-1.5 bg-slate-50 dark:bg-slate-900/60 px-3 py-1.5 rounded-xl border border-slate-200/80 dark:border-slate-800/80">
                        <svg class="w-4 h-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" /></svg>
                        <span><strong class="text-slate-900 dark:text-white font-semibold">{{ $area }}</strong> m² {{ __('Total Area') }}</span>
                    </div>
                @endif

                @if($targetProperty && $targetProperty->amenities->contains('code', 'ample_parking'))
                    <div class="card flex items-center gap-1.5 bg-slate-50 dark:bg-slate-900/60 px-3 py-1.5 rounded-xl border border-slate-200/80 dark:border-slate-800/80">
                        <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h4a3 3 0 013 3v0a3 3 0 01-3 3H8V7zm0 6v5" /></svg>
                        <span>{{ __('Parking') }}</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Bottom Action Row --}}
        <div class="mt-5 pt-4 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between">
            <div class="flex items-center gap-2">
                @if($targetProperty && $targetProperty->amenities->count() > 0)
                    <span class="text-xs text-slate-400 dark:text-slate-500">
                        {{ __('Includes') }}: {{ $targetProperty->amenities->take(2)->map(fn($a) => app()->getLocale() === 'am' && !empty($a->name_am) ? $a->name_am : __($a->name_en))->implode(', ') }}
                        @if($targetProperty->amenities->count() > 2)
                            +{{ $targetProperty->amenities->count() - 2 }} {{ __('more') }}
                        @endif
                    </span>
                @endif
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('properties.show', $propId) }}" class="btn btn-primary btn-sm flex items-center gap-1.5 shadow-sm shadow-orange-500/20">
                    <span>{{ __('View Property') }}</span>
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                </a>
            </div>
        </div>
    </div>
</article>
