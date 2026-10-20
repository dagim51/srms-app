<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Tenants') }}</x-slot>

    <div class="max-w-7xl mx-auto page-enter space-y-6">
        <x-page-header
            title="Tenants Directory"
            description="Manage active and past tenants, review KYC credentials, emergency contacts, and active lease assignments."
            icon="users"
            badge="Tenants">
            <x-slot:actions>
                <button class="btn btn-primary">{{ __('Add Tenant') }}</button>
            </x-slot:actions>
        </x-page-header>

        @include('frontend.rental.partials.tabs')

        <div class="card overflow-hidden">
            <div class="p-6 border-b border-[#EBDBC9] dark:border-[#273244]">
                <form method="GET" action="{{ route('dashboard.rental.tenants') }}" class="flex items-center gap-3 max-w-md">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </div>
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="{{ __('Search tenants by name, unit, or phone...') }}"
                               class="form-input search-input pl-9 pr-8 w-full text-sm">
                        @if(request('search'))
                            <a href="{{ route('dashboard.rental.tenants') }}" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </a>
                        @endif
                    </div>
                    @if(request('search'))
                        <a href="{{ route('dashboard.rental.tenants') }}" class="text-xs text-orange-600 hover:text-orange-700 dark:text-orange-400 font-medium whitespace-nowrap">{{ __('Reset') }}</a>
                    @endif
                </form>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-warm-50/50 dark:bg-[#1E293B]/50 border-b border-[#EBDBC9] dark:border-[#273244]">
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Tenant Name') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Unit') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Contact') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Balance') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EBDBC9]/50 dark:divide-[#273244]">
                        @foreach($tenants as $tenant)
                            <tr class="hover:bg-surface dark:hover:bg-surface/30 transition-colors">
                                <td class="p-4 font-bold text-muted dark:text-white">
                                    <a href="{{ route('dashboard.rental.tenants.show', $tenant['id']) }}" class="hover:text-primary-600">{{ $tenant['name'] }}</a>
                                </td>
                                <td class="p-4 text-muted dark:text-dark-subtle">{{ $tenant['unit'] }}</td>
                                <td class="p-4 text-muted dark:text-dark-subtle">{{ $tenant['phone'] }}</td>
                                <td class="p-4">
                                    <span class="font-bold {{ $tenant['balance'] !== '0 ETB' ? 'text-error-600' : 'text-success-600' }}">{{ $tenant['balance'] }}</span>
                                </td>
                                <td class="p-4">
                                    <span class="badge {{ $tenant['status'] === 'active' ? 'badge-success' : 'badge-warning' }} uppercase text-xs">{{ __($tenant['status']) }}</span>
                                </td>
                                <td class="p-4 text-right">
                                    <a href="{{ route('dashboard.rental.tenants.show', $tenant['id']) }}" class="text-primary-600 hover:text-primary-700 font-medium text-sm">{{ __('View Profile') }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
