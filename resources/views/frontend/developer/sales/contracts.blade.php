<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Contracts') }}</x-slot>

    <div class="max-w-7xl mx-auto">
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
                <input type="text" placeholder="{{ __('Search contracts by ID or buyer...') }}" class="input w-full max-w-md">
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
