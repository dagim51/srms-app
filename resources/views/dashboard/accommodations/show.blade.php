<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Manage Accommodation') }}</x-slot>
    <x-slot name="breadcrumb">
        <a href="{{ route('dashboard') }}" class="hover:text-primary-600 transition-colors">{{ __('Dashboard') }}</a>
        <span>/</span>
        <a href="{{ route('dashboard.accommodations.index') }}" class="hover:text-primary-600 transition-colors">{{ __('Hosting') }}</a>
        <span>/</span>
        <span class="text-muted dark:text-white font-medium">{{ __('Manage') }}</span>
    </x-slot>

    <div class="max-w-6xl mx-auto page-enter space-y-6">
        <x-page-header
            title="Manage Accommodation"
            :description="__('Manage operational status, rates, calendar availability, and guest stay rules for') . ' ' . $accommodation->property->title . '.'"
            icon="key"
            badge="Accommodations">
            <x-slot:actions>
                <div class="flex items-center gap-2">
                    <a href="{{ route('dashboard.accommodations.calendar', $accommodation) }}" class="btn btn-secondary text-xs flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        {{ __('Calendar') }}
                    </a>
                    <a href="{{ route('dashboard.accommodations.pricing', $accommodation) }}" class="btn btn-secondary text-xs flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        {{ __('Pricing') }}
                    </a>
                    <a href="{{ route('dashboard.accommodations.edit', $accommodation) }}" class="btn btn-primary text-xs flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                        {{ __('Edit') }}
                    </a>
                </div>
            </x-slot:actions>
        </x-page-header>

        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 text-emerald-800 text-sm font-medium border border-emerald-200">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            {{-- Left Column: Settings & Details --}}
            <div class="lg:col-span-2 space-y-6">
                
                {{-- Accommodation Overview --}}
                <div class="card bg-white dark:bg-[#1E293B] border border-warm dark:border-[#273244] rounded-2xl overflow-hidden">
                    @if($accommodation->property && $accommodation->property->media->count())
                        @php $primaryImg = $accommodation->property->media->first()->url ?? $accommodation->property->media->first()->storage_key; @endphp
                        <div class="h-64 w-full relative">
                            <img src="{{ $primaryImg }}" alt="{{ $accommodation->property->title }}" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent flex items-end p-6">
                                <div>
                                    <h2 class="text-2xl font-bold font-display text-white mb-2">{{ $accommodation->property->title }}</h2>
                                    <div class="flex items-center gap-3">
                                        @if($accommodation->is_active)
                                            <span class="px-3 py-1 rounded-md text-xs font-bold uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 backdrop-blur-md">{{ __('Active Listing') }}</span>
                                        @else
                                            <span class="px-3 py-1 rounded-md text-xs font-bold uppercase tracking-wider bg-surface0/50 text-muted border border-warm/30 backdrop-blur-md">{{ __('Inactive') }}</span>
                                        @endif
                                        <a href="{{ route('dashboard.accommodations.edit', $accommodation) }}" class="text-white text-xs font-bold hover:underline">{{ __('Edit Specs') }}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="p-6 border-b border-warm dark:border-warm">
                            <h2 class="text-2xl font-bold font-display text-muted dark:text-white">{{ $accommodation->property->title ?? 'Accommodation' }}</h2>
                        </div>
                    @endif

                    <div class="p-6 sm:p-8">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-muted mb-4">{{ __('Room & Unit Configuration') }}</h3>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
                            <div class="card p-4 bg-surface dark:bg-surface/50 rounded-xl border border-warm dark:border-warm text-center">
                                <svg class="w-6 h-6 mx-auto mb-2 text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                <span class="block font-bold text-muted dark:text-white text-lg">{{ $accommodation->max_guests }}</span>
                                <span class="text-xs text-muted">{{ __('Max Guests') }}</span>
                            </div>
                            <div class="card p-4 bg-surface dark:bg-surface/50 rounded-xl border border-warm dark:border-warm text-center">
                                <svg class="w-6 h-6 mx-auto mb-2 text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                                <span class="block font-bold text-muted dark:text-white text-lg">{{ $accommodation->bedrooms }}</span>
                                <span class="text-xs text-muted">{{ __('Bedrooms') }}</span>
                            </div>
                            <div class="card p-4 bg-surface dark:bg-surface/50 rounded-xl border border-warm dark:border-warm text-center">
                                <svg class="w-6 h-6 mx-auto mb-2 text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" /></svg>
                                <span class="block font-bold text-muted dark:text-white text-lg">{{ $accommodation->beds }}</span>
                                <span class="text-xs text-muted">{{ __('Beds') }}</span>
                            </div>
                            <div class="card p-4 bg-surface dark:bg-surface/50 rounded-xl border border-warm dark:border-warm text-center">
                                <svg class="w-6 h-6 mx-auto mb-2 text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                                <span class="block font-bold text-muted dark:text-white text-lg">{{ $accommodation->bathrooms }}</span>
                                <span class="text-xs text-muted">{{ __('Baths') }}</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-8">
                            <div>
                                <h3 class="text-sm font-bold uppercase tracking-wider text-muted mb-4">{{ __('Check-in / Check-out') }}</h3>
                                <div class="space-y-3">
                                    <div class="card flex items-center justify-between p-3 bg-surface dark:bg-surface/30 border border-warm dark:border-warm rounded-xl">
                                        <span class="text-muted dark:text-muted text-sm">{{ __('Check-in Time') }}</span>
                                        <span class="font-bold text-muted dark:text-white">{{ date('h:i A', strtotime($accommodation->check_in_time)) }}</span>
                                    </div>
                                    <div class="card flex items-center justify-between p-3 bg-surface dark:bg-surface/30 border border-warm dark:border-warm rounded-xl">
                                        <span class="text-muted dark:text-muted text-sm">{{ __('Check-out Time') }}</span>
                                        <span class="font-bold text-muted dark:text-white">{{ date('h:i A', strtotime($accommodation->check_out_time)) }}</span>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h3 class="text-sm font-bold uppercase tracking-wider text-muted mb-4">{{ __('House Rules') }}</h3>
                                <div class="p-4 bg-amber-50 dark:bg-amber-900/10 border border-amber-100 dark:border-amber-800/30 rounded-xl">
                                    <p class="text-sm text-amber-800 dark:text-amber-300 whitespace-pre-line leading-relaxed">{{ $accommodation->rules ?: 'No specific rules defined.' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            
            {{-- Right Column: Host Profile & Verification --}}
            <div class="space-y-6">
                
                {{-- Verification State --}}
                <div class="card bg-white dark:bg-[#1E293B] border border-warm dark:border-[#273244] rounded-2xl overflow-hidden p-6">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-muted mb-4">{{ __('Host Verification State') }}</h3>
                    
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-16 h-16 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 flex items-center justify-center relative flex-shrink-0">
                            <span class="text-2xl font-bold">{{ substr($user->name, 0, 1) }}</span>
                            <div class="absolute bottom-0 right-0 w-5 h-5 bg-emerald-500 border-2 border-white dark:border-[#1E293B] rounded-full flex items-center justify-center">
                                <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            </div>
                        </div>
                        <div>
                            <h4 class="font-bold text-muted dark:text-white text-lg">{{ $user->name }}</h4>
                            <p class="text-xs text-muted">{{ __('Verified Host') }}</p>
                        </div>
                    </div>

                    <div class="space-y-3 pt-4 border-t border-warm dark:border-warm">
                        <div class="flex items-center gap-3 text-sm text-muted dark:text-dark-subtle">
                            <svg class="w-5 h-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <span>{{ __('Identity Verified') }}</span>
                        </div>
                        <div class="flex items-center gap-3 text-sm text-muted dark:text-dark-subtle">
                            <svg class="w-5 h-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <span>{{ __('Email Verified') }}</span>
                        </div>
                        <div class="flex items-center gap-3 text-sm text-muted dark:text-dark-subtle">
                            <svg class="w-5 h-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <span>{{ __('Phone Verified') }}</span>
                        </div>
                    </div>
                </div>
                
                {{-- Stay Listing Setup summary --}}
                <div class="card bg-white dark:bg-[#1E293B] border border-warm dark:border-[#273244] rounded-2xl overflow-hidden p-6">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-muted mb-4">{{ __('Pricing & Availability') }}</h3>
                    
                    <div class="card p-4 bg-surface dark:bg-surface/30 border border-warm dark:border-warm rounded-xl text-center mb-4">
                        <span class="block text-muted text-xs font-bold uppercase tracking-wider mb-1">{{ __('Nightly Rate') }}</span>
                        <span class="block text-2xl font-bold text-muted dark:text-white">{{ $accommodation->property->currency === 'USD' ? '$' : 'Br' }}{{ number_format($accommodation->property->price / 30, 2) }}</span>
                    </div>

                    <a href="{{ route('dashboard.accommodations.calendar', $accommodation) }}" class="btn btn-outline w-full text-sm mb-2">{{ __('Manage Calendar') }}</a>
                    <a href="{{ route('dashboard.accommodations.pricing', $accommodation) }}" class="btn btn-outline w-full text-sm">{{ __('Pricing Settings') }}</a>
                </div>

            </div>
            
        </div>
    </div>
</x-layouts.dashboard>
