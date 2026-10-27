<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Lease') }}: {{ $lease['id'] }}</x-slot>

    <div class="max-w-7xl mx-auto page-enter space-y-6"
         x-data="{
             showRenewModal: false,
             showTerminateModal: false,
             showPaymentModal: false,
             selectedScheduleId: null,
             selectedScheduleAmount: '',
             selectedScheduleMonth: ''
         }">
        <x-page-header
            :title="__('Lease Agreement: ') . $lease['id']"
            :description="$lease['unit'] . ' • ' . __('Tenant:') . ' ' . $lease['tenant'] . ' • ' . __('Status:') . ' ' . __($lease['status'])"
            icon="document"
            :badge="$lease['status']">
            <x-slot:actions>
                <div class="flex items-center gap-2">
                    <a href="{{ route('dashboard.rental.leases') }}" class="btn btn-secondary text-xs flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                        {{ __('Back to Leases') }}
                    </a>
                    @if($lease['status'] === 'active')
                        <button @click="showRenewModal = true" class="btn btn-secondary text-xs">{{ __('Renew Lease') }}</button>
                        <button @click="showTerminateModal = true" class="btn btn-secondary text-xs text-error-600 border-error-200 hover:bg-error-50 dark:hover:bg-error-900/20">{{ __('Terminate') }}</button>
                    @endif
                </div>
            </x-slot:actions>
        </x-page-header>

        @if(session('success'))
            <div class="px-4 py-3 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 text-sm border border-emerald-200 dark:border-emerald-800 flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-8">
                <!-- Lease Terms -->
                <div class="card p-6 md:p-8">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="card-title text-xl">{{ __('Lease Terms') }}</h2>
                        <span class="badge {{ $lease['status'] === 'active' ? 'badge-success' : ($lease['status'] === 'terminated' ? 'badge-error' : 'badge-warning') }} uppercase text-xs">
                            {{ __(str_replace('_', ' ', $lease['status'])) }}
                        </span>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                        <div>
                            <p class="card-kv-label">{{ __('Start Date') }}</p>
                            <p class="card-kv-value font-medium mt-1">{{ $lease['start_date'] }}</p>
                        </div>
                        <div>
                            <p class="card-kv-label">{{ __('End Date') }}</p>
                            <p class="card-kv-value font-medium mt-1">{{ $lease['end_date'] }}</p>
                        </div>
                        <div>
                            <p class="card-kv-label">{{ __('Rent') }}</p>
                            <p class="card-kv-value mt-1">{{ $lease['rent'] }}/mo</p>
                        </div>
                        <div>
                            <p class="card-kv-label">{{ __('Deposit') }}</p>
                            <p class="card-kv-value mt-1">{{ $lease['deposit'] }}</p>
                        </div>
                    </div>
                </div>

                <!-- Rent Schedule -->
                <div class="card p-6 md:p-8">
                    <h2 class="card-title text-xl mb-6">{{ __('Rent Payment Schedule') }}</h2>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-[#EBDBC9] dark:border-[#273244]">
                                    <th class="py-3 pr-4 text-xs font-bold text-muted dark:text-muted uppercase">{{ __('Period') }}</th>
                                    <th class="py-3 px-4 text-xs font-bold text-muted dark:text-muted uppercase">{{ __('Due Date') }}</th>
                                    <th class="py-3 px-4 text-xs font-bold text-muted dark:text-muted uppercase">{{ __('Amount') }}</th>
                                    <th class="py-3 pl-4 text-xs font-bold text-muted dark:text-muted uppercase text-right">{{ __('Status / Action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#EBDBC9]/50 dark:divide-[#273244]">
                                @forelse($schedule as $item)
                                    <tr>
                                        <td class="py-4 pr-4 font-bold text-muted dark:text-white">{{ $item['month'] }}</td>
                                        <td class="py-4 px-4 text-muted dark:text-dark-subtle">{{ date('M d, Y', strtotime($item['due_date'])) }}</td>
                                        <td class="py-4 px-4 font-bold text-muted dark:text-white">{{ $item['amount'] }}</td>
                                        <td class="py-4 pl-4 text-right">
                                            @if($item['status'] === 'paid')
                                                <span class="badge badge-success uppercase text-xs">{{ __('Paid') }} ({{ date('M d', strtotime($item['paid_date'])) }})</span>
                                            @else
                                                <button @click="selectedScheduleId = '{{ $item['id'] }}'; selectedScheduleAmount = '{{ $item['amount'] }}'; selectedScheduleMonth = '{{ $item['month'] }}'; showPaymentModal = true"
                                                        class="btn btn-sm btn-primary text-xs">
                                                    {{ __('Record Payment') }}
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="p-6 text-center text-muted">{{ __('No schedules found for this lease.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Context Info -->
            <div>
                <div class="card p-6 mb-6">
                    <h3 class="card-title text-base mb-4">{{ __('Tenant') }}</h3>
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 flex items-center justify-center font-bold text-lg">
                            {{ substr($lease['tenant'], 0, 1) }}
                        </div>
                        <div>
                            <h4 class="font-bold text-muted dark:text-white">{{ $lease['tenant'] }}</h4>
                        </div>
                    </div>
                    @if(!empty($lease['tenant_user_id']))
                        <a href="{{ route('dashboard.rental.tenants.show', $lease['tenant_user_id']) }}" class="btn btn-secondary w-full justify-center">{{ __('View Profile') }}</a>
                    @else
                        <a href="{{ route('dashboard.rental.tenants') }}" class="btn btn-secondary w-full justify-center">{{ __('View Tenants') }}</a>
                    @endif
                </div>

                <div class="card p-6">
                    <h3 class="card-title text-base mb-3">{{ __('Lease Actions') }}</h3>
                    <div class="space-y-3">
                        @if($lease['status'] === 'active')
                            <button @click="showRenewModal = true" class="btn btn-secondary w-full justify-center text-xs">
                                {{ __('Extend / Renew Lease') }}
                            </button>
                            <button @click="showTerminateModal = true" class="btn btn-outline-error w-full justify-center text-xs">
                                {{ __('Terminate Tenancy') }}
                            </button>
                        @else
                            <p class="text-xs text-muted">{{ __('This lease is currently :status.', ['status' => $lease['status']]) }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Record Payment Modal --}}
        <div x-show="showPaymentModal" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="absolute inset-0 bg-black/50" @click="showPaymentModal = false"></div>
            <div class="relative bg-white dark:bg-dark-card rounded-2xl shadow-2xl w-full max-w-md p-6 z-10">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-muted dark:text-white">{{ __('Record Rent Payment') }}</h3>
                    <button @click="showPaymentModal = false" class="text-muted hover:text-muted/70">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form method="POST" action="{{ route('dashboard.rental.leases.payments', $lease['id']) }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="schedule_id" :value="selectedScheduleId">

                    <div class="p-3 bg-surface dark:bg-surface/20 rounded-xl space-y-1 text-sm border border-warm dark:border-warm">
                        <p class="font-bold text-muted dark:text-white" x-text="'Period: ' + selectedScheduleMonth"></p>
                        <p class="text-xs text-muted" x-text="'Scheduled Rent: ' + selectedScheduleAmount"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-muted dark:text-dark-subtle mb-1">{{ __('Payment Method') }} <span class="text-red-500">*</span></label>
                        <select name="payment_method" class="input w-full" required>
                            <option value="Telebirr">{{ __('Telebirr (Mobile Wallet)') }}</option>
                            <option value="CBE Birr">{{ __('CBE Birr') }}</option>
                            <option value="Bank Transfer">{{ __('Commercial Bank of Ethiopia (Direct Transfer)') }}</option>
                            <option value="Awash Bank">{{ __('Awash Bank') }}</option>
                            <option value="Cash">{{ __('Cash with Physical Receipt') }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-muted dark:text-dark-subtle mb-1">{{ __('Transaction Reference / Slip #') }}</label>
                        <input type="text" name="reference_number" class="input w-full" placeholder="e.g. TXN-9988224">
                    </div>

                    <div class="flex justify-end gap-3 pt-3">
                        <button type="button" @click="showPaymentModal = false" class="btn btn-secondary">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Confirm Payment') }}</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Renew Lease Modal --}}
        <div x-show="showRenewModal" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="absolute inset-0 bg-black/50" @click="showRenewModal = false"></div>
            <div class="relative bg-white dark:bg-dark-card rounded-2xl shadow-2xl w-full max-w-md p-6 z-10">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-muted dark:text-white">{{ __('Extend / Renew Lease') }}</h3>
                    <button @click="showRenewModal = false" class="text-muted hover:text-muted/70">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form method="POST" action="{{ route('dashboard.rental.leases.renew', $lease['id']) }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-muted dark:text-dark-subtle mb-1">{{ __('New Lease Expiration Date') }} <span class="text-red-500">*</span></label>
                        <input type="date" name="new_end_date" value="{{ date('Y-m-d', strtotime('+1 year', strtotime($lease['end_date'] ?? date('Y-m-d')))) }}" class="input w-full" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-muted dark:text-dark-subtle mb-1">{{ __('Revised Monthly Rent (ETB)') }}</label>
                        <input type="number" name="new_rent_amount" step="0.01" class="input w-full" placeholder="Leave empty to keep existing rent">
                    </div>

                    <div class="flex justify-end gap-3 pt-3">
                        <button type="button" @click="showRenewModal = false" class="btn btn-secondary">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Confirm Renewal') }}</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Terminate Lease Modal --}}
        <div x-show="showTerminateModal" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="absolute inset-0 bg-black/50" @click="showTerminateModal = false"></div>
            <div class="relative bg-white dark:bg-dark-card rounded-2xl shadow-2xl w-full max-w-md p-6 z-10">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-red-600 dark:text-red-400">{{ __('Terminate Lease Agreement') }}</h3>
                    <button @click="showTerminateModal = false" class="text-muted hover:text-muted/70">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form method="POST" action="{{ route('dashboard.rental.leases.terminate', $lease['id']) }}" class="space-y-4">
                    @csrf
                    <p class="text-sm text-muted dark:text-dark-subtle">
                        {{ __('Are you sure you want to terminate this lease agreement? The tenant status will be marked as terminated and future automated rent collections will cease.') }}
                    </p>

                    <div class="flex justify-end gap-3 pt-3">
                        <button type="button" @click="showTerminateModal = false" class="btn btn-secondary">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-error">{{ __('Yes, Terminate Lease') }}</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
