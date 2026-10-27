<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Payouts & Settlements') }}</x-slot>

    <div x-data="{
        showRequestModal: false,
        showProcessModal: false,
        selectedPayoutId: null,
        selectedPayoutRef: '',
        selectedPayoutAmount: '',
        selectedRecipient: ''
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

        <x-page-header
            title="Disbursements & Payout Settlements"
            description="Manage broker commissions, landlord rent disbursements, escrow settlements, and automated banking payouts."
            icon="cash"
            badge="Payouts">
            <x-slot:actions>
                <button type="button" @click="showRequestModal = true" class="btn btn-primary flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    {{ __('Request Disbursement') }}
                </button>
            </x-slot:actions>
        </x-page-header>

        @include('frontend.finance.partials.tabs')

        <div class="card overflow-hidden">
            <div class="p-6 border-b border-[#EBDBC9] dark:border-[#273244] flex gap-4 flex-wrap items-center justify-between">
                <form method="GET" action="{{ route('dashboard.finance.payouts') }}" class="flex items-center gap-3 flex-1 max-w-lg">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </div>
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="{{ __('Search by reference, recipient, bank or account...') }}"
                               class="form-input search-input pl-9 pr-8 w-full text-sm">
                        @if(request('search'))
                            <a href="{{ route('dashboard.finance.payouts') }}" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </a>
                        @endif
                    </div>
                    <select name="status" onchange="this.form.submit()" class="input max-w-xs text-sm">
                        <option value="">{{ __('All Statuses') }}</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                        <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>{{ __('Processing') }}</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>{{ __('Completed') }}</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>{{ __('Failed') }}</option>
                    </select>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-warm-50/50 dark:bg-[#1E293B]/50 border-b border-[#EBDBC9] dark:border-[#273244]">
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Reference') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Beneficiary') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Destination Bank') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Disbursement Amount') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Platform Fee') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EBDBC9]/50 dark:divide-[#273244]">
                        @forelse($payouts as $payout)
                            <tr class="hover:bg-surface dark:hover:bg-surface/30 transition-colors">
                                <td class="p-4 font-mono font-bold text-muted dark:text-white text-sm">
                                    {{ $payout['reference'] }}
                                    <div class="text-[11px] font-sans font-normal text-muted dark:text-dark-subtle">{{ $payout['date'] }}</div>
                                </td>
                                <td class="p-4 font-medium text-muted dark:text-dark-subtle">
                                    {{ $payout['recipient'] }}
                                </td>
                                <td class="p-4 text-sm text-muted dark:text-dark-subtle">
                                    <span class="font-semibold text-muted dark:text-white">{{ $payout['bank_name'] }}</span>
                                    <div class="font-mono text-xs text-muted dark:text-dark-subtle">{{ $payout['account_number'] }}</div>
                                </td>
                                <td class="p-4 font-bold text-muted dark:text-white">
                                    {{ $payout['amount'] }}
                                </td>
                                <td class="p-4 text-sm text-muted dark:text-dark-subtle font-mono">
                                    {{ $payout['fee'] }}
                                </td>
                                <td class="p-4">
                                    @php
                                        $pClass = 'badge-neutral';
                                        if ($payout['status'] === 'completed') $pClass = 'badge-success';
                                        if ($payout['status'] === 'pending') $pClass = 'badge-warning';
                                        if ($payout['status'] === 'processing') $pClass = 'badge-info';
                                        if ($payout['status'] === 'failed') $pClass = 'badge-error';
                                    @endphp
                                    <span class="badge {{ $pClass }} uppercase text-xs">{{ __($payout['status']) }}</span>
                                </td>
                                <td class="p-4 text-right space-x-2">
                                    @if(in_array($payout['status'], ['pending', 'processing']))
                                        <button type="button"
                                                @click="showProcessModal = true; selectedPayoutId = '{{ $payout['id'] }}'; selectedPayoutRef = '{{ $payout['reference'] }}'; selectedPayoutAmount = '{{ $payout['amount'] }}'; selectedRecipient = '{{ addslashes($payout['recipient']) }}'"
                                                class="text-xs px-2.5 py-1 rounded-lg bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 font-semibold hover:bg-primary-100 transition-colors">
                                            {{ __('Settle / Process') }}
                                        </button>
                                    @else
                                        <span class="text-xs text-muted dark:text-dark-subtle italic">
                                            {{ $payout['processed_at'] ?? __('Settled') }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-8 text-center text-muted dark:text-dark-subtle">
                                    <div class="flex flex-col items-center justify-center space-y-2">
                                        <svg class="w-10 h-10 text-muted/50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        <p class="text-sm font-medium">{{ __('No payout settlement records found.') }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($payouts, 'links'))
                <div class="p-4 border-t border-[#EBDBC9] dark:border-[#273244]">
                    {{ $payouts->links() }}
                </div>
            @endif
        </div>

        {{-- Request Payout Modal --}}
        <div x-show="showRequestModal"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">

            <div @click.away="showRequestModal = false"
                 class="bg-white dark:bg-dark-card rounded-2xl shadow-2xl border border-warm/50 dark:border-warm/20 max-w-lg w-full p-6 space-y-5">
                <div class="flex items-center justify-between border-b border-warm/40 dark:border-warm/10 pb-4">
                    <h3 class="text-lg font-bold text-muted dark:text-white">{{ __('Request Disbursement Settlement') }}</h3>
                    <button type="button" @click="showRequestModal = false" class="text-muted hover:text-muted dark:hover:text-white">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('dashboard.finance.payouts.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-muted dark:text-dark-subtle mb-1 uppercase tracking-wider">{{ __('Verified Destination Bank Account') }}</label>
                        <select name="payout_account_id" required class="input w-full text-sm">
                            <option value="">{{ __('-- Select Account --') }}</option>
                            @foreach($payoutAccounts as $account)
                                <option value="{{ $account->id }}">
                                    {{ $account->bank_name }} - {{ $account->account_number }} ({{ $account->account_name }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-muted dark:text-dark-subtle mb-1 uppercase tracking-wider">{{ __('Disbursement Amount (ETB)') }}</label>
                        <input type="number" step="0.01" name="amount" min="10" required placeholder="e.g. 50000" class="input w-full text-sm">
                        <p class="text-[11px] text-muted dark:text-dark-subtle mt-1">{{ __('1% platform processing and banking interchange fee will apply.') }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-muted dark:text-dark-subtle mb-1 uppercase tracking-wider">{{ __('Disbursement Purpose / Notes') }}</label>
                        <textarea name="notes" rows="3" placeholder="{{ __('e.g. Landlord net rental distribution for Meskel Flower Apartment - Oct 2026') }}" class="input w-full text-sm"></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-3 border-t border-warm/40 dark:border-warm/10">
                        <button type="button" @click="showRequestModal = false" class="btn btn-secondary text-sm">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary text-sm">{{ __('Submit Payout Request') }}</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Settle / Process Modal --}}
        <div x-show="showProcessModal"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">

            <div @click.away="showProcessModal = false"
                 class="bg-white dark:bg-dark-card rounded-2xl shadow-2xl border border-warm/50 dark:border-warm/20 max-w-md w-full p-6 space-y-5">
                <div class="flex items-center justify-between border-b border-warm/40 dark:border-warm/10 pb-4">
                    <div>
                        <h3 class="text-lg font-bold text-muted dark:text-white">{{ __('Execute Payout Settlement') }}</h3>
                        <p class="text-xs text-muted dark:text-dark-subtle mt-0.5" x-text="selectedPayoutRef + ' • ' + selectedPayoutAmount"></p>
                    </div>
                    <button type="button" @click="showProcessModal = false" class="text-muted hover:text-muted dark:hover:text-white">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form method="POST" :action="'{{ url('dashboard/finance/payouts') }}/' + selectedPayoutId + '/process'" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-muted dark:text-dark-subtle mb-1 uppercase tracking-wider">{{ __('Settlement Action / Status') }}</label>
                        <select name="status" required class="input w-full text-sm">
                            <option value="completed">{{ __('Approve & Mark Completed (Funds Sent)') }}</option>
                            <option value="processing">{{ __('Mark Processing (Transferred to Bank Queue)') }}</option>
                            <option value="failed">{{ __('Reject / Mark Failed (Invalid Account / KYC)') }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-muted dark:text-dark-subtle mb-1 uppercase tracking-wider">{{ __('Settlement Reference / Audit Notes') }}</label>
                        <input type="text" name="notes" placeholder="{{ __('e.g. CBE Core Banking FT #261024883719') }}" class="input w-full text-sm">
                    </div>

                    <div class="flex justify-end gap-3 pt-3 border-t border-warm/40 dark:border-warm/10">
                        <button type="button" @click="showProcessModal = false" class="btn btn-secondary text-sm">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary text-sm">{{ __('Execute Settlement') }}</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
