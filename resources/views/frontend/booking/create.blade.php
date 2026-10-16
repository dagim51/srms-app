<x-layouts.app>
    <x-slot name="title">Request to Book - {{ $accommodation->property->title }}</x-slot>

    {{-- Subtle ambient background --}}
    <div class="fixed inset-0 pointer-events-none overflow-hidden -z-10">
        <div class="absolute top-0 right-0 w-full max-w-3xl h-full max-h-3xl bg-primary-400/5 rounded-full blur-[120px]"></div>
    </div>

    <div class="page-container pt-28 pb-24">

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-x-16 gap-y-12 items-start">

            {{-- ── LEFT COLUMN ── --}}
            <div class="lg:col-span-7 order-2 lg:order-1">

                {{-- Page heading --}}
                <div class="flex items-center gap-4 mb-10">
                    <a href="{{ route('properties.show', $accommodation->property) }}"
                       class="group flex items-center justify-center w-11 h-11 rounded-full bg-white dark:bg-surface border border-warm dark:border-warm shadow-sm hover:shadow-md hover:border-primary-400 transition-all duration-200 shrink-0">
                        <svg class="w-5 h-5 text-muted group-hover:text-primary-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                    <h1 class="text-3xl md:text-4xl font-extrabold font-display text-muted dark:text-white tracking-tight">{{ __('Request to book') }}</h1>
                </div>

                {{-- Validation errors --}}
                @if($errors->any())
                    <div class="mb-8 p-4 rounded-2xl bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800/50 text-rose-800 dark:text-rose-300 text-sm">
                        <div class="flex gap-3">
                            <svg class="w-5 h-5 shrink-0 mt-0.5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <ul class="space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <form action="{{ route('booking.store', $accommodation) }}" method="POST" class="space-y-10">
                    @csrf

                    {{-- ── 1. Your trip ── --}}
                    <section>
                        <h2 class="text-xl font-bold text-muted dark:text-white mb-5 flex items-center gap-3">
                            <span class="flex items-center justify-center w-7 h-7 rounded-md bg-primary-100 dark:bg-primary-900/40 text-primary-600 dark:text-primary-400 text-xs font-black">1</span>
                            Your trip
                        </h2>

                        <div class="rounded-2xl border border-warm dark:border-warm bg-white dark:bg-surface/50 overflow-hidden divide-y divide-warm dark:divide-dark-border">
                            <div class="grid grid-cols-2 divide-x divide-warm dark:divide-dark-border">
                                <div class="p-5">
                                    <label class="block text-xs font-black uppercase tracking-widest text-muted dark:text-muted mb-2">{{ __('Check-in') }}</label>
                                    <input type="date" name="check_in_date" value="{{ old('check_in_date', $checkIn) }}"
                                           class="w-full bg-transparent border-0 p-0 text-muted dark:text-white font-semibold text-sm focus:ring-0"
                                           required>
                                </div>
                                <div class="p-5">
                                    <label class="block text-xs font-black uppercase tracking-widest text-muted dark:text-muted mb-2">{{ __('Check-out') }}</label>
                                    <input type="date" name="check_out_date" value="{{ old('check_out_date', $checkOut) }}"
                                           class="w-full bg-transparent border-0 p-0 text-muted dark:text-white font-semibold text-sm focus:ring-0"
                                           required>
                                </div>
                            </div>
                            <div class="p-5">
                                <label class="block text-xs font-black uppercase tracking-widest text-muted dark:text-muted mb-2">{{ __('Guests') }}</label>
                                <select name="guests"
                                        class="w-full bg-transparent border-0 p-0 text-muted dark:text-white font-semibold text-sm focus:ring-0"
                                        required>
                                    @for($i = 1; $i <= $accommodation->max_guests; $i++)
                                        <option value="{{ $i }}" {{ old('guests', $guests) == $i ? 'selected' : '' }}
                                                class="dark:bg-surface">
                                            {{ $i }} guest{{ $i > 1 ? 's' : '' }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </section>

                    <div class="border-t border-warm dark:border-warm"></div>

                    {{-- ── 2. Cancellation policy ── --}}
                    <section>
                        <h2 class="text-xl font-bold text-muted dark:text-white mb-5 flex items-center gap-3">
                            <span class="flex items-center justify-center w-7 h-7 rounded-md bg-primary-100 dark:bg-primary-900/40 text-primary-600 dark:text-primary-400 text-xs font-black">2</span>
                            Cancellation policy
                        </h2>

                        <div class="rounded-2xl border border-warm dark:border-warm bg-white dark:bg-surface/50 p-6">
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 rounded-full bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <div>
                                    <p class="font-bold text-muted dark:text-white capitalize">{{ $accommodation->cancellation_policy }}</p>
                                    <p class="text-sm text-muted dark:text-muted mt-1 leading-relaxed">
                                        @if($accommodation->cancellation_policy === 'flexible')
                                            Full refund available up to 24 hours before check-in. After that, the first night is non-refundable.
                                        @elseif($accommodation->cancellation_policy === 'moderate')
                                            Full refund available up to 5 days before check-in.
                                        @else
                                            50% refund if cancelled up to 1 week before check-in. No refund after that.
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <div class="border-t border-warm dark:border-warm"></div>

                    {{-- ── 3. Ground rules ── --}}
                    <section>
                        <h2 class="text-xl font-bold text-muted dark:text-white mb-5 flex items-center gap-3">
                            <span class="flex items-center justify-center w-7 h-7 rounded-md bg-primary-100 dark:bg-primary-900/40 text-primary-600 dark:text-primary-400 text-xs font-black">3</span>
                            Ground rules
                        </h2>

                        <p class="text-sm text-muted dark:text-muted mb-5">{{ __('We ask every guest to remember a few simple things about what makes a great guest.') }}</p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="flex gap-4 p-5 rounded-2xl border border-warm dark:border-warm bg-white dark:bg-surface/50">
                                <div class="w-9 h-9 rounded-full bg-surface-subtle dark:bg-surface flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4 text-muted dark:text-dark-subtle" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-muted dark:text-white text-sm">{{ __('Follow the house rules') }}</p>
                                    <p class="text-xs text-muted mt-1">{{ __('Check the listing description for specifics.') }}</p>
                                </div>
                            </div>
                            <div class="flex gap-4 p-5 rounded-2xl border border-warm dark:border-warm bg-white dark:bg-surface/50">
                                <div class="w-9 h-9 rounded-full bg-surface-subtle dark:bg-surface flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4 text-muted dark:text-dark-subtle" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-muted dark:text-white text-sm">{{ __('Treat it like your own') }}</p>
                                    <p class="text-xs text-muted mt-1">{{ __('Leave the place in the condition you found it.') }}</p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <div class="border-t border-warm dark:border-warm"></div>

                    {{-- ── Submit ── --}}
                    <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                        <button type="submit"
                                class="relative group inline-flex items-center justify-center gap-2 px-8 py-4 rounded-xl bg-surface dark:bg-primary-600 text-white font-bold text-base shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all duration-200 overflow-hidden">
                            <span class="relative z-10">{{ __('Confirm and book') }}</span>
                            <svg class="w-4 h-4 relative z-10 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                            <div class="absolute inset-0 bg-gradient-to-r from-primary-600 to-indigo-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        </button>
                        <p class="text-sm text-muted flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            You won't be charged yet
                        </p>
                    </div>

                </form>
            </div>

            {{-- ── RIGHT COLUMN – Price Summary ── --}}
            <div class="lg:col-span-5 order-1 lg:order-2">
                <div class="sticky top-28">
                    <div class="rounded-2xl border border-warm dark:border-warm bg-white dark:bg-surface/50 overflow-hidden shadow-sm">

                        {{-- Property thumbnail --}}
                        <div class="flex gap-4 p-6 border-b border-warm dark:border-warm">
                            <div class="w-24 h-20 rounded-xl overflow-hidden shrink-0">
                                <img src="{{ $accommodation->property->cover_image
                                    ? asset('storage/' . $accommodation->property->cover_image)
                                    : 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&q=80&w=200' }}"
                                     alt="{{ $accommodation->property->title }}"
                                     class="w-full h-full object-cover">
                            </div>
                            <div class="flex flex-col justify-center min-w-0">
                                <p class="text-xs text-muted uppercase font-black tracking-widest truncate">{{ $accommodation->property->type->name ?? 'Entire home' }}</p>
                                <h3 class="font-bold text-muted dark:text-white text-sm leading-snug line-clamp-2 mt-1">{{ $accommodation->property->title }}</h3>
                                <div class="flex items-center gap-1 mt-2">
                                    <svg class="w-3.5 h-3.5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    <span class="text-xs font-bold text-muted dark:text-muted">4.96</span>
                                    <span class="text-xs text-muted">(128)</span>
                                </div>
                            </div>
                        </div>

                        {{-- Price breakdown --}}
                        <div class="p-6 space-y-4">
                            <h3 class="font-bold text-muted dark:text-white">{{ __('Price details') }}</h3>

                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between items-center">
                                    <span class="text-muted dark:text-muted">
                                        {{ $accommodation->property->currency === 'USD' ? '$' : 'Br' }}{{ number_format($accommodation->property->price, 2) }} × {{ $nights }} nights
                                    </span>
                                    <span class="font-medium text-muted dark:text-white">
                                        {{ $accommodation->property->currency === 'USD' ? '$' : 'Br' }}{{ number_format($basePrice, 2) }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-muted dark:text-muted underline decoration-dotted underline-offset-2 cursor-help">{{ __('Cleaning fee') }}</span>
                                    <span class="font-medium text-muted dark:text-white">{{ $accommodation->property->currency === 'USD' ? '$' : 'Br' }}25.00</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-muted dark:text-muted underline decoration-dotted underline-offset-2 cursor-help">{{ __('SRMS service fee') }}</span>
                                    <span class="font-medium text-muted dark:text-white">{{ $accommodation->property->currency === 'USD' ? '$' : 'Br' }}15.00</span>
                                </div>
                            </div>

                            <div class="pt-4 border-t border-warm dark:border-warm flex justify-between items-center">
                                <div>
                                    <p class="font-bold text-muted dark:text-white">{{ __('Total') }}</p>
                                    <p class="text-xs text-muted mt-0.5">{{ __('Taxes included') }}</p>
                                </div>
                                <p class="text-2xl font-extrabold font-display text-muted dark:text-white">
                                    {{ $accommodation->property->currency === 'USD' ? '$' : 'Br' }}{{ number_format($basePrice + 40, 2) }}
                                </p>
                            </div>
                        </div>

                        {{-- Trust badge --}}
                        <div class="px-6 pb-5 flex items-center gap-2 text-xs text-muted">
                            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            Secure booking · No charge until confirmed
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>

</x-layouts.app>
