<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Invoices') }}</x-slot>

    <div x-data="{
        showCreateModal: false,
        showPayModal: false,
        payInvoiceId: null,
        payInvoiceCode: '',
        payAmount: ''
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
            title="Invoices & Billing"
            description="Manage client tax invoices, payment due dates, VAT summaries, and official receipt exports."
            icon="cash"
            badge="Invoices">
            <x-slot:actions>
                <button type="button" @click="showCreateModal = true" class="btn btn-primary flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    {{ __('Create Invoice') }}
                </button>
            </x-slot:actions>
        </x-page-header>

        @include('frontend.finance.partials.tabs')

        <div class="card overflow-hidden">
            <div class="p-6 border-b border-[#EBDBC9] dark:border-[#273244] flex gap-4 flex-wrap items-center justify-between">
                <form method="GET" action="{{ route('dashboard.finance.invoices') }}" class="flex items-center gap-3 flex-1 max-w-lg">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </div>
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="{{ __('Search invoices by ID, client or notes...') }}"
                               class="form-input search-input pl-9 pr-8 w-full text-sm">
                        @if(request('search'))
                            <a href="{{ route('dashboard.finance.invoices') }}" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </a>
                        @endif
                    </div>
                    <select name="status" onchange="this.form.submit()" class="input max-w-xs text-sm">
                        <option value="">{{ __('All Statuses') }}</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                        <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>{{ __('Paid') }}</option>
                        <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>{{ __('Overdue') }}</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>{{ __('Cancelled') }}</option>
                    </select>
                </form>
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
                        @forelse($invoices as $invoice)
                            <tr class="hover:bg-surface dark:hover:bg-surface/30 transition-colors">
                                <td class="p-4 font-bold text-muted dark:text-white font-mono text-sm">
                                    <a href="{{ route('dashboard.finance.invoices.show', $invoice['raw_id']) }}" class="hover:underline text-primary-600 dark:text-primary-400 font-mono font-bold">
                                        {{ $invoice['id'] }}
                                    </a>
                                </td>
                                <td class="p-4 text-muted dark:text-dark-subtle font-medium">{{ $invoice['client'] }}</td>
                                <td class="p-4 font-bold text-muted dark:text-white">{{ $invoice['amount'] }}</td>
                                <td class="p-4 text-sm text-muted dark:text-dark-subtle">{{ date('M d, Y', strtotime($invoice['date'])) }}</td>
                                <td class="p-4 text-sm {{ $invoice['status'] === 'overdue' ? 'text-error-600 font-bold' : 'text-muted dark:text-dark-subtle' }}">
                                    {{ date('M d, Y', strtotime($invoice['due_date'])) }}
                                </td>
                                <td class="p-4">
                                    @php
                                        $sClass = 'badge-neutral';
                                        if ($invoice['status'] === 'paid') $sClass = 'badge-success';
                                        if ($invoice['status'] === 'overdue') $sClass = 'badge-error';
                                        if ($invoice['status'] === 'pending') $sClass = 'badge-warning';
                                        if ($invoice['status'] === 'cancelled') $sClass = 'badge-neutral';
                                        if ($invoice['status'] === 'disputed') $sClass = 'badge-error';
                                    @endphp
                                    <span class="badge {{ $sClass }} uppercase text-xs">{{ __($invoice['status']) }}</span>
                                </td>
                                <td class="p-4 text-right space-x-2 whitespace-nowrap">
                                    <a href="{{ route('dashboard.finance.invoices.show', $invoice['raw_id']) }}"
                                       class="text-xs px-2.5 py-1 rounded-lg bg-surface dark:bg-surface/60 border border-[#EBDBC9] dark:border-[#273244] text-muted dark:text-dark-subtle font-semibold hover:border-primary-500 hover:text-primary-600 transition-colors inline-block">
                                        {{ __('View') }}
                                    </a>
                                    @if($invoice['status'] !== 'paid')
                                        <button type="button"
                                                @click="showPayModal = true; payInvoiceId = '{{ $invoice['raw_id'] }}'; payInvoiceCode = '{{ $invoice['id'] }}'; payAmount = '{{ $invoice['amount'] }}'"
                                                class="text-xs px-2.5 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 font-semibold hover:bg-emerald-100 transition-colors">
                                            {{ __('Mark Paid') }}
                                        </button>
                                    @else
                                        <span class="text-xs text-muted dark:text-dark-subtle italic">{{ __('Settled') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-8 text-center text-muted dark:text-dark-subtle">
                                    <div class="flex flex-col items-center justify-center space-y-2">
                                        <svg class="w-10 h-10 text-muted/50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <p class="text-sm font-medium">{{ __('No invoices matching the criteria.') }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($invoices, 'links'))
                <div class="p-4 border-t border-[#EBDBC9] dark:border-[#273244]">
                    {{ $invoices->links() }}
                </div>
            @endif
        </div>

        {{-- Create Invoice Modal --}}
        <div x-show="showCreateModal"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">

            <div @click.away="showCreateModal = false"
                 class="bg-white dark:bg-dark-card rounded-2xl shadow-2xl border border-warm/50 dark:border-warm/20 max-w-lg w-full p-6 space-y-5">
                <div class="flex items-center justify-between border-b border-warm/40 dark:border-warm/10 pb-4">
                    <h3 class="text-lg font-bold text-muted dark:text-white">{{ __('Generate Tax Invoice') }}</h3>
                    <button type="button" @click="showCreateModal = false" class="text-muted hover:text-muted dark:hover:text-white">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('dashboard.finance.invoices.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-muted dark:text-dark-subtle mb-1 uppercase tracking-wider">{{ __('Client / Debtor') }}</label>
                        <select name="user_id" required class="input w-full text-sm">
                            <option value="">{{ __('-- Select Client --') }}</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-muted dark:text-dark-subtle mb-1 uppercase tracking-wider">{{ __('Amount (ETB)') }}</label>
                            <input type="number" step="0.01" name="amount" min="1" required placeholder="e.g. 25000" class="input w-full text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-muted dark:text-dark-subtle mb-1 uppercase tracking-wider">{{ __('Payment Due Date') }}</label>
                            <input type="date" name="due_date" required value="{{ date('Y-m-d', strtotime('+14 days')) }}" class="input w-full text-sm">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-muted dark:text-dark-subtle mb-1 uppercase tracking-wider">{{ __('Invoice Notes / Purpose') }}</label>
                        <textarea name="notes" rows="3" placeholder="{{ __('e.g. Monthly maintenance retainer & utility allocation for Unit 4B') }}" class="input w-full text-sm"></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-3 border-t border-warm/40 dark:border-warm/10">
                        <button type="button" @click="showCreateModal = false" class="btn btn-secondary text-sm">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary text-sm">{{ __('Issue Invoice') }}</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Mark Paid Modal --}}
        <div x-show="showPayModal"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">

            <div @click.away="showPayModal = false"
                 class="bg-white dark:bg-dark-card rounded-2xl shadow-2xl border border-warm/50 dark:border-warm/20 max-w-md w-full p-6 space-y-5">
                <div class="flex items-center justify-between border-b border-warm/40 dark:border-warm/10 pb-4">
                    <div>
                        <h3 class="text-lg font-bold text-muted dark:text-white">{{ __('Record Invoice Settlement') }}</h3>
                        <p class="text-xs text-muted dark:text-dark-subtle mt-0.5" x-text="payInvoiceCode + ' (' + payAmount + ')'"></p>
                    </div>
                    <button type="button" @click="showPayModal = false" class="text-muted hover:text-muted dark:hover:text-white">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form method="POST" :action="'{{ url('dashboard/finance/invoices') }}/' + payInvoiceId + '/pay'" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-muted dark:text-dark-subtle mb-1 uppercase tracking-wider">{{ __('Payment Channel / Method') }}</label>
                        <select name="payment_method" required class="input w-full text-sm">
                            <option value="bank_transfer">{{ __('Commercial Bank Transfer (CBE)') }}</option>
                            <option value="telebirr">{{ __('Telebirr Gateway') }}</option>
                            <option value="cbe_birr">{{ __('CBE Birr Wallet') }}</option>
                            <option value="cash">{{ __('Cash / Bank Slip Receipt') }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-muted dark:text-dark-subtle mb-1 uppercase tracking-wider">{{ __('Payment Reference / Note') }}</label>
                        <input type="text" name="notes" placeholder="{{ __('e.g. Bank slip #CBE-988274 or Telebirr TX') }}" class="input w-full text-sm">
                    </div>

                    <div class="flex justify-end gap-3 pt-3 border-t border-warm/40 dark:border-warm/10">
                        <button type="button" @click="showPayModal = false" class="btn btn-secondary text-sm">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary text-sm">{{ __('Confirm & Mark Paid') }}</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
