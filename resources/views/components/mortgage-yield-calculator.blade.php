@props([
    'price' => 5000000,
    'estimatedRent' => 35000,
    'currency' => 'ETB',
])

<div x-data="mortgageYieldCalculator({
        initialPrice: {{ (float) ($price ?: 5000000) }},
        initialRent: {{ (float) ($estimatedRent ?: 35000) }},
        currency: '{{ $currency }}'
    })"
    class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xl overflow-hidden transition-all">
    
    <!-- Calculator Header & Tabs -->
    <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30 flex flex-wrap items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="p-1.5 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </span>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">
                    {{ __('Ethiopian Real Estate Financial Estimator') }}
                </h3>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                {{ __('Evaluate bank mortgage terms, gross rental yield, and net return on investment.') }}
            </p>
        </div>

        <!-- Mode Tabs -->
        <div class="flex items-center p-1 bg-slate-200/70 dark:bg-slate-800 rounded-xl text-xs font-semibold">
            <button type="button" 
                    @click="activeTab = 'mortgage'"
                    class="px-3.5 py-1.5 rounded-lg transition-all"
                    :class="activeTab === 'mortgage' ? 'bg-white dark:bg-slate-900 text-amber-600 dark:text-amber-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'">
                {{ __('Mortgage Loan') }}
            </button>
            <button type="button" 
                    @click="activeTab = 'investment'"
                    class="px-3.5 py-1.5 rounded-lg transition-all"
                    :class="activeTab === 'investment' ? 'bg-white dark:bg-slate-900 text-amber-600 dark:text-amber-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'">
                {{ __('Rental Yield & ROI') }}
            </button>
        </div>
    </div>

    <div class="p-6">
        <!-- 1. Mortgage Tab -->
        <div x-show="activeTab === 'mortgage'" class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Inputs Column -->
                <div class="lg:col-span-7 space-y-5">
                    
                    <!-- Property Purchase Price -->
                    <div>
                        <div class="flex justify-between items-center text-xs font-semibold mb-1">
                            <label class="text-slate-700 dark:text-slate-300 uppercase tracking-wider">{{ __('Property Price') }}</label>
                            <span class="text-amber-600 dark:text-amber-400 font-mono text-sm" x-text="formatCurrency(price)"></span>
                        </div>
                        <input type="range" min="1000000" max="50000000" step="250000" x-model.number="price" class="w-full accent-amber-500">
                    </div>

                    <!-- Downpayment Slider -->
                    <div>
                        <div class="flex justify-between items-center text-xs font-semibold mb-1">
                            <label class="text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                                {{ __('Down Payment') }} (<span x-text="downpaymentPct"></span>%)
                            </label>
                            <span class="text-slate-900 dark:text-slate-100 font-mono text-xs" x-text="formatCurrency(downpaymentAmount)"></span>
                        </div>
                        <input type="range" min="15" max="60" step="5" x-model.number="downpaymentPct" class="w-full accent-amber-500">
                        <div class="flex justify-between text-[10px] text-slate-400 mt-0.5">
                            <span>15% (Min Commercial)</span>
                            <span>30% (Standard)</span>
                            <span>60% (Low Risk)</span>
                        </div>
                    </div>

                    <!-- Loan Term (Years) -->
                    <div>
                        <div class="flex justify-between items-center text-xs font-semibold mb-1">
                            <label class="text-slate-700 dark:text-slate-300 uppercase tracking-wider">{{ __('Loan Tenure') }}</label>
                            <span class="text-slate-900 dark:text-slate-100 font-semibold text-xs"><span x-text="loanTermYears"></span> {{ __('Years') }}</span>
                        </div>
                        <div class="grid grid-cols-4 gap-2">
                            <template x-for="yr in [5, 10, 15, 20]" :key="yr">
                                <button type="button" 
                                        @click="loanTermYears = yr"
                                        class="py-2 text-xs font-semibold rounded-lg border transition-all text-center"
                                        :class="loanTermYears === yr ? 'bg-amber-50 dark:bg-amber-950/40 border-amber-500 text-amber-700 dark:text-amber-400 font-bold' : 'border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:border-slate-300'">
                                    <span x-text="yr + ' Yrs'"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Interest Rate -->
                    <div>
                        <div class="flex justify-between items-center text-xs font-semibold mb-1">
                            <label class="text-slate-700 dark:text-slate-300 uppercase tracking-wider">{{ __('Annual Interest Rate') }}</label>
                            <span class="text-slate-900 dark:text-slate-100 font-mono text-xs"><span x-text="interestRate"></span>% p.a.</span>
                        </div>
                        <input type="range" min="12" max="24" step="0.5" x-model.number="interestRate" class="w-full accent-amber-500">
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                            {{ __('Typical rates: CBE ~16.5% - 17.5%, Private Ethiopian Commercial Banks ~18% - 19.5%') }}
                        </p>
                    </div>
                </div>

                <!-- Live Results Card -->
                <div class="lg:col-span-5 bg-gradient-to-br from-amber-500/10 via-amber-500/5 to-transparent border border-amber-500/20 rounded-2xl p-5 flex flex-col justify-between">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400">
                            {{ __('Estimated Monthly Payment') }}
                        </span>
                        <div class="text-2xl lg:text-3xl font-extrabold text-slate-900 dark:text-white mt-1 font-mono">
                            <span x-text="formatCurrency(monthlyPayment)"></span>
                            <span class="text-xs font-medium text-slate-500">/mo</span>
                        </div>

                        <div class="mt-5 space-y-2.5 pt-4 border-t border-amber-500/20 text-xs">
                            <div class="flex justify-between text-slate-600 dark:text-slate-300">
                                <span>{{ __('Loan Principal') }}:</span>
                                <span class="font-mono font-semibold" x-text="formatCurrency(loanAmount)"></span>
                            </div>
                            <div class="flex justify-between text-slate-600 dark:text-slate-300">
                                <span>{{ __('Total Interest Payable') }}:</span>
                                <span class="font-mono font-semibold text-rose-500" x-text="formatCurrency(totalInterest)"></span>
                            </div>
                            <div class="flex justify-between text-slate-600 dark:text-slate-300">
                                <span>{{ __('Total Cost of Loan') }}:</span>
                                <span class="font-mono font-bold" x-text="formatCurrency(totalRepayment)"></span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 p-3 bg-white/70 dark:bg-slate-900/70 rounded-xl border border-slate-200 dark:border-slate-800 text-[11px] text-slate-600 dark:text-slate-400">
                        <div class="font-semibold text-slate-800 dark:text-slate-200 mb-0.5">💡 {{ __('Bank Eligibility Tip') }}</div>
                        {{ __('Recommended gross monthly income of at least') }} <strong class="text-amber-600 dark:text-amber-400 font-mono" x-text="formatCurrency(monthlyPayment * 2.8)"></strong> {{ __('to qualify under DTI guidelines.') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Rental Yield & ROI Tab -->
        <div x-show="activeTab === 'investment'" class="space-y-6" x-cloak>
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Inputs Column -->
                <div class="lg:col-span-7 space-y-5">
                    <!-- Expected Monthly Rent -->
                    <div>
                        <div class="flex justify-between items-center text-xs font-semibold mb-1">
                            <label class="text-slate-700 dark:text-slate-300 uppercase tracking-wider">{{ __('Projected Monthly Rent') }}</label>
                            <span class="text-emerald-600 dark:text-emerald-400 font-mono text-sm" x-text="formatCurrency(monthlyRent)"></span>
                        </div>
                        <input type="range" min="5000" max="300000" step="5000" x-model.number="monthlyRent" class="w-full accent-emerald-500">
                    </div>

                    <!-- Annual Maintenance & Management Allowance -->
                    <div>
                        <div class="flex justify-between items-center text-xs font-semibold mb-1">
                            <label class="text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                                {{ __('Maintenance & Management') }} (<span x-text="operatingExpensePct"></span>%)
                            </label>
                            <span class="text-slate-500 font-mono text-xs" x-text="formatCurrency((annualGrossRent * operatingExpensePct) / 100) + ' /yr'"></span>
                        </div>
                        <input type="range" min="2" max="20" step="1" x-model.number="operatingExpensePct" class="w-full accent-emerald-500">
                    </div>

                    <!-- Estimated Vacancy (Days/Months) -->
                    <div>
                        <div class="flex justify-between items-center text-xs font-semibold mb-1">
                            <label class="text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                                {{ __('Vacancy Provision') }} (<span x-text="vacancyRateWeeks"></span> {{ __('Weeks/Yr') }})
                            </label>
                            <span class="text-slate-500 font-mono text-xs" x-text="formatCurrency((annualGrossRent * (vacancyRateWeeks / 52))) + ' /yr'"></span>
                        </div>
                        <input type="range" min="0" max="8" step="1" x-model.number="vacancyRateWeeks" class="w-full accent-emerald-500">
                    </div>
                </div>

                <!-- ROI Metrics Showcase -->
                <div class="lg:col-span-5 bg-gradient-to-br from-emerald-500/10 via-emerald-500/5 to-transparent border border-emerald-500/20 rounded-2xl p-5 flex flex-col justify-between">
                    <div>
                        <div class="grid grid-cols-2 gap-3 mb-4">
                            <div class="bg-white/80 dark:bg-slate-900/80 p-3 rounded-xl border border-slate-200 dark:border-slate-800 text-center">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">{{ __('Gross Rental Yield') }}</span>
                                <div class="text-xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-1 font-mono">
                                    <span x-text="grossRentalYield.toFixed(2)"></span>%
                                </div>
                            </div>
                            <div class="bg-white/80 dark:bg-slate-900/80 p-3 rounded-xl border border-slate-200 dark:border-slate-800 text-center">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">{{ __('Net Cap Rate') }}</span>
                                <div class="text-xl font-extrabold text-emerald-700 dark:text-emerald-300 mt-1 font-mono">
                                    <span x-text="netCapRate.toFixed(2)"></span>%
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2.5 pt-3 border-t border-emerald-500/20 text-xs">
                            <div class="flex justify-between text-slate-600 dark:text-slate-300">
                                <span>{{ __('Gross Annual Rent') }}:</span>
                                <span class="font-mono font-semibold" x-text="formatCurrency(annualGrossRent)"></span>
                            </div>
                            <div class="flex justify-between text-slate-600 dark:text-slate-300">
                                <span>{{ __('Net Operating Income (NOI)') }}:</span>
                                <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400" x-text="formatCurrency(netOperatingIncome)"></span>
                            </div>
                            <div class="flex justify-between text-slate-600 dark:text-slate-300">
                                <span>{{ __('Payback Period') }}:</span>
                                <span class="font-mono font-semibold" x-text="paybackYears.toFixed(1) + ' Years'"></span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 p-2.5 bg-emerald-50/50 dark:bg-emerald-950/20 rounded-xl border border-emerald-200 dark:border-emerald-800/40 text-[11px] text-emerald-800 dark:text-emerald-300">
                        {{ __('Addis Ababa rental yields historically average between 7.5% and 11.2% in Bole, Kazanchis, and Sarbet areas.') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('mortgageYieldCalculator', (config) => ({
        activeTab: 'mortgage',
        price: config.initialPrice,
        currency: config.currency,
        
        // Mortgage inputs
        downpaymentPct: 30,
        loanTermYears: 15,
        interestRate: 17.5,

        // Rental yield inputs
        monthlyRent: config.initialRent,
        operatingExpensePct: 8,
        vacancyRateWeeks: 3,

        get downpaymentAmount() {
            return (this.price * this.downpaymentPct) / 100;
        },

        get loanAmount() {
            return this.price - this.downpaymentAmount;
        },

        get monthlyPayment() {
            const P = this.loanAmount;
            const r = (this.interestRate / 100) / 12;
            const n = this.loanTermYears * 12;
            if (P <= 0 || r <= 0 || n <= 0) return 0;
            return (P * r * Math.pow(1 + r, n)) / (Math.pow(1 + r, n) - 1);
        },

        get totalRepayment() {
            return this.monthlyPayment * this.loanTermYears * 12;
        },

        get totalInterest() {
            return Math.max(0, this.totalRepayment - this.loanAmount);
        },

        // Yield computations
        get annualGrossRent() {
            return this.monthlyRent * 12;
        },

        get grossRentalYield() {
            if (this.price <= 0) return 0;
            return (this.annualGrossRent / this.price) * 100;
        },

        get netOperatingIncome() {
            const expenses = (this.annualGrossRent * this.operatingExpensePct) / 100;
            const vacancyLoss = (this.annualGrossRent * (this.vacancyRateWeeks / 52));
            return Math.max(0, this.annualGrossRent - expenses - vacancyLoss);
        },

        get netCapRate() {
            if (this.price <= 0) return 0;
            return (this.netOperatingIncome / this.price) * 100;
        },

        get paybackYears() {
            if (this.netOperatingIncome <= 0) return 0;
            return this.price / this.netOperatingIncome;
        },

        formatCurrency(num) {
            return new Intl.NumberFormat('en-ET', { maximumFractionDigits: 0 }).format(Math.round(num)) + ' ' + this.currency;
        }
    }));
});
</script>
