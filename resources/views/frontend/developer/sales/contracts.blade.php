<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Contracts') }}</x-slot>

    <div class="max-w-7xl mx-auto page-enter">
        <x-page-header
            title="Sales Contracts"
            description="Manage generated contracts, unit agreements, and signature statuses."
            icon="document"
            badge="Contracts">
            <x-slot:actions>
                <button class="btn btn-primary">{{ __('Generate Contract') }}</button>
            </x-slot:actions>
        </x-page-header>

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
                        @foreach($contracts as $contract)
                            <tr class="hover:bg-surface dark:hover:bg-surface/30 transition-colors">
                                <td class="p-4 font-bold text-muted dark:text-white">{{ $contract['id'] }}</td>
                                <td class="p-4 font-medium text-muted dark:text-white">{{ $contract['unit'] }}</td>
                                <td class="p-4 text-muted dark:text-dark-subtle">{{ $contract['buyer'] }}</td>
                                <td class="p-4 font-bold text-muted dark:text-white">{{ $contract['value'] }}</td>
                                <td class="p-4">
                                    <span class="badge {{ $contract['status'] === 'signed' ? 'badge-primary' : ($contract['status'] === 'completed' ? 'badge-success' : 'badge-neutral') }} uppercase text-xs">{{ $contract['status'] }}</span>
                                </td>
                                <td class="p-4 text-right">
                                    <button class="text-primary-600 dark:text-primary-400 hover:text-primary-700 font-medium text-sm mr-3">{{ __('View PDF') }}</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
