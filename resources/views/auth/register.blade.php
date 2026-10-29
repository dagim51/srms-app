<x-layouts.auth>
    <x-slot name="title">{{ __('Create Account') }}</x-slot>

    <div class="card bg-white dark:bg-[#1E293B] border border-slate-200 dark:border-slate-700 rounded-3xl p-6 sm:p-9 max-w-2xl mx-auto shadow-xl"
         x-data="{
             step: {{ $errors->any() && ! $errors->has('intent') ? 2 : 1 }},
             category: 'seeking',
             intent: '{{ old('intent', 'buyer') }}',
             loading: false,
             showPass: false,
             passVal: '',
             
             init() {
                 const intentToCat = {
                     'buyer': 'seeking', 'tenant': 'seeking', 'traveler': 'seeking', 'buyer_tenant': 'seeking',
                     'seller': 'listing', 'landlord': 'listing', 'host': 'listing', 'owner_host': 'listing',
                     'agent': 'professional', 'developer': 'professional', 'property_manager': 'professional', 'service_provider': 'professional'
                 };
                 if (intentToCat[this.intent]) {
                     this.category = intentToCat[this.intent];
                 }
             },

             categories: {
                 'seeking': {
                     label: '{{ __('Seeking Property') }}',
                     tagline: '{{ __('Buy, rent, or book stays') }}'
                 },
                 'listing': {
                     label: '{{ __('Listing & Monetizing') }}',
                     tagline: '{{ __('Sell, lease, or host units') }}'
                 },
                 'professional': {
                     label: '{{ __('Real Estate Professional') }}',
                     tagline: '{{ __('Brokers, developers & experts') }}'
                 }
             },

             subtypes: {
                 'buyer': {
                     category: 'seeking',
                     title: '{{ __('Property Buyer') }}',
                     desc: '{{ __('Explore verified homes, apartments, land, or commercial spaces to purchase.') }}',
                     badge: '{{ __('Buyer') }}',
                     role: '{{ __('Buyer') }}'
                 },
                 'tenant': {
                     category: 'seeking',
                     title: '{{ __('Tenant / Renter') }}',
                     desc: '{{ __('Search residential or commercial leases with digital contract records.') }}',
                     badge: '{{ __('Tenant') }}',
                     role: '{{ __('Tenant') }}'
                 },
                 'traveler': {
                     category: 'seeking',
                     title: '{{ __('Short-Stay Guest') }}',
                     desc: '{{ __('Reserve vacation stays, serviced units, and weekend retreats across Ethiopia.') }}',
                     badge: '{{ __('Traveler') }}',
                     role: '{{ __('Guest / Traveler') }}'
                 },
                 'seller': {
                     category: 'listing',
                     title: '{{ __('Property Seller / Owner') }}',
                     desc: '{{ __('List properties for sale to verified domestic buyers and Diaspora investors.') }}',
                     badge: '{{ __('Asset Owner') }}',
                     role: '{{ __('Property Owner') }}'
                 },
                 'landlord': {
                     category: 'listing',
                     title: '{{ __('Landlord') }}',
                     desc: '{{ __('Manage tenant rosters, long-term lease contracts, security deposits, and rent.') }}',
                     badge: '{{ __('Landlord') }}',
                     role: '{{ __('Landlord') }}'
                 },
                 'host': {
                     category: 'listing',
                     title: '{{ __('Vacation Host') }}',
                     desc: '{{ __('Host short-stay travelers with dynamic nightly rates and synced calendar feeds.') }}',
                     badge: '{{ __('Hospitality Host') }}',
                     role: '{{ __('Host') }}'
                 },
                 'agent': {
                     category: 'professional',
                     title: '{{ __('Real Estate Agent / Broker') }}',
                     desc: '{{ __('Manage client leads, coordinate viewings, negotiate purchase offers, and earn fees.') }}',
                     badge: '{{ __('Licensed Broker') }}',
                     role: '{{ __('Agent') }}'
                 },
                 'developer': {
                     category: 'professional',
                     title: '{{ __('Property Developer') }}',
                     desc: '{{ __('Configure master development projects, unit inventories, and construction milestone billing.') }}',
                     badge: '{{ __('Development Firm') }}',
                     role: '{{ __('Developer') }}'
                 },
                 'property_manager': {
                     category: 'professional',
                     title: '{{ __('Property Manager') }}',
                     desc: '{{ __('Oversee facility maintenance, work orders, service vendors, and multi-unit complexes.') }}',
                     badge: '{{ __('Facilities Manager') }}',
                     role: '{{ __('Property Manager') }}'
                 },
                 'service_provider': {
                     category: 'professional',
                     title: '{{ __('Professional Service Provider') }}',
                     desc: '{{ __('Licensed conveyancer, land surveyor, architect, or certified property valuation inspector.') }}',
                     badge: '{{ __('Certified Specialist') }}',
                     role: '{{ __('Professional Service Provider') }}'
                 }
             },

             selectCategory(cat) {
                 this.category = cat;
                 if (!this.subtypes[this.intent] || this.subtypes[this.intent].category !== cat) {
                     const firstSubtype = Object.keys(this.subtypes).find(k => this.subtypes[k].category === cat);
                     if (firstSubtype) this.intent = firstSubtype;
                 }
             }
         }">

        {{-- Multi-Step Stepper Progress Bar --}}
        <div class="mb-7" aria-label="Registration Progress">
            <div class="flex items-center justify-between text-xs font-black tracking-wider uppercase mb-2">
                <span class="flex items-center gap-1.5" :class="step === 1 ? 'text-[#C2410C] dark:text-orange-400' : 'text-emerald-600 dark:text-emerald-400'">
                    <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-black"
                          :class="step === 1 ? 'bg-orange-100 dark:bg-orange-950/60 text-[#C2410C] dark:text-orange-400' : 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400'">
                        <template x-if="step > 1">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                        </template>
                        <template x-if="step === 1"><span>1</span></template>
                    </span>
                    <span>{{ __('1. Choose User Type') }}</span>
                </span>

                <div class="flex-1 mx-3 h-1 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-[#C2410C] to-orange-500 transition-all duration-300"
                         :style="`width: ${step === 1 ? '50%' : '100%'}`"></div>
                </div>

                <span class="flex items-center gap-1.5" :class="step === 2 ? 'text-[#C2410C] dark:text-orange-400' : 'text-slate-600 dark:text-slate-300'">
                    <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-black"
                          :class="step === 2 ? 'bg-orange-100 dark:bg-orange-950/60 text-[#C2410C] dark:text-orange-400' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300'">
                        2
                    </span>
                    <span>{{ __('2. Credentials') }}</span>
                </span>
            </div>
        </div>

        {{-- Form Container --}}
        <form method="POST" action="/register" novalidate @submit="loading = true" class="space-y-5">
            @csrf
            <input type="hidden" name="intent" :value="intent">

            {{-- ======================================================== --}}
            {{-- STEP 1: 2-TIER PROGRESSIVE USER TYPE QUESTIONNAIRE        --}}
            {{-- ======================================================== --}}
            <div x-show="step === 1" x-transition:enter="transition ease-out duration-200 transform" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-5">
                <div class="text-center mb-6">
                    <div class="w-12 h-12 rounded-2xl bg-orange-100 dark:bg-orange-950/40 text-orange-600 dark:text-orange-400 flex items-center justify-center mx-auto mb-3 shadow-xs">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                    </div>
                    <h1 class="font-display font-black text-slate-900 dark:text-white text-2xl mb-1.5">{{ __('Select Your Account Type') }}</h1>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 font-medium">{{ __('Choose what you want to do on SRMS to configure your personalized tools.') }}</p>
                </div>

                @if($errors->has('intent'))
                    <div class="p-3 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/60 text-rose-700 dark:text-rose-400 text-xs font-bold">
                        {{ $errors->first('intent') }}
                    </div>
                @endif

                {{-- LEVEL 1: Broad Category Tabs --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                        {{ __('1. Select Primary Activity') }}
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 p-1.5 rounded-2xl bg-slate-100 dark:bg-slate-900/70 border border-slate-200/80 dark:border-slate-800">
                        
                        {{-- Tab 1: Seeking Property --}}
                        <button type="button" 
                                @click="selectCategory('seeking')"
                                class="p-3 rounded-xl text-left transition-all duration-200 flex flex-col justify-between"
                                :class="category === 'seeking' 
                                    ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-sm ring-1 ring-orange-500/30' 
                                    : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-white/50 dark:hover:bg-slate-800/50'">
                            <div class="flex items-center gap-2 mb-1">
                                <div class="w-6 h-6 rounded-lg flex items-center justify-center text-xs"
                                     :class="category === 'seeking' ? 'bg-[#C2410C] text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300'">
                                    🔍
                                </div>
                                <span class="text-xs font-black">{{ __('Seeking Property') }}</span>
                            </div>
                            <span class="text-[10px] text-slate-500 dark:text-slate-400 leading-tight">{{ __('Buy, rent, or book stays') }}</span>
                        </button>

                        {{-- Tab 2: Listing & Monetizing --}}
                        <button type="button" 
                                @click="selectCategory('listing')"
                                class="p-3 rounded-xl text-left transition-all duration-200 flex flex-col justify-between"
                                :class="category === 'listing' 
                                    ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-sm ring-1 ring-orange-500/30' 
                                    : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-white/50 dark:hover:bg-slate-800/50'">
                            <div class="flex items-center gap-2 mb-1">
                                <div class="w-6 h-6 rounded-lg flex items-center justify-center text-xs"
                                     :class="category === 'listing' ? 'bg-[#C2410C] text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300'">
                                    🏷️
                                </div>
                                <span class="text-xs font-black">{{ __('Listing & Monetizing') }}</span>
                            </div>
                            <span class="text-[10px] text-slate-500 dark:text-slate-400 leading-tight">{{ __('Sell, lease, or host units') }}</span>
                        </button>

                        {{-- Tab 3: Industry Professional --}}
                        <button type="button" 
                                @click="selectCategory('professional')"
                                class="p-3 rounded-xl text-left transition-all duration-200 flex flex-col justify-between"
                                :class="category === 'professional' 
                                    ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-sm ring-1 ring-orange-500/30' 
                                    : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-white/50 dark:hover:bg-slate-800/50'">
                            <div class="flex items-center gap-2 mb-1">
                                <div class="w-6 h-6 rounded-lg flex items-center justify-center text-xs"
                                     :class="category === 'professional' ? 'bg-[#C2410C] text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300'">
                                    💼
                                </div>
                                <span class="text-xs font-black">{{ __('Real Estate Pro') }}</span>
                            </div>
                            <span class="text-[10px] text-slate-500 dark:text-slate-400 leading-tight">{{ __('Brokers, developers & experts') }}</span>
                        </button>
                    </div>
                </div>

                {{-- LEVEL 2: Granular Sub-Type Cards Grid --}}
                <div class="space-y-2 pt-1">
                    <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                        {{ __('2. Select Your Specific User Type') }}
                    </label>

                    {{-- SUBTYPES FOR: Seeking Property --}}
                    <div x-show="category === 'seeking'" x-transition:enter="transition ease-out duration-200 transform" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        
                        {{-- Buyer --}}
                        <div @click="intent = 'buyer'"
                             tabindex="0"
                             role="radio"
                             :aria-checked="intent === 'buyer'"
                             class="p-3.5 rounded-2xl border-2 cursor-pointer transition-all duration-200 flex flex-col justify-between"
                             :class="intent === 'buyer' 
                                 ? 'border-[#C2410C] bg-orange-50/70 dark:bg-orange-950/25 ring-2 ring-orange-500/20 shadow-sm' 
                                 : 'border-slate-200 dark:border-slate-700/80 bg-white dark:bg-slate-900/50 hover:border-slate-300 dark:hover:border-slate-600'">
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-lg">🏡</span>
                                    <span class="w-4 h-4 rounded-full border flex items-center justify-center transition-all"
                                          :class="intent === 'buyer' ? 'border-[#C2410C] bg-[#C2410C] text-white' : 'border-slate-300 dark:border-slate-600 bg-transparent'">
                                        <svg x-show="intent === 'buyer'" class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                    </span>
                                </div>
                                <h3 class="font-extrabold text-xs text-slate-900 dark:text-white mb-1">{{ __('Property Buyer') }}</h3>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">{{ __('Purchase houses, apartments, land, or commercial plots.') }}</p>
                            </div>
                            <div class="mt-2.5 pt-2 border-t border-slate-100 dark:border-slate-800 text-[10px] font-bold text-[#C2410C] dark:text-orange-400">
                                {{ __('Assigns Buyer Role') }}
                            </div>
                        </div>

                        {{-- Tenant --}}
                        <div @click="intent = 'tenant'"
                             tabindex="0"
                             role="radio"
                             :aria-checked="intent === 'tenant'"
                             class="p-3.5 rounded-2xl border-2 cursor-pointer transition-all duration-200 flex flex-col justify-between"
                             :class="intent === 'tenant' 
                                 ? 'border-[#C2410C] bg-orange-50/70 dark:bg-orange-950/25 ring-2 ring-orange-500/20 shadow-sm' 
                                 : 'border-slate-200 dark:border-slate-700/80 bg-white dark:bg-slate-900/50 hover:border-slate-300 dark:hover:border-slate-600'">
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-lg">🏢</span>
                                    <span class="w-4 h-4 rounded-full border flex items-center justify-center transition-all"
                                          :class="intent === 'tenant' ? 'border-[#C2410C] bg-[#C2410C] text-white' : 'border-slate-300 dark:border-slate-600 bg-transparent'">
                                        <svg x-show="intent === 'tenant'" class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                    </span>
                                </div>
                                <h3 class="font-extrabold text-xs text-slate-900 dark:text-white mb-1">{{ __('Tenant / Renter') }}</h3>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">{{ __('Search long-term rentals with digital lease signing.') }}</p>
                            </div>
                            <div class="mt-2.5 pt-2 border-t border-slate-100 dark:border-slate-800 text-[10px] font-bold text-[#C2410C] dark:text-orange-400">
                                {{ __('Assigns Tenant Role') }}
                            </div>
                        </div>

                        {{-- Traveler --}}
                        <div @click="intent = 'traveler'"
                             tabindex="0"
                             role="radio"
                             :aria-checked="intent === 'traveler'"
                             class="p-3.5 rounded-2xl border-2 cursor-pointer transition-all duration-200 flex flex-col justify-between"
                             :class="intent === 'traveler' 
                                 ? 'border-[#C2410C] bg-orange-50/70 dark:bg-orange-950/25 ring-2 ring-orange-500/20 shadow-sm' 
                                 : 'border-slate-200 dark:border-slate-700/80 bg-white dark:bg-slate-900/50 hover:border-slate-300 dark:hover:border-slate-600'">
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-lg">✈️</span>
                                    <span class="w-4 h-4 rounded-full border flex items-center justify-center transition-all"
                                          :class="intent === 'traveler' ? 'border-[#C2410C] bg-[#C2410C] text-white' : 'border-slate-300 dark:border-slate-600 bg-transparent'">
                                        <svg x-show="intent === 'traveler'" class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                    </span>
                                </div>
                                <h3 class="font-extrabold text-xs text-slate-900 dark:text-white mb-1">{{ __('Short-Stay Guest') }}</h3>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">{{ __('Book vacation stays, serviced units, and weekend retreats.') }}</p>
                            </div>
                            <div class="mt-2.5 pt-2 border-t border-slate-100 dark:border-slate-800 text-[10px] font-bold text-[#C2410C] dark:text-orange-400">
                                {{ __('Assigns Guest / Traveler') }}
                            </div>
                        </div>
                    </div>

                    {{-- SUBTYPES FOR: Listing & Monetizing --}}
                    <div x-show="category === 'listing'" x-transition:enter="transition ease-out duration-200 transform" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        
                        {{-- Seller --}}
                        <div @click="intent = 'seller'"
                             tabindex="0"
                             role="radio"
                             :aria-checked="intent === 'seller'"
                             class="p-3.5 rounded-2xl border-2 cursor-pointer transition-all duration-200 flex flex-col justify-between"
                             :class="intent === 'seller' 
                                 ? 'border-[#C2410C] bg-orange-50/70 dark:bg-orange-950/25 ring-2 ring-orange-500/20 shadow-sm' 
                                 : 'border-slate-200 dark:border-slate-700/80 bg-white dark:bg-slate-900/50 hover:border-slate-300 dark:hover:border-slate-600'">
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-lg">🔑</span>
                                    <span class="w-4 h-4 rounded-full border flex items-center justify-center transition-all"
                                          :class="intent === 'seller' ? 'border-[#C2410C] bg-[#C2410C] text-white' : 'border-slate-300 dark:border-slate-600 bg-transparent'">
                                        <svg x-show="intent === 'seller'" class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                    </span>
                                </div>
                                <h3 class="font-extrabold text-xs text-slate-900 dark:text-white mb-1">{{ __('Property Seller / Owner') }}</h3>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">{{ __('Sell properties to verified domestic & diaspora buyers.') }}</p>
                            </div>
                            <div class="mt-2.5 pt-2 border-t border-slate-100 dark:border-slate-800 text-[10px] font-bold text-[#C2410C] dark:text-orange-400">
                                {{ __('Assigns Property Owner') }}
                            </div>
                        </div>

                        {{-- Landlord --}}
                        <div @click="intent = 'landlord'"
                             tabindex="0"
                             role="radio"
                             :aria-checked="intent === 'landlord'"
                             class="p-3.5 rounded-2xl border-2 cursor-pointer transition-all duration-200 flex flex-col justify-between"
                             :class="intent === 'landlord' 
                                 ? 'border-[#C2410C] bg-orange-50/70 dark:bg-orange-950/25 ring-2 ring-orange-500/20 shadow-sm' 
                                 : 'border-slate-200 dark:border-slate-700/80 bg-white dark:bg-slate-900/50 hover:border-slate-300 dark:hover:border-slate-600'">
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-lg">📑</span>
                                    <span class="w-4 h-4 rounded-full border flex items-center justify-center transition-all"
                                          :class="intent === 'landlord' ? 'border-[#C2410C] bg-[#C2410C] text-white' : 'border-slate-300 dark:border-slate-600 bg-transparent'">
                                        <svg x-show="intent === 'landlord'" class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                    </span>
                                </div>
                                <h3 class="font-extrabold text-xs text-slate-900 dark:text-white mb-1">{{ __('Landlord') }}</h3>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">{{ __('Manage tenant leases, security deposits, and rental income.') }}</p>
                            </div>
                            <div class="mt-2.5 pt-2 border-t border-slate-100 dark:border-slate-800 text-[10px] font-bold text-[#C2410C] dark:text-orange-400">
                                {{ __('Assigns Landlord Role') }}
                            </div>
                        </div>

                        {{-- Host --}}
                        <div @click="intent = 'host'"
                             tabindex="0"
                             role="radio"
                             :aria-checked="intent === 'host'"
                             class="p-3.5 rounded-2xl border-2 cursor-pointer transition-all duration-200 flex flex-col justify-between"
                             :class="intent === 'host' 
                                 ? 'border-[#C2410C] bg-orange-50/70 dark:bg-orange-950/25 ring-2 ring-orange-500/20 shadow-sm' 
                                 : 'border-slate-200 dark:border-slate-700/80 bg-white dark:bg-slate-900/50 hover:border-slate-300 dark:hover:border-slate-600'">
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-lg">🛎️</span>
                                    <span class="w-4 h-4 rounded-full border flex items-center justify-center transition-all"
                                          :class="intent === 'host' ? 'border-[#C2410C] bg-[#C2410C] text-white' : 'border-slate-300 dark:border-slate-600 bg-transparent'">
                                        <svg x-show="intent === 'host'" class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                    </span>
                                </div>
                                <h3 class="font-extrabold text-xs text-slate-900 dark:text-white mb-1">{{ __('Vacation Host') }}</h3>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">{{ __('Host short-stay travelers with dynamic pricing & synced feeds.') }}</p>
                            </div>
                            <div class="mt-2.5 pt-2 border-t border-slate-100 dark:border-slate-800 text-[10px] font-bold text-[#C2410C] dark:text-orange-400">
                                {{ __('Assigns Host Role') }}
                            </div>
                        </div>
                    </div>

                    {{-- SUBTYPES FOR: Industry Professional --}}
                    <div x-show="category === 'professional'" x-transition:enter="transition ease-out duration-200 transform" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        
                        {{-- Agent --}}
                        <div @click="intent = 'agent'"
                             tabindex="0"
                             role="radio"
                             :aria-checked="intent === 'agent'"
                             class="p-3.5 rounded-2xl border-2 cursor-pointer transition-all duration-200 flex flex-col justify-between"
                             :class="intent === 'agent' 
                                 ? 'border-[#C2410C] bg-orange-50/70 dark:bg-orange-950/25 ring-2 ring-orange-500/20 shadow-sm' 
                                 : 'border-slate-200 dark:border-slate-700/80 bg-white dark:bg-slate-900/50 hover:border-slate-300 dark:hover:border-slate-600'">
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-lg">💼</span>
                                    <span class="w-4 h-4 rounded-full border flex items-center justify-center transition-all"
                                          :class="intent === 'agent' ? 'border-[#C2410C] bg-[#C2410C] text-white' : 'border-slate-300 dark:border-slate-600 bg-transparent'">
                                        <svg x-show="intent === 'agent'" class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                    </span>
                                </div>
                                <h3 class="font-extrabold text-xs text-slate-900 dark:text-white mb-1">{{ __('Real Estate Agent / Broker') }}</h3>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">{{ __('Manage client leads, coordinate viewings, negotiate offers, and commissions.') }}</p>
                            </div>
                            <div class="mt-2.5 pt-2 border-t border-slate-100 dark:border-slate-800 text-[10px] font-bold text-[#C2410C] dark:text-orange-400">
                                {{ __('Assigns Agent Role') }}
                            </div>
                        </div>

                        {{-- Developer --}}
                        <div @click="intent = 'developer'"
                             tabindex="0"
                             role="radio"
                             :aria-checked="intent === 'developer'"
                             class="p-3.5 rounded-2xl border-2 cursor-pointer transition-all duration-200 flex flex-col justify-between"
                             :class="intent === 'developer' 
                                 ? 'border-[#C2410C] bg-orange-50/70 dark:bg-orange-950/25 ring-2 ring-orange-500/20 shadow-sm' 
                                 : 'border-slate-200 dark:border-slate-700/80 bg-white dark:bg-slate-900/50 hover:border-slate-300 dark:hover:border-slate-600'">
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-lg">🏗️</span>
                                    <span class="w-4 h-4 rounded-full border flex items-center justify-center transition-all"
                                          :class="intent === 'developer' ? 'border-[#C2410C] bg-[#C2410C] text-white' : 'border-slate-300 dark:border-slate-600 bg-transparent'">
                                        <svg x-show="intent === 'developer'" class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                    </span>
                                </div>
                                <h3 class="font-extrabold text-xs text-slate-900 dark:text-white mb-1">{{ __('Property Developer') }}</h3>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">{{ __('Master development projects, off-plan unit sales, and milestone billing.') }}</p>
                            </div>
                            <div class="mt-2.5 pt-2 border-t border-slate-100 dark:border-slate-800 text-[10px] font-bold text-[#C2410C] dark:text-orange-400">
                                {{ __('Assigns Developer Role') }}
                            </div>
                        </div>

                        {{-- Property Manager --}}
                        <div @click="intent = 'property_manager'"
                             tabindex="0"
                             role="radio"
                             :aria-checked="intent === 'property_manager'"
                             class="p-3.5 rounded-2xl border-2 cursor-pointer transition-all duration-200 flex flex-col justify-between"
                             :class="intent === 'property_manager' 
                                 ? 'border-[#C2410C] bg-orange-50/70 dark:bg-orange-950/25 ring-2 ring-orange-500/20 shadow-sm' 
                                 : 'border-slate-200 dark:border-slate-700/80 bg-white dark:bg-slate-900/50 hover:border-slate-300 dark:hover:border-slate-600'">
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-lg">🏛️</span>
                                    <span class="w-4 h-4 rounded-full border flex items-center justify-center transition-all"
                                          :class="intent === 'property_manager' ? 'border-[#C2410C] bg-[#C2410C] text-white' : 'border-slate-300 dark:border-slate-600 bg-transparent'">
                                        <svg x-show="intent === 'property_manager'" class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                    </span>
                                </div>
                                <h3 class="font-extrabold text-xs text-slate-900 dark:text-white mb-1">{{ __('Property Manager') }}</h3>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">{{ __('Facility management, tenant work orders, and maintenance vendors.') }}</p>
                            </div>
                            <div class="mt-2.5 pt-2 border-t border-slate-100 dark:border-slate-800 text-[10px] font-bold text-[#C2410C] dark:text-orange-400">
                                {{ __('Assigns Property Manager') }}
                            </div>
                        </div>

                        {{-- Professional Service Provider --}}
                        <div @click="intent = 'service_provider'"
                             tabindex="0"
                             role="radio"
                             :aria-checked="intent === 'service_provider'"
                             class="p-3.5 rounded-2xl border-2 cursor-pointer transition-all duration-200 flex flex-col justify-between"
                             :class="intent === 'service_provider' 
                                 ? 'border-[#C2410C] bg-orange-50/70 dark:bg-orange-950/25 ring-2 ring-orange-500/20 shadow-sm' 
                                 : 'border-slate-200 dark:border-slate-700/80 bg-white dark:bg-slate-900/50 hover:border-slate-300 dark:hover:border-slate-600'">
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-lg">⚖️</span>
                                    <span class="w-4 h-4 rounded-full border flex items-center justify-center transition-all"
                                          :class="intent === 'service_provider' ? 'border-[#C2410C] bg-[#C2410C] text-white' : 'border-slate-300 dark:border-slate-600 bg-transparent'">
                                        <svg x-show="intent === 'service_provider'" class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                    </span>
                                </div>
                                <h3 class="font-extrabold text-xs text-slate-900 dark:text-white mb-1">{{ __('Professional Services') }}</h3>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">{{ __('Legal conveyancer, land surveyor, architect, or valuation inspector.') }}</p>
                            </div>
                            <div class="mt-2.5 pt-2 border-t border-slate-100 dark:border-slate-800 text-[10px] font-bold text-[#C2410C] dark:text-orange-400">
                                {{ __('Assigns Service Provider') }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Continue to Step 2 Button --}}
                <button type="button" 
                        @click="step = 2"
                        :disabled="!intent"
                        class="w-full py-3.5 rounded-2xl text-xs sm:text-sm font-extrabold bg-[#C2410C] hover:bg-[#9A3412] text-white shadow-lg shadow-orange-500/25 transition-all duration-200 active:scale-95 cursor-pointer flex items-center justify-center gap-2 mt-4 disabled:opacity-50 disabled:cursor-not-allowed"
                        id="btn-continue-step2">
                    <span>{{ __('Continue to Account Details') }}</span>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                </button>
            </div>

            {{-- ======================================================== --}}
            {{-- STEP 2: CREDENTIALS & ACCOUNT CREATION                    --}}
            {{-- ======================================================== --}}
            <div x-show="step === 2" x-cloak x-transition:enter="transition ease-out duration-200 transform" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-4">
                
                {{-- Chosen Persona Summary Chip --}}
                <div class="flex items-center justify-between p-3.5 rounded-2xl bg-orange-50/80 dark:bg-orange-950/30 border border-orange-200/80 dark:border-orange-900/50">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-[#C2410C] text-white flex items-center justify-center shadow-xs">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                        </div>
                        <div>
                            <div class="text-[10px] uppercase font-black tracking-wider text-orange-700 dark:text-orange-400">
                                <span x-text="subtypes[intent]?.badge || 'Selected Persona'"></span>
                                <span class="text-slate-400"> • </span>
                                <span class="text-slate-600 dark:text-slate-400" x-text="`Role: ${subtypes[intent]?.role || ''}`"></span>
                            </div>
                            <div class="text-xs sm:text-sm font-extrabold text-slate-900 dark:text-white" x-text="subtypes[intent]?.title || ''"></div>
                        </div>
                    </div>
                    <button type="button" 
                            @click="step = 1" 
                            class="px-3 py-1.5 rounded-xl text-xs font-bold text-orange-700 dark:text-orange-400 bg-white dark:bg-slate-900 border border-orange-200 dark:border-orange-800 hover:bg-orange-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                            id="btn-change-intent">
                        {{ __('Change') }}
                    </button>
                </div>

                {{-- Full Name --}}
                <div>
                    <label for="name" class="block text-xs font-extrabold uppercase tracking-wider text-slate-800 dark:text-slate-200 mb-1">
                        {{ __('Full Name') }}
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-orange-500">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                        </div>
                        <input type="text" 
                               name="name" 
                               id="name" 
                               value="{{ old('name') }}" 
                               required 
                               autocomplete="name" 
                               placeholder="{{ __('Abebe Bikila') }}" 
                               class="w-full pl-10 pr-3.5 py-2.5 text-xs sm:text-sm font-bold rounded-xl bg-white dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white placeholder:text-slate-400 focus:border-orange-500 outline-none transition-colors">
                    </div>
                    @if($errors->has('name'))
                        <p class="text-rose-600 text-xs mt-1 font-bold">{{ $errors->first('name') }}</p>
                    @endif
                </div>

                {{-- Email Address --}}
                <div>
                    <label for="email" class="block text-xs font-extrabold uppercase tracking-wider text-slate-800 dark:text-slate-200 mb-1">
                        {{ __('Email Address') }}
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-orange-500">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" /></svg>
                        </div>
                        <input type="email" 
                               name="email" 
                               id="email" 
                               value="{{ old('email') }}" 
                               required 
                               autocomplete="email" 
                               placeholder="you@example.com" 
                               class="w-full pl-10 pr-3.5 py-2.5 text-xs sm:text-sm font-bold rounded-xl bg-white dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white placeholder:text-slate-400 focus:border-orange-500 outline-none transition-colors">
                    </div>
                    @if($errors->has('email'))
                        <p class="text-rose-600 text-xs mt-1 font-bold">{{ $errors->first('email') }}</p>
                    @endif
                </div>

                {{-- Phone Number --}}
                <div>
                    <label for="phone" class="block text-xs font-extrabold uppercase tracking-wider text-slate-800 dark:text-slate-200 mb-1">
                        {{ __('Phone Number') }}
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-orange-500">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" /></svg>
                        </div>
                        <input type="tel" 
                               name="phone" 
                               id="phone" 
                               value="{{ old('phone') }}" 
                               autocomplete="tel" 
                               placeholder="+251 91 123 4567" 
                               class="w-full pl-10 pr-3.5 py-2.5 text-xs sm:text-sm font-bold rounded-xl bg-white dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white placeholder:text-slate-400 focus:border-orange-500 outline-none transition-colors">
                    </div>
                    @if($errors->has('phone'))
                        <p class="text-rose-600 text-xs mt-1 font-bold">{{ $errors->first('phone') }}</p>
                    @endif
                </div>

                {{-- Password --}}
                <div>
                    <label for="password" class="block text-xs font-extrabold uppercase tracking-wider text-slate-800 dark:text-slate-200 mb-1">
                        {{ __('Password') }}
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-orange-500">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>
                        </div>
                        <input :type="showPass ? 'text' : 'password'" 
                               name="password" 
                               id="password" 
                               x-model="passVal"
                               required 
                               autocomplete="new-password" 
                               placeholder="{{ __('At least 8 characters') }}" 
                               class="w-full pl-10 pr-10 py-2.5 text-xs sm:text-sm font-bold rounded-xl bg-white dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white placeholder:text-slate-400 focus:border-orange-500 outline-none transition-colors">
                        
                        <button type="button" @click="showPass = !showPass" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-500 hover:text-slate-900 dark:hover:text-white cursor-pointer" aria-label="Toggle password visibility">
                            <svg x-show="!showPass" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            <svg x-show="showPass" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" x-cloak><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                        </button>
                    </div>
                    @if($errors->has('password'))
                        <p class="text-rose-600 text-xs mt-1 font-bold">{{ $errors->first('password') }}</p>
                    @endif
                </div>

                {{-- Confirm Password --}}
                <div>
                    <label for="password_confirmation" class="block text-xs font-extrabold uppercase tracking-wider text-slate-800 dark:text-slate-200 mb-1">
                        {{ __('Confirm Password') }}
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-orange-500">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <input type="password" 
                               name="password_confirmation" 
                               id="password_confirmation" 
                               required 
                               autocomplete="new-password" 
                               placeholder="{{ __('Repeat your password') }}" 
                               class="w-full pl-10 pr-3.5 py-2.5 text-xs sm:text-sm font-bold rounded-xl bg-white dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white placeholder:text-slate-400 focus:border-orange-500 outline-none transition-colors">
                    </div>
                </div>

                {{-- Terms Agreement --}}
                <div class="flex items-start gap-2.5 pt-1">
                    <input type="checkbox" id="terms" name="terms" required
                           class="w-4 h-4 mt-0.5 rounded border-slate-300 text-orange-500 focus:ring-orange-500 cursor-pointer accent-orange-500">
                    <label for="terms" class="text-xs font-bold text-slate-700 dark:text-slate-300 cursor-pointer leading-relaxed">
                        {{ __("I agree to SRMS's") }}
                        <a href="/terms" class="text-orange-600 dark:text-orange-400 hover:underline" target="_blank">{{ __('Terms of Service') }}</a>
                        {{ __('and') }}
                        <a href="/privacy" class="text-orange-600 dark:text-orange-400 hover:underline" target="_blank">{{ __('Privacy Policy') }}</a>.
                    </label>
                </div>

                {{-- Navigation Buttons --}}
                <div class="flex items-center gap-3 pt-2">
                    <button type="button" 
                            @click="step = 1" 
                            class="w-1/3 py-3 rounded-2xl text-xs sm:text-sm font-extrabold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 transition-colors flex items-center justify-center gap-1.5"
                            id="btn-back-step1">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                        <span>{{ __('Back') }}</span>
                    </button>

                    <button type="submit" 
                            class="w-2/3 py-3.5 rounded-2xl text-xs sm:text-sm font-extrabold bg-[#C2410C] hover:bg-[#9A3412] text-white shadow-lg shadow-orange-500/25 transition-all duration-200 active:scale-95 cursor-pointer flex items-center justify-center gap-2"
                            id="btn-submit-register">
                        <span :class="{ 'hidden': loading }">{{ __('Create Account') }}</span>
                        <span :class="{ 'hidden': !loading }" x-cloak class="flex items-center gap-2">
                            <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span>{{ __('Creating account...') }}</span>
                        </span>
                    </button>
                </div>
            </div>
        </form>

        <div class="h-px bg-slate-200 dark:bg-slate-700 my-6"></div>

        <p class="text-center text-xs sm:text-sm text-slate-700 dark:text-slate-300 font-bold">
            {{ __('Already have an account?') }}
            <a href="/login" class="text-orange-600 dark:text-orange-400 font-extrabold hover:underline ml-1" id="goto-login">
                {{ __('Sign In') }}
            </a>
        </p>
    </div>
</x-layouts.auth>
