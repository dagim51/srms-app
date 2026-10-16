<x-layouts.dashboard>
    <x-slot name="page-title">User: {{ $user['id'] }}</x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="mb-8">
            <a href="{{ route('dashboard.admin.users') }}" class="text-sm font-medium text-muted hover:text-muted dark:hover:text-white flex items-center gap-2 mb-4 transition-colors w-max">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Back to Users
            </a>
            
            <div class="flex flex-col sm:flex-row justify-between sm:items-end gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-full bg-surface-subtle dark:bg-surface flex items-center justify-center font-bold text-muted dark:text-dark-subtle text-2xl shrink-0">
                        {{ substr($user['name'], 0, 1) }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            @if($user['status'] === 'active')
                                <span class="badge badge-success uppercase text-xs">{{ __('Active') }}</span>
                            @else
                                <span class="badge badge-error uppercase text-xs">{{ $user['status'] }}</span>
                            @endif
                            <span class="badge badge-neutral uppercase text-xs">{{ $user['role'] }}</span>
                        </div>
                        <h1 class="text-3xl font-extrabold font-display text-muted dark:text-white tracking-tight">{{ $user['name'] }}</h1>
                        <p class="text-sm text-muted mt-1">{{ $user['email'] }} &bull; {{ $user['id'] }}</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button class="btn btn-secondary">{{ __('Reset Password') }}</button>
                    @if($user['status'] === 'active')
                        <button class="btn btn-secondary text-error-600 border-error-200 hover:bg-error-50 dark:hover:bg-error-900/20">{{ __('Suspend User') }}</button>
                    @else
                        <button class="btn btn-secondary text-success-600 border-success-200 hover:bg-success-50 dark:hover:bg-success-900/20">{{ __('Reactivate') }}</button>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-8">
                <div class="card p-6 md:p-8">
                    <h2 class="card-title text-xl mb-6">{{ __('User Details') }}</h2>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-6 pt-2">
                        <div>
                            <p class="card-kv-label">{{ __('Email') }}</p>
                            <p class="card-kv-value mt-1">{{ $user['email'] }}</p>
                        </div>
                        <div>
                            <p class="card-kv-label">{{ __('Phone') }}</p>
                            <p class="card-kv-value mt-1">{{ $user['phone'] }}</p>
                        </div>
                        <div>
                            <p class="card-kv-label">{{ __('Joined') }}</p>
                            <p class="card-kv-value mt-1">{{ $user['joined'] }}</p>
                        </div>
                        <div>
                            <p class="card-kv-label">{{ __('KYC Status') }}</p>
                            <p class="font-medium text-success-600 dark:text-success-400 mt-1">{{ ucfirst($user['kyc_status']) }}</p>
                        </div>
                        <div class="col-span-2">
                            <p class="card-kv-label">{{ __('Last Login') }}</p>
                            <p class="font-mono text-sm text-muted dark:text-dark-subtle mt-1">{{ $user['last_login'] }}</p>
                        </div>
                    </div>
                </div>

                <!-- Danger Zone -->
                <div class="card p-6 md:p-8 border-error-200 dark:border-error-900/40 bg-error-50/10 dark:bg-error-950/20">
                    <h2 class="text-xl font-bold text-error-600 dark:text-error-400 mb-6 flex items-center gap-2">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                        Danger Zone
                    </h2>
                    <p class="text-sm text-muted dark:text-muted mb-6">{{ __('These actions are permanent and require deliberate confirmation.') }}</p>
                    <div class="card-nested p-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-error-200 dark:border-error-900/50">
                        <div>
                            <p class="font-bold text-muted dark:text-white">{{ __('Delete User Account') }}</p>
                            <p class="text-xs text-muted dark:text-muted mt-0.5">{{ __('Permanently removes user data, listings, and history.') }}</p>
                        </div>
                        <button class="btn btn-secondary text-error-600 border-error-200 hover:bg-error-50 dark:hover:bg-error-900/20 shrink-0" onclick="confirm('Are you sure you want to permanently delete this user?')">{{ __('Delete Account') }}</button>
                    </div>
                </div>
            </div>

            <div class="space-y-8">
                <div class="card p-6">
                    <h3 class="card-title text-lg mb-4">{{ __('Roles & Permissions') }}</h3>
                    <div class="space-y-4">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" class="w-4 h-4 text-primary-600 rounded border-warm dark:border-warm dark:bg-surface" {{ $user['role'] === 'Tenant' ? 'checked' : '' }}>
                            <span class="text-sm font-medium text-muted dark:text-white">{{ __('Tenant Access') }}</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" class="w-4 h-4 text-primary-600 rounded border-warm dark:border-warm dark:bg-surface" {{ $user['role'] === 'Property Manager' ? 'checked' : '' }}>
                            <span class="text-sm font-medium text-muted dark:text-white">{{ __('Property Manager') }}</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" class="w-4 h-4 text-primary-600 rounded border-warm dark:border-warm dark:bg-surface" {{ $user['role'] === 'Admin' ? 'checked' : '' }}>
                            <span class="text-sm font-medium text-muted dark:text-white">{{ __('System Administrator') }}</span>
                        </label>
                    </div>
                    <button class="btn btn-primary w-full mt-6">{{ __('Update Permissions') }}</button>
                </div>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
