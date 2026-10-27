<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Transaction History') }}</x-slot>

    <div x-data="{
        showRefundModal: false,
        selectedPaymentId: '',
        selectedPaymentRef: '',
        selectedPaymentAmount: '',
        maxRefundAmount: 0,
        selectPayment(id, ref, amount, rawAmount) {
            this.selectedPaymentId = id;
            this.selectedPaymentRef = ref;
            this.selectedPaymentAmount = amount;
            this.maxRefundAmount = rawAmount;
        }
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
            title="Financial Transactions"
            description="Comprehensive audit log of all inbound deposits, outbound disbursements, escrow releases, and refunds."
            icon="cash"
            badge="Transactions">
            <x-slot:actions>
                <button type="button"
                        @click="showRefundModal = true; selectPayment('', '', '', 0)"
                        class="btn btn-primary flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                    </svg>
                    {{ __('Process Refund') }}
                </button>
            </x-slot:actions>
        </x-page-header>

        @include('frontend.finance.partials.tabs')

        <div class="card overflow-hidden">
            <div class="p-6 border-b border-[#EBDBC9] dark:border-[#273244] flex gap-4 flex-wrap items-center justify-between">
                <form method="GET" action="{{ route('dashboard.finance.transactions') }}" class="flex items-center gap-3 flex-1 max-w-lg">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </div>
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="{{ __('Search by ID, Reference, or Name...') }}"
                               class="form-input search-input pl-9 pr-8 w-full text-sm">
                        @if(request('search'))
                            <a href="{{ route('dashboard.finance.transactions') }}" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </a>
                        @endif
                    </div>
                    <select name="status" onchange="this.form.submit()" class="input max-w-xs text-sm">
                        <option value="">{{ __('All Statuses') }}</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>{{ __('Completed') }}</option>
                        <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>{{ __('Processing') }}</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>{{ __('Failed') }}</option>
                        <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>{{ __('Refunded') }}</option>
                    </select>
                </form>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-warm-50/50 dark:bg-[#1E293B]/50 border-b border-[#EBDBC9] dark:border-[#273244]">
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Transaction') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Date & Time') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Amount') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Method') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EBDBC9]/50 dark:divide-[#273244]">
                        @forelse($transactions as $trx)
                            <tr class="hover:bg-surface dark:hover:bg-surface/30 transition-colors">
                                <td class="p-4">
                                    <p class="font-bold text-muted dark:text-white font-mono text-sm">
                                        <a href="{{ route('dashboard.finance.transactions.show', $trx['id']) }}" class="hover:text-primary-600 transition-colors">{{ $trx['id'] }}</a>
                                    </p>
                                    <p class="text-xs text-muted dark:text-dark-subtle mt-0.5">{{ $trx['reference'] }} &bull; {{ $trx['type'] }}</p>
                                </td>
                                <td class="p-4 text-sm text-muted dark:text-dark-subtle">{{ date('M d, Y H:i', strtotime($trx['date'])) }}</td>
                                <td class="p-4 font-bold {{ str_starts_with($trx['amount'], '-') ? 'text-error-600' : 'text-muted dark:text-white' }}">{{ $trx['amount'] }}</td>
                                <td class="p-4">
                                    @php
                                        $sClass = 'badge-neutral';
                                        if ($trx['status'] === 'completed') $sClass = 'badge-success';
                                        if ($trx['status'] === 'processing') $sClass = 'badge-primary';
                                        if ($trx['status'] === 'pending') $sClass = 'badge-warning';
                                        if ($trx['status'] === 'failed') $sClass = 'badge-error';
                                        if ($trx['status'] === 'refunded') $sClass = 'badge-neutral bg-surface-subtle text-muted';
                                    @endphp
                                    <span class="badge {{ $sClass }} uppercase text-xs">{{ __($trx['status']) }}</span>
                                </td>
                                <td class="p-4 text-sm text-muted dark:text-dark-subtle">{{ $trx['method'] }}</td>
                                <td class="p-4 text-right space-x-2">
                                    <a href="{{ route('dashboard.finance.transactions.show', $trx['id']) }}" class="text-primary-600 hover:text-primary-700 dark:text-primary-400 font-semibold text-xs">{{ __('View Receipt') }}</a>

                                    @if($trx['status'] === 'completed' && $trx['raw_amount'] > 0)
                                        <button type="button"
                                                @click="showRefundModal = true; selectPayment('{{ $trx['id'] }}', '{{ $trx['id'] }}', '{{ $trx['amount'] }}', {{ $trx['raw_amount'] }})"
                                                class="text-xs px-2.5 py-1 rounded-lg bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 font-semibold hover:bg-rose-100 dark:hover:bg-rose-900/50 transition-colors">
                                            {{ __('Refund') }}
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-muted dark:text-dark-subtle">
                                    <p class="text-sm">{{ __('No transactions found matching the filter.') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($transactions, 'links'))
                <div class="p-4 border-t border-[#EBDBC9] dark:border-[#273244]">
                    {{ $transactions->links() }}
                </div>
            @endif
        </div>

        {{-- Process Refund Modal --}}
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
                 class="bg-white dark:bg-dark-card rounded-2xl shadow-2xl border border-warm/50 dark:border-warm/20 max-w-lg w-full p-6 space-y-5">
                <div class="flex items-center justify-between border-b border-warm/40 dark:border-warm/10 pb-4">
                    <div>
                        <h3 class="text-lg font-bold text-muted dark:text-white">{{ __('Authorize Payment Reversal & Refund') }}</h3>
                        <p class="text-xs text-muted dark:text-dark-subtle mt-0.5" x-text="selectedPaymentRef ? ('Transaction: ' + selectedPaymentRef + ' • ' + selectedPaymentAmount) : 'Select a completed payment below'"></p>
                    </div>
                    <button type="button" @click="showRefundModal = false" class="text-muted hover:text-muted dark:hover:text-white">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form method="POST" :action="'{{ url('dashboard/finance/transactions') }}/' + selectedPaymentId + '/refund'" class="space-y-4">
                    @csrf

                    <template x-if="!selectedPaymentRef">
                        <div>
                            <label class="block text-xs font-semibold text-muted dark:text-dark-subtle mb-1 uppercase tracking-wider">{{ __('Select Completed Payment') }}</label>
                            <select @change="
                                const opt = $event.target.selectedOptions[0];
                                if (opt && opt.value) {
                                    selectPayment(opt.value, opt.text, opt.dataset.amount, parseFloat(opt.dataset.raw));
                                } else {
                                    selectPayment('', '', '', 0);
                                }
                            " required class="input w-full text-sm">
                                <option value="">{{ __('-- Select Payment to Refund --') }}</option>
                                @foreach($refundablePayments as $rp)
                                    <option value="{{ $rp->transaction_reference ?? $rp->id }}"
                                            data-amount="{{ number_format($rp->amount) }} {{ $rp->currency ?? 'ETB' }}"
                                            data-raw="{{ $rp->amount }}">
                                        {{ $rp->transaction_reference ?? ('TRX-' . strtoupper(substr($rp->id, 0, 8))) }} - {{ number_format($rp->amount) }} {{ $rp->currency ?? 'ETB' }} ({{ $rp->user?->name ?? 'Customer' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </template>

                    <div>
                        <label class="block text-xs font-semibold text-muted dark:text-dark-subtle mb-1 uppercase tracking-wider">{{ __('Refund Amount (ETB)') }}</label>
                        <input type="number" step="0.01" name="amount" min="0.01" :max="maxRefundAmount || 99999999" required placeholder="e.g. 5000" class="input w-full text-sm">
                        <p class="text-[11px] text-muted dark:text-dark-subtle mt-1" x-text="maxRefundAmount > 0 ? ('Maximum refundable: ' + maxRefundAmount + ' ETB') : ''"></p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-muted dark:text-dark-subtle mb-1 uppercase tracking-wider">{{ __('Reason for Reversal') }}</label>
                        <textarea name="reason" rows="3" required placeholder="{{ __('e.g. Deposit return upon checkout / cancellation policy adjustment') }}" class="input w-full text-sm"></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-3 border-t border-warm/40 dark:border-warm/10">
                        <button type="button" @click="showRefundModal = false" class="btn btn-secondary text-sm">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary text-sm bg-rose-600 hover:bg-rose-700 text-white border-transparent">{{ __('Authorize Refund') }}</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
