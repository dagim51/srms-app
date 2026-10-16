<div class="mb-8 border-b border-warm dark:border-warm">
    <nav class="-mb-px flex space-x-8 overflow-x-auto" aria-label="Tabs">
        <a href="{{ route('dashboard.sales.units') }}" 
           class="{{ request()->routeIs('dashboard.sales.units') || request()->routeIs('dashboard.sales.units.show') || request()->routeIs('dashboard.sales.reservation') ? 'border-primary-500 text-primary-600 dark:text-primary-400 font-bold' : 'border-transparent text-muted hover:text-muted hover:border-warm dark:text-muted dark:hover:text-muted' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors">
            {{ __('Unit Inventory') }}
        </a>
        <a href="{{ route('dashboard.sales.buyers') }}" 
           class="{{ request()->routeIs('dashboard.sales.buyers*') ? 'border-primary-500 text-primary-600 dark:text-primary-400 font-bold' : 'border-transparent text-muted hover:text-muted hover:border-warm dark:text-muted dark:hover:text-muted' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors">
            {{ __('Buyers') }}
        </a>
        <a href="{{ route('dashboard.sales.payment_plans') }}" 
           class="{{ request()->routeIs('dashboard.sales.payment_plans') ? 'border-primary-500 text-primary-600 dark:text-primary-400 font-bold' : 'border-transparent text-muted hover:text-muted hover:border-warm dark:text-muted dark:hover:text-muted' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors">
            {{ __('Payment Plans') }}
        </a>
        <a href="{{ route('dashboard.sales.contracts') }}" 
           class="{{ request()->routeIs('dashboard.sales.contracts') ? 'border-primary-500 text-primary-600 dark:text-primary-400 font-bold' : 'border-transparent text-muted hover:text-muted hover:border-warm dark:text-muted dark:hover:text-muted' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors">
            {{ __('Contracts') }}
        </a>
        <a href="{{ route('dashboard.sales.handovers') }}" 
           class="{{ request()->routeIs('dashboard.sales.handovers') ? 'border-primary-500 text-primary-600 dark:text-primary-400 font-bold' : 'border-transparent text-muted hover:text-muted hover:border-warm dark:text-muted dark:hover:text-muted' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors">
            {{ __('Handovers') }}
        </a>
    </nav>
</div>
