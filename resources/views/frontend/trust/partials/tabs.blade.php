<div class="mb-8 border-b border-[#EBDBC9] dark:border-[#273244] overflow-x-auto">
    <nav class="-mb-px flex space-x-8 min-w-max" aria-label="Tabs">
        <a href="{{ route('dashboard.trust.index') }}" 
           class="{{ request()->routeIs('dashboard.trust.index') ? 'border-primary-500 text-primary-600 dark:text-primary-400 font-bold' : 'border-transparent text-muted hover:text-muted hover:border-warm dark:text-dark-subtle dark:hover:text-white' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors">
            {{ __('Safety Center') }}
        </a>
        <a href="{{ route('dashboard.trust.verifications') }}" 
           class="{{ request()->routeIs('dashboard.trust.verifications*') ? 'border-primary-500 text-primary-600 dark:text-primary-400 font-bold' : 'border-transparent text-muted hover:text-muted hover:border-warm dark:text-dark-subtle dark:hover:text-white' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors">
            {{ __('Verifications Queue') }}
        </a>
        <a href="{{ route('dashboard.trust.reports') }}" 
           class="{{ request()->routeIs('dashboard.trust.reports*') ? 'border-primary-500 text-primary-600 dark:text-primary-400 font-bold' : 'border-transparent text-muted hover:text-muted hover:border-warm dark:text-dark-subtle dark:hover:text-white' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors">
            {{ __('User Reports') }}
        </a>
        <a href="{{ route('dashboard.trust.disputes') }}" 
           class="{{ request()->routeIs('dashboard.trust.disputes*') || request()->routeIs('dashboard.trust.cases.*') ? 'border-primary-500 text-primary-600 dark:text-primary-400 font-bold' : 'border-transparent text-muted hover:text-muted hover:border-warm dark:text-dark-subtle dark:hover:text-white' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors">
            {{ __('Dispute Center') }}
        </a>
    </nav>
</div>
