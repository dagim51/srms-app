<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('My Reservations') }}</x-slot>
    <x-slot:breadcrumb>
        <x-breadcrumb :items="[
            ['label' => __('Dashboard'), 'url' => route('dashboard')],
            ['label' => __('Reservations')]
        ]" />
    </x-slot:breadcrumb>

    <div class="space-y-6 page-enter">
        <x-page-header
            title="Marketplace Reservations"
            description="Oversee short-stay accommodation bookings, check-in schedules, and guest reservation statuses."
            icon="key"
            badge="Reservations" />

        {{-- Top KPI Stat Cards --}}
        @php
            $baseRes = \App\Models\Reservation::where('user_id', auth()->id());
            $totalRes = (clone $baseRes)->count();
            $confirmedRes = (clone $baseRes)->where('status', 'confirmed')->count();
            $pendingRes = (clone $baseRes)->where('status', 'pending')->count();
            $expiredRes = (clone $baseRes)->where('status', 'expired')->count();
        @endphp
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <x-card-stat
                :label="__('Total Bookings')"
                :value="$totalRes"
                iconColor="orange"
                icon="key"
                :badge="__('Stays')"
                badgeVariant="brand"
                caption="{{ __('All reservation records') }}"
            />
            <x-card-stat
                :label="__('Confirmed')"
                :value="$confirmedRes"
                iconColor="emerald"
                icon="check"
                :badge="__('Active')"
                badgeVariant="success"
                caption="{{ __('Secured short stays') }}"
            />
            <x-card-stat
                :label="__('Pending Payment')"
                :value="$pendingRes"
                iconColor="amber"
                icon="clock"
                :badge="__('Pending')"
                badgeVariant="warning"
                caption="{{ __('Awaiting payment lock') }}"
            />
            <x-card-stat
                :label="__('Expired / Past')"
                :value="$expiredRes"
                iconColor="rose"
                icon="sparkles"
                :badge="__('Released')"
                badgeVariant="neutral"
                caption="{{ __('Expired hold windows') }}"
            />
        </div>

        {{-- Actions & Filter Toolbar --}}
        <div class="cinematic-fade-up cinematic-stagger-2 bg-white dark:bg-[#1E293B] rounded-xl p-4 border border-warm dark:border-warm shadow-sm space-y-4 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-muted dark:text-white">{{ __('Hospitality & Stay Reservations') }}</h2>
                    <p class="text-xs text-muted dark:text-muted">{{ __('Guest booking requests, short-stay dates, and temporary deposit holds') }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('short-stay') }}" class="btn btn-primary btn-sm flex items-center gap-1.5 shadow-sm shadow-orange-500/20">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        {{ __('Book New Stay') }}
                    </a>
                </div>
            </div>

            {{-- Filter Form --}}
            <form method="GET" action="{{ route('dashboard.reservations.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-12 gap-3 pt-2 border-t border-warm dark:border-warm items-center">
                <div class="md:col-span-6 relative">
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="{{ __('Search reservations by property title or address...') }}"
                           class="form-input search-input w-full pl-9 pr-8 text-xs">
                    <svg class="w-4 h-4 text-muted absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                    @if(request('search'))
                        <a href="{{ route('dashboard.reservations.index', request()->except('search')) }}" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200" title="{{ __('Clear search') }}">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </a>
                    @endif
                </div>

                <div class="md:col-span-4">
                    <select name="status" onchange="this.form.submit()" class="form-select w-full text-xs">
                        <option value="">{{ __('All Statuses') }}</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                        <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>{{ __('Confirmed') }}</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>{{ __('Cancelled') }}</option>
                        <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>{{ __('Expired') }}</option>
                    </select>
                </div>

                <div class="md:col-span-2 flex items-center gap-2">
                    <button type="submit" class="btn btn-secondary btn-sm text-xs py-2 px-3 w-full justify-center">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                        <span>{{ __('Filter') }}</span>
                    </button>

                    @if(request()->hasAny(['search', 'status']))
                        <a href="{{ route('dashboard.reservations.index') }}" class="p-2 text-muted hover:text-orange-600 dark:hover:text-orange-400" title="{{ __('Reset Filters') }}">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        @if($reservations->isEmpty())
            @if(request('search') || request('status'))
                <div class="card p-12 text-center cinematic-fade-up">
                    <svg class="w-12 h-12 text-muted dark:text-muted mb-4 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                    <h4 class="font-display font-semibold text-muted dark:text-white text-lg mb-2">{{ __('No matching reservations') }}</h4>
                    <p class="text-muted dark:text-muted text-sm mb-6 max-w-sm mx-auto">{{ __('No reservations match your current search and filter criteria.') }}</p>
                    <a href="{{ route('dashboard.reservations.index') }}" class="btn btn-secondary btn-sm">
                        {{ __('Clear All Filters') }}
                    </a>
                </div>
            @else
                <x-empty-state
                    icon="key"
                    title="{{ __('No active reservations') }}"
                    description="{{ __('You haven\'t reserved any properties yet. Secure your dream property before it\'s gone.') }}"
                    action="{{ route('properties.index') }}"
                    actionText="{{ __('Explore Properties') }}"
                />
            @endif
        @else
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                @foreach($reservations as $reservation)
                    @php
                        $statusColors = [
                            'pending' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300 ring-amber-500',
                            'confirmed' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300',
                            'cancelled' => 'bg-surface-subtle text-muted dark:bg-surface dark:text-dark-subtle',
                            'expired' => 'bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-300',
                        ];
                        
                        $badgeColor = $statusColors[$reservation->status] ?? $statusColors['pending'];
                        $isPending = $reservation->status === 'pending';
                        
                        // Calculate time remaining
                        $expiresSoon = false;
                        $timeRemaining = '';
                        if ($isPending && $reservation->expires_at) {
                            $now = \Carbon\Carbon::now();
                            if ($now->lessThan($reservation->expires_at)) {
                                $diff = $now->diff($reservation->expires_at);
                                if ($diff->d > 0) {
                                    $timeRemaining = $diff->d . 'd ' . $diff->h . 'h';
                                } else {
                                    $timeRemaining = $diff->h . 'h ' . $diff->i . 'm';
                                    if ($diff->h < 12) $expiresSoon = true;
                                }
                            }
                        }
                    @endphp
                    
                    <a href="{{ route('dashboard.reservations.show', $reservation) }}" class="card card-hover block group overflow-hidden {{ $isPending ? 'border-primary-500 ring-2 ring-primary-500/20' : '' }}">
                        <div class="p-5">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center gap-2">
                                    <x-badge variant="{{ $reservation->status === 'confirmed' ? 'success' : ($reservation->status === 'pending' ? 'warning' : ($reservation->status === 'expired' ? 'error' : 'neutral')) }}">
                                        {{ __($reservation->status) }}
                                    </x-badge>
                                    @if($isPending)
                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-primary-500"></span>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <div class="text-xs text-muted">{{ $reservation->created_at->format('M d, Y') }}</div>
                                </div>
                            </div>

                            @if($reservation->listing && $reservation->listing->property)
                                <div class="flex items-center gap-4 mb-4">
                                    @php $primaryImg = $reservation->listing->property->media->first() ? ($reservation->listing->property->media->first()->url ?? $reservation->listing->property->media->first()->storage_key) : 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=300&q=80'; @endphp
                                    <div class="w-20 h-20 rounded-lg overflow-hidden flex-shrink-0 border border-warm dark:border-warm">
                                        <img src="{{ $primaryImg }}" alt="{{ $reservation->listing->property->title }}" class="w-full h-full object-cover">
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-muted dark:text-white text-base group-hover:text-primary-600 transition-colors line-clamp-1">
                                            {{ $reservation->listing->property->title }}
                                        </h3>
                                        <p class="text-xs text-muted dark:text-muted mt-1">{{ $reservation->listing->formatted_price }}</p>
                                    </div>
                                </div>
                            @endif

                            @if($isPending && $timeRemaining)
                                <div class="bg-amber-50 dark:bg-amber-900/20 p-3 rounded-xl flex items-center justify-between border border-amber-100 dark:border-amber-800/30 mb-4">
                                    <div class="flex items-center gap-2 {{ $expiresSoon ? 'text-rose-600 dark:text-rose-400' : 'text-amber-700 dark:text-amber-400' }}">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        <span class="text-xs font-bold uppercase tracking-wider">{{ __('Expires in') }} {{ $timeRemaining }}</span>
                                    </div>
                                    <span class="text-xs font-bold text-amber-700 dark:text-amber-400 bg-amber-100 dark:bg-amber-900/50 px-2 py-1 rounded">{{ __('Pay Now') }}</span>
                                </div>
                            @endif
                            
                            <div class="pt-4 border-t border-warm dark:border-warm flex items-center justify-end text-xs">
                                <span class="text-primary-600 font-bold group-hover:underline flex items-center gap-1">
                                    {{ __('View Details') }}
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                </span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            @if($reservations->hasPages())
                <div class="mt-6">
                    {{ $reservations->links() }}
                </div>
            @endif
        @endif
    </div>
</x-layouts.dashboard>
