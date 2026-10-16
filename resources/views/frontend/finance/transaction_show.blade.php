<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Transaction') }}: {{ $transaction['id'] }}</x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="mb-8">
            <a href="{{ route('dashboard.finance.transactions') }}" class="text-sm font-medium text-muted hover:text-muted dark:hover:text-white flex items-center gap-2 mb-4 transition-colors w-max">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                {{ __('Back to Transactions') }}
            </a>
            
            <div class="flex flex-col sm:flex-row justify-between sm:items-end gap-4">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        @php
                            $sClass = 'badge-neutral';
                            if ($transaction['status'] === 'completed') $sClass = 'badge-success';
                            if ($transaction['status'] === 'processing') $sClass = 'badge-primary';
                            if ($transaction['status'] === 'failed') $sClass = 'badge-error';
                        @endphp
                        <span class="badge {{ $sClass }} uppercase">{{ __($transaction['status']) }}</span>
                        <span class="badge badge-neutral uppercase">{{ __($transaction['type']) }}</span>
                    </div>
                    <h1 class="text-3xl font-extrabold font-display text-muted dark:text-white tracking-tight">{{ $transaction['id'] }}</h1>
                    <p class="text-muted dark:text-muted mt-1">{{ $transaction['reference'] }}</p>
                </div>
                <div class="flex gap-2 flex-wrap">
                    <button class="btn btn-secondary flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                        Print Receipt
                    </button>
                    @if($transaction['status'] === 'completed')
                        <button class="btn btn-secondary text-error-600 border-error-200 hover:bg-error-50 dark:hover:bg-error-900/20">{{ __('Issue Refund') }}</button>
                    @endif
                </div>
            </div>
        </div>

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

    </div>
</x-layouts.dashboard>
