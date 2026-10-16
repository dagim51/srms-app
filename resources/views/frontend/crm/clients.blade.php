<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Clients') }}</x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="mb-8 flex justify-between items-end">
            <div>
                <h1 class="text-3xl font-extrabold font-display text-muted dark:text-white tracking-tight">{{ __('Clients') }}</h1>
                <p class="text-muted dark:text-muted mt-2">{{ __('Manage your converted clients and their properties.') }}</p>
            </div>
            <button class="btn btn-primary">{{ __('Add Client') }}</button>
        </div>

        @include('frontend.crm.partials.tabs')

        <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-3xl overflow-hidden shadow-sm">
            <div class="p-6 border-b border-warm dark:border-warm">
                <input type="text" placeholder="{{ __('Search clients...') }}" class="input w-full max-w-md">
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface dark:bg-surface/50 border-b border-warm dark:border-warm">
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Client Name') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Contact Info') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Type') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Properties') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-warm dark:divide-dark-border">
                        @foreach($clients as $client)
                            <tr class="hover:bg-surface dark:hover:bg-surface/30 transition-colors">
                                <td class="p-4 font-bold text-muted dark:text-white">{{ $client['name'] }}</td>
                                <td class="p-4">
                                    <p class="text-sm text-muted dark:text-white">{{ $client['email'] }}</p>
                                    <p class="text-xs text-muted dark:text-muted">{{ $client['phone'] }}</p>
                                </td>
                                <td class="p-4">
                                    <span class="badge badge-neutral uppercase">{{ __($client['type']) }}</span>
                                </td>
                                <td class="p-4 font-medium text-muted dark:text-white">{{ $client['properties'] }}</td>
                                <td class="p-4 text-right">
                                    <button class="text-primary-600 hover:text-primary-700 font-medium text-sm">{{ __('View Profile') }}</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
