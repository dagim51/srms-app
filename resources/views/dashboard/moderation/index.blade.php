<x-layouts.dashboard>
    <x-slot name="title">Listing Moderation Queue | Trust & Safety</x-slot>

    @section('page-title', 'Moderation Queue')
    @section('breadcrumb')
        <a href="/dashboard" class="hover:text-gray-700 dark:hover:text-gray-300">Dashboard</a>
        <span class="mx-1.5 text-gray-300 dark:text-gray-600">/</span>
        <span class="text-gray-700 dark:text-gray-300">Moderation Queue</span>
    @endsection

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

        {{-- Top KPI Stat Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <a href="{{ route('dashboard.moderation.listings.index', ['status' => 'pending_review']) }}" 
               class="bg-white dark:bg-[#1E293B] rounded-2xl p-4 border {{ $status === 'pending_review' ? 'border-primary-500 ring-2 ring-primary-500/20' : 'border-gray-200 dark:border-gray-800' }} shadow-sm flex items-center justify-between hover:shadow-md transition-all">
                <div>
                    <p class="text-xs font-semibold text-blue-600 dark:text-blue-400">Pending Review</p>
                    <p class="text-2xl font-display font-extrabold text-gray-900 dark:text-white mt-0.5">{{ $stats['in_queue'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                    <svg class="w-5 h-5 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
            </a>

            <a href="{{ route('dashboard.moderation.listings.index', ['status' => 'published']) }}" 
               class="bg-white dark:bg-[#1E293B] rounded-2xl p-4 border {{ in_array($status, ['published', 'approved']) ? 'border-emerald-500 ring-2 ring-emerald-500/20' : 'border-gray-200 dark:border-gray-800' }} shadow-sm flex items-center justify-between hover:shadow-md transition-all">
                <div>
                    <p class="text-xs font-semibold text-emerald-600 dark:text-emerald-400">Approved / Live</p>
                    <p class="text-2xl font-display font-extrabold text-gray-900 dark:text-white mt-0.5">{{ $stats['approved'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
            </a>

            <a href="{{ route('dashboard.moderation.listings.index', ['status' => 'rejected']) }}" 
               class="bg-white dark:bg-[#1E293B] rounded-2xl p-4 border {{ $status === 'rejected' ? 'border-rose-500 ring-2 ring-rose-500/20' : 'border-gray-200 dark:border-gray-800' }} shadow-sm flex items-center justify-between hover:shadow-md transition-all">
                <div>
                    <p class="text-xs font-semibold text-rose-600 dark:text-rose-400">Rejected</p>
                    <p class="text-2xl font-display font-extrabold text-gray-900 dark:text-white mt-0.5">{{ $stats['rejected'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
            </a>

            <a href="{{ route('dashboard.moderation.listings.index', ['status' => 'suspended']) }}" 
               class="bg-white dark:bg-[#1E293B] rounded-2xl p-4 border {{ $status === 'suspended' ? 'border-gray-500 ring-2 ring-gray-500/20' : 'border-gray-200 dark:border-gray-800' }} shadow-sm flex items-center justify-between hover:shadow-md transition-all">
                <div>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">Suspended</p>
                    <p class="text-2xl font-display font-extrabold text-gray-900 dark:text-white mt-0.5">{{ $stats['suspended'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-500 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                </div>
            </a>
        </div>

        {{-- Filter Toolbar --}}
        <div class="bg-white dark:bg-[#1E293B] border border-gray-200 dark:border-gray-800 rounded-2xl p-4 shadow-sm space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">Trust & Safety Moderation Workstation</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Inspect commercial listings against authentic documentation and Ethiopian marketplace standards.</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-primary-100 text-primary-800 dark:bg-primary-950/40 dark:text-primary-300">
                        Role: Moderator
                    </span>
                </div>
            </div>

            {{-- Filter Form --}}
            <form method="GET" action="{{ route('dashboard.moderation.listings.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 pt-2 border-t border-gray-100 dark:border-gray-800">
                <div class="sm:col-span-5 relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by title, property code, or purpose..." class="form-input w-full pl-9 text-xs">
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </div>

                <div class="sm:col-span-3">
                    <select name="status" onchange="this.form.submit()" class="form-input w-full text-xs font-medium">
                        <option value="pending_review" {{ $status === 'pending_review' ? 'selected' : '' }}>Pending Review Queue ({{ $stats['in_queue'] }})</option>
                        <option value="published" {{ $status === 'published' ? 'selected' : '' }}>Approved / Published ({{ $stats['approved'] }})</option>
                        <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Rejected Submissions ({{ $stats['rejected'] }})</option>
                        <option value="suspended" {{ $status === 'suspended' ? 'selected' : '' }}>Suspended ({{ $stats['suspended'] }})</option>
                        <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All Statuses ({{ $stats['total'] }})</option>
                    </select>
                </div>

                <div class="sm:col-span-3">
                    <select name="purpose" onchange="this.form.submit()" class="form-input w-full text-xs">
                        <option value="all">All Modes</option>
                        <option value="For Sale" {{ request('purpose') === 'For Sale' ? 'selected' : '' }}>For Sale</option>
                        <option value="For Rent" {{ request('purpose') === 'For Rent' ? 'selected' : '' }}>For Rent</option>
                        <option value="Short Stay" {{ request('purpose') === 'Short Stay' ? 'selected' : '' }}>Short Stay</option>
                    </select>
                </div>

                <div class="sm:col-span-1 flex items-center justify-end">
                    @if(request()->hasAny(['search', 'purpose']) || ($status !== 'pending_review'))
                        <a href="{{ route('dashboard.moderation.listings.index') }}" class="p-2 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200" title="Reset Filters">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Queue Table --}}
        <div class="bg-white dark:bg-[#1E293B] border border-gray-200 dark:border-gray-800 rounded-2xl shadow-sm overflow-hidden">
            @if($listings->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-800/60 text-gray-500 dark:text-gray-400 font-semibold uppercase tracking-wider border-b border-gray-200 dark:border-gray-800">
                            <tr>
                                <th class="px-5 py-3.5">Property Under Review</th>
                                <th class="px-4 py-3.5">Mode & Asking Price</th>
                                <th class="px-4 py-3.5">Quality Score</th>
                                <th class="px-4 py-3.5">Queue Status</th>
                                <th class="px-4 py-3.5">Submitted</th>
                                <th class="px-5 py-3.5 text-right">Moderator Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-700 dark:text-gray-300">
                            @foreach($listings as $listing)
                                @php
                                    $property = $listing->property;
                                    $cover = $property && $property->media->first() 
                                        ? ($property->media->first()->url ?? $property->media->first()->storage_key) 
                                        : 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=400&q=80';
                                @endphp
                                <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-800/40 transition-colors">
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-3">
                                            <img src="{{ $cover }}" class="w-14 h-12 rounded-xl object-cover shadow-sm flex-shrink-0" alt="">
                                            <div>
                                                <a href="{{ route('dashboard.moderation.listings.show', $listing) }}" class="font-bold text-gray-900 dark:text-white hover:text-primary-600 line-clamp-1">
                                                    {{ $property->title ?? 'Untitled Property' }}
                                                </a>
                                                <div class="flex items-center gap-2 mt-0.5">
                                                    <span class="text-[11px] font-mono text-gray-400">
                                                        {{ $property->property_code ?? 'PROP' }}
                                                    </span>
                                                    <span class="text-gray-300 dark:text-gray-700">•</span>
                                                    <span class="text-[11px] text-gray-400">
                                                        {{ $property->bedrooms ?? 0 }} beds · {{ $property->building_area ?? 0 }} m²
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <p class="font-display font-extrabold text-gray-900 dark:text-white">
                                            {{ $listing->formatted_price }}
                                        </p>
                                        <span class="inline-block mt-0.5 px-2 py-0.5 rounded text-[10px] font-bold bg-primary-50 text-primary-700 dark:bg-primary-950/40 dark:text-primary-300">
                                            {{ $listing->purpose }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4">
                                        <x-listing-quality :listing="$listing" compact />
                                    </td>
                                    <td class="px-4 py-4">
                                        @if($listing->status === 'pending_review')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 dark:bg-blue-950/40 dark:text-blue-400">
                                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                                                Pending Review
                                            </span>
                                        @elseif($listing->status === 'published' || $listing->status === 'approved')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-400">
                                                Approved
                                            </span>
                                        @elseif($listing->status === 'rejected')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/40 dark:text-rose-400">
                                                Rejected
                                            </span>
                                        @elseif($listing->status === 'suspended')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-gray-200 text-gray-800 dark:bg-gray-800 dark:text-gray-300">
                                                Suspended
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 capitalize">
                                                {{ $listing->status }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 text-gray-500 dark:text-gray-400">
                                        {{ $listing->updated_at->diffForHumans() }}
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <div class="inline-flex items-center gap-2">
                                            {{-- Detailed Review Button --}}
                                            <a href="{{ route('dashboard.moderation.listings.show', $listing) }}" class="btn btn-outline text-xs px-2.5 py-1 font-semibold" title="Inspect Listing">
                                                Review
                                            </a>

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
                <div class="p-12 text-center">
                    <div class="w-16 h-16 rounded-full bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    </div>
                    <h3 class="font-display font-bold text-gray-900 dark:text-white text-base">Queue is Clear!</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 max-w-sm mx-auto mt-1">
                        There are no listings matching this filter status waiting for moderation review.
                    </p>
                </div>
            @endif
        </div>

        {{-- Pagination --}}
        @if($listings->hasPages())
            <div class="mt-4">
                {{ $listings->links() }}
            </div>
        @endif

        {{-- Quick Reject Modal with Template Reasons --}}
        <div x-show="quickRejectOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
            <div @click.away="quickRejectOpen = false" class="bg-white dark:bg-[#1E293B] rounded-2xl max-w-lg w-full p-6 shadow-xl border border-gray-200 dark:border-gray-800 space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-950/40 text-rose-600 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">Reject Listing Submission</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Rejection explanation will be shared directly with the property owner.</p>
                    </div>
                </div>

                <form :action="rejectUrl" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="action" value="reject">

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Common Rejection Reasons (Templates)</label>
                        <div class="flex flex-wrap gap-1.5">
                            <button type="button" @click="templateReason = 'Low resolution or insufficient photo gallery (minimum 3 high-res required)'" class="px-2.5 py-1 rounded-lg text-[11px] bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 text-gray-700 dark:text-gray-300">
                                📷 Low photo quality
                            </button>
                            <button type="button" @click="templateReason = 'Asking price is unrealistic or missing currency clarity for stated area'" class="px-2.5 py-1 rounded-lg text-[11px] bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 text-gray-700 dark:text-gray-300">
                                💰 Unclear pricing
                            </button>
                            <button type="button" @click="templateReason = 'Property description is incomplete or lacks key architectural highlights'" class="px-2.5 py-1 rounded-lg text-[11px] bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 text-gray-700 dark:text-gray-300">
                                📝 Incomplete text
                            </button>
                            <button type="button" @click="templateReason = 'Suspected duplicate listing for an already registered property in this neighborhood'" class="px-2.5 py-1 rounded-lg text-[11px] bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 text-gray-700 dark:text-gray-300">
                                ⚠️ Duplicate
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Rejection Feedback / Remediation Instructions</label>
                        <textarea name="comments" x-model="templateReason" rows="3" class="form-input w-full text-xs" required placeholder="Describe exactly what needs to be fixed..."></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" @click="quickRejectOpen = false" class="btn btn-ghost text-xs">Cancel</button>
                        <button type="submit" class="btn btn-danger text-xs font-bold px-4">Confirm Rejection</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.dashboard>
