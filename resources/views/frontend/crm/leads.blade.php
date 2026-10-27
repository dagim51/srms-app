<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('All Leads') }}</x-slot>

    <div x-data="{ showModal: false }" class="max-w-7xl mx-auto page-enter space-y-6">
        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-sm flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" @click="$el.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800">&times;</button>
            </div>
        @endif

        <x-page-header
            title="Lead Pipeline Management"
            description="Capture, score, and nurture inbound prospective buyers, tenants, and property inquiries."
            icon="chart"
            badge="Leads">
            <x-slot:actions>
                <button @click="showModal = true" class="btn btn-primary flex items-center gap-1.5 shadow-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>{{ __('Add Lead') }}</span>
                </button>
            </x-slot:actions>
        </x-page-header>

        @include('frontend.crm.partials.tabs')

        {{-- Actions & Filter Toolbar --}}
        <div class="card cinematic-fade-up cinematic-stagger-2 bg-white dark:bg-[#1E293B] rounded-xl p-4 border border-warm dark:border-warm space-y-4 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-muted dark:text-white">{{ __('CRM Leads & Inquiries') }}</h2>
                    <p class="text-xs text-muted dark:text-muted">{{ __('Prospect tracking, buyer intent pipeline, and assigned agent tasks') }}</p>
                </div>
            </div>

            {{-- Filter Form --}}
            <form method="GET" action="{{ route('dashboard.crm.leads') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-12 gap-3 pt-2 border-t border-warm dark:border-warm items-center">
                <div class="md:col-span-6 relative">
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="{{ __('Search leads by name, email, phone, or assignee...') }}"
                           class="form-input search-input w-full pl-9 pr-8 text-xs">
                    <svg class="w-4 h-4 text-muted absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                    @if(request('search'))
                        <a href="{{ route('dashboard.crm.leads', request()->except('search')) }}" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200" title="{{ __('Clear search') }}">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </a>
                    @endif
                </div>

                <div class="md:col-span-4">
                    <select name="status" onchange="this.form.submit()" class="form-select w-full text-xs">
                        <option value="">{{ __('All Statuses') }}</option>
                        <option value="new" {{ request('status') === 'new' ? 'selected' : '' }}>{{ __('New') }}</option>
                        <option value="contacted" {{ request('status') === 'contacted' ? 'selected' : '' }}>{{ __('Contacted') }}</option>
                        <option value="qualified" {{ request('status') === 'qualified' ? 'selected' : '' }}>{{ __('Qualified') }}</option>
                        <option value="viewing" {{ request('status') === 'viewing' ? 'selected' : '' }}>{{ __('Viewing') }}</option>
                        <option value="negotiation" {{ request('status') === 'negotiation' ? 'selected' : '' }}>{{ __('Negotiation') }}</option>
                        <option value="offer" {{ request('status') === 'offer' ? 'selected' : '' }}>{{ __('Offer') }}</option>
                        <option value="won" {{ request('status') === 'won' ? 'selected' : '' }}>{{ __('Won (Closed)') }}</option>
                        <option value="lost" {{ request('status') === 'lost' ? 'selected' : '' }}>{{ __('Lost') }}</option>
                    </select>
                </div>

                <div class="md:col-span-2 flex items-center gap-2">
                    <button type="submit" class="btn btn-secondary btn-sm text-xs py-2 px-3 w-full justify-center">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                        <span>{{ __('Filter') }}</span>
                    </button>

                    @if(request()->hasAny(['search', 'status']))
                        <a href="{{ route('dashboard.crm.leads') }}" class="p-2 text-muted hover:text-orange-600 dark:hover:text-orange-400" title="{{ __('Reset Filters') }}">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#FFF5EB]/60 dark:bg-[#172033]/60 border-b border-[#EBDBC9] dark:border-[#273244]">
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Name') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Property / Interest') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Assigned To') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Last Contact') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EBDBC9]/60 dark:divide-[#273244]">
                        @forelse($leads as $lead)
                            <tr class="hover:bg-surface dark:hover:bg-surface/30 transition-colors">
                                <td class="p-4 font-bold text-muted dark:text-white">
                                    <a href="{{ route('dashboard.crm.leads.show', $lead['id']) }}" class="hover:text-primary-600 transition-colors block">{{ $lead['name'] }}</a>
                                    @if(!empty($lead['client_id']))
                                        <a href="{{ route('dashboard.crm.clients.show', $lead['client_id']) }}" class="inline-flex items-center gap-1 text-[11px] text-orange-600 dark:text-orange-400 hover:underline font-semibold mt-0.5" title="{{ __('View Client Profile') }}">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                            <span>{{ __('Client Profile') }}</span>
                                        </a>
                                    @endif
                                </td>
                                <td class="p-4 text-muted dark:text-dark-subtle">{{ $lead['property'] }}</td>
                                <td class="p-4">
                                    @php
                                        $badgeClass = match($lead['raw_status'] ?? 'new') {
                                            'won' => 'badge-success',
                                            'lost' => 'badge-error',
                                            'negotiation', 'offer' => 'badge-primary',
                                            'qualified', 'viewing' => 'badge-warning',
                                            default => 'badge-neutral',
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }} uppercase text-[11px]">{{ __($lead['status']) }}</span>
                                </td>
                                <td class="p-4 text-muted dark:text-dark-subtle">
                                    <div class="flex items-center gap-2">
                                        <div class="avatar avatar-xs avatar-brand">
                                            {{ substr($lead['assigned_to'], 0, 1) }}
                                        </div>
                                        <span>{{ $lead['assigned_to'] }}</span>
                                    </div>
                                </td>
                                <td class="p-4 text-muted dark:text-dark-subtle text-xs">{{ $lead['last_contact'] }}</td>
                                <td class="p-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @if(!empty($lead['client_id']))
                                            <a href="{{ route('dashboard.crm.clients.show', $lead['client_id']) }}" class="btn btn-primary btn-sm text-xs py-1 px-2.5" title="{{ __('Open Client Profile') }}">
                                                {{ __('Client') }}
                                            </a>
                                        @endif
                                        <a href="{{ route('dashboard.crm.leads.show', $lead['id']) }}" class="btn btn-secondary btn-sm text-xs py-1 px-2.5">{{ __('View Details') }}</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-12 text-center text-muted">
                                    <div class="max-w-sm mx-auto space-y-3">
                                        <div class="w-12 h-12 rounded-full bg-orange-100 dark:bg-orange-950/40 text-orange-600 dark:text-orange-400 mx-auto flex items-center justify-center">
                                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                        </div>
                                        <p class="font-medium text-slate-700 dark:text-slate-300">{{ __('No CRM leads found matching criteria.') }}</p>
                                        <button @click="showModal = true" class="btn btn-primary btn-sm text-xs">{{ __('Capture First Lead') }}</button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(method_exists($leads, 'links'))
                <div class="p-4 border-t border-[#EBDBC9] dark:border-[#273244]">
                    {{ $leads->links() }}
                </div>
            @endif
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
                        <h3 class="text-lg font-bold text-muted dark:text-white">{{ __('Capture New Lead') }}</h3>
                        <p class="text-xs text-muted">{{ __('Add a prospective buyer or tenant to the CRM pipeline.') }}</p>
                    </div>
                    <button @click="showModal = false" class="text-muted hover:text-slate-700 dark:hover:text-slate-200 text-lg">&times;</button>
                </div>
                <form method="POST" action="{{ route('dashboard.crm.leads.store') }}" class="p-6 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-muted dark:text-white mb-1">{{ __('Full Name') }} *</label>
                        <input type="text" name="name" required class="form-input w-full text-sm" placeholder="e.g. Abebe Bikila">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-muted dark:text-white mb-1">{{ __('Email Address') }}</label>
                            <input type="email" name="email" class="form-input w-full text-sm" placeholder="e.g. abebe@example.com">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-muted dark:text-white mb-1">{{ __('Phone Number') }}</label>
                            <input type="tel" name="phone" class="form-input w-full text-sm" placeholder="e.g. +251 91 123 4567">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-muted dark:text-white mb-1">{{ __('Inquiry Source') }}</label>
                            <select name="source" class="form-select w-full text-sm">
                                <option value="inquiry">{{ __('Inquiry (Portal)') }}</option>
                                <option value="web">{{ __('Website Form') }}</option>
                                <option value="walk_in">{{ __('Walk-in Client') }}</option>
                                <option value="referral">{{ __('Direct Referral') }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-muted dark:text-white mb-1">{{ __('Pipeline Stage') }}</label>
                            <select name="status" class="form-select w-full text-sm">
                                <option value="new">{{ __('New') }}</option>
                                <option value="contacted">{{ __('Contacted') }}</option>
                                <option value="qualified">{{ __('Qualified') }}</option>
                                <option value="viewing">{{ __('Viewing Scheduled') }}</option>
                                <option value="negotiation">{{ __('Negotiation') }}</option>
                                <option value="offer">{{ __('Offer Submitted') }}</option>
                            </select>
                        </div>
                    </div>

                    @if(!empty($listings) && count($listings) > 0)
                        <div>
                            <label class="block text-xs font-bold text-muted dark:text-white mb-1">{{ __('Property of Interest') }}</label>
                            <select name="listing_id" class="form-select w-full text-sm">
                                <option value="">{{ __('— General Property Inquiry —') }}</option>
                                @foreach($listings as $item)
                                    <option value="{{ $item->id }}">{{ $item->property?->title ?? 'Listing #' . substr($item->id, 0, 8) }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    @if(!empty($agents) && count($agents) > 0)
                        <div>
                            <label class="block text-xs font-bold text-muted dark:text-white mb-1">{{ __('Assign Real Estate Agent') }}</label>
                            <select name="assigned_to" class="form-select w-full text-sm">
                                @foreach($agents as $agent)
                                    <option value="{{ $agent->id }}" {{ $agent->id === auth()->id() ? 'selected' : '' }}>{{ $agent->name }} ({{ $agent->email }})</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div>
                        <label class="block text-xs font-bold text-muted dark:text-white mb-1">{{ __('Initial Notes & Requirements') }}</label>
                        <textarea name="notes" rows="3" class="form-input w-full text-sm" placeholder="Client budget, bedroom requirements, preferred sub-city, move-in target..."></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-warm dark:border-warm">
                        <button type="button" @click="showModal = false" class="btn btn-secondary text-sm">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary text-sm">{{ __('Save & Capture Lead') }}</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
