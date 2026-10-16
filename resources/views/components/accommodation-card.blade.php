@props(['accommodation', 'showPrice' => true])

<div 
    class="group relative bg-white dark:bg-[#0D1322] rounded-2xl border border-slate-200/80 dark:border-slate-800/80 overflow-hidden shadow-xs hover:shadow-xl hover:-translate-y-1 transition-all duration-300"
    x-data="{ favorite: false }"
    @click.outside="favorite = false"
>
    {{-- Image --}}
    <div class="relative aspect-[4/3] overflow-hidden">
        <img 
            src="{{ $accommodation->property->cover_image 
                ? asset('storage/' . $accommodation->property->cover_image) 
                : 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&q=80&w=800' }}" 
            alt="{{ $accommodation->property->title }}"
            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
        >
        
        {{-- Badges --}}
        <div class="absolute top-3 left-3 flex flex-col gap-2">
            @if($accommodation->is_active)
                <span class="px-2 py-1 rounded-full text-xs font-bold text-white bg-emerald-500">
                    {{ __('Active') }}
                </span>
            @endif
            
            <button 
                @click="favorite = !favorite"
                class="p-2 rounded-full bg-white/90 dark:bg-surface/90 hover:bg-primary-100 dark:hover:bg-primary-900/30 transition-colors shadow-sm"
                aria-label="{{ $attributes->get('aria-label', 'Add to favorites') }}"
            >
                <svg 
                    :class="favorite ? 'text-rose-500 fill-current' : 'text-muted dark:text-muted'" 
                    class="w-5 h-5" 
                    fill="none" 
                    viewBox="0 0 24 24" 
                    stroke="currentColor" 
                    stroke-width="2"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                </svg>
            </button>
        </div>

        {{-- Price Tag --}}
        @if($showPrice && $accommodation->property->price)
            <div class="absolute bottom-3 right-3 px-3 py-1.5 rounded-full bg-white/95 dark:bg-slate-900/95 shadow-md text-sm font-bold text-slate-900 dark:text-white backdrop-blur-sm border border-slate-200/50 dark:border-slate-800/50">
                {{ $accommodation->property->currency === 'USD' ? '$' : 'Br' }}{{ number_format($accommodation->property->price, 0) }}<span class="text-xs font-normal text-slate-400">/night</span>
            </div>
        @endif
    </div>

    {{-- Content --}}
    <div class="p-5 space-y-3">
        {{-- Type & Rating --}}
        <div class="flex items-center justify-between">
            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider bg-violet-100 dark:bg-violet-950/40 text-violet-700 dark:text-violet-300 border border-violet-200/50 dark:border-violet-800/50">
                {{ $accommodation->property->type->name ?? 'Entire home' }}
            </span>
            <div class="flex items-center gap-1 text-amber-500">
                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14l-4.9-4.87L18.36 9.27l-4.9-4.87L10.91 3.09 12 0l1.09 3.09z"/></svg>
                <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">4.9</span>
            </div>
        </div>

        {{-- Title & Location --}}
        <h3 class="font-bold text-slate-900 dark:text-white text-lg leading-tight line-clamp-2 group-hover:text-violet-600 dark:group-hover:text-violet-400 transition-colors">
            {{ $accommodation->property->title }}
        </h3>
        <div class="flex items-center gap-1 text-sm text-slate-500 dark:text-slate-400">
            <svg class="w-4 h-4 shrink-0 text-violet-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
            <span class="line-clamp-1">{{ $accommodation->property->neighborhood->name ?? 'Addis Ababa' }}</span>
        </div>

        {{-- Capacity --}}
        <div class="flex items-center gap-4 text-xs text-slate-600 dark:text-slate-300 pt-3 border-t border-slate-100 dark:border-slate-800/80">
            <div class="flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                <span>{{ $accommodation->max_guests }} {{ __('guests') }}</span>
            </div>
            <div class="flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.2A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.2A2 2 0 0118 9h2.07a2 2 0 011.664.89l.812 1.2A2 2 0 0122 13H4a2 2 0 01-2-2v-1a2 2 0 012-2h2.07a2 2 0 011.664-.89l.812-1.2A2 2 0 0110 3h3.86a2 2 0 011.664.89l.812 1.2A2 2 0 0118 7h2.07a2 2 0 011.664.89l.812 1.2A2 2 0 0122 9h2.07a2 2 0 011.664.89l.812 1.2A2 2 0 0122 13H4a2 2 0 01-2-2v-1a2 2 0 012-2h2.07a2 2 0 011.664-.89l.812-1.2A2 2 0 0110 3h3.86a2 2 0 011.664.89l.812 1.2A2 2 0 0114 7h2.07a2 2 0 011.664.89l.812 1.2A2 2 0 0118 9h2.07a2 2 0 011.664.89l.812 1.2A2 2 0 0122 13H4a2 2 0 01-2-2v-1z" /></svg>
                <span>{{ $accommodation->bedrooms }} {{ $accommodation->bedrooms === 1 ? 'bed' : 'beds' }}</span>
            </div>
            <div class="flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 01-2 2v-6a2 2 0 01-2-2v-6a2 2 0 012-2h2.5a1 1 0 011 1v12a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 01-2-2v-6a2 2 0 012-2h2.5a1 1 0 011 1v12a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 01-2-2v-6a2 2 0 012-2H8.5a1 1 0 011-1z" /></svg>
                <span>{{ $accommodation->bathrooms }} {{ $accommodation->bathrooms === 1 ? 'bath' : 'baths' }}</span>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="flex gap-3 pt-2">
            <a href="{{ route('accommodations.show', $accommodation) }}" class="flex-1 py-3 px-4 rounded-xl text-center font-semibold text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20 hover:bg-primary-100 dark:hover:bg-primary-900/30 transition-colors">
                {{ __('View details') }}
            </a>
            @if(auth()->check() && auth()->id() !== $accommodation->host_id)
                <a href="{{ route('booking.create', $accommodation) }}" class="flex-1 py-3 px-4 rounded-xl bg-primary-600 text-white font-semibold text-center shadow-sm hover:bg-primary-700 transition-colors">
                    {{ __('Reserve') }}
                </a>
            @endif
        </div>
    </div>
</div>