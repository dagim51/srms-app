<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Invoices') }}</x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="mb-8 flex justify-between items-end">
            <div>
                <h1 class="text-3xl font-extrabold font-display text-muted dark:text-white tracking-tight">{{ __('Invoices') }}</h1>
                <p class="text-muted dark:text-muted mt-2">{{ __('Manage billing and receivables.') }}</p>
            </div>
            <button class="btn btn-primary">{{ __('Create Invoice') }}</button>
        </div>

        @include('frontend.finance.partials.tabs')

        <div class="card overflow-hidden">
            <div class="p-6 border-b border-[#EBDBC9] dark:border-[#273244] flex gap-4 flex-wrap">
                <input type="text" placeholder="{{ __('Search invoices...') }}" class="input w-full max-w-sm">
                <select class="input max-w-xs">
                    <option>{{ __('All Statuses') }}</option>
                    <option>{{ __('Paid') }}</option>
                    <option>{{ __('Draft') }}</option>
                    <option>{{ __('Sent') }}</option>
                    <option>{{ __('Overdue') }}</option>
                    <option>{{ __('Cancelled') }}</option>
                </select>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-warm-50/50 dark:bg-[#1E293B]/50 border-b border-[#EBDBC9] dark:border-[#273244]">
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Invoice ID') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Client') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Amount') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Date Issued') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Due Date') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EBDBC9]/50 dark:divide-[#273244]">
                        @foreach($invoices as $invoice)
                            <tr class="hover:bg-surface dark:hover:bg-surface/30 transition-colors">
                                <td class="p-4 font-bold text-muted dark:text-white">{{ $invoice['id'] }}</td>
                                <td class="p-4 text-muted dark:text-dark-subtle">{{ $invoice['client'] }}</td>
                                <td class="p-4 font-bold text-muted dark:text-white">{{ $invoice['amount'] }}</td>
                                <td class="p-4 text-sm text-muted dark:text-dark-subtle">{{ date('M d, Y', strtotime($invoice['date'])) }}</td>
                                <td class="p-4 text-sm {{ $invoice['status'] === 'overdue' ? 'text-error-600 font-bold' : 'text-muted dark:text-dark-subtle' }}">{{ date('M d, Y', strtotime($invoice['due_date'])) }}</td>
                                <td class="p-4">
                                    @php
                                        $sClass = 'badge-neutral';
                                        if ($invoice['status'] === 'paid') $sClass = 'badge-success';
                                        if ($invoice['status'] === 'overdue') $sClass = 'badge-error';
                                    @endphp
                                    <span class="badge {{ $sClass }} uppercase text-xs">{{ __($invoice['status']) }}</span>
                                </td>
                                <td class="p-4 text-right">
                                    <button class="text-primary-600 hover:text-primary-700 font-medium text-sm">{{ __('View') }}</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
