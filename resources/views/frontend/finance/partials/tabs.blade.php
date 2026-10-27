<div class="mb-8 border-b border-warm dark:border-warm overflow-x-auto">
    <nav class="-mb-px flex space-x-8 min-w-max" aria-label="Tabs">
        <a href="{{ route('dashboard.finance.index') }}" 
           class="{{ request()->routeIs('dashboard.finance.index') ? 'border-primary-500 text-primary-600 dark:text-primary-400 font-bold' : 'border-transparent text-muted hover:text-muted hover:border-warm dark:text-muted dark:hover:text-muted' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors">
            {{ __('Financial Summary') }}
        </a>
        <a href="{{ route('dashboard.finance.transactions') }}" 
           class="{{ request()->routeIs('dashboard.finance.transactions*') ? 'border-primary-500 text-primary-600 dark:text-primary-400 font-bold' : 'border-transparent text-muted hover:text-muted hover:border-warm dark:text-muted dark:hover:text-muted' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors">
            {{ __('Transactions') }}
        </a>
        <a href="{{ route('dashboard.finance.invoices') }}" 
           class="{{ request()->routeIs('dashboard.finance.invoices*') ? 'border-primary-500 text-primary-600 dark:text-primary-400 font-bold' : 'border-transparent text-muted hover:text-muted hover:border-warm dark:text-muted dark:hover:text-muted' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors">
            {{ __('Invoices') }}
        </a>
        <a href="{{ route('dashboard.finance.payouts') }}" 
           class="{{ request()->routeIs('dashboard.finance.payouts*') ? 'border-primary-500 text-primary-600 dark:text-primary-400 font-bold' : 'border-transparent text-muted hover:text-muted hover:border-warm dark:text-muted dark:hover:text-muted' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors">
            {{ __('Payouts & Settlements') }}
        </a>
        <a href="{{ route('dashboard.finance.ledger') }}" 
           class="{{ request()->routeIs('dashboard.finance.ledger*') ? 'border-primary-500 text-primary-600 dark:text-primary-400 font-bold' : 'border-transparent text-muted hover:text-muted hover:border-warm dark:text-muted dark:hover:text-muted' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors">
            {{ __('Ledger View') }}
        </a>
    </nav>
</div>
