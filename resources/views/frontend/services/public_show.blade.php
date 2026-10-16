<x-layouts.app>
    <x-slot name="title">{{ $provider['name'] }} — {{ $provider['category'] }} | SRMS Ethiopia</x-slot>
    <x-slot name="description">{{ $provider['about'] }}</x-slot>

    <div class="py-12 page-container min-h-screen space-y-10 pt-28" x-data="{ requestModalOpen: false, selectedService: '{{ $provider['services'][0]['name'] ?? 'General Consultation' }}', requestedSuccess: false }">
        {{-- Breadcrumb & Back Link --}}
        <div>
            <a href="{{ route('services.index') }}" class="text-xs font-semibold text-muted hover:text-primary-600 dark:hover:text-primary-400 flex items-center gap-2 mb-4 transition-colors w-max">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                <span>{{ __('Back to All Professional Services') }}</span>
            </a>

            {{-- Provider Banner Header --}}
            <div class="card p-6 sm:p-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                <div class="flex items-start sm:items-center gap-5">
                    <img src="{{ $provider['avatar'] }}" alt="{{ $provider['name'] }}" class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl object-cover border-2 border-primary-500/20 shadow-md flex-shrink-0">
                    <div>
                        <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                            <span class="text-xs font-bold uppercase tracking-wider text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-950/40 px-2.5 py-0.5 rounded-md">
                                {{ $provider['category'] }}
                            </span>
                            <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 px-2.5 py-0.5 rounded-md">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                                Verified Provider
                            </span>
                        </div>
                        <h1 class="card-title text-2xl sm:text-4xl tracking-tight">
                            {{ $provider['name'] }}
                        </h1>
                        <div class="flex items-center gap-3 mt-2 text-xs sm:text-sm">
                            <span class="flex items-center gap-1 font-bold text-muted dark:text-white">
                                <svg class="w-4 h-4 text-amber-400 fill-amber-400" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                {{ $provider['rating'] }} 
                            </span>
                            <span class="text-muted">({{ $provider['reviews'] }} Verified Reviews)</span>
                            <span class="text-muted dark:text-muted">•</span>
                            <span class="font-extrabold text-primary-600 dark:text-primary-400">{{ $provider['rate'] }}</span>
                        </div>
                    </div>
                </div>

                <div class="w-full md:w-auto flex flex-col sm:flex-row gap-3">
                    <button type="button" @click="requestModalOpen = true" class="btn btn-primary px-8 text-xs font-bold py-3.5 shadow-md flex items-center justify-center gap-2 active:scale-[0.98] transition-transform">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                        <span>{{ __('Request Service / Consultation') }}</span>
                    </button>
                    <a href="tel:+251911000000" class="btn btn-outline text-xs font-bold py-3.5 px-5 flex items-center justify-center gap-2 active:scale-[0.98] transition-transform">
                        <svg class="w-4 h-4 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                        <span>{{ __('Direct Call') }}</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Main Column: About & Services Menu --}}
            <div class="lg:col-span-2 space-y-8">
                {{-- About Card --}}
                <div class="card p-6 md:p-8 space-y-4">
                    <h2 class="card-title text-xl">{{ __('Practice Overview & Accreditation') }}</h2>
                    <p class="text-sm text-muted dark:text-dark-subtle leading-relaxed">
                        {{ $provider['about'] }}
                    </p>
                    <div class="pt-4 border-t border-[#EBDBC9] dark:border-[#273244] flex flex-wrap gap-4 text-xs">
                        <div class="flex items-center gap-2 text-muted dark:text-dark-subtle">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span>{{ __('Ethiopian Ministry of Justice Licensed') }}</span>
                        </div>
                        <div class="flex items-center gap-2 text-muted dark:text-dark-subtle">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span>{{ __('Bilingual Amharic & English Notary') }}</span>
                        </div>
                        <div class="flex items-center gap-2 text-muted dark:text-dark-subtle">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span>{{ __('Addis Ababa Land Bureau Registered') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Services Menu Card --}}
                <div class="card p-6 md:p-8 space-y-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="card-title text-xl">{{ __('Services & Standard Pricing') }}</h2>
                            <p class="card-subtitle text-xs mt-1">{{ __('Select a service to start an inquiry or escrow assignment') }}</p>
                        </div>
                        <span class="text-xs font-bold text-primary-600 bg-primary-50 dark:bg-primary-950/40 px-3 py-1 rounded-full">
                            {{ count($provider['services']) }} Services Available
                        </span>
                    </div>

                    <div class="space-y-4">
                        @foreach($provider['services'] as $service)
                            <div class="card-nested p-5 hover:border-primary-300 dark:hover:border-primary-700 transition-all group flex flex-col sm:flex-row justify-between sm:items-center gap-4">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <h3 class="font-bold text-base text-muted dark:text-white group-hover:text-primary-500 transition-colors">
                                            {{ $service['name'] }}
                                        </h3>
                                        <span class="text-xs font-bold text-muted bg-surface-subtle dark:bg-surface px-2 py-0.5 rounded-md">
                                            {{ $service['duration'] }}
                                        </span>
                                    </div>
                                    @if(isset($service['desc']))
                                        <p class="text-xs text-muted dark:text-muted max-w-xl leading-relaxed">
                                            {{ $service['desc'] }}
                                        </p>
                                    @endif
                                </div>

                                <div class="flex items-center justify-between sm:justify-end gap-4 flex-shrink-0">
                                    <p class="font-black text-base text-primary-600 dark:text-primary-400">
                                        {{ $service['price'] }}
                                    </p>
                                    <button type="button" @click="selectedService = '{{ $service['name'] }}'; requestModalOpen = true;" 
                                            class="btn btn-outline text-xs font-bold py-2 px-4 active:scale-[0.98] transition-transform">{{ __('Book') }}</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Sidebar Column: Assurance & Office Info --}}
            <div class="space-y-6">
                <div class="card p-6 space-y-5">
                    <h3 class="card-title text-base pb-3 border-b border-[#EBDBC9] dark:border-[#273244]">{{ __('Provider Information') }}</h3>
                    <div class="space-y-4 text-xs">
                        <div>
                            <p class="card-kv-label">{{ __('Office Location') }}</p>
                            <p class="card-kv-value mt-0.5">{{ $provider['location'] }}</p>
                        </div>
                        <div>
                            <p class="card-kv-label">{{ __('Average Response Time') }}</p>
                            <p class="font-bold text-emerald-600 dark:text-emerald-400 mt-0.5">&lt; 15 Minutes</p>
                        </div>
                        <div>
                            <p class="card-kv-label">{{ __('Payment Methods') }}</p>
                            <p class="card-kv-value mt-0.5">{{ __('Telebirr, CBE Birr, Bank Wire, SRMS Escrow') }}</p>
                        </div>
                        <div>
                            <p class="card-kv-label">{{ __('SRMS Trust Status') }}</p>
                            <p class="card-kv-value text-primary-600 dark:text-primary-400 mt-0.5">{{ __('Verified Identity & Clean Audit History') }}</p>
                        </div>
                    </div>
                </div>

                <div class="card p-6 space-y-3 bg-gradient-to-br from-primary-50 to-orange-100/50 dark:from-primary-950/20 dark:to-orange-950/10 border-primary-200 dark:border-primary-900/40">
                    <div class="w-8 h-8 rounded-xl bg-primary-500 text-white flex items-center justify-center font-bold">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                    </div>
                    <h4 class="card-title text-sm">{{ __('SRMS Escrow Guarantee') }}</h4>
                    <p class="text-xs text-muted dark:text-muted leading-relaxed">{{ __('Never pay upfront without protection. Payments for legal and surveying services can be held safely in SRMS escrow until deliverables are provided.') }}</p>
                </div>
            </div>
        </div>

        {{-- Request Modal (Alpine.js) --}}
        <div x-show="requestModalOpen" 
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <div class="card max-w-lg w-full p-8 relative space-y-6"
                 @click.away="requestModalOpen = false">
                <button type="button" @click="requestModalOpen = false" class="absolute top-6 right-6 text-muted hover:text-muted dark:hover:text-white">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>

                <div x-show="!requestedSuccess" class="space-y-4">
                    <div>
                        <h3 class="text-xl font-bold font-display text-muted dark:text-white">{{ __('Book Consultation / Service') }}</h3>
                        <p class="text-xs text-muted mt-1">Connecting with <strong class="text-muted dark:text-white">{{ $provider['name'] }}</strong></p>
                    </div>

                    <form @submit.prevent="requestedSuccess = true" class="space-y-4 text-xs">
                        <div>
                            <label class="block font-bold text-muted dark:text-dark-subtle mb-1">{{ __('Selected Service') }}</label>
                            <input type="text" x-model="selectedService" readonly class="form-input w-full text-xs bg-surface dark:bg-surface/50 font-bold text-primary-600">
                        </div>

                        <div>
                            <label class="block font-bold text-muted dark:text-dark-subtle mb-1">{{ __('Your Full Name') }}</label>
                            <input type="text" required placeholder="{{ __('Abebe Bikila') }}" class="form-input w-full text-xs">
                        </div>

                        <div>
                            <label class="block font-bold text-muted dark:text-dark-subtle mb-1">Phone Number (Ethiopia)</label>
                            <input type="tel" required placeholder="+251 91 123 4567" class="form-input w-full text-xs">
                        </div>

                        <div>
                            <label class="block font-bold text-muted dark:text-dark-subtle mb-1">{{ __('Details / Property Reference') }}</label>
                            <textarea rows="3" required placeholder="{{ __('Describe your property situation or documents needing review...') }}" class="form-input w-full text-xs"></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-2">
                            <button type="button" @click="requestModalOpen = false" class="btn btn-ghost text-xs">{{ __('Cancel') }}</button>
                            <button type="submit" class="btn btn-primary text-xs font-bold px-6 py-2.5">{{ __('Send Request') }}</button>
                        </div>
                    </form>
                </div>

                {{-- Success Notice --}}
                <div x-show="requestedSuccess" class="text-center py-6 space-y-3">
                    <div class="w-12 h-12 bg-emerald-500/10 text-emerald-500 rounded-full flex items-center justify-center mx-auto">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                    </div>
                    <h4 class="text-lg font-bold text-muted dark:text-white">{{ __('Service Request Submitted') }}</h4>
                    <p class="text-xs text-muted max-w-sm mx-auto leading-relaxed">
                        {{ $provider['name'] }} has received your request and will reach out via phone/SMS shortly with an escrow quote.
                    </p>
                    <button type="button" @click="requestModalOpen = false; requestedSuccess = false;" class="btn btn-primary text-xs font-bold px-8 mt-4">{{ __('Done') }}</button>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
