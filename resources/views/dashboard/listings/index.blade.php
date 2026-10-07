<x-layouts.dashboard>
    <x-slot name="title">Listing Management | Ethiopian Real Estate</x-slot>

    @section('page-title', 'Listings')

    <div class="space-y-6 page-enter" x-data="{ viewMode: 'grid', deleteModalOpen: false, deleteActionUrl: '', deleteTitle: '' }">
        {{-- Flash Messages --}}
        @if(session('success'))
            <x-alert type="success" class="mb-4">
                {{ session('success') }}
            </x-alert>
        @endif

        {{-- Top KPI Stat Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="bg-white dark:bg-[#1E293B] rounded-xl p-4 border border-gray-200 dark:border-gray-800 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Listings</p>
                    <p class="text-2xl font-display font-bold text-gray-900 dark:text-white mt-0.5">{{ $stats['total'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-600 dark:text-gray-300">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                </div>
            </div>

            <div class="bg-white dark:bg-[#1E293B] rounded-xl p-4 border border-gray-200 dark:border-gray-800 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-emerald-600 dark:text-emerald-400">Published</p>
                    <p class="text-2xl font-display font-bold text-gray-900 dark:text-white mt-0.5">{{ $stats['published'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
            </div>

            <div class="bg-white dark:bg-[#1E293B] rounded-xl p-4 border border-gray-200 dark:border-gray-800 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-amber-600 dark:text-amber-400">Drafts</p>
                    <p class="text-2xl font-display font-bold text-gray-900 dark:text-white mt-0.5">{{ $stats['draft'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-amber-50 dark:bg-amber-950/40 flex items-center justify-center text-amber-600 dark:text-amber-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                </div>
            </div>

            <div class="bg-white dark:bg-[#1E293B] rounded-xl p-4 border border-gray-200 dark:border-gray-800 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-blue-600 dark:text-blue-400">In Review</p>
                    <p class="text-2xl font-display font-bold text-gray-900 dark:text-white mt-0.5">{{ $stats['pending'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-950/40 flex items-center justify-center text-blue-600 dark:text-blue-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
            </div>

            <div class="bg-white dark:bg-[#1E293B] rounded-xl p-4 border border-gray-200 dark:border-gray-800 shadow-sm flex items-center justify-between col-span-2 sm:col-span-1">
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Paused</p>
                    <p class="text-2xl font-display font-bold text-gray-900 dark:text-white mt-0.5">{{ $stats['paused'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-500">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
            </div>
        </div>

        {{-- Actions & Filter Toolbar --}}
        <div class="bg-white dark:bg-[#1E293B] rounded-xl p-4 border border-gray-200 dark:border-gray-800 shadow-sm space-y-4">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Commercial Listings</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Marketplace offers linked directly to canonical properties</p>
                </div>
                <div class="flex items-center gap-2">
                    <div class="flex bg-gray-100 dark:bg-gray-800 p-0.5 rounded-lg border border-gray-200 dark:border-gray-700">
                        <button type="button" @click="viewMode = 'grid'" :class="{ 'bg-white dark:bg-[#1E293B] text-gray-900 dark:text-white shadow-sm': viewMode === 'grid', 'text-gray-500': viewMode !== 'grid' }" class="px-2.5 py-1.5 rounded-md text-xs font-medium transition-all" title="Grid View">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                        </button>
                        <button type="button" @click="viewMode = 'table'" :class="{ 'bg-white dark:bg-[#1E293B] text-gray-900 dark:text-white shadow-sm': viewMode === 'table', 'text-gray-500': viewMode !== 'table' }" class="px-2.5 py-1.5 rounded-md text-xs font-medium transition-all" title="Table View">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
                        </button>
                    </div>

                    <a href="{{ route('dashboard.listings.create') }}" class="btn btn-primary text-xs font-medium flex items-center gap-1.5 py-2 px-3.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        Create New Listing
                    </a>
                </div>
            </div>

            {{-- Filter Form --}}
            <form method="GET" action="{{ route('dashboard.listings.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-12 gap-3 pt-2 border-t border-gray-100 dark:border-gray-800">
                <div class="md:col-span-4 relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search title, code, purpose..." class="form-input w-full pl-9 text-xs">
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </div>

                <div class="md:col-span-3">
                    <select name="purpose" onchange="this.form.submit()" class="form-input w-full text-xs">
                        <option value="all">All Modes (Sale, Rent, Stay)</option>
                        <option value="For Sale" {{ request('purpose') == 'For Sale' ? 'selected' : '' }}>For Sale</option>
                        <option value="For Rent" {{ request('purpose') == 'For Rent' ? 'selected' : '' }}>For Rent</option>
                        <option value="Short Stay" {{ request('purpose') == 'Short Stay' ? 'selected' : '' }}>Short Stay</option>
                        <option value="Land" {{ request('purpose') == 'Land' ? 'selected' : '' }}>Land</option>
                    </select>
                </div>

                <div class="md:col-span-3">
                    <select name="status" onchange="this.form.submit()" class="form-input w-full text-xs">
                        <option value="all">All Statuses</option>
                        <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published (Active)</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="pending_review" {{ request('status') == 'pending_review' ? 'selected' : '' }}>Pending Review</option>
                        <option value="paused" {{ request('status') == 'paused' ? 'selected' : '' }}>Paused</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>

                <div class="md:col-span-2 flex items-center gap-2">
                    <select name="sort" onchange="this.form.submit()" class="form-input w-full text-xs">
                        <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Newest</option>
                        <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Price: High-Low</option>
                        <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Price: Low-High</option>
                    </select>
                    @if(request()->hasAny(['search', 'purpose', 'status', 'sort']))
                        <a href="{{ route('dashboard.listings.index') }}" class="p-2 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200" title="Reset Filters">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Listing Presentation: Grid Mode --}}
        <div x-show="viewMode === 'grid'">
            @if($listings->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($listings as $listing)
                        @php
                            $property = $listing->property;
                            $coverImg = $property && $property->media->first() 
                                ? ($property->media->first()->url ?? $property->media->first()->storage_key) 
                                : 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=800&q=80';
                        @endphp
                        <div class="bg-white dark:bg-[#1E293B] border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all flex flex-col group">
                            {{-- Image Banner & Badges --}}
                            <div class="relative aspect-video bg-gray-100 dark:bg-gray-800 overflow-hidden">
                                <img src="{{ $coverImg }}" alt="{{ $property->title ?? 'Property' }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                
                                {{-- Status Badge --}}
                                <div class="absolute top-3 left-3 flex items-center gap-1.5">
                                    @if($listing->status === 'published')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-500 text-white shadow-sm">
                                            <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                                            Published
                                        </span>
                                    @elseif($listing->status === 'draft')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-500 text-white shadow-sm">
                                            Draft
                                        </span>
                                    @elseif($listing->status === 'pending_review')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-blue-500 text-white shadow-sm">
                                            In Review
                                        </span>
                                    @elseif($listing->status === 'paused')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-gray-600 text-white shadow-sm">
                                            Paused
                                        </span>
                                    @elseif($listing->status === 'rejected')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-rose-500 text-white shadow-sm">
                                            Rejected
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-gray-500 text-white shadow-sm">
                                            {{ ucfirst($listing->status) }}
                                        </span>
                                    @endif

                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-black/60 backdrop-blur-sm text-white">
                                        {{ $listing->purpose }}
                                    </span>
                                </div>

                                {{-- Canonical Property Code Pill --}}
                                <div class="absolute bottom-3 right-3">
                                    <span class="px-2 py-0.5 rounded-md text-[11px] font-mono bg-black/70 backdrop-blur-sm text-gray-200">
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
                                    <p class="font-display font-extrabold text-gray-900 dark:text-white text-lg">
                                        {{ $listing->formatted_price }}
                                        @if(in_array($listing->purpose, ['For Rent', 'Short Stay']))
                                            <span class="text-xs font-normal text-gray-500">/{{ $listing->purpose === 'Short Stay' ? 'night' : 'mo' }}</span>
                                        @endif
                                    </p>
                                </div>

                                <h3 class="font-display font-bold text-gray-900 dark:text-white text-base line-clamp-1 group-hover:text-primary-600 transition-colors">
                                    <a href="{{ route('dashboard.listings.preview', $listing) }}">
                                        {{ $property->title ?? 'Untitled Property' }}
                                    </a>
                                </h3>

                                <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2 mt-1 mb-3">
                                    {{ $property->description ?? 'No description provided.' }}
                                </p>

                                {{-- Property Core Specs --}}
                                @if($property)
                                    <div class="flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400 mb-4 pb-3 border-b border-gray-100 dark:border-gray-800">
                                        @if($property->bedrooms)
                                            <span class="flex items-center gap-1" title="Bedrooms">
                                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                                                {{ $property->bedrooms }} Beds
                                            </span>
                                        @endif
                                        @if($property->bathrooms)
                                            <span class="flex items-center gap-1" title="Bathrooms">
                                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                                {{ $property->bathrooms }} Baths
                                            </span>
                                        @endif
                                        @if($property->building_area)
                                            <span class="flex items-center gap-1" title="Building Area">
                                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" /></svg>
                                                {{ $property->building_area }} m²
                                            </span>
                                        @endif
                                    </div>
                                @endif

                                {{-- Quality Score Meter & Action Footer --}}
                                <div class="mt-auto flex items-center justify-between pt-2">
                                    <x-listing-quality :listing="$listing" compact />

                                    <div class="flex items-center gap-1.5">
                                        {{-- Lifecycle Status / Rejection Explanation Link --}}
                                        <a href="{{ route('dashboard.listings.submission-status', $listing) }}" class="p-1.5 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors" title="View Submission Status & Lifecycle History">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                        </a>

                                        {{-- If Draft or Rejected: Submit for Review --}}
                                        @if(in_array($listing->status, ['draft', 'rejected']))
                                            <a href="{{ route('dashboard.listings.submit', $listing) }}" class="px-2 py-1 text-[11px] font-bold rounded {{ $listing->status === 'rejected' ? 'bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400 hover:bg-rose-100' : 'bg-primary-50 text-primary-600 dark:bg-primary-950/40 dark:text-primary-400 hover:bg-primary-100' }} transition-colors">
                                                {{ $listing->status === 'rejected' ? 'Fix & Submit' : 'Submit' }}
                                            </a>
                                        @elseif(in_array($listing->status, ['published', 'paused']))
                                            <form method="POST" action="{{ route('dashboard.listings.toggle-status', $listing) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors" title="{{ $listing->status === 'published' ? 'Pause Listing' : 'Publish Listing' }}">
                                                    @if($listing->status === 'published')
                                                        <svg class="w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                    @else
                                                        <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                    @endif
                                                </button>
                                            </form>
                                        @endif

                                        <a href="{{ route('dashboard.listings.preview', $listing) }}" class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors" title="Public Preview">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                        </a>

                                        <a href="{{ route('dashboard.listings.edit', $listing) }}" class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors" title="Edit Listing">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                        </a>

                                        <button type="button" @click="deleteModalOpen = true; deleteActionUrl = '{{ route('dashboard.listings.destroy', $listing) }}'; deleteTitle = '{{ addslashes($property->title ?? 'Listing') }}'" class="p-1.5 text-gray-400 hover:text-rose-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors" title="Delete Listing">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white dark:bg-[#1E293B] border border-gray-200 dark:border-gray-800 rounded-xl p-12 text-center">
                    <div class="w-16 h-16 mx-auto rounded-full bg-primary-50 dark:bg-primary-950/40 text-primary-600 dark:text-primary-400 flex items-center justify-center mb-4">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                    </div>
                    <h3 class="font-display font-bold text-gray-900 dark:text-white text-lg">No Listings Found</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 max-w-md mx-auto mt-1 mb-6">
                        No commercial listings match your current filters. Create a new listing to publish your property on the marketplace.
                    </p>
                    <a href="{{ route('dashboard.listings.create') }}" class="btn btn-primary text-sm">
                        Create Your First Listing
                    </a>
                </div>
            @endif
        </div>

        {{-- Listing Presentation: Table Mode --}}
        <div x-show="viewMode === 'table'" class="bg-white dark:bg-[#1E293B] border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm overflow-hidden">
            @if($listings->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-800/60 text-gray-500 dark:text-gray-400 font-semibold uppercase tracking-wider border-b border-gray-200 dark:border-gray-800">
                            <tr>
                                <th class="px-4 py-3">Property</th>
                                <th class="px-4 py-3">Mode</th>
                                <th class="px-4 py-3">Price</th>
                                <th class="px-4 py-3">Quality Score</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-700 dark:text-gray-300">
                            @foreach($listings as $listing)
                                @php
                                    $property = $listing->property;
                                    $coverImg = $property && $property->media->first() 
                                        ? ($property->media->first()->url ?? $property->media->first()->storage_key) 
                                        : 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=400&q=80';
                                @endphp
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/40 transition-colors">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <img src="{{ $coverImg }}" alt="" class="w-12 h-10 rounded-lg object-cover flex-shrink-0">
                                            <div>
                                                <a href="{{ route('dashboard.listings.preview', $listing) }}" class="font-semibold text-gray-900 dark:text-white hover:text-primary-600 transition-colors line-clamp-1">
                                                    {{ $property->title ?? 'Untitled Property' }}
                                                </a>
                                                <span class="text-[11px] font-mono text-gray-400">
                                                    {{ $property->property_code ?? 'PROP' }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 font-medium">
                                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                                            {{ $listing->purpose }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 font-display font-bold text-gray-900 dark:text-white">
                                        {{ $listing->formatted_price }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <x-listing-quality :listing="$listing" compact />
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($listing->status === 'published')
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-400">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Published
                                            </span>
                                        @elseif($listing->status === 'draft')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-950/40 dark:text-amber-400">
                                                Draft
                                            </span>
                                        @elseif($listing->status === 'pending_review')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-950/40 dark:text-blue-400">
                                                In Review
                                            </span>
                                        @elseif($listing->status === 'paused')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300">
                                                Paused
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 dark:bg-rose-950/40 dark:text-rose-400">
                                                {{ ucfirst($listing->status) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="inline-flex items-center gap-1.5">
                                            <a href="{{ route('dashboard.listings.submission-status', $listing) }}" class="p-1.5 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400" title="Lifecycle Status">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                            </a>
                                            <a href="{{ route('dashboard.listings.preview', $listing) }}" class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200" title="Preview">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                            </a>
                                            <a href="{{ route('dashboard.listings.edit', $listing) }}" class="p-1.5 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200" title="Edit">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                            </a>
                                            <button type="button" @click="deleteModalOpen = true; deleteActionUrl = '{{ route('dashboard.listings.destroy', $listing) }}'; deleteTitle = '{{ addslashes($property->title ?? 'Listing') }}'" class="p-1.5 text-gray-400 hover:text-rose-600" title="Delete">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
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
            <div @click.away="deleteModalOpen = false" class="bg-white dark:bg-[#1E293B] rounded-2xl max-w-md w-full p-6 shadow-xl border border-gray-200 dark:border-gray-800 space-y-4">
                <div class="w-12 h-12 rounded-full bg-rose-50 dark:bg-rose-950/40 text-rose-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Delete Listing?</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Are you sure you want to delete listing for "<span x-text="deleteTitle" class="font-semibold text-gray-700 dark:text-gray-300"></span>"? The canonical property will remain intact.
                    </p>
                </div>
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="deleteModalOpen = false" class="btn btn-ghost text-xs">Cancel</button>
                    <form :action="deleteActionUrl" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger text-xs">Confirm Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.dashboard>
