<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('User Reports') }}</x-slot>

    <div x-data="{
        showReviewModal: false,
        reviewReportId: '',
        reviewReportCode: '',
        reviewReason: '',
        reviewStatus: 'under_review',
        moderatorNotes: '',
        openReview(id, code, reason, currentStatus, currentNotes) {
            this.reviewReportId = id;
            this.reviewReportCode = code;
            this.reviewReason = reason;
            this.reviewStatus = currentStatus === 'open' ? 'under_review' : currentStatus;
            this.moderatorNotes = currentNotes || '';
            this.showReviewModal = true;
        }
    }" class="max-w-7xl mx-auto page-enter space-y-6">

        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-sm flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        <x-page-header
            title="User & Marketplace Reports"
            description="Investigate community-flagged listings, scam alerts, and abusive conduct reports."
            icon="flag"
            badge="Moderation" />

        @include('frontend.trust.partials.tabs')

        <div class="card overflow-hidden border border-[#EBDBC9] dark:border-[#273244] shadow-sm">
            <div class="p-6 border-b border-[#EBDBC9] dark:border-[#273244] flex gap-4 flex-wrap items-center justify-between">
                <form method="GET" action="{{ route('dashboard.trust.reports') }}" class="flex items-center gap-3 flex-1 max-w-lg">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </div>
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="{{ __('Search reports by reporter, reason or listing...') }}"
                               class="form-input search-input pl-9 pr-8 w-full text-sm">
                        @if(request('search'))
                            <a href="{{ route('dashboard.trust.reports') }}" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </a>
                        @endif
                    </div>
                    <select name="status" onchange="this.form.submit()" class="input max-w-xs text-sm">
                        <option value="">{{ __('All Statuses') }}</option>
                        <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>{{ __('Open (Action Required)') }}</option>
                        <option value="under_review" {{ request('status') === 'under_review' ? 'selected' : '' }}>{{ __('Under Review') }}</option>
                        <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>{{ __('Resolved') }}</option>
                        <option value="dismissed" {{ request('status') === 'dismissed' ? 'selected' : '' }}>{{ __('Dismissed') }}</option>
                    </select>
                </form>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-warm-50/50 dark:bg-[#1E293B]/50 border-b border-[#EBDBC9] dark:border-[#273244]">
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Report ID') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Reporter') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Reason & Severity') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Reported Entity') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Date') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EBDBC9]/50 dark:divide-[#273244]">
                        @forelse($reports as $report)
                            <tr class="hover:bg-surface dark:hover:bg-surface/30 transition-colors">
                                <td class="p-4 font-mono font-bold text-primary-600 dark:text-primary-400 text-sm">
                                    <a href="{{ route('dashboard.trust.cases.show', $report['raw_id']) }}" class="hover:underline">
                                        {{ $report['id'] }}
                                    </a>
                                </td>
                                <td class="p-4 text-sm font-medium text-muted dark:text-white">{{ $report['reporter'] }}</td>
                                <td class="p-4">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="inline-flex items-center gap-1 py-0.5 px-2 rounded-md bg-error-50 dark:bg-error-900/20 text-error-700 dark:text-error-400 font-semibold text-xs border border-error-100 dark:border-error-800">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                            {{ $report['reason'] }}
                                        </span>
                                        <span class="text-[10px] uppercase font-bold text-muted/60 dark:text-dark-subtle">
                                            {{ $report['severity'] }}
                                        </span>
                                    </div>
                                </td>
                                <td class="p-4 text-sm text-muted dark:text-dark-subtle">{{ $report['target'] }}</td>
                                <td class="p-4 text-sm text-muted dark:text-dark-subtle">{{ date('M d, Y', strtotime($report['date'])) }}</td>
                                <td class="p-4">
                                    @php
                                        $sClass = 'badge-neutral';
                                        if ($report['status'] === 'open') $sClass = 'badge-error';
                                        elseif ($report['status'] === 'under_review') $sClass = 'badge-warning';
                                        elseif ($report['status'] === 'resolved') $sClass = 'badge-success';
                                    @endphp
                                    <span class="badge {{ $sClass }} uppercase text-xs">{{ str_replace('_', ' ', $report['status']) }}</span>
                                </td>
                                <td class="p-4 text-right space-x-2 whitespace-nowrap">
                                    <button type="button"
                                            @click="openReview('{{ $report['raw_id'] }}', '{{ $report['id'] }}', '{{ addslashes($report['reason']) }}', '{{ $report['status'] }}', '{{ addslashes($report['moderator_notes'] ?? '') }}')"
                                            class="btn btn-secondary text-xs py-1 px-2.5">
                                        {{ __('Moderate') }}
                                    </button>
                                    <a href="{{ route('dashboard.trust.cases.show', $report['raw_id']) }}" class="btn btn-secondary text-xs py-1 px-2.5">
                                        {{ __('Details') }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-8 text-center text-muted dark:text-dark-subtle">
                                    <p class="text-sm font-medium">{{ __('No user reports found matching criteria.') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($reports, 'links'))
                <div class="p-4 border-t border-[#EBDBC9] dark:border-[#273244]">
                    {{ $reports->links() }}
                </div>
            @endif
        </div>

        {{-- Moderate Report Modal --}}
        <div x-show="showReviewModal"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <div class="card max-w-md w-full p-6 space-y-4 shadow-2xl bg-surface dark:bg-[#1E293B]" @click.outside="showReviewModal = false">
                <div class="flex justify-between items-center pb-3 border-b border-[#EBDBC9] dark:border-[#273244]">
                    <div>
                        <h3 class="font-bold text-lg text-muted dark:text-white">{{ __('Moderate User Report') }}</h3>
                        <p class="text-xs text-muted/70 dark:text-dark-subtle" x-text="reviewReportCode"></p>
                    </div>
                    <button type="button" @click="showReviewModal = false" class="text-muted/60 hover:text-muted dark:hover:text-white">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form method="POST" :action="'{{ url('/dashboard/trust/reports') }}/' + reviewReportId + '/status'" class="space-y-4">
                    @csrf

                    <div class="p-3 bg-warm-50 dark:bg-[#182234] rounded-xl text-xs space-y-1">
                        <span class="font-semibold text-muted dark:text-white block">{{ __('Flag Reason:') }}</span>
                        <p class="text-muted/80 dark:text-dark-subtle" x-text="reviewReason"></p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-muted/70 dark:text-dark-subtle uppercase mb-1">
                            {{ __('Moderation Decision Status') }} <span class="text-rose-500">*</span>
                        </label>
                        <select name="status" x-model="reviewStatus" required class="input w-full text-xs">
                            <option value="under_review">{{ __('Under Review (Assigned for Investigation)') }}</option>
                            <option value="resolved">{{ __('Resolved (Violation Addressed / Listing Suspended)') }}</option>
                            <option value="dismissed">{{ __('Dismissed (False Positive / Permissible Content)') }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-muted/70 dark:text-dark-subtle uppercase mb-1">
                            {{ __('Moderator Audit Notes') }}
                        </label>
                        <textarea name="moderator_notes" x-model="moderatorNotes" rows="3" class="input w-full text-xs" placeholder="{{ __('State investigation outcome or action taken...') }}"></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-3 border-t border-[#EBDBC9] dark:border-[#273244]">
                        <button type="button" @click="showReviewModal = false" class="btn btn-secondary text-xs">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit" class="btn btn-primary text-xs font-bold">
                            {{ __('Save Decision') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
