<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Dispute Center') }}</x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="mb-8 flex justify-between items-end">
            <div>
                <h1 class="text-3xl font-extrabold font-display text-muted dark:text-white tracking-tight">{{ __('Dispute Center') }}</h1>
                <p class="text-muted dark:text-muted mt-2">{{ __('Mediate conflicts between tenants, property managers, and service providers.') }}</p>
            </div>
        </div>

        @include('frontend.trust.partials.tabs')

        <div class="card overflow-hidden">
            <div class="p-6 border-b border-[#EBDBC9] dark:border-[#273244] flex gap-4 flex-wrap">
                <input type="text" placeholder="{{ __('Search disputes by ID or parties...') }}" class="input w-full max-w-sm">
                <select class="input max-w-xs">
                    <option>{{ __('All Statuses') }}</option>
                    <option>{{ __('Open') }}</option>
                    <option>{{ __('Escalated') }}</option>
                    <option>{{ __('Investigating') }}</option>
                    <option>{{ __('Resolved') }}</option>
                </select>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-warm-50/50 dark:bg-[#1E293B]/50 border-b border-[#EBDBC9] dark:border-[#273244]">
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Case ID') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Plaintiff') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Defendant') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Issue') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Amount') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Date') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EBDBC9]/50 dark:divide-[#273244]">
                        @foreach($disputes as $dispute)
                            <tr class="hover:bg-surface dark:hover:bg-surface/30 transition-colors">
                                <td class="p-4 font-bold text-muted dark:text-white">{{ $dispute['id'] }}</td>
                                <td class="p-4 text-sm font-medium text-muted dark:text-white">{{ $dispute['plaintiff'] }}</td>
                                <td class="p-4 text-sm text-muted dark:text-dark-subtle">{{ $dispute['defendant'] }}</td>
                                <td class="p-4 text-sm text-muted dark:text-dark-subtle">{{ $dispute['reason'] }}</td>
                                <td class="p-4 font-bold text-muted dark:text-white">{{ $dispute['amount'] }}</td>
                                <td class="p-4 text-sm text-muted dark:text-dark-subtle">{{ date('M d, Y', strtotime($dispute['date'])) }}</td>
                                <td class="p-4">
                                    @php
                                        $sClass = 'badge-neutral';
                                        if ($dispute['status'] === 'escalated') $sClass = 'badge-error';
                                        if ($dispute['status'] === 'investigating') $sClass = 'badge-warning';
                                        if ($dispute['status'] === 'resolved') $sClass = 'badge-success';
                                    @endphp
                                    <span class="badge {{ $sClass }} uppercase text-xs">{{ str_replace('_', ' ', $dispute['status']) }}</span>
                                </td>
                                <td class="p-4 text-right">
                                    <a href="{{ route('dashboard.trust.cases.show', $dispute['id']) }}" class="btn btn-sm btn-secondary">{{ __('Review') }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
