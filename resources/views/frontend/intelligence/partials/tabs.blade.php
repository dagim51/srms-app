<div class="mb-8 border-b border-warm dark:border-warm overflow-x-auto">
    <nav class="-mb-px flex space-x-8 min-w-max" aria-label="Tabs">
        <a href="{{ route('dashboard.intelligence.valuation') }}" 
           class="{{ request()->routeIs('dashboard.intelligence.valuation') ? 'border-primary-500 text-primary-600 dark:text-primary-400 font-bold' : 'border-transparent text-muted hover:text-muted hover:border-warm dark:text-muted dark:hover:text-muted' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors">
            {{ __('Valuation Tool') }}
        </a>
        <a href="{{ route('dashboard.intelligence.calculators') }}" 
           class="{{ request()->routeIs('dashboard.intelligence.calculators') ? 'border-primary-500 text-primary-600 dark:text-primary-400 font-bold' : 'border-transparent text-muted hover:text-muted hover:border-warm dark:text-muted dark:hover:text-muted' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors">
            {{ __('Investment Calculators') }}
        </a>
        <a href="{{ route('dashboard.intelligence.portfolio') }}" 
           class="{{ request()->routeIs('dashboard.intelligence.portfolio') ? 'border-primary-500 text-primary-600 dark:text-primary-400 font-bold' : 'border-transparent text-muted hover:text-muted hover:border-warm dark:text-muted dark:hover:text-muted' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors">
            {{ __('Portfolio View') }}
        </a>
    </nav>
</div>
