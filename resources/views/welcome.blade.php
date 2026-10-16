<x-layouts.app>
    <x-slot name="title">{{ __("SRMS - Ethiopia's Premier Real Estate & Property Marketplace") }}</x-slot>
    <x-slot name="description">{{ __("Discover, buy, rent, and invest in verified properties across Addis Ababa, Hawassa, Bahir Dar, and all major Ethiopian regional capitals.") }}</x-slot>

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

        {{-- Hero Background Gradient (Light & Dark Adaptive) --}}
        <div class="absolute inset-0 bg-gradient-to-b from-indigo-50/60 via-slate-50 to-slate-50 dark:from-[#090D16] dark:via-[#0F172A] dark:to-[#090D16]" aria-hidden="true"></div>
        <div class="absolute inset-0 opacity-[0.03] dark:opacity-[0.02]" style="background-image: linear-gradient(#000 1px, transparent 1px), linear-gradient(to right, #000 1px, transparent 1px); background-size: 36px 36px;" aria-hidden="true"></div>
        
        {{-- Ambient Multicolored Glow Blobs --}}
        <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[48rem] h-[48rem] bg-gradient-to-tr from-orange-500/15 via-violet-500/10 to-emerald-500/10 dark:from-orange-500/20 dark:via-indigo-500/15 dark:to-emerald-500/10 rounded-full blur-3xl pointer-events-none" aria-hidden="true"></div>
        <div class="absolute bottom-10 right-10 w-80 h-80 bg-violet-500/10 dark:bg-violet-600/10 rounded-full blur-3xl pointer-events-none" aria-hidden="true"></div>
        <div class="absolute top-20 left-10 w-72 h-72 bg-emerald-500/10 dark:bg-emerald-600/10 rounded-full blur-3xl pointer-events-none" aria-hidden="true"></div>

        <div class="relative page-container text-center py-24 sm:py-32 pt-36">
            {{-- Trust Eyebrow --}}
            <div class="inline-flex items-center gap-2 bg-white dark:bg-[#1E293B] border border-slate-200/90 dark:border-slate-800 shadow-sm shadow-orange-500/10 rounded-full py-1.5 px-4 text-xs uppercase tracking-wider font-extrabold text-[#111827] dark:text-white mb-6">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse flex-shrink-0"></span>
                <span>{{ __('Official Verified Ethiopian Real Estate Marketplace') }}</span>
            </div>

            {{-- Main Headline with Multicolored Gradient Accent --}}
            <h1 id="hero-heading" class="font-display font-extrabold text-[#0F172A] dark:text-white text-4xl sm:text-6xl lg:text-7xl leading-[1.08] tracking-tight mb-6 max-w-5xl mx-auto">
                {{ __('Discover Verified') }}
                <span class="bg-gradient-to-r from-orange-500 via-amber-500 to-emerald-500 bg-clip-text text-transparent"> {{ __('Ethiopian Real Estate') }}</span>
                <br class="hidden sm:block"> {{ __('& Luxury Living') }}
            </h1>

            <p class="text-[#475569] dark:text-[#CBD5E1] text-base sm:text-lg max-w-2xl mx-auto mb-10 leading-relaxed font-normal">
                {{ __('Seamless title verification, PostGIS geocoding, and transparent escrow. From luxury penthouses in Bole Atlas to lakeview estates in Hawassa.') }}
            </p>

            {{-- MODERN MULTICOLORED SEARCH CONSOLE --}}
            <div class="max-w-4xl mx-auto bg-white/95 dark:bg-[#0F172A]/95 backdrop-blur-xl border border-slate-200 dark:border-slate-800 rounded-3xl p-3 sm:p-5 shadow-2xl shadow-slate-900/10">
                {{-- Mode Switcher Tabs with Purposeful Colors --}}
                <div class="flex items-center justify-center flex-wrap gap-1.5 sm:gap-2 mb-4 p-1.5 bg-slate-100/80 dark:bg-slate-900/80 rounded-2xl max-w-2xl mx-auto border border-slate-200/60 dark:border-slate-800/60">
                    {{-- Buy Tab (Coral / Orange) --}}
                    <button type="button" @click="activeTab = 'buy'" 
                            :class="{ 'bg-gradient-to-r from-orange-500 to-amber-500 text-white font-bold shadow-md shadow-orange-500/30 scale-102': activeTab === 'buy', 'text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white font-semibold': activeTab !== 'buy' }"
                            class="px-4 py-2 rounded-xl text-xs sm:text-sm transition-all duration-200 active:scale-95 flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
                        <span>{{ __('Buy') }}</span>
                    </button>

                    {{-- Rent Tab (Vibrant Emerald) --}}
                    <button type="button" @click="activeTab = 'rent'" 
                            :class="{ 'bg-gradient-to-r from-emerald-600 to-teal-500 text-white font-bold shadow-md shadow-emerald-600/30 scale-102': activeTab === 'rent', 'text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white font-semibold': activeTab !== 'rent' }"
                            class="px-4 py-2 rounded-xl text-xs sm:text-sm transition-all duration-200 active:scale-95 flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" /></svg>
                        <span>{{ __('Rent') }}</span>
                    </button>

                    {{-- Short Stay Tab (Electric Violet) --}}
                    <button type="button" @click="activeTab = 'stay'" 
                            :class="{ 'bg-gradient-to-r from-violet-600 to-indigo-600 text-white font-bold shadow-md shadow-violet-600/30 scale-102': activeTab === 'stay', 'text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white font-semibold': activeTab !== 'stay' }"
                            class="px-4 py-2 rounded-xl text-xs sm:text-sm transition-all duration-200 active:scale-95 flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-3-3-3m0 18c-1.657 0-3-4.03-3-9s1.343-3 3-3m-9 9a9 9 0 019-9" /></svg>
                        <span>{{ __('Short Stay') }}</span>
                    </button>

                    {{-- Land Tab (Golden Amber) --}}
                    <button type="button" @click="activeTab = 'land'" 
                            :class="{ 'bg-gradient-to-r from-amber-500 to-yellow-600 text-white font-bold shadow-md shadow-amber-600/30 scale-102': activeTab === 'land', 'text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white font-semibold': activeTab !== 'land' }"
                            class="px-4 py-2 rounded-xl text-xs sm:text-sm transition-all duration-200 active:scale-95 flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689A1.125 1.125 0 003 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z" /></svg>
                        <span>{{ __('Land & Plots') }}</span>
                    </button>

                    {{-- New Projects Tab (Electric Sky) --}}
                    <button type="button" @click="activeTab = 'projects'" 
                            :class="{ 'bg-gradient-to-r from-sky-500 to-blue-600 text-white font-bold shadow-md shadow-sky-600/30 scale-102': activeTab === 'projects', 'text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white font-semibold': activeTab !== 'projects' }"
                            class="px-4 py-2 rounded-xl text-xs sm:text-sm transition-all duration-200 active:scale-95 flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" /></svg>
                        <span>{{ __('New Projects') }}</span>
                    </button>
                </div>

                {{-- Search Input Fields --}}
                <form action="{{ route('search') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-2 text-left">
                    <input type="hidden" name="purpose" :value="purposeParam">

                    {{-- Location Input --}}
                    <div class="sm:col-span-5 bg-white dark:bg-[#1E293B] rounded-2xl px-4 py-3 border border-slate-200 dark:border-slate-800 focus-within:border-orange-500 transition-colors">
                        <label class="block text-[11px] font-extrabold uppercase tracking-wider text-slate-700 dark:text-slate-300">{{ __('Location / Neighborhood') }}</label>
                        <div class="flex items-center gap-2 mt-1">
                            <svg class="w-4 h-4 text-orange-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.242-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            <input type="text" name="location" x-model="locationQuery" placeholder="{{ __('e.g. Bole Atlas, Kazanchis, Hawassa...') }}" class="bg-transparent text-[#0F172A] dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 text-xs sm:text-sm outline-none w-full font-bold">
                        </div>
                    </div>

                    {{-- Property Type Selector --}}
                    <div class="sm:col-span-4 bg-white dark:bg-[#1E293B] rounded-2xl px-4 py-3 border border-slate-200 dark:border-slate-800 focus-within:border-orange-500 transition-colors">
                        <label class="block text-[11px] font-extrabold uppercase tracking-wider text-slate-700 dark:text-slate-300">{{ __('Property Category') }}</label>
                        <select name="type" class="bg-transparent text-[#0F172A] dark:text-white text-xs sm:text-sm outline-none w-full mt-1 font-bold cursor-pointer">
                            <option value="all" class="text-black">{{ __('All Property Types') }}</option>
                            <option value="Apartment" class="text-black">{{ __('Apartment / Condominium') }}</option>
                            <option value="Villa" class="text-black">{{ __('Luxury Villa / Compound') }}</option>
                            <option value="Townhouse" class="text-black">{{ __('Townhouse / Duplex') }}</option>
                            <option value="Commercial" class="text-black">{{ __('Commercial / Office Space') }}</option>
                            <option value="Land" class="text-black">{{ __('Residential / Industrial Land') }}</option>
                        </select>
                    </div>

                    {{-- Search CTA Button --}}
                    <div class="sm:col-span-3 flex">
                        <button type="submit" class="btn btn-primary w-full text-xs sm:text-sm font-extrabold py-3.5 px-4 rounded-2xl flex items-center justify-center gap-2 bg-gradient-to-r from-orange-500 via-orange-600 to-amber-600 hover:from-orange-600 hover:to-amber-700 text-white shadow-xl shadow-orange-500/25 transition-all duration-200 active:scale-95 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                            <span>{{ __('Search Properties') }}</span>
                        </button>
                    </div>
                </form>

                {{-- Trending Neighborhood Quick Pills --}}
                <div class="flex items-center justify-center flex-wrap gap-1.5 sm:gap-2 mt-4 text-xs text-slate-600 dark:text-slate-300">
                    <span class="text-slate-900 dark:text-white font-bold flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>
                        {{ __('Popular Areas:') }}
                    </span>
                    <button type="button" @click="locationQuery = 'Bole Atlas'" class="px-3 py-1 rounded-full bg-white dark:bg-[#1E293B] border border-slate-200 dark:border-slate-800 hover:border-orange-500 text-slate-800 dark:text-white transition-all font-bold cursor-pointer hover:scale-105">{{ __('Bole Atlas') }}</button>
                    <button type="button" @click="locationQuery = 'Kazanchis'" class="px-3 py-1 rounded-full bg-white dark:bg-[#1E293B] border border-slate-200 dark:border-slate-800 hover:border-emerald-500 text-slate-800 dark:text-white transition-all font-bold cursor-pointer hover:scale-105">{{ __('Kazanchis') }}</button>
                    <button type="button" @click="locationQuery = 'Old Airport'" class="px-3 py-1 rounded-full bg-white dark:bg-[#1E293B] border border-slate-200 dark:border-slate-800 hover:border-violet-500 text-slate-800 dark:text-white transition-all font-bold cursor-pointer hover:scale-105">{{ __('Old Airport') }}</button>
                    <button type="button" @click="locationQuery = 'CMC Michael'" class="px-3 py-1 rounded-full bg-white dark:bg-[#1E293B] border border-slate-200 dark:border-slate-800 hover:border-amber-500 text-slate-800 dark:text-white transition-all font-bold cursor-pointer hover:scale-105">{{ __('CMC Michael') }}</button>
                    <button type="button" @click="locationQuery = 'Bisrate Gabriel'" class="px-3 py-1 rounded-full bg-white dark:bg-[#1E293B] border border-slate-200 dark:border-slate-800 hover:border-sky-500 text-slate-800 dark:text-white transition-all font-bold cursor-pointer hover:scale-105">{{ __('Bisrate Gabriel') }}</button>
                    <button type="button" @click="locationQuery = 'Hawassa Lakeview'" class="px-3 py-1 rounded-full bg-white dark:bg-[#1E293B] border border-slate-200 dark:border-slate-800 hover:border-rose-500 text-slate-800 dark:text-white transition-all font-bold cursor-pointer hover:scale-105">{{ __('Hawassa') }}</button>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════
         2. BY THE NUMBERS (INSTITUTIONAL METRIC CARDS WITH CARD CLASS)
         ═══════════════════════════════════════════════════════════ --}}
    <section class="py-12 bg-white dark:bg-[#0D1322] border-y border-slate-200/80 dark:border-slate-800/80">
        <div class="page-container">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
                {{-- Metric 1: Verified Properties (Emerald) --}}
                <div class="card card-hover card-glow-emerald bg-white dark:bg-[#0D1322] rounded-2xl p-5 border border-slate-200/80 dark:border-slate-800/80 shadow-xs hover:shadow-md transition-all flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-400 text-white flex items-center justify-center flex-shrink-0 shadow-md shadow-emerald-500/20">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div>
                        <div class="text-2xl sm:text-3xl font-display font-black text-slate-900 dark:text-white">3,200+</div>
                        <div class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">{{ __('Verified Listings') }}</div>
                    </div>
                </div>

                {{-- Metric 2: Transaction Volume (Amber / Gold) --}}
                <div class="card card-hover card-glow-amber bg-white dark:bg-[#0D1322] rounded-2xl p-5 border border-slate-200/80 dark:border-slate-800/80 shadow-xs hover:shadow-md transition-all flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-amber-500 to-orange-400 text-white flex items-center justify-center flex-shrink-0 shadow-md shadow-amber-500/20">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div>
                        <div class="text-2xl sm:text-3xl font-display font-black text-slate-900 dark:text-white">ETB 18B+</div>
                        <div class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">{{ __('Market Value') }}</div>
                    </div>
                </div>

                {{-- Metric 3: Title Accuracy (Electric Indigo) --}}
                <div class="card card-hover card-glow-violet bg-white dark:bg-[#0D1322] rounded-2xl p-5 border border-slate-200/80 dark:border-slate-800/80 shadow-xs hover:shadow-md transition-all flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-500 text-white flex items-center justify-center flex-shrink-0 shadow-md shadow-indigo-500/20">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" /></svg>
                    </div>
                    <div>
                        <div class="text-2xl sm:text-3xl font-display font-black text-slate-900 dark:text-white">100%</div>
                        <div class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">{{ __('Deed Authenticated') }}</div>
                    </div>
                </div>

                {{-- Metric 4: Regional Coverage (Electric Sky) --}}
                <div class="card card-hover card-glow-sky bg-white dark:bg-[#0D1322] rounded-2xl p-5 border border-slate-200/80 dark:border-slate-800/80 shadow-xs hover:shadow-md transition-all flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-sky-500 to-cyan-400 text-white flex items-center justify-center flex-shrink-0 shadow-md shadow-sky-500/20">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                    </div>
                    <div>
                        <div class="text-2xl sm:text-3xl font-display font-black text-slate-900 dark:text-white">12 Hubs</div>
                        <div class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">{{ __('Regional Cities') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════
         3. FEATURED PROPERTIES SECTION
         ═══════════════════════════════════════════════════════════ --}}
    <section class="py-20 bg-slate-50 dark:bg-[#090D16]">
        <div class="page-container">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-12">
                <div>
                    <div class="inline-flex items-center gap-1.5 text-xs font-extrabold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 mb-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        {{ __('Handpicked & Verified') }}
                    </div>
                    <h2 class="text-3xl sm:text-4xl font-display font-extrabold text-slate-900 dark:text-white tracking-tight">
                        {{ __('Featured Properties in Ethiopia') }}
                    </h2>
                    <p class="text-sm text-slate-600 dark:text-slate-300 mt-2 max-w-xl">
                        {{ __('Explore prime residences and high-yield commercial investments verified by our Trust & Safety committee.') }}
                    </p>
                </div>
                <a href="{{ route('properties.index') }}" class="btn btn-secondary inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-xs font-bold bg-white dark:bg-[#1E293B] border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white hover:border-orange-500 transition-colors shadow-sm self-start md:self-end">
                    <span>{{ __('Explore All Properties') }}</span>
                    <svg class="w-4 h-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
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
         4. NEW DEVELOPMENTS SECTION (OFF-PLAN & LUXURY TOWERS)
         ═══════════════════════════════════════════════════════════ --}}
    @if($newDevelopments->count() > 0)
        <section class="py-20 bg-white dark:bg-[#0D1322] border-y border-slate-200 dark:border-slate-800">
            <div class="page-container">
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-12">
                    <div>
                        <div class="inline-flex items-center gap-1.5 text-xs font-extrabold uppercase tracking-wider text-purple-600 dark:text-purple-400 mb-2">
                            <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                            {{ __('Developer Direct Projects') }}
                        </div>
                        <h2 class="text-3xl sm:text-4xl font-display font-extrabold text-slate-900 dark:text-white tracking-tight">
                            {{ __('New Developments & Off-Plan') }}
                        </h2>
                        <p class="text-sm text-slate-600 dark:text-slate-300 mt-2 max-w-xl">
                            {{ __('Direct partnerships with top Ethiopian real estate developers with bank-financed payment milestones and pre-construction pricing.') }}
                        </p>
                    </div>
                    <a href="{{ route('properties.index', ['purpose' => 'Development']) }}" class="btn btn-secondary inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-xs font-bold bg-slate-50 dark:bg-[#1E293B] border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white hover:border-purple-500 transition-colors shadow-sm self-start md:self-end">
                        <span>{{ __('Browse New Developments') }}</span>
                        <svg class="w-4 h-4 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
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
    <section class="py-20 bg-slate-50 dark:bg-[#090D16]">
        <div class="page-container">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <div class="inline-flex items-center gap-1.5 text-xs font-extrabold uppercase tracking-wider text-orange-600 dark:text-orange-400 mb-2">
                    {{ __('Regional Hubs') }}
                </div>
                <h2 class="text-3xl sm:text-4xl font-display font-extrabold text-slate-900 dark:text-white tracking-tight">
                    {{ __('Explore Popular Cities in Ethiopia') }}
                </h2>
                <p class="text-sm text-slate-600 dark:text-slate-300 mt-2">
                    {{ __('Find prime residential estates and commercial properties across the country\'s most dynamic regional economies.') }}
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($popularCities as $city)
                    <a href="{{ route('properties.index', ['location' => $city['query']]) }}" 
                       class="card card-hover card-glow-orange relative h-72 rounded-3xl overflow-hidden group shadow-lg hover:shadow-2xl transition-all duration-500 border border-slate-200/80 dark:border-slate-800/80">
                        <img src="{{ $city['image'] }}" alt="{{ $city['name'] }}" 
                             loading="lazy"
                             class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 ease-out">
                        
                        <div class="absolute top-4 left-4 flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-black/50 backdrop-blur-md text-white border border-white/20 shadow-xs">
                            <svg class="w-3.5 h-3.5 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                            <span>{{ __('Ethiopia') }}</span>
                        </div>

                        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/45 to-transparent p-6 flex flex-col justify-end">
                            <div class="flex items-baseline justify-between">
                                <div>
                                    <h3 class="text-2xl font-display font-black text-white tracking-tight">
                                        {{ $city['name'] }}
                                    </h3>
                                    <span class="text-xs font-bold text-orange-400">{{ $city['amharic'] }}</span>
                                </div>
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-white/20 backdrop-blur-md text-white border border-white/25">
                                    {{ $city['count'] }} {{ __('Listings') }}
                                </span>
                            </div>
                            <p class="text-xs text-white/80 mt-2 line-clamp-1 font-medium">
                                {{ $city['description'] }}
                            </p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════
         6. INSTITUTIONAL TRUST & VERIFICATION PILLARS
         ═══════════════════════════════════════════════════════════ --}}
    <section class="py-20 bg-white dark:bg-[#0D1322] border-t border-slate-200/80 dark:border-slate-800/80">
        <div class="page-container">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <span class="text-xs font-extrabold uppercase tracking-wider text-orange-600 dark:text-orange-400">{{ __('Institutional Safeguards') }}</span>
                <h2 class="text-3xl sm:text-4xl font-display font-extrabold text-slate-900 dark:text-white mt-1">
                    {{ __('Built for Transparency & Safety') }}
                </h2>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-2">
                    {{ __('Every commercial transaction on SRMS is backed by rigorous legal and technical safeguards.') }}
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
                {{-- Pillar 1: Title Deed Verification (Coral/Amber) --}}
                <div class="card card-hover card-glow-orange bg-white dark:bg-[#0D1322] rounded-2xl p-6 text-center border border-slate-200/80 dark:border-slate-800/80 shadow-xs hover:shadow-md transition-all">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-orange-500 to-amber-500 text-white flex items-center justify-center mx-auto mb-4 shadow-md shadow-orange-500/20">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">{{ __('Title Deed Verification') }}</h3>
                    <p class="text-xs text-slate-600 dark:text-slate-300 mt-2 leading-relaxed font-medium">
                        {{ __('Cross-referenced with municipal land administration registries to eliminate duplicate sales and unverified titles.') }}
                    </p>
                </div>

                {{-- Pillar 2: PostGIS Coordinates (Sky Blue) --}}
                <div class="card card-hover card-glow-sky bg-white dark:bg-[#0D1322] rounded-2xl p-6 text-center border border-slate-200/80 dark:border-slate-800/80 shadow-xs hover:shadow-md transition-all">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-sky-500 to-cyan-500 text-white flex items-center justify-center mx-auto mb-4 shadow-md shadow-sky-500/20">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.242-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">{{ __('PostGIS Geocoded Accuracy') }}</h3>
                    <p class="text-xs text-slate-600 dark:text-slate-300 mt-2 leading-relaxed font-medium">
                        {{ __('Precise coordinates and boundary polygons ensure accurate neighborhood exploration with privacy radius protection.') }}
                    </p>
                </div>

                {{-- Pillar 3: Escrow & Secure Payments (Emerald) --}}
                <div class="card card-hover card-glow-emerald bg-white dark:bg-[#0D1322] rounded-2xl p-6 text-center border border-slate-200/80 dark:border-slate-800/80 shadow-xs hover:shadow-md transition-all">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-600 text-white flex items-center justify-center mx-auto mb-4 shadow-md shadow-emerald-500/20">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">{{ __('Escrow & Secure Payments') }}</h3>
                    <p class="text-xs text-slate-600 dark:text-slate-300 mt-2 leading-relaxed font-medium">
                        {{ __('Funds released only upon verified property handover and physical inspection confirmation.') }}
                    </p>
                </div>

                {{-- Pillar 4: Developer Direct Escrow (Violet) --}}
                <div class="card card-hover card-glow-violet bg-white dark:bg-[#0D1322] rounded-2xl p-6 text-center border border-slate-200/80 dark:border-slate-800/80 shadow-xs hover:shadow-md transition-all">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-violet-500 to-purple-600 text-white flex items-center justify-center mx-auto mb-4 shadow-md shadow-violet-500/20">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">{{ __('Developer Direct Escrow') }}</h3>
                    <p class="text-xs text-slate-600 dark:text-slate-300 mt-2 leading-relaxed font-medium">
                        {{ __('Official partnerships with Grade 1 contractors for verified construction completion milestones.') }}
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════
         7. CALL TO ACTION BANNER (LIST YOUR PROPERTY / AGENT ONBOARDING)
         ═══════════════════════════════════════════════════════════ --}}
    <section class="py-16 bg-slate-50 dark:bg-[#090D16]">
        <div class="page-container">
            <div class="relative rounded-3xl overflow-hidden bg-gradient-to-r from-orange-600 via-amber-600 to-orange-700 p-8 sm:p-14 text-white shadow-2xl">
                <div class="absolute -right-20 -bottom-20 w-80 h-80 bg-white/10 rounded-full blur-2xl pointer-events-none" aria-hidden="true"></div>
                <div class="relative max-w-2xl">
                    <span class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-white/20 backdrop-blur-md text-xs font-extrabold uppercase tracking-wider mb-4 border border-white/20">
                        {{ __('For Owners & Real Estate Developers') }}
                    </span>
                    <h2 class="text-3xl sm:text-5xl font-display font-black tracking-tight leading-tight mb-4">
                        {{ __('Reach Thousands of Verified Buyers in Ethiopia & the Diaspora') }}
                    </h2>
                    <p class="text-sm sm:text-base text-white/90 mb-8 leading-relaxed">
                        {{ __('List your property on SRMS to access authenticated buyers, automated escrow milestone management, and PostGIS map placement.') }}
                    </p>
                    <div class="flex items-center flex-wrap gap-3">
                        <a href="/register" class="btn btn-primary px-6 py-3.5 rounded-full text-xs sm:text-sm font-extrabold bg-white text-orange-600 hover:bg-orange-50 shadow-lg shadow-black/10 transition-all duration-200 active:scale-95">
                            {{ __('List Your Property Free') }}
                        </a>
                        <a href="/services" class="btn btn-secondary px-6 py-3.5 rounded-full text-xs sm:text-sm font-extrabold bg-black/20 hover:bg-black/30 text-white backdrop-blur-md border border-white/30 transition-all duration-200 active:scale-95">
                            {{ __('Explore Agent & Legal Services') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
