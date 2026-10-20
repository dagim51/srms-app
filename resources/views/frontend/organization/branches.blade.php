<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Branches') }}</x-slot>

    <div class="max-w-7xl mx-auto page-enter">
        <x-page-header
            title="Branches"
            description="Manage your organization's physical locations and branch operations."
            icon="building"
            badge="Branches" />

        @include('frontend.organization.partials.tabs')

        <div class="card overflow-hidden">
            <div class="card-header flex items-center justify-between">
                <h2 class="card-title text-xl">{{ __('Active Branches') }}</h2>
                <button class="btn btn-primary btn-sm">{{ __('Add Branch') }}</button>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-warm-50/50 dark:bg-[#1E293B]/50 border-b border-[#EBDBC9] dark:border-[#273244]">
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Branch Name') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Address') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider text-center">{{ __('Agents') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EBDBC9]/50 dark:divide-[#273244]">
                        @foreach($branches as $branch)
                            <tr class="hover:bg-surface dark:hover:bg-surface/30 transition-colors">
                                <td class="p-4 font-bold text-muted dark:text-white">{{ $branch['name'] }}</td>
                                <td class="p-4 text-muted dark:text-dark-subtle">{{ $branch['address'] }}</td>
                                <td class="p-4 text-center font-medium text-muted dark:text-white">{{ $branch['agents'] }}</td>
                                <td class="p-4">
                                    <span class="badge {{ $branch['status'] === 'active' ? 'badge-success' : 'badge-neutral' }} uppercase">
                                        {{ $branch['status'] }}
                                    </span>
                                </td>
                                <td class="p-4 text-right">
                                    <button class="text-primary-600 hover:text-primary-700 dark:text-primary-400 font-medium text-sm">{{ __('Edit') }}</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.dashboard>
