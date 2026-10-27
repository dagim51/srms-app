<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Lead') }}: {{ $lead['name'] }}</x-slot>

    <div x-data="{ showActivityModal: false }" class="max-w-7xl mx-auto page-enter space-y-6">
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
            :title="$lead['name']"
            :description="__('Status:') . ' ' . __($lead['status']) . ' • ' . __('Source:') . ' ' . $lead['source'] . ' • ' . __('Created:') . ' ' . $lead['created']"
            icon="chart"
            :badge="$lead['status']">
            <x-slot:actions>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('dashboard.crm.leads') }}" class="btn btn-secondary text-xs flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                        {{ __('Back to Leads') }}
                    </a>
                    <button @click="showActivityModal = true" class="btn btn-secondary text-xs flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        {{ __('Log Activity') }}
                    </button>
                    @if(!empty($lead['client_id']))
                        <a href="{{ route('dashboard.crm.clients.show', $lead['client_id']) }}" class="btn btn-primary text-xs flex items-center gap-1.5 shadow-sm">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            {{ __('Client Profile') }}
                        </a>
                    @endif
                    @if($lead['raw_status'] !== 'won')
                        <form method="POST" action="{{ route('dashboard.crm.leads.convert', $lead['id']) }}" class="inline">
                            @csrf
                            <button type="submit" class="btn btn-primary text-xs flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ __('Convert to Client') }}
                            </button>
                        </form>
                    @else
                        <span class="badge badge-success text-xs py-1.5 px-3 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            {{ __('Converted Client') }}
                        </span>
                    @endif
                </div>
            </x-slot:actions>
        </x-page-header>

        {{-- Interactive Stage Progression Bar --}}
        <div class="card bg-white dark:bg-[#1E293B] border border-warm dark:border-warm p-4 rounded-2xl">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-muted uppercase tracking-wider">{{ __('Pipeline Stage Progression') }}</span>
                <span class="text-xs font-semibold text-primary-600 dark:text-primary-400">{{ __('Current Stage:') }} {{ $lead['status'] }}</span>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-8 gap-2">
                @php
                    $stages = ['new' => 'New', 'contacted' => 'Contacted', 'qualified' => 'Qualified', 'viewing' => 'Viewing', 'negotiation' => 'Negotiation', 'offer' => 'Offer', 'won' => 'Won', 'lost' => 'Lost'];
                @endphp
                @foreach($stages as $key => $label)
                    <form method="POST" action="{{ route('dashboard.crm.leads.stage', $lead['id']) }}">
                        @csrf
                        <input type="hidden" name="status" value="{{ $key }}">
                        <button type="submit" class="w-full py-2 px-2 text-xs font-bold rounded-xl transition-all text-center {{ $lead['raw_status'] === $key ? 'bg-primary-600 text-white shadow-sm ring-2 ring-primary-500 ring-offset-1 dark:ring-offset-gray-900' : 'bg-surface dark:bg-[#111827] text-muted hover:bg-orange-50 hover:text-primary-600 dark:hover:bg-slate-800' }}">
                            {{ __($label) }}
                        </button>
                    </form>
                @endforeach
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-8">
                <div class="card p-6 sm:p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="card-title text-xl">{{ __('Activity Timeline & Interaction History') }}</h2>
                        <button @click="showActivityModal = true" class="btn btn-secondary btn-sm text-xs">{{ __('+ Add Entry') }}</button>
                    </div>
                    
                    <div class="space-y-6 relative before:absolute before:inset-0 before:ml-5 before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-gradient-to-b before:from-transparent before:via-[#EBDBC9] dark:before:via-[#273244] before:to-transparent">
                        @forelse($timeline as $item)
                            <div class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
                                <!-- Icon -->
                                <div class="flex items-center justify-center w-10 h-10 rounded-full border border-white dark:border-[#111827] bg-orange-100 dark:bg-orange-950/40 text-orange-600 dark:text-orange-400 shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 shadow-sm z-10">
                                    @if($item['type'] === 'call')
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                                    @elseif($item['type'] === 'email')
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                    @elseif($item['type'] === 'viewing')
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    @elseif($item['type'] === 'conversion')
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    @else
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                                    @endif
                                </div>
                                
                                <!-- Card -->
                                <div class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] card-nested p-4 bg-surface dark:bg-[#111827] rounded-xl border border-warm dark:border-warm">
                                    <div class="flex items-center justify-between mb-1">
                                        <h3 class="font-bold text-muted dark:text-white text-sm">{{ $item['title'] }}</h3>
                                    </div>
                                    <div class="text-xs text-muted dark:text-muted flex justify-between mt-2 pt-2 border-t border-warm/40 dark:border-warm/40">
                                        <span>{{ $item['user'] }}</span>
                                        <span>{{ $item['date'] }}</span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8 text-muted">
                                <p class="text-sm">{{ __('No timeline activities recorded yet.') }}</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="card p-6">
                    <h3 class="card-title text-base mb-4">{{ __('Prospect Dossier') }}</h3>
                    <ul class="space-y-4 text-sm">
                        <li>
                            <p class="card-kv-label">{{ __('Contact Email') }}</p>
                            <p class="card-kv-value font-medium">{{ $lead['email'] }}</p>
                        </li>
                        @if(!empty($lead['client_id']))
                            <li class="p-3 bg-orange-50/50 dark:bg-orange-950/20 border border-orange-200/60 dark:border-orange-800/60 rounded-xl">
                                <p class="card-kv-label text-orange-800 dark:text-orange-300 font-bold">{{ __('Registered Customer Profile') }}</p>
                                <a href="{{ route('dashboard.crm.clients.show', $lead['client_id']) }}" class="text-xs text-primary-600 dark:text-primary-400 font-bold hover:underline flex items-center gap-1 mt-1">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    <span>{{ __('Open Client Profile') }} &rarr;</span>
                                </a>
                            </li>
                        @endif
                        <li>
                            <p class="card-kv-label">{{ __('Phone Number') }}</p>
                            <p class="card-kv-value font-medium">{{ $lead['phone'] }}</p>
                        </li>
                        <li>
                            <p class="card-kv-label">{{ __('Property of Interest') }}</p>
                            <p class="card-kv-value">{{ $lead['property'] }}</p>
                        </li>
                        <li>
                            <p class="card-kv-label">{{ __('Inquiry Channel') }}</p>
                            <p class="card-kv-value">{{ $lead['source'] }}</p>
                        </li>
                        <li>
                            <p class="card-kv-label">{{ __('Assigned Real Estate Agent') }}</p>
                            <p class="card-kv-value text-orange-600 dark:text-orange-400 font-semibold">{{ $lead['assigned_to'] }}</p>
                        </li>
                        <li>
                            <p class="card-kv-label">{{ __('Notes & Requirements') }}</p>
                            <p class="text-xs text-muted dark:text-muted mt-1 bg-surface dark:bg-[#111827] p-3 rounded-lg border border-warm dark:border-warm">
                                {{ $lead['notes'] }}
                            </p>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Log Activity Modal --}}
        <div x-show="showActivityModal"
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
            <div @click.away="showActivityModal = false"
                 class="card max-w-md w-full bg-white dark:bg-[#1E293B] rounded-2xl shadow-xl border border-warm dark:border-warm overflow-hidden">
                <div class="p-6 border-b border-warm dark:border-warm flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-bold text-muted dark:text-white">{{ __('Log CRM Activity') }}</h3>
                        <p class="text-xs text-muted">{{ __('Record an interaction with this lead.') }}</p>
                    </div>
                    <button @click="showActivityModal = false" class="text-muted hover:text-slate-700 dark:hover:text-slate-200 text-lg">&times;</button>
                </div>
                <form method="POST" action="{{ route('dashboard.crm.leads.activity', $lead['id']) }}" class="p-6 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-muted dark:text-white mb-1">{{ __('Activity Type') }}</label>
                        <select name="type" class="form-select w-full text-sm">
                            <option value="call">{{ __('Phone Call') }}</option>
                            <option value="email">{{ __('Email Correspondence') }}</option>
                            <option value="viewing">{{ __('Site / Property Viewing') }}</option>
                            <option value="meeting">{{ __('In-Person Meeting') }}</option>
                            <option value="note">{{ __('General Note / Follow-up') }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-muted dark:text-white mb-1">{{ __('Activity Summary / Minutes') }} *</label>
                        <textarea name="description" required rows="3" class="form-input w-full text-sm" placeholder="Discussed price negotiation, client requested floor plans..."></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-muted dark:text-white mb-1">{{ __('Scheduled / Follow-up Date (Optional)') }}</label>
                        <input type="date" name="scheduled_at" class="form-input w-full text-sm">
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-warm dark:border-warm">
                        <button type="button" @click="showActivityModal = false" class="btn btn-secondary text-sm">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary text-sm">{{ __('Log Activity') }}</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
