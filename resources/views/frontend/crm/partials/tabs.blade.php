<div class="mb-8 border-b border-warm dark:border-warm">
    <nav class="-mb-px flex space-x-8 overflow-x-auto" aria-label="Tabs">
        <a href="{{ route('dashboard.crm.index') }}" 
           class="{{ request()->routeIs('dashboard.crm.index') ? 'border-primary-500 text-primary-600 dark:text-primary-400 font-bold' : 'border-transparent text-muted hover:text-muted hover:border-warm dark:text-muted dark:hover:text-muted' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors">
            {{ __('CRM Overview') }}
        </a>
        <a href="{{ route('dashboard.crm.pipeline') }}" 
           class="{{ request()->routeIs('dashboard.crm.pipeline') ? 'border-primary-500 text-primary-600 dark:text-primary-400 font-bold' : 'border-transparent text-muted hover:text-muted hover:border-warm dark:text-muted dark:hover:text-muted' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors">
            {{ __('Pipeline') }}
        </a>
        <a href="{{ route('dashboard.crm.leads') }}" 
           class="{{ request()->routeIs('dashboard.crm.leads*') ? 'border-primary-500 text-primary-600 dark:text-primary-400 font-bold' : 'border-transparent text-muted hover:text-muted hover:border-warm dark:text-muted dark:hover:text-muted' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors">
            {{ __('All Leads') }}
        </a>
        <a href="{{ route('dashboard.crm.clients') }}" 
           class="{{ request()->routeIs('dashboard.crm.clients') ? 'border-primary-500 text-primary-600 dark:text-primary-400 font-bold' : 'border-transparent text-muted hover:text-muted hover:border-warm dark:text-muted dark:hover:text-muted' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors">
            {{ __('Clients') }}
        </a>
        <a href="{{ route('dashboard.crm.tasks') }}" 
           class="{{ request()->routeIs('dashboard.crm.tasks') ? 'border-primary-500 text-primary-600 dark:text-primary-400 font-bold' : 'border-transparent text-muted hover:text-muted hover:border-warm dark:text-muted dark:hover:text-muted' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors">
            {{ __('Tasks & Follow-ups') }}
        </a>
        <a href="{{ route('dashboard.crm.commissions') }}" 
           class="{{ request()->routeIs('dashboard.crm.commissions') ? 'border-primary-500 text-primary-600 dark:text-primary-400 font-bold' : 'border-transparent text-muted hover:text-muted hover:border-warm dark:text-muted dark:hover:text-muted' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors">
            {{ __('Commissions') }}
        </a>
    </nav>
</div>
