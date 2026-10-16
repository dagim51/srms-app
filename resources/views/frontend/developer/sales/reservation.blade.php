<x-layouts.dashboard>
    <x-slot name="page-title">Reservation: {{ $unit['number'] }}</x-slot>

    <div class="max-w-3xl mx-auto">
        <div class="mb-8">
            <a href="{{ route('dashboard.sales.units.show', $unit['id']) }}" class="text-sm font-medium text-muted hover:text-muted dark:hover:text-white flex items-center gap-2 mb-4 transition-colors w-max">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Back to Unit
            </a>
            
            <h1 class="text-3xl font-extrabold font-display text-muted dark:text-white tracking-tight">{{ __('Make Reservation') }}</h1>
            <p class="text-muted mt-2">Reserve Unit {{ $unit['number'] }} ({{ $unit['price'] }}) for a client.</p>
        </div>

        <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-3xl p-8 shadow-sm">
            <form action="#" method="POST" class="space-y-6">
                <div>
                    <label class="block text-sm font-bold text-muted dark:text-dark-subtle mb-2">{{ __('Select or Add Client') }}</label>
                    <select class="input w-full">
                        <option value="">-- Choose Existing Client --</option>
                        <option value="1">{{ __('Abebe Kebede') }}</option>
                        <option value="2">{{ __('Sara Tadesse') }}</option>
                    </select>
                    <div class="mt-2 text-right">
                        <button type="button" class="text-sm text-primary-600 font-medium hover:underline">{{ __('+ Or create new client') }}</button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-muted dark:text-dark-subtle mb-2">{{ __('Payment Plan') }}</label>
                        <select class="input w-full">
                            <option>{{ __('Standard 30/70 Plan') }}</option>
                            <option>{{ __('Early Bird 50/50') }}</option>
                            <option>{{ __('Full Payment') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-muted dark:text-dark-subtle mb-2">Reservation Fee (ETB)</label>
                        <input type="text" class="input w-full" value="100,000" readonly>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-muted dark:text-dark-subtle mb-2">{{ __('Reservation Expiry Date') }}</label>
                    <input type="date" class="input w-full" value="{{ date('Y-m-d', strtotime('+14 days')) }}">
                    <p class="text-xs text-muted mt-1">{{ __('If the contract is not signed by this date, the unit will be released.') }}</p>
                </div>
                
                <div class="pt-6 border-t border-warm dark:border-warm flex justify-end gap-3">
                    <a href="{{ route('dashboard.sales.units.show', $unit['id']) }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                    <button type="button" class="btn btn-primary">{{ __('Confirm Reservation') }}</button>
                </div>
            </form>
        </div>

    </div>
</x-layouts.dashboard>
