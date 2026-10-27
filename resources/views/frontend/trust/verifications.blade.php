<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Verifications Queue') }}</x-slot>

    <div x-data="{
        showReviewModal: false,
        reviewId: '',
        reviewApplicant: '',
        reviewType: '',
        reviewStatus: 'approved',
        reviewRiskScore: 'low',
        reviewNotes: '',
        openReview(id, applicant, type, status, risk, notes) {
            this.reviewId = id;
            this.reviewApplicant = applicant;
            this.reviewType = type;
            this.reviewStatus = status === 'pending_review' || status === 'pending' ? 'approved' : status;
            this.reviewRiskScore = risk || 'low';
            this.reviewNotes = notes || '';
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
            title="Verifications Queue"
            description="Review identity submissions, seller cadastral credentials, developer track records, and property deed verifications."
            icon="shield"
            badge="Trust & Verifications" />

        @include('frontend.trust.partials.tabs')

        <!-- Stat Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
            <x-card-stat
                :label="__('Total Verifications')"
                :value="$stats['total']"
                :badge="__('Total')"
                badgeVariant="brand"
                iconColor="orange">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg>
                </x-slot:icon>
            </x-card-stat>

            <x-card-stat
                :label="__('Pending Review')"
                :value="$stats['pending']"
                :badge="__('Action Needed')"
                badgeVariant="warning"
                iconColor="amber">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </x-slot:icon>
            </x-card-stat>

            <x-card-stat
                :label="__('Approved Credentials')"
                :value="$stats['approved']"
                :badge="__('Verified')"
                badgeVariant="success"
                iconColor="green">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </x-slot:icon>
            </x-card-stat>

            <x-card-stat
                :label="__('Rejected / Flagged')"
                :value="$stats['rejected']"
                :badge="__('Rejected')"
                badgeVariant="error"
                iconColor="rose">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </x-slot:icon>
            </x-card-stat>
        </div>

        <div class="card overflow-hidden border border-[#EBDBC9] dark:border-[#273244] shadow-sm">
            <div class="p-6 border-b border-[#EBDBC9] dark:border-[#273244] flex gap-4 flex-wrap items-center justify-between">
                <form method="GET" action="{{ route('dashboard.trust.verifications') }}" class="flex items-center gap-3 flex-1 max-w-xl flex-wrap sm:flex-nowrap">
                    <div class="relative flex-1 min-w-[200px]">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </div>
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="{{ __('Search by applicant, ID or category...') }}"
                               class="form-input search-input pl-9 pr-8 w-full text-sm">
                        @if(request('search'))
                            <a href="{{ route('dashboard.trust.verifications') }}" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </a>
                        @endif
                    </div>

                    <select name="type" onchange="this.form.submit()" class="input max-w-xs text-sm">
                        <option value="">{{ __('All Categories') }}</option>
                        <option value="identity" {{ request('type') === 'identity' ? 'selected' : '' }}>{{ __('Identity (Fayda / Passport)') }}</option>
                        <option value="seller" {{ request('type') === 'seller' ? 'selected' : '' }}>{{ __('Seller Verification') }}</option>
                        <option value="developer" {{ request('type') === 'developer' ? 'selected' : '' }}>{{ __('Developer Licensing') }}</option>
                        <option value="title_deed" {{ request('type') === 'title_deed' ? 'selected' : '' }}>{{ __('Cadastral Title Deed') }}</option>
                    </select>

                    <select name="status" onchange="this.form.submit()" class="input max-w-xs text-sm">
                        <option value="">{{ __('All Statuses') }}</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('Pending Review') }}</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>{{ __('Approved') }}</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>{{ __('Rejected') }}</option>
                        <option value="additional_info_required" {{ request('status') === 'additional_info_required' ? 'selected' : '' }}>{{ __('More Info Required') }}</option>
                    </select>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-warm-50/50 dark:bg-[#1E293B]/50 border-b border-[#EBDBC9] dark:border-[#273244]">
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Applicant / Entity') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Verification Type') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Risk Score') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Submitted Date') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EBDBC9]/50 dark:divide-[#273244]">
                        @forelse($verifications as $v)
                            <tr class="hover:bg-surface dark:hover:bg-surface/30 transition-colors">
                                <td class="p-4">
                                    <p class="font-bold text-muted dark:text-white text-sm">{{ $v->user?->name ?? 'Direct Applicant' }}</p>
                                    <p class="text-xs text-muted/70 dark:text-dark-subtle mt-0.5">{{ $v->user?->email ?? 'N/A' }}</p>
                                </td>
                                <td class="p-4 text-sm text-muted dark:text-white font-medium">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-warm-100/70 dark:bg-[#172033]/80 border border-[#EBDBC9]/80 dark:border-[#273244]/80 text-xs font-bold">
                                        {{ ucwords(str_replace('_', ' ', $v->type)) }}
                                    </span>
                                </td>
                                <td class="p-4">
                                    @php
                                        $rClass = 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800';
                                        if ($v->risk_score === 'high') {
                                            $rClass = 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-400 border-rose-200 dark:border-rose-800';
                                        } elseif ($v->risk_score === 'medium') {
                                            $rClass = 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 border-amber-200 dark:border-amber-800';
                                        }
                                    @endphp
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold uppercase border {{ $rClass }}">
                                        {{ $v->risk_score ?? 'low' }} risk
                                    </span>
                                </td>
                                <td class="p-4 text-sm text-muted dark:text-dark-subtle">
                                    {{ $v->created_at ? $v->created_at->format('M d, Y H:i') : now()->format('M d, Y') }}
                                </td>
                                <td class="p-4">
                                    @php
                                        $sClass = 'badge-warning';
                                        if ($v->status === 'approved') $sClass = 'badge-success';
                                        elseif ($v->status === 'rejected') $sClass = 'badge-error';
                                        elseif ($v->status === 'additional_info_required') $sClass = 'badge-primary';
                                    @endphp
                                    <span class="badge {{ $sClass }} uppercase text-xs">
                                        {{ str_replace('_', ' ', $v->status) }}
                                    </span>
                                </td>
                                <td class="p-4 text-right space-x-2 whitespace-nowrap">
                                    <button type="button"
                                            @click="openReview('{{ $v->id }}', '{{ addslashes($v->user?->name ?? 'Applicant') }}', '{{ addslashes(str_replace('_', ' ', $v->type)) }}', '{{ $v->status }}', '{{ $v->risk_score }}', '{{ addslashes($v->notes ?? '') }}')"
                                            class="btn btn-secondary text-xs py-1 px-3">
                                        {{ __('Review Credential') }}
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-muted dark:text-dark-subtle">
                                    <p class="text-sm font-medium">{{ __('No verification applications match criteria.') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($verifications, 'links'))
                <div class="p-4 border-t border-[#EBDBC9] dark:border-[#273244]">
                    {{ $verifications->links() }}
                </div>
            @endif
        </div>

        {{-- Verification Review Modal --}}
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
                        <h3 class="font-bold text-lg text-muted dark:text-white">{{ __('Verify Credential Dossier') }}</h3>
                        <p class="text-xs text-muted/70 dark:text-dark-subtle" x-text="reviewApplicant + ' • ' + reviewType"></p>
                    </div>
                    <button type="button" @click="showReviewModal = false" class="text-muted/60 hover:text-muted dark:hover:text-white">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form method="POST" :action="'{{ url('/dashboard/trust/verifications') }}/' + reviewId + '/review'" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold text-muted/70 dark:text-dark-subtle uppercase mb-1">
                            {{ __('Verification Outcome') }} <span class="text-rose-500">*</span>
                        </label>
                        <select name="status" x-model="reviewStatus" required class="input w-full text-xs">
                            <option value="approved">{{ __('Approved (Award Verified Badge & Clear Status)') }}</option>
                            <option value="rejected">{{ __('Rejected (Invalid or Forged Documentation)') }}</option>
                            <option value="additional_info_required">{{ __('Request Additional Certified Documents') }}</option>
                            <option value="pending_review">{{ __('Keep in Pending Queue') }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-muted/70 dark:text-dark-subtle uppercase mb-1">
                            {{ __('Assessed Risk Rating') }} <span class="text-rose-500">*</span>
                        </label>
                        <select name="risk_score" x-model="reviewRiskScore" required class="input w-full text-xs">
                            <option value="low">{{ __('Low Risk (Standard Clean Profile)') }}</option>
                            <option value="medium">{{ __('Medium Risk (Caution Advised / New Account)') }}</option>
                            <option value="high">{{ __('High Risk (Flag for Enhanced Monitoring)') }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-muted/70 dark:text-dark-subtle uppercase mb-1">
                            {{ __('Verification Officer Audit Notes') }}
                        </label>
                        <textarea name="notes" x-model="reviewNotes" rows="3" class="input w-full text-xs" placeholder="{{ __('State verified document numbers, registry checks, or rejection reasons...') }}"></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-3 border-t border-[#EBDBC9] dark:border-[#273244]">
                        <button type="button" @click="showReviewModal = false" class="btn btn-secondary text-xs">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit" class="btn btn-primary text-xs font-bold">
                            {{ __('Confirm Decision') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
