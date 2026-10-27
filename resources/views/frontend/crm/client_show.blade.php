<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Client Profile') }}: {{ $client->name }}</x-slot>

    <div x-data="{ showLeadModal: false }" class="max-w-7xl mx-auto page-enter space-y-6">
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
            :title="$client->name"
            :description="$client->email . ' • ' . ($client->phone ?? __('No phone registered')) . ' • ' . __('Client since') . ' ' . $client->created_at->format('M Y')"
            icon="users"
            badge="Client Profile">
            <x-slot:actions>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('dashboard.crm.clients') }}" class="btn btn-secondary text-xs flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                        {{ __('Back to Clients') }}
                    </a>
                    <button @click="showLeadModal = true" class="btn btn-primary text-xs flex items-center gap-1.5 shadow-sm">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        {{ __('Add Deal / Lead') }}
                    </button>
                </div>
            </x-slot:actions>
        </x-page-header>

        {{-- Stats Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6">
            <x-card-stat
                :label="__('Total Inquiries')"
                :value="number_format($stats['total_leads'])"
                iconColor="orange"
                :badge="__('Lifetime')"
                badgeVariant="brand">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                </x-slot:icon>
            </x-card-stat>

            <x-card-stat
                :label="__('Active Pipeline Deals')"
                :value="number_format($stats['active_inquiries'])"
                iconColor="amber"
                :badge="__('In Progress')"
                badgeVariant="warning">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                </x-slot:icon>
            </x-card-stat>

            <x-card-stat
                :label="__('Closed Deals')"
                :value="number_format($stats['closed_deals'])"
                iconColor="emerald"
                :badge="__('Won')"
                badgeVariant="success">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </x-slot:icon>
            </x-card-stat>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-8">
                {{-- Linked Inquiries & Pipeline Deals --}}
                <div class="card p-6 bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-3xl overflow-hidden">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-lg font-bold text-muted dark:text-white">{{ __('Associated Inquiries & Deals') }}</h2>
                            <p class="text-xs text-muted">{{ __('Property requests and negotiation files linked to this client') }}</p>
                        </div>
                        <button @click="showLeadModal = true" class="btn btn-secondary btn-sm text-xs">{{ __('+ New Inquiry') }}</button>
                    </div>

                    <div class="overflow-x-auto -mx-6">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-surface dark:bg-surface/50 border-b border-warm dark:border-warm">
                                    <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Inquiry / Name') }}</th>
                                    <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Property of Interest') }}</th>
                                    <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Stage') }}</th>
                                    <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Assigned Agent') }}</th>
                                    <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider text-right">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-warm dark:divide-dark-border text-sm">
                                @forelse($leads as $lead)
                                    <tr class="hover:bg-surface dark:hover:bg-surface/30 transition-colors">
                                        <td class="p-4 font-bold text-muted dark:text-white">
                                            <a href="{{ route('dashboard.crm.leads.show', $lead->id) }}" class="hover:text-primary-600 transition-colors">
                                                {{ $lead->name }}
                                            </a>
                                        </td>
                                        <td class="p-4 text-muted dark:text-dark-subtle">
                                            {{ $lead->listing?->property?->title ?? 'General Inquiry' }}
                                        </td>
                                        <td class="p-4">
                                            @php
                                                $badgeClass = match(strtolower($lead->status)) {
                                                    'won' => 'badge-success',
                                                    'lost' => 'badge-error',
                                                    'negotiation', 'offer' => 'badge-primary',
                                                    'qualified', 'viewing' => 'badge-warning',
                                                    default => 'badge-neutral',
                                                };
                                            @endphp
                                            <span class="badge {{ $badgeClass }} uppercase text-[10px]">
                                                {{ ucfirst($lead->status) }}
                                            </span>
                                        </td>
                                        <td class="p-4 text-muted dark:text-dark-subtle text-xs">
                                            {{ $lead->assignee?->name ?? 'Unassigned' }}
                                        </td>
                                        <td class="p-4 text-right">
                                            <a href="{{ route('dashboard.crm.leads.show', $lead->id) }}" class="btn btn-secondary btn-sm text-xs py-1 px-2.5">
                                                {{ __('View Dossier') }}
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="p-8 text-center text-muted">
                                            {{ __('No property inquiries or leads recorded for this client yet.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Client Activity Timeline --}}
                <div class="card p-6 bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-3xl">
                    <h2 class="card-title text-lg mb-6">{{ __('Interaction & Activity History') }}</h2>
                    <div class="space-y-4">
                        @forelse($activities as $act)
                            <div class="flex items-start gap-3 p-3.5 rounded-xl border border-warm dark:border-warm bg-surface/40 dark:bg-surface/20">
                                <div class="w-8 h-8 rounded-full bg-orange-100 dark:bg-orange-950/40 text-orange-600 dark:text-orange-400 flex items-center justify-center shrink-0 mt-0.5">
                                    @if($act->type === 'call')
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                                    @elseif($act->type === 'email')
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                    @elseif($act->type === 'viewing')
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    @else
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="font-bold text-xs text-muted dark:text-white truncate">{{ $act->description ?? 'Activity Entry' }}</p>
                                        <span class="text-[10px] text-muted whitespace-nowrap">{{ $act->created_at->diffForHumans() }}</span>
                                    </div>
                                    <div class="flex items-center gap-3 mt-1 text-[11px] text-muted">
                                        <span>{{ __('By:') }} {{ $act->user?->name ?? 'Staff' }}</span>
                                        @if($act->lead)
                                            <span>• {{ __('Lead:') }} <a href="{{ route('dashboard.crm.leads.show', $act->lead_id) }}" class="hover:underline text-primary-600">{{ $act->lead->name }}</a></span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-center py-6 text-muted">{{ __('No activity logs found for this client.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Client Dossier Sidebar --}}
            <div class="space-y-6">
                <div class="card p-6 bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-3xl">
                    <h3 class="card-title text-base mb-4">{{ __('Customer Profile Details') }}</h3>
                    <ul class="space-y-4 text-sm">
                        <li>
                            <p class="card-kv-label">{{ __('Full Name') }}</p>
                            <p class="card-kv-value font-bold text-muted dark:text-white">{{ $client->name }}</p>
                        </li>
                        <li>
                            <p class="card-kv-label">{{ __('Email Address') }}</p>
                            <p class="card-kv-value font-medium">{{ $client->email }}</p>
                        </li>
                        <li>
                            <p class="card-kv-label">{{ __('Phone Number') }}</p>
                            <p class="card-kv-value font-medium">{{ $client->phone ?? '—' }}</p>
                        </li>
                        <li>
                            <p class="card-kv-label">{{ __('Account Type') }}</p>
                            <span class="badge badge-neutral uppercase text-xs mt-1">{{ __('Registered Client') }}</span>
                        </li>
                        <li>
                            <p class="card-kv-label">{{ __('Customer ID') }}</p>
                            <p class="card-kv-value font-mono text-xs text-muted">{{ $client->id }}</p>
                        </li>
                        <li>
                            <p class="card-kv-label">{{ __('Registered Since') }}</p>
                            <p class="card-kv-value text-xs text-muted">{{ $client->created_at->format('F d, Y') }}</p>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Add Inquiry Modal for this Client --}}
        <div x-show="showLeadModal"
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
            <div @click.away="showLeadModal = false"
                 class="card max-w-lg w-full bg-white dark:bg-[#1E293B] rounded-2xl shadow-xl border border-warm dark:border-warm overflow-hidden">
                <div class="p-6 border-b border-warm dark:border-warm flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-bold text-muted dark:text-white">{{ __('Capture New Inquiry for') }} {{ $client->name }}</h3>
                        <p class="text-xs text-muted">{{ __('Add a property requirement or negotiation lead for this client.') }}</p>
                    </div>
                    <button @click="showLeadModal = false" class="text-muted hover:text-slate-700 dark:hover:text-slate-200 text-lg">&times;</button>
                </div>
                <form method="POST" action="{{ route('dashboard.crm.leads.store') }}" class="p-6 space-y-4">
                    @csrf
                    <input type="hidden" name="name" value="{{ $client->name }}">
                    <input type="hidden" name="email" value="{{ $client->email }}">
                    <input type="hidden" name="phone" value="{{ $client->phone }}">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-muted dark:text-white mb-1">{{ __('Pipeline Stage') }}</label>
                            <select name="status" class="form-select w-full text-sm">
                                <option value="new">{{ __('New') }}</option>
                                <option value="contacted">{{ __('Contacted') }}</option>
                                <option value="qualified">{{ __('Qualified') }}</option>
                                <option value="viewing">{{ __('Viewing') }}</option>
                                <option value="negotiation">{{ __('Negotiation') }}</option>
                                <option value="offer">{{ __('Offer') }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-muted dark:text-white mb-1">{{ __('Inquiry Source') }}</label>
                            <select name="source" class="form-select w-full text-sm">
                                <option value="inquiry">{{ __('Direct Client Inquiry') }}</option>
                                <option value="referral">{{ __('Referral') }}</option>
                                <option value="walk_in">{{ __('Walk-in') }}</option>
                                <option value="web">{{ __('Portal') }}</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-muted dark:text-white mb-1">{{ __('Requirements & Property Notes') }} *</label>
                        <textarea name="notes" required rows="3" class="form-input w-full text-sm" placeholder="Specify budget, target location, property type..."></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-warm dark:border-warm">
                        <button type="button" @click="showLeadModal = false" class="btn btn-secondary text-sm">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary text-sm">{{ __('Save Deal / Inquiry') }}</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
