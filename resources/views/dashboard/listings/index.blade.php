<x-layouts.dashboard>
    <x-slot name="title">{{ __('Listing Management') }} | {{ __('Ethiopian Real Estate') }}</x-slot>

    @section('page-title', __('Listings'))

    <div class="space-y-6 page-enter" x-data="{ viewMode: 'grid', deleteModalOpen: false, deleteActionUrl: '', deleteTitle: '' }">
        {{-- Flash Messages --}}
        @if(session('success'))
            <x-alert type="success" class="mb-4">
                {{ session('success') }}
            </x-alert>
        @endif

        {{-- Standardized Page Header Card --}}
        <x-page-header
            title="Listings Management"
            description="Manage public market listings, sales pricing, rental rates, promotion tiers, and verification lifecycle states."
            icon="tag"
            badge="Listings">
            <x-slot:actions>
                <a href="{{ route('dashboard.listings.create') }}" class="btn btn-primary text-xs font-medium flex items-center gap-1.5 py-2 px-3.5 shadow-sm shadow-orange-500/20">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    {{ __('Create New Listing') }}
                </a>
            </x-slot:actions>
        </x-page-header>

        {{-- Top KPI Stat Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="cinematic-fade-up cinematic-stagger-1">
                <x-card-stat
                    :label="__('Total Listings')"
                    :value="$stats['total']"
                    iconColor="orange"
                    icon="tag"
                    :badge="__('Inventory')"
                    badgeVariant="brand"
                    caption="{{ __('All registered listings') }}"
                    href="{{ route('dashboard.listings.index') }}"
                />
            </div>
            <div class="cinematic-fade-up cinematic-stagger-2">
                <x-card-stat
                    :label="__('Published')"
                    :value="$stats['published']"
                    iconColor="emerald"
                    icon="check"
                    :badge="__('Active')"
                    badgeVariant="success"
                    caption="{{ __('Public on marketplace') }}"
                    href="{{ route('dashboard.listings.index', ['status' => 'published']) }}"
                />
            </div>
            <div class="cinematic-fade-up cinematic-stagger-3">
                <x-card-stat
                    :label="__('Drafts')"
                    :value="$stats['draft']"
                    iconColor="amber"
                    icon="document"
                    :badge="__('Draft')"
                    badgeVariant="warning"
                    caption="{{ __('Unpublished listings') }}"
                    href="{{ route('dashboard.listings.index', ['status' => 'draft']) }}"
                />
            </div>
            <div class="cinematic-fade-up cinematic-stagger-4">
                <x-card-stat
                    :label="__('In Review')"
                    :value="$stats['pending']"
                    iconColor="blue"
                    icon="clock"
                    :badge="__('Verification')"
                    badgeVariant="info"
                    caption="{{ __('Awaiting verification') }}"
                    href="{{ route('dashboard.listings.index', ['status' => 'pending']) }}"
                />
            </div>
            <div class="cinematic-fade-up cinematic-stagger-5">
                <x-card-stat
                    :label="__('Paused')"
                    :value="$stats['paused']"
                    iconColor="purple"
                    icon="chart-pie"
                    :badge="__('Paused')"
                    badgeVariant="neutral"
                    caption="{{ __('Temporarily suspended') }}"
                    href="{{ route('dashboard.listings.index', ['status' => 'paused']) }}"
                />
            </div>
        </div>

        {{-- Actions & Filter Toolbar --}}
        <div class="cinematic-fade-up cinematic-stagger-2 bg-white dark:bg-[#1E293B] rounded-xl p-4 border border-warm dark:border-warm shadow-sm space-y-4">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-muted dark:text-white">{{ __('Commercial Listings') }}</h2>
                    <p class="text-xs text-muted dark:text-muted">{{ __('Marketplace offers linked directly to canonical properties') }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <div class="flex bg-slate-100 dark:bg-slate-900 p-1 rounded-xl border border-slate-200 dark:border-slate-800">
                        <button type="button" @click="viewMode = 'grid'" :class="{ 'bg-[#C2410C] text-white shadow-md shadow-orange-500/25': viewMode === 'grid', 'text-slate-600 hover:text-[#C2410C] dark:text-slate-400 dark:hover:text-white': viewMode !== 'grid' }" class="p-2.5 rounded-lg text-xs font-medium transition-all min-h-[44px] min-w-[44px] flex items-center justify-center cursor-pointer" title="{{ __('Grid View') }}" aria-label="{{ __('Grid View') }}">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                        </button>
                        <button type="button" @click="viewMode = 'table'" :class="{ 'bg-[#C2410C] text-white shadow-md shadow-orange-500/25': viewMode === 'table', 'text-slate-600 hover:text-[#C2410C] dark:text-slate-400 dark:hover:text-white': viewMode !== 'table' }" class="p-2.5 rounded-lg text-xs font-medium transition-all min-h-[44px] min-w-[44px] flex items-center justify-center cursor-pointer" title="{{ __('Table View') }}" aria-label="{{ __('Table View') }}">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
                        </button>
                    </div>

                    <a href="{{ route('dashboard.listings.create') }}" class="btn btn-primary btn-sm flex items-center gap-1.5 shadow-sm shadow-orange-500/20">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        {{ __('Create New Listing') }}
                    </a>
                </div>
            </div>

            {{-- Filter Form --}}
            <form method="GET" action="{{ route('dashboard.listings.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-12 gap-3 pt-2 border-t border-warm dark:border-warm">
                <div class="md:col-span-4 relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search title, code, purpose...') }}" class="form-input search-input w-full pl-9 pr-8 text-xs">
                    <svg class="w-4 h-4 text-muted absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    @if(request('search'))
                        <a href="{{ route('dashboard.listings.index', request()->except('search')) }}" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </a>
                    @endif
                </div>

                <div class="md:col-span-3">
                    <select name="purpose" onchange="this.form.submit()" class="form-select w-full text-xs">
                        <option value="all">{{ __('All Modes (Sale, Rent, Stay)') }}</option>
                        <option value="For Sale" {{ request('purpose') == 'For Sale' ? 'selected' : '' }}>{{ __('For Sale') }}</option>
                        <option value="For Rent" {{ request('purpose') == 'For Rent' ? 'selected' : '' }}>{{ __('For Rent') }}</option>
                        <option value="Short Stay" {{ request('purpose') == 'Short Stay' ? 'selected' : '' }}>{{ __('Short Stay') }}</option>
                        <option value="Land" {{ request('purpose') == 'Land' ? 'selected' : '' }}>{{ __('Land') }}</option>
                    </select>
                </div>

                <div class="md:col-span-3">
                    <select name="status" onchange="this.form.submit()" class="form-select w-full text-xs">
                        <option value="all">{{ __('All Statuses') }}</option>
                        <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>{{ __('Published (Active)') }}</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>{{ __('Draft') }}</option>
                        <option value="pending_review" {{ request('status') == 'pending_review' ? 'selected' : '' }}>{{ __('Pending Review') }}</option>
                        <option value="paused" {{ request('status') == 'paused' ? 'selected' : '' }}>{{ __('Paused') }}</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>{{ __('Rejected') }}</option>
                    </select>
                </div>

                <div class="md:col-span-2 flex items-center gap-2">
                    <select name="sort" onchange="this.form.submit()" class="form-select w-full text-xs">
                        <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>{{ __('Newest') }}</option>
                        <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>{{ __('Price: High-Low') }}</option>
                        <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>{{ __('Price: Low-High') }}</option>
                    </select>
                    @if(request()->hasAny(['search', 'purpose', 'status', 'sort']))
                        <a href="{{ route('dashboard.listings.index') }}" class="p-2 text-muted hover:text-muted dark:hover:text-muted" title="{{ __('Reset Filters') }}">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Listing Presentation: Grid Mode --}}
        <div x-show="viewMode === 'grid'" class="cinematic-fade-up cinematic-stagger-3">
            @if($listings->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($listings as $listing)
                        @php
                            $property = $listing->property;
                            $coverImg = $property && $property->media->first() 
                                ? ($property->media->first()->url ?? $property->media->first()->storage_key) 
                                : 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=800&q=80';
                        @endphp
                        <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all flex flex-col group">
                            {{-- Image Banner & Badges --}}
                            <div class="relative aspect-video bg-surface-subtle dark:bg-surface overflow-hidden">
                                <img src="{{ $coverImg }}" alt="{{ $property->title ?? 'Property' }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                
                                {{-- Status Badge --}}
                                <div class="absolute top-3 left-3 flex items-center gap-1.5">
                                    @if($listing->status === 'published')
                                        <x-badge variant="success" dot :uppercase="false">{{ __('Published') }}</x-badge>
                                    @elseif($listing->status === 'draft')
                                        <x-badge variant="warning" :uppercase="false">{{ __('Draft') }}</x-badge>
                                    @elseif($listing->status === 'pending_review')
                                        <x-badge variant="info" :uppercase="false">{{ __('In Review') }}</x-badge>
                                    @elseif($listing->status === 'paused')
                                        <x-badge variant="neutral" :uppercase="false">{{ __('Paused') }}</x-badge>
                                    @elseif($listing->status === 'rejected')
                                        <x-badge variant="error" :uppercase="false">{{ __('Rejected') }}</x-badge>
                                    @else
                                        <x-badge variant="neutral" :uppercase="false">{{ __(ucfirst($listing->status)) }}</x-badge>
                                    @endif

                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-black/60 backdrop-blur-sm text-white">
                                        {{ __($listing->purpose) }}
                                    </span>
                                </div>

                                {{-- Canonical Property Code Pill --}}
                                <div class="absolute bottom-3 right-3">
                                    <span class="px-2 py-0.5 rounded-md text-xs font-mono bg-black/70 backdrop-blur-sm text-muted">
                                        {{ $property->property_code ?? 'PROP-CORE' }}
                                    </span>
                                </div>
                            </div>

                            {{-- Body --}}
                            <div class="p-5 flex-1 flex flex-col">
                                <div class="flex items-baseline justify-between mb-1">
                                    <span class="text-xs font-semibold uppercase tracking-wider text-primary-600 dark:text-primary-400">
                                        {{ $listing->purpose }}
                                    </span>
                                    <p class="font-display font-extrabold text-muted dark:text-white text-lg">
                                        {{ $listing->formatted_price }}
                                        @if(in_array($listing->purpose, ['For Rent', 'Short Stay']))
                                            <span class="text-xs font-normal text-muted">/{{ $listing->purpose === 'Short Stay' ? 'night' : 'mo' }}</span>
                                        @endif
                                    </p>
                                </div>

                                <h3 class="font-display font-bold text-muted dark:text-white text-base line-clamp-1 group-hover:text-primary-600 transition-colors">
                                    <a href="{{ route('dashboard.listings.preview', $listing) }}">
                                        {{ $property->title ?? 'Untitled Property' }}
                                    </a>
                                </h3>

                                <p class="text-xs text-muted dark:text-muted line-clamp-2 mt-1 mb-3">
                                    {{ $property->description ?? 'No description provided.' }}
                                </p>

                                {{-- Property Core Specs --}}
                                @if($property)
                                    <div class="flex items-center gap-3 text-xs text-muted dark:text-muted mb-4 pb-3 border-b border-warm dark:border-warm">
                                        @if($property->bedrooms)
                                            <span class="flex items-center gap-1" title="Bedrooms">
                                                <svg class="w-3.5 h-3.5 text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                                                {{ $property->bedrooms }} Beds
                                            </span>
                                        @endif
                                        @if($property->bathrooms)
                                            <span class="flex items-center gap-1" title="Bathrooms">
                                                <svg class="w-3.5 h-3.5 text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                                {{ $property->bathrooms }} Baths
                                            </span>
                                        @endif
                                        @if($property->building_area)
                                            <span class="flex items-center gap-1" title="Building Area">
                                                <svg class="w-3.5 h-3.5 text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" /></svg>
                                                {{ $property->building_area }} m²
                                            </span>
                                        @endif
                                    </div>
                                @endif

                                {{-- Quality Score Meter & Action Footer --}}
                                <div class="mt-auto flex items-center justify-between pt-2">
                                    <x-listing-quality :listing="$listing" compact />

                                    <div class="flex items-center gap-1.5">
                                        <a href="{{ route('dashboard.listings.submission-status', $listing) }}" class="p-1.5 text-muted hover:text-blue-600 dark:hover:text-blue-400 rounded-lg hover:bg-surface-subtle dark:hover:bg-surface transition-colors" title="View Submission Status & Lifecycle History">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                        </a>

                                        @if(in_array($listing->status, ['draft', 'rejected']))
                                            <a href="{{ route('dashboard.listings.submit', $listing) }}" class="px-2 py-1 text-xs font-bold rounded {{ $listing->status === 'rejected' ? 'bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400 hover:bg-rose-100' : 'bg-primary-50 text-primary-600 dark:bg-primary-950/40 dark:text-primary-400 hover:bg-primary-100' }} transition-colors">
                                                {{ $listing->status === 'rejected' ? 'Fix & Submit' : 'Submit' }}
                                            </a>
                                        @elseif(in_array($listing->status, ['published', 'paused']))
                                            <form method="POST" action="{{ route('dashboard.listings.toggle-status', $listing) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="p-1.5 text-muted hover:text-muted dark:hover:text-muted rounded-lg hover:bg-surface-subtle dark:hover:bg-surface transition-colors" title="{{ $listing->status === 'published' ? 'Pause Listing' : 'Publish Listing' }}">
                                                    @if($listing->status === 'published')
                                                        <svg class="w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                    @else
                                                        <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                    @endif
                                                </button>
                                            </form>
                                        @endif

                                        <a href="{{ route('dashboard.listings.preview', $listing) }}" class="p-1.5 text-muted hover:text-muted dark:hover:text-muted rounded-lg hover:bg-surface-subtle dark:hover:bg-surface transition-colors" title="Public Preview">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                        </a>

                                        <a href="{{ route('dashboard.listings.edit', $listing) }}" class="p-1.5 text-muted hover:text-muted dark:hover:text-muted rounded-lg hover:bg-surface-subtle dark:hover:bg-surface transition-colors" title="Edit Listing">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                        </a>

                                        <button type="button" @click="deleteModalOpen = true; deleteActionUrl = '{{ route('dashboard.listings.destroy', $listing) }}'; deleteTitle = '{{ addslashes($property->title ?? 'Listing') }}'" class="p-1.5 text-muted hover:text-rose-600 rounded-lg hover:bg-surface-subtle dark:hover:bg-surface transition-colors" title="Delete Listing">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <x-card padding="p-8">
                    <x-empty-state
                        icon="tag"
                        :title="__('No Listings Found')"
                        :description="__('No commercial listings match your current filters. Create a new listing to publish your property on the marketplace.')"
                        :action="route('dashboard.listings.create')"
                        :actionText="__('Create Your First Listing')"
                    />
                </x-card>
            @endif
        </div>

        {{-- Listing Presentation: Table Mode --}}
        <div x-show="viewMode === 'table'" class="table-container cinematic-fade-up cinematic-stagger-3">
            @if($listings->count() > 0)
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('Property') }}</th>
                            <th>{{ __('Mode') }}</th>
                            <th>{{ __('Price') }}</th>
                            <th>{{ __('Quality Score') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th class="text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($listings as $listing)
                            @php
                                $property = $listing->property;
                                $coverImg = $property && $property->media->first() 
                                    ? ($property->media->first()->url ?? $property->media->first()->storage_key) 
                                    : 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=400&q=80';
                            @endphp
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $coverImg }}" alt="" class="w-12 h-10 rounded-lg object-cover flex-shrink-0">
                                        <div>
                                            <a href="{{ route('dashboard.listings.preview', $listing) }}" class="font-semibold text-muted dark:text-white hover:text-primary-600 transition-colors line-clamp-1">
                                                {{ $property->title ?? 'Untitled Property' }}
                                            </a>
                                            <span class="text-xs font-mono text-muted">
                                                {{ $property->property_code ?? 'PROP' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="font-medium">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-surface-subtle dark:bg-surface text-muted dark:text-dark-subtle">
                                        {{ $listing->purpose }}
                                    </span>
                                </td>
                                <td class="font-display font-bold text-muted dark:text-white">
                                    {{ $listing->formatted_price }}
                                </td>
                                <td>
                                    <x-listing-quality :listing="$listing" compact />
                                </td>
                                <td>
                                    @if($listing->status === 'published')
                                        <x-badge variant="success" dot :uppercase="false">{{ __('Published') }}</x-badge>
                                    @elseif($listing->status === 'draft')
                                        <x-badge variant="warning" :uppercase="false">{{ __('Draft') }}</x-badge>
                                    @elseif($listing->status === 'pending_review')
                                        <x-badge variant="info" :uppercase="false">{{ __('In Review') }}</x-badge>
                                    @elseif($listing->status === 'paused')
                                        <x-badge variant="neutral" :uppercase="false">{{ __('Paused') }}</x-badge>
                                    @elseif($listing->status === 'rejected')
                                        <x-badge variant="error" :uppercase="false">{{ __('Rejected') }}</x-badge>
                                    @else
                                        <x-badge variant="neutral" :uppercase="false">{{ ucfirst($listing->status) }}</x-badge>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        <a href="{{ route('dashboard.listings.submission-status', $listing) }}" class="p-1.5 text-muted hover:text-blue-600 dark:hover:text-blue-400" title="Lifecycle Status">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                        </a>
                                        <a href="{{ route('dashboard.listings.preview', $listing) }}" class="p-1.5 text-muted hover:text-muted dark:hover:text-muted" title="Preview">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                        </a>
                                        <a href="{{ route('dashboard.listings.edit', $listing) }}" class="p-1.5 text-muted hover:text-muted dark:hover:text-muted" title="Edit">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                        </a>
                                        <button type="button" @click="deleteModalOpen = true; deleteActionUrl = '{{ route('dashboard.listings.destroy', $listing) }}'; deleteTitle = '{{ addslashes($property->title ?? 'Listing') }}'" class="p-1.5 text-muted hover:text-rose-600" title="Delete">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <x-card padding="p-8">
                    <x-empty-state
                        icon="tag"
                        :title="__('No Listings Found')"
                        :description="__('No commercial listings match your current filters.')"
                        :action="route('dashboard.listings.create')"
                        :actionText="__('Create Listing')"
                    />
                </x-card>
            @endif
        </div>

        {{-- Pagination --}}
        @if($listings->hasPages())
            <div class="mt-4">
                {{ $listings->links() }}
            </div>
        @endif

        {{-- Delete Confirmation Modal --}}
        <div x-show="deleteModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
            <div @click.away="deleteModalOpen = false" class="modal-content max-w-md w-full p-6 space-y-4">
                <div class="w-12 h-12 rounded-full bg-rose-50 dark:bg-rose-950/40 text-rose-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-muted dark:text-white">{{ __('Delete Listing?') }}</h3>
                    <p class="text-xs text-muted dark:text-muted mt-1">
                        Are you sure you want to delete listing for "<span x-text="deleteTitle" class="font-semibold text-muted dark:text-dark-subtle"></span>"? The canonical property will remain intact.
                    </p>
                </div>
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="deleteModalOpen = false" class="btn btn-ghost text-xs">{{ __('Cancel') }}</button>
                    <form :action="deleteActionUrl" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger text-xs">{{ __('Confirm Delete') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.dashboard>
