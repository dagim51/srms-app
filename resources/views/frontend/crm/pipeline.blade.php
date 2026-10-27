<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Pipeline') }}</x-slot>

    <div x-data="{ showModal: false, targetStage: 'new' }" class="max-w-7xl mx-auto page-enter h-[calc(100vh-140px)] flex flex-col space-y-4">
        @if(session('success'))
            <div class="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-xs flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" @click="$el.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800">&times;</button>
            </div>
        @endif

        <x-page-header
            title="Visual Deal Pipeline"
            description="Kanban-style sales and lease negotiation stages from initial inquiry to closed deals."
            icon="chart"
            badge="Deal Pipeline">
            <x-slot:actions>
                <button @click="targetStage = 'new'; showModal = true" class="btn btn-primary text-xs flex items-center gap-1.5 shadow-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>{{ __('Add Lead') }}</span>
                </button>
            </x-slot:actions>
        </x-page-header>

        @include('frontend.crm.partials.tabs')

        <div class="flex-1 overflow-x-auto overflow-y-hidden pb-4">
            <div class="flex h-full gap-4 min-w-max">
                @foreach($pipeline as $stage => $stageLeads)
                    <div class="card w-80 flex flex-col bg-surface dark:bg-[#172033] rounded-2xl border border-warm dark:border-warm h-full max-h-full">
                        <div class="p-4 border-b border-warm dark:border-warm flex justify-between items-center bg-white dark:bg-[#1E293B] rounded-t-2xl">
                            <div class="flex items-center gap-2">
                                <h3 class="font-bold text-muted dark:text-white text-sm">{{ __($stage) }}</h3>
                                <span class="bg-surface-subtle dark:bg-surface text-muted dark:text-dark-subtle text-xs font-bold px-2 py-0.5 rounded-full">{{ count($stageLeads) }}</span>
                            </div>
                            <button @click="targetStage = '{{ strtolower($stage) }}'; showModal = true" class="text-xs text-muted hover:text-primary-600 p-1 rounded-md" title="{{ __('Add lead to') }} {{ $stage }}">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            </button>
                        </div>
                        
                        <div class="p-3 flex-1 overflow-y-auto space-y-3">
                            @foreach($stageLeads as $lead)
                                <div class="card card-hover bg-white dark:bg-[#1E293B] border border-warm dark:border-warm p-4 rounded-xl hover:border-primary-500 transition-colors space-y-3">
                                    <div class="flex justify-between items-start">
                                        <a href="{{ route('dashboard.crm.leads.show', $lead['id']) }}" class="font-bold text-muted dark:text-white hover:text-primary-600 transition-colors text-sm">
                                            {{ $lead['name'] }}
                                        </a>
                                        <div class="avatar avatar-xs avatar-brand" title="{{ __('Assigned to') }} {{ $lead['assigned_to'] }}">
                                            {{ substr($lead['assigned_to'], 0, 1) }}
                                        </div>
                                    </div>

                                    <p class="text-xs text-muted dark:text-muted truncate">{{ $lead['property'] }}</p>
                                    
                                    <div class="pt-2 border-t border-warm dark:border-warm flex items-center justify-between gap-2">
                                        <span class="text-[11px] text-muted">{{ $lead['last_contact'] }}</span>
                                        
                                        {{-- Move Stage Dropdown --}}
                                        <form method="POST" action="{{ route('dashboard.crm.leads.stage', $lead['id']) }}" class="inline">
                                            @csrf
                                            <select name="status" onchange="this.form.submit()" class="form-select text-[11px] py-1 px-2 border-warm dark:border-warm rounded-lg bg-surface dark:bg-[#111827] focus:ring-1 focus:ring-primary-500">
                                                @foreach(['New', 'Contacted', 'Qualified', 'Viewing', 'Negotiation', 'Offer', 'Won', 'Lost'] as $s)
                                                    <option value="{{ strtolower($s) }}" {{ strtolower($lead['status'] ?? '') === strtolower($s) ? 'selected' : '' }}>
                                                        {{ __($s) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                            
                            @if(count($stageLeads) === 0)
                                <div @click="targetStage = '{{ strtolower($stage) }}'; showModal = true" class="h-24 flex flex-col items-center justify-center border-2 border-dashed border-warm dark:border-warm rounded-xl text-xs font-medium text-muted hover:border-primary-500 cursor-pointer transition-colors p-2 text-center">
                                    <svg class="w-5 h-5 mb-1 text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    <span>{{ __('Add to') }} {{ __($stage) }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Add Lead Modal --}}
        <div x-show="showModal"
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
            <div @click.away="showModal = false"
                 class="card max-w-lg w-full bg-white dark:bg-[#1E293B] rounded-2xl shadow-xl border border-warm dark:border-warm overflow-hidden">
                <div class="p-6 border-b border-warm dark:border-warm flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-bold text-muted dark:text-white">{{ __('Create Pipeline Deal / Lead') }}</h3>
                        <p class="text-xs text-muted">{{ __('Capture buyer or tenant directly into pipeline.') }}</p>
                    </div>
                    <button @click="showModal = false" class="text-muted hover:text-slate-700 dark:hover:text-slate-200 text-lg">&times;</button>
                </div>
                <form method="POST" action="{{ route('dashboard.crm.leads.store') }}" class="p-6 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-muted dark:text-white mb-1">{{ __('Full Name') }} *</label>
                        <input type="text" name="name" required class="form-input w-full text-sm" placeholder="e.g. Liya Kebede">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-muted dark:text-white mb-1">{{ __('Email Address') }}</label>
                            <input type="email" name="email" class="form-input w-full text-sm" placeholder="e.g. liya@example.com">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-muted dark:text-white mb-1">{{ __('Phone Number') }}</label>
                            <input type="tel" name="phone" class="form-input w-full text-sm" placeholder="e.g. +251 92 345 6789">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-muted dark:text-white mb-1">{{ __('Stage') }}</label>
                            <select name="status" x-model="targetStage" class="form-select w-full text-sm">
                                <option value="new">{{ __('New') }}</option>
                                <option value="contacted">{{ __('Contacted') }}</option>
                                <option value="qualified">{{ __('Qualified') }}</option>
                                <option value="viewing">{{ __('Viewing') }}</option>
                                <option value="negotiation">{{ __('Negotiation') }}</option>
                                <option value="offer">{{ __('Offer') }}</option>
                                <option value="won">{{ __('Won') }}</option>
                                <option value="lost">{{ __('Lost') }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-muted dark:text-white mb-1">{{ __('Inquiry Source') }}</label>
                            <select name="source" class="form-select w-full text-sm">
                                <option value="inquiry">{{ __('Inquiry (Portal)') }}</option>
                                <option value="web">{{ __('Website') }}</option>
                                <option value="walk_in">{{ __('Walk-in Client') }}</option>
                                <option value="referral">{{ __('Direct Referral') }}</option>
                            </select>
                        </div>
                    </div>

                    @if(!empty($listings) && count($listings) > 0)
                        <div>
                            <label class="block text-xs font-bold text-muted dark:text-white mb-1">{{ __('Property / Deal Listing') }}</label>
                            <select name="listing_id" class="form-select w-full text-sm">
                                <option value="">{{ __('— Select Listing —') }}</option>
                                @foreach($listings as $item)
                                    <option value="{{ $item->id }}">{{ $item->property?->title ?? 'Listing #' . substr($item->id, 0, 8) }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    @if(!empty($agents) && count($agents) > 0)
                        <div>
                            <label class="block text-xs font-bold text-muted dark:text-white mb-1">{{ __('Assign Agent') }}</label>
                            <select name="assigned_to" class="form-select w-full text-sm">
                                @foreach($agents as $agent)
                                    <option value="{{ $agent->id }}" {{ $agent->id === auth()->id() ? 'selected' : '' }}>{{ $agent->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div>
                        <label class="block text-xs font-bold text-muted dark:text-white mb-1">{{ __('Deal Notes') }}</label>
                        <textarea name="notes" rows="2" class="form-input w-full text-sm" placeholder="Notes, budget constraints, timeline..."></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-warm dark:border-warm">
                        <button type="button" @click="showModal = false" class="btn btn-secondary text-sm">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary text-sm">{{ __('Add to Pipeline') }}</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
