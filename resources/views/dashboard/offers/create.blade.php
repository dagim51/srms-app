<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Make an Offer') }}</x-slot>
    <x-slot name="breadcrumb">
        <a href="{{ route('dashboard') }}" class="hover:text-primary-600 transition-colors">{{ __('Dashboard') }}</a>
        <span>/</span>
        <a href="{{ route('dashboard.offers.index') }}" class="hover:text-primary-600 transition-colors">{{ __('Offers') }}</a>
        <span>/</span>
        <span class="text-muted dark:text-white font-medium">{{ __('New') }}</span>
    </x-slot>

    <div class="max-w-3xl mx-auto page-enter space-y-6">
        <x-page-header
            title="Make an Offer"
            description="Submit formal purchase bids, lease propositions, counter-offers, and earnest money deposit terms."
            icon="document"
            badge="Offers" />

        <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-[#273244] rounded-2xl shadow-sm overflow-hidden p-6 sm:p-8">
            
            @if($listing)
                <div class="flex items-start gap-4 p-4 rounded-xl border border-warm dark:border-warm bg-surface/50 dark:bg-surface/30 mb-8">
                    @php $primaryImg = $listing->property->media->first() ? ($listing->property->media->first()->url ?? $listing->property->media->first()->storage_key) : 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=300&q=80'; @endphp
                    <div class="w-20 h-20 rounded-lg overflow-hidden flex-shrink-0">
                        <img src="{{ $primaryImg }}" alt="{{ $listing->property->title }}" class="w-full h-full object-cover">
                    </div>
                    <div class="flex-1">
                        <h3 class="font-bold text-muted dark:text-white text-base">{{ $listing->property->title }}</h3>
                        <p class="text-sm text-muted dark:text-muted mt-1">Asking Price: <span class="font-bold text-muted dark:text-white">{{ $listing->formatted_price }}</span></p>
                        <p class="text-xs text-primary-600 dark:text-primary-400 mt-1 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.242-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            {{ $listing->property->location_id ? 'Addis Ababa' : 'Location details available' }}
                        </p>
                    </div>
                </div>
            @else
                <div class="mb-6 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-sm">
                    Please select a property from the marketplace to make an offer.
                </div>
            @endif

            <form action="{{ route('dashboard.offers.store') }}" method="POST" class="space-y-6">
                @csrf
                <input type="hidden" name="listing_id" value="{{ $listing?->id }}">

                <div>
                    <label class="block text-sm font-semibold text-muted dark:text-dark-subtle mb-2">Your Offer Amount ({{ $listing?->currency ?? 'ETB' }}) <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-muted font-bold">
                            {{ $listing?->currency === 'USD' ? '$' : 'Br' }}
                        </span>
                        <input type="number" name="amount" required min="1" step="0.01" value="{{ old('amount') }}" class="form-input w-full rounded-xl pl-12 text-lg font-bold">
                    </div>
                    @error('amount') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-muted dark:text-dark-subtle mb-2">Additional Terms / Conditions (Optional)</label>
                    <textarea name="notes" rows="4" class="form-input w-full rounded-xl resize-none" placeholder="{{ __('E.g., Contingent on inspection, financing, etc.') }}">{{ old('notes') }}</textarea>
                    @error('notes') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="pt-4 flex items-center gap-4 justify-end border-t border-warm dark:border-warm">
                    <a href="{{ url()->previous() }}" class="btn btn-ghost font-bold">Cancel</a>
                    <button type="submit" class="btn btn-primary font-bold px-8 shadow-md" {{ !$listing ? 'disabled' : '' }}>{{ __('Submit Offer') }}</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.dashboard>
