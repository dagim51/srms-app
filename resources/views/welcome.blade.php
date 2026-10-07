<x-layouts.app>
    <x-slot name="title">SRMS - Ethiopia's Premier Property Marketplace</x-slot>
    <x-slot name="description">Discover, buy, rent, and manage properties across Ethiopia. The modern real estate marketplace built for professionals.</x-slot>

    {{-- ═══════════════════════════════════
         HERO SECTION
         ═══════════════════════════════════ --}}
    <section class="relative min-h-dvh flex items-center justify-center overflow-hidden" aria-labelledby="hero-heading">

        {{-- Background Gradient --}}
        <div class="absolute inset-0 bg-gradient-to-br from-gray-950 via-gray-900 to-gray-950" aria-hidden="true"></div>

        {{-- Decorative Grid --}}
        <div class="absolute inset-0 opacity-[0.03]" style="background-image: linear-gradient(#fff 1px, transparent 1px), linear-gradient(to right, #fff 1px, transparent 1px); background-size: 40px 40px;" aria-hidden="true"></div>

        {{-- Orange Glow --}}
        <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[40rem] h-[40rem] bg-orange-500/10 rounded-full blur-3xl pointer-events-none" aria-hidden="true"></div>
        <div class="absolute bottom-0 right-0 w-72 h-72 bg-orange-600/8 rounded-full blur-3xl pointer-events-none" aria-hidden="true"></div>

        {{-- Content --}}
        <div class="relative page-container text-center py-32 pt-40">

            {{-- Eyebrow --}}
            <div class="inline-flex items-center gap-2 badge badge-brand mb-8 py-1.5 px-4 text-xs uppercase tracking-widest font-bold">
                <span class="status-dot status-active w-1.5 h-1.5"></span>
                Ethiopia's Premier Property Platform
            </div>

            {{-- Headline --}}
            <h1 id="hero-heading" class="font-display font-extrabold text-white text-4xl sm:text-5xl lg:text-7xl leading-[1.05] tracking-tight mb-6 max-w-5xl mx-auto">
                Find Your Perfect
                <span class="text-gradient-brand"> Property</span>
                <br class="hidden sm:block"> in Ethiopia
            </h1>

            <p class="text-gray-400 text-lg sm:text-xl max-w-2xl mx-auto mb-10 leading-relaxed">
                Buy, rent, manage, and discover properties across Ethiopia. From Addis Ababa to the regions — your complete real estate platform.
            </p>

            {{-- CTAs --}}
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3 mb-16">
                <a href="/properties" class="btn btn-primary btn-xl w-full sm:w-auto" id="hero-browse-btn">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                    Browse Properties
                </a>
                <a href="/register" class="btn btn-secondary btn-xl w-full sm:w-auto text-white border-white/20 hover:bg-white/10 hover:border-white/30" id="hero-list-btn">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    List a Property
                </a>
            </div>

            {{-- Search Bar --}}
            <div class="max-w-2xl mx-auto">
                <form action="/properties" method="GET" class="glass rounded-xl p-2 flex gap-2 border-white/10" id="hero-search-form">
                    <div class="flex-1 flex items-center gap-3 px-3">
                        <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                        <input type="text" name="location" placeholder="City, district, or address..."
                               class="flex-1 bg-transparent text-white placeholder:text-gray-500 outline-none text-sm py-2"
                               aria-label="Search location"
                               id="hero-location-input">
                    </div>
                    <button type="submit" class="btn btn-primary px-6" id="hero-search-submit">Search</button>
                </form>
            </div>

            {{-- Stats --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-6 mt-16 max-w-3xl mx-auto">
                <div class="text-center">
                    <p class="text-2xl sm:text-3xl font-display font-extrabold text-white">2,400+</p>
                    <p class="text-sm text-gray-500 mt-1">Active Listings</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl sm:text-3xl font-display font-extrabold text-white">380+</p>
                    <p class="text-sm text-gray-500 mt-1">Verified Agents</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl sm:text-3xl font-display font-extrabold text-white">18</p>
                    <p class="text-sm text-gray-500 mt-1">Cities Covered</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl sm:text-3xl font-display font-extrabold text-white">99%</p>
                    <p class="text-sm text-gray-500 mt-1">Client Satisfaction</p>
                </div>
            </div>
        </div>

        {{-- Scroll Indicator --}}
        <div class="absolute bottom-8 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2 text-gray-600 animate-bounce" aria-hidden="true">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
        </div>
    </section>

    {{-- ═══════════════════════════════════
         LISTING TYPES
         ═══════════════════════════════════ --}}
    <section class="py-20 bg-white dark:bg-[#111827]" aria-labelledby="listing-types-heading">
        <div class="page-container">
            <div class="text-center mb-14">
                <h2 id="listing-types-heading" class="section-heading mb-3">What are you looking for?</h2>
                <p class="section-subheading max-w-xl mx-auto">From short stays to long-term rentals, developer projects to professional services.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">

                {{-- Buy --}}
                <a href="/properties?type=sale" class="card card-hover group p-6 flex flex-col items-start gap-4 cursor-pointer" id="type-buy">
                    <div class="w-12 h-12 rounded-xl bg-orange-50 dark:bg-orange-500/10 flex items-center justify-center group-hover:bg-orange-500 group-hover:shadow-lg group-hover:shadow-orange-500/25 transition-all duration-200">
                        <svg class="w-6 h-6 text-orange-500 group-hover:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" /></svg>
                    </div>
                    <div>
                        <h3 class="font-display font-bold text-gray-900 dark:text-white text-lg mb-1">Buy Property</h3>
                        <p class="text-gray-500 dark:text-gray-400 text-sm leading-relaxed">Find your dream home or commercial property across Ethiopia.</p>
                    </div>
                    <span class="badge badge-brand mt-auto text-xs">1,200+ listings</span>
                </a>

                {{-- Rent --}}
                <a href="/properties?type=rent" class="card card-hover group p-6 flex flex-col items-start gap-4 cursor-pointer" id="type-rent">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center group-hover:bg-blue-500 group-hover:shadow-lg group-hover:shadow-blue-500/25 transition-all duration-200">
                        <svg class="w-6 h-6 text-blue-500 group-hover:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.294 6.336a6.721 6.721 0 01-3.17.789 6.721 6.721 0 01-3.168-.789 3.376 3.376 0 016.338 0z" /></svg>
                    </div>
                    <div>
                        <h3 class="font-display font-bold text-gray-900 dark:text-white text-lg mb-1">Rent Property</h3>
                        <p class="text-gray-500 dark:text-gray-400 text-sm leading-relaxed">Flexible rental options for residential and commercial spaces.</p>
                    </div>
                    <span class="badge badge-info mt-auto text-xs">800+ listings</span>
                </a>

                {{-- Short Stay --}}
                <a href="/short-stay" class="card card-hover group p-6 flex flex-col items-start gap-4 cursor-pointer" id="type-short-stay">
                    <div class="w-12 h-12 rounded-xl bg-purple-50 dark:bg-purple-500/10 flex items-center justify-center group-hover:bg-purple-500 group-hover:shadow-lg group-hover:shadow-purple-500/25 transition-all duration-200">
                        <svg class="w-6 h-6 text-purple-500 group-hover:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008zM9.75 15h.008v.008H9.75V15zm0 2.25h.008v.008H9.75v-.008zM7.5 15h.008v.008H7.5V15zm0 2.25h.008v.008H7.5v-.008zm6.75-4.5h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V15zm0 2.25h.008v.008h-.008v-.008zm2.25-4.5h.008v.008H16.5v-.008zm0 2.25h.008v.008H16.5V15z" /></svg>
                    </div>
                    <div>
                        <h3 class="font-display font-bold text-gray-900 dark:text-white text-lg mb-1">Short Stay</h3>
                        <p class="text-gray-500 dark:text-gray-400 text-sm leading-relaxed">Hotels, furnished apartments, and holiday homes by the night.</p>
                    </div>
                    <span class="badge badge-neutral mt-auto text-xs" style="color: #7C3AED; background: #F5F3FF; border-color: #DDD6FE;">250+ stays</span>
                </a>

                {{-- Developer --}}
                <a href="/developers" class="card card-hover group p-6 flex flex-col items-start gap-4 cursor-pointer" id="type-developer">
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center group-hover:bg-emerald-500 group-hover:shadow-lg group-hover:shadow-emerald-500/25 transition-all duration-200">
                        <svg class="w-6 h-6 text-emerald-500 group-hover:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" /></svg>
                    </div>
                    <div>
                        <h3 class="font-display font-bold text-gray-900 dark:text-white text-lg mb-1">Developer Projects</h3>
                        <p class="text-gray-500 dark:text-gray-400 text-sm leading-relaxed">New developments and off-plan properties from top developers.</p>
                    </div>
                    <span class="badge badge-success mt-auto text-xs">45+ projects</span>
                </a>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════
         FEATURED PROPERTIES (placeholder - connects to Phase 13 API)
         ═══════════════════════════════════ --}}
    <section class="py-20 bg-gray-50 dark:bg-[#0B0F19]" aria-labelledby="featured-heading">
        <div class="page-container">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-12">
                <div>
                    <h2 id="featured-heading" class="section-heading mb-2">Featured Properties</h2>
                    <p class="section-subheading">Hand-picked premium listings across Ethiopia.</p>
                </div>
                <a href="/properties" class="btn btn-secondary btn-sm flex-shrink-0" id="view-all-properties">
                    View all properties
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                </a>
            </div>

                        {{-- Property Grid -- will be powered by API in Phase 13 --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6" id="featured-properties-grid">
                @php
                    $sampleProperties = [
                        (object)[
                            'id' => 1,
                            'title' => 'Luxury Villa in Bole',
                            'type' => 'sale',
                            'price' => 45000000,
                            'location' => 'Bole, Addis Ababa',
                            'bedrooms' => 5,
                            'bathrooms' => 4,
                            'area' => 450,
                            'image_url' => 'https://images.unsplash.com/photo-1613490908592-fd5e48f7ce2f?auto=format&fit=crop&q=80&w=800'
                        ],
                        (object)[
                            'id' => 2,
                            'title' => 'Modern Apartment CMC',
                            'type' => 'rent',
                            'price' => 85000,
                            'location' => 'CMC, Addis Ababa',
                            'bedrooms' => 3,
                            'bathrooms' => 2,
                            'area' => 120,
                            'image_url' => 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&q=80&w=800'
                        ],
                        (object)[
                            'id' => 3,
                            'title' => 'Commercial Space Piassa',
                            'type' => 'rent',
                            'price' => 150000,
                            'location' => 'Piassa, Addis Ababa',
                            'bedrooms' => 0,
                            'bathrooms' => 2,
                            'area' => 300,
                            'image_url' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&q=80&w=800'
                        ]
                    ];
                @endphp

                @foreach($sampleProperties as $property)
                    <x-property-card :property="$property" :isFeatured="$loop->first" />
                @endforeach
            </div>

            {{-- Empty state (shown when no listings) --}}
            <div id="featured-empty" class="hidden">
                <div class="empty-state">
                    <svg class="empty-state-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" /></svg>
                    <h3 class="font-display font-bold text-gray-900 dark:text-white text-xl mb-2">No featured listings yet</h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm mb-6">Check back soon — new properties are being added.</p>
                    <a href="/properties" class="btn btn-primary">Explore all properties</a>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════
         WHY SRMS
         ═══════════════════════════════════ --}}
    <section class="py-20 bg-white dark:bg-[#111827]" aria-labelledby="why-srms-heading">
        <div class="page-container">
            <div class="grid lg:grid-cols-2 gap-16 items-center">
                <div>
                    <div class="badge badge-brand mb-6">Why SRMS</div>
                    <h2 id="why-srms-heading" class="section-heading mb-5">
                        Built for Ethiopia's<br> real estate future
                    </h2>
                    <p class="section-subheading mb-10">
                        We combine modern technology with deep local market knowledge to make property transactions transparent, fast, and trustworthy.
                    </p>

                    <div class="space-y-5">
                        <div class="flex gap-4">
                            <div class="w-10 h-10 rounded-lg bg-orange-50 dark:bg-orange-500/10 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="w-5 h-5 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white mb-1">Verified Listings</h3>
                                <p class="text-gray-500 dark:text-gray-400 text-sm leading-relaxed">Every property goes through our moderation process before going live.</p>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <div class="w-10 h-10 rounded-lg bg-orange-50 dark:bg-orange-500/10 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="w-5 h-5 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white mb-1">Ethiopian Location Hierarchy</h3>
                                <p class="text-gray-500 dark:text-gray-400 text-sm leading-relaxed">Search by region, zone, woreda, and kebele — built for how Ethiopia works.</p>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <div class="w-10 h-10 rounded-lg bg-orange-50 dark:bg-orange-500/10 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="w-5 h-5 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" /></svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-white mb-1">Secure Payments</h3>
                                <p class="text-gray-500 dark:text-gray-400 text-sm leading-relaxed">Integrated with Chapa, Telebirr, and other local payment providers.</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right side visual --}}
                <div class="relative">
                    <div class="bg-gradient-to-br from-orange-500/10 to-orange-600/5 rounded-2xl p-8 border border-orange-500/10 dark:border-orange-500/20">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="card p-5">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Listed today</p>
                                <p class="font-display font-bold text-2xl text-gray-900 dark:text-white">24</p>
                                <p class="text-xs text-green-500 mt-1 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5L12 3m0 0l7.5 7.5M12 3v18" /></svg>
                                    +18% this week
                                </p>
                            </div>
                            <div class="card p-5">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Avg. response</p>
                                <p class="font-display font-bold text-2xl text-gray-900 dark:text-white">2.4h</p>
                                <p class="text-xs text-orange-500 mt-1">Agent response time</p>
                            </div>
                            <div class="card p-5 col-span-2">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Active cities</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach(['Addis Ababa', 'Dire Dawa', 'Hawassa', 'Bahir Dar', 'Mekelle', 'Gondar'] as $city)
                                        <span class="badge badge-neutral text-xs">{{ $city }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════
         CTA SECTION
         ═══════════════════════════════════ --}}
    <section class="py-20 bg-gradient-to-br from-orange-500 to-orange-600" aria-labelledby="cta-heading">
        <div class="page-container text-center">
            <h2 id="cta-heading" class="font-display font-extrabold text-white text-3xl sm:text-4xl lg:text-5xl tracking-tight mb-5">
                Ready to find your next property?
            </h2>
            <p class="text-orange-100 text-lg max-w-xl mx-auto mb-10 leading-relaxed">
                Join thousands of Ethiopians who trust SRMS to find, list, and manage properties.
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="/register" class="btn btn-xl bg-white text-orange-600 hover:bg-orange-50 border-transparent shadow-xl w-full sm:w-auto" id="cta-register">
                    Create Free Account
                </a>
                <a href="/properties" class="btn btn-xl text-white border-white/40 hover:bg-white/10 w-full sm:w-auto" id="cta-browse">
                    Browse Listings
                </a>
            </div>
        </div>
    </section>

</x-layouts.app>

