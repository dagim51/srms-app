<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Clients') }}</x-slot>

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
            title="Clients Directory"
            description="Manage converted clients, active property mandates, and long-term customer relationships."
            icon="users"
            badge="CRM">
            <x-slot:actions>
                <button @click="showModal = true" class="btn btn-primary text-xs flex items-center gap-1.5 shadow-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>{{ __('Add Client') }}</span>
                </button>
            </x-slot:actions>
        </x-page-header>

        @include('frontend.crm.partials.tabs')

        <div class="card bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-3xl overflow-hidden">
            <div class="p-6 border-b border-warm dark:border-warm">
                <form method="GET" action="{{ route('dashboard.crm.clients') }}" class="flex items-center gap-3 max-w-md">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </div>
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="{{ __('Search clients by name, email, or phone...') }}"
                               class="form-input search-input pl-9 pr-8 w-full text-sm">
                        @if(request('search'))
                            <a href="{{ route('dashboard.crm.clients') }}" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </a>
                        @endif
                    </div>
                    @if(request('search'))
                        <a href="{{ route('dashboard.crm.clients') }}" class="text-xs text-orange-600 hover:text-orange-700 dark:text-orange-400 font-medium whitespace-nowrap">{{ __('Reset') }}</a>
                    @endif
                </form>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface dark:bg-surface/50 border-b border-warm dark:border-warm">
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Client Name') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Contact Info') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Type') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Active Inquiries') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-warm dark:divide-dark-border">
                        @forelse($clients as $client)
                            <tr class="hover:bg-surface dark:hover:bg-surface/30 transition-colors">
                                <td class="p-4 font-bold text-muted dark:text-white">
                                    <a href="{{ route('dashboard.crm.clients.show', $client['id']) }}" class="hover:text-primary-600 transition-colors flex items-center gap-2">
                                        <div class="avatar avatar-xs avatar-brand">
                                            {{ substr($client['name'], 0, 1) }}
                                        </div>
                                        <span>{{ $client['name'] }}</span>
                                    </a>
                                </td>
                                <td class="p-4">
                                    <p class="text-sm text-muted dark:text-white font-medium">{{ $client['email'] }}</p>
                                    <p class="text-xs text-muted dark:text-muted">{{ $client['phone'] }}</p>
                                </td>
                                <td class="p-4">
                                    <span class="badge badge-neutral uppercase text-[11px]">{{ __($client['type']) }}</span>
                                </td>
                                <td class="p-4 font-medium text-muted dark:text-white">
                                    <span class="badge badge-brand text-[11px]">{{ $client['leads_count'] ?? 0 }} {{ __('Inquiries') }}</span>
                                </td>
                                <td class="p-4 text-right">
                                    <a href="{{ route('dashboard.crm.clients.show', $client['id']) }}" class="btn btn-secondary btn-sm text-xs py-1 px-3">
                                        {{ __('View Profile') }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-12 text-center text-muted">
                                    <div class="max-w-sm mx-auto space-y-3">
                                        <div class="w-12 h-12 rounded-full bg-orange-100 dark:bg-orange-950/40 text-orange-600 dark:text-orange-400 mx-auto flex items-center justify-center">
                                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                        </div>
                                        <p class="font-medium text-slate-700 dark:text-slate-300">{{ __('No clients found.') }}</p>
                                        <button @click="showModal = true" class="btn btn-primary btn-sm text-xs">{{ __('Add First Client') }}</button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(method_exists($clients, 'links'))
                <div class="p-4 border-t border-warm dark:border-warm">
                    {{ $clients->links() }}
                </div>
            @endif
        </div>

        {{-- Add Client Modal --}}
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
                 class="card max-w-md w-full bg-white dark:bg-[#1E293B] rounded-2xl shadow-xl border border-warm dark:border-warm overflow-hidden">
                <div class="p-6 border-b border-warm dark:border-warm flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-bold text-muted dark:text-white">{{ __('Register New Client') }}</h3>
                        <p class="text-xs text-muted">{{ __('Add a customer profile directly to CRM directory.') }}</p>
                    </div>
                    <button @click="showModal = false" class="text-muted hover:text-slate-700 dark:hover:text-slate-200 text-lg">&times;</button>
                </div>
                <form method="POST" action="{{ route('dashboard.crm.clients.store') }}" class="p-6 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-muted dark:text-white mb-1">{{ __('Full Name') }} *</label>
                        <input type="text" name="name" required class="form-input w-full text-sm" placeholder="e.g. Almaz Ayana">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-muted dark:text-white mb-1">{{ __('Email Address') }} *</label>
                        <input type="email" name="email" required class="form-input w-full text-sm" placeholder="e.g. almaz@example.com">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-muted dark:text-white mb-1">{{ __('Phone Number') }}</label>
                        <input type="tel" name="phone" class="form-input w-full text-sm" placeholder="e.g. +251 91 234 5678">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-muted dark:text-white mb-1">{{ __('Internal Notes') }}</label>
                        <textarea name="notes" rows="2" class="form-input w-full text-sm" placeholder="VIP investor, preferred contact time, etc."></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-warm dark:border-warm">
                        <button type="button" @click="showModal = false" class="btn btn-secondary text-sm">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary text-sm">{{ __('Register Client') }}</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
