<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Staff & Vendors') }}</x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="mb-8 flex justify-between items-end">
            <div>
                <h1 class="text-3xl font-extrabold font-display text-muted dark:text-white tracking-tight">{{ __('Staff & Vendors') }}</h1>
                <p class="text-muted dark:text-muted mt-2">{{ __('Manage maintenance staff, cleaners, and third-party vendors.') }}</p>
            </div>
            <button class="btn btn-primary">{{ __('Add Personnel') }}</button>
        </div>

        @include('frontend.operations.partials.tabs')

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($staff as $person)
                <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-3xl p-6 shadow-sm flex flex-col">
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 flex items-center justify-center font-bold text-lg relative">
                                {{ substr($person['name'], 0, 1) }}
                                <span class="absolute bottom-0 right-0 w-3 h-3 rounded-full border-2 border-white dark:border-[#1E293B] {{ $person['status'] === 'active' ? 'bg-success-500' : 'bg-surface-subtle' }}"></span>
                            </div>
                            <div>
                                <h3 class="font-bold text-muted dark:text-white">{{ $person['name'] }}</h3>
                                <p class="text-xs text-muted">{{ $person['role'] }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4 mt-auto pt-4 border-t border-warm dark:border-warm">
                        <div>
                            <p class="text-xs font-bold text-muted uppercase">{{ __('Active Tasks') }}</p>
                            <p class="font-black text-xl text-primary-600 mt-1">{{ $person['active_tasks'] }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-muted uppercase">{{ __('Completed Today') }}</p>
                            <p class="font-black text-xl text-success-600 mt-1">{{ $person['completed_today'] }}</p>
                        </div>
                    </div>
                    
                    <div class="mt-4 pt-4 border-t border-warm dark:border-warm">
                        <button class="btn btn-secondary w-full justify-center">{{ __('Assign Task') }}</button>
                    </div>
                </div>
            @endforeach
        </div>

    </div>
</x-layouts.dashboard>
