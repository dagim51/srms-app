<x-layouts.dashboard>
    <x-slot name="title">Listing Preview | {{ $listing->property->title ?? 'Listing' }}</x-slot>

    @section('page-title', 'Listing Preview')
    @section('breadcrumb')
        <a href="{{ route('dashboard.listings.index') }}" class="hover:text-muted dark:hover:text-muted">{{ __('Listings') }}</a>
        <span class="mx-1.5 text-muted dark:text-muted">/</span>
        <span class="text-muted dark:text-dark-subtle">{{ __('Preview') }}</span>
    @endsection

    @php
        $property = $listing->property;
        $primaryMedia = $property && $property->media->first() 
            ? ($property->media->first()->url ?? $property->media->first()->storage_key) 
            : 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=1200&q=80';
        $gallery = $property ? $property->media : collect();
    @endphp

    <div class="max-w-6xl mx-auto page-enter pb-16 space-y-6">
        <x-page-header
            title="Listing Marketplace Preview"
            :description="__('Live preview of public marketplace display for') . ' ' . ($listing->property->title ?? 'this property') . '.'"
            icon="tag"
            badge="Preview" />

        {{-- Preview Banner & Actions Bar --}}
        <div class="card bg-slate-900 border border-slate-800 text-white rounded-2xl p-4 md:p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-primary-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded text-xs font-bold uppercase tracking-wider bg-primary-500 text-white">{{ __('Marketplace Preview') }}</span>
                        <span class="text-xs text-slate-300">Status: <strong class="text-white">{{ ucfirst($listing->status) }}</strong></span>
                    </div>
                    <p class="text-xs text-slate-400 mt-0.5">{{ __('This preview demonstrates how prospective buyers and tenants will see your listing.') }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('dashboard.listings.edit', $listing) }}" class="btn btn-outline text-xs bg-white/5 border-white/20 text-white hover:bg-white/10">{{ __('Edit Listing') }}</a>
                <form method="POST" action="{{ route('dashboard.listings.toggle-status', $listing) }}">
                    @csrf
                    @method('PATCH')
                    @if($listing->status === 'published')
                        <button type="submit" class="btn btn-outline text-xs bg-amber-500/20 border-amber-500/30 text-amber-300 hover:bg-amber-500/30">{{ __('Pause Listing') }}</button>
                    @else
                        <button type="submit" class="btn btn-primary text-xs">{{ __('Publish to Marketplace') }}</button>
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
                <div class="card bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-2xl overflow-hidden">
                    <div class="relative aspect-video bg-surface-subtle dark:bg-surface">
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
                        <div class="p-4 grid grid-cols-4 sm:grid-cols-6 gap-2 border-t border-warm dark:border-warm">
                            @foreach($gallery->take(6) as $m)
                                <img src="{{ $m->url ?? $m->storage_key }}" class="h-16 w-full object-cover rounded-lg border border-warm dark:border-warm" alt="">
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Property Details & Description --}}
                <div class="card bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-2xl p-6 space-y-6">
                    <div>
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h1 class="text-2xl font-display font-extrabold text-[#0F172A] dark:text-white">
                                    {{ $property->title ?? 'Untitled Property' }}
                                </h1>
                                <p class="text-sm text-[#4B5563] dark:text-[#CBD5E1] flex items-center gap-1.5 mt-1">
                                    <svg class="w-4 h-4 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.242-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    {{ ($property->location_summary && $property->location_summary !== 'Location not set') ? $property->location_summary : 'Addis Ababa, Ethiopia' }}
                                </p>
                            </div>
                            <div class="text-right">
                                <span class="text-xs font-semibold text-muted uppercase tracking-wider">{{ __('Asking Price') }}</span>
                                <p class="text-2xl font-display font-extrabold text-primary-600 dark:text-primary-400">
                                    {{ $listing->formatted_price }}
                                </p>
                            </div>
                        </div>

                        {{-- Core Spec Pills --}}
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6 pt-6 border-t border-slate-200/80 dark:border-slate-800/80 text-left">
                            <x-card-stat
                                :label="__('Bedrooms')"
                                :value="$property->bedrooms ?? '-'"
                                iconColor="blue"
                                :badge="__('Rooms')"
                                badgeVariant="info">
                                <x-slot:icon>
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
                                </x-slot:icon>
                            </x-card-stat>
                            <x-card-stat
                                :label="__('Bathrooms')"
                                :value="$property->bathrooms ?? '-'"
                                iconColor="blue"
                                :badge="__('Baths')"
                                badgeVariant="info">
                                <x-slot:icon>
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" /></svg>
                                </x-slot:icon>
                            </x-card-stat>
                            <x-card-stat
                                :label="__('Building Area')"
                                :value="$property->building_area ? $property->building_area . ' m²' : '-'"
                                iconColor="orange"
                                icon="building"
                                :badge="__('Interior')"
                                badgeVariant="brand"
                            />
                            <x-card-stat
                                :label="__('Land Area')"
                                :value="$property->land_area ? $property->land_area . ' m²' : '-'"
                                iconColor="emerald"
                                icon="building"
                                :badge="__('Plot')"
                                badgeVariant="success"
                            />
                        </div>
                    </div>

                    {{-- Description --}}
                    <div>
                        <h3 class="text-sm font-bold text-[#0F172A] dark:text-white uppercase tracking-wider mb-2">{{ __('About This Property') }}</h3>
                        <p class="text-sm text-[#4B5563] dark:text-[#CBD5E1] leading-relaxed whitespace-pre-line">
                            {{ $property->description ?? 'No description provided.' }}
                        </p>
                    </div>

                    {{-- Features & Amenities --}}
                    <div>
                        <h3 class="text-sm font-bold text-[#0F172A] dark:text-white uppercase tracking-wider mb-3">{{ __('Features & Amenities') }}</h3>
                        @if($property->amenities->count() > 0 || $property->features->count() > 0)
                            <div class="flex flex-wrap gap-2">
                                @foreach($property->amenities as $am)
                                    @php
                                        $amName = match(app()->getLocale()) {
                                            'am' => !empty($am->name_am) ? $am->name_am : ($am->name_en ?? $am->name ?? $am->code),
                                            'om' => !empty($am->name_om) ? $am->name_om : ($am->name_en ?? $am->name ?? $am->code),
                                            default => __($am->name_en ?? $am->name ?? $am->code),
                                        };
                                    @endphp
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 border border-emerald-100 dark:border-emerald-800/40">
                                        <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                        {{ $amName }}
                                    </span>
                                @endforeach
                                @foreach($property->features as $ft)
                                    @php
                                        $ftName = match(app()->getLocale()) {
                                            'am' => !empty($ft->name_am) ? $ft->name_am : ($ft->name_en ?? $ft->name ?? $ft->code),
                                            'om' => !empty($ft->name_om) ? $ft->name_om : ($ft->name_en ?? $ft->name ?? $ft->code),
                                            default => __($ft->name_en ?? $ft->name ?? $ft->code),
                                        };
                                    @endphp
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-surface-subtle dark:bg-surface text-[#4B5563] dark:text-[#CBD5E1]">
                                        {{ $ftName }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <p class="text-xs text-muted italic">{{ __('No amenities specified yet.') }}</p>
                        @endif
                    </div>

                    {{-- Location Map Display --}}
                    @if($property->latitude && $property->longitude)
                        <div>
                            <h3 class="text-sm font-bold text-[#0F172A] dark:text-white uppercase tracking-wider mb-3">{{ __('Location & Neighborhood') }}</h3>
                            <x-property-location-map :latitude="$property->latitude" :longitude="$property->longitude" :title="$property->title" />
                        </div>
                    @endif
                </div>
            </div>

            {{-- Sidebar Column: Marketplace Card Simulation --}}
            <div class="space-y-6">
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#6B7280] dark:text-[#94A3B8] mb-3">{{ __('Search Result Card Simulation') }}</h3>
                    
                    {{-- Exact Search Card View --}}
                    <div class="card bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-2xl overflow-hidden">
                        <div class="relative aspect-video bg-surface-subtle dark:bg-surface">
                            <img src="{{ $primaryMedia }}" class="w-full h-full object-cover" alt="">
                            <div class="absolute top-3 left-3">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-primary-600 text-white shadow-sm">
                                    {{ $listing->purpose }}
                                </span>
                            </div>
                            <div class="absolute bottom-0 inset-x-0 p-3 bg-gradient-to-t from-black/80 via-black/40 to-transparent">
                                <p class="font-display font-extrabold text-white text-lg">
                                    {{ $listing->formatted_price }}
                                </p>
                            </div>
                        </div>
                        <div class="p-4">
                            <h4 class="font-bold text-[#0F172A] dark:text-white text-sm line-clamp-1">
                                {{ $property->title }}
                            </h4>
                            <p class="text-xs text-[#6B7280] dark:text-[#94A3B8] mt-1 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.242-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                {{ ($property->location_summary && $property->location_summary !== 'Location not set') ? $property->location_summary : 'Addis Ababa, Ethiopia' }}
                            </p>
                            <div class="flex items-center gap-3 text-xs text-[#6B7280] dark:text-[#94A3B8] mt-3 pt-3 border-t border-warm dark:border-warm">
                                <span>{{ $property->bedrooms ?? 0 }} Beds</span> •
                                <span>{{ $property->bathrooms ?? 0 }} Baths</span> •
                                <span>{{ $property->building_area ?? 0 }} m²</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Canonical Asset Info Box --}}
                <div class="card bg-surface dark:bg-surface/40 border border-warm dark:border-warm/60 rounded-2xl p-5 space-y-3 text-xs">
                    <h4 class="font-bold text-muted dark:text-white">{{ __('Canonical Asset Relationship') }}</h4>
                    <p class="text-muted dark:text-muted leading-relaxed">
                        This listing does not duplicate the real estate asset. It references canonical property code <span class="font-mono font-bold text-muted dark:text-muted">{{ $property->property_code }}</span>.
                    </p>
                    <div class="pt-2 border-t border-warm dark:border-warm flex justify-between text-muted">
                        <span>Visibility:</span>
                        <span class="font-bold text-muted dark:text-white capitalize">{{ $listing->visibility }}</span>
                    </div>
                    <div class="flex justify-between text-muted">
                        <span>Published Date:</span>
                        <span class="font-medium text-muted dark:text-white">{{ $listing->published_at ? date('M d, Y', strtotime($listing->published_at)) : 'Not Published' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.dashboard>
