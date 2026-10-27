<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Contracts') }}</x-slot>

    <div class="max-w-7xl mx-auto page-enter space-y-6" x-data="{ selectedContract: null }">
        <x-page-header
            title="Sales Contracts"
            description="Manage generated contracts, unit agreements, and signature statuses."
            icon="document"
            badge="Contracts" />

        @if(session('success'))
            <div class="px-4 py-3 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 text-sm border border-emerald-200 dark:border-emerald-800 flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ session('success') }}
            </div>
        @endif

        @include('frontend.developer.sales.partials.tabs')

        <div class="card overflow-hidden">
            <div class="p-6 border-b border-[#EBDBC9] dark:border-[#273244]">
                <form method="GET" action="{{ route('dashboard.sales.contracts') }}" class="flex items-center gap-3 max-w-md">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </div>
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="{{ __('Search contracts by ID, unit, or buyer...') }}"
                               class="form-input search-input pl-9 pr-8 w-full text-sm">
                        @if(request('search'))
                            <a href="{{ route('dashboard.sales.contracts') }}" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </a>
                        @endif
                    </div>
                    @if(request('search'))
                        <a href="{{ route('dashboard.sales.contracts') }}" class="text-xs text-orange-600 hover:text-orange-700 dark:text-orange-400 font-medium whitespace-nowrap">{{ __('Reset') }}</a>
                    @endif
                </form>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-warm-50/50 dark:bg-[#1E293B]/50 border-b border-[#EBDBC9] dark:border-[#273244]">
                            <th class="p-4 text-xs font-semibold text-muted uppercase tracking-wider">{{ __('Contract ID') }}</th>
                            <th class="p-4 text-xs font-semibold text-muted uppercase tracking-wider">{{ __('Unit') }}</th>
                            <th class="p-4 text-xs font-semibold text-muted uppercase tracking-wider">{{ __('Buyer Name') }}</th>
                            <th class="p-4 text-xs font-semibold text-muted uppercase tracking-wider">{{ __('Value') }}</th>
                            <th class="p-4 text-xs font-semibold text-muted uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="p-4 text-xs font-semibold text-muted uppercase tracking-wider text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EBDBC9]/50 dark:divide-[#273244]/60">
                        @forelse($contracts as $contract)
                            <tr class="hover:bg-surface dark:hover:bg-surface/30 transition-colors">
                                <td class="p-4 font-bold text-muted dark:text-white font-mono text-xs">{{ $contract['id'] }}</td>
                                <td class="p-4 font-medium text-muted dark:text-white">{{ $contract['unit'] }}</td>
                                <td class="p-4 text-muted dark:text-dark-subtle">{{ $contract['buyer'] }}</td>
                                <td class="p-4 font-bold text-muted dark:text-white">{{ $contract['value'] }}</td>
                                <td class="p-4">
                                    <span class="badge {{ $contract['status'] === 'signed' ? 'badge-primary' : ($contract['status'] === 'completed' ? 'badge-success' : 'badge-neutral') }} uppercase text-xs">{{ $contract['status'] }}</span>
                                </td>
                                <td class="p-4 text-right">
                                    <button @click="selectedContract = {{ json_encode($contract) }}"
                                            class="btn btn-secondary btn-sm text-xs">
                                        {{ __('Update Status') }}
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-muted">
                                    {{ __('No contracts found.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(method_exists($contracts, 'links'))
                <div class="p-4 border-t border-[#EBDBC9] dark:border-[#273244]">
                    {{ $contracts->links() }}
                </div>
            @endif
        </div>

        {{-- Update Contract Modal --}}
        <div x-show="selectedContract !== null" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="absolute inset-0 bg-black/50" @click="selectedContract = null"></div>
            <div class="relative bg-white dark:bg-dark-card rounded-2xl shadow-2xl w-full max-w-md p-6 z-10" x-data="{ contractStatus: 'signed' }">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-muted dark:text-white">{{ __('Execute / Update Contract') }}</h3>
                    <button @click="selectedContract = null" class="text-muted hover:text-muted/70">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <template x-if="selectedContract">
                    <form method="POST" :action="'{{ url('dashboard/sales/contracts') }}/' + selectedContract.unit_id + '/status'" class="space-y-4">
                        @csrf
                        <div class="p-3 bg-surface dark:bg-surface/20 rounded-xl space-y-1 text-sm border border-warm dark:border-warm">
                            <p class="font-bold text-muted dark:text-white" x-text="selectedContract.id + ' • ' + selectedContract.unit"></p>
                            <p class="text-xs text-muted" x-text="'Buyer: ' + selectedContract.buyer + ' (' + selectedContract.value + ')'"></p>
                            <p class="text-xs text-muted" x-text="'Current Status: ' + selectedContract.status"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-muted dark:text-dark-subtle mb-1">{{ __('Select New Contract State') }} <span class="text-red-500">*</span></label>
                            <select name="status" x-model="contractStatus" class="input w-full" required>
                                <option value="signed">{{ __('Signed / Executed (Unit Contracted)') }}</option>
                                <option value="sold">{{ __('Fully Paid / Closed Sale') }}</option>
                                <option value="draft">{{ __('Reserved / Draft State') }}</option>
                            </select>
                        </div>

                        <div class="flex justify-end gap-3 pt-3">
                            <button type="button" @click="selectedContract = null" class="btn btn-secondary">{{ __('Close') }}</button>
                            <button type="submit" class="btn btn-primary">{{ __('Save State') }}</button>
                        </div>
                    </form>
                </template>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
