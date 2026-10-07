@props([
    'property',
    'isFeatured' => false,
])

<div class="card card-hover overflow-hidden group flex flex-col h-full relative">
    {{-- Badges --}}
    <div class="absolute top-4 left-4 z-10 flex flex-col gap-2">
        @if($isFeatured)
            <span class="badge badge-brand bg-white/90 backdrop-blur-sm border-white/20 shadow-sm text-xs font-bold uppercase tracking-wider">
                Featured
            </span>
        @endif
        <span class="badge {{ $property->type === 'sale' ? 'badge-brand' : 'badge-info' }} bg-white/90 backdrop-blur-sm shadow-sm">
            For {{ ucfirst($property->type) }}
        </span>
    </div>

    {{-- Favorite Button --}}
    <button class="absolute top-4 right-4 z-10 w-8 h-8 rounded-full bg-white/90 backdrop-blur-sm text-gray-400 flex items-center justify-center hover:text-rose-500 hover:bg-white transition-colors shadow-sm" aria-label="Save property">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" /></svg>
    </button>

    {{-- Image --}}
    <div class="relative aspect-[4/3] overflow-hidden bg-gray-100 dark:bg-gray-800">
        <img src="{{ $property->image_url ?? 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80' }}" 
             alt="{{ $property->title }}" 
             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-in-out">
        
        {{-- Price Overlay --}}
        <div class="absolute bottom-0 left-0 right-0 p-4 bg-gradient-to-t from-gray-900/80 to-transparent">
            <p class="font-display font-extrabold text-white text-xl">
                ETB {{ number_format($property->price) }}
                @if($property->type === 'rent')
                    <span class="text-sm font-normal text-gray-200">/mo</span>
                @endif
            </p>
        </div>
    </div>

    {{-- Content --}}
    <div class="p-5 flex-1 flex flex-col">
        <h3 class="font-display font-bold text-gray-900 dark:text-white text-lg line-clamp-1 mb-1">
            <a href="/properties/{{ $property->id ?? 1 }}" class="focus:outline-none">
                <span class="absolute inset-0" aria-hidden="true"></span>
                {{ $property->title ?? 'Modern Family Home' }}
            </a>
        </h3>
        <p class="text-gray-500 dark:text-gray-400 text-sm flex items-center gap-1.5 mb-4 line-clamp-1">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
            {{ $property->location ?? 'Bole, Addis Ababa' }}
        </p>

        {{-- Amenities --}}
        <div class="flex items-center gap-4 text-sm text-gray-600 dark:text-gray-300 mt-auto pt-4 border-t border-gray-100 dark:border-gray-800">
            <div class="flex items-center gap-1.5" title="Bedrooms">
                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0M12 12.75h.008v.008H12v-.008z" /></svg>
                <span>{{ $property->bedrooms ?? 3 }}</span>
            </div>
            <div class="flex items-center gap-1.5" title="Bathrooms">
                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.872c0-1.143.9-2.138 2.036-2.261 1.08-.117 2.155-.245 3.22-.382 1.156-.149 2.016-1.144 2.016-2.314v-4.14C15.522 5.12 14.152 4 12.42 4h-.84c-1.731 0-3.101 1.12-3.101 3.03V11.17c0 1.17.86 2.165 2.016 2.314 1.065.137 2.14.265 3.22.382 1.136.123 2.036 1.118 2.036 2.261V21M3 21h18" /></svg>
                <span>{{ $property->bathrooms ?? 2 }}</span>
            </div>
            <div class="flex items-center gap-1.5" title="Area">
                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" /></svg>
                <span>{{ $property->area ?? 150 }} m²</span>
            </div>
        </div>
    </div>
</div>
