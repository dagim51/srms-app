<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Identity & Verifications') }}</x-slot>

    <div class="max-w-7xl mx-auto page-enter space-y-6">
        <x-page-header
            title="Identity & Document Verifications"
            description="Process applicant KYC verifications, land title deed registry checks, municipal seals, and corporate licenses."
            icon="document"
            badge="Verifications">
            <x-slot:actions>
                <button class="btn btn-primary">{{ __('Request Verification') }}</button>
            </x-slot:actions>
        </x-page-header>

        @include('frontend.documents.partials.tabs')

        <div class="card overflow-hidden">
            <div class="p-6 border-b border-[#EBDBC9] dark:border-[#273244]">
                <form method="GET" action="{{ route('dashboard.documents.verifications') }}" class="flex flex-wrap items-center gap-3">
                    <div class="relative flex-1 min-w-56">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </div>
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="{{ __('Search by name, ID, or type...') }}"
                               class="form-input search-input pl-9 pr-8 w-full text-sm">
                        @if(request('search'))
                            <a href="{{ route('dashboard.documents.verifications') }}" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </a>
                        @endif
                    </div>
                    @if(request('search'))
                        <a href="{{ route('dashboard.documents.verifications') }}" class="text-xs text-orange-600 hover:text-orange-700 dark:text-orange-400 font-medium px-2 py-1.5">{{ __('Reset') }}</a>
                    @endif
                </form>
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
