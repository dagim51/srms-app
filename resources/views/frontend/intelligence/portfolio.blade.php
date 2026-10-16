<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Portfolio View') }}</x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="mb-8">
            <h1 class="text-3xl font-extrabold font-display text-muted dark:text-white tracking-tight">{{ __('Portfolio Overview') }}</h1>
            <p class="text-muted dark:text-muted mt-2">{{ __('Track the performance and equity of your real estate assets.') }}</p>
        </div>

        @include('frontend.intelligence.partials.tabs')

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <x-card-stat 
                label="{{ __('Total Value') }}" 
                value="{{ $portfolio['total_value'] }}" 
                icon="heroicon-o-banknotes"
                iconColor="emerald"
                :badge="__('Portfolio')"
                badgeVariant="success"
            />
            <x-card-stat 
                label="{{ __('Total Equity') }}" 
                value="{{ $portfolio['total_equity'] }}" 
                icon="heroicon-o-chart-pie"
                iconColor="blue"
                :badge="__('Equity')"
                badgeVariant="info"
            />
            <x-card-stat 
                label="{{ __('Monthly Cashflow') }}" 
                value="{{ $portfolio['monthly_cashflow'] }}" 
                icon="heroicon-o-arrow-trending-up"
                iconColor="orange"
                :badge="__('Cashflow')"
                badgeVariant="brand"
            />
        </div>

        <!-- Asset List -->
        <div class="card p-6 md:p-8">
            <h3 class="card-title text-xl mb-6">{{ __('Your Assets') }}</h3>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-warm dark:border-warm text-xs font-semibold text-muted uppercase tracking-wider">
                            <th class="py-3 font-semibold">{{ __('Property Name') }}</th>
                            <th class="py-3 font-semibold">{{ __('Current Value') }}</th>
                            <th class="py-3 font-semibold">{{ __('Equity') }}</th>
                            <th class="py-3 font-semibold">{{ __('Est. ROI') }}</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-warm dark:divide-dark-border">
                        @foreach($portfolio['properties'] as $prop)
                            <tr class="hover:bg-surface dark:hover:bg-surface/50 transition-colors">
                                <td class="py-4 font-bold text-muted dark:text-white">{{ $prop['name'] }}</td>
                                <td class="py-4 font-medium text-muted dark:text-white">{{ $prop['value'] }}</td>
                                <td class="py-4 font-medium text-primary-600 dark:text-primary-400">{{ $prop['equity'] }}</td>
                                <td class="py-4 font-bold text-success-600 dark:text-success-400">{{ $prop['roi'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
