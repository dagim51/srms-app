<x-layouts.dashboard>
    <x-slot name="title">Listing Moderation Queue | Trust & Safety</x-slot>

    <x-slot:breadcrumb>
        <x-breadcrumb :items="[
            ['label' => __('Dashboard'), 'url' => '/dashboard'],
            ['label' => __('Moderation Queue')]
        ]" />
    </x-slot:breadcrumb>

    <div class="space-y-6 page-enter pb-16" x-data="{ quickRejectOpen: false, rejectUrl: '', rejectTitle: '', templateReason: '' }">
        {{-- Flash Messages --}}
        @if(session('success'))
            <x-alert type="success">
                {{ session('success') }}
            </x-alert>
        @endif
        @if(session('error'))
            <x-alert type="error">
                {{ session('error') }}
            </x-alert>
        @endif

        {{-- Standardized Page Header Card --}}
        <x-page-header
            title="Listing Moderation Queue"
            description="Review and verify property listings submitted for public discovery, legal compliance, and quality assurance."
            icon="shield"
            badge="Trust & Safety" />

        {{-- Top KPI Stat Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="cinematic-fade-up cinematic-stagger-1">
                <x-card-stat
                    :label="__('Pending Review')"
                    :value="$stats['in_queue']"
                    iconColor="blue"
                    icon="clock"
                    :badge="__('Queue')"
                    badgeVariant="info"
                    :href="route('dashboard.moderation.listings.index', ['status' => 'pending_review'])"
                    :class="$status === 'pending_review' ? 'border-sky-500 ring-2 ring-sky-500/20' : ''"
                />
            </div>
            <div class="cinematic-fade-up cinematic-stagger-2">
                <x-card-stat
                    :label="__('Approved / Live')"
                    :value="$stats['approved']"
                    iconColor="emerald"
                    icon="check"
                    :badge="__('Approved')"
                    badgeVariant="success"
                    :href="route('dashboard.moderation.listings.index', ['status' => 'published'])"
                    :class="in_array($status, ['published', 'approved']) ? 'border-emerald-500 ring-2 ring-emerald-500/20' : ''"
                />
            </div>
            <div class="cinematic-fade-up cinematic-stagger-3">
                <x-card-stat
                    :label="__('Rejected')"
                    :value="$stats['rejected']"
                    iconColor="rose"
                    icon="wrench"
                    :badge="__('Rejected')"
                    badgeVariant="error"
                    :href="route('dashboard.moderation.listings.index', ['status' => 'rejected'])"
                    :class="$status === 'rejected' ? 'border-rose-500 ring-2 ring-rose-500/20' : ''"
                />
            </div>
            <div class="cinematic-fade-up cinematic-stagger-4">
                <x-card-stat
                    :label="__('Suspended')"
                    :value="$stats['suspended']"
                    iconColor="amber"
                    icon="clock"
                    :badge="__('Suspended')"
                    badgeVariant="warning"
                    :href="route('dashboard.moderation.listings.index', ['status' => 'suspended'])"
                    :class="$status === 'suspended' ? 'border-amber-500 ring-2 ring-amber-500/20' : ''"
                />
            </div>
        </div>

        {{-- Filter Toolbar --}}
        <div class="cinematic-fade-up cinematic-stagger-2 card p-5 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="card-title text-base">{{ __('Trust & Safety Moderation Workstation') }}</h2>
                    <p class="card-subtitle text-xs mt-0.5">{{ __('Inspect commercial listings against authentic documentation and Ethiopian marketplace standards.') }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-primary-100 text-primary-800 dark:bg-primary-950/40 dark:text-primary-300">{{ __('Role: Moderator') }}</span>
                </div>
            </div>

            {{-- Filter Form --}}
            <form method="GET" action="{{ route('dashboard.moderation.listings.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 pt-3 border-t border-[#EBDBC9] dark:border-[#273244]">
                <div class="sm:col-span-5 relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search by title, property code, or purpose...') }}" class="form-input search-input w-full pl-9 pr-8 text-xs">
                    <svg class="w-4 h-4 text-muted absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    @if(request('search'))
                        <a href="{{ route('dashboard.moderation.listings.index', request()->except('search')) }}" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </a>
                    @endif
                </div>

                <div class="sm:col-span-3">
                    <select name="status" onchange="this.form.submit()" class="form-select w-full text-xs font-medium">
                        <option value="pending_review" {{ $status === 'pending_review' ? 'selected' : '' }}>Pending Review Queue ({{ $stats['in_queue'] }})</option>
                        <option value="published" {{ $status === 'published' ? 'selected' : '' }}>Approved / Published ({{ $stats['approved'] }})</option>
                        <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Rejected Submissions ({{ $stats['rejected'] }})</option>
                        <option value="suspended" {{ $status === 'suspended' ? 'selected' : '' }}>Suspended ({{ $stats['suspended'] }})</option>
                        <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All Statuses ({{ $stats['total'] }})</option>
                    </select>
                </div>

                <div class="sm:col-span-3">
                    <select name="purpose" onchange="this.form.submit()" class="form-select w-full text-xs">
                        <option value="all">{{ __('All Modes') }}</option>
                        <option value="For Sale" {{ request('purpose') === 'For Sale' ? 'selected' : '' }}>{{ __('For Sale') }}</option>
                        <option value="For Rent" {{ request('purpose') === 'For Rent' ? 'selected' : '' }}>{{ __('For Rent') }}</option>
                        <option value="Short Stay" {{ request('purpose') === 'Short Stay' ? 'selected' : '' }}>{{ __('Short Stay') }}</option>
                    </select>
                </div>

                <div class="sm:col-span-1 flex items-center justify-end">
                    @if(request()->hasAny(['search', 'purpose']) || ($status !== 'pending_review'))
                        <a href="{{ route('dashboard.moderation.listings.index') }}" class="p-2 text-muted hover:text-muted dark:hover:text-muted" title="Reset Filters">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <div class="cinematic-fade-up cinematic-stagger-3 card overflow-hidden">
            @if($listings->count() > 0)
                <div class="table-container">
                    <table class="data-table text-xs">
                        <thead>
                            <tr>
                                <th class="px-5 py-3.5">{{ __('Property Under Review') }}</th>
                                <th class="px-4 py-3.5">{{ __('Mode & Asking Price') }}</th>
                                <th class="px-4 py-3.5">{{ __('Quality Score') }}</th>
                                <th class="px-4 py-3.5">{{ __('Queue Status') }}</th>
                                <th class="px-4 py-3.5">{{ __('Submitted') }}</th>
                                <th class="px-5 py-3.5 text-right">{{ __('Moderator Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#EBDBC9]/50 dark:divide-[#273244]/60 text-muted dark:text-dark-subtle">
                            @foreach($listings as $listing)
                                @php
                                    $property = $listing->property;
                                    $cover = $property && $property->media->first() 
                                        ? ($property->media->first()->url ?? $property->media->first()->storage_key) 
                                        : 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=400&q=80';
                                @endphp
                                <tr class="hover:bg-surface/60 dark:hover:bg-surface/40 transition-colors">
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-3">
                                            <img src="{{ $cover }}" class="w-14 h-12 rounded-xl object-cover shadow-sm flex-shrink-0" alt="">
                                            <div>
                                                <a href="{{ route('dashboard.moderation.listings.show', $listing) }}" class="font-bold text-muted dark:text-white hover:text-primary-600 line-clamp-1">
                                                    {{ $property->title ?? 'Untitled Property' }}
                                                </a>
                                                <div class="flex items-center gap-2 mt-0.5">
                                                    <span class="text-xs font-mono text-muted">
                                                        {{ $property->property_code ?? 'PROP' }}
                                                    </span>
                                                    <span class="text-muted dark:text-muted">•</span>
                                                    <span class="text-xs text-muted">
                                                        {{ $property->bedrooms ?? 0 }} beds · {{ $property->building_area ?? 0 }} m²
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <p class="font-display font-extrabold text-muted dark:text-white">
                                            {{ $listing->formatted_price }}
                                        </p>
                                        <span class="inline-block mt-0.5 px-2 py-0.5 rounded text-xs font-bold bg-primary-50 text-primary-700 dark:bg-primary-950/40 dark:text-primary-300">
                                            {{ $listing->purpose }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4">
                                        <x-listing-quality :listing="$listing" compact />
                                    </td>
                                    <td class="px-4 py-4">
                                        @if($listing->status === 'pending_review')
                                            <x-badge variant="info" dot>{{ __('Pending Review') }}</x-badge>
                                        @elseif($listing->status === 'published' || $listing->status === 'approved')
                                            <x-badge variant="success">{{ __('Approved') }}</x-badge>
                                        @elseif($listing->status === 'rejected')
                                            <x-badge variant="error">{{ __('Rejected') }}</x-badge>
                                        @elseif($listing->status === 'suspended')
                                            <x-badge variant="neutral">{{ __('Suspended') }}</x-badge>
                                        @else
                                            <x-badge variant="neutral" class="capitalize">{{ $listing->status }}</x-badge>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 text-muted dark:text-muted">
                                        {{ $listing->updated_at->diffForHumans() }}
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <div class="inline-flex items-center gap-2">
                                            {{-- Detailed Review Button --}}
                                            <a href="{{ route('dashboard.moderation.listings.show', $listing) }}" class="btn btn-outline text-xs px-2.5 py-1 font-semibold" title="Inspect Listing">{{ __('Review') }}</a>

                                            {{-- Quick Approve (if in pending_review or rejected) --}}
                                            @if($listing->status !== 'published')
                                                <form method="POST" action="{{ route('dashboard.moderation.listings.decision', $listing) }}" onsubmit="return confirm('Approve and publish this listing to the public marketplace?')">
                                                    @csrf
                                                    <input type="hidden" name="action" value="approve">
                                                    <input type="hidden" name="comments" value="Approved via quick moderation. Meets marketplace requirements.">
                                                    <button type="submit" class="p-1.5 text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 rounded-lg transition-colors" title="Quick Approve">
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                                    </button>
                                                </form>
                                            @endif

                                            {{-- Quick Reject --}}
                                            @if($listing->status !== 'rejected')
                                                <button type="button" 
                                                        @click="quickRejectOpen = true; rejectUrl = '{{ route('dashboard.moderation.listings.decision', $listing) }}'; rejectTitle = '{{ addslashes($property->title ?? 'Listing') }}'"
                                                        class="p-1.5 text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg transition-colors" title="Quick Reject">
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <x-empty-state
                    icon="shield"
                    title="Queue is Clear!"
                    description="{{ __('There are no listings matching this filter status waiting for moderation review.') }}"
                />
            @endif
        </div>

        {{-- Pagination --}}
        @if($listings->hasPages())
            <div class="mt-4">
                {{ $listings->links() }}
            </div>
        @endif

        <div x-show="quickRejectOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
            <div @click.away="quickRejectOpen = false" class="modal-content max-w-lg w-full p-6 space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-950/40 text-rose-600 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-muted dark:text-white">{{ __('Reject Listing Submission') }}</h3>
                        <p class="text-xs text-muted dark:text-muted">{{ __('Rejection explanation will be shared directly with the property owner.') }}</p>
                    </div>
                </div>

                <form :action="rejectUrl" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="action" value="reject">

                    <div>
                        <label class="block text-xs font-semibold text-muted dark:text-dark-subtle mb-1.5">Common Rejection Reasons (Templates)</label>
                        <div class="flex flex-wrap gap-1.5">
                            <button type="button" @click="templateReason = 'Low resolution or insufficient photo gallery (minimum 3 high-res required)'" class="px-2.5 py-1 rounded-lg text-xs bg-surface-subtle dark:bg-surface hover:bg-surface-subtle text-muted dark:text-dark-subtle">
                                📷 Low photo quality
                            </button>
                            <button type="button" @click="templateReason = 'Asking price is unrealistic or missing currency clarity for stated area'" class="px-2.5 py-1 rounded-lg text-xs bg-surface-subtle dark:bg-surface hover:bg-surface-subtle text-muted dark:text-dark-subtle">
                                💰 Unclear pricing
                            </button>
                            <button type="button" @click="templateReason = 'Property description is incomplete or lacks key architectural highlights'" class="px-2.5 py-1 rounded-lg text-xs bg-surface-subtle dark:bg-surface hover:bg-surface-subtle text-muted dark:text-dark-subtle">
                                📝 Incomplete text
                            </button>
                            <button type="button" @click="templateReason = 'Suspected duplicate listing for an already registered property in this neighborhood'" class="px-2.5 py-1 rounded-lg text-xs bg-surface-subtle dark:bg-surface hover:bg-surface-subtle text-muted dark:text-dark-subtle">
                                ⚠️ Duplicate
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-muted dark:text-dark-subtle mb-1.5">{{ __('Rejection Feedback / Remediation Instructions') }}</label>
                        <textarea name="comments" x-model="templateReason" rows="3" class="form-textarea w-full text-xs" required placeholder="{{ __('Describe exactly what needs to be fixed...') }}"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" @click="quickRejectOpen = false" class="btn btn-ghost text-xs">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-danger text-xs font-bold px-4">{{ __('Confirm Rejection') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.dashboard>
