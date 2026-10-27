<x-layouts.app>
    <x-slot name="title">{{ $accommodation->property->title }}</x-slot>

    {{-- Subtle ambient background --}}
    <div class="fixed inset-0 pointer-events-none overflow-hidden -z-10">
        <div class="absolute top-0 left-1/2 w-full max-w-3xl h-full max-h-3xl bg-primary-400/5 rounded-full blur-[120px] -translate-x-1/2"></div>
    </div>

    <div class="page-container pt-28 pb-24">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-x-16 gap-y-12 items-start">
            
            {{-- ── LEFT COLUMN ── --}}
            <div class="lg:col-span-7 order-2 lg:order-1 space-y-8">
                
                {{-- Image Gallery --}}
                <div class="card relative aspect-[16/9] rounded-2xl overflow-hidden bg-surface-subtle dark:bg-surface">
                    <div class="w-full h-full">
                        <img 
                            src="{{ $accommodation->property->cover_image 
                                ? asset('storage/' . $accommodation->property->cover_image) 
                                : 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&q=80&w=1200' }}" 
                            alt="{{ $accommodation->property->title }}"
                            class="w-full h-full object-cover"
                        >
                    </div>
                    
                    {{-- Gallery Thumbnails --}}
                    @if($accommodation->media->count() > 1)
                        <div class="absolute bottom-4 left-4 right-4 flex justify-center gap-2">
                            @foreach($accommodation->media->take(5) as $index => $media)
                                <button 
                                    class="w-14 h-14 rounded-lg overflow-hidden bg-white/90 dark:bg-surface/90 border-2 border-transparent hover:border-primary-500 transition-all duration-200"
                                    aria-label="{{ 'View image ' . ($index + 1) }}"
                                >
                                    <img 
                                        src="{{ $media->url ?? $media->thumbnail_url }}" 
                                        alt="{{ $accommodation->property->title }} - Image {{ $index + 1 }}"
                                        class="w-full h-full object-cover"
                                    >
                                </button>
                            @endforeach
                        </div>
                    @endif
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

                {{-- ── 1. Your trip ── --}}
                <section class="card rounded-2xl border border-warm dark:border-warm bg-white dark:bg-surface/50 overflow-hidden divide-y divide-warm dark:divide-dark-border">
                    <div class="p-6 border-b border-warm dark:border-warm">
                        <h2 class="text-xl font-bold text-muted dark:text-white mb-5 flex items-center gap-3">
                            <span class="flex items-center justify-center w-7 h-7 rounded-md bg-primary-100 dark:bg-primary-900/40 text-primary-600 dark:text-primary-400 text-xs font-black">1</span>
                            {{ __('Your trip') }}
                        </h2>
                    </div>
                    
                    <form action="{{ route('booking.store', $accommodation) }}" method="POST" class="p-6 space-y-6">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-xs font-black uppercase tracking-widest text-muted dark:text-muted mb-2">{{ __('Check-in') }}</label>
                                <input type="date" name="check_in_date" value="{{ old('check_in_date', $checkIn) }}" class="w-full px-4 py-4 rounded-xl border border-warm dark:border-warm bg-white dark:bg-surface text-muted dark:text-white focus:ring-2 focus:ring-primary-500" required min="{{ now()->format('Y-m-d') }}">
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase tracking-widest text-muted dark:text-muted mb-2">{{ __('Check-out') }}</label>
                                <input type="date" name="check_out_date" value="{{ old('check_out_date', $checkOut) }}" class="w-full px-4 py-4 rounded-xl border border-warm dark:border-warm bg-white dark:bg-surface text-muted dark:text-white focus:ring-2 focus:ring-primary-500" required min="{{ now()->addDays(2)->format('Y-m-d') }}">
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase tracking-widest text-muted dark:text-muted mb-2">{{ __('Guests') }}</label>
                                <select name="guests" class="w-full px-4 py-4 rounded-xl border border-warm dark:border-warm bg-white dark:bg-surface text-muted dark:text-white focus:ring-2 focus:ring-primary-500" required>
                                    @for($i = 1; $i <= $accommodation->max_guests; $i++)
                                        <option value="{{ $i }}" {{ old('guests', $guests) == $i ? 'selected' : '' }} class="dark:bg-surface">
                                            {{ $i }} guest{{ $i > 1 ? 's' : '' }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </form>
                </section>

                <div class="border-t border-warm dark:border-warm"></div>

                {{-- ── Reserve Button ── --}}
                <section class="p-6 border-y border-warm dark:border-warm bg-white dark:bg-surface/50">
                    <div class="text-center">
                        <a href="{{ route('booking.create', $accommodation) }}" class="inline-block py-3 px-6 rounded-xl bg-primary-600 text-white font-semibold shadow-sm hover:bg-primary-700 transition-colors">
                            {{ __('Reserve') }}
                        </a>
                    </div>
                </section>

                {{-- ── 2. Cancellation policy ── --}}
                <section class="p-6">
                    <h2 class="text-xl font-bold text-muted dark:text-white mb-5 flex items-center gap-3">
                        <span class="flex items-center justify-center w-7 h-7 rounded-md bg-primary-100 dark:bg-primary-900/40 text-primary-600 dark:text-primary-400 text-xs font-black">2</span>
                        Cancellation policy
                    </h2>

                    <div class="card rounded-2xl border border-warm dark:border-warm bg-white dark:bg-surface/50 p-6">
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
                <section class="p-6">
                    <h2 class="text-xl font-bold text-muted dark:text-white mb-5 flex items-center gap-3">
                        <span class="flex items-center justify-center w-7 h-7 rounded-md bg-primary-100 dark:bg-primary-900/40 text-primary-600 dark:text-primary-400 text-xs font-black">3</span>
                        Ground rules
                    </h2>

                    <p class="text-sm text-muted dark:text-muted mb-5">{{ __('We ask every guest to remember a few simple things about what makes a great guest.') }}</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="card flex gap-4 p-5 rounded-2xl border border-warm dark:border-warm bg-white dark:bg-surface/50">
                            <div class="w-9 h-9 rounded-full bg-surface-subtle dark:bg-surface flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-muted dark:text-dark-subtle" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            </div>
                            <div>
                                <p class="font-semibold text-muted dark:text-white text-sm">{{ __('Follow the house rules') }}</p>
                                <p class="text-xs text-muted mt-1">{{ __('Check the listing description for specifics.') }}</p>
                            </div>
                        </div>
                        <div class="card flex gap-4 p-5 rounded-2xl border border-warm dark:border-warm bg-white dark:bg-surface/50">
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
            </section>

            {{-- ── RIGHT COLUMN – Price Summary ── --}}
            <div class="lg:col-span-5 order-1 lg:order-2">
                <div class="sticky top-28">
                    <div class="card rounded-2xl border border-warm dark:border-warm bg-white dark:bg-surface/50 overflow-hidden">
                        
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
                                    <svg class="w-3.5 h-3.5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    <span class="text-xs font-bold text-muted dark:text-muted">4.96</span>
                                    <span class="text-xs text-muted">(128)</span>
                                </div>
                        </div>

                        {{-- Price breakdown --}}
                        <div class="p-6 space-y-4">
                            <h3 class="font-bold text-muted dark:text-white">{{ __('Price details') }}</h3>

                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between items-center">
                                    <span class="text-muted dark:text-muted">
                                        {{ $accommodation->listing->currency === 'USD' ? '$' : 'Br' }}{{ number_format($accommodation->listing->price, 2) }} × {{ $nights }} nights
                                    </span>
                                    <span class="font-medium text-muted dark:text-white">
                                        {{ $accommodation->listing->currency === 'USD' ? '$' : 'Br' }}{{ number_format($basePrice, 2) }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-muted dark:text-muted underline decoration-dotted underline-offset-2 cursor-help">{{ __('Cleaning fee') }}</span>
                                    <span class="font-medium text-muted dark:text-white">{{ $accommodation->listing->currency === 'USD' ? '$' : 'Br' }}25.00</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-muted dark:text-muted underline decoration-dotted underline-offset-2 cursor-help">{{ __('SRMS service fee') }}</span>
                                    <span class="font-medium text-muted dark:text-white">{{ $accommodation->listing->currency === 'USD' ? '$' : 'Br' }}15.00</span>
                                </div>
                            </div>

                            <div class="pt-4 border-t border-warm dark:border-warm flex justify-between items-center">
                                <div>
                                    <p class="font-bold text-muted dark:text-white">{{ __('Total') }}</p>
                                    <p class="text-xs text-muted mt-0.5">{{ __('Taxes included') }}</p>
                                </div>
                                <p class="text-2xl font-extrabold font-display text-muted dark:text-white">
                                    {{ $accommodation->listing->currency === 'USD' ? '$' : 'Br' }}{{ number_format($basePrice + 40, 2) }}
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