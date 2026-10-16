<div class="mb-8 border-b border-warm dark:border-warm">
    <nav class="-mb-px flex space-x-8 overflow-x-auto" aria-label="Tabs">
        <a href="{{ route('dashboard.rental.index') }}" 
           class="{{ request()->routeIs('dashboard.rental.index') ? 'border-primary-500 text-primary-600 dark:text-primary-400 font-bold' : 'border-transparent text-muted hover:text-muted hover:border-warm dark:text-muted dark:hover:text-muted' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors">
            {{ __('Dashboard') }}
        </a>
        <a href="{{ route('dashboard.rental.applications') }}" 
           class="{{ request()->routeIs('dashboard.rental.applications') ? 'border-primary-500 text-primary-600 dark:text-primary-400 font-bold' : 'border-transparent text-muted hover:text-muted hover:border-warm dark:text-muted dark:hover:text-muted' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors">
            {{ __('Applications') }}
        </a>
        <a href="{{ route('dashboard.rental.tenants') }}" 
           class="{{ request()->routeIs('dashboard.rental.tenants*') ? 'border-primary-500 text-primary-600 dark:text-primary-400 font-bold' : 'border-transparent text-muted hover:text-muted hover:border-warm dark:text-muted dark:hover:text-muted' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors">
            {{ __('Tenants') }}
        </a>
        <a href="{{ route('dashboard.rental.leases') }}" 
           class="{{ request()->routeIs('dashboard.rental.leases*') ? 'border-primary-500 text-primary-600 dark:text-primary-400 font-bold' : 'border-transparent text-muted hover:text-muted hover:border-warm dark:text-muted dark:hover:text-muted' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors">
            {{ __('Leases') }}
        </a>
        <a href="{{ route('dashboard.rental.payments') }}" 
           class="{{ request()->routeIs('dashboard.rental.payments') ? 'border-primary-500 text-primary-600 dark:text-primary-400 font-bold' : 'border-transparent text-muted hover:text-muted hover:border-warm dark:text-muted dark:hover:text-muted' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors">
            {{ __('Payments') }}
        </a>
    </nav>
</div>
