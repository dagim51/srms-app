<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Reserve Property') }}</x-slot>
    <x-slot name="breadcrumb">
        <a href="{{ route('dashboard') }}" class="hover:text-primary-600 transition-colors">{{ __('Dashboard') }}</a>
        <span>/</span>
        <a href="{{ route('dashboard.reservations.index') }}" class="hover:text-primary-600 transition-colors">{{ __('Reservations') }}</a>
        <span>/</span>
        <span class="text-muted dark:text-white font-medium">{{ __('New') }}</span>
    </x-slot>

    <div class="max-w-3xl mx-auto page-enter space-y-6">
        <x-page-header
            title="Reserve Property"
            description="Submit formal reservation requests, schedule holding deposit transfers, and secure property allocations."
            icon="key"
            badge="Reservations" />

        <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-[#273244] rounded-2xl shadow-sm overflow-hidden p-6 sm:p-8">
            
            @if(session('error'))
                <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-medium">
                    {{ session('error') }}
                </div>
            @endif

            @if($listing)
                <div class="flex flex-col md:flex-row items-start gap-6 p-5 rounded-xl border border-warm dark:border-warm bg-surface/50 dark:bg-surface/30 mb-8">
                    @php $primaryImg = $listing->property->media->first() ? ($listing->property->media->first()->url ?? $listing->property->media->first()->storage_key) : 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=300&q=80'; @endphp
                    <div class="w-full md:w-32 h-32 rounded-lg overflow-hidden flex-shrink-0">
                        <img src="{{ $primaryImg }}" alt="{{ $listing->property->title }}" class="w-full h-full object-cover">
                    </div>
                    <div class="flex-1 w-full">
                        <h3 class="font-bold text-muted dark:text-white text-lg mb-1">{{ $listing->property->title }}</h3>
                        <p class="text-sm text-muted dark:text-muted mb-3">{{ $listing->formatted_price }} • {{ $listing->purpose }}</p>
                        
                        <div class="grid grid-cols-2 gap-4 text-xs">
                            <div class="p-3 bg-white dark:bg-[#1E293B] rounded-lg border border-warm dark:border-warm">
                                <span class="block text-muted uppercase tracking-wider font-bold mb-1">{{ __('Reservation Fee') }}</span>
                                <span class="font-bold text-muted dark:text-white text-sm">
                                    {{ $listing->currency === 'USD' ? '$' : 'Br' }}{{ number_format($listing->price * 0.05, 2) }}
                                </span>
                            </div>
                            <div class="p-3 bg-white dark:bg-[#1E293B] rounded-lg border border-warm dark:border-warm">
                                <span class="block text-muted uppercase tracking-wider font-bold mb-1">{{ __('Hold Duration') }}</span>
                                <span class="font-bold text-muted dark:text-white text-sm">{{ __('48 Hours') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="prose prose-sm dark:prose-invert max-w-none mb-8 text-muted dark:text-muted">
                    <h4>{{ __('Reservation Terms') }}</h4>
                    <ul>
                        <li>By reserving this property, it will be temporarily taken off the market for 48 hours.</li>
                        <li>You must complete the reservation fee payment within this time frame.</li>
                        <li>The reservation fee is typically 5% of the property value and is fully refundable if the deal does not proceed after physical inspection.</li>
                        <li>If payment is not received within 48 hours, the reservation will automatically expire and the property will become available to others.</li>
                    </ul>
                </div>

                <form action="{{ route('dashboard.reservations.store') }}" method="POST" class="space-y-6">
                    @csrf
                    <input type="hidden" name="listing_id" value="{{ $listing->id }}">

                    <div>
                        <label class="block text-sm font-semibold text-muted dark:text-dark-subtle mb-2">Additional Notes (Optional)</label>
                        <textarea name="notes" rows="3" class="form-input w-full rounded-xl resize-none" placeholder="{{ __('Any specific requirements or questions?') }}">{{ old('notes') }}</textarea>
                        @error('notes') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="pt-4 flex items-center gap-4 justify-end border-t border-warm dark:border-warm">
                        <a href="{{ url()->previous() }}" class="btn btn-ghost font-bold">Cancel</a>
                        <button type="submit" class="btn btn-primary font-bold px-8 shadow-md">{{ __('Confirm Reservation Request') }}</button>
                    </div>
                </form>
            @else
                <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-sm">
                    Please select a property from the marketplace to reserve it.
                </div>
            @endif
        </div>
    </div>
</x-layouts.dashboard>
