<div class="mb-8 border-b border-warm dark:border-warm overflow-x-auto">
    <nav class="-mb-px flex space-x-8 min-w-max" aria-label="Tabs">
        <a href="{{ route('dashboard.alerts.index') }}" 
           class="{{ request()->routeIs('dashboard.alerts.*') ? 'border-primary-500 text-primary-600 dark:text-primary-400 font-bold' : 'border-transparent text-muted hover:text-muted hover:border-warm dark:text-muted dark:hover:text-muted' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors">
            Saved Searches & Alerts
        </a>
        <a href="{{ route('dashboard.recommendations.index') }}" 
           class="{{ request()->routeIs('dashboard.recommendations.*') ? 'border-primary-500 text-primary-600 dark:text-primary-400 font-bold' : 'border-transparent text-muted hover:text-muted hover:border-warm dark:text-muted dark:hover:text-muted' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors flex items-center gap-2">
            Recommendations
            <span class="badge badge-primary bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400 border-0 text-xs">{{ __('New') }}</span>
        </a>
    </nav>
</div>
