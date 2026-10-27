<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Offers & Negotiations') }}</x-slot>
    <x-slot:breadcrumb>
        <x-breadcrumb :items="[
            ['label' => __('Dashboard'), 'url' => route('dashboard')],
            ['label' => __('Offers')]
        ]" />
    </x-slot:breadcrumb>

    <div class="space-y-6">
        <x-page-header
            title="Offers & Negotiations"
            description="Track formal purchase bids, lease offers, counter-negotiations, and contractual deposit milestones."
            icon="document"
            badge="Negotiations" />

        {{-- Top KPI Stat Cards --}}
        @php
            $baseOffers = \App\Models\Offer::where(fn($q) => $q->where('buyer_id', auth()->id())->orWhereHas('listing', fn($lq) => $lq->where('owner_actor_id', auth()->id())))->whereNull('parent_offer_id');
            $totalOffers = (clone $baseOffers)->count();
            $pendingOffers = (clone $baseOffers)->where('status', 'pending')->count();
            $acceptedOffers = (clone $baseOffers)->where('status', 'accepted')->count();
            $counteredOffers = (clone $baseOffers)->where('status', 'countered')->count();
        @endphp
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="cinematic-fade-up cinematic-stagger-1">
                <x-card-stat
                    :label="__('Total Offers')"
                    :value="$totalOffers"
                    iconColor="orange"
                    icon="banknotes"
                    :badge="__('Pipeline')"
                    badgeVariant="brand"
                    caption="{{ __('Active bids & offers') }}"
                />
            </div>
            <div class="cinematic-fade-up cinematic-stagger-2">
                <x-card-stat
                    :label="__('Pending Decision')"
                    :value="$pendingOffers"
                    iconColor="amber"
                    icon="clock"
                    :badge="__('Action Req')"
                    badgeVariant="warning"
                    caption="{{ __('Awaiting seller response') }}"
                />
            </div>
            <div class="cinematic-fade-up cinematic-stagger-3">
                <x-card-stat
                    :label="__('Accepted Bids')"
                    :value="$acceptedOffers"
                    iconColor="emerald"
                    icon="check"
                    :badge="__('Agreed')"
                    badgeVariant="success"
                    caption="{{ __('Proceeding to escrow') }}"
                />
            </div>
            <div class="cinematic-fade-up cinematic-stagger-4">
                <x-card-stat
                    :label="__('Counter-Offers')"
                    :value="$counteredOffers"
                    iconColor="blue"
                    icon="arrow-trending-up"
                    :badge="__('Countered')"
                    badgeVariant="info"
                    caption="{{ __('Negotiation active') }}"
                />
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 text-emerald-800 text-sm font-medium border border-emerald-200">
                {{ session('success') }}
            </div>
        @endif

        {{-- Actions & Filter Toolbar --}}
        <div class="card cinematic-fade-up cinematic-stagger-2 bg-white dark:bg-[#1E293B] rounded-xl p-4 border border-warm dark:border-warm space-y-4 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-muted dark:text-white">{{ __('Bids & Acquisition Offers') }}</h2>
                    <p class="text-xs text-muted dark:text-muted">{{ __('Purchase negotiations, escrow proposals, and counter-offers') }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('properties.index') }}" class="btn btn-primary btn-sm flex items-center gap-1.5 shadow-sm shadow-orange-500/20">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        {{ __('Make New Offer') }}
                    </a>
                </div>
            </div>

            {{-- Filter Form --}}
            <form method="GET" action="{{ route('dashboard.offers.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-12 gap-3 pt-2 border-t border-warm dark:border-warm items-center">
                <div class="md:col-span-6 relative">
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="{{ __('Search offers by property title, address, or buyer...') }}"
                           class="form-input search-input w-full pl-9 pr-8 text-xs">
                    <svg class="w-4 h-4 text-muted absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                    @if(request('search'))
                        <a href="{{ route('dashboard.offers.index', request()->except('search')) }}" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200" title="{{ __('Clear search') }}">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </a>
                    @endif
                </div>

                <div class="md:col-span-4">
                    <select name="status" onchange="this.form.submit()" class="form-select w-full text-xs">
                        <option value="">{{ __('All Statuses') }}</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                        <option value="accepted" {{ request('status') === 'accepted' ? 'selected' : '' }}>{{ __('Accepted') }}</option>
                        <option value="countered" {{ request('status') === 'countered' ? 'selected' : '' }}>{{ __('Countered') }}</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>{{ __('Rejected') }}</option>
                    </select>
                </div>

                <div class="md:col-span-2 flex items-center gap-2">
                    <button type="submit" class="btn btn-secondary btn-sm text-xs py-2 px-3 w-full justify-center">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                        <span>{{ __('Filter') }}</span>
                    </button>

                    @if(request()->hasAny(['search', 'status']))
                        <a href="{{ route('dashboard.offers.index') }}" class="p-2 text-muted hover:text-orange-600 dark:hover:text-orange-400" title="{{ __('Reset Filters') }}">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        @if($offers->isEmpty())
            @if(request('search') || request('status'))
                <div class="card p-12 text-center cinematic-fade-up">
                    <svg class="w-12 h-12 text-muted dark:text-muted mb-4 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                    <h4 class="font-display font-semibold text-muted dark:text-white text-lg mb-2">{{ __('No matching offers') }}</h4>
                    <p class="text-muted dark:text-muted text-sm mb-6 max-w-sm mx-auto">{{ __('No bids or offers match your current search and filter criteria.') }}</p>
                    <a href="{{ route('dashboard.offers.index') }}" class="btn btn-secondary btn-sm">
                        {{ __('Clear All Filters') }}
                    </a>
                </div>
            @else
                <x-empty-state
                    icon="document"
                    title="{{ __('No active offers') }}"
                    description="{{ __('You haven\'t made or received any offers yet. Explore the marketplace to find your dream property.') }}"
                    action="{{ route('properties.index') }}"
                    actionText="{{ __('Explore Properties') }}"
                />
            @endif
        @else
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 cinematic-fade-up cinematic-stagger-2">
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
                    
                    <a href="{{ route('dashboard.offers.show', $offer) }}" class="card card-hover block group overflow-hidden {{ $isActionRequired ? 'border-primary-500 ring-2 ring-primary-500/20' : '' }}">
                        <div class="p-5">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center gap-2">
                                    <x-badge variant="{{ $current->status === 'accepted' ? 'success' : ($current->status === 'pending' ? 'warning' : ($current->status === 'rejected' ? 'error' : 'info')) }}">
                                        {{ __($current->status) }}
                                    </x-badge>
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

                            <div class="card bg-surface dark:bg-surface/50 p-4 rounded-xl flex items-center justify-between border border-warm dark:border-warm mb-4">
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

            @if($offers->hasPages())
                <div class="mt-6">
                    {{ $offers->links() }}
                </div>
            @endif
        @endif
    </div>
</x-layouts.dashboard>
