<x-layouts.app>
    <x-slot name="title">SRMS - Ethiopia's Premier Real Estate & Property Marketplace</x-slot>
    <x-slot name="description">Discover, buy, rent, and invest in verified properties across Addis Ababa, Hawassa, Bahir Dar, and all major Ethiopian regional capitals.</x-slot>

    {{-- ═══════════════════════════════════════════════════════════
         1. HERO SECTION WITH 5 TRANSACTION MODES (BUY | RENT | STAY | LAND | PROJECTS)
         ═══════════════════════════════════════════════════════════ --}}
    <section class="relative min-h-[92vh] flex items-center justify-center overflow-hidden" 
             aria-labelledby="hero-heading"
             x-data="{
                activeTab: 'buy',
                locationQuery: '',
                propertyType: 'all',
                priceRange: 'all',

                get purposeParam() {
                    return {
                        'buy': 'For Sale',
                        'rent': 'For Rent',
                        'stay': 'Short Stay',
                        'land': 'Land',
                        'projects': 'Development'
                    }[this.activeTab] || 'all';
                }
             }">

        {{-- Dark Hero Background Gradient --}}
        <div class="absolute inset-0 bg-gradient-to-br from-gray-950 via-gray-900 to-gray-950" aria-hidden="true"></div>
        <div class="absolute inset-0 opacity-[0.03]" style="background-image: linear-gradient(#fff 1px, transparent 1px), linear-gradient(to right, #fff 1px, transparent 1px); background-size: 40px 40px;" aria-hidden="true"></div>
        
        {{-- Orange Glow Blobs --}}
        <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[42rem] h-[42rem] bg-orange-500/15 rounded-full blur-3xl pointer-events-none" aria-hidden="true"></div>
        <div class="absolute bottom-0 right-0 w-80 h-80 bg-orange-600/10 rounded-full blur-3xl pointer-events-none" aria-hidden="true"></div>

        <div class="relative page-container text-center py-28 pt-36">
            {{-- Trust Eyebrow --}}
            <div class="inline-flex items-center gap-2 badge badge-brand mb-6 py-1.5 px-4 text-xs uppercase tracking-widest font-bold shadow-lg shadow-orange-500/10">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                Official Verified Ethiopian Real Estate Marketplace
            </div>

            {{-- Main Headline --}}
            <h1 id="hero-heading" class="font-display font-extrabold text-white text-4xl sm:text-5xl lg:text-7xl leading-[1.08] tracking-tight mb-6 max-w-5xl mx-auto">
                Discover Verified
                <span class="text-gradient-brand"> Ethiopian Real Estate</span>
                <br class="hidden sm:block"> & Luxury Living
            </h1>

            <p class="text-gray-300 dark:text-gray-400 text-base sm:text-lg max-w-2xl mx-auto mb-10 leading-relaxed font-normal">
                Seamless title verification, PostGIS geocoding, and transparent pricing. From luxury villas in Bole Atlas to lakeview homes in Hawassa.
            </p>

            {{-- SEARCH ENGINE CONSOLE WITH 5 TABS --}}
            <div class="max-w-4xl mx-auto bg-white/10 dark:bg-[#111827]/80 backdrop-blur-xl border border-white/20 dark:border-gray-800 rounded-3xl p-3 sm:p-5 shadow-2xl">
                {{-- Mode Switcher Tabs --}}
                <div class="flex items-center justify-center gap-1 sm:gap-2 mb-4 p-1 bg-black/30 backdrop-blur-md rounded-2xl max-w-xl mx-auto">
                    <button type="button" @click="activeTab = 'buy'" 
                            :class="{ 'bg-primary-500 text-white font-bold shadow-md': activeTab === 'buy', 'text-gray-300 hover:text-white': activeTab !== 'buy' }"
                            class="px-4 py-2 rounded-xl text-xs sm:text-sm transition-all flex items-center gap-1.5">
                        <span>Buy</span>
                    </button>

                    <button type="button" @click="activeTab = 'rent'" 
                            :class="{ 'bg-primary-500 text-white font-bold shadow-md': activeTab === 'rent', 'text-gray-300 hover:text-white': activeTab !== 'rent' }"
                            class="px-4 py-2 rounded-xl text-xs sm:text-sm transition-all flex items-center gap-1.5">
                        <span>Rent</span>
                    </button>

                    <button type="button" @click="activeTab = 'stay'" 
                            :class="{ 'bg-primary-500 text-white font-bold shadow-md': activeTab === 'stay', 'text-gray-300 hover:text-white': activeTab !== 'stay' }"
                            class="px-4 py-2 rounded-xl text-xs sm:text-sm transition-all flex items-center gap-1.5">
                        <span>Short Stay</span>
                    </button>

                    <button type="button" @click="activeTab = 'land'" 
                            :class="{ 'bg-primary-500 text-white font-bold shadow-md': activeTab === 'land', 'text-gray-300 hover:text-white': activeTab !== 'land' }"
                            class="px-4 py-2 rounded-xl text-xs sm:text-sm transition-all flex items-center gap-1.5">
                        <span>Land</span>
                    </button>

                    <button type="button" @click="activeTab = 'projects'" 
                            :class="{ 'bg-primary-500 text-white font-bold shadow-md': activeTab === 'projects', 'text-gray-300 hover:text-white': activeTab !== 'projects' }"
                            class="px-4 py-2 rounded-xl text-xs sm:text-sm transition-all flex items-center gap-1.5">
                        <span>New Projects</span>
                    </button>
                </div>

                {{-- Search Inputs --}}
                <form action="{{ route('properties.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-2 text-left">
                    <input type="hidden" name="purpose" :value="purposeParam">

                    {{-- Location Input --}}
                    <div class="sm:col-span-6 bg-white dark:bg-[#1E293B] rounded-2xl px-4 py-3 border border-gray-200 dark:border-gray-700">
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400">Location / Neighborhood</label>
                        <div class="flex items-center gap-2 mt-0.5">
                            <svg class="w-4 h-4 text-primary-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.242-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            <input type="text" name="location" x-model="locationQuery" placeholder="e.g. Bole, Kazanchis, Old Airport..." class="bg-transparent text-gray-900 dark:text-white placeholder:text-gray-400 text-xs sm:text-sm outline-none w-full">
                        </div>
                    </div>

                    {{-- Bedrooms Selector --}}
                    <div class="sm:col-span-3 bg-white dark:bg-[#1E293B] rounded-2xl px-4 py-3 border border-gray-200 dark:border-gray-700">
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400">Bedrooms</label>
                        <select name="bedrooms" class="bg-transparent text-gray-900 dark:text-white text-xs sm:text-sm outline-none w-full mt-0.5 font-medium cursor-pointer">
                            <option value="any">Any Bedrooms</option>
                            <option value="1">1+ Bedrooms</option>
                            <option value="2">2+ Bedrooms</option>
                            <option value="3">3+ Bedrooms</option>
                            <option value="4">4+ Bedrooms</option>
                        </select>
                    </div>

                    {{-- Search CTA Button --}}
                    <div class="sm:col-span-3 flex">
                        <button type="submit" class="btn btn-primary w-full text-xs sm:text-sm font-bold py-3.5 rounded-2xl flex items-center justify-center gap-2 shadow-lg shadow-orange-500/25">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                            <span>Search</span>
                        </button>
                    </div>
                </form>

                {{-- Popular Neighborhood Quick Pills --}}
                <div class="flex items-center justify-center flex-wrap gap-2 mt-4 text-[11px] text-gray-300">
                    <span class="text-gray-400">Trending Areas:</span>
                    <button type="button" @click="locationQuery = 'Bole Atlas'" class="px-2 py-0.5 rounded-md bg-white/10 hover:bg-white/20 transition-colors">Bole Atlas</button>
                    <button type="button" @click="locationQuery = 'Kazanchis'" class="px-2 py-0.5 rounded-md bg-white/10 hover:bg-white/20 transition-colors">Kazanchis</button>
                    <button type="button" @click="locationQuery = 'Old Airport'" class="px-2 py-0.5 rounded-md bg-white/10 hover:bg-white/20 transition-colors">Old Airport</button>
                    <button type="button" @click="locationQuery = 'CMC Michael'" class="px-2 py-0.5 rounded-md bg-white/10 hover:bg-white/20 transition-colors">CMC Michael</button>
                    <button type="button" @click="locationQuery = 'Bisrate Gabriel'" class="px-2 py-0.5 rounded-md bg-white/10 hover:bg-white/20 transition-colors">Bisrate Gabriel</button>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════
         2. FEATURED PROPERTIES SECTION
         ═══════════════════════════════════════════════════════════ --}}
    <section class="py-20 bg-gray-50 dark:bg-[#0F172A] border-y border-gray-200 dark:border-gray-800">
        <div class="page-container">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-12">
                <div>
                    <div class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-primary-600 dark:text-primary-400 mb-2">
                        <span class="w-2 h-2 rounded-full bg-primary-500"></span>
                        Handpicked & Verified
                    </div>
                    <h2 class="text-3xl sm:text-4xl font-display font-extrabold text-gray-900 dark:text-white tracking-tight">
                        Featured Properties in Ethiopia
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 max-w-xl">
                        Explore premier residences and high-yield commercial investments verified by our Trust & Safety committee.
                    </p>
                </div>
                <a href="{{ route('properties.index') }}" class="btn btn-outline text-xs font-bold inline-flex items-center gap-1.5 self-start md:self-end">
                    Explore All Properties
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($featuredListings as $listing)
                    <x-property-card :listing="$listing" :isFeatured="true" />
                @endforeach
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════
         3. POPULAR STAYS SECTION (VACATION RENTALS & SERVICED PENTHOUSES)
         ═══════════════════════════════════════════════════════════ --}}
    @if($popularStays->count() > 0)
        <section class="py-20 bg-white dark:bg-[#0B0F19]">
            <div class="page-container">
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-12">
                    <div>
                        <div class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-purple-600 dark:text-purple-400 mb-2">
                            <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                            Short Stays & Serviced Living
                        </div>
                        <h2 class="text-3xl sm:text-4xl font-display font-extrabold text-gray-900 dark:text-white tracking-tight">
                            Curated Short Stays
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 max-w-xl">
                            Nightly executive penthouses, diplomatic apartments, and furnished getaways with reliable power and fiber internet.
                        </p>
                    </div>
                    <a href="{{ route('properties.index', ['purpose' => 'Short Stay']) }}" class="btn btn-outline text-xs font-bold inline-flex items-center gap-1.5 self-start md:self-end">
                        View All Short Stays
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($popularStays as $stay)
                        <x-property-card :listing="$stay" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ═══════════════════════════════════════════════════════════
         4. NEW DEVELOPMENTS SECTION (OFF-PLAN & LUXURY TOWERS)
         ═══════════════════════════════════════════════════════════ --}}
    @if($newDevelopments->count() > 0)
        <section class="py-20 bg-gray-50 dark:bg-[#0F172A] border-y border-gray-200 dark:border-gray-800">
            <div class="page-container">
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-12">
                    <div>
                        <div class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400 mb-2">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            Developer Direct Projects
                        </div>
                        <h2 class="text-3xl sm:text-4xl font-display font-extrabold text-gray-900 dark:text-white tracking-tight">
                            New Developments & Off-Plan
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 max-w-xl">
                            Direct partnerships with top Ethiopian real estate developers with bank-financed payment milestones and pre-construction pricing.
                        </p>
                    </div>
                    <a href="{{ route('properties.index', ['purpose' => 'Development']) }}" class="btn btn-outline text-xs font-bold inline-flex items-center gap-1.5 self-start md:self-end">
                        Browse New Developments
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($newDevelopments as $dev)
                        <x-property-card :listing="$dev" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ═══════════════════════════════════════════════════════════
         5. POPULAR ETHIOPIAN CITIES SECTION
         ═══════════════════════════════════════════════════════════ --}}
    <section class="py-20 bg-white dark:bg-[#0B0F19]">
        <div class="page-container">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <div class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-primary-600 dark:text-primary-400 mb-2">
                    Regional Real Estate
                </div>
                <h2 class="text-3xl sm:text-4xl font-display font-extrabold text-gray-900 dark:text-white tracking-tight">
                    Explore Popular Cities in Ethiopia
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                    Find prime residential estates and commercial properties across the country's most dynamic regional economies.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($popularCities as $city)
                    <a href="{{ route('properties.index', ['location' => $city['query']]) }}" 
                       class="relative h-64 rounded-3xl overflow-hidden group shadow-md hover:shadow-2xl transition-all duration-500">
                        <img src="{{ $city['image'] }}" alt="{{ $city['name'] }}" 
                             loading="lazy"
                             class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 ease-out">
                        
                        <div class="absolute inset-0 bg-gradient-to-t from-gray-950/95 via-gray-950/50 to-transparent p-6 flex flex-col justify-end">
                            <div class="flex items-baseline justify-between">
                                <div>
                                    <h3 class="text-2xl font-display font-black text-white tracking-tight">
                                        {{ $city['name'] }}
                                    </h3>
                                    <span class="text-xs font-bold text-primary-400">{{ $city['amharic'] }}</span>
                                </div>
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-white/20 backdrop-blur-md text-white border border-white/20">
                                    {{ $city['count'] }} Listings
                                </span>
                            </div>
                            <p class="text-xs text-gray-300 mt-2 line-clamp-1">
                                {{ $city['description'] }}
                            </p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════
         6. TRUST & VERIFICATION PILLARS
         ═══════════════════════════════════════════════════════════ --}}
    <section class="py-20 bg-gray-50 dark:bg-[#0F172A] border-t border-gray-200 dark:border-gray-800">
        <div class="page-container">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <span class="text-xs font-bold uppercase tracking-wider text-primary-600 dark:text-primary-400">Institutional Trust</span>
                <h2 class="text-3xl font-display font-extrabold text-gray-900 dark:text-white mt-1">
                    Built for Transparency & Safety
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-2">
                    Every commercial transaction on SRMS is backed by rigorous legal and technical safeguards.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white dark:bg-[#1E293B] p-6 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm text-center">
                    <div class="w-12 h-12 rounded-2xl bg-orange-100 dark:bg-orange-950/40 text-primary-600 dark:text-primary-400 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                    </div>
                    <h3 class="font-bold text-sm text-gray-900 dark:text-white">Title Deed Verification</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 leading-relaxed">
                        Cross-referenced with municipal land administration registries to eliminate duplicate sales and unverified titles.
                    </p>
                </div>

                <div class="bg-white dark:bg-[#1E293B] p-6 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm text-center">
                    <div class="w-12 h-12 rounded-2xl bg-blue-100 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.242-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    </div>
                    <h3 class="font-bold text-sm text-gray-900 dark:text-white">PostGIS Geocoded Accuracy</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 leading-relaxed">
                        Precise coordinates and boundary polygons ensure accurate neighborhood exploration with privacy radius protection.
                    </p>
                </div>

                <div class="bg-white dark:bg-[#1E293B] p-6 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm text-center">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-100 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <h3 class="font-bold text-sm text-gray-900 dark:text-white">Escrow & Secure Payments</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 leading-relaxed">
                        Funds released only upon verified property handover and physical inspection confirmation.
                    </p>
                </div>

                <div class="bg-white dark:bg-[#1E293B] p-6 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm text-center">
                    <div class="w-12 h-12 rounded-2xl bg-purple-100 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                    </div>
                    <h3 class="font-bold text-sm text-gray-900 dark:text-white">Developer Direct Escrow</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 leading-relaxed">
                        Official partnerships with Grade 1 contractors for verified construction completion milestones.
                    </p>
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
