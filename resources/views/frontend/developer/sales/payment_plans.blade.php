<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Payment Plans') }}</x-slot>

    <div class="max-w-7xl mx-auto page-enter">
        <x-page-header
            title="Payment Plans"
            description="Manage installment templates, milestone payment schedules, and structures."
            icon="banknotes"
            badge="Payment Plans">
            <x-slot:actions>
                <button class="btn btn-primary">{{ __('Create Plan') }}</button>
            </x-slot:actions>
        </x-page-header>

        @include('frontend.developer.sales.partials.tabs')

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($plans as $plan)
                <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-3xl p-6 shadow-sm">
                    <h3 class="text-xl font-bold text-muted dark:text-white mb-4">{{ $plan['name'] }}</h3>
                    
                    <ul class="space-y-3 mb-6 text-sm">
                        <li class="flex justify-between">
                            <span class="text-muted">{{ __('Down Payment') }}</span>
                            <span class="font-bold text-muted dark:text-white">{{ $plan['down_payment'] }}</span>
                        </li>
                        <li class="flex justify-between">
                            <span class="text-muted">{{ __('Installment Milestones') }}</span>
                            <span class="font-bold text-muted dark:text-white">{{ $plan['milestones'] }}</span>
                        </li>
                        <li class="flex justify-between">
                            <span class="text-muted">{{ __('Active Contracts') }}</span>
                            <span class="font-bold text-muted dark:text-white">{{ $plan['active_contracts'] }}</span>
                        </li>
                    </ul>
                    
                    <div class="flex gap-2">
                        <button class="btn btn-secondary flex-1">{{ __('Edit Template') }}</button>
                    </div>
                </div>
            @endforeach
        </div>

    </div>
</x-layouts.dashboard>
