<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Listing Assistant') }}</x-slot>

    <div class="max-w-7xl mx-auto">
        <x-page-header
            :title="__('AI Listing Assistant')"
            :description="__('Generate compelling marketing copy, amenity highlights, and bilingual property descriptions in seconds.')"
            icon="sparkles"
            badge="AI Powered"
        />

        @include('frontend.ai.partials.tabs')

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            
            <!-- Input Form -->
            <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-3xl p-8 shadow-sm">
                <h2 class="text-xl font-bold text-muted dark:text-white mb-6">{{ __('Property Details') }}</h2>
                <form action="{{ route('dashboard.ai.assistant') }}" method="POST">
                    @csrf
                    <div class="space-y-5 mb-8">
                        <div>
                            <label class="block text-sm font-bold text-muted dark:text-dark-subtle mb-2">Key Features (comma separated)</label>
                            <input type="text" name="features" placeholder="{{ __('e.g. 4 beds, pool, renovated kitchen, city views') }}" class="input w-full" value="{{ request('features') }}" required>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-muted dark:text-dark-subtle mb-2">{{ __('Property Type') }}</label>
                                <select name="type" class="input w-full">
                                    <option>{{ __('Villa') }}</option>
                                    <option>{{ __('Apartment') }}</option>
                                    <option>{{ __('Commercial') }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-muted dark:text-dark-subtle mb-2">{{ __('Tone') }}</label>
                                <select name="tone" class="input w-full">
                                    <option>{{ __('Professional') }}</option>
                                    <option>{{ __('Enthusiastic') }}</option>
                                    <option>{{ __('Luxury') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-full flex items-center justify-center gap-2">
                        <svg class="w-5 h-5 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                        Generate Draft
                    </button>
                </form>
            </div>

            <!-- Output / Draft -->
            <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-3xl p-8 shadow-sm flex flex-col">
                <h2 class="text-xl font-bold text-muted dark:text-white mb-6 flex items-center justify-between">
                    Generated Draft
                    @if($draft)
                        <span class="badge badge-success bg-success-50 text-success-700 uppercase text-xs">{{ __('AI Generated') }}</span>
                    @endif
                </h2>
                
                @if($draft)
                    <div class="flex-1 flex flex-col">
                        <textarea class="input w-full flex-1 min-h-[10rem] resize-none text-muted dark:text-dark-subtle leading-relaxed font-medium p-4">{{ $draft }}</textarea>
                        
                        <div class="mt-6 flex flex-col sm:flex-row gap-3">
                            <button class="btn btn-primary flex-1">{{ __('Save Description') }}</button>
                            <button class="btn btn-secondary flex-1">{{ __('Copy to Clipboard') }}</button>
                        </div>
                        <p class="text-xs text-muted mt-4 text-center">{{ __('You can edit the AI draft manually before saving to ensure it meets your requirements.') }}</p>
                    </div>
                @else
                    <div class="flex-1 flex flex-col items-center justify-center text-center p-8 border-2 border-dashed border-warm dark:border-warm rounded-2xl bg-surface dark:bg-surface/50">
                        <div class="w-16 h-16 bg-surface-subtle dark:bg-surface rounded-full flex items-center justify-center text-muted mb-4">
                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                        </div>
                        <h3 class="font-bold text-muted dark:text-white">{{ __('No Draft Yet') }}</h3>
                        <p class="text-sm text-muted mt-1 max-w-xs">{{ __('Enter your property features and click generate to let the AI write your listing description.') }}</p>
                    </div>
                @endif
            </div>

        </div>

    </div>
</x-layouts.dashboard>
