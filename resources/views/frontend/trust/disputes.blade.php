<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Dispute Center') }}</x-slot>

    <div x-data="{
        showCreateModal: false
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
            title="Dispute Center"
            description="Mediate conflicts between tenants, buyers, property managers, and escrow stakeholders."
            icon="scales"
            badge="Mediation">
            <x-slot:actions>
                <button type="button" @click="showCreateModal = true" class="btn btn-primary text-xs flex items-center gap-1.5 shadow-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    {{ __('File New Dispute') }}
                </button>
            </x-slot:actions>
        </x-page-header>

        @include('frontend.trust.partials.tabs')

        <div class="card overflow-hidden border border-[#EBDBC9] dark:border-[#273244] shadow-sm">
            <div class="p-6 border-b border-[#EBDBC9] dark:border-[#273244] flex gap-4 flex-wrap items-center justify-between">
                <form method="GET" action="{{ route('dashboard.trust.disputes') }}" class="flex items-center gap-3 flex-1 max-w-lg">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </div>
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="{{ __('Search disputes by ID, subject, or party...') }}"
                               class="form-input search-input pl-9 pr-8 w-full text-sm">
                        @if(request('search'))
                            <a href="{{ route('dashboard.trust.disputes') }}" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </a>
                        @endif
                    </div>
                    <select name="status" onchange="this.form.submit()" class="input max-w-xs text-sm">
                        <option value="">{{ __('All Statuses') }}</option>
                        <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>{{ __('Open') }}</option>
                        <option value="under_review" {{ request('status') === 'under_review' ? 'selected' : '' }}>{{ __('Under Review') }}</option>
                        <option value="escalated" {{ request('status') === 'escalated' ? 'selected' : '' }}>{{ __('Escalated') }}</option>
                        <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>{{ __('Resolved') }}</option>
                        <option value="dismissed" {{ request('status') === 'dismissed' ? 'selected' : '' }}>{{ __('Dismissed') }}</option>
                    </select>
                </form>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-warm-50/50 dark:bg-[#1E293B]/50 border-b border-[#EBDBC9] dark:border-[#273244]">
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Case ID') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Complainant (A)') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Respondent (B)') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Type / Subject') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Contested Sum') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Priority') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EBDBC9]/50 dark:divide-[#273244]">
                        @forelse($disputes as $dispute)
                            <tr class="hover:bg-surface dark:hover:bg-surface/30 transition-colors">
                                <td class="p-4 font-mono font-bold text-primary-600 dark:text-primary-400 text-sm">
                                    <a href="{{ route('dashboard.trust.cases.show', $dispute['raw_id']) }}" class="hover:underline">
                                        {{ $dispute['id'] }}
                                    </a>
                                </td>
                                <td class="p-4 text-sm font-medium text-muted dark:text-white">{{ $dispute['plaintiff'] }}</td>
                                <td class="p-4 text-sm text-muted dark:text-dark-subtle">{{ $dispute['defendant'] }}</td>
                                <td class="p-4">
                                    <p class="font-bold text-muted dark:text-white text-xs">{{ $dispute['reason'] }}</p>
                                    <p class="text-[11px] text-muted/70 dark:text-dark-subtle mt-0.5">{{ $dispute['type'] }}</p>
                                </td>
                                <td class="p-4 font-bold text-muted dark:text-white">{{ $dispute['amount'] }}</td>
                                <td class="p-4">
                                    @php
                                        $pClass = 'badge-neutral';
                                        if ($dispute['priority'] === 'urgent') $pClass = 'badge-error';
                                        elseif ($dispute['priority'] === 'high') $pClass = 'badge-warning';
                                        elseif ($dispute['priority'] === 'medium') $pClass = 'badge-primary';
                                    @endphp
                                    <span class="badge {{ $pClass }} uppercase text-[10px]">{{ $dispute['priority'] }}</span>
                                </td>
                                <td class="p-4">
                                    @php
                                        $sClass = 'badge-neutral';
                                        if ($dispute['status'] === 'escalated') $sClass = 'badge-error';
                                        elseif ($dispute['status'] === 'under_review' || $dispute['status'] === 'investigating') $sClass = 'badge-warning';
                                        elseif ($dispute['status'] === 'resolved') $sClass = 'badge-success';
                                        elseif ($dispute['status'] === 'open') $sClass = 'badge-error';
                                    @endphp
                                    <span class="badge {{ $sClass }} uppercase text-xs">{{ str_replace('_', ' ', $dispute['status']) }}</span>
                                </td>
                                <td class="p-4 text-right space-x-2 whitespace-nowrap">
                                    <a href="{{ route('dashboard.trust.cases.show', $dispute['raw_id']) }}" class="btn btn-secondary text-xs py-1 px-3">
                                        {{ __('Mediate') }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-8 text-center text-muted dark:text-dark-subtle">
                                    <p class="text-sm font-medium">{{ __('No dispute cases found matching criteria.') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($disputes, 'links'))
                <div class="p-4 border-t border-[#EBDBC9] dark:border-[#273244]">
                    {{ $disputes->links() }}
                </div>
            @endif
        </div>

        {{-- Create Dispute Case Modal --}}
        <div x-show="showCreateModal"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <div class="card max-w-lg w-full p-6 space-y-4 shadow-2xl bg-surface dark:bg-[#1E293B]" @click.outside="showCreateModal = false">
                <div class="flex justify-between items-center pb-3 border-b border-[#EBDBC9] dark:border-[#273244]">
                    <div>
                        <h3 class="font-bold text-lg text-muted dark:text-white">{{ __('Log New Dispute Case') }}</h3>
                        <p class="text-xs text-muted/70 dark:text-dark-subtle">{{ __('Initiate formal mediation dossier for lease, escrow, or contract conflict') }}</p>
                    </div>
                    <button type="button" @click="showCreateModal = false" class="text-muted/60 hover:text-muted dark:hover:text-white">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('dashboard.trust.cases.store') }}" class="space-y-4">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-muted/70 dark:text-dark-subtle uppercase mb-1">
                                {{ __('Complainant (Party A)') }} <span class="text-rose-500">*</span>
                            </label>
                            <select name="complainant_id" required class="input w-full text-xs">
                                <option value="">{{ __('Select Complainant...') }}</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-muted/70 dark:text-dark-subtle uppercase mb-1">
                                {{ __('Respondent (Party B)') }} <span class="text-rose-500">*</span>
                            </label>
                            <select name="respondent_id" required class="input w-full text-xs">
                                <option value="">{{ __('Select Respondent...') }}</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-muted/70 dark:text-dark-subtle uppercase mb-1">
                                {{ __('Dispute Category') }} <span class="text-rose-500">*</span>
                            </label>
                            <select name="type" required class="input w-full text-xs">
                                <option value="contract_dispute">{{ __('Contract & Terms Dispute') }}</option>
                                <option value="payment_issue">{{ __('Security Deposit / Payment Withholding') }}</option>
                                <option value="damage_claim">{{ __('Property Damage Claim') }}</option>
                                <option value="fraud_suspect">{{ __('Suspected Scammer / Fraud') }}</option>
                                <option value="escrow_violation">{{ __('Escrow Condition Breach') }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-muted/70 dark:text-dark-subtle uppercase mb-1">
                                {{ __('Priority Level') }} <span class="text-rose-500">*</span>
                            </label>
                            <select name="priority" required class="input w-full text-xs">
                                <option value="low">{{ __('Low (Routine inquiry)') }}</option>
                                <option value="medium" selected>{{ __('Medium (Standard mediation)') }}</option>
                                <option value="high">{{ __('High (Fund freeze requested)') }}</option>
                                <option value="urgent">{{ __('Urgent (Immediate intervention)') }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-muted/70 dark:text-dark-subtle uppercase mb-1">
                                {{ __('Contested Sum (ETB)') }}
                            </label>
                            <input type="number" step="0.01" name="amount_in_dispute" placeholder="0.00" class="input w-full text-xs">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-muted/70 dark:text-dark-subtle uppercase mb-1">
                                {{ __('Related Property (Optional)') }}
                            </label>
                            <select name="disputable_id" class="input w-full text-xs">
                                <option value="">{{ __('None / Direct Party Dispute') }}</option>
                                @foreach($properties as $prop)
                                    <option value="{{ $prop->id }}">{{ $prop->title }} ({{ $prop->property_code }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-muted/70 dark:text-dark-subtle uppercase mb-1">
                            {{ __('Dispute Subject') }} <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="subject" required placeholder="e.g. Failure to return 30,000 ETB deposit following lease completion" class="input w-full text-xs">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-muted/70 dark:text-dark-subtle uppercase mb-1">
                            {{ __('Case Narrative & Particulars') }} <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="description" rows="3" required placeholder="{{ __('Detailed timeline of events, agreements reached, and reasons for filing...') }}" class="input w-full text-xs"></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-3 border-t border-[#EBDBC9] dark:border-[#273244]">
                        <button type="button" @click="showCreateModal = false" class="btn btn-secondary text-xs">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit" class="btn btn-primary text-xs font-bold">
                            {{ __('Register Dispute Case') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
