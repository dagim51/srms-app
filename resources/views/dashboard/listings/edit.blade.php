<x-layouts.dashboard>
    <x-slot name="title">{{ __('Edit Listing') }} | {{ $listing->property->title ?? 'Listing' }}</x-slot>

    @section('page-title', __('Edit Listing'))
    @section('breadcrumb')
        <a href="{{ route('dashboard.listings.index') }}" class="hover:text-muted dark:hover:text-muted">{{ __('Listings') }}</a>
        <span class="mx-1.5 text-muted dark:text-muted">/</span>
        <span class="text-muted dark:text-dark-subtle">{{ __('Edit') }}</span>
    @endsection

    @php
        $property = $listing->property;
        $cover = $property && $property->media->first() 
            ? ($property->media->first()->url ?? $property->media->first()->storage_key) 
            : 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=800&q=80';
    @endphp

    <div class="max-w-5xl mx-auto page-enter pb-16 space-y-6">
        <x-page-header
            title="Edit Listing"
            :description="__('Update pricing models, lease tenures, and publishing status for') . ' ' . ($listing->property->title ?? 'this property') . '.'"
            icon="tag"
            badge="Listing Editor">
            <x-slot:actions>
                <div class="flex items-center gap-2">
                    <a href="{{ route('dashboard.listings.preview', $listing) }}" class="btn btn-secondary text-xs flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                        {{ __('Preview') }}
                    </a>
                    <a href="{{ route('dashboard.listings.index') }}" class="btn btn-ghost text-xs">
                        {{ __('Back to Listings') }}
                    </a>
                </div>
            </x-slot:actions>
        </x-page-header>

        {{-- Flash Messages --}}
        @if(session('success'))
            <x-alert type="success">
                {{ session('success') }}
            </x-alert>
        @endif

        {{-- Top Summary Bar --}}
        <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-2xl p-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <img src="{{ $cover }}" class="w-16 h-16 rounded-xl object-cover shadow-sm" alt="">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded text-xs font-mono bg-primary-100 text-primary-800 dark:bg-primary-950/40 dark:text-primary-300 font-bold">
                            {{ $property->property_code ?? 'PROP' }}
                        </span>
                        <h2 class="text-lg font-bold text-muted dark:text-white">{{ $property->title ?? __('Untitled Property') }}</h2>
                    </div>
                    <p class="text-xs text-muted dark:text-muted mt-1">
                        {{ __('Canonical asset created') }} {{ $property->created_at ? $property->created_at->diffForHumans() : __('recently') }}.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('dashboard.listings.preview', $listing) }}" class="btn btn-outline text-xs flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                    {{ __('Public Preview') }}
                </a>
                <a href="{{ route('dashboard.properties.media', $property) }}" target="_blank" class="btn btn-ghost text-xs flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    {{ __('Property Media') }} ({{ $property->media->count() }})
                </a>
            </div>
        </div>

        {{-- Listing Quality Score Widget --}}
        <x-listing-quality :listing="$listing" />

        {{-- Edit Form --}}
        <form action="{{ route('dashboard.listings.update', $listing) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-2xl p-6 shadow-sm space-y-6">
                <div class="border-b border-warm dark:border-warm pb-4">
                    <h3 class="font-display font-bold text-base text-muted dark:text-white">{{ __('Commercial Parameters') }}</h3>
                    <p class="text-xs text-muted dark:text-muted mt-0.5">{{ __('Update transaction mode, pricing, currency, and lifecycle state.') }}</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    {{-- Purpose / Mode --}}
                    <div>
                        <label class="block text-xs font-semibold text-muted dark:text-dark-subtle mb-1.5">{{ __('Listing Mode') }}</label>
                        <select name="purpose" class="form-input w-full text-xs" required>
                            <option value="For Sale" {{ $listing->purpose === 'For Sale' ? 'selected' : '' }}>{{ __('For Sale') }}</option>
                            <option value="For Rent" {{ $listing->purpose === 'For Rent' ? 'selected' : '' }}>{{ __('For Rent') }}</option>
                            <option value="Short Stay" {{ $listing->purpose === 'Short Stay' ? 'selected' : '' }}>{{ __('Short Stay') }} / {{ __('Vacation') }}</option>
                            <option value="Land" {{ $listing->purpose === 'Land' ? 'selected' : '' }}>{{ __('Land') }} / {{ __('Plot') }}</option>
                            <option value="Development" {{ $listing->purpose === 'Development' ? 'selected' : '' }}>{{ __('New Development') }}</option>
                        </select>
                    </div>

                    {{-- Currency --}}
                    <div>
                        <label class="block text-xs font-semibold text-muted dark:text-dark-subtle mb-1.5">{{ __('Currency') }}</label>
                        <select name="currency" class="form-input w-full text-xs" required>
                            <option value="ETB" {{ $listing->currency === 'ETB' ? 'selected' : '' }}>ETB - Ethiopian Birr</option>
                            <option value="USD" {{ $listing->currency === 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                            <option value="EUR" {{ $listing->currency === 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                        </select>
                    </div>

                    {{-- Price --}}
                    <div>
                        <label class="block text-xs font-semibold text-muted dark:text-dark-subtle mb-1.5">{{ __('Price Amount') }}</label>
                        <input type="number" step="1000" name="price" value="{{ old('price', $listing->price) }}" class="form-input w-full text-xs font-bold" required>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5 pt-4 border-t border-warm dark:border-warm">
                    {{-- Status --}}
                    <div>
                        <label class="block text-xs font-semibold text-muted dark:text-dark-subtle mb-1.5">{{ __('Lifecycle Status') }}</label>
                        <select name="status" class="form-input w-full text-xs font-medium" required>
                            <option value="draft" {{ $listing->status === 'draft' ? 'selected' : '' }}>{{ __('Draft') }}</option>
                            <option value="pending_review" {{ $listing->status === 'pending_review' ? 'selected' : '' }}>{{ __('Pending Review') }}</option>
                            <option value="published" {{ $listing->status === 'published' ? 'selected' : '' }}>{{ __('Published (Active)') }}</option>
                            <option value="paused" {{ $listing->status === 'paused' ? 'selected' : '' }}>{{ __('Paused') }}</option>
                            <option value="rejected" {{ $listing->status === 'rejected' ? 'selected' : '' }}>{{ __('Rejected') }}</option>
                            <option value="suspended" {{ $listing->status === 'suspended' ? 'selected' : '' }}>{{ __('Suspended') }}</option>
                            <option value="archived" {{ $listing->status === 'archived' ? 'selected' : '' }}>{{ __('Archived') }}</option>
                        </select>
                    </div>

                    {{-- Visibility --}}
                    <div>
                        <label class="block text-xs font-semibold text-muted dark:text-dark-subtle mb-1.5">{{ __('Marketplace Visibility') }}</label>
                        <select name="visibility" class="form-input w-full text-xs" required>
                            <option value="public" {{ $listing->visibility === 'public' ? 'selected' : '' }}>{{ __('Public (Searchable)') }}</option>
                            <option value="private" {{ $listing->visibility === 'private' ? 'selected' : '' }}>{{ __('Private') }}</option>
                            <option value="unlisted" {{ $listing->visibility === 'unlisted' ? 'selected' : '' }}>{{ __('Unlisted (Direct URL)') }}</option>
                        </select>
                    </div>

                    {{-- Expiry Date --}}
                    <div>
                        <label class="block text-xs font-semibold text-muted dark:text-dark-subtle mb-1.5">{{ __('Expiration Date') }}</label>
                        <input type="date" name="expires_at" value="{{ $listing->expires_at ? date('Y-m-d', strtotime($listing->expires_at)) : '' }}" class="form-input w-full text-xs">
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-between pt-2">
                <a href="{{ route('dashboard.listings.index') }}" class="btn btn-ghost text-xs">{{ __('Back to Listings') }}</a>
                <div class="flex items-center gap-3">
                    <button type="submit" class="btn btn-primary text-xs font-bold px-6">
                        {{ __('Save Changes') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-layouts.dashboard>
