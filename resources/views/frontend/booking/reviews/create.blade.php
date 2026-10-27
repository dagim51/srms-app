<x-layouts.app>
    <x-slot name="title">{{ __('Write a Review') }} - {{ $booking->accommodation->property->title }}</x-slot>

    <div class="page-container pt-28 pb-24 page-enter">
        <div class="max-w-3xl mx-auto">
            
            <div class="mb-4">
                <a href="{{ route('booking.show', $booking) }}" class="inline-flex items-center gap-2 text-muted hover:text-muted dark:text-muted dark:hover:text-muted text-sm transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    {{ __('Back to booking') }}
                </a>
            </div>

            <x-page-header
                :title="__('Write a review for') . ' ' . $booking->accommodation->property->title"
                description="Help future guests by sharing your verified stay experience and rating."
                icon="sparkles"
                badge="Review" />

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

            <form action="{{ route('booking.review.store', $booking) }}" method="POST" class="space-y-8">
                @csrf

                {{-- Rating --}}
                <section class="card rounded-2xl border border-warm dark:border-warm bg-white dark:bg-surface/50 p-6" x-data="{ rating: 5, hoverRating: 0 }">
                    <h2 class="text-xl font-bold text-muted dark:text-white mb-6 flex items-center gap-3">
                        <span class="flex items-center justify-center w-7 h-7 rounded-md bg-primary-100 dark:bg-primary-900/40 text-primary-600 dark:text-primary-400 text-xs font-black">1</span>
                        {{ __('Your rating') }}
                    </h2>
                    
                    <div class="flex gap-3">
                        @for($i = 1; $i <= 5; $i++)
                            <button type="button" 
                                    class="star-btn relative w-12 h-12 rounded-xl border-2 border-warm dark:border-warm bg-white dark:bg-surface flex items-center justify-center transition-all duration-200"
                                    :class="(hoverRating || rating) >= {{ $i }} ? 'border-amber-400 text-amber-400 shadow-md shadow-amber-500/10' : 'text-slate-400 hover:border-slate-500'"
                                    @click="rating = {{ $i }}; document.getElementById('rating-input').value = {{ $i }}"
                                    @mouseenter="hoverRating = {{ $i }}"
                                    @mouseleave="hoverRating = 0">
                                <svg class="w-7 h-7 transition-colors duration-150" :class="(hoverRating || rating) >= {{ $i }} ? 'text-amber-400' : 'text-slate-600'" fill="currentColor" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                            </button>
                        @endfor
                    </div>
                    
                    <p class="text-sm text-muted dark:text-muted mt-4 text-center">
                        <span x-text="rating === 5 ? '⭐⭐⭐⭐⭐ Exceptional (5/5)' : (rating === 4 ? '⭐⭐⭐⭐ Very Good (4/5)' : (rating === 3 ? '⭐⭐⭐ Average (3/5)' : (rating === 2 ? '⭐⭐ Poor (2/5)' : '⭐ Terrible (1/5)')))"></span>
                    </p>
                    
                    <input type="hidden" name="rating" id="rating-input" :value="rating" value="5" required>
                </section>

                {{-- Comment --}}
                <section class="card rounded-2xl border border-warm dark:border-warm bg-white dark:bg-surface/50 p-6">
                    <label for="comment" class="block text-sm font-semibold text-muted dark:text-white mb-3">{{ __('Your review') }}</label>
                    <textarea name="comment" id="comment" rows="6" class="w-full px-4 py-4 rounded-xl border border-warm dark:border-warm bg-white dark:bg-surface text-muted dark:text-white focus:ring-2 focus:ring-primary-500 resize-none" placeholder="{{ __('Tell future guests about your experience...') }}" required></textarea>
                    <p class="text-sm text-muted dark:text-muted mt-2">{{ __('Be honest, helpful, and specific. Mention what you loved and what could be improved.') }}</p>
                    
                    @error('comment')
                        <p class="text-sm text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </section>

                {{-- Submit --}}
                <div class="flex gap-4">
                    <a href="{{ route('booking.show', $booking) }}" class="flex-1 py-4 px-8 rounded-xl border border-warm dark:border-warm text-muted dark:text-dark-subtle font-semibold text-center hover:bg-surface dark:hover:bg-surface transition-colors">
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit" class="relative overflow-hidden group flex-1 py-4 px-8 rounded-xl bg-primary-600 text-white font-bold text-lg shadow-xl hover:shadow-2xl hover:scale-[1.02] transition-all duration-200 overflow-hidden">
                        <span class="relative z-10">{{ __('Submit review') }}</span>
                        <div class="absolute inset-0 bg-gradient-to-r from-primary-600 to-indigo-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>