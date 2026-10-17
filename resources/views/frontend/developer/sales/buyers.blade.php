<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Buyers List') }}</x-slot>

    <div class="max-w-7xl mx-auto">
        <x-page-header
            title="Buyers Directory"
            description="Manage registered unit buyers and their property portfolios."
            icon="user"
            badge="Buyers">
            <x-slot:actions>
                <button class="btn btn-primary">{{ __('Add Buyer') }}</button>
            </x-slot:actions>
        </x-page-header>

        @include('frontend.developer.sales.partials.tabs')

        <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-3xl overflow-hidden shadow-sm">
            <div class="p-6 border-b border-warm dark:border-warm">
                <input type="text" placeholder="{{ __('Search buyers...') }}" class="input w-full max-w-md">
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
                        @foreach($buyers as $buyer)
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
                                    <a href="{{ route('dashboard.sales.buyers.show', $buyer['id']) }}" class="text-primary-600 hover:text-primary-700 font-medium text-sm">{{ __('View Portfolio') }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
