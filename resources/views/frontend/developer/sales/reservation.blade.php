<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Reservation') }}: {{ $unit['number'] }}</x-slot>

    <div class="max-w-3xl mx-auto page-enter space-y-6">
        <div class="mb-4">
            <a href="{{ route('dashboard.sales.units.show', $unit['id']) }}" class="text-sm font-medium text-muted hover:text-muted dark:hover:text-white flex items-center gap-2 transition-colors w-max">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                {{ __('Back to Unit') }}
            </a>
        </div>

        <x-page-header
            :title="__('Make Reservation - Unit ') . $unit['number']"
            :description="__('Reserve Unit ') . $unit['number'] . ' (' . $unit['price'] . ') ' . __('for an off-plan client.')"
            icon="key"
            badge="Reservation" />

        @if(session('success'))
            <div class="px-4 py-3 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 text-sm border border-emerald-200 dark:border-emerald-800 flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ session('success') }}
            </div>
        @endif

        <div class="card bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-3xl p-8">
            <form action="{{ route('dashboard.sales.reservation.store', $unit['id']) }}" method="POST" class="space-y-6">
                @csrf
                <div>
                    <label class="block text-sm font-bold text-muted dark:text-dark-subtle mb-2">{{ __('Select Prospective Buyer') }} <span class="text-red-500">*</span></label>
                    <select name="buyer_id" class="input w-full" required>
                        <option value="">{{ __('— Choose Registered Buyer —') }}</option>
                        @foreach($buyers as $buyer)
                            <option value="{{ $buyer->id }}">{{ $buyer->name }} ({{ $buyer->phone ?? $buyer->email }})</option>
                        @endforeach
                    </select>
                    <div class="mt-2 text-right">
                        <a href="{{ route('dashboard.sales.buyers') }}" class="text-xs text-primary-600 font-medium hover:underline">{{ __('+ Register new buyer in directory') }}</a>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-muted dark:text-dark-subtle mb-2">{{ __('Payment Plan Selection') }}</label>
                        <select name="payment_plan_id" class="input w-full">
                            <option value="">{{ __('— Standard Development Plan —') }}</option>
                            @foreach($paymentPlans as $plan)
                                <option value="{{ $plan->id }}">{{ $plan->name }} ({{ $plan->installments->count() }} milestones)</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-muted dark:text-dark-subtle mb-2">{{ __('Holding Deposit Fee (ETB)') }} <span class="text-red-500">*</span></label>
                        <input type="number" name="deposit_amount" step="0.01" class="input w-full" value="100000" required>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-muted dark:text-dark-subtle mb-2">{{ __('Reservation Notes & Terms') }}</label>
                    <textarea name="notes" rows="3" class="input w-full" placeholder="{{ __('Special conditions, installment milestones, or buyer requests...') }}"></textarea>
                    <p class="text-xs text-muted mt-1">{{ __('Reserving this unit will take it off the public marketplace and create an active Handover Record.') }}</p>
                </div>

                <div class="pt-6 border-t border-warm dark:border-warm flex justify-end gap-3">
                    <a href="{{ route('dashboard.sales.units.show', $unit['id']) }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn btn-primary">{{ __('Confirm Reservation') }}</button>
                </div>
            </form>
        </div>

    </div>
</x-layouts.dashboard>
