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
            'For Sale' => 'bg-orange-500 text-white shadow-md shadow-orange-500/20',
            'For Rent' => 'bg-emerald-600 text-white shadow-md shadow-emerald-600/20',
            'Short Stay' => 'bg-sky-600 text-white shadow-md shadow-sky-600/20',
            'Land' => 'bg-amber-600 text-white shadow-md shadow-amber-600/20',
            'Development' => 'bg-purple-600 text-white shadow-md shadow-purple-600/20',
            default => 'bg-[#111827] text-white',
        };

        $priceSuffix = match($listing->purpose) {
            'For Rent' => '/month',
            'Short Stay' => '/night',
            default => '',
        };
    @endphp

    <div class="py-10 page-container min-h-screen space-y-8 pt-28" x-data="{ activeMedia: '{{ $primaryImg }}', inquirySent: false }">
        {{-- Breadcrumbs Navigation --}}
        <nav class="flex items-center gap-2 text-xs text-[#6B7280] dark:text-[#94A3B8] font-medium">
            <a href="/" class="hover:text-orange-600 dark:hover:text-orange-400 transition-colors">{{ __('Home') }}</a>
            <span>/</span>
            <a href="{{ route('properties.index') }}" class="hover:text-orange-600 dark:hover:text-orange-400 transition-colors">{{ __('Properties') }}</a>
            <span>/</span>
            <a href="{{ route('properties.index', ['purpose' => $listing->purpose]) }}" class="hover:text-orange-600 dark:hover:text-orange-400 transition-colors">{{ __($listing->purpose) }}</a>
            <span>/</span>
            <span class="text-[#111827] dark:text-white font-bold truncate max-w-xs">{{ $property->title ?? __('Details') }}</span>
        </nav>

        {{-- 1. HERO PHOTO GALLERY & THUMBNAILS --}}
        <div class="space-y-3">
            <div class="relative aspect-[16/9] md:aspect-[21/9] rounded-3xl overflow-hidden bg-white dark:bg-[#111827] shadow-xl border border-[#EBDBC9] dark:border-[#273244]">
                <img :src="activeMedia" 
                     alt="{{ $property->title ?? __('Property') }}" 
                     onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=1200&q=80';"
                     class="w-full h-full object-cover transition-all duration-500">
                
                {{-- Badges on Gallery --}}
                <div class="absolute top-4 left-4 flex flex-wrap gap-2 z-10 pointer-events-none">
                    <span class="px-3.5 py-1.5 rounded-full text-xs font-extrabold {{ $purposeColor }}">
                        {{ __($listing->purpose) }}
                    </span>
                    <span class="px-3 py-1.5 rounded-full text-xs font-mono font-bold bg-black/70 backdrop-blur-md text-white border border-white/20">
                        {{ $property->property_code ?? 'PROP' }}
                    </span>
                    <span class="px-3.5 py-1.5 rounded-full text-xs font-bold bg-emerald-500/90 backdrop-blur-md text-white flex items-center gap-1.5 shadow-md">
                        <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
                        {{ __('Verified Title Deed') }}
                    </span>
                </div>

                {{-- Favorite Action Button --}}
                <div class="absolute top-4 right-4 z-20" x-data="{ favorited: false, loading: false }">
                    <button @click="
                                if (loading) return;
                                loading = true;
                                favorited = !favorited;
                                fetch('/favorites/toggle/{{ $property->id }}', {
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
                            class="px-4 py-2 rounded-full bg-black/60 hover:bg-black/80 backdrop-blur-md text-white text-xs font-bold flex items-center gap-2 shadow-lg transition-all active:scale-95 cursor-pointer"
                            id="detail-favorite-toggle-btn">
                        <svg class="w-4 h-4 transition-colors" :class="favorited ? 'text-rose-500 fill-rose-500' : 'text-white'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                        </svg>
                        <span x-text="favorited ? '{{ __('Saved') }}' : '{{ __('Save Property') }}'">{{ __('Save Property') }}</span>
                    </button>
                </div>

                {{-- Photo Count Pill --}}
                <div class="absolute bottom-4 right-4 px-3.5 py-1.5 rounded-full bg-black/75 backdrop-blur-md text-white text-xs font-bold flex items-center gap-1.5 shadow-md">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    <span>{{ $mediaList->count() }} {{ __('Photos') }}</span>
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
                                :class="activeMedia === '{{ $imgUrl }}' ? 'border-orange-500 ring-2 ring-orange-500/30 scale-102' : 'border-[#EBDBC9] dark:border-[#273244] opacity-75 hover:opacity-100'">
                            <img src="{{ $imgUrl }}" class="w-full h-full object-cover" alt="" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=600&q=80';">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- 2. MAIN CONTENT & STICKY INQUIRY SIDEBAR --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            {{-- Left Column: Property Full Description & Technical Details --}}
            <div class="lg:col-span-8 space-y-8">
                {{-- Title & Price Banner (White Card, Black Text) --}}
                <div class="bg-white dark:bg-[#0D1322] border border-slate-200/80 dark:border-slate-800/80 rounded-3xl p-6 sm:p-8 space-y-6 shadow-xs">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold text-orange-600 dark:text-orange-400 flex items-center gap-1.5 mb-2">
                                <svg class="w-4 h-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.242-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                <span>{{ $property->subCity->name ?? 'Addis Ababa' }}, {{ $property->city->name ?? 'Ethiopia' }}</span>
                            </p>
                            <h1 class="text-2xl sm:text-3xl font-display font-black text-slate-900 dark:text-white tracking-tight leading-tight">
                                {{ $property->title ?? __('Untitled Property') }}
                            </h1>
                        </div>

                        <div class="sm:text-right">
                            <span class="block text-xs font-extrabold uppercase tracking-wider text-slate-600 dark:text-slate-400">{{ __('Asking Price') }}</span>
                            <p class="text-2xl sm:text-3xl font-display font-black text-orange-600 dark:text-orange-400 leading-tight mt-0.5">
                                {{ $listing->formatted_price }}
                                @if($priceSuffix)
                                    <span class="text-sm font-normal text-slate-500 dark:text-slate-400">{{ $priceSuffix }}</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    {{-- Spec Pills Grid (Standardized x-card-stat cards with icons) --}}
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-6 border-t border-slate-200/80 dark:border-slate-800/80 text-left">
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

                {{-- Architectural Description --}}
                <div class="bg-white dark:bg-[#0D1322] border border-slate-200/80 dark:border-slate-800/80 rounded-3xl p-6 sm:p-8 space-y-4 shadow-xs">
                    <h2 class="text-xl font-display font-black text-[#000000] dark:text-white">
                        {{ __('About This Property') }}
                    </h2>
                    <div class="text-sm text-[#000000] dark:text-[#CBD5E1] leading-relaxed whitespace-pre-line font-medium">
                        {{ $property->description ?? __('No description provided.') }}
                    </div>
                </div>

                {{-- Verified Features & Amenities --}}
                <div class="bg-white dark:bg-[#111827] border border-[#EBDBC9] dark:border-[#273244] rounded-3xl p-6 sm:p-8 space-y-4 shadow-sm">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-950/50 flex items-center justify-center text-emerald-600">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <h2 class="text-xl font-display font-black text-[#000000] dark:text-white">
                            {{ __('Features & Verified Amenities') }}
                        </h2>
                    </div>

                    @if($property->amenities->count() > 0 || $property->features->count() > 0)
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs">
                            @foreach($property->amenities as $am)
                                <div class="bg-emerald-50/70 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/40 text-emerald-900 dark:text-emerald-300 rounded-xl p-3 flex items-center gap-2 font-bold">
                                    <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                    <span>{{ app()->getLocale() === 'am' && !empty($am->name_am) ? $am->name_am : __($am->name_en ?? $am->name ?? $am->code) }}</span>
                                </div>
                            @endforeach
                            @foreach($property->features as $ft)
                                <div class="bg-white dark:bg-[#1E293B] border border-[#EBDBC9] dark:border-[#273244] text-[#000000] dark:text-white rounded-xl p-3 flex items-center gap-2 font-bold">
                                    <span class="w-2 h-2 rounded-full bg-orange-500"></span>
                                    <span>{{ app()->getLocale() === 'am' && !empty($ft->name_am) ? $ft->name_am : __($ft->name_en ?? $ft->name ?? $ft->code) }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs">
                            <div class="bg-emerald-50/70 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/40 text-emerald-900 dark:text-emerald-300 rounded-xl p-3 flex items-center gap-2 font-bold">
                                <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span>{{ __('Backup Generator') }}</span>
                            </div>
                            <div class="bg-emerald-50/70 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/40 text-emerald-900 dark:text-emerald-300 rounded-xl p-3 flex items-center gap-2 font-bold">
                                <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span>{{ __('Reserve Water Tank') }}</span>
                            </div>
                            <div class="bg-emerald-50/70 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/40 text-emerald-900 dark:text-emerald-300 rounded-xl p-3 flex items-center gap-2 font-bold">
                                <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span>{{ __('24/7 Gated Security') }}</span>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Location Map (PostGIS Geocoded Accuracy) --}}
                @if($property->latitude && $property->longitude)
                    <div class="bg-white dark:bg-[#111827] border border-[#EBDBC9] dark:border-[#273244] rounded-3xl p-6 sm:p-8 space-y-4 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-xl font-display font-black text-[#000000] dark:text-white">{{ __('Location & Neighborhood') }}</h2>
                                <p class="text-xs text-[#000000] dark:text-[#94A3B8] mt-0.5 font-bold">{{ __('PostGIS geocoded with privacy-preserving radius protection') }}</p>
                            </div>
                            <a href="https://maps.google.com/?q={{ $property->latitude }},{{ $property->longitude }}" target="_blank" class="px-4 py-2 rounded-full text-xs font-bold bg-[#FFF5EB] dark:bg-[#1E293B] border border-[#EBDBC9] dark:border-[#273244] text-[#111827] dark:text-white hover:border-orange-500 transition-colors shadow-xs">
                                {{ __('Open in Google Maps') }}
                            </a>
                        </div>
                        <x-property-location-map :latitude="$property->latitude" :longitude="$property->longitude" :title="$property->title" />
                    </div>
                @endif
            </div>

            {{-- Right Column: Sticky Contact & Inquiry Console --}}
            <div class="lg:col-span-4 space-y-6 sticky top-28">
                <div class="bg-white dark:bg-[#111827] border border-[#EBDBC9] dark:border-[#273244] rounded-3xl p-6 space-y-6 shadow-md">
                    {{-- Verified Agent Badge --}}
                    <div class="flex items-center gap-3 pb-5 border-b border-[#EBDBC9] dark:border-[#273244]">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-orange-500 to-amber-500 text-white flex items-center justify-center font-extrabold text-base flex-shrink-0 shadow-md shadow-orange-500/25">
                            SR
                        </div>
                        <div>
                            <div class="flex items-center gap-1.5">
                                <h3 class="font-extrabold text-sm text-[#000000] dark:text-white">{{ __('SRMS Certified Agent') }}</h3>
                                <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                            </div>
                            <p class="text-xs text-[#000000] dark:text-[#94A3B8] font-bold">{{ __('Response time: ~15 mins') }}</p>
                        </div>
                    </div>

                    {{-- Inquiry Success Notice --}}
                    <div x-show="inquirySent" x-cloak class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-300 dark:border-emerald-800 text-xs text-emerald-900 dark:text-emerald-300 space-y-1">
                        <p class="font-bold flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            {{ __('Inquiry Dispatched Successfully!') }}
                        </p>
                        <p class="leading-relaxed">{{ __('The assigned licensed broker will contact you shortly to coordinate your private viewing.') }}</p>
                    </div>

                    {{-- Fast Inquiry Form --}}
                    <form @submit.prevent="
                            fetch('/dashboard/inquiries', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content'),
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({
                                    listing_id: '{{ $listing->id }}',
                                    message: $refs.msg.value
                                })
                            }).then(r => r.json()).then(d => {
                                if (d.success) {
                                    inquirySent = true;
                                    if (d.redirect) window.location.href = d.redirect;
                                }
                            })
                        " x-show="!inquirySent" class="space-y-4">
                        <div>
                            <label class="block text-xs font-extrabold uppercase tracking-wider text-[#000000] dark:text-white mb-1">{{ __('Your Full Name') }}</label>
                            <input type="text" required placeholder="{{ __('Abebe Bikila') }}" class="w-full px-3.5 py-2.5 text-xs font-bold rounded-xl bg-white dark:bg-[#1E293B] border border-[#EBDBC9] dark:border-[#273244] text-[#111827] dark:text-white placeholder:text-[#9CA3AF] focus:border-orange-500 outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-extrabold uppercase tracking-wider text-[#000000] dark:text-white mb-1">{{ __('Phone Number (Ethiopia)') }}</label>
                            <input type="tel" required placeholder="+251 91 123 4567" class="w-full px-3.5 py-2.5 text-xs font-bold rounded-xl bg-white dark:bg-[#1E293B] border border-[#EBDBC9] dark:border-[#273244] text-[#111827] dark:text-white placeholder:text-[#9CA3AF] focus:border-orange-500 outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-extrabold uppercase tracking-wider text-[#000000] dark:text-white mb-1">{{ __('Message / Inquiry') }}</label>
                            <textarea x-ref="msg" rows="3" required class="w-full px-3.5 py-2.5 text-xs font-medium rounded-xl bg-white dark:bg-[#1E293B] border border-[#EBDBC9] dark:border-[#273244] text-[#111827] dark:text-white placeholder:text-[#9CA3AF] focus:border-orange-500 outline-none leading-relaxed" 
                                      placeholder="{{ __('I am interested in this property and would like to schedule an inspection viewing.') }}"></textarea>
                        </div>

                        <button type="submit" class="w-full py-3.5 rounded-2xl text-xs sm:text-sm font-extrabold bg-gradient-to-r from-orange-500 to-amber-600 hover:from-orange-600 hover:to-amber-700 text-white shadow-md shadow-orange-500/25 transition-all cursor-pointer active:scale-95">
                            {{ __('Send Inquiry to Agent') }}
                        </button>
                    </form>

                    {{-- WhatsApp & Direct Action Buttons --}}
                    <div class="space-y-2 pt-2 border-t border-[#EBDBC9] dark:border-[#273244]">
                        @if($listing->purpose === 'For Sale')
                            <a href="{{ route('dashboard.offers.create', ['listing_id' => $listing->id]) }}" class="w-full py-2.5 px-4 rounded-xl text-xs font-bold flex items-center justify-center gap-2 bg-[#FFF5EB] dark:bg-[#1E293B] border border-orange-300 dark:border-orange-700 text-orange-700 dark:text-orange-300 hover:bg-orange-100 dark:hover:bg-orange-950/30 transition-colors shadow-xs">
                                <svg class="w-4 h-4 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <span>{{ __('Make a Formal Offer') }}</span>
                            </a>
                        @endif
                        <a href="{{ route('dashboard.viewings.create', ['listing_id' => $listing->id]) }}" class="w-full py-2.5 px-4 rounded-xl text-xs font-bold flex items-center justify-center gap-2 bg-blue-50 dark:bg-blue-950/20 border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300 hover:bg-blue-100 dark:hover:bg-blue-950/30 transition-colors shadow-xs">
                            <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            <span>{{ __('Schedule In-Person Viewing') }}</span>
                        </a>
                        <a href="{{ route('dashboard.reservations.create', ['listing_id' => $listing->id]) }}" class="w-full py-2.5 px-4 rounded-xl text-xs font-bold flex items-center justify-center gap-2 bg-purple-50 dark:bg-purple-950/20 border border-purple-200 dark:border-purple-800 text-purple-700 dark:text-purple-300 hover:bg-purple-100 dark:hover:bg-purple-950/30 transition-colors shadow-xs">
                            <svg class="w-4 h-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <span>{{ __('Reserve Property Deposit') }}</span>
                        </a>
                        <a href="https://wa.me/251911000000" target="_blank" class="w-full py-2.5 px-4 rounded-xl text-xs font-bold flex items-center justify-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white transition-colors shadow-md shadow-emerald-500/20">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                            <span>{{ __('Chat with Agent on WhatsApp') }}</span>
                        </a>
                        <a href="tel:+251911000000" class="w-full py-2.5 px-4 rounded-xl text-xs font-bold flex items-center justify-center gap-2 bg-[#FFF5EB] dark:bg-[#1E293B] border border-[#EBDBC9] dark:border-[#273244] text-[#111827] dark:text-white hover:border-orange-500 transition-colors shadow-xs">
                            <svg class="w-4 h-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                            <span>{{ __('Direct Call') }} (+251 91 100 0000)</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. SIMILAR / NEARBY PROPERTIES SECTION --}}
        @if($similarListings->count() > 0)
            <div class="pt-12 border-t border-[#EBDBC9] dark:border-[#273244] space-y-6">
                <div>
                    <div class="inline-flex items-center gap-1.5 text-xs font-extrabold uppercase tracking-wider text-orange-600 dark:text-orange-400 mb-1">
                        <span class="w-2 h-2 rounded-full bg-orange-500"></span>
                        {{ __('Recommended For You') }}
                    </div>
                    <h2 class="text-2xl font-display font-black text-[#111827] dark:text-white">
                        {{ __('Similar Properties') }} ({{ __($listing->purpose) }})
                    </h2>
                    <p class="text-xs text-[#4B5563] dark:text-[#CBD5E1] mt-0.5 font-medium">{{ __('Explore related verified properties in the same category & price range') }}</p>
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
