<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('User Reports') }}</x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="mb-8 flex justify-between items-end">
            <div>
                <h1 class="text-3xl font-extrabold font-display text-muted dark:text-white tracking-tight">{{ __('User Reports') }}</h1>
                <p class="text-muted dark:text-muted mt-2">{{ __('Investigate user-submitted reports for fraud, scams, and abuse.') }}</p>
            </div>
        </div>

        @include('frontend.trust.partials.tabs')

        <div class="card overflow-hidden">
            <div class="p-6 border-b border-[#EBDBC9] dark:border-[#273244] flex gap-4 flex-wrap">
                <input type="text" placeholder="{{ __('Search reports...') }}" class="input w-full max-w-sm">
                <select class="input max-w-xs">
                    <option>{{ __('All Reasons') }}</option>
                    <option>{{ __('Scam/Fraud') }}</option>
                    <option>{{ __('Harassment') }}</option>
                    <option>{{ __('Suspicious Activity') }}</option>
                    <option>{{ __('Inappropriate Content') }}</option>
                </select>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-warm-50/50 dark:bg-[#1E293B]/50 border-b border-[#EBDBC9] dark:border-[#273244]">
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Report ID') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Reporter') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Reason') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Target') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Date') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EBDBC9]/50 dark:divide-[#273244]">
                        @foreach($reports as $report)
                            <tr class="hover:bg-surface dark:hover:bg-surface/30 transition-colors">
                                <td class="p-4 font-bold text-muted dark:text-white">{{ $report['id'] }}</td>
                                <td class="p-4 text-sm font-medium text-muted dark:text-white">{{ $report['reporter'] }}</td>
                                <td class="p-4">
                                    <span class="inline-flex items-center gap-1.5 py-1 px-2 rounded-md bg-error-50 dark:bg-error-900/20 text-error-700 dark:text-error-400 font-medium text-xs border border-error-100 dark:border-error-800">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                        {{ $report['reason'] }}
                                    </span>
                                </td>
                                <td class="p-4 text-sm text-muted dark:text-dark-subtle">{{ $report['target'] }}</td>
                                <td class="p-4 text-sm text-muted dark:text-dark-subtle">{{ date('M d, Y', strtotime($report['date'])) }}</td>
                                <td class="p-4">
                                    @php
                                        $sClass = 'badge-neutral';
                                        if ($report['status'] === 'open') $sClass = 'badge-error opacity-80';
                                        if ($report['status'] === 'reviewing') $sClass = 'badge-warning';
                                        if ($report['status'] === 'resolved') $sClass = 'badge-success';
                                    @endphp
                                    <span class="badge {{ $sClass }} uppercase text-xs">{{ str_replace('_', ' ', $report['status']) }}</span>
                                </td>
                                <td class="p-4 text-right">
                                    <a href="{{ route('dashboard.trust.cases.show', $report['id']) }}" class="btn btn-sm btn-secondary">{{ __('Open Case') }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
