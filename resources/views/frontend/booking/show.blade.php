<x-layouts.app>
    <x-slot name="title">{{ __('Booking Status - SRMS') }}</x-slot>

    <div class="relative min-h-screen bg-[#FFF5EB] dark:bg-[#0B0F19] selection:bg-primary-500/30">
        {{-- Background decorative blobs --}}
        @if($booking->status === 'confirmed')
            <div class="absolute top-0 right-0 w-full max-w-3xl h-full max-h-3xl rounded-full bg-emerald-400/10 blur-[120px] pointer-events-none"></div>
        @elseif($booking->status === 'cancelled')
            <div class="absolute top-0 right-0 w-full max-w-3xl h-full max-h-3xl rounded-full bg-rose-400/10 blur-[120px] pointer-events-none"></div>
        @else
            <div class="absolute top-0 right-0 w-full max-w-3xl h-full max-h-3xl rounded-full bg-amber-400/10 blur-[120px] pointer-events-none"></div>
        @endif

        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pt-32 pb-24 relative z-10 page-enter">

            <x-page-header
                :title="'Booking: ' . $booking->reference"
                :description="'Property: ' . $booking->accommodation->property->title . ' • Status: ' . strtoupper($booking->status)"
                icon="calendar"
                :badge="strtoupper($booking->status)">
                <x-slot:actions>
                    <a href="{{ route('dashboard.reservations.index') }}" class="btn btn-secondary text-xs">
                        {{ __('View All Reservations') }}
                    </a>
                </x-slot:actions>
            </x-page-header>
            @if(session('success'))
                <div class="mb-10 p-5 rounded-2xl bg-emerald-50/80 dark:bg-emerald-900/20 text-emerald-800 dark:text-emerald-300 font-medium border border-emerald-200/50 dark:border-emerald-800/50 backdrop-blur-md animate-fade-in flex items-center gap-3 shadow-sm">
                    <svg class="w-6 h-6 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    {{ session('success') }}
                </div>
            @endif

            <div class="card bg-white/80 dark:bg-[#1E293B]/80 backdrop-blur-xl border border-white/20 dark:border-white/5 rounded-3xl dark: overflow-hidden relative">
                
                {{-- Header Status --}}
                <div class="p-12 border-b border-warm dark:border-warm/50 text-center relative overflow-hidden">
                    <div class="relative z-10">
                        @if($booking->status === 'pending')
                            <div class="w-20 h-20 mx-auto bg-gradient-to-br from-amber-100 to-amber-50 dark:from-amber-900/40 dark:to-amber-800/10 text-amber-600 dark:text-amber-400 rounded-full flex items-center justify-center mb-6 shadow-sm border border-amber-200/50 dark:border-amber-700/30">
                                <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <h1 class="text-3xl md:text-4xl font-extrabold font-display text-[#0F172A] dark:text-white tracking-tight">{{ __('Payment Required') }}</h1>
                            <p class="text-[#4B5563] dark:text-[#CBD5E1] mt-3 text-lg">{{ __('Your booking is reserved. Please complete your payment to confirm your stay.') }}</p>
                        @elseif($booking->status === 'confirmed')
                            <div class="w-20 h-20 mx-auto bg-gradient-to-br from-emerald-100 to-emerald-50 dark:from-emerald-900/40 dark:to-emerald-800/10 text-emerald-600 dark:text-emerald-400 rounded-full flex items-center justify-center mb-6 shadow-sm border border-emerald-200/50 dark:border-emerald-700/30">
                                <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            </div>
                            <h1 class="text-3xl md:text-4xl font-extrabold font-display text-[#0F172A] dark:text-white tracking-tight">Booking Confirmed!</h1>
                            <p class="text-[#4B5563] dark:text-[#CBD5E1] mt-3 text-lg">You're all set for your trip to <span class="font-bold text-[#111827] dark:text-white">{{ $booking->accommodation->property->neighborhood->name ?? 'Addis Ababa' }}</span>.</p>
                        @elseif($booking->status === 'cancelled')
                            <div class="w-20 h-20 mx-auto bg-gradient-to-br from-rose-100 to-rose-50 dark:from-rose-900/40 dark:to-rose-800/10 text-rose-600 dark:text-rose-400 rounded-full flex items-center justify-center mb-6 shadow-sm border border-rose-200/50 dark:border-rose-700/30">
                                <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </div>
                            <h1 class="text-3xl md:text-4xl font-extrabold font-display text-[#0F172A] dark:text-white tracking-tight">{{ __('Booking Cancelled') }}</h1>
                            <p class="text-[#4B5563] dark:text-[#CBD5E1] mt-3 text-lg">{{ __('This reservation has been successfully cancelled.') }}</p>
                        @elseif($booking->status === 'checked_in')
                            <div class="w-20 h-20 mx-auto bg-gradient-to-br from-indigo-100 to-indigo-50 dark:from-indigo-900/40 dark:to-indigo-800/10 text-indigo-600 dark:text-indigo-400 rounded-full flex items-center justify-center mb-6 shadow-sm border border-indigo-200/50 dark:border-indigo-700/30">
                                <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" /></svg>
                            </div>
                            <h1 class="text-3xl md:text-4xl font-extrabold font-display text-[#0F172A] dark:text-white tracking-tight">{{ __('Checked In') }}</h1>
                            <p class="text-[#4B5563] dark:text-[#CBD5E1] mt-3 text-lg">Enjoy your stay at <span class="font-bold text-[#111827] dark:text-white">{{ $booking->accommodation->property->title }}</span>.</p>
                        @elseif($booking->status === 'completed')
                            <div class="w-20 h-20 mx-auto bg-gradient-to-br from-purple-100 to-purple-50 dark:from-purple-900/40 dark:to-purple-800/10 text-purple-600 dark:text-purple-400 rounded-full flex items-center justify-center mb-6 shadow-sm border border-purple-200/50 dark:border-purple-700/30">
                                <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            </div>
                            <h1 class="text-3xl md:text-4xl font-extrabold font-display text-[#0F172A] dark:text-white tracking-tight">{{ __('Stay Completed') }}</h1>
                            <p class="text-[#4B5563] dark:text-[#CBD5E1] mt-3 text-lg">Thank you for staying with us. Please consider leaving a review!</p>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-12">
                    {{-- Left: Details --}}
                    <div class="md:col-span-7 p-10 md:p-12 space-y-10">
                        
                        <div>
                            <h3 class="text-xs font-black text-[#6B7280] dark:text-[#94A3B8] uppercase tracking-widest mb-6">{{ __('Reservation Details') }}</h3>
                            
                            <div class="flex gap-5 items-center group">
                                <div class="w-20 h-20 rounded-2xl overflow-hidden shrink-0 shadow-sm border border-warm dark:border-warm">
                                    <img src="{{ $booking->accommodation->property->cover_image ? asset('storage/' . $booking->accommodation->property->cover_image) : 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&q=80&w=200' }}" alt="Property" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                                </div>
                                <div>
                                    <h4 class="font-bold font-display text-lg text-[#0F172A] dark:text-white line-clamp-2 leading-tight">{{ $booking->accommodation->property->title }}</h4>
                                    <p class="text-sm text-[#4B5563] dark:text-[#CBD5E1] mt-1 font-medium">{{ $booking->accommodation->property->type->name ?? 'Entire home' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="card grid grid-cols-2 gap-4 bg-surface/50 dark:bg-surface/30 p-2 rounded-2xl border border-warm/50 dark:border-warm/50">
                            <div class="card p-4 rounded-xl bg-white dark:bg-[#1E293B] border border-warm dark:border-warm">
                                <p class="text-xs text-primary-500 font-black uppercase tracking-widest mb-1">{{ __('Check-in') }}</p>
                                <p class="font-bold text-[#111827] dark:text-white text-lg">{{ $booking->check_in_date->format('M d, Y') }}</p>
                                <p class="text-xs text-muted font-medium mt-0.5">{{ \Carbon\Carbon::parse($booking->accommodation->check_in_time)->format('g:i A') }}</p>
                            </div>
                            <div class="card p-4 rounded-xl bg-white dark:bg-[#1E293B] border border-warm dark:border-warm">
                                <p class="text-xs text-primary-500 font-black uppercase tracking-widest mb-1">{{ __('Checkout') }}</p>
                                <p class="font-bold text-[#111827] dark:text-white text-lg">{{ $booking->check_out_date->format('M d, Y') }}</p>
                                <p class="text-xs text-muted font-medium mt-0.5">{{ \Carbon\Carbon::parse($booking->accommodation->check_out_time)->format('g:i A') }}</p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-8">
                            <div>
                                <p class="text-xs text-[#6B7280] dark:text-[#94A3B8] font-black uppercase tracking-widest mb-2">{{ __('Guests') }}</p>
                                <p class="text-[#111827] dark:text-white font-semibold text-lg flex items-center gap-2">
                                    <svg class="w-5 h-5 text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                    {{ $booking->guests }} guest{{ $booking->guests > 1 ? 's' : '' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-[#6B7280] dark:text-[#94A3B8] font-black uppercase tracking-widest mb-2">{{ __('Confirmation Code') }}</p>
                                <p class="text-[#111827] dark:text-white font-mono font-bold text-lg tracking-widest">{{ strtoupper(substr($booking->id, 0, 8)) }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Right: Payment/Actions --}}
                    <div class="md:col-span-5 bg-surface/50 dark:bg-surface/20 p-10 md:p-12 border-t md:border-t-0 md:border-l border-warm dark:border-warm/50 flex flex-col justify-between">
                        
                        <div>
                            <h3 class="text-xs font-black text-[#6B7280] dark:text-[#94A3B8] uppercase tracking-widest mb-6">{{ __('Payment Summary') }}</h3>
                            
                            <div class="flex justify-between items-end pb-8 border-b border-warm dark:border-warm">
                                <div>
                                    <span class="block text-sm font-bold text-[#111827] dark:text-white">{{ __('Total Amount') }}</span>
                                    <span class="block text-xs text-muted mt-1">{{ __('Includes taxes and fees') }}</span>
                                </div>
                                <span class="text-3xl font-extrabold font-display text-[#0F172A] dark:text-white">{{ $booking->accommodation->property->currency === 'USD' ? '$' : 'Br' }}{{ number_format($booking->total_price, 2) }}</span>
                            </div>
                        </div>

                        <div class="space-y-4 mt-8">
                            @if($booking->status === 'pending')
                                <form action="{{ route('booking.confirm', $booking) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="relative overflow-hidden group w-full py-5 rounded-2xl bg-primary-600 hover:bg-primary-700 text-white font-bold text-lg shadow-xl hover:shadow-2xl hover:scale-[1.02] transition-all duration-300">
                                        <span class="relative z-10">Pay {{ $booking->accommodation->property->currency === 'USD' ? '$' : 'Br' }}{{ number_format($booking->total_price, 2) }}</span>
                                        <div class="absolute inset-0 bg-gradient-to-r from-primary-600 to-indigo-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                    </button>
                                    <p class="text-center text-xs font-medium text-muted mt-4 flex items-center justify-center gap-1.5">
                                        <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                        Secure mock payment
                                    </p>
                                </form>
                            @endif

                            @if($booking->status === 'confirmed')
                                <form action="{{ route('booking.check-in', $booking) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="relative overflow-hidden group w-full py-5 rounded-2xl bg-indigo-600 text-white font-bold text-lg shadow-xl hover:shadow-2xl hover:scale-[1.02] transition-all duration-300">
                                        <span class="relative z-10">{{ __('Check-In Now') }}</span>
                                        <div class="absolute inset-0 bg-gradient-to-r from-indigo-500 to-purple-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                    </button>
                                </form>
                            @elseif($booking->status === 'checked_in')
                                <form action="{{ route('booking.check-out', $booking) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="relative overflow-hidden group w-full py-5 rounded-2xl bg-slate-800 hover:bg-slate-900 text-white font-bold text-lg shadow-xl hover:shadow-2xl hover:scale-[1.02] transition-all duration-300">
                                        <span class="relative z-10">{{ __('Check-Out') }}</span>
                                        <div class="absolute inset-0 bg-gradient-to-r from-slate-700 to-slate-900 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                    </button>
                                </form>
                            @elseif($booking->status === 'completed')
                                <a href="{{ route('booking.review.create', $booking) }}" class="relative overflow-hidden group w-full py-5 rounded-2xl bg-purple-600 text-white font-bold text-lg shadow-xl hover:shadow-2xl hover:scale-[1.02] transition-all duration-300 inline-block text-center flex items-center justify-center">
                                    <span class="relative z-10">{{ __('Write a Review') }}</span>
                                    <div class="absolute inset-0 bg-gradient-to-r from-purple-500 to-fuchsia-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                </a>
                            @endif

                            @if(in_array($booking->status, ['pending', 'confirmed']))
                                <div class="pt-6">
                                    <form action="{{ route('booking.cancel', $booking) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="w-full text-center text-rose-500 hover:text-rose-600 text-sm font-bold transition-colors py-3 px-4 rounded-xl hover:bg-rose-50 dark:hover:bg-rose-900/20" onclick="return confirm('Are you sure you want to cancel this booking?');">{{ __('Cancel Reservation') }}</button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</x-layouts.app>
