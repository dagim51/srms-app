<div class="mb-8 border-b border-warm dark:border-warm">
    <nav class="-mb-px flex space-x-8 overflow-x-auto" aria-label="Tabs">
        <a href="{{ route('dashboard.projects.show', $project['id']) }}" 
           class="{{ request()->routeIs('dashboard.projects.show') ? 'border-primary-500 text-primary-600 dark:text-primary-400 font-bold' : 'border-transparent text-muted hover:text-muted hover:border-warm dark:text-muted dark:hover:text-muted' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors">
            {{ __('Overview') }}
        </a>
        <a href="{{ route('dashboard.projects.buildings', $project['id']) }}" 
           class="{{ request()->routeIs('dashboard.projects.buildings') ? 'border-primary-500 text-primary-600 dark:text-primary-400 font-bold' : 'border-transparent text-muted hover:text-muted hover:border-warm dark:text-muted dark:hover:text-muted' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors">
            {{ __('Buildings') }}
        </a>
        <a href="{{ route('dashboard.projects.units', $project['id']) }}" 
           class="{{ request()->routeIs('dashboard.projects.units') ? 'border-primary-500 text-primary-600 dark:text-primary-400 font-bold' : 'border-transparent text-muted hover:text-muted hover:border-warm dark:text-muted dark:hover:text-muted' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors">
            {{ __('Unit Inventory') }}
        </a>
        <a href="{{ route('dashboard.projects.progress', $project['id']) }}" 
           class="{{ request()->routeIs('dashboard.projects.progress') ? 'border-primary-500 text-primary-600 dark:text-primary-400 font-bold' : 'border-transparent text-muted hover:text-muted hover:border-warm dark:text-muted dark:hover:text-muted' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors">
            {{ __('Construction Progress') }}
        </a>
        <a href="{{ route('dashboard.projects.documents', $project['id']) }}" 
           class="{{ request()->routeIs('dashboard.projects.documents') ? 'border-primary-500 text-primary-600 dark:text-primary-400 font-bold' : 'border-transparent text-muted hover:text-muted hover:border-warm dark:text-muted dark:hover:text-muted' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors">
            {{ __('Documents & Media') }}
        </a>
    </nav>
</div>
