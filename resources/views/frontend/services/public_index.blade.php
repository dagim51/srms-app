<x-layouts.app>
    <x-slot name="title">{{ __('Private Concierge & Professional Services | Aura Estates SRMS') }}</x-slot>
    <x-slot name="description">Connect with certified property lawyers, cadastral surveyors, licensed architects, property valuers, and structural inspectors across Ethiopia.</x-slot>

    <div class="py-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-12 min-h-screen space-y-8 pt-28">
        {{-- Clean Page Header (Aura Estates Style) --}}
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-slate-200 dark:border-slate-800 pb-6 cinematic-fade-up">
            <div class="max-w-3xl space-y-2">
                <nav class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 font-medium">
                    <a href="/" class="hover:text-orange-600 dark:hover:text-orange-400 transition-colors">{{ __('Home') }}</a>
                    <span>/</span>
                    <span class="text-slate-900 dark:text-white font-bold">{{ __('Professional Services') }}</span>
                </nav>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-orange-100/80 dark:bg-orange-950/60 border border-orange-200 dark:border-orange-800/80 text-orange-700 dark:text-orange-300 text-xs font-semibold uppercase tracking-wider">
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-orange-500 animate-ping"></span>
                    <span>{{ __('Certified & Vetted Practitioners') }}</span>
                </div>
                <h1 class="text-3xl sm:text-5xl font-serif font-bold tracking-tight text-slate-900 dark:text-white leading-tight">
                    {{ __('Private Concierge &') }} <span class="italic font-normal text-transparent bg-clip-text bg-gradient-to-r from-orange-600 to-amber-500 dark:from-orange-400 dark:to-amber-300">{{ __('Advisory') }}</span> {{ __('Services') }}
                </h1>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed font-normal max-w-2xl">
                    {{ __('Secure your acquisitions with licensed attorneys, cadastral land surveyors, registered architects, and accredited property appraisers.') }}
                </p>
            </div>
        </div>

        {{-- Search & Category Filter Section --}}
        <div class="space-y-4 cinematic-fade-up cinematic-stagger-1">
            <form action="{{ route('services.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <svg class="w-5 h-5 text-orange-500 absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="{{ __('Search lawyers, surveyors, architects, or services...') }}" 
                           class="w-full pl-12 pr-4 py-3 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-sm text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:border-orange-500 shadow-sm font-medium transition-colors">
                </div>
                @if(request('category'))
                    <input type="hidden" name="category" value="{{ request('category') }}">
                @endif
                <button type="submit" class="btn btn-primary px-8 text-xs font-semibold py-3 rounded-2xl bg-gradient-to-r from-orange-600 to-orange-500 hover:from-orange-500 hover:to-orange-600 text-white shadow-md shadow-orange-500/20 active:scale-95 transition-all cursor-pointer">
                    {{ __('Search Directory') }}
                </button>
            </form>

            {{-- Categories Scroll Strip --}}
            <div class="flex items-center gap-2 overflow-x-auto pb-2 border-b border-slate-200 dark:border-slate-800">
                <a href="{{ route('services.index', request()->only('q')) }}" 
                   class="px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition-all {{ $currentCategory === 'all' ? 'bg-orange-600 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:text-orange-600' }}">
                    {{ __('All Categories') }}
                </a>
                @foreach($categories as $cat)
                    <a href="{{ route('services.index', array_merge(request()->only('q'), ['category' => $cat])) }}" 
                       class="px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition-all {{ $currentCategory === $cat ? 'bg-orange-600 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:text-orange-600' }}">
                        {{ $cat }}
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Professionals Grid (Aura Estates Style) --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse($professionals as $pro)
                <div class="card bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 hover:border-orange-500/40 rounded-3xl p-6 hover: transition-all duration-500 flex flex-col justify-between group cinematic-fade-up cinematic-stagger-{{ ($loop->index % 3) + 1 }}">
                    <div class="space-y-4">
                        {{-- Top Header with Avatar & Badge --}}
                        <div class="flex items-start gap-4">
                            <img src="{{ $pro['avatar'] }}" alt="{{ $pro['name'] }}" class="w-14 h-14 rounded-2xl object-cover border border-slate-200 dark:border-slate-700 shadow-sm flex-shrink-0">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-1">
                                    <span class="text-xs font-bold uppercase tracking-wider text-orange-600 dark:text-orange-400 bg-orange-50 dark:bg-orange-950/40 px-2.5 py-0.5 rounded-lg border border-orange-200/60 dark:border-orange-800/40">
                                        {{ $pro['category'] }}
                                    </span>
                                    <span class="flex items-center gap-1 text-xs font-bold text-slate-900 dark:text-white">
                                        <span class="text-amber-500">★</span>
                                        {{ $pro['rating'] }} <span class="text-slate-400 font-normal">({{ $pro['reviews'] }})</span>
                                    </span>
                                </div>
                                <h3 class="font-serif font-bold text-base text-slate-900 dark:text-white mt-2 truncate group-hover:text-orange-600 transition-colors">
                                    {{ $pro['name'] }}
                                </h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-1 mt-0.5">
                                    <svg class="w-3.5 h-3.5 text-orange-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                                    <span>{{ $pro['location'] }}</span>
                                </p>
                            </div>
                        </div>

                        {{-- Specialty Details --}}
                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed line-clamp-2">
                            {{ $pro['specialty'] }}
                        </p>

                        {{-- Rate Display --}}
                        <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                            <div>
                                <p class="text-xs uppercase font-bold text-slate-400">{{ __('Advisory Fee') }}</p>
                                <p class="font-bold text-sm text-orange-600 dark:text-orange-400 mt-0.5">{{ $pro['rate'] }}</p>
                            </div>
                            <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 px-2.5 py-1 rounded-full border border-emerald-200/60 dark:border-emerald-800/40">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                                {{ __('Verified Partner') }}
                            </span>
                        </div>
                    </div>

                    {{-- Action Button --}}
                    <div class="pt-5 mt-4 border-t border-slate-100 dark:border-slate-800 flex gap-2">
                        <a href="{{ route('services.show', $pro['id']) }}" class="btn btn-primary w-full text-xs font-semibold py-2.5 px-4 rounded-xl bg-gradient-to-r from-orange-600 to-orange-500 hover:from-orange-500 hover:to-orange-600 text-white shadow-md shadow-orange-500/20 flex items-center justify-center gap-1.5 transition-all">
                            <span>{{ __('Consultation & Services') }}</span>
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </a>
                    </div>
                </div>
            @empty
                <div class="card col-span-3 text-center py-16 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800">
                    <p class="text-base text-slate-500">{{ __('No service professionals found matching your query.') }}</p>
                    <a href="{{ route('services.index') }}" class="btn btn-primary btn-sm mt-4 px-4 py-2 rounded-xl bg-orange-600 text-white font-semibold">{{ __('Clear Filters') }}</a>
                </div>
            @endforelse
        </div>

        {{-- Verification Assurance Card (Obsidian Card) --}}
        <div class="card bg-slate-900 dark:bg-slate-950 text-white border border-slate-800 rounded-3xl p-8 sm:p-10 flex flex-col md:flex-row items-center justify-between gap-6 cinematic-fade-up cinematic-stagger-2">
            <div class="space-y-1">
                <h3 class="text-lg font-serif font-bold text-white">{{ __('Are you a licensed attorney, surveyor, or architectural firm?') }}</h3>
                <p class="text-xs text-slate-300">{{ __('Join the Aura Estates verified network to receive high-net-worth client requests and cadastral escrow assignments.') }}</p>
            </div>
            <a href="{{ route('dashboard.services.dashboard') }}" class="btn btn-secondary text-xs font-semibold py-3 px-6 rounded-xl bg-slate-800 border border-slate-700 hover:bg-orange-600 hover:border-orange-600 text-white transition-all whitespace-nowrap">{{ __('Provider Portal') }}</a>
        </div>
    </div>
</x-layouts.app>
