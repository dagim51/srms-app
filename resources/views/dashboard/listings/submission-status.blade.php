<x-layouts.dashboard>
    <x-slot name="title">{{ __('Listing Lifecycle Status') }} | {{ $listing->property->title ?? 'Listing' }}</x-slot>

    @section('page-title', __('Submission Status'))
    @section('breadcrumb')
        <a href="{{ route('dashboard.listings.index') }}" class="hover:text-muted dark:hover:text-muted">{{ __('Listings') }}</a>
        <span class="mx-1.5 text-muted dark:text-muted">/</span>
        <span class="text-muted dark:text-dark-subtle">{{ __('Lifecycle Status') }}</span>
    @endsection

    @php
        $property = $listing->property;
        $cover = $property && $property->media->first() 
            ? ($property->media->first()->url ?? $property->media->first()->storage_key) 
            : 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=800&q=80';
        
        $currentStatus = $listing->status;
        $history = $listing->moderations;
    @endphp

    <div class="max-w-4xl mx-auto page-enter pb-16 space-y-6">
        {{-- Flash Messages --}}
        @if(session('success'))
            <x-alert type="success">
                {{ session('success') }}
            </x-alert>
        @endif

        {{-- Top Summary Header --}}
        <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-2xl p-6 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
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
                    {{ __('Marketplace Preview') }}
                </a>
                <a href="{{ route('dashboard.listings.edit', $listing) }}" class="btn btn-ghost text-xs">
                    {{ __('Edit Listing') }}
                </a>
            </div>
        </div>

        {{-- Visual Lifecycle Progress Stepper --}}
        <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-2xl p-6 shadow-sm">
            <h3 class="font-display font-bold text-base text-muted dark:text-white mb-6">{{ __('Listing Lifecycle Progression') }}</h3>
            
            @php
                $steps = [
                    ['key' => 'draft', 'label' => __('1. Draft Created'), 'desc' => __('Saved locally')],
                    ['key' => 'pending_review', 'label' => __('2. Submitted'), 'desc' => __('In moderation queue')],
                    ['key' => 'validated', 'label' => __('3. Validation'), 'desc' => __('Automated checks passed')],
                    ['key' => 'approved', 'label' => __('4. Decision & Approved'), 'desc' => __('Moderator sign-off')],
                    ['key' => 'published', 'label' => __('5. Published'), 'desc' => __('Live on marketplace')],
                ];

                // Determine active step index
                $statusOrder = [
                    'draft' => 0,
                    'pending_review' => 1,
                    'approved' => 3,
                    'published' => 4,
                    'rejected' => 1, // stuck at review
                    'suspended' => 4,
                ];
                $activeIndex = $statusOrder[$currentStatus] ?? 0;
            @endphp

            <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 relative">
                @foreach($steps as $idx => $st)
                    @php
                        $isComplete = $idx <= $activeIndex && $currentStatus !== 'rejected';
                        $isCurrent = $idx === $activeIndex;
                        $isRejectedStep = $currentStatus === 'rejected' && $idx === 1;
                    @endphp
                    <div class="p-3.5 rounded-xl border flex flex-col justify-between 
                        {{ $isRejectedStep ? 'bg-rose-50 border-rose-200 dark:bg-rose-950/20 dark:border-rose-900/40' : 
                           ($isComplete ? 'bg-emerald-50 border-emerald-200 dark:bg-emerald-950/20 dark:border-emerald-900/40' : 
                           ($isCurrent ? 'bg-primary-50 border-primary-200 dark:bg-primary-950/20 dark:border-primary-900/40' : 'bg-surface border-warm dark:bg-surface/40 dark:border-warm')) }}">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-mono font-bold text-muted">#0{{ $idx + 1 }}</span>
                                @if($isRejectedStep)
                                    <span class="w-4 h-4 rounded-full bg-rose-500 text-white flex items-center justify-center text-xs font-bold">✕</span>
                                @elseif($isComplete)
                                    <span class="w-4 h-4 rounded-full bg-emerald-500 text-white flex items-center justify-center text-xs font-bold">✓</span>
                                @elseif($isCurrent)
                                    <span class="w-4 h-4 rounded-full bg-primary-500 text-white flex items-center justify-center text-xs font-bold animate-pulse">•</span>
                                @else
                                    <span class="w-4 h-4 rounded-full bg-surface-subtle dark:bg-surface text-white flex items-center justify-center text-xs font-bold"></span>
                                @endif
                            </div>
                            <h4 class="font-bold text-xs {{ $isRejectedStep ? 'text-rose-900 dark:text-rose-300' : ($isComplete ? 'text-emerald-900 dark:text-emerald-300' : 'text-muted dark:text-white') }}">
                                {{ $st['label'] }}
                            </h4>
                        </div>
                        <p class="text-xs text-muted dark:text-muted mt-2">{{ $st['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Dynamic Status State Cards --}}
        @if($currentStatus === 'rejected')
            <div class="bg-rose-50 dark:bg-rose-950/20 border-2 border-rose-300 dark:border-rose-800 rounded-2xl p-6 shadow-sm space-y-4">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-xl bg-rose-500 text-white flex items-center justify-center flex-shrink-0 shadow-md">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-600 text-white">{{ __('Action Required: Listing Rejected') }}</span>
                            @if($latestModeration)
                                <span class="text-xs text-rose-700 dark:text-rose-300">{{ __('Reviewed') }} {{ $latestModeration->created_at->diffForHumans() }}</span>
                            @endif
                        </div>
                        <h3 class="text-base font-bold text-rose-900 dark:text-rose-100 mt-1">
                            {{ __('Moderator Feedback & Rejection Reason') }}
                        </h3>
                        <div class="mt-3 bg-white dark:bg-[#1E293B] border border-rose-200 dark:border-rose-900/60 rounded-xl p-4 text-xs text-muted dark:text-muted leading-relaxed shadow-sm font-medium">
                            {{ $latestModeration->comments ?? __('Listing details did not satisfy trust and verification standards. Please review photos, pricing, or description.') }}
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-t border-rose-200 dark:border-rose-900/40 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <p class="text-xs text-rose-700 dark:text-rose-300">
                        {{ __('Revise the required elements and resubmit for priority review.') }}
                    </p>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('dashboard.listings.edit', $listing) }}" class="btn btn-outline text-xs bg-white text-rose-700 border-rose-300 hover:bg-rose-100">
                            {{ __('Edit Listing Fields') }}
                        </a>
                        <a href="{{ route('dashboard.listings.submit', $listing) }}" class="btn btn-primary text-xs font-bold">
                            {{ __('Resubmit for Review') }}
                        </a>
                    </div>
                </div>
            </div>

        @elseif($currentStatus === 'pending_review')
            <div class="bg-blue-50 dark:bg-blue-950/20 border border-blue-200 dark:border-blue-900/40 rounded-2xl p-6 shadow-sm space-y-4">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-500 text-white flex items-center justify-center flex-shrink-0 shadow-md">
                        <svg class="w-6 h-6 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-600 text-white">{{ __('Under Committee Review') }}</span>
                            <span class="text-xs text-blue-700 dark:text-blue-300">{{ __('Queue Position') }} #03</span>
                        </div>
                        <h3 class="text-base font-bold text-blue-900 dark:text-blue-100 mt-1">
                            {{ __('Your listing is currently in the Trust & Safety Moderation Queue') }}
                        </h3>
                        <p class="text-xs text-blue-700 dark:text-blue-300 mt-1 leading-relaxed">
                            {{ __('Our team verifies title integrity, photographic authenticity, and pricing compliance. Most submissions are processed within 2–6 business hours. You will receive an automated notification as soon as a decision is recorded.') }}
                        </p>
                    </div>
                </div>
            </div>

        @elseif($currentStatus === 'published' || $currentStatus === 'approved')
            <div class="bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-900/40 rounded-2xl p-6 shadow-sm space-y-4">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-xl bg-emerald-500 text-white flex items-center justify-center flex-shrink-0 shadow-md">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-600 text-white">{{ __('Active & Published') }}</span>
                            <span class="text-xs text-emerald-700 dark:text-emerald-300">{{ __('Publicly discoverable') }}</span>
                        </div>
                        <h3 class="text-base font-bold text-emerald-900 dark:text-emerald-100 mt-1">
                            {{ __('Listing is live on the Ethiopian Real Estate Marketplace') }}
                        </h3>
                        <p class="text-xs text-emerald-700 dark:text-emerald-300 mt-1 leading-relaxed">
                            {{ __('Your property is active in search results, GIS maps, and public discovery collections. Inquiries and viewing requests will appear in your messages inbox.') }}
                        </p>
                    </div>
                </div>
            </div>

        @elseif($currentStatus === 'draft')
            <div class="bg-amber-50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-900/40 rounded-2xl p-6 shadow-sm flex items-center justify-between gap-4">
                <div>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-500 text-white">{{ __('Draft State') }}</span>
                    <h3 class="text-base font-bold text-amber-900 dark:text-amber-100 mt-1">{{ __('Listing has not been submitted for review yet') }}</h3>
                    <p class="text-xs text-amber-700 dark:text-amber-300 mt-0.5">{{ __('Submit your listing to the moderation committee when you are ready to publish.') }}</p>
                </div>
                <a href="{{ route('dashboard.listings.submit', $listing) }}" class="btn btn-primary text-xs font-bold">
                    {{ __('Submit Listing Now') }}
                </a>
            </div>
        @endif

        {{-- Moderation Audit Trail & Event Timeline --}}
        <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-2xl p-6 shadow-sm space-y-4">
            <h3 class="font-display font-bold text-base text-muted dark:text-white">{{ __('Moderation & Audit Event History') }}</h3>
            <p class="text-xs text-muted dark:text-muted">{{ __('Complete immutable record of all lifecycle events for compliance and trust.') }}</p>

            @if($history->count() > 0)
                <div class="space-y-3 pt-2">
                    @foreach($history as $ev)
                        <div class="p-3.5 rounded-xl border border-warm dark:border-warm bg-surface/50 dark:bg-surface/30 flex items-start justify-between gap-4 text-xs">
                            <div class="flex items-start gap-3">
                                @if($ev->action === 'approve')
                                    <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 flex items-center justify-center font-bold text-xs">✓</span>
                                @elseif($ev->action === 'reject')
                                    <span class="w-6 h-6 rounded-full bg-rose-100 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 flex items-center justify-center font-bold text-xs">✕</span>
                                @elseif($ev->action === 'suspend')
                                    <span class="w-6 h-6 rounded-full bg-surface-subtle text-muted dark:bg-surface dark:text-dark-subtle flex items-center justify-center font-bold text-xs">⏸</span>
                                @else
                                    <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300 flex items-center justify-center font-bold text-xs">ℹ</span>
                                @endif

                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-muted dark:text-white capitalize">{{ $ev->action }}d</span>
                                        <span class="text-muted">•</span>
                                        <span class="text-muted dark:text-muted">{{ $ev->created_at->format('M d, Y H:i') }} ({{ $ev->created_at->diffForHumans() }})</span>
                                    </div>
                                    @if($ev->comments)
                                        <p class="text-muted dark:text-dark-subtle mt-1 leading-relaxed">{{ $ev->comments }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-6 text-center text-xs text-muted border border-dashed border-warm dark:border-warm rounded-xl">
                    {{ __('No moderation events recorded yet.') }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.dashboard>
