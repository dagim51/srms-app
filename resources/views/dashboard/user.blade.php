<x-layouts.dashboard>
    <x-slot name="title">{{ __('My Portal') }}</x-slot>

    @section('page-title', __('My Portal'))

    <div class="space-y-6">

        {{-- Standardized Page Header Card with Primary Accent #C2410C & Interactive Toolbar --}}
        <div class="cinematic-fade-up">
            <x-page-header
                title="Client Portal"
                description="Manage your saved properties, scheduled viewings, active negotiation offers, and rental agreements."
                icon="home"
                badge="Personal Real Estate Hub">
                <x-slot:actions>
                    <a href="/properties" class="btn btn-secondary btn-sm" id="btn-browse-properties">
                        <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" />
                        </svg>
                        <span>{{ __('Browse Properties') }}</span>
                    </a>

                    <a href="/dashboard/properties/map" class="btn btn-primary btn-sm shadow-sm shadow-orange-500/20" id="btn-explore-map">
                        <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                        </svg>
                        <span>{{ __('Explore Map') }}</span>
                    </a>
                </x-slot:actions>
            </x-page-header>
        </div>

        {{-- Aura Estates Signature Welcome Banner for Clients/Buyers & Short-Stay Travelers --}}
        <div class="cinematic-fade-up card relative overflow-hidden rounded-2xl bg-gradient-to-r from-orange-500/[0.08] via-amber-500/[0.04] to-transparent border border-orange-200/70 dark:border-orange-900/40 p-5 sm:p-6 backdrop-blur-md">
            @if(session('registered_intent') || session('welcome_toast'))
                <div class="mb-4 inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-700 dark:text-emerald-400 text-xs font-bold">
                    <span>🎉</span>
                    <span>{{ __('Welcome to SRMS! Your account has been personalized based on your preferences.') }}</span>
                </div>
            @endif

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 relative z-10">
                <div class="space-y-1.5">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-orange-100/90 dark:bg-orange-950/70 border border-orange-200/80 dark:border-orange-800/80 text-orange-700 dark:text-orange-300 text-[11px] font-semibold tracking-wide uppercase">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span>{{ __('Welcome,') }} {{ auth()->user()->name }}</span>
                        @if(auth()->user()->intent === 'traveler')
                            <span class="text-slate-400">|</span>
                            <span>{{ __('Traveler Portal') }}</span>
                        @elseif(auth()->user()->intent === 'tenant')
                            <span class="text-slate-400">|</span>
                            <span>{{ __('Tenant Portal') }}</span>
                        @else
                            <span class="text-slate-400">|</span>
                            <span>{{ __('Buyer Hub') }}</span>
                        @endif
                    </div>

                    @if(auth()->user()->intent === 'traveler')
                        <h2 class="font-serif text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                            {{ __('Discover Handpicked Stays') }} <span class="italic font-normal text-transparent bg-clip-text bg-gradient-to-r from-orange-600 via-amber-500 to-orange-400 dark:from-orange-400 dark:to-amber-300">{{ __('Across Ethiopia') }}</span>
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 max-w-2xl leading-relaxed">
                            {{ __('Explore verified vacation rentals, luxury apartments, and boutique accommodations with live availability, instant booking, and host reviews.') }}
                        </p>
                    @elseif(auth()->user()->intent === 'tenant')
                        <h2 class="font-serif text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                            {{ __('Manage Your Leases & Rentals') }} <span class="italic font-normal text-transparent bg-clip-text bg-gradient-to-r from-orange-600 via-amber-500 to-orange-400 dark:from-orange-400 dark:to-amber-300">{{ __('Across Ethiopia') }}</span>
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 max-w-2xl leading-relaxed">
                            {{ __('Track active digital leases, verified rental agreements, rent schedules, and maintenance requests in one unified tenant hub.') }}
                        </p>
                    @else
                        <h2 class="font-serif text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                            {{ __('Discover Verified Real Estate') }} <span class="italic font-normal text-transparent bg-clip-text bg-gradient-to-r from-orange-600 via-amber-500 to-orange-400 dark:from-orange-400 dark:to-amber-300">{{ __('Across Ethiopia') }}</span>
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 max-w-2xl leading-relaxed">
                            {{ __('Track your saved listings, upcoming on-site viewings, formal purchase offers, and verified title records in one unified workspace.') }}
                        </p>
                    @endif
                </div>

                <div class="flex items-center gap-2.5 sm:self-center flex-shrink-0">
                    @if(auth()->user()->intent === 'traveler')
                        <a href="/accommodations" class="px-4 py-2 rounded-xl text-xs font-semibold bg-gradient-to-r from-orange-600 to-orange-500 hover:from-orange-500 hover:to-orange-600 text-white shadow-md shadow-orange-500/20 hover:shadow-lg transition-all active:scale-[0.98]">
                            {{ __('Explore Accommodations') }}
                        </a>
                    @else
                        <a href="/properties" class="px-4 py-2 rounded-xl text-xs font-semibold bg-gradient-to-r from-orange-600 to-orange-500 hover:from-orange-500 hover:to-orange-600 text-white shadow-md shadow-orange-500/20 hover:shadow-lg transition-all active:scale-[0.98]">
                            {{ __('Explore Properties') }}
                        </a>
                    @endif

                    <a href="/dashboard/favorites" class="px-4 py-2 rounded-xl text-xs font-semibold bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200 hover:text-orange-600 dark:hover:text-orange-400 border border-slate-200/80 dark:border-slate-700 shadow-xs hover:shadow-md transition-all active:scale-[0.98]">
                        {{ __('View Favorites') }} ({{ $savedFavoritesCount }})
                    </a>
                </div>
            </div>
        </div>
        </div>

        {{-- Client KPI Cards Grid --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4" aria-label="Personal Activity Overview">
            
            {{-- 1. Favorites --}}
            <div class="cinematic-fade-up cinematic-stagger-1">
                <x-card-stat
                    :label="__('Saved Listings')"
                    :value="$savedFavoritesCount"
                    :caption="__('In your wishlist')"
                    :badge="__('Saved')"
                    badgeVariant="brand"
                    iconColor="rose"
                    href="/dashboard/favorites"
                    role="region"
                    aria-label="Saved listings">
                    <x-slot:icon>
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                        </svg>
                    </x-slot:icon>
                </x-card-stat>
            </div>

            {{-- 2. Viewings --}}
            <div class="cinematic-fade-up cinematic-stagger-2">
                <x-card-stat
                    :label="__('Scheduled Tours')"
                    :value="$scheduledViewingsCount"
                    :caption="__('Physical visits')"
                    :badge="$scheduledViewingsCount > 0 ? __('Upcoming') : __('None')"
                    :badgeVariant="$scheduledViewingsCount > 0 ? 'success' : 'neutral'"
                    iconColor="purple"
                    href="/dashboard/viewings"
                    role="region"
                    aria-label="Scheduled tours">
                    <x-slot:icon>
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                        </svg>
                    </x-slot:icon>
                </x-card-stat>
            </div>

            {{-- 3. Offers --}}
            <div class="cinematic-fade-up cinematic-stagger-3">
                <x-card-stat
                    :label="__('Active Offers')"
                    :value="$submittedOffersCount"
                    :caption="__('Negotiations')"
                    :badge="$submittedOffersCount > 0 ? __('In Review') : __('None')"
                    :badgeVariant="$submittedOffersCount > 0 ? 'info' : 'neutral'"
                    iconColor="amber"
                    href="/dashboard/offers"
                    role="region"
                    aria-label="Active offers">
                    <x-slot:icon>
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </x-slot:icon>
                </x-card-stat>
            </div>

            {{-- 4. Messages / Inquiries --}}
            <div class="cinematic-fade-up cinematic-stagger-4">
                <x-card-stat
                    :label="__('Inquiries')"
                    :value="$activeInquiriesCount"
                    :caption="__('Direct chats')"
                    :badge="$activeInquiriesCount > 0 ? __('Active') : __('Empty')"
                    :badgeVariant="$activeInquiriesCount > 0 ? 'info' : 'neutral'"
                    iconColor="blue"
                    href="/dashboard/messages"
                    role="region"
                    aria-label="Inquiries">
                    <x-slot:icon>
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" />
                        </svg>
                    </x-slot:icon>
                </x-card-stat>
            </div>

            {{-- 5. Active Leases --}}
            <div class="cinematic-fade-up cinematic-stagger-5">
                <x-card-stat
                    :label="__('My Leases')"
                    :value="$activeLeasesCount"
                    :caption="__('Rent contracts')"
                    :badge="$activeLeasesCount > 0 ? __('Active') : __('None')"
                    :badgeVariant="$activeLeasesCount > 0 ? 'success' : 'neutral'"
                    iconColor="emerald"
                    href="/dashboard/rental"
                    role="region"
                    aria-label="Active leases">
                    <x-slot:icon>
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                    </x-slot:icon>
                </x-card-stat>
            </div>

            {{-- 6. Short-Stay Stays --}}
            <div class="cinematic-fade-up cinematic-stagger-6">
                <x-card-stat
                    :label="__('Bookings')"
                    :value="$activeBookingsCount"
                    :caption="__('Short stays')"
                    :badge="$activeBookingsCount > 0 ? __('Reserved') : __('None')"
                    :badgeVariant="$activeBookingsCount > 0 ? 'info' : 'neutral'"
                    iconColor="orange"
                    href="/dashboard/reservations"
                    role="region"
                    aria-label="Bookings">
                    <x-slot:icon>
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                    </x-slot:icon>
                </x-card-stat>
            </div>

        </div>

        {{-- Main Two-Column Layout --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 cinematic-fade-up cinematic-stagger-2">

            {{-- Left Column: Saved Favorites & Recommended (2 cols wide) --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Saved Properties Section --}}
                <div class="luxury-dashboard-card p-6">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800/80 mb-5">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-rose-500/10 dark:bg-rose-500/20 text-rose-600 dark:text-rose-400 flex items-center justify-center font-bold text-sm">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white font-display">
                                    {{ __('Your Saved Properties') }}
                                </h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                    {{ __('Listings you have bookmarked for easy reference and comparative review.') }}
                                </p>
                            </div>
                        </div>
                        <a href="/dashboard/favorites" class="text-xs font-semibold text-orange-600 dark:text-orange-400 hover:underline">
                            {{ __('View all') }} →
                        </a>
                    </div>

                    @if($recentFavorites->isEmpty())
                        <div class="text-center py-10 px-4 rounded-xl border border-dashed border-slate-200 dark:border-slate-800">
                            <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 mb-3">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                                </svg>
                            </div>
                            <h4 class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ __('No saved properties yet') }}</h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto mt-1 mb-4">
                                {{ __('Explore the marketplace and click the heart icon on any listing to bookmark it here.') }}
                            </p>
                            <a href="/properties" class="btn btn-primary btn-sm">
                                {{ __('Browse Verified Properties') }}
                            </a>
                        </div>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @foreach($recentFavorites as $fav)
                                @php
                                    $prop = $fav->property;
                                    $primaryListing = $prop->listings->first();
                                    $mediaUrl = $prop->media->first()?->file_path ? asset('storage/' . $prop->media->first()->file_path) : 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=600&q=80';
                                @endphp
                                <div class="card p-3 rounded-xl border border-slate-200/80 dark:border-slate-800 hover:border-orange-500/40 transition-all flex gap-3 group">
                                    <img src="{{ $mediaUrl }}" alt="{{ $prop->title }}" class="w-20 h-20 rounded-lg object-cover flex-shrink-0">
                                    <div class="min-w-0 flex-1 flex flex-col justify-between">
                                        <div>
                                            <span class="text-[10px] font-bold uppercase tracking-wider text-orange-600 dark:text-orange-400">
                                                {{ $prop->category->name ?? 'Residential' }}
                                            </span>
                                            <h4 class="text-xs font-bold text-slate-900 dark:text-white truncate group-hover:text-orange-600 transition-colors">
                                                {{ $prop->title }}
                                            </h4>
                                            <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate mt-0.5">
                                                {{ $prop->address ?? 'Addis Ababa' }}
                                            </p>
                                        </div>
                                        <div class="flex items-center justify-between mt-2 pt-1 border-t border-slate-100 dark:border-slate-800/80">
                                            <span class="text-xs font-mono font-bold text-slate-900 dark:text-white">
                                                {{ $primaryListing ? number_format($primaryListing->price) . ' ETB' : 'Contact for Price' }}
                                            </span>
                                            <a href="/properties/{{ $prop->id }}" class="text-[11px] font-semibold text-orange-600 hover:text-orange-700">
                                                {{ __('View') }} →
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Recommended Marketplace Listings --}}
                <div class="luxury-dashboard-card p-6">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800/80 mb-5">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-orange-500/10 dark:bg-orange-500/20 text-orange-600 dark:text-orange-400 flex items-center justify-center font-bold text-sm">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.516 0c.85.493 1.509 1.333 1.509 2.316V18" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white font-display">
                                    {{ __('Featured Marketplace Opportunities') }}
                                </h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                    {{ __('Verified title deeds with municipal compliance and escrow protection.') }}
                                </p>
                            </div>
                        </div>
                        <a href="/properties" class="text-xs font-semibold text-orange-600 dark:text-orange-400 hover:underline">
                            {{ __('Explore Marketplace') }} →
                        </a>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        @forelse($recommendedListings as $listing)
                            @php
                                $prop = $listing->property;
                                $img = $prop?->media->first()?->file_path ? asset('storage/' . $prop->media->first()->file_path) : 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?auto=format&fit=crop&w=600&q=80';
                            @endphp
                            <div class="card overflow-hidden rounded-xl border border-slate-200/80 dark:border-slate-800 hover:shadow-lg transition-all group flex flex-col justify-between">
                                <div class="relative">
                                    <img src="{{ $img }}" alt="{{ $prop?->title ?? 'Property' }}" class="w-full h-32 object-cover group-hover:scale-105 transition-transform duration-300">
                                    <span class="absolute top-2 left-2 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-slate-900/80 text-white backdrop-blur-xs">
                                        {{ $listing->purpose ?? 'For Sale' }}
                                    </span>
                                </div>
                                <div class="p-3 flex-1 flex flex-col justify-between">
                                    <div>
                                        <h4 class="text-xs font-bold text-slate-900 dark:text-white truncate">
                                            {{ $prop?->title ?? 'Executive Residence' }}
                                        </h4>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate mt-0.5">
                                            {{ $prop?->address ?? 'Addis Ababa' }}
                                        </p>
                                    </div>
                                    <div class="flex items-center justify-between mt-3 pt-2 border-t border-slate-100 dark:border-slate-800/80">
                                        <span class="text-xs font-mono font-bold text-orange-600 dark:text-orange-400">
                                            {{ number_format($listing->price) }} ETB
                                        </span>
                                        <a href="/properties/{{ $prop?->id }}" class="text-[11px] font-semibold text-slate-700 dark:text-slate-300 hover:text-orange-600">
                                            {{ __('Details') }} →
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-3 text-center py-6 text-xs text-slate-400">
                                {{ __('No properties currently available.') }}
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>

            {{-- Right Column: Inquiries, Actions & KYC Verification (1 col wide) --}}
            <div class="space-y-6">

                {{-- Inquiries & Conversations --}}
                <div class="luxury-dashboard-card p-6">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800/80 mb-4">
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white font-display">
                            {{ __('Recent Inquiries') }}
                        </h3>
                        <a href="/dashboard/messages" class="text-xs font-semibold text-orange-600 dark:text-orange-400 hover:underline">
                            {{ __('All Chats') }} →
                        </a>
                    </div>

                    @if($recentInquiries->isEmpty())
                        <div class="text-center py-6 text-xs text-slate-400">
                            <p>{{ __('No inquiries submitted yet.') }}</p>
                            <p class="text-[11px] text-slate-500 mt-1">{{ __('Submit inquiries on any property to chat with verified agents.') }}</p>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($recentInquiries as $inq)
                                <a href="/dashboard/messages" class="block p-3 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-800 hover:border-orange-500/40 transition-colors">
                                    <div class="flex items-center justify-between text-xs mb-1">
                                        <span class="font-bold text-slate-900 dark:text-white truncate max-w-[160px]">
                                            {{ $inq->listing?->property?->title ?? 'Property Inquiry' }}
                                        </span>
                                        <span class="badge {{ $inq->status === 'responded' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300' }} text-[10px] px-1.5 py-0 border-none font-bold">
                                            {{ ucfirst($inq->status) }}
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 line-clamp-2">
                                        {{ $inq->message }}
                                    </p>
                                    <span class="text-[10px] text-slate-400 mt-1 block font-mono">
                                        {{ $inq->created_at?->diffForHumans() }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Fast Navigation Quick Links --}}
                <div class="luxury-dashboard-card p-6">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white font-display mb-3 pb-2 border-b border-slate-100 dark:border-slate-800/80">
                        {{ __('Quick Services') }}
                    </h3>

                    <div class="space-y-2">
                        <a href="/dashboard/documents" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-100/80 dark:hover:bg-slate-800/60 transition-colors text-xs font-semibold text-slate-700 dark:text-slate-200">
                            <div class="flex items-center gap-2.5">
                                <span class="w-7 h-7 rounded-lg bg-blue-100 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                </span>
                                <span>{{ __('KYC & Identity Documents') }}</span>
                            </div>
                            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
                        </a>

                        <a href="/dashboard/rental" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-100/80 dark:hover:bg-slate-800/60 transition-colors text-xs font-semibold text-slate-700 dark:text-slate-200">
                            <div class="flex items-center gap-2.5">
                                <span class="w-7 h-7 rounded-lg bg-emerald-100 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                                </span>
                                <span>{{ __('Rental Applications & Leases') }}</span>
                            </div>
                            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
                        </a>

                        <a href="/dashboard/settings" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-100/80 dark:hover:bg-slate-800/60 transition-colors text-xs font-semibold text-slate-700 dark:text-slate-200">
                            <div class="flex items-center gap-2.5">
                                <span class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                </span>
                                <span>{{ __('Profile & Security Settings') }}</span>
                            </div>
                            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
                        </a>
                    </div>
                </div>

                {{-- Trust & Safety Badge --}}
                <div class="card p-4 rounded-xl bg-emerald-500/[0.06] border border-emerald-500/20 text-xs">
                    <div class="flex items-center gap-2.5 font-bold text-emerald-800 dark:text-emerald-300 mb-1">
                        <svg class="w-4.5 h-4.5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                        </svg>
                        <span>{{ __('Verified Municipal Land Title Compliance') }}</span>
                    </div>
                    <p class="text-[11px] text-slate-600 dark:text-slate-400 leading-relaxed">
                        {{ __('All listings and lease agreements on SRMS undergo cadastral parcel verification and authenticated bank escrow protection.') }}
                    </p>
                </div>

            </div>

        </div>

    </div>
</x-layouts.dashboard>
