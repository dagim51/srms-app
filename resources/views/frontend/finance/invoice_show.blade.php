<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Invoice') }}: {{ $invoiceData['id'] }}</x-slot>

    <div x-data="{
        showDecisionModal: false,
        selectedDecision: '{{ $invoiceData['status'] === 'paid' ? 'approve' : 'pay' }}',
        paymentMethod: 'bank_transfer',
        decisionNotes: ''
    }" class="max-w-7xl mx-auto page-enter space-y-6">

        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-sm flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-sm flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-rose-600 dark:text-rose-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        @endif

        <x-page-header
            :title="__('Tax Invoice: ') . $invoiceData['id']"
            :description="$invoiceData['client_name'] . ' • ' . __('Due:') . ' ' . date('M d, Y', strtotime($invoiceData['due_date'])) . ' • ' . __('Status:') . ' ' . __($invoiceData['status'])"
            icon="cash"
            :badge="$invoiceData['status']">
            <x-slot:actions>
                <div class="flex items-center gap-2 flex-wrap">
                    <a href="{{ route('dashboard.finance.invoices') }}" class="btn btn-secondary text-xs flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        {{ __('Back to Invoices') }}
                    </a>
                    <button class="btn btn-secondary text-xs flex items-center gap-1.5" onclick="window.print()">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        {{ __('Print Invoice') }}
                    </button>
                    @if($invoiceData['status'] !== 'paid')
                        <button type="button"
                                @click="showDecisionModal = true; selectedDecision = 'pay'"
                                class="btn btn-primary text-xs flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white border-0 shadow-sm">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ __('Record Settlement') }}
                        </button>
                    @endif
                </div>
            </x-slot:actions>
        </x-page-header>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Main Invoice Document --}}
            <div class="lg:col-span-2 space-y-8">
                <!-- Official Tax Invoice Card -->
                <div class="card p-6 md:p-8 bg-surface dark:bg-[#1E293B]/70 border border-[#EBDBC9] dark:border-[#273244] shadow-sm relative overflow-hidden">
                    <div class="absolute -right-8 -top-8 w-32 h-32 bg-primary-500/5 dark:bg-primary-500/10 rounded-full blur-2xl pointer-events-none"></div>

                    <!-- Header -->
                    <div class="flex flex-col sm:flex-row justify-between items-start gap-4 pb-6 border-b border-[#EBDBC9] dark:border-[#273244]">
                        <div>
                            <span class="inline-block px-2.5 py-1 rounded-md text-xs font-bold uppercase tracking-wider bg-primary-100 dark:bg-primary-950/50 text-primary-700 dark:text-primary-300 mb-2">
                                {{ __('Official Tax Invoice') }}
                            </span>
                            <h2 class="text-2xl font-black text-muted dark:text-white font-mono tracking-tight">{{ $invoiceData['id'] }}</h2>
                            <p class="text-xs text-muted/70 dark:text-dark-subtle mt-0.5">SRMS Financial Clearing & Settlement Service</p>
                        </div>
                        <div class="text-right sm:text-right">
                            <div class="font-bold text-xs uppercase tracking-wider text-muted/60 dark:text-dark-subtle">{{ __('Status') }}</div>
                            @php
                                $statusBadge = 'badge-neutral';
                                if ($invoiceData['status'] === 'paid') $statusBadge = 'badge-success';
                                elseif ($invoiceData['status'] === 'overdue') $statusBadge = 'badge-error';
                                elseif ($invoiceData['status'] === 'pending') $statusBadge = 'badge-warning';
                                elseif ($invoiceData['status'] === 'disputed') $statusBadge = 'badge-error';
                            @endphp
                            <span class="badge {{ $statusBadge }} text-sm font-bold uppercase mt-1 px-3 py-1">
                                {{ __($invoiceData['status']) }}
                            </span>
                        </div>
                    </div>

                    <!-- Invoice Metadata Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 my-6 py-4 bg-warm-50/50 dark:bg-[#182234]/40 rounded-xl p-4 border border-[#EBDBC9]/60 dark:border-[#273244]/60">
                        <div>
                            <span class="text-xs font-semibold text-muted/70 dark:text-dark-subtle uppercase tracking-wider block">{{ __('Billed To') }}</span>
                            <span class="font-bold text-muted dark:text-white text-base mt-1 block">{{ $invoiceData['client_name'] }}</span>
                            <span class="text-xs text-muted/70 dark:text-dark-subtle block">{{ $invoiceData['client_email'] }}</span>
                            <span class="text-[11px] font-mono text-muted/50 dark:text-dark-subtle/70 block mt-0.5 truncate max-w-[200px]">ID: {{ $invoiceData['client_id'] }}</span>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-muted/70 dark:text-dark-subtle uppercase tracking-wider block">{{ __('Date Issued') }}</span>
                            <span class="font-medium text-muted dark:text-white text-sm mt-1 block">{{ date('F d, Y', strtotime($invoiceData['date'])) }}</span>
                            <span class="text-xs font-semibold text-muted/70 dark:text-dark-subtle uppercase tracking-wider block mt-3">{{ __('Payment Due Date') }}</span>
                            <span class="font-bold text-sm mt-0.5 block {{ $invoiceData['status'] === 'overdue' ? 'text-rose-600 dark:text-rose-400' : 'text-muted dark:text-white' }}">
                                {{ date('F d, Y', strtotime($invoiceData['due_date'])) }}
                            </span>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-muted/70 dark:text-dark-subtle uppercase tracking-wider block">{{ __('Tax Entity & Jurisdiction') }}</span>
                            <span class="text-xs text-muted dark:text-white font-medium mt-1 block">FDRE Ministry of Revenues</span>
                            <span class="text-xs text-muted/70 dark:text-dark-subtle block">Standard VAT: 15.00%</span>
                            <span class="text-xs text-muted/70 dark:text-dark-subtle block">Currency: ETB (Ethiopian Birr)</span>
                        </div>
                    </div>

                    <!-- Items / Breakdown Table -->
                    <div class="mt-6">
                        <h3 class="text-xs font-bold text-muted/70 dark:text-dark-subtle uppercase tracking-wider mb-3">{{ __('Invoice Items & Tax Assessment') }}</h3>
                        <div class="overflow-x-auto border border-[#EBDBC9] dark:border-[#273244] rounded-xl overflow-hidden">
                            <table class="w-full text-left border-collapse text-sm">
                                <thead>
                                    <tr class="bg-warm-100/60 dark:bg-[#1E293B] border-b border-[#EBDBC9] dark:border-[#273244]">
                                        <th class="p-3 text-xs font-bold text-muted dark:text-muted uppercase">{{ __('Item Description') }}</th>
                                        <th class="p-3 text-xs font-bold text-muted dark:text-muted uppercase text-right">{{ __('Rate / VAT') }}</th>
                                        <th class="p-3 text-xs font-bold text-muted dark:text-muted uppercase text-right">{{ __('Net Amount') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#EBDBC9]/50 dark:divide-[#273244]">
                                    <tr class="hover:bg-warm-50/30 dark:hover:bg-[#1E293B]/30">
                                        <td class="p-3.5">
                                            <p class="font-bold text-muted dark:text-white">Financial & Property Services Clearing</p>
                                            <p class="text-xs text-muted/70 dark:text-dark-subtle mt-0.5">{{ $invoiceData['notes'] }}</p>
                                        </td>
                                        <td class="p-3.5 text-right font-medium text-muted dark:text-dark-subtle">15% Standard VAT</td>
                                        <td class="p-3.5 text-right font-mono font-medium text-muted dark:text-white">{{ $invoiceData['subtotal'] }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Calculation Summary -->
                    <div class="mt-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 pt-4 border-t border-[#EBDBC9] dark:border-[#273244]">
                        <div class="text-xs text-muted/70 dark:text-dark-subtle max-w-sm space-y-1">
                            <p class="font-medium text-muted dark:text-slate-300">Notes & Terms:</p>
                            <p>{{ $invoiceData['notes'] }}</p>
                        </div>
                        <div class="w-full sm:w-64 space-y-2 text-sm bg-warm-50/40 dark:bg-[#182234]/40 p-4 rounded-xl border border-[#EBDBC9]/50 dark:border-[#273244]/50">
                            <div class="flex justify-between items-center text-muted/70 dark:text-dark-subtle">
                                <span>{{ __('Net Subtotal') }}:</span>
                                <span class="font-mono">{{ $invoiceData['subtotal'] }}</span>
                            </div>
                            <div class="flex justify-between items-center text-muted/70 dark:text-dark-subtle">
                                <span>{{ __('Tax (15% VAT)') }}:</span>
                                <span class="font-mono">{{ $invoiceData['tax'] }}</span>
                            </div>
                            <div class="border-t border-[#EBDBC9] dark:border-[#273244] pt-2 flex justify-between items-center font-bold text-base text-muted dark:text-white">
                                <span>{{ __('Total Due') }}:</span>
                                <span class="text-primary-600 dark:text-primary-400 font-black text-lg">{{ $invoiceData['amount'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Associated Settlement Payments -->
                <div class="card p-6 border border-[#EBDBC9] dark:border-[#273244]">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                            <h3 class="card-title text-lg">{{ __('Settlement Payments') }}</h3>
                        </div>
                        <span class="text-xs text-muted/60 dark:text-dark-subtle">{{ count($payments) }} {{ __('payment record(s)') }}</span>
                    </div>

                    @if(count($payments) > 0)
                        <div class="overflow-x-auto border border-[#EBDBC9] dark:border-[#273244] rounded-xl overflow-hidden">
                            <table class="w-full text-left border-collapse text-sm">
                                <thead>
                                    <tr class="bg-warm-100/60 dark:bg-[#1E293B] border-b border-[#EBDBC9] dark:border-[#273244]">
                                        <th class="p-3 text-xs font-bold text-muted dark:text-muted uppercase">{{ __('Reference') }}</th>
                                        <th class="p-3 text-xs font-bold text-muted dark:text-muted uppercase">{{ __('Method') }}</th>
                                        <th class="p-3 text-xs font-bold text-muted dark:text-muted uppercase">{{ __('Amount') }}</th>
                                        <th class="p-3 text-xs font-bold text-muted dark:text-muted uppercase">{{ __('Date') }}</th>
                                        <th class="p-3 text-xs font-bold text-muted dark:text-muted uppercase">{{ __('Status') }}</th>
                                        <th class="p-3 text-xs font-bold text-muted dark:text-muted uppercase text-right">{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#EBDBC9]/50 dark:divide-[#273244]">
                                    @foreach($payments as $p)
                                        <tr class="hover:bg-warm-50/30 dark:hover:bg-[#1E293B]/30">
                                            <td class="p-3 font-mono font-bold text-primary-600 dark:text-primary-400">
                                                {{ $p->transaction_reference ?? 'PAY-'.strtoupper(substr($p->id, 0, 8)) }}
                                            </td>
                                            <td class="p-3 uppercase text-xs font-medium text-muted dark:text-dark-subtle">{{ $p->payment_method ?? 'Bank' }}</td>
                                            <td class="p-3 font-bold text-muted dark:text-white">{{ number_format($p->amount, 2) }} {{ $p->currency ?? 'ETB' }}</td>
                                            <td class="p-3 text-xs text-muted dark:text-dark-subtle">{{ $p->created_at ? $p->created_at->format('M d, Y H:i') : 'N/A' }}</td>
                                            <td class="p-3">
                                                <span class="badge {{ $p->status === 'completed' ? 'badge-success' : 'badge-warning' }} uppercase text-xs">
                                                    {{ $p->status }}
                                                </span>
                                            </td>
                                            <td class="p-3 text-right">
                                                <a href="{{ route('dashboard.finance.transactions.show', $p->transaction_reference ?? $p->id) }}" class="text-xs font-semibold text-primary-600 dark:text-primary-400 hover:underline">
                                                    {{ __('View Receipt') }} &rarr;
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-6 text-center bg-warm-50/30 dark:bg-[#182234]/30 rounded-xl border border-dashed border-[#EBDBC9] dark:border-[#273244]">
                            <svg class="w-8 h-8 text-muted/40 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-sm font-medium text-muted dark:text-dark-subtle">{{ __('No settlement payments recorded yet for this invoice.') }}</p>
                            @if($invoiceData['status'] !== 'paid')
                                <button type="button" @click="showDecisionModal = true; selectedDecision = 'pay'" class="mt-3 btn btn-primary text-xs py-1.5 px-3">
                                    {{ __('Record First Settlement') }}
                                </button>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            {{-- Right Sidebar: Decision Panel & Audit Log --}}
            <div class="space-y-6">
                <!-- Financial Decision Card -->
                <div class="card p-6 border-2 border-primary-500/20 dark:border-primary-500/30 shadow-md">
                    <div class="flex items-center gap-2 mb-4 pb-3 border-b border-[#EBDBC9] dark:border-[#273244]">
                        <div class="w-8 h-8 rounded-lg bg-primary-100 dark:bg-primary-950/60 text-primary-600 dark:text-primary-400 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-base text-muted dark:text-white">{{ __('Financial Decision Panel') }}</h3>
                            <p class="text-xs text-muted/70 dark:text-dark-subtle">{{ __('Governance & Status Management') }}</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('dashboard.finance.invoices.decision', $invoiceData['raw_id']) }}" class="space-y-4">
                        @csrf

                        <div>
                            <label class="block text-xs font-bold text-muted/70 dark:text-dark-subtle uppercase tracking-wider mb-2">
                                {{ __('Select Decision Action') }}
                            </label>
                            <div class="grid grid-cols-1 gap-2">
                                <label class="flex items-start gap-3 p-3 rounded-xl border cursor-pointer transition-colors"
                                       :class="selectedDecision === 'approve' ? 'border-primary-500 bg-primary-50/50 dark:bg-primary-950/20 text-primary-900 dark:text-primary-100' : 'border-[#EBDBC9] dark:border-[#273244] hover:bg-warm-50 dark:hover:bg-[#182234]'">
                                    <input type="radio" name="decision" value="approve" x-model="selectedDecision" class="mt-0.5 text-primary-600 focus:ring-primary-500">
                                    <div>
                                        <div class="text-sm font-bold">{{ __('Approve & Issue') }}</div>
                                        <div class="text-xs text-muted/70 dark:text-dark-subtle">{{ __('Validate invoice terms and confirm active pending status for billing.') }}</div>
                                    </div>
                                </label>

                                <label class="flex items-start gap-3 p-3 rounded-xl border cursor-pointer transition-colors"
                                       :class="selectedDecision === 'pay' ? 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/20 text-emerald-900 dark:text-emerald-100' : 'border-[#EBDBC9] dark:border-[#273244] hover:bg-warm-50 dark:hover:bg-[#182234]'">
                                    <input type="radio" name="decision" value="pay" x-model="selectedDecision" class="mt-0.5 text-emerald-600 focus:ring-emerald-500">
                                    <div>
                                        <div class="text-sm font-bold text-emerald-700 dark:text-emerald-300">{{ __('Record Settlement / Mark Paid') }}</div>
                                        <div class="text-xs text-muted/70 dark:text-dark-subtle">{{ __('Mark invoice settled and create double-entry payment transaction.') }}</div>
                                    </div>
                                </label>

                                <label class="flex items-start gap-3 p-3 rounded-xl border cursor-pointer transition-colors"
                                       :class="selectedDecision === 'dispute' ? 'border-amber-500 bg-amber-50/50 dark:bg-amber-950/20 text-amber-900 dark:text-amber-100' : 'border-[#EBDBC9] dark:border-[#273244] hover:bg-warm-50 dark:hover:bg-[#182234]'">
                                    <input type="radio" name="decision" value="dispute" x-model="selectedDecision" class="mt-0.5 text-amber-600 focus:ring-amber-500">
                                    <div>
                                        <div class="text-sm font-bold text-amber-700 dark:text-amber-300">{{ __('Flag Dispute / Review') }}</div>
                                        <div class="text-xs text-muted/70 dark:text-dark-subtle">{{ __('Hold settlement pending financial audit or client inquiry review.') }}</div>
                                    </div>
                                </label>

                                <label class="flex items-start gap-3 p-3 rounded-xl border cursor-pointer transition-colors"
                                       :class="selectedDecision === 'cancel' ? 'border-rose-500 bg-rose-50/50 dark:bg-rose-950/20 text-rose-900 dark:text-rose-100' : 'border-[#EBDBC9] dark:border-[#273244] hover:bg-warm-50 dark:hover:bg-[#182234]'">
                                    <input type="radio" name="decision" value="cancel" x-model="selectedDecision" class="mt-0.5 text-rose-600 focus:ring-rose-500">
                                    <div>
                                        <div class="text-sm font-bold text-rose-700 dark:text-rose-300">{{ __('Void / Cancel Invoice') }}</div>
                                        <div class="text-xs text-muted/70 dark:text-dark-subtle">{{ __('Revoke this invoice from receivables and invalidate due amount.') }}</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Payment Method (shown if decision is pay) -->
                        <div x-show="selectedDecision === 'pay'" class="space-y-1.5 pt-2" x-transition>
                            <label class="block text-xs font-bold text-muted/70 dark:text-dark-subtle uppercase tracking-wider">
                                {{ __('Payment Gateway / Method') }} <span class="text-rose-500">*</span>
                            </label>
                            <select name="payment_method" x-model="paymentMethod" class="input w-full text-sm">
                                <option value="bank_transfer">{{ __('Commercial Bank of Ethiopia (Bank Transfer)') }}</option>
                                <option value="cbe_birr">{{ __('CBE Birr Mobile') }}</option>
                                <option value="telebirr">{{ __('Telebirr SuperApp') }}</option>
                                <option value="cash">{{ __('Cash / Physical Receipt') }}</option>
                            </select>
                        </div>

                        <!-- Decision Notes -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-muted/70 dark:text-dark-subtle uppercase tracking-wider">
                                {{ __('Decision Rationale / Audit Notes') }}
                            </label>
                            <textarea name="decision_notes"
                                      rows="3"
                                      placeholder="{{ __('State reasons, transaction references or review findings...') }}"
                                      class="input w-full text-sm"></textarea>
                        </div>

                        <div class="pt-2">
                            <button type="submit"
                                    class="w-full btn btn-primary py-2.5 flex items-center justify-center gap-2 font-bold shadow-md">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>{{ __('Apply Decision') }}</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Timeline & Governance History -->
                <div class="card p-6 border border-[#EBDBC9] dark:border-[#273244]">
                    <h3 class="font-bold text-sm text-muted dark:text-white mb-4 uppercase tracking-wider text-xs">
                        {{ __('Lifecycle & Audit Trail') }}
                    </h3>
                    <div class="relative pl-6 space-y-6 before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-[#EBDBC9] dark:before:bg-[#273244]">
                        <div class="relative">
                            <div class="absolute -left-[1.65rem] top-1 w-3 h-3 rounded-full bg-primary-500 ring-4 ring-primary-100 dark:ring-primary-950"></div>
                            <div class="text-xs font-bold text-muted dark:text-white">{{ __('Invoice Created') }}</div>
                            <div class="text-[11px] text-muted/60 dark:text-dark-subtle">{{ date('M d, Y H:i', strtotime($invoiceData['date'])) }}</div>
                            <div class="text-xs text-muted/80 dark:text-dark-subtle mt-1">{{ __('Generated for client:') }} {{ $invoiceData['client_name'] }}</div>
                        </div>

                        <div class="relative">
                            <div class="absolute -left-[1.65rem] top-1 w-3 h-3 rounded-full {{ $invoiceData['status'] === 'overdue' ? 'bg-rose-500' : 'bg-amber-400' }} ring-4 ring-amber-100 dark:ring-amber-950"></div>
                            <div class="text-xs font-bold text-muted dark:text-white">{{ __('Payment Due Date') }}</div>
                            <div class="text-[11px] text-muted/60 dark:text-dark-subtle">{{ date('M d, Y', strtotime($invoiceData['due_date'])) }}</div>
                        </div>

                        @if($invoiceData['status'] === 'paid')
                            <div class="relative">
                                <div class="absolute -left-[1.65rem] top-1 w-3 h-3 rounded-full bg-emerald-500 ring-4 ring-emerald-100 dark:ring-emerald-950"></div>
                                <div class="text-xs font-bold text-emerald-600 dark:text-emerald-400">{{ __('Settled & Paid') }}</div>
                                <div class="text-[11px] text-muted/60 dark:text-dark-subtle">{{ count($payments) > 0 && $payments[0]->created_at ? $payments[0]->created_at->format('M d, Y H:i') : __('Settlement completed') }}</div>
                            </div>
                        @elseif($invoiceData['status'] === 'cancelled')
                            <div class="relative">
                                <div class="absolute -left-[1.65rem] top-1 w-3 h-3 rounded-full bg-rose-500 ring-4 ring-rose-100 dark:ring-rose-950"></div>
                                <div class="text-xs font-bold text-rose-600 dark:text-rose-400">{{ __('Voided / Cancelled') }}</div>
                                <div class="text-[11px] text-muted/60 dark:text-dark-subtle">{{ __('Invoice marked void by financial authority') }}</div>
                            </div>
                        @elseif($invoiceData['status'] === 'disputed')
                            <div class="relative">
                                <div class="absolute -left-[1.65rem] top-1 w-3 h-3 rounded-full bg-amber-500 ring-4 ring-amber-100 dark:ring-amber-950"></div>
                                <div class="text-xs font-bold text-amber-600 dark:text-amber-400">{{ __('Dispute Under Review') }}</div>
                                <div class="text-[11px] text-muted/60 dark:text-dark-subtle">{{ __('Pending resolution by Finance Officer') }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Settlement Modal --}}
        <div x-show="showDecisionModal"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <div class="card max-w-md w-full p-6 space-y-4 shadow-2xl bg-surface dark:bg-[#1E293B]" @click.outside="showDecisionModal = false">
                <div class="flex justify-between items-center pb-3 border-b border-[#EBDBC9] dark:border-[#273244]">
                    <h3 class="font-bold text-lg text-muted dark:text-white">{{ __('Confirm Financial Decision') }}</h3>
                    <button type="button" @click="showDecisionModal = false" class="text-muted/60 hover:text-muted dark:hover:text-white">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('dashboard.finance.invoices.decision', $invoiceData['raw_id']) }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="decision" :value="selectedDecision">

                    <div class="p-3 bg-warm-50 dark:bg-[#182234] rounded-xl text-xs space-y-1">
                        <div class="font-semibold text-muted dark:text-white">{{ __('Invoice:') }} <span class="font-mono text-primary-600">{{ $invoiceData['id'] }}</span></div>
                        <div class="text-muted/70 dark:text-dark-subtle">{{ __('Amount:') }} <span class="font-bold text-muted dark:text-white">{{ $invoiceData['amount'] }}</span></div>
                    </div>

                    <div x-show="selectedDecision === 'pay'" class="space-y-1">
                        <label class="block text-xs font-bold text-muted/70 dark:text-dark-subtle uppercase">{{ __('Payment Method') }}</label>
                        <select name="payment_method" class="input w-full text-sm">
                            <option value="bank_transfer">{{ __('Bank Transfer (CBE / Awash / Dashen)') }}</option>
                            <option value="telebirr">{{ __('Telebirr SuperApp') }}</option>
                            <option value="cbe_birr">{{ __('CBE Birr Mobile') }}</option>
                            <option value="cash">{{ __('Cash / Physical Voucher') }}</option>
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-muted/70 dark:text-dark-subtle uppercase">{{ __('Decision Note') }}</label>
                        <textarea name="decision_notes" rows="2" class="input w-full text-sm" placeholder="{{ __('Optional verification note...') }}"></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="showDecisionModal = false" class="btn btn-secondary text-xs">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit" class="btn btn-primary text-xs font-bold">
                            {{ __('Submit Decision') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
