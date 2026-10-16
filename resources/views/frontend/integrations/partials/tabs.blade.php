<div class="mb-8 border-b border-warm dark:border-warm overflow-x-auto">
    <nav class="-mb-px flex space-x-8 min-w-max" aria-label="Tabs">
        <a href="{{ route('dashboard.integrations.index') }}" 
           class="{{ request()->routeIs('dashboard.integrations.index') ? 'border-primary-500 text-primary-600 dark:text-primary-400 font-bold' : 'border-transparent text-muted hover:text-muted hover:border-warm dark:text-muted dark:hover:text-muted' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors">
            {{ __('Connected Services') }}
        </a>
        <a href="{{ route('dashboard.integrations.history') }}" 
           class="{{ request()->routeIs('dashboard.integrations.history') ? 'border-primary-500 text-primary-600 dark:text-primary-400 font-bold' : 'border-transparent text-muted hover:text-muted hover:border-warm dark:text-muted dark:hover:text-muted' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors">
            {{ __('Integration History') }}
        </a>
    </nav>
</div>
