<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Identity & Verifications') }}</x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="mb-8 flex justify-between items-end">
            <div>
                <h1 class="text-3xl font-extrabold font-display text-muted dark:text-white tracking-tight">{{ __('Identity Verifications') }}</h1>
                <p class="text-muted dark:text-muted mt-2">{{ __('Review KYC, income, and business checks.') }}</p>
            </div>
            <button class="btn btn-primary">{{ __('Request Verification') }}</button>
        </div>

        @include('frontend.documents.partials.tabs')

        <div class="card overflow-hidden">
            <div class="p-6 border-b border-[#EBDBC9] dark:border-[#273244] flex gap-4 flex-wrap">
                <input type="text" placeholder="{{ __('Search by name or ID...') }}" class="input w-full max-w-sm">
                <select class="input max-w-xs">
                    <option>{{ __('Pending Review') }}</option>
                    <option>{{ __('Approved') }}</option>
                    <option>{{ __('Rejected') }}</option>
                    <option>{{ __('All Statuses') }}</option>
                </select>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-warm-50/50 dark:bg-[#1E293B]/50 border-b border-[#EBDBC9] dark:border-[#273244]">
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Verification ID') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Subject') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Type') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Risk Score') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Submitted') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider text-right">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EBDBC9]/50 dark:divide-[#273244]">
                        @foreach($verifications as $v)
                            <tr class="hover:bg-surface dark:hover:bg-surface/30 transition-colors">
                                <td class="p-4 font-bold text-muted dark:text-white">
                                    <a href="{{ route('dashboard.documents.verifications.show', $v['id']) }}" class="hover:text-primary-600">{{ $v['id'] }}</a>
                                </td>
                                <td class="p-4 font-medium text-muted dark:text-white">{{ $v['subject'] }}</td>
                                <td class="p-4 text-sm text-muted dark:text-dark-subtle">{{ $v['type'] }}</td>
                                <td class="p-4">
                                    @php
                                        $rClass = 'badge-neutral';
                                        if ($v['risk_score'] === 'low') $rClass = 'badge-success opacity-80';
                                        if ($v['risk_score'] === 'medium') $rClass = 'badge-warning opacity-80';
                                        if ($v['risk_score'] === 'high') $rClass = 'badge-error opacity-80';
                                    @endphp
                                    <span class="badge {{ $rClass }} uppercase text-xs">{{ $v['risk_score'] }} Risk</span>
                                </td>
                                <td class="p-4">
                                    @php
                                        $sClass = 'badge-neutral';
                                        if ($v['status'] === 'approved') $sClass = 'badge-success';
                                        if ($v['status'] === 'pending_review') $sClass = 'badge-warning';
                                        if ($v['status'] === 'rejected') $sClass = 'badge-error';
                                    @endphp
                                    <span class="badge {{ $sClass }} uppercase text-xs">{{ str_replace('_', ' ', $v['status']) }}</span>
                                </td>
                                <td class="p-4 text-sm text-muted dark:text-dark-subtle">{{ date('M d, H:i', strtotime($v['submitted'])) }}</td>
                                <td class="p-4 text-right">
                                    <a href="{{ route('dashboard.documents.verifications.show', $v['id']) }}" class="btn btn-sm btn-secondary">{{ __('Review') }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
