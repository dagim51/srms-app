<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('General Ledger') }}</x-slot>

    <div x-data="{
        showEntryModal: false
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
            title="General Accounting Ledger"
            description="Double-entry journal entries, audited account balance movements, and general ledger reconciliation."
            icon="cash"
            badge="Ledger">
            <x-slot:actions>
                <div class="flex items-center gap-3">
                    <button type="button" @click="showEntryModal = true" class="btn btn-primary flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        {{ __('New Journal Entry') }}
                    </button>
                </div>
            </x-slot:actions>
        </x-page-header>

        @include('frontend.finance.partials.tabs')

        <div class="card overflow-hidden">
            <div class="p-6 border-b border-[#EBDBC9] dark:border-[#273244]">
                <form method="GET" action="{{ route('dashboard.finance.ledger') }}" class="flex flex-wrap items-center gap-4">
                    <div class="flex-1 min-w-[200px] max-w-sm">
                        <select name="account_id" onchange="this.form.submit()" class="input w-full text-sm">
                            <option value="">{{ __('All Chart of Accounts') }}</option>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}" {{ request('account_id') == $acc->id ? 'selected' : '' }}>
                                    {{ $acc->code ? $acc->code . ' - ' : '' }}{{ $acc->name }} ({{ ucfirst($acc->type ?? 'Asset') }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @if(request('account_id'))
                        <a href="{{ route('dashboard.finance.ledger') }}" class="text-xs text-orange-600 hover:text-orange-700 dark:text-orange-400 font-medium">
                            {{ __('Clear Filter') }}
                        </a>
                    @endif
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse font-mono text-sm">
                    <thead>
                        <tr class="bg-warm-50/50 dark:bg-[#1E293B]/50 border-b border-[#EBDBC9] dark:border-[#273244]">
                            <th class="p-4 font-bold text-muted dark:text-muted uppercase tracking-wider text-xs">{{ __('Entry ID') }}</th>
                            <th class="p-4 font-bold text-muted dark:text-muted uppercase tracking-wider text-xs">{{ __('Date') }}</th>
                            <th class="p-4 font-bold text-muted dark:text-muted uppercase tracking-wider text-xs">{{ __('Account') }}</th>
                            <th class="p-4 font-bold text-muted dark:text-muted uppercase tracking-wider text-xs">{{ __('Description') }}</th>
                            <th class="p-4 font-bold text-muted dark:text-muted uppercase tracking-wider text-right text-xs">{{ __('Debit (ETB)') }}</th>
                            <th class="p-4 font-bold text-muted dark:text-muted uppercase tracking-wider text-right text-xs">{{ __('Credit (ETB)') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EBDBC9]/50 dark:divide-[#273244]">
                        @forelse($entries as $entry)
                            <tr class="hover:bg-surface dark:hover:bg-surface/30 transition-colors">
                                <td class="p-4 text-muted dark:text-white font-semibold">{{ $entry['id'] }}</td>
                                <td class="p-4 text-muted dark:text-dark-subtle">{{ date('Y-m-d', strtotime($entry['date'])) }}</td>
                                <td class="p-4 font-bold text-primary-600 dark:text-primary-400 font-sans">{{ $entry['account'] }}</td>
                                <td class="p-4 text-muted dark:text-dark-subtle font-sans">{{ $entry['description'] }}</td>
                                <td class="p-4 text-right {{ $entry['debit'] !== '0' ? 'text-muted dark:text-white font-bold' : 'text-muted/40' }}">{{ $entry['debit'] }}</td>
                                <td class="p-4 text-right {{ $entry['credit'] !== '0' ? 'text-muted dark:text-white font-bold' : 'text-muted/40' }}">{{ $entry['credit'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-muted dark:text-dark-subtle font-sans">
                                    <p class="text-sm">{{ __('No ledger transaction lines recorded.') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-surface dark:bg-surface/50 border-t-2 border-warm dark:border-warm">
                        <tr>
                            <td colspan="4" class="p-4 text-right font-bold text-muted dark:text-white uppercase font-sans text-xs">{{ __('Reconciled Ledger Totals:') }}</td>
                            <td class="p-4 text-right font-black text-muted dark:text-white">{{ number_format($totalDebit ?? 0) }}</td>
                            <td class="p-4 text-right font-black text-muted dark:text-white">{{ number_format($totalCredit ?? 0) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if(method_exists($entries, 'links'))
                <div class="p-4 border-t border-[#EBDBC9] dark:border-[#273244]">
                    {{ $entries->links() }}
                </div>
            @endif
        </div>

        {{-- New Journal Entry Modal --}}
        <div x-show="showEntryModal"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">

            <div @click.away="showEntryModal = false"
                 class="bg-white dark:bg-dark-card rounded-2xl shadow-2xl border border-warm/50 dark:border-warm/20 max-w-lg w-full p-6 space-y-5">
                <div class="flex items-center justify-between border-b border-warm/40 dark:border-warm/10 pb-4">
                    <h3 class="text-lg font-bold text-muted dark:text-white">{{ __('Post Double-Entry Journal Adjustment') }}</h3>
                    <button type="button" @click="showEntryModal = false" class="text-muted hover:text-muted dark:hover:text-white">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('dashboard.finance.ledger.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-muted dark:text-dark-subtle mb-1 uppercase tracking-wider">{{ __('Transaction Description / Narration') }}</label>
                        <input type="text" name="description" required placeholder="{{ __('e.g. Escrow deposit reclassification or VAT correction') }}" class="input w-full text-sm">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-muted dark:text-dark-subtle mb-1 uppercase tracking-wider">{{ __('Debit Account (+DR)') }}</label>
                            <select name="debit_account_id" required class="input w-full text-sm">
                                <option value="">{{ __('-- Select Account --') }}</option>
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->name }} ({{ $acc->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-muted dark:text-dark-subtle mb-1 uppercase tracking-wider">{{ __('Credit Account (-CR)') }}</label>
                            <select name="credit_account_id" required class="input w-full text-sm">
                                <option value="">{{ __('-- Select Account --') }}</option>
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->name }} ({{ $acc->code }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-muted dark:text-dark-subtle mb-1 uppercase tracking-wider">{{ __('Amount (ETB)') }}</label>
                            <input type="number" step="0.01" name="amount" min="1" required placeholder="e.g. 15000" class="input w-full text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-muted dark:text-dark-subtle mb-1 uppercase tracking-wider">{{ __('Effective Entry Date') }}</label>
                            <input type="date" name="entry_date" value="{{ date('Y-m-d') }}" class="input w-full text-sm">
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-3 border-t border-warm/40 dark:border-warm/10">
                        <button type="button" @click="showEntryModal = false" class="btn btn-secondary text-sm">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary text-sm">{{ __('Post Entry') }}</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
