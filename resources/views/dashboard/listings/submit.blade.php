<x-layouts.dashboard>
    <x-slot name="title">{{ __('Submit Listing for Review') }} | {{ $listing->property->title ?? 'Listing' }}</x-slot>

    @section('page-title', __('Submit Listing'))
    @section('breadcrumb')
        <a href="{{ route('dashboard.listings.index') }}" class="hover:text-muted dark:hover:text-muted">{{ __('Listings') }}</a>
        <span class="mx-1.5 text-muted dark:text-muted">/</span>
        <a href="{{ route('dashboard.listings.submission-status', $listing) }}" class="hover:text-muted dark:hover:text-muted">{{ __('Status') }}</a>
        <span class="mx-1.5 text-muted dark:text-muted">/</span>
        <span class="text-muted dark:text-dark-subtle">{{ __('Submit') }}</span>
    @endsection

    @php
        $property = $listing->property;
        $cover = $property && $property->media->first() 
            ? ($property->media->first()->url ?? $property->media->first()->storage_key) 
            : 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=800&q=80';
    @endphp

    <div class="max-w-4xl mx-auto page-enter pb-16 space-y-6">
        <x-page-header
            title="Submit Listing for Review"
            :description="__('Perform pre-flight checks and submit') . ' ' . ($listing->property->title ?? 'this property') . ' ' . __('for compliance and marketplace publication.')"
            icon="shield"
            badge="Moderation" />

        {{-- Flash Messages --}}
        @if(session('error'))
            <x-alert type="error">
                {{ session('error') }}
            </x-alert>
        @endif

        {{-- Top Summary Header --}}
        <div class="card bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-2xl p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <img src="{{ $cover }}" class="w-16 h-16 rounded-xl object-cover shadow-sm flex-shrink-0" alt="">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded text-xs font-mono bg-primary-100 text-primary-800 dark:bg-primary-950/40 dark:text-primary-300 font-bold">
                            {{ $property->property_code ?? 'PROP' }}
                        </span>
                        <h2 class="text-lg font-bold text-muted dark:text-white">{{ $property->title ?? __('Untitled Property') }}</h2>
                    </div>
                    <p class="text-xs text-muted dark:text-muted mt-1">
                        {{ $listing->purpose }} • {{ __('Asking') }} <strong class="text-muted dark:text-muted">{{ $listing->formatted_price }}</strong>
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('dashboard.listings.preview', $listing) }}" target="_blank" class="btn btn-outline text-xs">
                    {{ __('Preview Card') }}
                </a>
                <a href="{{ route('dashboard.listings.edit', $listing) }}" class="btn btn-ghost text-xs">
                    {{ __('Edit Details') }}
                </a>
            </div>
        </div>

        {{-- Pre-Submission Automated Validation Results --}}
        <div class="card bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-2xl p-6 space-y-5">
            <div class="flex items-center justify-between border-b border-warm dark:border-warm pb-4">
                <div>
                    <h3 class="font-display font-bold text-base text-muted dark:text-white">{{ __('Pre-Submission Validation Check') }}</h3>
                    <p class="text-xs text-muted dark:text-muted mt-0.5">{{ __('Automated checks verify data integrity before moderation queue entry.') }}</p>
                </div>
                @if($validationChecks['all_passed'])
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-400">
                        <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        {{ __('All Mandatory Checks Passed') }}
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/40 dark:text-rose-400">
                        <svg class="w-4 h-4 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        {{ __('Action Required Before Submission') }}
                    </span>
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
                @foreach($validationChecks['rules'] as $rule)
                    <div class="p-3.5 rounded-xl border flex items-start gap-3 {{ $rule['passed'] ? 'bg-emerald-50/40 border-emerald-100 dark:bg-emerald-950/20 dark:border-emerald-900/30' : ($rule['required'] ? 'bg-rose-50/40 border-rose-100 dark:bg-rose-950/20 dark:border-rose-900/30' : 'bg-surface border-warm dark:bg-surface/40 dark:border-warm') }}">
                        @if($rule['passed'])
                            <div class="w-5 h-5 rounded-full bg-emerald-500 text-white flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            </div>
                        @else
                            <div class="w-5 h-5 rounded-full {{ $rule['required'] ? 'bg-rose-500' : 'bg-amber-500' }} text-white flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                            </div>
                        @endif

                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <span class="font-bold {{ $rule['passed'] ? 'text-muted dark:text-white' : ($rule['required'] ? 'text-rose-900 dark:text-rose-300' : 'text-muted dark:text-dark-subtle') }}">
                                    {{ $rule['label'] }}
                                </span>
                                @if($rule['required'])
                                    <span class="text-xs font-semibold uppercase tracking-wider text-rose-500">{{ __('Mandatory') }}</span>
                                @else
                                    <span class="text-xs font-semibold uppercase tracking-wider text-muted">{{ __('Recommended') }}</span>
                                @endif
                            </div>
                            <p class="text-xs text-muted dark:text-muted mt-0.5 leading-relaxed">
                                {{ $rule['message'] }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Submission Form --}}
        <form action="{{ route('dashboard.listings.submit.process', $listing) }}" method="POST" class="space-y-6" x-data="{ agreed: false }">
            @csrf

            <div class="card bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-2xl p-6 space-y-5">
                <div class="border-b border-warm dark:border-warm pb-4">
                    <h3 class="font-display font-bold text-base text-muted dark:text-white">{{ __('Submitter Remarks & Compliance') }}</h3>
                    <p class="text-xs text-muted dark:text-muted mt-0.5">{{ __('Provide optional comments or title deed details for the moderation committee.') }}</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-muted dark:text-dark-subtle mb-1.5">
                        {{ __('Notes for Moderator (Optional)') }}
                    </label>
                    <textarea name="comments" rows="3" class="form-input w-full text-xs leading-relaxed" 
                              placeholder="{{ __('e.g. Master title deed verified; all high resolution interior photos attached; price is firm as per owner contract.') }}"></textarea>
                </div>

                <div class="pt-3 border-t border-warm dark:border-warm">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" x-model="agreed" class="mt-0.5 rounded text-primary-600 focus:ring-primary-500">
                        <span class="text-xs text-muted dark:text-dark-subtle leading-relaxed">
                            {{ __('I certify that all property specifications, pricing, and photographic media submitted here are authentic and compliant with the Ethiopian Real Estate Marketplace Code of Conduct.') }}
                        </span>
                    </label>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center justify-between pt-2">
                <a href="{{ route('dashboard.listings.index') }}" class="btn btn-ghost text-xs">
                    {{ __('Cancel & Return to Listings') }}
                </a>

                <button type="submit" 
                        :disabled="!agreed || {{ $validationChecks['all_passed'] ? 'false' : 'true' }}"
                        :class="{ 'opacity-50 cursor-not-allowed': !agreed || {{ $validationChecks['all_passed'] ? 'false' : 'true' }} }"
                        class="btn btn-primary text-xs font-bold px-6 py-2.5 flex items-center gap-2 shadow-md">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    {{ __('Submit for Marketplace Moderation') }}
                </button>
            </div>
        </form>
    </div>
</x-layouts.dashboard>
