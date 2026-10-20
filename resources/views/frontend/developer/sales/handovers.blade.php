<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Handovers') }}</x-slot>

    <div class="max-w-7xl mx-auto page-enter">
        <x-page-header
            title="Handovers & Snagging"
            description="Manage the final handover process and inspection snagging of completed units."
            icon="key"
            badge="Handovers">
            <x-slot:actions>
                <button class="btn btn-primary">{{ __('Schedule Handover') }}</button>
            </x-slot:actions>
        </x-page-header>

        @include('frontend.developer.sales.partials.tabs')

        <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-3xl overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface dark:bg-surface/50 border-b border-warm dark:border-warm">
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Unit Number') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Buyer Name') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Scheduled Date') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-warm dark:divide-dark-border">
                        @foreach($handovers as $handover)
                            <tr class="hover:bg-surface dark:hover:bg-surface/30 transition-colors">
                                <td class="p-4 font-bold text-muted dark:text-white">{{ $handover['unit'] }}</td>
                                <td class="p-4 text-muted dark:text-dark-subtle">{{ $handover['buyer'] }}</td>
                                <td class="p-4 font-medium text-muted dark:text-white">{{ date('M d, Y', strtotime($handover['scheduled_date'])) }}</td>
                                <td class="p-4">
                                    <span class="badge {{ $handover['status'] === 'scheduled' ? 'badge-primary' : 'badge-warning' }} uppercase text-xs">{{ str_replace('_', ' ', $handover['status']) }}</span>
                                </td>
                                <td class="p-4 text-right">
                                    <button class="text-primary-600 hover:text-primary-700 font-medium text-sm">{{ __('Snag List') }}</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
