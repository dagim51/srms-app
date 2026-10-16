<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Transaction History') }}</x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="mb-8 flex justify-between items-end">
            <div>
                <h1 class="text-3xl font-extrabold font-display text-muted dark:text-white tracking-tight">{{ __('Transaction History') }}</h1>
                <p class="text-muted dark:text-muted mt-2">{{ __('Comprehensive log of all payment events.') }}</p>
            </div>
            <button class="btn btn-primary">{{ __('Process Refund') }}</button>
        </div>

        @include('frontend.finance.partials.tabs')

        <div class="card overflow-hidden">
            <div class="p-6 border-b border-[#EBDBC9] dark:border-[#273244] flex gap-4 flex-wrap">
                <input type="text" placeholder="{{ __('Search by ID, Reference, or Name...') }}" class="input w-full max-w-sm">
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
