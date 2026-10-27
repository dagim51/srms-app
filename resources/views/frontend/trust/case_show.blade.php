<x-layouts.dashboard>
    <x-slot name="page-title">{{ $case['subject'] }}</x-slot>

    <div x-data="{
        showResolveModal: false,
        showEvidenceModal: false,
        resolveStatus: 'resolved',
        resolutionNotes: ''
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

        <div class="flex items-center justify-between">
            <a href="{{ route('dashboard.trust.disputes') }}" class="text-xs font-bold text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white flex items-center gap-2 transition-colors">
                <svg class="w-4 h-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                <span>{{ __('Back to Dispute Mediation Center') }}</span>
            </a>
        </div>

        <x-page-header
            :title="$case['subject']"
            :description="($case['code'] ?? $case['id']) . ' • ' . __('Filed on') . ' ' . $case['created_at'] . ' • ' . __('Type:') . ' ' . $case['type']"
            icon="shield"
            :badge="strtoupper($case['status'])">
            <x-slot:actions>
                <div class="flex gap-2 flex-wrap items-center">
                    @if(!($case['is_report'] ?? false))
                        <button type="button" @click="showEvidenceModal = true" class="btn btn-secondary text-xs flex items-center gap-1.5 shadow-sm">
                            <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                            </svg>
                            {{ __('Attach Evidence') }}
                        </button>
                    @endif

                    <button type="button"
                            @click="showResolveModal = true"
                            class="btn btn-primary text-xs flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white border-0 shadow-sm font-bold">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ __('Issue Mediation Decision') }}
                    </button>
                </div>
            </x-slot:actions>
        </x-page-header>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-8">
                <!-- Case Summary Card -->
                <div class="card p-6 md:p-8 border border-[#EBDBC9] dark:border-[#273244] shadow-sm">
                    <div class="flex items-center justify-between pb-4 mb-6 border-b border-[#EBDBC9] dark:border-[#273244]">
                        <h2 class="card-title text-xl">{{ __('Case Particulars & Narrative') }}</h2>
                        <span class="badge {{ $case['status'] === 'resolved' ? 'badge-success' : 'badge-warning' }} uppercase text-xs">
                            {{ str_replace('_', ' ', $case['status']) }}
                        </span>
                    </div>
                    
                    <div class="p-4 bg-warm-50/60 dark:bg-[#172033]/60 rounded-xl border border-[#EBDBC9] dark:border-[#273244] mb-6 text-sm text-muted dark:text-dark-subtle leading-relaxed">
                        {{ $case['description'] }}
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 pt-2">
                        <div>
                            <p class="card-kv-label">{{ __('Complainant (Plaintiff)') }}</p>
                            <p class="card-kv-value font-bold mt-1 text-sm text-muted dark:text-white">{{ $case['plaintiff'] }}</p>
                        </div>
                        <div>
                            <p class="card-kv-label">{{ __('Respondent (Defendant)') }}</p>
                            <p class="card-kv-value font-bold mt-1 text-sm text-muted dark:text-white">{{ $case['defendant'] }}</p>
                        </div>
                        <div>
                            <p class="card-kv-label">{{ __('Contested Claim Amount') }}</p>
                            <p class="card-kv-value font-black text-xl text-primary-600 dark:text-primary-400 mt-1">{{ $case['amount_contested'] }}</p>
                        </div>
                    </div>

                    @if(!empty($case['resolution_notes']))
                        <div class="mt-6 pt-6 border-t border-[#EBDBC9] dark:border-[#273244]">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-muted/70 dark:text-dark-subtle mb-2">{{ __('Formal Mediator Resolution Record') }}</h3>
                            <div class="p-4 bg-emerald-50/50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-800 rounded-xl text-sm text-emerald-900 dark:text-emerald-200">
                                {{ $case['resolution_notes'] }}
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Attached Evidence Dossier -->
                <div class="card p-6 md:p-8 border border-[#EBDBC9] dark:border-[#273244] shadow-sm">
                    <div class="flex items-center justify-between mb-6 pb-4 border-b border-[#EBDBC9] dark:border-[#273244]">
                        <div>
                            <h2 class="card-title text-xl">{{ __('Evidentiary Submissions') }}</h2>
                            <p class="text-xs text-muted/70 dark:text-dark-subtle">{{ count($evidence) }} {{ __('certified document(s) & media artifacts') }}</p>
                        </div>
                        @if(!($case['is_report'] ?? false))
                            <button type="button" @click="showEvidenceModal = true" class="btn btn-secondary text-xs py-1.5 px-3">
                                {{ __('+ Add Document') }}
                            </button>
                        @endif
                    </div>
                    
                    @if(count($evidence) > 0)
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @foreach($evidence as $item)
                                <div class="card-nested p-4 flex gap-4 items-start border border-[#EBDBC9] dark:border-[#273244] rounded-xl bg-surface dark:bg-[#1E293B]">
                                    <div class="w-10 h-10 rounded-lg bg-primary-50 dark:bg-primary-950/60 flex items-center justify-center text-primary-600 dark:text-primary-400 shadow-xs shrink-0">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="font-bold text-muted dark:text-white text-sm truncate" title="{{ $item['name'] }}">{{ $item['name'] }}</p>
                                        <p class="text-xs text-muted/70 dark:text-dark-subtle mt-0.5">{{ $item['type'] }} &bull; {{ $item['submitted_by'] }}</p>
                                        <p class="text-[11px] text-muted/60 dark:text-dark-subtle mt-0.5">{{ $item['date'] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-8 text-center bg-warm-50/30 dark:bg-[#182234]/30 rounded-xl border border-dashed border-[#EBDBC9] dark:border-[#273244]">
                            <svg class="w-8 h-8 text-muted/40 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                            <p class="text-xs font-medium text-muted dark:text-dark-subtle">{{ __('No independent evidence artifacts attached to this case dossier yet.') }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Investigation Tools & Timeline -->
            <div class="space-y-6">
                <!-- Mediation Actions Panel -->
                <div class="card p-6 border border-[#EBDBC9] dark:border-[#273244] shadow-sm">
                    <h3 class="font-bold text-base text-muted dark:text-white mb-4 pb-2 border-b border-[#EBDBC9] dark:border-[#273244] flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        </svg>
                        <span>{{ __('Mediation Governance') }}</span>
                    </h3>

                    <div class="space-y-3">
                        <button type="button" @click="showResolveModal = true; resolveStatus = 'resolved'" class="btn btn-primary w-full text-xs justify-center font-bold bg-emerald-600 hover:bg-emerald-700 text-white border-0 py-2">
                            {{ __('Issue Resolution Agreement') }}
                        </button>
                        <button type="button" @click="showResolveModal = true; resolveStatus = 'under_review'" class="btn btn-secondary w-full text-xs justify-center py-2">
                            {{ __('Mark Under Review (Assign SLA)') }}
                        </button>
                        <button type="button" @click="showResolveModal = true; resolveStatus = 'escalated'" class="btn btn-secondary w-full text-xs text-amber-600 border-amber-200 hover:bg-amber-50 dark:hover:bg-amber-950/30 justify-center py-2">
                            {{ __('Escalate to Legal Tribunal') }}
                        </button>
                        <button type="button" @click="showResolveModal = true; resolveStatus = 'dismissed'" class="btn btn-secondary w-full text-xs text-rose-600 border-rose-200 hover:bg-rose-50 dark:hover:bg-rose-950/30 justify-center py-2">
                            {{ __('Dismiss Case (No Standing)') }}
                        </button>
                    </div>
                </div>

                <!-- Case Timeline -->
                <div class="card p-6 border border-[#EBDBC9] dark:border-[#273244] shadow-sm">
                    <h3 class="font-bold text-sm text-muted dark:text-white mb-4 uppercase tracking-wider text-xs">
                        {{ __('Mediation Chronology') }}
                    </h3>
                    <div class="relative pl-6 space-y-4 before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-[#EBDBC9] dark:before:bg-[#273244]">
                        @foreach($timeline as $entry)
                            <div class="relative">
                                <div class="absolute -left-[1.65rem] top-1 w-3 h-3 rounded-full bg-primary-500 ring-4 ring-primary-100 dark:ring-primary-950"></div>
                                <div class="text-xs font-bold text-muted dark:text-white">{{ $entry['action'] }}</div>
                                <div class="text-[11px] text-muted/60 dark:text-dark-subtle">{{ $entry['date'] }} &bull; {{ $entry['user'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Mediation Decision Modal --}}
        <div x-show="showResolveModal"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <div class="card max-w-md w-full p-6 space-y-4 shadow-2xl bg-surface dark:bg-[#1E293B]" @click.outside="showResolveModal = false">
                <div class="flex justify-between items-center pb-3 border-b border-[#EBDBC9] dark:border-[#273244]">
                    <h3 class="font-bold text-lg text-muted dark:text-white">{{ __('Finalize Case Decision') }}</h3>
                    <button type="button" @click="showResolveModal = false" class="text-muted/60 hover:text-muted dark:hover:text-white">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('dashboard.trust.cases.resolve', $case['id']) }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold text-muted/70 dark:text-dark-subtle uppercase mb-1">
                            {{ __('Decision Outcome') }} <span class="text-rose-500">*</span>
                        </label>
                        <select name="status" x-model="resolveStatus" required class="input w-full text-xs">
                            <option value="resolved">{{ __('Resolved (Mutual Settlement Reached / Paid)') }}</option>
                            <option value="under_review">{{ __('Under Review (Further Evidence Demanded)') }}</option>
                            <option value="escalated">{{ __('Escalated (Referred to Legal Bureau)') }}</option>
                            <option value="closed">{{ __('Closed (Administrative Conclusion)') }}</option>
                            <option value="dismissed">{{ __('Dismissed (Claim Lacks Standing / Void)') }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-muted/70 dark:text-dark-subtle uppercase mb-1">
                            {{ __('Binding Mediator Rationale / Resolution Notes') }} <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="resolution_notes"
                                  rows="4"
                                  required
                                  placeholder="{{ __('State findings, agreed financial refunds/reversals, or dismissal basis...') }}"
                                  class="input w-full text-xs">{{ $case['resolution_notes'] ?? '' }}</textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-3 border-t border-[#EBDBC9] dark:border-[#273244]">
                        <button type="button" @click="showResolveModal = false" class="btn btn-secondary text-xs">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit" class="btn btn-primary text-xs font-bold">
                            {{ __('Save & Issue Ruling') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Attach Evidence Modal --}}
        @if(!($case['is_report'] ?? false))
            <div x-show="showEvidenceModal"
                 x-cloak
                 class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">
                <div class="card max-w-md w-full p-6 space-y-4 shadow-2xl bg-surface dark:bg-[#1E293B]" @click.outside="showEvidenceModal = false">
                    <div class="flex justify-between items-center pb-3 border-b border-[#EBDBC9] dark:border-[#273244]">
                        <h3 class="font-bold text-lg text-muted dark:text-white">{{ __('Attach Evidence Artifact') }}</h3>
                        <button type="button" @click="showEvidenceModal = false" class="text-muted/60 hover:text-muted dark:hover:text-white">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('dashboard.trust.cases.evidence', $case['id']) }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf

                        <div>
                            <label class="block text-xs font-bold text-muted/70 dark:text-dark-subtle uppercase mb-1">
                                {{ __('Document / Artifact Title') }} <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="title" required placeholder="e.g. Bank Payment Wire Slip or Damage Photo" class="input w-full text-xs">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-muted/70 dark:text-dark-subtle uppercase mb-1">
                                {{ __('Artifact Category') }} <span class="text-rose-500">*</span>
                            </label>
                            <select name="type" required class="input w-full text-xs">
                                <option value="document">{{ __('Signed Agreement / Contract') }}</option>
                                <option value="payment_slip">{{ __('Bank Transfer / Telebirr Receipt') }}</option>
                                <option value="image">{{ __('Property Inspection Photo') }}</option>
                                <option value="contract">{{ __('Deed or Title Documentation') }}</option>
                                <option value="audio">{{ __('Audio / Communication Transcript') }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-muted/70 dark:text-dark-subtle uppercase mb-1">
                                {{ __('Description / Context') }}
                            </label>
                            <textarea name="description" rows="2" placeholder="{{ __('Summary of what this document proves...') }}" class="input w-full text-xs"></textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-muted/70 dark:text-dark-subtle uppercase mb-1">
                                {{ __('Upload File (PDF, PNG, JPG)') }}
                            </label>
                            <input type="file" name="file" class="input w-full text-xs">
                        </div>

                        <div class="flex justify-end gap-3 pt-3 border-t border-[#EBDBC9] dark:border-[#273244]">
                            <button type="button" @click="showEvidenceModal = false" class="btn btn-secondary text-xs">
                                {{ __('Cancel') }}
                            </button>
                            <button type="submit" class="btn btn-primary text-xs font-bold">
                                {{ __('Upload Evidence') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

    </div>
</x-layouts.dashboard>
