<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Financial Summary') }}</x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="mb-8 flex justify-between items-end">
            <div>
                <h1 class="text-3xl font-extrabold font-display text-muted dark:text-white tracking-tight">{{ __('Financial Core') }}</h1>
                <p class="text-muted dark:text-muted mt-2">{{ __('Overview of revenue, payments, and receivables.') }}</p>
            </div>
            <div class="flex gap-2">
                <button class="btn btn-secondary flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    {{ __('Export Report') }}
                </button>
                <button class="btn btn-primary">{{ __('Process Payment') }}</button>
            </div>
        </div>

        @include('frontend.finance.partials.tabs')

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
            <x-card-stat
                :label="__('Total Revenue (YTD)')"
                :value="$stats['total_revenue']"
                :caption="__('+12% from last year')"
                :badge="__('YTD')"
                badgeVariant="success"
                iconColor="emerald">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </x-slot:icon>
            </x-card-stat>
            
            <x-card-stat
                :label="__('Monthly Recurring (MRR)')"
                :value="$stats['monthly_recurring']"
                :caption="__('+5% from last month')"
                :badge="__('MRR')"
                badgeVariant="brand"
                iconColor="orange">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
                </x-slot:icon>
            </x-card-stat>
            
            <x-card-stat
                :label="__('Pending Receivables')"
                :value="$stats['pending_receivables']"
                :caption="__('14 overdue invoices')"
                :badge="__('Pending')"
                badgeVariant="warning"
                iconColor="amber">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </x-slot:icon>
            </x-card-stat>
            
            <x-card-stat
                :label="__('Active Payouts')"
                :value="$stats['active_payouts']"
                :caption="__('3 pending transfers')"
                :badge="__('Payouts')"
                badgeVariant="info"
                iconColor="blue">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                </x-slot:icon>
            </x-card-stat>
        </div>

        <!-- Recent Transactions -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">{{ __('Recent Transactions') }}</h2>
                <a href="{{ route('dashboard.finance.transactions') }}" class="btn btn-ghost btn-sm text-orange-600 dark:text-orange-400 font-semibold">{{ __('View Ledger') }}</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface dark:bg-surface/50 border-b border-warm dark:border-warm">
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Transaction ID') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Date') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Type') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Amount') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Method') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider text-right">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-warm dark:divide-dark-border">
                        @foreach($recentTransactions as $trx)
                            <tr class="hover:bg-surface dark:hover:bg-surface/30 transition-colors">
                                <td class="p-4 font-bold text-muted dark:text-white">
                                    <a href="{{ route('dashboard.finance.transactions.show', $trx['id']) }}" class="hover:text-primary-600">{{ $trx['id'] }}</a>
                                </td>
                                <td class="p-4 text-muted dark:text-dark-subtle">{{ date('M d, Y', strtotime($trx['date'])) }}</td>
                                <td class="p-4 text-muted dark:text-dark-subtle">{{ $trx['type'] }}</td>
                                <td class="p-4 font-bold {{ str_starts_with($trx['amount'], '-') ? 'text-error-600' : 'text-muted dark:text-white' }}">{{ $trx['amount'] }}</td>
                                <td class="p-4">
                                    @php
                                        $sClass = 'badge-neutral';
                                        if ($trx['status'] === 'completed') $sClass = 'badge-success';
                                        if ($trx['status'] === 'processing') $sClass = 'badge-primary';
                                        if ($trx['status'] === 'failed') $sClass = 'badge-error';
                                        if ($trx['status'] === 'refunded') $sClass = 'badge-warning';
                                    @endphp
                                    <span class="badge {{ $sClass }} uppercase text-xs">{{ __($trx['status']) }}</span>
                                </td>
                                <td class="p-4 text-muted dark:text-dark-subtle">{{ $trx['method'] }}</td>
                                <td class="p-4 text-right">
                                    <a href="{{ route('dashboard.finance.transactions.show', $trx['id']) }}" class="btn btn-sm btn-secondary">{{ __('Details') }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
