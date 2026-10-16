<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Tenants') }}</x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="mb-8 flex justify-between items-end">
            <div>
                <h1 class="text-3xl font-extrabold font-display text-muted dark:text-white tracking-tight">{{ __('Tenants') }}</h1>
                <p class="text-muted dark:text-muted mt-2">{{ __('Manage active and past tenants.') }}</p>
            </div>
            <button class="btn btn-primary">{{ __('Add Tenant') }}</button>
        </div>

        @include('frontend.rental.partials.tabs')

        <div class="card overflow-hidden">
            <div class="p-6 border-b border-[#EBDBC9] dark:border-[#273244] flex gap-4">
                <input type="text" placeholder="{{ __('Search tenants...') }}" class="input w-full max-w-md">
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
