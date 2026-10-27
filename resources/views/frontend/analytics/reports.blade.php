<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Report Builder') }}</x-slot>

    <div class="max-w-7xl mx-auto page-enter">
        <x-page-header
            title="Report Builder"
            description="Generate custom analytical reports and financial summaries for your portfolio."
            icon="document"
            badge="Reports">
            <x-slot:actions>
                <button class="btn btn-primary flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                    {{ __('New Report') }}
                </button>
            </x-slot:actions>
        </x-page-header>

        @include('frontend.analytics.partials.tabs')

        <div class="card bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-3xl overflow-hidden">
            <div class="p-6 border-b border-warm dark:border-warm">
                <h2 class="text-xl font-bold text-muted dark:text-white">{{ __('Saved Reports') }}</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface dark:bg-surface/50 border-b border-warm dark:border-warm">
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Report Name') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Type') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Frequency') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Last Run') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-warm dark:divide-dark-border">
                        @foreach($savedReports as $report)
                            <tr class="hover:bg-surface dark:hover:bg-surface/30 transition-colors">
                                <td class="p-4 font-bold text-muted dark:text-white">{{ $report['name'] }}</td>
                                <td class="p-4 text-sm text-muted dark:text-dark-subtle">{{ __($report['type']) }}</td>
                                <td class="p-4 text-sm text-muted dark:text-dark-subtle">{{ __($report['frequency']) }}</td>
                                <td class="p-4 text-sm text-muted dark:text-dark-subtle">{{ date('M d, Y', strtotime($report['last_run'])) }}</td>
                                <td class="p-4">
                                    @php
                                        $sClass = 'badge-neutral';
                                        if ($report['status'] === 'ready') $sClass = 'badge-success';
                                        if ($report['status'] === 'processing') $sClass = 'badge-primary';
                                    @endphp
                                    <span class="badge {{ $sClass }} uppercase text-xs">{{ __($report['status']) }}</span>
                                </td>
                                <td class="p-4 text-right">
                                    <button class="btn btn-sm btn-secondary">{{ __('Run Now') }}</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
