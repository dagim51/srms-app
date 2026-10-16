<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Offer Details') }}</x-slot>
    <x-slot name="breadcrumb">
        <a href="{{ route('dashboard') }}" class="hover:text-primary-600 transition-colors">{{ __('Dashboard') }}</a>
        <span>/</span>
        <a href="{{ route('dashboard.offers.index') }}" class="hover:text-primary-600 transition-colors">{{ __('Offers') }}</a>
        <span>/</span>
        <span class="text-muted dark:text-white font-medium">{{ __('Details') }}</span>
    </x-slot>

    @php
        $statusColors = [
            'pending' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300',
            'accepted' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300',
            'countered' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300',
            'rejected' => 'bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-300',
        ];
        
        $currentStatus = $latestOffer->status;
        $badgeColor = $statusColors[$currentStatus] ?? $statusColors['pending'];
        
        $canAct = $currentStatus === 'pending';
        
        // Who made the latest offer?
        // If it's the root offer, the buyer made it.
        // If it's a counter offer, we alternate based on negotiation logic. 
        // For simplicity: if the current user is the buyer, and the latest offer was made by them, they can't accept/reject it.
        // Wait, our backend logic said the latest offer's 'buyer_id' is always the original buyer. 
        // So we determine who made it by the length of history. Even index = Buyer, Odd index = Seller.
        $historyCount = $history->count();
        $latestMadeByBuyer = ($historyCount % 2) !== 0; // 1st offer (root) = Buyer, 2nd (counter) = Seller, 3rd = Buyer, etc.
        
        $isMyTurnToAct = false;
        if ($canAct) {
            if ($isBuyer && !$latestMadeByBuyer) {
                $isMyTurnToAct = true;
            } elseif ($isOwner && $latestMadeByBuyer) {
                $isMyTurnToAct = true;
            }
        }
    @endphp

    <div class="max-w-5xl mx-auto space-y-6">
        
        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 text-emerald-800 text-sm font-medium border border-emerald-200 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                    {{ session('success') }}
                </div>
            </div>
        @endif
        
        @error('amount')
            <div class="p-4 rounded-xl bg-rose-50 text-rose-800 text-sm font-medium border border-rose-200">
                {{ $message }}
            </div>
        @enderror

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            {{-- Left Column: Current Status and Action --}}
            <div class="lg:col-span-2 space-y-6">
                
                {{-- Current Status Card --}}
                <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-[#273244] rounded-2xl shadow-sm overflow-hidden">
                    <div class="p-6 sm:p-8">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-xl font-bold font-display text-muted dark:text-white">{{ __('Current Offer Status') }}</h2>
                            <span class="inline-block px-3 py-1 rounded-md text-xs font-bold uppercase tracking-wider {{ $badgeColor }}">
                                {{ $currentStatus }}
                            </span>
                        </div>
                        
                        <div class="bg-surface dark:bg-surface/50 p-6 rounded-2xl border border-warm dark:border-warm flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
                            <div>
                                <p class="text-sm font-bold text-muted uppercase tracking-wider mb-2">{{ __('Offer Amount') }}</p>
                                <p class="text-3xl font-display font-extrabold text-muted dark:text-white">
                                    {{ number_format($latestOffer->amount) }} <span class="text-xl text-muted">{{ $rootOffer->listing->currency }}</span>
                                </p>
                            </div>
                            
                            <div class="hidden md:block w-px h-16 bg-surface-subtle dark:bg-surface"></div>
                            
                            <div class="flex flex-col gap-1">
                                <p class="text-xs text-muted">
                                    <span class="font-bold text-muted dark:text-white">Submitted by:</span> 
                                    {{ $latestMadeByBuyer ? ($rootOffer->buyer->name ?? 'Buyer') : 'Seller' }}
                                </p>
                                <p class="text-xs text-muted">
                                    <span class="font-bold text-muted dark:text-white">Time:</span> 
                                    {{ $latestOffer->created_at->format('M d, Y h:i A') }}
                                </p>
                                @if($currentStatus === 'pending')
                                    <p class="text-xs text-rose-500 mt-1 font-medium">{{ __('Expires in 48 hours') }}</p>
                                @endif
                            </div>
                        </div>

                        {{-- Action Area --}}
                        @if($canAct)
                            @if($isMyTurnToAct)
                                <div class="space-y-4" x-data="{ showCounter: false }">
                                    <h3 class="font-bold text-muted dark:text-white mb-2">{{ __('Your Turn to Respond') }}</h3>
                                    
                                    <div class="flex flex-col sm:flex-row gap-3">
                                        <form action="{{ route('dashboard.offers.update', $latestOffer) }}" method="POST" class="flex-1">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="action" value="accept">
                                            <button type="submit" class="btn btn-primary w-full shadow-md py-3 text-sm" onclick="return confirm('Are you sure you want to accept this offer?');">{{ __('Accept Offer') }}</button>
                                        </form>
                                        
                                        <button @click="showCounter = !showCounter" type="button" class="btn btn-outline flex-1 py-3 text-sm bg-surface dark:bg-surface">{{ __('Counter Offer') }}</button>
                                        
                                        <form action="{{ route('dashboard.offers.update', $latestOffer) }}" method="POST" class="flex-1">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="action" value="reject">
                                            <button type="submit" class="btn btn-outline w-full py-3 text-sm text-rose-600 border-rose-200 hover:bg-rose-50 dark:hover:bg-rose-900/20" onclick="return confirm('Are you sure you want to reject this offer permanently?');">{{ __('Reject Offer') }}</button>
                                        </form>
                                    </div>
                                    
                                    {{-- Counter Offer Form --}}
                                    <div x-show="showCounter" x-cloak class="mt-4 p-6 rounded-2xl bg-surface dark:bg-surface/30 border border-warm dark:border-warm">
                                        <h4 class="font-bold text-muted dark:text-white mb-4">{{ __('Make a Counteroffer') }}</h4>
                                        <form action="{{ route('dashboard.offers.update', $latestOffer) }}" method="POST" class="space-y-4">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="action" value="counter">
                                            
                                            <div>
                                                <label class="block text-xs font-bold text-muted dark:text-dark-subtle mb-1">Counter Amount ({{ $rootOffer->listing->currency }})</label>
                                                <div class="relative">
                                                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-muted font-bold">
                                                        {{ $rootOffer->listing->currency === 'USD' ? '$' : 'Br' }}
                                                    </span>
                                                    <input type="number" name="counter_amount" required min="1" step="0.01" class="form-input w-full rounded-xl pl-12 text-base font-bold">
                                                </div>
                                            </div>
                                            
                                            <div>
                                                <label class="block text-xs font-bold text-muted dark:text-dark-subtle mb-1">{{ __('Notes / Terms') }}</label>
                                                <textarea name="notes" rows="2" class="form-input w-full rounded-xl resize-none text-sm"></textarea>
                                            </div>
                                            
                                            <button type="submit" class="btn btn-primary w-full text-sm">{{ __('Send Counteroffer') }}</button>
                                        </form>
                                    </div>
                                </div>
                            @else
                                <div class="p-4 rounded-xl bg-blue-50 dark:bg-blue-900/20 text-blue-800 dark:text-blue-300 text-sm border border-blue-200 dark:border-blue-800 flex items-center gap-3">
                                    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    Waiting for the {{ $latestMadeByBuyer ? 'Seller' : 'Buyer' }} to review and respond to your offer.
                                </div>
                            @endif
                        @elseif($currentStatus === 'accepted')
                            <div class="p-6 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-center">
                                <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-800/50 rounded-full flex items-center justify-center mx-auto mb-3 text-emerald-600 dark:text-emerald-400">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                                </div>
                                <h3 class="text-lg font-bold text-emerald-900 dark:text-emerald-100 mb-1">Offer Accepted!</h3>
                                <p class="text-sm text-emerald-700 dark:text-emerald-300">{{ __('The negotiation was successful. Proceed with the next steps to finalize the deal.') }}</p>
                                <a href="#" class="btn btn-primary btn-sm mt-4">{{ __('Proceed to Documents') }}</a>
                            </div>
                        @else
                            <div class="p-4 rounded-xl bg-surface dark:bg-surface text-muted dark:text-dark-subtle text-sm border border-warm dark:border-warm text-center font-medium">
                                This offer has been closed ({{ $currentStatus }}).
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Negotiation Timeline --}}
                <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-[#273244] rounded-2xl shadow-sm overflow-hidden p-6 sm:p-8">
                    <h2 class="text-xl font-bold font-display text-muted dark:text-white mb-6">{{ __('Negotiation Timeline') }}</h2>
                    
                    <div class="relative border-l-2 border-warm dark:border-warm ml-4 space-y-8">
                        @foreach($history->reverse() as $index => $historyOffer)
                            @php
                                $offerIndex = $history->count() - $index;
                                $isBuyerOffer = ($offerIndex % 2) !== 0; // 1, 3, 5 = buyer. 2, 4 = seller.
                            @endphp
                            <div class="relative pl-6">
                                {{-- Timeline dot --}}
                                <div class="absolute -left-[9px] top-1.5 w-4 h-4 rounded-full border-2 border-white dark:border-[#1E293B] {{ $index === 0 ? 'bg-primary-500' : 'bg-surface-subtle dark:bg-surface-subtle' }}"></div>
                                
                                <div class="bg-surface/50 dark:bg-surface/30 border border-warm dark:border-warm p-4 rounded-xl">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-bold text-muted dark:text-white">
                                            {{ number_format($historyOffer->amount) }} {{ $rootOffer->listing->currency }}
                                        </span>
                                        <span class="text-xs font-bold text-muted uppercase">
                                            Offer #{{ $offerIndex }}
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between text-xs text-muted">
                                        <span>From: <span class="font-medium text-muted dark:text-dark-subtle">{{ $isBuyerOffer ? 'Buyer' : 'Seller' }}</span></span>
                                        <span>{{ $historyOffer->created_at->format('M d, g:i A') }}</span>
                                    </div>
                                    @if($historyOffer->notes)
                                        <div class="mt-3 pt-3 border-t border-warm dark:border-warm text-sm text-muted dark:text-muted italic">
                                            "{{ $historyOffer->notes }}"
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>
            
            {{-- Right Column: Property & Details --}}
            <div class="space-y-6">
                <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-[#273244] rounded-2xl shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-warm dark:border-warm">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-muted mb-4">{{ __('Property Details') }}</h3>
                        
                        @if($rootOffer->listing && $rootOffer->listing->property)
                            @php $primaryImg = $rootOffer->listing->property->media->first() ? ($rootOffer->listing->property->media->first()->url ?? $rootOffer->listing->property->media->first()->storage_key) : 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=300&q=80'; @endphp
                            <div class="w-full h-40 rounded-xl overflow-hidden mb-4 border border-warm dark:border-warm">
                                <img src="{{ $primaryImg }}" alt="{{ $rootOffer->listing->property->title }}" class="w-full h-full object-cover">
                            </div>
                            
                            <h4 class="font-bold text-muted dark:text-white text-lg leading-tight mb-2">{{ $rootOffer->listing->property->title }}</h4>
                            
                            <div class="flex justify-between items-center text-sm py-2 border-b border-warm dark:border-warm">
                                <span class="text-muted">{{ __('Asking Price') }}</span>
                                <span class="font-bold text-muted dark:text-white">{{ $rootOffer->listing->formatted_price }}</span>
                            </div>
                            <div class="flex justify-between items-center text-sm py-2 border-b border-warm dark:border-warm">
                                <span class="text-muted">{{ __('Listing ID') }}</span>
                                <span class="font-medium text-muted dark:text-white">{{ $rootOffer->listing->property->property_code ?? substr($rootOffer->listing->id, 0, 8) }}</span>
                            </div>
                            
                            <a href="{{ route('properties.show', $rootOffer->listing->property->id) }}" class="btn btn-outline btn-sm w-full mt-4">
                                View Full Listing
                            </a>
                        @endif
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</x-layouts.dashboard>
