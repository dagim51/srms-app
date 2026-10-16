<div class="mb-8 border-b border-warm dark:border-warm">
    <nav class="-mb-px flex space-x-8 overflow-x-auto" aria-label="Tabs">
        <a href="{{ route('dashboard.organization.index') }}" 
           class="{{ request()->routeIs('dashboard.organization.index') ? 'border-primary-500 text-primary-600 dark:text-primary-400 font-bold' : 'border-transparent text-muted hover:text-muted hover:border-warm dark:text-muted dark:hover:text-muted' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors">
            {{ __('Overview') }}
        </a>
        <a href="{{ route('dashboard.organization.profile') }}" 
           class="{{ request()->routeIs('dashboard.organization.profile') ? 'border-primary-500 text-primary-600 dark:text-primary-400 font-bold' : 'border-transparent text-muted hover:text-muted hover:border-warm dark:text-muted dark:hover:text-muted' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors">
            {{ __('Company Profile') }}
        </a>
        <a href="{{ route('dashboard.organization.branches') }}" 
           class="{{ request()->routeIs('dashboard.organization.branches') ? 'border-primary-500 text-primary-600 dark:text-primary-400 font-bold' : 'border-transparent text-muted hover:text-muted hover:border-warm dark:text-muted dark:hover:text-muted' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors">
            {{ __('Branches') }}
        </a>
        <a href="{{ route('dashboard.organization.teams') }}" 
           class="{{ request()->routeIs('dashboard.organization.teams') ? 'border-primary-500 text-primary-600 dark:text-primary-400 font-bold' : 'border-transparent text-muted hover:text-muted hover:border-warm dark:text-muted dark:hover:text-muted' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors">
            {{ __('Teams') }}
        </a>
        <a href="{{ route('dashboard.organization.members') }}" 
           class="{{ request()->routeIs('dashboard.organization.members') ? 'border-primary-500 text-primary-600 dark:text-primary-400 font-bold' : 'border-transparent text-muted hover:text-muted hover:border-warm dark:text-muted dark:hover:text-muted' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors">
            {{ __('Members & Agents') }}
        </a>
    </nav>
</div>
