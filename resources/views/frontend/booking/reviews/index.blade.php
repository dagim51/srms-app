<x-layouts.app>
    <x-slot name="title">{{ __('My Reviews - SRMS') }}</x-slot>

    <div class="page-container pt-32 pb-24 max-w-7xl page-enter">
        <x-page-header
            title="My Reviews"
            description="Manage reviews you've written for your completed stays and accommodations."
            icon="sparkles"
            badge="Reviews" />

        @if(session('success'))
            <div class="mb-8 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 text-emerald-800 dark:text-emerald-300 font-medium flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ session('success') }}
            </div>
        @endif

        <div class="space-y-6">
            @forelse($reviews as $review)
                <div class="card bg-white dark:bg-[#1E293B] rounded-2xl border border-warm dark:border-warm p-6 flex flex-col sm:flex-row gap-6">
                    <div class="sm:w-1/4 shrink-0 border-r border-warm dark:border-warm pr-6">
                        @if($review->reviewable_type === \App\Models\Accommodation::class && $review->reviewable)
                            <div class="aspect-[4/3] rounded-xl overflow-hidden mb-3">
                                <img src="{{ $review->reviewable->property->cover_image ? asset('storage/' . $review->reviewable->property->cover_image) : 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&q=80&w=200' }}" class="w-full h-full object-cover">
                            </div>
                            <h3 class="font-bold text-muted dark:text-white line-clamp-2 text-sm">{{ $review->reviewable->property->title }}</h3>
                        @endif
                        <p class="text-xs text-muted mt-2">{{ $review->created_at->format('M d, Y') }}</p>
                    </div>
                    
                    <div class="sm:w-3/4">
                        <div class="flex items-center gap-1 mb-3">
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="w-5 h-5 {{ $i <= $review->rating ? 'text-amber-400' : 'text-muted dark:text-muted' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                            @endfor
                        </div>
                        <p class="text-muted dark:text-dark-subtle leading-relaxed">{{ $review->comment }}</p>
                    </div>
                </div>
            @empty
                <div class="card text-center py-20 bg-surface dark:bg-surface/20 rounded-3xl border border-dashed border-warm dark:border-warm">
                    <svg class="w-16 h-16 mx-auto text-muted dark:text-muted mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" /></svg>
                    <h3 class="text-lg font-bold text-muted dark:text-white">{{ __('No reviews yet') }}</h3>
                    <p class="text-muted dark:text-muted mt-1 max-w-sm mx-auto">{{ __('Once you complete a stay and write a review, it will appear here.') }}</p>
                </div>
            @endforelse

            <div class="mt-8">
                {{ $reviews->links() }}
            </div>
        </div>
    </div>
</x-layouts.app>
