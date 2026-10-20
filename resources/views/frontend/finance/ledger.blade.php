<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('General Ledger') }}</x-slot>

    <div class="max-w-7xl mx-auto page-enter space-y-6">
        <x-page-header
            title="General Accounting Ledger"
            description="Double-entry journal entries, audited account balance movements, and general ledger reconciliation."
            icon="cash"
            badge="Ledger">
            <x-slot:actions>
                <button class="btn btn-secondary flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    {{ __('Export CSV') }}
                </button>
            </x-slot:actions>
        </x-page-header>

        @include('frontend.finance.partials.tabs')

        <div class="card overflow-hidden">
            <div class="p-6 border-b border-[#EBDBC9] dark:border-[#273244] flex flex-wrap gap-4">
                <input type="date" class="input">
                <input type="date" class="input">
                <select class="input max-w-xs">
                    <option>{{ __('All Accounts') }}</option>
                    <option>{{ __('Cash - Commercial Bank') }}</option>
                    <option>{{ __('Accounts Receivable') }}</option>
                    <option>{{ __('Refunds Payable') }}</option>
                    <option>{{ __('Sales Revenue') }}</option>
                    <option>{{ __('Tax Payable') }}</option>
                </select>
                <button class="btn btn-primary">{{ __('Filter') }}</button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse font-mono text-sm">
                    <thead>
                        <tr class="bg-warm-50/50 dark:bg-[#1E293B]/50 border-b border-[#EBDBC9] dark:border-[#273244]">
                            <th class="p-4 font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Entry ID') }}</th>
                            <th class="p-4 font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Date') }}</th>
                            <th class="p-4 font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Account') }}</th>
                            <th class="p-4 font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Description') }}</th>
                            <th class="p-4 font-bold text-muted dark:text-muted uppercase tracking-wider text-right">{{ __('Debit') }}</th>
                            <th class="p-4 font-bold text-muted dark:text-muted uppercase tracking-wider text-right">{{ __('Credit') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EBDBC9]/50 dark:divide-[#273244]">
                        @foreach($entries as $entry)
                            <tr class="hover:bg-surface dark:hover:bg-surface/30 transition-colors">
                                <td class="p-4 text-muted dark:text-white">{{ $entry['id'] }}</td>
                                <td class="p-4 text-muted dark:text-dark-subtle">{{ date('Y-m-d', strtotime($entry['date'])) }}</td>
                                <td class="p-4 font-bold text-primary-600">{{ $entry['account'] }}</td>
                                <td class="p-4 text-muted dark:text-dark-subtle">{{ $entry['description'] }}</td>
                                <td class="p-4 text-right {{ $entry['debit'] !== '0' ? 'text-muted dark:text-white font-bold' : 'text-muted' }}">{{ $entry['debit'] }}</td>
                                <td class="p-4 text-right {{ $entry['credit'] !== '0' ? 'text-muted dark:text-white font-bold' : 'text-muted' }}">{{ $entry['credit'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-surface dark:bg-surface/50 border-t-2 border-warm dark:border-warm">
                        <tr>
                            <td colspan="4" class="p-4 text-right font-bold text-muted dark:text-white uppercase">{{ __('Totals:') }}</td>
                            <td class="p-4 text-right font-black text-muted dark:text-white">{{ __('4,240,000') }}</td>
                            <td class="p-4 text-right font-black text-muted dark:text-white">{{ __('4,240,000') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
