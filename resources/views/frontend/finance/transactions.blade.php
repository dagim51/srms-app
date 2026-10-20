<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Transaction History') }}</x-slot>

    <div class="max-w-7xl mx-auto page-enter space-y-6">
        <x-page-header
            title="Financial Transactions"
            description="Comprehensive audit log of all inbound deposits, outbound disbursements, escrow releases, and refunds."
            icon="cash"
            badge="Transactions">
            <x-slot:actions>
                <button class="btn btn-primary">{{ __('Process Refund') }}</button>
            </x-slot:actions>
        </x-page-header>

        @include('frontend.finance.partials.tabs')

        <div class="card overflow-hidden">
            <div class="p-6 border-b border-[#EBDBC9] dark:border-[#273244] flex gap-4 flex-wrap items-center">
                <form method="GET" action="{{ route('dashboard.finance.transactions') }}" class="flex items-center gap-2 flex-1 max-w-sm">
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
                    @if(request('search'))
                        <a href="{{ route('dashboard.finance.transactions') }}" class="text-xs text-orange-600 hover:text-orange-700 dark:text-orange-400 font-medium whitespace-nowrap">{{ __('Reset') }}</a>
                    @endif
                </form>
                <select class="input max-w-xs">
                    <option>{{ __('All Types') }}</option>
                    <option>{{ __('Sale Installment') }}</option>
                    <option>{{ __('Rent Payment') }}</option>
                    <option>{{ __('Refund') }}</option>
                    <option>{{ __('Payout') }}</option>
                </select>
                <select class="input max-w-xs">
                    <option>{{ __('All Statuses') }}</option>
                    <option>{{ __('Completed') }}</option>
                    <option>{{ __('Processing') }}</option>
                    <option>{{ __('Pending') }}</option>
                    <option>{{ __('Failed') }}</option>
                    <option>{{ __('Refunded') }}</option>
                </select>
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
                        @foreach($transactions as $trx)
                            <tr class="hover:bg-surface dark:hover:bg-surface/30 transition-colors">
                                <td class="p-4">
                                    <p class="font-bold text-muted dark:text-white">
                                        <a href="{{ route('dashboard.finance.transactions.show', $trx['id']) }}" class="hover:text-primary-600">{{ $trx['id'] }}</a>
                                    </p>
                                    <p class="text-xs text-muted mt-0.5">{{ $trx['reference'] }} &bull; {{ $trx['type'] }}</p>
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
                                <td class="p-4 text-right">
                                    <a href="{{ route('dashboard.finance.transactions.show', $trx['id']) }}" class="text-primary-600 hover:text-primary-700 font-medium text-sm">{{ __('View Receipt') }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
