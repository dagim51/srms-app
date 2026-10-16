<div class="mb-8 border-b border-warm dark:border-warm overflow-x-auto">
    <nav class="-mb-px flex space-x-8 min-w-max" aria-label="Tabs">
        <a href="{{ route('dashboard.ai.search') }}" 
           class="{{ request()->routeIs('dashboard.ai.search') ? 'border-primary-500 text-primary-600 dark:text-primary-400 font-bold' : 'border-transparent text-muted hover:text-muted hover:border-warm dark:text-muted dark:hover:text-muted' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            AI Search
        </a>
        <a href="{{ route('dashboard.ai.assistant') }}" 
           class="{{ request()->routeIs('dashboard.ai.assistant') ? 'border-primary-500 text-primary-600 dark:text-primary-400 font-bold' : 'border-transparent text-muted hover:text-muted hover:border-warm dark:text-muted dark:hover:text-muted' }} whitespace-nowrap py-4 px-1 border-b-2 text-sm transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
            Listing Assistant
        </a>
    </nav>
</div>
