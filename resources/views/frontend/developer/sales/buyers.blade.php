<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Buyers List') }}</x-slot>

    <div class="max-w-7xl mx-auto page-enter space-y-6" x-data="{ showNewBuyer: false }">
        <x-page-header
            title="Buyers Directory"
            description="Manage registered unit buyers and their property portfolios."
            icon="user"
            badge="Buyers">
            <x-slot:actions>
                <button @click="showNewBuyer = true" class="btn btn-primary text-xs shadow-sm shadow-orange-500/20">
                    <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    {{ __('Register Buyer') }}
                </button>
            </x-slot:actions>
        </x-page-header>

        @if(session('success'))
            <div class="px-4 py-3 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 text-sm border border-emerald-200 dark:border-emerald-800 flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ session('success') }}
            </div>
        @endif

        @include('frontend.developer.sales.partials.tabs')

        <div class="card bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-3xl overflow-hidden">
            <div class="p-6 border-b border-warm dark:border-warm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <form method="GET" action="{{ route('dashboard.sales.buyers') }}" class="flex items-center gap-3 max-w-md w-full">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </div>
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="{{ __('Search buyers by name, phone, or email...') }}"
                               class="form-input search-input pl-9 pr-8 w-full text-sm">
                        @if(request('search'))
                            <a href="{{ route('dashboard.sales.buyers') }}" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </a>
                        @endif
                    </div>
                    @if(request('search'))
                        <a href="{{ route('dashboard.sales.buyers') }}" class="text-xs text-orange-600 hover:text-orange-700 dark:text-orange-400 font-medium whitespace-nowrap">{{ __('Reset') }}</a>
                    @endif
                </form>

                <button @click="showNewBuyer = true" class="btn btn-primary btn-sm text-xs self-start sm:self-auto">
                    <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    {{ __('New Buyer') }}
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface dark:bg-surface/50 border-b border-warm dark:border-warm">
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Buyer Name') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Contact') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Units Owned') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-warm dark:divide-dark-border">
                        @forelse($buyers as $buyer)
                            <tr class="hover:bg-surface dark:hover:bg-surface/30 transition-colors">
                                <td class="p-4 font-bold text-muted dark:text-white">
                                    <a href="{{ route('dashboard.sales.buyers.show', $buyer['id']) }}" class="hover:text-primary-600">{{ $buyer['name'] }}</a>
                                </td>
                                <td class="p-4">
                                    <p class="text-sm text-muted dark:text-white">{{ $buyer['email'] }}</p>
                                    <p class="text-xs text-muted">{{ $buyer['phone'] }}</p>
                                </td>
                                <td class="p-4 font-bold text-muted dark:text-white">{{ $buyer['units'] }}</td>
                                <td class="p-4">
                                    <span class="badge {{ $buyer['status'] === 'active' ? 'badge-success' : 'badge-neutral' }} uppercase text-xs">{{ $buyer['status'] }}</span>
                                </td>
                                <td class="p-4 text-right">
                                    <a href="{{ route('dashboard.sales.buyers.show', $buyer['id']) }}" class="btn btn-secondary btn-sm text-xs">{{ __('View Portfolio') }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-8 text-center text-muted">
                                    {{ __('No buyers found.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(method_exists($buyers, 'links'))
                <div class="p-4 border-t border-warm dark:border-warm">
                    {{ $buyers->links() }}
                </div>
            @endif
        </div>

        {{-- Register New Buyer Modal --}}
        <div x-show="showNewBuyer" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="absolute inset-0 bg-black/50" @click="showNewBuyer = false"></div>
            <div class="relative bg-white dark:bg-dark-card rounded-2xl shadow-2xl w-full max-w-md p-6 z-10">
                <div class="flex justify-between items-center mb-5">
                    <h3 class="text-lg font-bold text-muted dark:text-white">{{ __('Register Prospective Buyer') }}</h3>
                    <button @click="showNewBuyer = false" class="text-muted hover:text-muted/70">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form method="POST" action="{{ route('dashboard.sales.buyers.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-muted dark:text-dark-subtle mb-1">{{ __('Full Name') }} <span class="text-red-500">*</span></label>
                        <input type="text" name="name" class="input w-full" placeholder="e.g. Dawit Bekele" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-muted dark:text-dark-subtle mb-1">{{ __('Email Address') }} <span class="text-red-500">*</span></label>
                        <input type="email" name="email" class="input w-full" placeholder="e.g. dawit@example.com" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-muted dark:text-dark-subtle mb-1">{{ __('Phone Number') }} <span class="text-red-500">*</span></label>
                        <input type="text" name="phone" class="input w-full" placeholder="e.g. +251 91 122 3344" required>
                    </div>

                    <div class="flex justify-end gap-3 pt-3">
                        <button type="button" @click="showNewBuyer = false" class="btn btn-secondary">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Save Buyer') }}</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
