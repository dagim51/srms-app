<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Safety Center') }}</x-slot>

    <div x-data="{
        showCreateModal: false,
        complainantId: '',
        respondentId: '',
        disputeType: 'contract_dispute',
        priority: 'medium',
        subject: '',
        amount: '',
        description: ''
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
            title="Trust & Safety Center"
            description="Investigate reported listings, review dispute cases, verify sellers & developers, and enforce marketplace integrity."
            icon="shield"
            badge="Trust & Safety">
            <x-slot:actions>
                <div class="flex items-center gap-2">
                    <button type="button" @click="showCreateModal = true" class="btn btn-primary text-xs flex items-center gap-1.5 shadow-sm">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        {{ __('Open Dispute Case') }}
                    </button>
                </div>
            </x-slot:actions>
        </x-page-header>

        @include('frontend.trust.partials.tabs')

        <!-- Stats Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
            <x-card-stat
                :label="__('Active Cases')"
                :value="$stats['active_cases']"
                :badge="__('Cases')"
                badgeVariant="brand"
                iconColor="orange">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg>
                </x-slot:icon>
            </x-card-stat>
            
            <x-card-stat
                :label="__('Pending Verifications')"
                :value="$stats['pending_verifications'] ?? 0"
                :badge="__('KYC / Deeds')"
                badgeVariant="info"
                iconColor="blue">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                </x-slot:icon>
            </x-card-stat>

            <x-card-stat
                :label="__('New User Reports')"
                :value="$stats['new_reports']"
                :badge="__('Reports')"
                badgeVariant="error"
                iconColor="rose">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
                </x-slot:icon>
            </x-card-stat>
            
            <x-card-stat
                :label="__('Open Disputes')"
                :value="$stats['open_disputes']"
                :badge="__('Disputes')"
                badgeVariant="warning"
                iconColor="amber">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </x-slot:icon>
            </x-card-stat>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Recent Activity Table -->
            <div class="lg:col-span-2 card p-6 border border-[#EBDBC9] dark:border-[#273244] shadow-sm">
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-[#EBDBC9] dark:border-[#273244]">
                    <div>
                        <h2 class="card-title text-lg font-bold text-muted dark:text-white">{{ __('Active Dispute Dossiers') }}</h2>
                        <p class="text-xs text-muted/70 dark:text-dark-subtle">{{ __('Formal mediation cases awaiting or under officer review') }}</p>
                    </div>
                    <a href="{{ route('dashboard.trust.disputes') }}" class="text-xs font-semibold text-primary-600 hover:text-primary-700 dark:text-primary-400">
                        {{ __('View All Disputes') }} &rarr;
                    </a>
                </div>

                <div class="overflow-x-auto border border-[#EBDBC9] dark:border-[#273244] rounded-xl overflow-hidden">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-warm-100/60 dark:bg-[#1E293B] border-b border-[#EBDBC9] dark:border-[#273244]">
                                <th class="p-3 text-xs font-bold text-muted dark:text-muted uppercase">{{ __('Case ID') }}</th>
                                <th class="p-3 text-xs font-bold text-muted dark:text-muted uppercase">{{ __('Type') }}</th>
                                <th class="p-3 text-xs font-bold text-muted dark:text-muted uppercase">{{ __('Subject') }}</th>
                                <th class="p-3 text-xs font-bold text-muted dark:text-muted uppercase">{{ __('Status') }}</th>
                                <th class="p-3 text-xs font-bold text-muted dark:text-muted uppercase text-right">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#EBDBC9]/50 dark:divide-[#273244]">
                            @forelse($recentActivity as $activity)
                                <tr class="hover:bg-warm-50/30 dark:hover:bg-[#1E293B]/30 transition-colors">
                                    <td class="p-3 font-mono font-bold text-primary-600 dark:text-primary-400 text-xs">
                                        {{ $activity['id'] }}
                                    </td>
                                    <td class="p-3 text-xs font-medium text-muted dark:text-dark-subtle">{{ $activity['type'] }}</td>
                                    <td class="p-3 text-sm text-muted dark:text-white font-medium truncate max-w-[200px]">{{ $activity['subject'] }}</td>
                                    <td class="p-3">
                                        @php
                                            $sClass = 'badge-neutral';
                                            if ($activity['status'] === 'investigating' || $activity['status'] === 'escalated') $sClass = 'badge-warning';
                                            elseif ($activity['status'] === 'resolved' || $activity['status'] === 'action_taken') $sClass = 'badge-success';
                                            elseif ($activity['status'] === 'open') $sClass = 'badge-error';
                                        @endphp
                                        <span class="badge {{ $sClass }} uppercase text-xs">{{ str_replace('_', ' ', $activity['status']) }}</span>
                                    </td>
                                    <td class="p-3 text-right">
                                        <a href="{{ route('dashboard.trust.cases.show', $activity['raw_id'] ?? $activity['id']) }}" class="btn btn-secondary text-xs py-1 px-2.5">
                                            {{ __('Review') }}
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="p-6 text-center text-muted dark:text-dark-subtle text-xs">
                                        {{ __('No open disputes currently active.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pending Verifications Triage Panel -->
            <div class="card p-6 border border-[#EBDBC9] dark:border-[#273244] shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-[#EBDBC9] dark:border-[#273244]">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg bg-blue-100 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <h3 class="font-bold text-base text-muted dark:text-white">{{ __('Verifications Queue') }}</h3>
                        </div>
                        <a href="{{ route('dashboard.trust.verifications') }}" class="text-xs font-semibold text-primary-600 dark:text-primary-400 hover:underline">
                            {{ __('All') }} &rarr;
                        </a>
                    </div>

                    <div class="space-y-3">
                        @forelse($recentVerifications as $v)
                            <div class="p-3 rounded-xl bg-warm-50/50 dark:bg-[#172033]/50 border border-[#EBDBC9]/70 dark:border-[#273244]/70 flex items-center justify-between">
                                <div class="min-w-0 pr-2">
                                    <p class="font-bold text-xs text-muted dark:text-white truncate">{{ $v->user?->name ?? 'Applicant' }}</p>
                                    <p class="text-[11px] text-muted/70 dark:text-dark-subtle uppercase mt-0.5">{{ str_replace('_', ' ', $v->type) }}</p>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    @php
                                        $vClass = 'badge-warning';
                                        if ($v->status === 'approved') $vClass = 'badge-success';
                                        elseif ($v->status === 'rejected') $vClass = 'badge-error';
                                    @endphp
                                    <span class="badge {{ $vClass }} text-[10px] uppercase">{{ str_replace('_', ' ', $v->status) }}</span>
                                    <a href="{{ route('dashboard.trust.verifications', ['search' => $v->user?->name ?? $v->id]) }}" class="text-xs font-semibold text-primary-600 hover:underline">
                                        &rarr;
                                    </a>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-muted dark:text-dark-subtle text-center py-4">{{ __('No pending identity or credential verifications.') }}</p>
                        @endforelse
                    </div>
                </div>

                <div class="pt-4 mt-4 border-t border-[#EBDBC9] dark:border-[#273244]">
                    <a href="{{ route('dashboard.trust.verifications') }}" class="btn btn-secondary w-full text-xs font-bold justify-center">
                        {{ __('Open Verification Desk') }}
                    </a>
                </div>
            </div>
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
                        <p class="text-xs text-muted/70 dark:text-dark-subtle">{{ __('Initiate a formal mediation dossier for lease, escrow, or contract conflict') }}</p>
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
