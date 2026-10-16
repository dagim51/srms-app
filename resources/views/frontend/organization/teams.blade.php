<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Teams') }}</x-slot>

    <div class="max-w-7xl mx-auto">
        
        <div class="mb-8">
            <h1 class="text-3xl font-extrabold font-display text-muted dark:text-white tracking-tight">{{ __('Teams') }}</h1>
            <p class="text-muted dark:text-muted mt-2">{{ __('Group your agents into logical units for better management.') }}</p>
        </div>

        @include('frontend.organization.partials.tabs')

        <div class="card overflow-hidden">
            <div class="card-header flex items-center justify-between">
                <h2 class="card-title text-xl">{{ __('Organization Teams') }}</h2>
                <button class="btn btn-primary btn-sm">{{ __('Create Team') }}</button>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6">
                @foreach($teams as $team)
                    <div class="card-nested p-6">
                        <h3 class="font-bold text-lg text-muted dark:text-white mb-2">{{ $team['name'] }}</h3>
                        <p class="text-sm text-muted dark:text-muted mb-4">Manager: <span class="font-medium text-muted dark:text-dark-subtle">{{ $team['manager'] }}</span></p>
                        
                        <div class="flex items-center justify-between mt-auto pt-4 border-t border-[#EBDBC9] dark:border-[#273244]">
                            <span class="text-sm font-medium bg-surface-subtle dark:bg-surface px-3 py-1 rounded-full text-muted dark:text-dark-subtle">{{ $team['members'] }} Members</span>
                            <button class="text-primary-600 hover:text-primary-700 dark:text-primary-400 text-sm font-medium">{{ __('Manage') }}</button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-layouts.dashboard>
