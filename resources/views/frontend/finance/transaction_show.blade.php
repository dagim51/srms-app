<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Transaction') }}: {{ $transaction['id'] }}</x-slot>

    <div x-data="{
        showRefundModal: false
    }" class="max-w-7xl mx-auto page-enter space-y-6">

        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-sm flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-sm flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        @endif

        <x-page-header
            :title="__('Transaction Record: ') . $transaction['id']"
            :description="$transaction['reference'] . ' • ' . __('Type:') . ' ' . __($transaction['type']) . ' • ' . __('Status:') . ' ' . __($transaction['status'])"
            icon="cash"
            :badge="$transaction['status']">
            <x-slot:actions>
                <div class="flex items-center gap-2 flex-wrap">
                    <a href="{{ route('dashboard.finance.transactions') }}" class="btn btn-secondary text-xs flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                        {{ __('Back to Transactions') }}
                    </a>
                    <button class="btn btn-secondary text-xs flex items-center gap-1.5" onclick="window.print()">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                        {{ __('Print Receipt') }}
                    </button>
                    @if($transaction['status'] === 'completed' && ($transaction['raw_amount'] ?? 1) > 0)
                        <button type="button"
                                @click="showRefundModal = true"
                                class="btn btn-secondary text-xs text-rose-600 dark:text-rose-400 border-rose-200 dark:border-rose-900/40 hover:bg-rose-50 dark:hover:bg-rose-950/30">
                            {{ __('Issue Refund') }}
                        </button>
                    @endif
                </div>
            </x-slot:actions>
        </x-page-header>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-8">
                <!-- Payment Summary -->
                <div class="card p-6 md:p-8">
                    <h2 class="card-title text-xl mb-6">{{ __('Payment Summary') }}</h2>
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8">
                        <div>
                            <p class="card-kv-label">{{ __('Amount') }}</p>
                            <p class="card-kv-value mt-1">{{ $transaction['amount'] }}</p>
                        </div>
                        <div>
                            <p class="card-kv-label">{{ __('Tax (15% VAT)') }}</p>
                            <p class="card-kv-value font-medium mt-1">{{ $transaction['tax'] }}</p>
                        </div>
                        <div>
                            <p class="card-kv-label">{{ __('Total Settled') }}</p>
                            <p class="card-kv-value font-black text-xl text-primary-600 dark:text-primary-400 mt-1">{{ $transaction['total'] }}</p>
                        </div>
                        <div>
                            <p class="card-kv-label">{{ __('Method') }}</p>
                            <p class="card-kv-value font-medium mt-1">{{ $transaction['method'] }}</p>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-[#EBDBC9] dark:border-[#273244] grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="card-kv-label">{{ __('Customer / Tenant') }}</p>
                            <p class="card-kv-value font-medium mt-1">{{ $transaction['customer'] }}</p>
                        </div>
                        <div>
                            <p class="card-kv-label">{{ __('Gateway Reference') }}</p>
                            <p class="font-mono text-sm text-muted dark:text-dark-subtle mt-1">{{ $transaction['gateway_ref'] }}</p>
                        </div>
                        <div>
                            <p class="card-kv-label">{{ __('Related Invoice') }}</p>
                            <p class="card-kv-value font-medium text-primary-600 dark:text-primary-400 mt-1">{{ $transaction['invoice_id'] }}</p>
                        </div>
                        <div>
                            <p class="card-kv-label">{{ __('Processed On') }}</p>
                            <p class="card-kv-value font-medium mt-1">{{ $transaction['date'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Timeline -->
            <div>
                <div class="card p-6 sticky top-6">
                    <h3 class="card-title text-base mb-6">{{ __('Processing Timeline') }}</h3>
                    <div class="space-y-6 relative before:absolute before:inset-0 before:ml-5 before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-gradient-to-b before:from-transparent before:via-[#EBDBC9] dark:before:via-[#273244] before:to-transparent">
                        @foreach($transaction['timeline'] as $event)
                            <div class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
                                <div class="flex items-center justify-center w-10 h-10 rounded-full border-4 border-white dark:border-[#111827] bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 shadow-xs z-10">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                                <div class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] card-nested p-4">
                                    <p class="font-bold text-sm text-muted dark:text-white">{{ __($event['status']) }}</p>
                                    <p class="text-xs text-muted mt-1">{{ $event['message'] }}</p>
                                    <p class="text-xs text-muted mt-2 font-mono">{{ $event['time'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Issue Refund Modal --}}
        <div x-show="showRefundModal"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">

            <div @click.away="showRefundModal = false"
                 class="bg-white dark:bg-dark-card rounded-2xl shadow-2xl border border-warm/50 dark:border-warm/20 max-w-md w-full p-6 space-y-5">
                <div class="flex items-center justify-between border-b border-warm/40 dark:border-warm/10 pb-4">
                    <div>
                        <h3 class="text-lg font-bold text-muted dark:text-white">{{ __('Authorize Transaction Refund') }}</h3>
                        <p class="text-xs text-muted dark:text-dark-subtle mt-0.5">{{ $transaction['id'] }} • {{ $transaction['amount'] }}</p>
                    </div>
                    <button type="button" @click="showRefundModal = false" class="text-muted hover:text-muted dark:hover:text-white">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('dashboard.finance.transactions.refund', $transaction['id']) }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-muted dark:text-dark-subtle mb-1 uppercase tracking-wider">{{ __('Refund Amount (ETB)') }}</label>
                        <input type="number" step="0.01" name="amount" min="0.01" max="{{ $transaction['raw_amount'] ?? 999999 }}" value="{{ $transaction['raw_amount'] ?? '' }}" required class="input w-full text-sm">
                        <p class="text-[11px] text-muted dark:text-dark-subtle mt-1">{{ __('Maximum refundable:') }} {{ $transaction['amount'] }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-muted dark:text-dark-subtle mb-1 uppercase tracking-wider">{{ __('Reason for Refund / Reversal') }}</label>
                        <textarea name="reason" rows="3" required placeholder="{{ __('e.g. Guest booking cancellation or duplicate charge adjustment') }}" class="input w-full text-sm"></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-3 border-t border-warm/40 dark:border-warm/10">
                        <button type="button" @click="showRefundModal = false" class="btn btn-secondary text-sm">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary text-sm bg-rose-600 hover:bg-rose-700 text-white border-transparent">{{ __('Authorize Reversal') }}</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
