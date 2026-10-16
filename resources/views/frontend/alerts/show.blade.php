<x-layouts.dashboard>
    <x-slot name="page-title">Alert: {{ $alert['name'] }}</x-slot>

    <div class="max-w-4xl mx-auto">
        <div class="mb-8">
            <a href="{{ route('dashboard.alerts.index') }}" class="text-sm font-medium text-muted hover:text-muted dark:hover:text-white flex items-center gap-2 mb-4 transition-colors w-max">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Back to Alerts
            </a>
            
            <div class="flex flex-col sm:flex-row justify-between sm:items-end gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="badge badge-neutral uppercase text-xs">{{ $alert['type'] }}</span>
                        @if($alert['status'] === 'active')
                            <span class="badge badge-success uppercase text-xs">{{ __('Active') }}</span>
                        @else
                            <span class="badge badge-error uppercase text-xs">{{ __('Paused') }}</span>
                        @endif
                    </div>
                    <h1 class="text-3xl font-extrabold font-display text-muted dark:text-white tracking-tight">{{ $alert['name'] }}</h1>
                </div>
                <div class="flex gap-2">
                    @if($alert['status'] === 'active')
                        <button class="btn btn-secondary border-error-200 text-error-600 hover:bg-error-50 dark:hover:bg-error-900/20">{{ __('Pause Alert') }}</button>
                    @else
                        <button class="btn btn-secondary border-success-200 text-success-600 hover:bg-success-50 dark:hover:bg-success-900/20">{{ __('Resume Alert') }}</button>
                    @endif
                    <button class="btn btn-primary">{{ __('Save Changes') }}</button>
                </div>
            </div>
        </div>

        <div class="space-y-8">
            <!-- Search Criteria -->
            <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-3xl p-8 shadow-sm">
                <h2 class="text-xl font-bold text-muted dark:text-white mb-6">{{ __('Search Criteria') }}</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-muted dark:text-dark-subtle mb-2">{{ __('Location') }}</label>
                        <input type="text" value="{{ $alert['location'] }}" class="input w-full">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-muted dark:text-dark-subtle mb-2">{{ __('Property Type') }}</label>
                        <select class="input w-full">
                            <option {{ $alert['property_type'] === 'Apartment' ? 'selected' : '' }}>{{ __('Apartment') }}</option>
                            <option {{ $alert['property_type'] === 'Villa' ? 'selected' : '' }}>{{ __('Villa') }}</option>
                            <option {{ $alert['property_type'] === 'Office' ? 'selected' : '' }}>{{ __('Office') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-muted dark:text-dark-subtle mb-2">{{ __('Minimum Beds') }}</label>
                        <input type="number" value="{{ $alert['min_beds'] }}" class="input w-full">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-muted dark:text-dark-subtle mb-2">Maximum Price (ETB)</label>
                        <input type="number" value="{{ $alert['max_price'] }}" class="input w-full">
                    </div>
                </div>
            </div>

            <!-- Notification Preferences -->
            <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-3xl p-8 shadow-sm">
                <h2 class="text-xl font-bold text-muted dark:text-white mb-6">{{ __('Notification Preferences') }}</h2>
                
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-bold text-muted dark:text-dark-subtle mb-2">{{ __('Frequency') }}</label>
                        <select class="input w-full max-w-sm">
                            <option {{ $alert['frequency'] === 'Instant' ? 'selected' : '' }}>Instant (As soon as it's listed)</option>
                            <option {{ $alert['frequency'] === 'Daily' ? 'selected' : '' }}>{{ __('Daily Digest') }}</option>
                            <option {{ $alert['frequency'] === 'Weekly' ? 'selected' : '' }}>{{ __('Weekly Summary') }}</option>
                        </select>
                    </div>
                    
                    <div class="space-y-4 pt-4 border-t border-warm dark:border-warm">
                        <label class="flex items-center gap-3">
                            <input type="checkbox" class="w-4 h-4 text-primary-600 rounded border-warm" {{ $alert['email_notify'] ? 'checked' : '' }}>
                            <span class="text-sm font-medium text-muted dark:text-white">{{ __('Email Notifications') }}</span>
                        </label>
                        <label class="flex items-center gap-3">
                            <input type="checkbox" class="w-4 h-4 text-primary-600 rounded border-warm" {{ $alert['push_notify'] ? 'checked' : '' }}>
                            <span class="text-sm font-medium text-muted dark:text-white">Push Notifications (Mobile App)</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="bg-white dark:bg-[#1E293B] border border-error-200 dark:border-error-900/30 rounded-3xl p-8 shadow-sm">
                <h2 class="text-xl font-bold text-error-600 mb-4 flex items-center gap-2">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    Delete Alert
                </h2>
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <p class="text-sm text-muted dark:text-muted">{{ __('Permanently delete this saved search. You will no longer receive notifications.') }}</p>
                    <button class="btn btn-secondary text-error-600 border-error-200 hover:bg-error-50 dark:hover:bg-error-900/20 shrink-0" onclick="confirm('Delete this alert?')">{{ __('Delete Alert') }}</button>
                </div>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
