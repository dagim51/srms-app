<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Reservation Details') }}</x-slot>
    <x-slot name="breadcrumb">
        <a href="{{ route('dashboard') }}" class="hover:text-primary-600 transition-colors">{{ __('Dashboard') }}</a>
        <span>/</span>
        <a href="{{ route('dashboard.reservations.index') }}" class="hover:text-primary-600 transition-colors">{{ __('Reservations') }}</a>
        <span>/</span>
        <span class="text-muted dark:text-white font-medium">{{ __('Details') }}</span>
    </x-slot>

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
                    $timeRemaining = $diff->d . ' days, ' . $diff->h . ' hours';
                } else {
                    $timeRemaining = $diff->h . ' hours, ' . $diff->i . ' mins';
                    if ($diff->h < 12) $expiresSoon = true;
                }
            }
        }
    @endphp

    <div class="max-w-5xl mx-auto space-y-6">
        
        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 text-emerald-800 text-sm font-medium border border-emerald-200">
                {{ session('success') }}
            </div>
        @endif
        
        @if(session('info'))
            <div class="p-4 rounded-xl bg-blue-50 text-blue-800 text-sm font-medium border border-blue-200">
                {{ session('info') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            {{-- Left Column: Status and Payment --}}
            <div class="lg:col-span-2 space-y-6">
                
                {{-- Status Card --}}
                <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-[#273244] rounded-2xl shadow-sm overflow-hidden">
                    <div class="p-6 sm:p-8">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                            <div>
                                <h2 class="text-xl font-bold font-display text-muted dark:text-white mb-1">{{ __('Reservation Status') }}</h2>
                                <p class="text-sm text-muted">{{ __('ID') }}: {{ strtoupper(substr($reservation->id, 0, 8)) }}</p>
                            </div>
                            <span class="inline-block px-4 py-1.5 rounded-lg text-sm font-bold uppercase tracking-wider text-center {{ $badgeColor }}">
                                {{ __($reservation->status) }}
                            </span>
                        </div>

                        @if($isPending)
                            <div class="bg-amber-50 dark:bg-amber-900/10 p-6 rounded-2xl border border-amber-200 dark:border-amber-800 mb-8 text-center sm:text-left flex flex-col sm:flex-row items-center gap-6">
                                <div class="w-16 h-16 rounded-full bg-amber-100 dark:bg-amber-900/50 flex items-center justify-center text-amber-500 flex-shrink-0">
                                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-amber-900 dark:text-amber-100 mb-1">{{ __('Pending Payment') }}</h3>
                                    <p class="text-sm text-amber-700 dark:text-amber-300 mb-2">{{ __('Complete your reservation fee payment to secure this property. It is currently held for you.') }}</p>
                                    @if($timeRemaining)
                                        <p class="text-sm font-bold {{ $expiresSoon ? 'text-rose-600 dark:text-rose-400 animate-pulse' : 'text-amber-600 dark:text-amber-400' }}">
                                            {{ __('Expires in') }}: {{ $timeRemaining }}
                                        </p>
                                    @endif
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row gap-4 border-t border-warm dark:border-warm pt-6">
                                {{-- Mock Payment Form --}}
                                <form action="{{ route('dashboard.reservations.update', $reservation) }}" method="POST" class="flex-1">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="action" value="pay">
                                    <button type="submit" class="btn btn-primary w-full py-3 shadow-md">
                                        {{ __('Pay Reservation Fee') }}
                                    </button>
                                </form>

                                <form action="{{ route('dashboard.reservations.update', $reservation) }}" method="POST" class="sm:w-1/3">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="action" value="cancel">
                                    <button type="submit" class="btn btn-outline w-full py-3 text-rose-600 border-rose-200 hover:bg-rose-50 dark:hover:bg-rose-900/20" onclick="return confirm('{{ __('Are you sure you want to cancel this reservation?') }}');">
                                        {{ __('Cancel') }}
                                    </button>
                                </form>
                            </div>
                        @elseif($reservation->status === 'confirmed')
                            <div class="bg-emerald-50 dark:bg-emerald-900/10 p-6 rounded-2xl border border-emerald-200 dark:border-emerald-800 mb-8 text-center sm:text-left flex flex-col sm:flex-row items-center gap-6">
                                <div class="w-16 h-16 rounded-full bg-emerald-100 dark:bg-emerald-900/50 flex items-center justify-center text-emerald-500 flex-shrink-0">
                                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-emerald-900 dark:text-emerald-100 mb-1">{{ __('Reservation Confirmed') }}</h3>
                                    <p class="text-sm text-emerald-700 dark:text-emerald-300">{{ __('Your reservation fee has been paid. The property is officially reserved for you.') }}</p>
                                </div>
                            </div>
                            <div class="flex border-t border-warm dark:border-warm pt-6">
                                <button class="btn btn-primary shadow-sm" disabled>{{ __('View Receipt (Coming Soon)') }}</button>
                            </div>
                        @elseif($reservation->status === 'expired')
                            <div class="bg-rose-50 dark:bg-rose-900/10 p-6 rounded-2xl border border-rose-200 dark:border-rose-800 text-center sm:text-left flex flex-col sm:flex-row items-center gap-6">
                                <div class="w-16 h-16 rounded-full bg-rose-100 dark:bg-rose-900/50 flex items-center justify-center text-rose-500 flex-shrink-0">
                                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-rose-900 dark:text-rose-100 mb-1">{{ __('Reservation Expired') }}</h3>
                                    <p class="text-sm text-rose-700 dark:text-rose-300">{{ __('The 48-hour hold period has elapsed without payment. This property is no longer reserved for you.') }}</p>
                                </div>
                            </div>
                        @else
                            <div class="p-6 text-center text-muted">
                                {{ __('This reservation was cancelled.') }}
                            </div>
                        @endif
                    </div>
                </div>

                @if($reservation->notes)
                    <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-[#273244] rounded-2xl shadow-sm overflow-hidden p-6 sm:p-8">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-muted mb-4">{{ __('Your Notes') }}</h3>
                        <div class="p-4 bg-surface dark:bg-surface/50 rounded-xl text-sm text-muted dark:text-dark-subtle border border-warm dark:border-warm">
                            {{ $reservation->notes }}
                        </div>
                    </div>
                @endif
            </div>
            
            {{-- Right Column: Property & Fee Summary --}}
            <div class="space-y-6">
                <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-[#273244] rounded-2xl shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-warm dark:border-warm">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-muted mb-4">{{ __('Reservation Summary') }}</h3>
                        
                        @if($reservation->listing && $reservation->listing->property)
                            @php $primaryImg = $reservation->listing->property->media->first() ? ($reservation->listing->property->media->first()->url ?? $reservation->listing->property->media->first()->storage_key) : 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=300&q=80'; @endphp
                            <div class="w-full h-32 rounded-xl overflow-hidden mb-4 border border-warm dark:border-warm">
                                <img src="{{ $primaryImg }}" alt="{{ $reservation->listing->property->title }}" class="w-full h-full object-cover">
                            </div>
                            
                            <h4 class="font-bold text-muted dark:text-white text-base leading-tight mb-4">{{ $reservation->listing->property->title }}</h4>
                            
                            <div class="space-y-3">
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-muted">{{ __('Property Price') }}</span>
                                    <span class="font-medium text-muted dark:text-white">{{ $reservation->listing->formatted_price }}</span>
                                </div>
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-muted">{{ __('Reservation Rate') }}</span>
                                    <span class="font-medium text-muted dark:text-white">5%</span>
                                </div>
                                
                                <div class="pt-3 mt-3 border-t border-warm dark:border-warm flex justify-between items-center">
                                    <span class="font-bold text-muted dark:text-white">{{ __('Total Due') }}</span>
                                    <span class="font-bold text-primary-600 text-lg">
                                        {{ $reservation->listing->currency === 'USD' ? '$' : 'Br' }}{{ number_format($reservation->listing->price * 0.05, 2) }}
                                    </span>
                                </div>
                            </div>
                            
                            <div class="mt-6 pt-4 border-t border-warm dark:border-warm">
                                <a href="{{ route('properties.show', $reservation->listing->property->id) }}" class="btn btn-outline btn-sm w-full flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                    {{ __('View Full Listing') }}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</x-layouts.dashboard>
