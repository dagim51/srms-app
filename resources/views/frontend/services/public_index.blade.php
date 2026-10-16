<x-layouts.app>
    <x-slot name="title">{{ __('Verified Real Estate Professional Services in Ethiopia | SRMS') }}</x-slot>
    <x-slot name="description">Connect with certified property lawyers, cadastral surveyors, licensed architects, property valuers, and structural inspectors across Ethiopia.</x-slot>

    <div class="py-8 page-container min-h-screen space-y-6 pt-24">
        {{-- Clean Page Header --}}
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-warm dark:border-warm pb-5">
            <div class="max-w-3xl space-y-2">
                <nav class="flex items-center gap-2 text-xs text-muted dark:text-muted">
                    <a href="/" class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors">{{ __('Home') }}</a>
                    <span>/</span>
                    <span class="text-muted dark:text-white font-medium">{{ __('Professional Services') }}</span>
                </nav>
                <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full bg-orange-500/10 border border-orange-500/20 text-orange-600 dark:bg-primary-500/20 dark:border-primary-500/30 dark:text-primary-300 text-xs font-bold uppercase tracking-wider">
                    <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-orange-500"></span>
                    Certified & Vetted Practitioners
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold font-display tracking-tight text-muted dark:text-white leading-tight">
                    Professional <span class="text-gradient-brand">{{ __('Real Estate') }}</span> Services
                </h1>
                <p class="text-xs sm:text-sm text-muted dark:text-dark-subtle leading-relaxed font-normal max-w-2xl">{{ __('Secure your transaction with licensed attorneys, cadastral land surveyors, registered architects, and accredited property appraisers.') }}</p>
            </div>
        </div>

        {{-- Search & Category Filter Section --}}
        <div class="space-y-4">
            <form action="{{ route('services.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <svg class="w-5 h-5 text-muted absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="{{ __('Search lawyers, surveyors, architects, or services...') }}" 
                           class="w-full pl-12 pr-4 py-3 rounded-2xl bg-white dark:bg-[#1E293B] border border-warm dark:border-warm text-sm text-muted dark:text-white placeholder:text-muted focus:outline-none focus:ring-2 focus:ring-primary-500 shadow-sm">
                </div>
                @if(request('category'))
                    <input type="hidden" name="category" value="{{ request('category') }}">
                @endif
                <button type="submit" class="btn btn-primary px-8 text-xs font-bold py-3 active:scale-[0.98] transition-transform">{{ __('Search') }}</button>
            </form>

            {{-- Categories Scroll Strip --}}
            <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-hide border-b border-warm dark:border-warm">
                <a href="{{ route('services.index', request()->only('q')) }}" 
                   class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all {{ $currentCategory === 'all' ? 'bg-primary-500 text-white shadow-sm' : 'bg-surface-subtle hover:bg-surface-subtle text-muted dark:bg-surface dark:text-dark-subtle dark:hover:bg-surface' }}">
                    All Categories
                </a>
                @foreach($categories as $cat)
                    <a href="{{ route('services.index', array_merge(request()->only('q'), ['category' => $cat])) }}" 
                       class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all {{ $currentCategory === $cat ? 'bg-primary-500 text-white shadow-sm' : 'bg-surface-subtle hover:bg-surface-subtle text-muted dark:bg-surface dark:text-dark-subtle dark:hover:bg-surface' }}">
                        {{ $cat }}
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Professionals Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($professionals as $pro)
                <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-3xl p-6 shadow-xl shadow-orange-950/5 hover:shadow-2xl hover:shadow-orange-950/10 dark:shadow-black/60 dark:hover:shadow-black/80 hover:-translate-y-2 transition-all duration-300 flex flex-col justify-between group">
                    <div class="space-y-4">
                        {{-- Top Header with Avatar & Badge --}}
                        <div class="flex items-start gap-4">
                            <img src="{{ $pro['avatar'] }}" alt="{{ $pro['name'] }}" class="w-14 h-14 rounded-2xl object-cover border border-warm dark:border-warm shadow-sm flex-shrink-0">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-1">
                                    <span class="text-xs font-bold uppercase tracking-wider text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-950/40 px-2 py-0.5 rounded-md">
                                        {{ $pro['category'] }}
                                    </span>
                                    <span class="flex items-center gap-1 text-xs font-bold text-muted dark:text-white">
                                        <svg class="w-3.5 h-3.5 text-amber-400 fill-amber-400" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                        {{ $pro['rating'] }} <span class="text-muted font-normal">({{ $pro['reviews'] }})</span>
                                    </span>
                                </div>
                                <h3 class="font-bold text-base text-muted dark:text-white mt-1.5 truncate group-hover:text-primary-500 transition-colors">
                                    {{ $pro['name'] }}
                                </h3>
                                <p class="text-xs text-muted dark:text-muted flex items-center gap-1 mt-0.5">
                                    <svg class="w-3 h-3 text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    {{ $pro['location'] }}
                                </p>
                            </div>
                        </div>

                        {{-- Specialty Details --}}
                        <p class="text-xs text-muted dark:text-dark-subtle leading-relaxed line-clamp-2">
                            {{ $pro['specialty'] }}
                        </p>

                        {{-- Rate Display --}}
                        <div class="pt-3 border-t border-warm dark:border-warm flex items-center justify-between">
                            <div>
                                <p class="text-xs uppercase font-bold text-muted">{{ __('Standard Rate') }}</p>
                                <p class="font-extrabold text-sm text-primary-600 dark:text-primary-400">{{ $pro['rate'] }}</p>
                            </div>
                            <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 px-2 py-0.5 rounded-full">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                                Verified
                            </span>
                        </div>
                    </div>

                    {{-- Action Button --}}
                    <div class="pt-5 mt-4 border-t border-warm dark:border-warm flex gap-2">
                        <a href="{{ route('services.show', $pro['id']) }}" class="btn btn-primary w-full text-xs font-bold py-2.5 flex items-center justify-center gap-1.5 active:scale-[0.98] transition-transform">
                            <span>{{ __('View Profile & Menu') }}</span>
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-3 text-center py-16 bg-white dark:bg-[#1E293B] rounded-3xl border border-warm dark:border-warm">
                    <p class="text-base text-muted">{{ __('No service professionals found matching your query.') }}</p>
                    <a href="{{ route('services.index') }}" class="btn btn-primary btn-sm mt-4">{{ __('Clear Filters') }}</a>
                </div>
            @endforelse
        </div>

        {{-- Verification Assurance Card --}}
        <div class="bg-surface-subtle dark:bg-surface/40 border border-warm dark:border-warm rounded-3xl p-8 flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="space-y-1">
                <h3 class="text-lg font-bold text-muted dark:text-white">{{ __('Are you a licensed attorney, surveyor, or architect?') }}</h3>
                <p class="text-xs text-muted dark:text-muted">{{ __('Join the SRMS verified professional network to receive client requests and title escrow assignments.') }}</p>
            </div>
            <a href="{{ route('dashboard.services.dashboard') }}" class="btn btn-outline text-xs font-bold py-2.5 px-6 whitespace-nowrap active:scale-[0.98] transition-transform">{{ __('Provider Portal') }}</a>
        </div>
    </div>
</x-layouts.app>
