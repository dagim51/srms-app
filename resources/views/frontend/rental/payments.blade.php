<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Rent Payments') }}</x-slot>

    <div class="max-w-7xl mx-auto page-enter space-y-6">
        <x-page-header
            title="Rent Payments & Receipts"
            description="Track rent collections, overdue payment alerts, transaction receipts, and automated billing cycles."
            icon="cash"
            badge="Payments">
            <x-slot:actions>
                <button class="btn btn-primary">{{ __('Record Payment') }}</button>
            </x-slot:actions>
        </x-page-header>

        @include('frontend.rental.partials.tabs')

        <div class="card overflow-hidden">
            <div class="p-6 border-b border-[#EBDBC9] dark:border-[#273244] flex flex-wrap gap-4 items-center">
                <form method="GET" action="{{ route('dashboard.rental.payments') }}" class="flex items-center gap-2 flex-1 max-w-md">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </div>
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="{{ __('Search by Receipt ID or Tenant...') }}"
                               class="form-input search-input pl-9 pr-8 w-full text-sm">
                        @if(request('search'))
                            <a href="{{ route('dashboard.rental.payments') }}" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </a>
                        @endif
                    </div>
                    @if(request('search'))
                        <a href="{{ route('dashboard.rental.payments') }}" class="text-xs text-orange-600 hover:text-orange-700 dark:text-orange-400 font-medium whitespace-nowrap">{{ __('Reset') }}</a>
                    @endif
                </form>
                <select class="input max-w-xs">
                    <option>{{ __('All Statuses') }}</option>
                    <option>{{ __('Completed') }}</option>
                    <option>{{ __('Pending Verification') }}</option>
                    <option>{{ __('Failed') }}</option>
                </select>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-warm-50/50 dark:bg-[#1E293B]/50 border-b border-[#EBDBC9] dark:border-[#273244]">
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Receipt ID') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Tenant & Unit') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Amount') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Date') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Method') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EBDBC9]/50 dark:divide-[#273244]">
                        @foreach($payments as $payment)
                            <tr class="hover:bg-surface dark:hover:bg-surface/30 transition-colors">
                                <td class="p-4 font-bold text-muted dark:text-white">{{ $payment['id'] }}</td>
                                <td class="p-4">
                                    <p class="font-medium text-muted dark:text-white">{{ $payment['tenant'] }}</p>
                                    <p class="text-xs text-muted">{{ $payment['unit'] }}</p>
                                </td>
                                <td class="p-4 font-bold text-muted dark:text-white">{{ $payment['amount'] }}</td>
                                <td class="p-4 text-muted dark:text-dark-subtle">{{ date('M d, Y', strtotime($payment['date'])) }}</td>
                                <td class="p-4 text-muted dark:text-dark-subtle">{{ $payment['method'] }}</td>
                                <td class="p-4">
                                    <span class="badge {{ $payment['status'] === 'completed' ? 'badge-success' : 'badge-warning' }} uppercase text-xs">{{ __(str_replace('_', ' ', $payment['status'])) }}</span>
                                </td>
                                <td class="p-4 text-right">
                                    <button class="text-primary-600 hover:text-primary-700 font-medium text-sm">{{ __('Download Receipt') }}</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
