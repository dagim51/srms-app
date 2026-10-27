<x-layouts.app>
    <x-slot name="title">{{ __("AURA ESTATES | Modern Luxury Real Estate & Verified Property Registry") }}</x-slot>
    <x-slot name="description">{{ __("Discover handpicked coastal villas, sky penthouses, and sustainable architectural estates across Addis Ababa and Ethiopia's premier destinations.") }}</x-slot>

    {{-- ==================== HERO SECTION (AURA ESTATES EDITORIAL LAYOUT) ==================== --}}
    <section class="relative pt-28 pb-20 lg:pt-36 lg:pb-28 overflow-hidden" 
             aria-labelledby="hero-heading"
             x-data="{
                activeTab: 'all',
                locationQuery: '',
                propertyType: 'all',
                priceRange: 'all',

                get purposeParam() {
                    return {
                        'all': 'all',
                        'buy': 'For Sale',
                        'rent': 'For Rent',
                        'land': 'Land',
                        'projects': 'Development'
                    }[this.activeTab] || 'all';
                }
             }">
        <!-- Decorative Ambient Background Glows -->
        <div class="absolute top-12 left-1/4 w-96 h-96 bg-orange-500/15 dark:bg-orange-500/10 rounded-full blur-3xl pointer-events-none" aria-hidden="true"></div>
        <div class="absolute bottom-10 right-10 w-80 h-80 bg-amber-500/15 dark:bg-orange-600/10 rounded-full blur-3xl pointer-events-none" aria-hidden="true"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-12">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                
                <!-- Left Column: Hero Copy & Badges -->
                <div class="lg:col-span-7 space-y-6 cinematic-fade-up cinematic-stagger-1">
                    <div class="inline-flex items-center gap-2.5 px-4 py-1.5 rounded-full bg-orange-100/90 dark:bg-orange-950/70 border border-orange-200 dark:border-orange-800/80 text-orange-700 dark:text-orange-300 text-xs font-semibold tracking-wide uppercase">
                        <span class="w-2 h-2 rounded-full bg-orange-500 animate-ping"></span>
                        <span>{{ __('2026 Architectural Collection') }}</span>
                    </div>

                    <h1 id="hero-heading" class="font-serif text-4xl sm:text-6xl lg:text-[64px] font-bold tracking-tight leading-[1.08] text-slate-900 dark:text-white">
                        {{ __('Curating Spaces Where') }} <span class="italic font-normal text-transparent bg-clip-text bg-gradient-to-r from-orange-600 to-amber-500 dark:from-orange-400 dark:to-amber-300">{{ __('Architecture') }}</span> {{ __('Meets Serenity.') }}
                    </h1>

                    <p class="text-slate-600 dark:text-slate-300 text-base sm:text-lg max-w-xl leading-relaxed">
                        {{ __('Discover handpicked cliff villas, sky penthouses, and master-planned architectural estates with verified title registry across Ethiopia.') }}
                    </p>

                    <!-- Quick Trust Highlights -->
                    <div class="flex flex-wrap items-center gap-6 pt-2">
                        <div class="flex -space-x-3">
                            <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=120&q=80" onerror="this.src='https://placehold.co/100x100/ea580c/ffffff?text=A'" alt="Client" class="w-10 h-10 rounded-full border-2 border-white dark:border-slate-900 object-cover">
                            <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=120&q=80" onerror="this.src='https://placehold.co/100x100/c2410c/ffffff?text=B'" alt="Client" class="w-10 h-10 rounded-full border-2 border-white dark:border-slate-900 object-cover">
                            <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=120&q=80" onerror="this.src='https://placehold.co/100x100/f97316/ffffff?text=C'" alt="Client" class="w-10 h-10 rounded-full border-2 border-white dark:border-slate-900 object-cover">
                            <div class="w-10 h-10 rounded-full border-2 border-white dark:border-slate-900 bg-gradient-to-br from-orange-500 to-orange-600 text-white text-xs font-bold flex items-center justify-center shadow-sm">4.9★</div>
                        </div>
                        <div class="text-sm">
                            <p class="font-semibold text-slate-900 dark:text-white">{{ __('Trusted by 12,500+ Discerning Buyers') }}</p>
                            <p class="text-slate-500 dark:text-slate-400 text-xs">{{ __('Verified private off-market & signature registry') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Visual Showcase with Floating Cards -->
                <div class="lg:col-span-5 relative cinematic-fade-up cinematic-stagger-2">
                    <div class="relative rounded-3xl overflow-hidden shadow-2xl border-4 border-white dark:border-slate-800 aspect-[4/5] group">
                        <img src="https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=1200&q=85" 
                             onerror="this.src='https://placehold.co/800x1000/431407/ffffff?text=Bole+Skylight+Villa'" 
                             alt="Luxury Modern Residence" 
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/85 via-slate-950/20 to-transparent"></div>
                        
                        <!-- Bottom Overlay Info -->
                        <div class="absolute bottom-6 left-6 right-6 text-white flex items-end justify-between">
                            <div>
                                <span class="px-2.5 py-1 rounded-md bg-orange-500 text-white font-bold text-xs uppercase tracking-wider shadow-sm">{{ __('Featured Residence') }}</span>
                                <h3 class="font-serif text-2xl font-bold mt-2">The Bole Skylight Villa</h3>
                                <p class="text-slate-200 text-sm flex items-center gap-1.5 mt-0.5">
                                    <svg class="w-4 h-4 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                                    <span>Bole Atlas, Addis Ababa</span>
                                </p>
                            </div>
                            <a href="{{ route('properties.index') }}" class="w-12 h-12 rounded-2xl bg-orange-500/90 hover:bg-orange-500 text-white backdrop-blur-md flex items-center justify-center transition-all shadow-lg shadow-orange-600/30">
                                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                            </a>
                        </div>
                    </div>

                    <!-- Floating Animated Card 1: Live Yield -->
                    <div class="card hidden sm:flex items-center gap-3.5 absolute -left-8 top-10 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md p-4 rounded-2xl border border-slate-200/80 dark:border-slate-800 animate-bounce duration-1000">
                        <div class="w-11 h-11 rounded-xl bg-orange-500/15 text-orange-600 dark:text-orange-400 flex items-center justify-center text-lg">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" /></svg>
                        </div>
                        <div>
                            <span class="text-xs text-slate-500 dark:text-slate-400 block">{{ __('Avg. Annual Yield') }}</span>
                            <span class="text-base font-bold text-slate-900 dark:text-white">+14.8% Appreciation</span>
                        </div>
                    </div>

                    <!-- Floating Animated Card 2: 360 Tour Badge -->
                    <a href="{{ route('properties.index') }}" class="card cursor-pointer hidden sm:flex items-center gap-3 absolute -right-4 sm:-right-6 bottom-28 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md p-3.5 rounded-2xl border border-slate-200/80 dark:border-slate-800 hover:scale-105 transition-transform">
                        <div class="w-10 h-10 rounded-xl bg-orange-500/20 text-orange-600 dark:text-orange-400 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" /></svg>
                        </div>
                        <div class="pr-2">
                            <span class="text-xs font-bold text-slate-900 dark:text-white block">{{ __('360° Virtual Tour') }}</span>
                            <span class="text-[11px] text-orange-600 dark:text-orange-400 font-medium">{{ __('Click to explore →') }}</span>
                        </div>
                    </a>
                </div>
            </div>

            <!-- ==================== INTERACTIVE SEARCH CONSOLE ==================== -->
            <div class="mt-14 lg:mt-16 relative z-20 max-w-6xl mx-auto cinematic-fade-up cinematic-stagger-2">
                <div class="card bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/90 dark:border-slate-800 p-4 sm:p-6">
                    
                    <!-- Status Tabs (All / For Sale / For Rent / Land / New Projects) -->
                    <div class="flex items-center justify-between flex-wrap gap-4 pb-4 mb-4 border-b border-slate-100 dark:border-slate-800">
                        <div class="inline-flex p-1 rounded-xl bg-slate-100 dark:bg-slate-800 flex-wrap gap-1">
                            <button type="button" @click="activeTab = 'all'" :class="activeTab === 'all' ? 'bg-orange-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-orange-600'" class="px-4 py-2 rounded-lg text-xs sm:text-sm font-semibold transition-all cursor-pointer">
                                {{ __('All Listings') }}
                            </button>
                            <button type="button" @click="activeTab = 'buy'" :class="activeTab === 'buy' ? 'bg-orange-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-orange-600'" class="px-4 py-2 rounded-lg text-xs sm:text-sm font-semibold transition-all cursor-pointer">
                                {{ __('Buy Property') }}
                            </button>
                            <button type="button" @click="activeTab = 'rent'" :class="activeTab === 'rent' ? 'bg-orange-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-orange-600'" class="px-4 py-2 rounded-lg text-xs sm:text-sm font-semibold transition-all cursor-pointer">
                                {{ __('Luxury Rentals') }}
                            </button>
                            <button type="button" @click="activeTab = 'land'" :class="activeTab === 'land' ? 'bg-orange-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-orange-600'" class="px-4 py-2 rounded-lg text-xs sm:text-sm font-semibold transition-all cursor-pointer">
                                {{ __('Land & Plots') }}
                            </button>
                            <button type="button" @click="activeTab = 'projects'" :class="activeTab === 'projects' ? 'bg-orange-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-orange-600'" class="px-4 py-2 rounded-lg text-xs sm:text-sm font-semibold transition-all cursor-pointer">
                                {{ __('New Projects') }}
                            </button>
                        </div>

                        <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>{{ __('Verified marketplace across') }} <strong class="text-orange-600 dark:text-orange-400">{{ $featuredListings->count() + 12 }}</strong> {{ __('curated properties') }}</span>
                        </div>
                    </div>

                    <!-- Search Inputs Grid -->
                    <form action="{{ route('search') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4 items-center">
                        <input type="hidden" name="purpose" :value="purposeParam">

                        <!-- Keyword / Location Input -->
                        <div class="lg:col-span-4 relative">
                            <label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-1">{{ __('Location or Keyword') }}</label>
                            <div class="relative">
                                <svg class="w-4 h-4 text-orange-500 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                                <input type="text" name="location" x-model="locationQuery" placeholder="{{ __('Bole Atlas, Kazanchis, Hawassa...') }}" 
                                       class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-800/70 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:border-orange-500 dark:focus:border-orange-400 transition-colors font-medium">
                            </div>
                        </div>

                        <!-- Architectural Style Dropdown -->
                        <div class="lg:col-span-3">
                            <label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-1">{{ __('Architectural Style') }}</label>
                            <select name="type" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800/70 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:border-orange-500 dark:focus:border-orange-400 transition-colors font-medium cursor-pointer">
                                <option value="all">{{ __('All Property Types') }}</option>
                                <option value="Villa">{{ __('Luxury Villas & Estates') }}</option>
                                <option value="Apartment">{{ __('Sky Penthouses & Condos') }}</option>
                                <option value="Townhouse">{{ __('Townhouses & Compounds') }}</option>
                                <option value="Commercial">{{ __('Commercial Headquarters') }}</option>
                                <option value="Land">{{ __('Residential / Industrial Plots') }}</option>
                            </select>
                        </div>

                        <!-- Price Range Dropdown -->
                        <div class="lg:col-span-3">
                            <label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-1">{{ __('Price Ceiling') }}</label>
                            <select name="max_price" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800/70 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:border-orange-500 dark:focus:border-orange-400 transition-colors font-medium cursor-pointer">
                                <option value="">{{ __('Any Price') }}</option>
                                <option value="5000000">Up to ETB 5,000,000</option>
                                <option value="15000000">Up to ETB 15,000,000</option>
                                <option value="30000000">Up to ETB 30,000,000</option>
                                <option value="50000000">Up to ETB 50,000,000+</option>
                            </select>
                        </div>

                        <!-- Search Button -->
                        <div class="lg:col-span-2 flex gap-2 pt-3 sm:pt-5 lg:pt-4">
                            <button type="submit" class="btn btn-primary w-full py-3 px-4 bg-gradient-to-r from-orange-600 to-orange-500 hover:from-orange-500 hover:to-orange-600 text-white font-semibold text-sm rounded-xl shadow-md shadow-orange-500/25 hover:shadow-lg hover:shadow-orange-500/40 transition-all flex items-center justify-center gap-2 cursor-pointer">
                                <span>{{ __('Explore') }}</span>
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                            </button>
                        </div>
                    </form>

                    <!-- Popular Area Pills -->
                    <div class="flex items-center gap-2 flex-wrap pt-4 mt-4 border-t border-slate-100 dark:border-slate-800 text-xs">
                        <span class="text-slate-500 dark:text-slate-400 font-semibold">{{ __('Trending Destinations:') }}</span>
                        <button type="button" @click="locationQuery = 'Bole Atlas'" class="px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-800 hover:bg-orange-50 dark:hover:bg-slate-700 hover:text-orange-600 text-slate-700 dark:text-slate-300 font-medium transition-colors cursor-pointer">Bole Atlas</button>
                        <button type="button" @click="locationQuery = 'Kazanchis'" class="px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-800 hover:bg-orange-50 dark:hover:bg-slate-700 hover:text-orange-600 text-slate-700 dark:text-slate-300 font-medium transition-colors cursor-pointer">Kazanchis</button>
                        <button type="button" @click="locationQuery = 'Old Airport'" class="px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-800 hover:bg-orange-50 dark:hover:bg-slate-700 hover:text-orange-600 text-slate-700 dark:text-slate-300 font-medium transition-colors cursor-pointer">Old Airport</button>
                        <button type="button" @click="locationQuery = 'CMC Michael'" class="px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-800 hover:bg-orange-50 dark:hover:bg-slate-700 hover:text-orange-600 text-slate-700 dark:text-slate-300 font-medium transition-colors cursor-pointer">CMC Michael</button>
                        <button type="button" @click="locationQuery = 'Hawassa'" class="px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-800 hover:bg-orange-50 dark:hover:bg-slate-700 hover:text-orange-600 text-slate-700 dark:text-slate-300 font-medium transition-colors cursor-pointer">Hawassa</button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ==================== EDITORIAL STATS COUNTER BAR ==================== --}}
    <section id="stats-section" class="py-12 border-y border-slate-200/80 dark:border-slate-800/80 bg-white/60 dark:bg-slate-900/40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-12">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 text-center">
                <!-- Stat 1 -->
                <div class="card p-4 rounded-2xl cinematic-fade-up cinematic-stagger-1">
                    <div class="font-serif text-3xl sm:text-5xl font-bold text-orange-600 dark:text-orange-400 flex items-center justify-center">
                        <span>ETB 18B+</span>
                    </div>
                    <p class="text-sm font-medium text-slate-600 dark:text-slate-400 mt-2">{{ __('Total Volume Registered') }}</p>
                </div>

                <!-- Stat 2 -->
                <div class="card p-4 rounded-2xl cinematic-fade-up cinematic-stagger-2">
                    <div class="font-serif text-3xl sm:text-5xl font-bold text-orange-600 dark:text-orange-400 flex items-center justify-center">
                        <span>3,200+</span>
                    </div>
                    <p class="text-sm font-medium text-slate-600 dark:text-slate-400 mt-2">{{ __('Verified Listings') }}</p>
                </div>

                <!-- Stat 3 -->
                <div class="card p-4 rounded-2xl cinematic-fade-up cinematic-stagger-3">
                    <div class="font-serif text-3xl sm:text-5xl font-bold text-orange-600 dark:text-orange-400 flex items-center justify-center">
                        <span>100%</span>
                    </div>
                    <p class="text-sm font-medium text-slate-600 dark:text-slate-400 mt-2">{{ __('Deed Authenticated') }}</p>
                </div>

                <!-- Stat 4 -->
                <div class="card p-4 rounded-2xl cinematic-fade-up cinematic-stagger-4">
                    <div class="font-serif text-3xl sm:text-5xl font-bold text-orange-600 dark:text-orange-400 flex items-center justify-center">
                        <span>12</span>
                    </div>
                    <p class="text-sm font-medium text-slate-600 dark:text-slate-400 mt-2">{{ __('Regional Architectural Hubs') }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ==================== CURATED RESIDENCES PORTFOLIO SECTION ==================== --}}
    <section id="properties" class="py-20 lg:py-28">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-12">
            
            <!-- Section Header & Category Pills -->
            <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6 mb-12">
                <div>
                    <span class="text-xs font-bold uppercase tracking-[0.2em] text-orange-600 dark:text-orange-400 block mb-2">{{ __('Portfolio') }}</span>
                    <h2 class="font-serif text-3xl sm:text-5xl font-bold text-slate-900 dark:text-white">{{ __('Curated Residences') }}</h2>
                    <p class="text-slate-600 dark:text-slate-400 text-sm mt-2 max-w-xl">{{ __('Explore prime residences and high-yield properties authenticated by municipal land administration.') }}</p>
                </div>

                <!-- Category Pills & View All Link -->
                <div class="flex items-center gap-3 flex-wrap">
                    <a href="{{ route('properties.index') }}" class="btn btn-secondary px-5 py-2.5 rounded-xl text-xs font-semibold bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:text-orange-600 hover:border-orange-500 transition-colors shadow-sm inline-flex items-center gap-2">
                        <span>{{ __('Explore All Properties') }}</span>
                        <svg class="w-4 h-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                    </a>
                </div>
            </div>

            <!-- Dynamic Property Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($featuredListings as $listing)
                    <div class="cinematic-fade-up cinematic-stagger-{{ ($loop->index % 4) + 1 }}">
                        <x-property-card :listing="$listing" :isFeatured="true" />
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ==================== ARCHITECTURAL SPOTLIGHT SHOWCASE (OBSIDIAN DARK CONTRAST) ==================== --}}
    <section id="spotlight" class="py-20 bg-slate-900 dark:bg-slate-950 text-white relative overflow-hidden">
        <!-- Ambient Orange Glow -->
        <div class="absolute -top-24 right-1/4 w-96 h-96 bg-orange-600/20 rounded-full blur-3xl pointer-events-none" aria-hidden="true"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-12 relative z-10">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-12 gap-6">
                <div>
                    <span class="text-xs font-bold uppercase tracking-[0.2em] text-orange-400 block mb-2">{{ __('Signature Collection') }}</span>
                    <h2 class="font-serif text-3xl sm:text-5xl font-bold">{{ __('Architectural Spotlight') }}</h2>
                    <p class="text-slate-300 text-sm sm:text-base mt-2 max-w-xl">{{ __('Tour iconic trophy residences, penthouses, and private estates curated across Ethiopia.') }}</p>
                </div>

                <a href="{{ route('properties.index', ['purpose' => 'Development']) }}" class="btn btn-secondary px-5 py-2.5 rounded-xl text-xs font-semibold bg-slate-800 border border-slate-700 hover:bg-orange-600 hover:border-orange-600 text-white transition-all self-start sm:self-auto inline-flex items-center gap-2">
                    <span>{{ __('View Masterplans') }}</span>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                </a>
            </div>

            <!-- Snap Carousel Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Spotlight Item 1 -->
                <div class="card card-hover bg-slate-800/60 border border-slate-700/80 hover:border-orange-500/50 rounded-3xl overflow-hidden flex flex-col justify-between group transition-colors">
                    <div class="relative h-72 overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1613490493576-7fde63acd811?auto=format&fit=crop&w=800&q=80" 
                             alt="Villa Celestia" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                        <div class="absolute top-4 left-4 px-3 py-1 rounded-full bg-slate-950/80 backdrop-blur-md text-orange-400 text-xs font-semibold border border-orange-500/30">
                            {{ __('Off-Market Trophy') }}
                        </div>
                        <div class="absolute bottom-4 right-4 px-3.5 py-1.5 rounded-xl bg-orange-600 text-white font-bold text-sm shadow-lg">
                            ETB 45,000,000
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center gap-2 text-slate-300 text-xs uppercase tracking-wider mb-2">
                            <svg class="w-3.5 h-3.5 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                            <span>Bole Medhanialem, Addis Ababa</span>
                        </div>
                        <h3 class="font-serif text-2xl font-bold text-white">Villa Celestia Sky Garden</h3>
                        <p class="text-slate-300/80 text-sm mt-2 line-clamp-2">{{ __('Featuring 18-foot cantilevered glass terraces, geothermal cooling, zero-edge heated pool, and private wine cellar.') }}</p>
                        <div class="mt-6 pt-4 border-t border-slate-700/70 flex items-center justify-between text-xs text-slate-300">
                            <span><strong class="text-white">6</strong> Beds • <strong class="text-white">7</strong> Baths • <strong class="text-white">920</strong> m²</span>
                            <a href="{{ route('properties.index') }}" class="text-orange-400 font-semibold hover:text-orange-300 hover:underline flex items-center gap-1">
                                <span>{{ __('Inspect Dossier') }}</span>
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Spotlight Item 2 -->
                <div class="card card-hover bg-slate-800/60 border border-slate-700/80 hover:border-orange-500/50 rounded-3xl overflow-hidden flex flex-col justify-between group transition-colors">
                    <div class="relative h-72 overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&w=800&q=80" 
                             alt="The Crown Triplex" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                        <div class="absolute top-4 left-4 px-3 py-1 rounded-full bg-slate-950/80 backdrop-blur-md text-orange-400 text-xs font-semibold border border-orange-500/30">
                            {{ __('Private Helipad Access') }}
                        </div>
                        <div class="absolute bottom-4 right-4 px-3.5 py-1.5 rounded-xl bg-orange-600 text-white font-bold text-sm shadow-lg">
                            ETB 38,500,000
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center gap-2 text-slate-300 text-xs uppercase tracking-wider mb-2">
                            <svg class="w-3.5 h-3.5 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                            <span>Kazanchis Skyline, Addis Ababa</span>
                        </div>
                        <h3 class="font-serif text-2xl font-bold text-white">The Crown Skyline Penthouse</h3>
                        <p class="text-slate-300/80 text-sm mt-2 line-clamp-2">{{ __('360-degree panoramic skyline vistas, private keyed elevator gallery, and double-height glass atrium.') }}</p>
                        <div class="mt-6 pt-4 border-t border-slate-700/70 flex items-center justify-between text-xs text-slate-300">
                            <span><strong class="text-white">4</strong> Beds • <strong class="text-white">5</strong> Baths • <strong class="text-white">680</strong> m²</span>
                            <a href="{{ route('properties.index') }}" class="text-orange-400 font-semibold hover:text-orange-300 hover:underline flex items-center gap-1">
                                <span>{{ __('Inspect Dossier') }}</span>
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Spotlight Item 3 -->
                <div class="card card-hover bg-slate-800/60 border border-slate-700/80 hover:border-orange-500/50 rounded-3xl overflow-hidden flex flex-col justify-between group transition-colors">
                    <div class="relative h-72 overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=800&q=80" 
                             alt="Hawassa Sanctuary" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                        <div class="absolute top-4 left-4 px-3 py-1 rounded-full bg-slate-950/80 backdrop-blur-md text-orange-400 text-xs font-semibold border border-orange-500/30">
                            {{ __('Lakefront Compound') }}
                        </div>
                        <div class="absolute bottom-4 right-4 px-3.5 py-1.5 rounded-xl bg-orange-600 text-white font-bold text-sm shadow-lg">
                            ETB 29,000,000
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center gap-2 text-slate-300 text-xs uppercase tracking-wider mb-2">
                            <svg class="w-3.5 h-3.5 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                            <span>Lake Hawassa, Sidama</span>
                        </div>
                        <h3 class="font-serif text-2xl font-bold text-white">Hawassa Waterfront Sanctuary</h3>
                        <p class="text-slate-300/80 text-sm mt-2 line-clamp-2">{{ __('Eucalyptus and volcanic stone compound featuring private boat dock, solar microgrid, and botanical courtyards.') }}</p>
                        <div class="mt-6 pt-4 border-t border-slate-700/70 flex items-center justify-between text-xs text-slate-300">
                            <span><strong class="text-white">5</strong> Beds • <strong class="text-white">6</strong> Baths • <strong class="text-white">850</strong> m²</span>
                            <a href="{{ route('properties.index') }}" class="text-orange-400 font-semibold hover:text-orange-300 hover:underline flex items-center gap-1">
                                <span>{{ __('Inspect Dossier') }}</span>
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ==================== VALUE PROPOSITION / THE AURA STANDARD ==================== --}}
    <section id="why-us" class="py-20 lg:py-28">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-12">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <span class="text-xs font-bold uppercase tracking-[0.2em] text-orange-600 dark:text-orange-400 block mb-2">{{ __('Institutional Safeguards') }}</span>
                <h2 class="font-serif text-3xl sm:text-5xl font-bold text-slate-900 dark:text-white">{{ __('Redefining Private Real Estate Advisory') }}</h2>
                <p class="text-slate-600 dark:text-slate-400 text-sm sm:text-base mt-3">{{ __('From PostGIS geocoded cadastral mapping to milestone-based escrow, every transaction is engineered for discretion and precision.') }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="card p-8 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 hover:border-orange-500/40 hover: transition-all duration-300 group cinematic-fade-up cinematic-stagger-1">
                    <div class="w-14 h-14 rounded-2xl bg-orange-500/10 text-orange-600 dark:text-orange-400 flex items-center justify-center text-2xl mb-6 group-hover:scale-110 group-hover:bg-orange-500 group-hover:text-white transition-all">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" /></svg>
                    </div>
                    <h3 class="font-serif text-xl font-bold text-slate-900 dark:text-white mb-3">{{ __('Private Off-Market Vault') }}</h3>
                    <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed">
                        {{ __('Gain priority access to confidential residential estates and prime commercial holdings that never appear on public listings.') }}
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="card p-8 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 hover:border-orange-500/40 hover: transition-all duration-300 group cinematic-fade-up cinematic-stagger-2">
                    <div class="w-14 h-14 rounded-2xl bg-orange-500/10 text-orange-600 dark:text-orange-400 flex items-center justify-center text-2xl mb-6 group-hover:scale-110 group-hover:bg-orange-500 group-hover:text-white transition-all">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-2.171-6.171a11.956 11.956 0 00-6.829 0l-.828.276a1.5 1.5 0 00-1.026 1.42v6.236c0 5.498 3.864 10.158 9.07 11.439a1.5 1.5 0 00.77 0c5.206-1.281 9.07-5.941 9.07-11.439V5.275a1.5 1.5 0 00-1.026-1.42l-.828-.276a11.956 11.956 0 00-6.829 0z" /></svg>
                    </div>
                    <h3 class="font-serif text-xl font-bold text-slate-900 dark:text-white mb-3">{{ __('Cadastral & Title Vetting') }}</h3>
                    <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed">
                        {{ __('Every listing undergoes rigorous verification against municipal land administration records with PostGIS polygon boundary validation.') }}
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="card p-8 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 hover:border-orange-500/40 hover: transition-all duration-300 group cinematic-fade-up cinematic-stagger-3">
                    <div class="w-14 h-14 rounded-2xl bg-orange-500/10 text-orange-600 dark:text-orange-400 flex items-center justify-center text-2xl mb-6 group-hover:scale-110 group-hover:bg-orange-500 group-hover:text-white transition-all">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <h3 class="font-serif text-xl font-bold text-slate-900 dark:text-white mb-3">{{ __('White-Glove Escrow Closing') }}</h3>
                    <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed">
                        {{ __('Funds remain safeguarded in banking escrow and are released strictly upon physical property handover and title registration.') }}
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- ==================== CLIENT PERSPECTIVES (TESTIMONIALS) ==================== --}}
    <section id="testimonials" class="py-20 bg-slate-100/80 dark:bg-slate-900/60 border-y border-slate-200/70 dark:border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-12">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                
                <!-- Left Column Heading -->
                <div class="lg:col-span-5">
                    <span class="text-xs font-bold uppercase tracking-[0.2em] text-orange-600 dark:text-orange-400 block mb-2">{{ __('Client Perspectives') }}</span>
                    <h2 class="font-serif text-3xl sm:text-5xl font-bold text-slate-900 dark:text-white leading-tight">{{ __('Stories From Those Who Found Home.') }}</h2>
                    <p class="text-slate-600 dark:text-slate-400 text-sm sm:text-base mt-4">
                        {{ __('Our relationships span generations across Ethiopia and the diaspora. Discover how our advisory team navigates acquisitions with effortless grace.') }}
                    </p>
                </div>

                <!-- Right Column Testimonial Card -->
                <div class="lg:col-span-7">
                    <div class="card bg-white dark:bg-slate-900 rounded-3xl p-8 sm:p-12 border border-slate-200/80 dark:border-slate-800 relative">
                        <div class="flex items-center gap-1 text-orange-500 text-sm mb-6">
                            <span>★★★★★</span>
                        </div>

                        <p class="font-serif text-xl sm:text-2xl text-slate-800 dark:text-slate-100 leading-relaxed italic">
                            "Aura Estates and SRMS secured our penthouse in Bole Atlas six months before public listing. Their title verification and milestone escrow gave our diaspora family complete peace of mind."
                        </p>

                        <div class="flex items-center justify-between flex-wrap gap-4 mt-8 pt-6 border-t border-slate-100 dark:border-slate-800">
                            <div class="flex items-center gap-4">
                                <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=200&q=80" 
                                     alt="Client Avatar" class="w-14 h-14 rounded-2xl object-cover ring-2 ring-orange-500/30">
                                <div>
                                    <h4 class="font-bold text-slate-900 dark:text-white text-base">Elena & Marcus Vance</h4>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Acquired Luxury Villa • Bole Atlas, Addis Ababa</p>
                                </div>
                            </div>
                            <span class="px-3 py-1 rounded-full bg-orange-500/10 text-orange-600 dark:text-orange-400 text-xs font-semibold">
                                ✓ {{ __('Verified Buyer') }}
                            </span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- ==================== CONCIERGE & PRIVATE VIEWING BOOKING ==================== --}}
    <section id="contact" class="py-20 lg:py-28">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-12">
            <div class="card bg-slate-900 text-white rounded-3xl overflow-hidden border border-slate-800">
                <div class="grid grid-cols-1 lg:grid-cols-12">
                    
                    <!-- Left Column: Advisory Info -->
                    <div class="lg:col-span-5 p-8 sm:p-12 bg-gradient-to-br from-slate-900 via-slate-900 to-orange-950/70 flex flex-col justify-between relative overflow-hidden">
                        <div class="relative z-10 space-y-6">
                            <div>
                                <span class="text-xs font-bold uppercase tracking-[0.2em] text-orange-400 block mb-2">{{ __('Private Concierge') }}</span>
                                <h2 class="font-serif text-3xl sm:text-4xl font-bold leading-tight">{{ __('Schedule a Private Consultation') }}</h2>
                                <p class="text-slate-300 text-sm mt-3 leading-relaxed">
                                    {{ __('Whether acquiring a signature residence or positioning an estate for global sale, our advisory desk is at your service.') }}
                                </p>
                            </div>

                            <div class="space-y-4 pt-2">
                                <div class="flex items-start gap-4">
                                    <div class="w-10 h-10 rounded-xl bg-orange-500/15 text-orange-400 border border-orange-500/30 flex items-center justify-center shrink-0 mt-0.5">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-sm">{{ __('Addis Ababa Flagship') }}</h4>
                                        <p class="text-slate-300 text-xs mt-0.5">Bole Medhanialem Tower, Suite 900, Addis Ababa</p>
                                    </div>
                                </div>

                                <div class="flex items-start gap-4">
                                    <div class="w-10 h-10 rounded-xl bg-orange-500/15 text-orange-400 border border-orange-500/30 flex items-center justify-center shrink-0 mt-0.5">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" /></svg>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-sm">{{ __('Direct Advisory Desk') }}</h4>
                                        <p class="text-slate-300 text-xs mt-0.5">+251 (11) 661-AURA • Available 24/7</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Managing Partner Card -->
                        <div class="mt-8 pt-6 border-t border-slate-800 flex items-center gap-4 relative z-10">
                            <img src="https://images.unsplash.com/photo-1560250097-0b93528c311a?auto=format&fit=crop&w=150&q=80" 
                                 alt="Managing Partner" class="w-12 h-12 rounded-full object-cover border-2 border-orange-500">
                            <div>
                                <p class="text-sm font-semibold">Dawit Hailemariam</p>
                                <p class="text-xs text-orange-400">{{ __('Head of Architectural Acquisitions') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Interactive Booking Form -->
                    <div class="lg:col-span-7 p-8 sm:p-12 bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
                        <form onsubmit="event.preventDefault(); alert('Your viewing inquiry has been registered. An advisor will contact you within 2 hours.');" class="space-y-4">
                            <h3 class="font-serif text-2xl font-bold mb-2">{{ __('Request Private Dossier or Tour') }}</h3>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">{{ __('Full Name') }} *</label>
                                    <input type="text" required placeholder="Alexander Wright" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm focus:outline-none focus:border-orange-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">{{ __('Private Email') }} *</label>
                                    <input type="email" required placeholder="alexander@invest.com" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm focus:outline-none focus:border-orange-500">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">{{ __('Phone Number') }}</label>
                                    <input type="tel" placeholder="+251 91 123 4567" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm focus:outline-none focus:border-orange-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">{{ __('Residence Category') }}</label>
                                    <select class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm focus:outline-none focus:border-orange-500">
                                        <option value="Villa">Luxury Villa or Compound</option>
                                        <option value="Penthouse">Sky Penthouse</option>
                                        <option value="Masterplan">Off-Plan Development</option>
                                        <option value="Commercial">Commercial Tower</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">{{ __('Bespoke Requirements') }}</label>
                                <textarea rows="3" placeholder="{{ __('Tell us about your architectural preferences, location, or preferred timeline...') }}" class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm focus:outline-none focus:border-orange-500"></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary w-full py-3.5 px-6 rounded-xl bg-gradient-to-r from-orange-600 to-orange-500 hover:from-orange-500 hover:to-orange-600 text-white font-semibold text-sm shadow-md shadow-orange-500/25 hover:shadow-lg transition-all flex items-center justify-center gap-2 cursor-pointer">
                                <span>{{ __('Confirm Private Request') }}</span>
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" /></svg>
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
