<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Investment Calculators') }}</x-slot>

    <div class="max-w-7xl mx-auto page-enter">
        <x-page-header
            :title="__('Investment Calculators')"
            :description="__('Evaluate mortgages, rental yields, cap rates, and financing feasibility for real estate investments.')"
            icon="chart"
            badge="Financial Calculators"
        />

        @include('frontend.intelligence.partials.tabs')

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            
            <!-- Mortgage Calculator -->
            <div class="card p-6 md:p-8">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                    </div>
                    <h2 class="card-title text-xl">{{ __('Mortgage Calculator') }}</h2>
                </div>
                
                <div class="space-y-5 mb-8">
                    <div>
                        <label class="card-kv-label block mb-1.5">{{ __('Home Price (ETB)') }}</label>
                        <input type="number" value="{{ $mortgage['principal'] }}" class="input w-full">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="card-kv-label block mb-1.5">{{ __('Down Payment') }}</label>
                            <input type="number" value="{{ $mortgage['down_payment'] }}" class="input w-full">
                        </div>
                        <div>
                            <label class="card-kv-label block mb-1.5">{{ __('Loan Term') }}</label>
                            <select class="input w-full">
                                <option {{ $mortgage['years'] == 15 ? 'selected' : '' }}>{{ __('15 Years') }}</option>
                                <option {{ $mortgage['years'] == 20 ? 'selected' : '' }}>{{ __('20 Years') }}</option>
                                <option {{ $mortgage['years'] == 30 ? 'selected' : '' }}>{{ __('30 Years') }}</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="card-kv-label block mb-1.5">{{ __('Interest Rate (%)') }}</label>
                        <input type="number" step="0.1" value="{{ $mortgage['interest_rate'] }}" class="input w-full">
                    </div>
                </div>

                <div class="card-nested p-6 text-center">
                    <span class="card-stat-label block mb-2">{{ __('Estimated Monthly Payment') }}</span>
                    <span class="card-stat-value text-4xl text-primary-600 dark:text-primary-400">{{ $mortgage['monthly_payment'] }}</span>
                </div>
            </div>

            <!-- Investment Metrics -->
            <div class="card p-6 md:p-8">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-success-100 dark:bg-success-900/30 text-success-600 dark:text-success-400 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                    </div>
                    <h2 class="card-title text-xl">{{ __('Investment Metrics') }}</h2>
                </div>
                
                <div class="space-y-5 mb-8">
                    <div>
                        <label class="card-kv-label block mb-1.5">{{ __('Purchase Price (ETB)') }}</label>
                        <input type="number" value="{{ $investment['purchase_price'] }}" class="input w-full">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="card-kv-label block mb-1.5">{{ __('Monthly Rent (ETB)') }}</label>
                            <input type="number" value="{{ $investment['monthly_rent'] }}" class="input w-full">
                        </div>
                        <div>
                            <label class="card-kv-label block mb-1.5">{{ __('Annual Expenses') }}</label>
                            <input type="number" value="{{ $investment['annual_expenses'] }}" class="input w-full">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div class="card-nested p-4 text-center border-success-200/60 dark:border-success-900/30 bg-success-50/40 dark:bg-success-950/20">
                        <span class="block text-xs font-semibold text-success-700 dark:text-success-400 uppercase tracking-wider mb-1" title="Capitalization Rate">{{ __('Cap Rate') }}</span>
                        <span class="text-2xl font-black text-success-600 dark:text-success-400">{{ $investment['cap_rate'] }}</span>
                    </div>
                    <div class="card-nested p-4 text-center border-primary-200/60 dark:border-primary-900/30 bg-primary-50/40 dark:bg-primary-950/20">
                        <span class="block text-xs font-semibold text-primary-700 dark:text-primary-400 uppercase tracking-wider mb-1" title="Cash on Cash Return">{{ __('Cash on Cash') }}</span>
                        <span class="text-2xl font-black text-primary-600 dark:text-primary-400">{{ $investment['cash_on_cash'] }}</span>
                    </div>
                    <div class="card-nested p-4 text-center border-warning-200/60 dark:border-warning-900/30 bg-warning-50/40 dark:bg-warning-950/20">
                        <span class="block text-xs font-semibold text-warning-700 dark:text-warning-400 uppercase tracking-wider mb-1" title="Gross Yield">{{ __('Gross Yield') }}</span>
                        <span class="text-2xl font-black text-warning-700 dark:text-warning-400">{{ $investment['gross_yield'] }}</span>
                    </div>
                </div>
            </div>

        </div>

    </div>
</x-layouts.dashboard>
