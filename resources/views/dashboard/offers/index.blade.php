<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Offers & Negotiations') }}</x-slot>
    <x-slot name="breadcrumb">
        <a href="{{ route('dashboard') }}" class="hover:text-primary-600 transition-colors">{{ __('Dashboard') }}</a>
        <span>/</span>
        <span class="text-muted dark:text-white font-medium">{{ __('Offers') }}</span>
    </x-slot>

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold font-display text-muted dark:text-white">{{ __('Active Offers') }}</h2>
        </div>

        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 text-emerald-800 text-sm font-medium border border-emerald-200">
                {{ session('success') }}
            </div>
        @endif

        @if($offers->isEmpty())
            <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-[#273244] rounded-2xl shadow-sm overflow-hidden p-12 text-center">
                <div class="w-16 h-16 bg-surface dark:bg-surface rounded-full flex items-center justify-center mx-auto mb-4 text-primary-500">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <h3 class="text-lg font-bold text-muted dark:text-white mb-2">{{ __('No active offers') }}</h3>
                <p class="text-muted max-w-sm mx-auto mb-6 text-sm">{{ __("You haven't made or received any offers yet. Explore the marketplace to find your dream property.") }}</p>
                <a href="{{ route('properties.index') }}" class="btn btn-primary shadow-md">{{ __('Explore Properties') }}</a>
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                @foreach($offers as $offer)
                    @php
                        $isOwner = $offer->listing->owner_actor_id === auth()->id();
                        $statusColors = [
                            'pending' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300',
                            'accepted' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300',
                            'countered' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300',
                            'rejected' => 'bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-300',
                        ];
                        
                        // Find the latest active offer in the chain to display the current status
                        $current = $offer;
                        while($current->counterOffers->count() > 0) {
                            $current = $current->counterOffers->sortByDesc('created_at')->first();
                        }
                        
                        $badgeColor = $statusColors[$current->status] ?? $statusColors['pending'];
                        $isActionRequired = ($isOwner && $current->buyer_id !== auth()->id() && $current->status === 'pending') || 
                                            (!$isOwner && $current->buyer_id === auth()->id() && $current->status === 'pending');
                    @endphp
                    
                    <a href="{{ route('dashboard.offers.show', $offer) }}" class="block bg-white dark:bg-[#1E293B] border border-warm dark:border-[#273244] rounded-2xl shadow-sm hover:shadow-md transition-all group overflow-hidden {{ $isActionRequired ? 'ring-2 ring-primary-500' : '' }}">
                        <div class="p-5">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center gap-2">
                                    <span class="inline-block px-2.5 py-1 rounded-md text-xs font-bold uppercase tracking-wider {{ $badgeColor }}">
                                        {{ __($current->status) }}
                                    </span>
                                    @if($isActionRequired)
                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-primary-500"></span>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <div class="text-xs text-muted">{{ $current->created_at->diffForHumans() }}</div>
                                </div>
                            </div>

                            <div class="flex items-center gap-4 mb-4">
                                @php $primaryImg = $offer->listing->property->media->first() ? ($offer->listing->property->media->first()->url ?? $offer->listing->property->media->first()->storage_key) : 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=300&q=80'; @endphp
                                <div class="w-16 h-16 rounded-lg overflow-hidden flex-shrink-0 border border-warm dark:border-warm">
                                    <img src="{{ $primaryImg }}" alt="{{ $offer->listing->property->title }}" class="w-full h-full object-cover">
                                </div>
                                <div>
                                    <h3 class="font-bold text-muted dark:text-white text-base group-hover:text-primary-600 transition-colors line-clamp-1">
                                        {{ $offer->listing->property->title ?? __('Property Details') }}
                                    </h3>
                                    <p class="text-xs text-muted dark:text-muted mt-0.5">{{ __('Asking') }}: {{ $offer->listing->formatted_price }}</p>
                                </div>
                            </div>

                            <div class="bg-surface dark:bg-surface/50 p-4 rounded-xl flex items-center justify-between border border-warm dark:border-warm mb-4">
                                <div>
                                    <p class="text-xs font-bold text-muted uppercase tracking-wider mb-1">{{ __('Current Offer') }}</p>
                                    <p class="text-lg font-bold text-muted dark:text-white">{{ number_format($current->amount) }} {{ $offer->listing->currency }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs font-bold text-muted uppercase tracking-wider mb-1">{{ $isOwner ? __('From Buyer') : __('To Seller') }}</p>
                                    <p class="text-sm font-medium text-muted dark:text-dark-subtle">
                                        {{ $isOwner ? ($offer->buyer->name ?? __('User')) : __('Listing Agent') }}
                                    </p>
                                </div>
                            </div>
                            
                            <div class="pt-4 border-t border-warm dark:border-warm flex items-center justify-between text-xs">
                                <span class="text-muted font-medium">
                                    {{ __('Negotiation steps') }}: <span class="font-bold text-muted dark:text-white">1</span>
                                </span>
                                
                                <span class="text-primary-600 font-bold group-hover:underline flex items-center gap-1">
                                    {{ __('View Details') }}
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                </span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.dashboard>
