<div class="mb-8 border-b border-warm dark:border-warm overflow-x-auto">
    <nav class="-mb-px flex space-x-8 min-w-max" aria-label="Tabs">
        <a href="{{ route('dashboard.admin.index') }}" 
           class="{{ request()->routeIs('dashboard.admin.index') ? 'border-error-500 text-error-600 dark:text-error-400 font-bold' : 'border-transparent text-muted hover:text-muted hover:border-warm dark:text-muted dark:hover:text-muted' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors">
            Overview
        </a>
        <a href="{{ route('dashboard.admin.users') }}" 
           class="{{ request()->routeIs('dashboard.admin.users*') ? 'border-primary-500 text-primary-600 dark:text-primary-400 font-bold' : 'border-transparent text-muted hover:text-muted hover:border-warm dark:text-muted dark:hover:text-muted' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors">
            {{ __('Users & Team') }}
        </a>
        <a href="{{ route('dashboard.admin.roles') }}" 
           class="{{ request()->routeIs('dashboard.admin.roles*') ? 'border-primary-500 text-primary-600 dark:text-primary-400 font-bold' : 'border-transparent text-muted hover:text-muted hover:border-warm dark:text-muted dark:hover:text-muted' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors">
            {{ __('Roles') }}
        </a>
        <a href="{{ route('dashboard.admin.permissions') }}" 
           class="{{ request()->routeIs('dashboard.admin.permissions*') ? 'border-primary-500 text-primary-600 dark:text-primary-400 font-bold' : 'border-transparent text-muted hover:text-muted hover:border-warm dark:text-muted dark:hover:text-muted' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors">
            {{ __('Permissions & Access') }}
        </a>
        <a href="{{ route('dashboard.admin.audit') }}" 
           class="{{ request()->routeIs('dashboard.admin.audit') ? 'border-primary-500 text-primary-600 dark:text-primary-400 font-bold' : 'border-transparent text-muted hover:text-muted hover:border-warm dark:text-muted dark:hover:text-muted' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors">
            {{ __('Audit Logs') }}
        </a>
        <a href="{{ route('dashboard.admin.settings') }}" 
           class="{{ request()->routeIs('dashboard.admin.settings') ? 'border-error-500 text-error-600 dark:text-error-400 font-bold' : 'border-transparent text-muted hover:text-muted hover:border-warm dark:text-muted dark:hover:text-muted' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors">
            Global Settings
        </a>
    </nav>
</div>
