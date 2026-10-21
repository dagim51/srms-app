<x-layouts.dashboard>
    <x-slot name="page-title">User: {{ is_array($user) ? $user['id'] : $user->id }}</x-slot>

    <div class="max-w-7xl mx-auto page-enter space-y-6">
        <x-page-header
            :title="is_array($user) ? $user['name'] : $user->name"
            :description="is_array($user) ? $user['email'] . ' • ' . $user['id'] : $user->email . ' • ' . $user->id . ' • ' . __('Role:') . ' ' . (is_array($user) ? $user['role'] : $user->role)"
            icon="users"
            :badge="is_array($user) ? $user['role'] : $user->role">
            <x-slot:actions>
                <div class="flex items-center gap-2">
                    <a href="{{ route('dashboard.admin.users') }}" class="btn btn-secondary text-xs flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                        {{ __('Back to Users') }}
                    </a>
                    <button class="btn btn-secondary text-xs">{{ __('Reset Password') }}</button>
                    @if((is_array($user) ? $user['status'] : $user->status) === 'active')
                        <button class="btn btn-secondary text-xs text-error-600 border-error-200 hover:bg-error-50 dark:hover:bg-error-900/20">{{ __('Suspend User') }}</button>
                    @else
                        <button class="btn btn-secondary text-xs text-success-600 border-success-200 hover:bg-success-50 dark:hover:bg-success-900/20">{{ __('Reactivate') }}</button>
                    @endif
                </div>
            </x-slot:actions>
        </x-page-header>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-8">
                <div class="card p-6 md:p-8">
                    <h2 class="card-title text-xl mb-6">{{ __('User Details') }}</h2>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-6 pt-2">
                        <div>
                            <p class="card-kv-label">{{ __('Email') }}</p>
                            <p class="card-kv-value mt-1">{{ is_array($user) ? $user['email'] : $user->email }}</p>
                        </div>
                        <div>
                            <p class="card-kv-label">{{ __('Phone') }}</p>
                            <p class="card-kv-value mt-1">{{ is_array($user) ? $user['phone'] : $user->phone }}</p>
                        </div>
                        <div>
                            <p class="card-kv-label">{{ __('Joined') }}</p>
                            <p class="card-kv-value mt-1">{{ is_array($user) ? $user['joined'] : $user->joined }}</p>
                        </div>
                        <div>
                            <p class="card-kv-label">{{ __('KYC Status') }}</p>
                            <p class="font-medium text-success-600 dark:text-success-400 mt-1">{{ ucfirst(is_array($user) ? $user['kyc_status'] : $user->kyc_status) }}</p>
                        </div>
                        <div class="col-span-2">
                            <p class="card-kv-label">{{ __('Last Login') }}</p>
                            <p class="font-mono text-sm text-muted dark:text-dark-subtle mt-1">{{ is_array($user) ? $user['last_login'] : $user->last_login }}</p>
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
                    <h3 class="card-title text-lg mb-4">{{ __('Role Assignment') }}</h3>
                    <form method="POST" action="{{ route('dashboard.admin.users.role', is_array($user) ? $user['id'] : $user->id) }}">
                        @csrf
                        <div class="space-y-2">
                            @foreach(['Administrator', 'Property Manager', 'Agent', 'Developer', 'Host', 'Seller', 'User'] as $r)
                                <label class="flex items-center gap-3 cursor-pointer p-2 rounded-xl hover:bg-surface dark:hover:bg-surface/50 transition-colors">
                                    <input type="radio" name="role" value="{{ $r }}" class="w-4 h-4 text-primary-600 border-warm dark:border-warm dark:bg-surface" {{ (is_array($user) ? $user['role'] : $user->role) === $r ? 'checked' : '' }}>
                                    <span class="text-sm font-medium text-muted dark:text-white">{{ $r }}</span>
                                </label>
                            @endforeach
                        </div>
                        <button type="submit" class="btn btn-primary w-full mt-6">{{ __('Update Role') }}</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="space-y-8">
            <div class="card p-6">
                <h3 class="card-title text-lg mb-4">{{ __('Audit Trail') }}</h3>
                <div class="card-nested space-y-3">
                    @if(is_array($user) ? isset($user['audit_logs']) : isset($user->audit_logs))
                        @foreach(is_array($user) ? $user['audit_logs'] : $user->audit_logs as $log)
                            <div class="flex items-center justify-between p-3 rounded-xl bg-surface dark:bg-surface/50">
                                <div class="flex items-center gap-3">
                                    <div class="w-2 h-2 rounded-full bg-primary-500"></div>
                                    <div>
                                        <p class="font-medium text-sm">{{ $log['action'] ?? $log->action }}</p>
                                        <p class="text-xs text-muted">{{ $log['date'] ?? $log->date }}</p>
                                    </div>
                                </div>
                                <span class="text-xs text-muted">{{ $log['ip'] ?? $log->ip }}</span>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted text-center py-4">{{ __('No audit logs available for this user.') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layouts.dashboard>