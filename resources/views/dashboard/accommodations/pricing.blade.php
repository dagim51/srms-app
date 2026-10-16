<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Pricing & Policies') }}</x-slot>
    <x-slot name="breadcrumb">
        <a href="{{ route('dashboard') }}" class="hover:text-primary-600 transition-colors">{{ __('Dashboard') }}</a>
        <span>/</span>
        <a href="{{ route('dashboard.accommodations.index') }}" class="hover:text-primary-600 transition-colors">{{ __('Hosting') }}</a>
        <span>/</span>
        <a href="{{ route('dashboard.accommodations.show', $accommodation) }}" class="hover:text-primary-600 transition-colors">{{ __('Manage') }}</a>
        <span>/</span>
        <span class="text-muted dark:text-white font-medium">{{ __('Pricing') }}</span>
    </x-slot>

    <div class="max-w-6xl mx-auto space-y-6">
        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 text-emerald-800 text-sm font-medium border border-emerald-200">
                {{ session('success') }}
            </div>
        @endif
        @if($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 text-rose-800 text-sm font-medium border border-rose-200">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            {{-- Left Column: Base config --}}
            <div class="lg:col-span-2 space-y-6">
                
                {{-- Base Pricing & Policies Form --}}
                <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-[#273244] rounded-2xl shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-warm dark:border-warm">
                        <h2 class="text-xl font-bold font-display text-muted dark:text-white">{{ __('Base Pricing & Stay Policies') }}</h2>
                        <p class="text-muted text-sm mt-1">{{ __('Set your standard nightly rate, length of stay rules, and cancellation terms.') }}</p>
                    </div>

                    <form action="{{ route('dashboard.accommodations.pricing.update', $accommodation) }}" method="POST" class="p-6 space-y-6">
                        @csrf @method('PUT')
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-muted dark:text-dark-subtle mb-1">{{ __('Base Nightly Rate') }}</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-muted sm:text-sm">{{ $accommodation->property->currency === 'USD' ? '$' : 'Br' }}</span>
                                    </div>
                                    <input type="number" name="base_price" step="0.01" value="{{ old('base_price', $accommodation->property->price) }}" class="form-input pl-10 w-full rounded-xl" required>
                                </div>
                                <p class="text-xs text-muted mt-1">{{ __('This applies to all un-modified dates.') }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-muted dark:text-dark-subtle mb-1">{{ __('Cancellation Policy') }}</label>
                                <select name="cancellation_policy" class="form-input w-full rounded-xl">
                                    <option value="flexible" {{ old('cancellation_policy', $accommodation->cancellation_policy) === 'flexible' ? 'selected' : '' }}>{{ __('Flexible (Full refund 1 day prior)') }}</option>
                                    <option value="moderate" {{ old('cancellation_policy', $accommodation->cancellation_policy) === 'moderate' ? 'selected' : '' }}>{{ __('Moderate (Full refund 5 days prior)') }}</option>
                                    <option value="strict" {{ old('cancellation_policy', $accommodation->cancellation_policy) === 'strict' ? 'selected' : '' }}>{{ __('Strict (50% refund 1 week prior)') }}</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-muted dark:text-dark-subtle mb-1">{{ __('Minimum Stay (Nights)') }}</label>
                                <input type="number" name="minimum_stay" min="1" value="{{ old('minimum_stay', $accommodation->minimum_stay) }}" class="form-input w-full rounded-xl" required>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-muted dark:text-dark-subtle mb-1">{{ __('Maximum Stay (Nights)') }}</label>
                                <input type="number" name="maximum_stay" min="1" value="{{ old('maximum_stay', $accommodation->maximum_stay) }}" class="form-input w-full rounded-xl" required>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-warm dark:border-warm flex justify-end">
                            <button type="submit" class="btn btn-primary shadow-md">
                                {{ __('Save Configurations') }}
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Dynamic Rules List --}}
                <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-[#273244] rounded-2xl shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-warm dark:border-warm">
                        <h2 class="text-xl font-bold font-display text-muted dark:text-white">{{ __('Dynamic Pricing Rules') }}</h2>
                        <p class="text-muted text-sm mt-1">{{ __('Rules applying to specific dates or periods automatically adjust your base price.') }}</p>
                    </div>

                    <div class="divide-y divide-warm dark:divide-dark-border">
                        @forelse($rules as $rule)
                            <div class="p-4 flex items-center justify-between hover:bg-surface dark:hover:bg-surface/50 transition">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center bg-indigo-100 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400">
                                        @if($rule->type === 'weekend')
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                        @elseif($rule->type === 'seasonal')
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                        @else
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-muted dark:text-white capitalize">
                                            {{ __($rule->type) }} {{ __('Rule') }}
                                            <span class="ml-2 text-xs font-semibold px-2 py-0.5 rounded-full {{ $rule->price_modifier_value > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                                {{ $rule->price_modifier_value > 0 ? '+' : '' }}{{ $rule->price_modifier_value }}{{ $rule->price_modifier_type === 'percentage' ? '%' : ($accommodation->property->currency === 'USD' ? '$' : 'Br') }}
                                            </span>
                                        </p>
                                        <p class="text-xs text-muted mt-0.5">
                                            @if($rule->start_date && $rule->end_date)
                                                {{ \Carbon\Carbon::parse($rule->start_date)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($rule->end_date)->format('M d, Y') }}
                                            @else
                                                {{ __('Applies indefinitely based on rule type') }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <form action="{{ route('dashboard.accommodations.pricing.rules.destroy', [$accommodation, $rule]) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-muted hover:text-rose-500 transition-colors" onclick="return confirm('{{ __('Delete this rule?') }}');">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </form>
                            </div>
                        @empty
                            <div class="p-8 text-center text-muted text-sm">
                                {{ __('No dynamic pricing rules set.') }}
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>
            
            {{-- Right Column: Controls & Preview --}}
            <div class="space-y-6">
                
                {{-- Add Rule Form --}}
                <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-[#273244] rounded-2xl shadow-sm overflow-hidden p-6">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-muted mb-4">{{ __('Add Pricing Rule') }}</h3>
                    
                    <form action="{{ route('dashboard.accommodations.pricing.rules.store', $accommodation) }}" method="POST" class="space-y-4">
                        @csrf
                        
                        <div>
                            <label class="block text-sm font-semibold text-muted dark:text-dark-subtle mb-1">{{ __('Rule Type') }}</label>
                            <select name="type" class="form-input w-full rounded-xl">
                                <option value="seasonal">{{ __('Seasonal (Specific Dates)') }}</option>
                                <option value="weekend">{{ __('Weekend (Fri/Sat automatically)') }}</option>
                                <option value="special">{{ __('Special Event') }}</option>
                            </select>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-muted dark:text-dark-subtle mb-1">{{ __('Start Date') }}</label>
                                <input type="date" name="start_date" class="form-input w-full rounded-xl">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-muted dark:text-dark-subtle mb-1">{{ __('End Date') }}</label>
                                <input type="date" name="end_date" class="form-input w-full rounded-xl">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-muted dark:text-dark-subtle mb-1">{{ __('Adjustment Type') }}</label>
                                <select name="price_modifier_type" class="form-input w-full rounded-xl">
                                    <option value="percentage">{{ __('Percentage (%)') }}</option>
                                    <option value="fixed">{{ __('Fixed Amount') }}</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-muted dark:text-dark-subtle mb-1">{{ __('Value (+/-)') }}</label>
                                <input type="number" step="0.01" name="price_modifier_value" placeholder="{{ __('e.g. 15 or -10') }}" class="form-input w-full rounded-xl" required>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-full shadow-md mt-2">
                            {{ __('Add Rule') }}
                        </button>
                    </form>
                </div>

                {{-- Price Breakdown Preview (Mock) --}}
                <div class="bg-surface dark:bg-surface/30 border border-warm dark:border-[#273244] rounded-2xl p-6">
                    <h3 class="text-sm font-bold text-muted dark:text-white mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                        {{ __('Guest Price Preview') }}
                    </h3>
                    
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between text-muted dark:text-muted">
                            <span>{{ __('Base Rate x 3 nights') }}</span>
                            <span>{{ $accommodation->property->currency === 'USD' ? '$' : 'Br' }}{{ number_format($accommodation->property->price * 3, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-emerald-600 dark:text-emerald-400">
                            <span>{{ __('Weekly Discount (Applied)') }}</span>
                            <span>-{{ $accommodation->property->currency === 'USD' ? '$' : 'Br' }}{{ number_format(($accommodation->property->price * 3) * 0.10, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-muted dark:text-muted">
                            <span>{{ __('Cleaning Fee') }}</span>
                            <span>{{ $accommodation->property->currency === 'USD' ? '$' : 'Br' }}25.00</span>
                        </div>
                        <div class="flex justify-between text-muted dark:text-muted">
                            <span>{{ __('Service Fee') }}</span>
                            <span>{{ $accommodation->property->currency === 'USD' ? '$' : 'Br' }}15.00</span>
                        </div>
                        <div class="pt-2 mt-2 border-t border-warm dark:border-warm flex justify-between font-bold text-muted dark:text-white">
                            <span>{{ __('Total (Guest Pays)') }}</span>
                            <span>{{ $accommodation->property->currency === 'USD' ? '$' : 'Br' }}{{ number_format(($accommodation->property->price * 3) - (($accommodation->property->price * 3) * 0.10) + 40, 2) }}</span>
                        </div>
                    </div>
                </div>

            </div>
            
        </div>
    </div>
</x-layouts.dashboard>
