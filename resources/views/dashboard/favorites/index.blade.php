<x-layouts.dashboard>
    <x-slot name="title">{{ __('Saved Favorites') }} - {{ __('Ethiopian Real Estate Platform') }}</x-slot>

    <div class="space-y-6"
         x-data="{
             collectionModalOpen: false,
             selectedPropertyId: null,
             selectedPropertyTitle: '',
             selectedCollectionId: '',
             newCollectionName: '',
             newCollectionPrivate: true,
             creatingNew: false,
             toastMessage: '',
             toastOpen: false,

             openCollectionModal(propId, title) {
                 this.selectedPropertyId = propId;
                 this.selectedPropertyTitle = title;
                 this.collectionModalOpen = true;
             },

             showToast(msg) {
                 this.toastMessage = msg;
                 this.toastOpen = true;
                 setTimeout(() => this.toastOpen = false, 3500);
             },

             async removeFavorite(propId, cardEl) {
                 if (!confirm('{{ __('Remove this property from your saved favorites?') }}')) return;
                 try {
                     const res = await fetch('/favorites/toggle/' + propId, {
                         method: 'POST',
                         headers: {
                             'X-CSRF-TOKEN': '{{ csrf_token() }}',
                             'Accept': 'application/json',
                             'Content-Type': 'application/json'
                         }
                     });
                     const data = await res.json();
                     this.showToast(data.message || '{{ __('Removed from favorites.') }}');
                     cardEl.remove();
                     // Check if empty
                     if (document.querySelectorAll('.favorite-item-card').length === 0) {
                         window.location.reload();
                     }
                 } catch (e) {
                     alert('{{ __('Error updating favorites') }}');
                 }
             },

             async saveToCollection() {
                 if (this.creatingNew) {
                     if (!this.newCollectionName.trim()) {
                         alert('{{ __('Please enter a collection name') }}');
                         return;
                     }
                     // Create collection with initial property
                     const res = await fetch('/dashboard/collections', {
                         method: 'POST',
                         headers: {
                             'X-CSRF-TOKEN': '{{ csrf_token() }}',
                             'Accept': 'application/json',
                             'Content-Type': 'application/json'
                         },
                         body: JSON.stringify({
                             name: this.newCollectionName,
                             is_private: this.newCollectionPrivate,
                             property_id: this.selectedPropertyId
                         })
                     });
                     const data = await res.json();
                     this.collectionModalOpen = false;
                     this.showToast(data.message || '{{ __('Collection created and property saved.') }}');
                     setTimeout(() => window.location.reload(), 800);
                 } else {
                     if (!this.selectedCollectionId) {
                         alert('{{ __('Please select a collection') }}');
                         return;
                     }
                     // Add to existing collection
                     const res = await fetch('/dashboard/collections/' + this.selectedCollectionId + '/properties', {
                         method: 'POST',
                         headers: {
                             'X-CSRF-TOKEN': '{{ csrf_token() }}',
                             'Accept': 'application/json',
                             'Content-Type': 'application/json'
                         },
                         body: JSON.stringify({
                             property_id: this.selectedPropertyId
                         })
                     });
                     const data = await res.json();
                     this.collectionModalOpen = false;
                     this.showToast(data.message || '{{ __('Added to collection.') }}');
                 }
             }
         }">

        {{-- Toast Notification --}}
        <div x-show="toastOpen" 
             x-transition:enter="transition duration-[220ms] [transition-timing-function:cubic-bezier(0.23,1,0.32,1)]"
             x-transition:enter-start="opacity-0 translate-y-3 scale-[0.96]"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-[180ms]"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-2 scale-[0.98]"
             class="fixed bottom-6 right-6 z-50 bg-slate-900 dark:bg-slate-800 text-white px-5 py-3 rounded-2xl shadow-2xl flex items-center gap-3 border border-slate-700/80 text-sm font-medium"
             x-cloak>
            <div class="w-6 h-6 rounded-full bg-[#C2410C] flex items-center justify-center text-white flex-shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
            </div>
            <span x-text="toastMessage"></span>
        </div>

        {{-- Standardized Page Header Card --}}
        <x-page-header
            title="Saved Properties"
            description="Your personal catalog of bookmarked properties, custom watchlists, and price fluctuation tracking."
            icon="heart"
            badge="Favorites">
            <x-slot:actions>
                <a href="{{ route('dashboard.collections.index') }}" class="btn btn-secondary text-xs flex items-center gap-2" id="manage-collections-btn">
                    <svg class="w-4 h-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121 12v.75m-8.625-1.5v3.75m-3.75-3.75v3.75m6-3.75v3.75M3 18.75V18a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 18v.75" /></svg>
                    <span>{{ __('My Collections') }} ({{ $userCollections->count() }})</span>
                </a>
                <a href="{{ route('search') }}" class="btn btn-primary text-xs flex items-center gap-1.5 shadow-sm shadow-orange-500/20" id="search-more-favorites-btn">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                    <span>{{ __('Explore More') }}</span>
                </a>
            </x-slot:actions>
        </x-page-header>

        {{-- Search & Filter Controls Bar --}}
        <div class="card cinematic-fade-up cinematic-stagger-1 bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-2xl p-4 flex flex-col sm:flex-row items-center justify-between gap-3">
            <form action="{{ route('dashboard.favorites.index') }}" method="GET" class="flex-1 w-full flex flex-col sm:flex-row items-center gap-2.5">
                {{-- Search Input --}}
                <div class="relative flex-1 w-full">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-muted">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Filter saved favorites by title, location, code...') }}" class="form-input search-input w-full pl-9 pr-8 text-xs rounded-xl py-2">
                    @if(request('search'))
                        <a href="{{ route('dashboard.favorites.index', request()->except('search')) }}" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </a>
                    @endif
                </div>

                {{-- Purpose Filter --}}
                <div class="w-full sm:w-44">
                    <select name="purpose" onchange="this.form.submit()" class="form-select w-full text-xs rounded-xl py-2 font-medium">
                        <option value="all">{{ __('All Purposes') }}</option>
                        <option value="For Sale" {{ request('purpose') === 'For Sale' ? 'selected' : '' }}>{{ __('For Sale') }}</option>
                        <option value="For Rent" {{ request('purpose') === 'For Rent' ? 'selected' : '' }}>{{ __('For Rent') }}</option>
                        <option value="Short Stay" {{ request('purpose') === 'Short Stay' ? 'selected' : '' }}>{{ __('Short Stay') }}</option>
                        <option value="Land" {{ request('purpose') === 'Land' ? 'selected' : '' }}>{{ __('Land') }}</option>
                    </select>
                </div>

                {{-- Sort Filter --}}
                <div class="w-full sm:w-36">
                    <select name="sort" onchange="this.form.submit()" class="form-select w-full text-xs rounded-xl py-2 font-medium">
                        <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>{{ __('Recently Saved') }}</option>
                        <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>{{ __('Oldest Saved') }}</option>
                    </select>
                </div>
            </form>
        </div>

        {{-- FAVORITES LIST / GRID --}}
        @if($favorites->isEmpty())
            <x-empty-state
                icon="heart"
                :title="__('No Saved Favorites Yet')"
                :description="__('You haven\'t saved any properties to your favorites yet. Click the heart icon on any property in the marketplace or search engine to save it here for quick access.')"
                size="lg">
                <x-slot:actions>
                    <a href="{{ route('search') }}" class="btn btn-primary px-6 py-2.5 rounded-xl text-sm font-bold shadow-md shadow-orange-500/20">
                        {{ __('Explore Properties') }}
                    </a>
                    <a href="{{ route('properties.index') }}" class="btn btn-secondary px-6 py-2.5 rounded-xl text-sm font-bold">
                        {{ __('Browse Marketplace') }}
                    </a>
                </x-slot:actions>
            </x-empty-state>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 cinematic-fade-up cinematic-stagger-2" id="favorites-grid">
                @foreach($favorites as $fav)
                    @php
                        $prop = $fav->property;
                        $listing = $prop->listings->first();
                        $cover = $prop->media->first()?->url ?? $prop->media->first()?->storage_key ?? 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=800&q=80';
                        $priceFormatted = $listing ? $listing->formatted_price : 'ETB ' . number_format($prop->price ?? 0);
                        $purpose = $listing->purpose ?? 'For Sale';
                    @endphp

                    <div class="favorite-item-card card card-hover overflow-hidden flex flex-col justify-between"
                         id="favorite-card-{{ $prop->id }}">

                        <div>
                            {{-- Image Preview & Badges --}}
                            <div class="relative aspect-[16/10] overflow-hidden bg-surface-subtle dark:bg-surface">
                                <img src="{{ $cover }}" alt="{{ $prop->title }}" class="w-full h-full object-cover">
                                
                                <div class="absolute top-3 left-3 z-10 flex flex-wrap gap-1.5">
                                    <x-badge variant="brand" size="xs">
                                        {{ __($purpose) }}
                                    </x-badge>
                                    @if($prop->status === 'verified')
                                        <x-badge variant="success" size="xs">
                                            {{ __('Verified') }}
                                        </x-badge>
                                    @endif
                                </div>

                                {{-- Quick Remove Button --}}
                                <button type="button" 
                                        @click="removeFavorite('{{ $prop->id }}', $el.closest('.favorite-item-card'))"
                                        class="absolute top-3 right-3 z-10 w-8 h-8 rounded-full bg-black/60 hover:bg-rose-600 text-white flex items-center justify-center transition-all shadow-md"
                                        title="{{ __('Remove from favorites') }}">
                                    <svg class="w-4 h-4 fill-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>

                                <span class="absolute bottom-3 left-3 text-xs font-mono font-bold bg-black/70 backdrop-blur-sm text-muted px-2 py-0.5 rounded-md">
                                    {{ $prop->property_code }}
                                </span>
                            </div>

                            {{-- Card Details --}}
                            <div class="p-5 space-y-2">
                                <p class="text-xs font-semibold text-primary-600 dark:text-primary-400 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                                    <span>{{ __('Addis Ababa, Ethiopia') }}</span>
                                </p>

                                <h3 class="font-display font-bold text-muted dark:text-white text-base line-clamp-1 hover:text-primary-600 transition-colors">
                                    <a href="{{ route('properties.show', $listing ? $listing->id : $prop->id) }}">
                                        {{ $prop->title }}
                                    </a>
                                </h3>

                                <p class="font-display font-black text-muted dark:text-white text-lg text-primary-600 dark:text-primary-400">
                                    {{ $priceFormatted }}
                                </p>

                                {{-- Specs Pills --}}
                                <div class="flex items-center gap-3 text-xs text-muted dark:text-muted pt-2 border-t border-warm dark:border-warm">
                                    @if($prop->bedrooms) <span><strong>{{ $prop->bedrooms }}</strong> {{ __('Beds') }}</span> &bull; @endif
                                    @if($prop->bathrooms) <span><strong>{{ $prop->bathrooms }}</strong> {{ __('Baths') }}</span> &bull; @endif
                                    @if($prop->building_area) <span><strong>{{ $prop->building_area }}</strong> m²</span> @endif
                                </div>
                            </div>
                        </div>

                        {{-- Card Bottom Actions --}}
                        <div class="p-4 bg-surface dark:bg-surface/50 border-t border-warm dark:border-warm flex items-center justify-between gap-2">
                            {{-- Add / Move to Collection Button --}}
                            <button type="button" 
                                    @click="openCollectionModal('{{ $prop->id }}', '{{ addslashes($prop->title) }}')"
                                    class="text-xs font-bold text-primary-600 dark:text-primary-400 hover:text-primary-700 flex items-center gap-1.5 p-1 rounded-lg">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                <span>{{ __('Save to Collection') }}</span>
                            </button>

                            <a href="{{ route('properties.show', $listing ? $listing->id : $prop->id) }}" class="btn btn-primary btn-sm text-xs font-bold px-3 py-1.5 rounded-lg">
                                {{ __('View') }}
                            </a>
                        </div>

                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-8 pt-4 border-t border-warm dark:border-warm flex justify-center">
                {{ $favorites->links() }}
            </div>
        @endif

        {{-- SAVE TO COLLECTION MODAL --}}
        <div x-show="collectionModalOpen" 
             class="fixed inset-0 z-50 overflow-y-auto" 
             aria-labelledby="collection-modal-title" 
             role="dialog" 
             aria-modal="true"
             x-cloak>
            
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="collectionModalOpen" 
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     @click="collectionModalOpen = false"
                     class="fixed inset-0 bg-surface/60 backdrop-blur-xs transition-opacity"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="collectionModalOpen"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="modal-content inline-block align-bottom text-left transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
                    
                    <div class="p-6 space-y-5">
                        <div class="flex items-center justify-between pb-3 border-b border-warm dark:border-warm">
                            <div>
                                <h3 class="font-display font-bold text-lg text-muted dark:text-white" id="collection-modal-title">
                                    {{ __('Save to Collection') }}
                                </h3>
                                <p class="text-xs text-muted dark:text-muted truncate max-w-xs mt-0.5" x-text="selectedPropertyTitle"></p>
                            </div>
                            <button @click="collectionModalOpen = false" class="p-1 rounded-lg text-muted hover:text-muted">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>

                        {{-- Mode Selector: Existing vs New Collection --}}
                        <div class="flex items-center gap-2 p-1 bg-surface-subtle dark:bg-surface rounded-xl">
                            <button type="button" 
                                    @click="creatingNew = false"
                                    :class="!creatingNew ? 'bg-white dark:bg-surface text-muted dark:text-white shadow-sm font-bold' : 'text-muted hover:text-muted'"
                                    class="flex-1 py-1.5 text-xs rounded-lg transition-all">
                                {{ __('Existing Collections') }}
                            </button>
                            <button type="button" 
                                    @click="creatingNew = true"
                                    :class="creatingNew ? 'bg-white dark:bg-surface text-muted dark:text-white shadow-sm font-bold' : 'text-muted hover:text-muted'"
                                    class="flex-1 py-1.5 text-xs rounded-lg transition-all">
                                {{ __('+ New Collection') }}
                            </button>
                        </div>

                        {{-- 1. Select Existing Collection --}}
                        <div x-show="!creatingNew" class="space-y-3">
                            @if($userCollections->isEmpty())
                                <div class="text-center py-6 text-muted text-xs">
                                    <p>{{ __("You don't have any collections yet.") }}</p>
                                    <button type="button" @click="creatingNew = true" class="mt-2 text-primary-600 font-bold underline">
                                        {{ __('Create your first collection') }}
                                    </button>
                                </div>
                            @else
                                <label class="form-label text-xs font-semibold">{{ __('Choose Collection') }}</label>
                                <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                                    @foreach($userCollections as $col)
                                        <label class="flex items-center justify-between p-3 rounded-xl border border-warm dark:border-warm cursor-pointer hover:bg-surface dark:hover:bg-surface/60 transition-colors has-checked:border-primary-500 has-checked:bg-orange-50/40 dark:has-checked:bg-orange-950/20">
                                            <div class="flex items-center gap-2.5">
                                                <input type="radio" name="target_collection" value="{{ $col->id }}" x-model="selectedCollectionId" class="form-radio">
                                                <span class="text-xs font-bold text-muted dark:text-muted">{{ $col->name }}</span>
                                            </div>
                                            <span class="text-xs text-muted font-medium">
                                                {{ $col->properties_count }} {{ __('properties') }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        {{-- 2. Create New Collection Form --}}
                        <div x-show="creatingNew" class="space-y-4">
                            <div>
                                <label class="form-label text-xs font-semibold">{{ __('Collection Name') }}</label>
                                <input type="text" x-model="newCollectionName" placeholder="{{ __('e.g. Bole Villas, Vacation Stays, Investment Plots...') }}" class="form-input text-xs rounded-xl">
                            </div>

                            <label class="flex items-center justify-between p-3 rounded-xl border border-warm dark:border-warm cursor-pointer hover:bg-surface dark:hover:bg-surface">
                                <div>
                                    <p class="text-xs font-bold text-muted dark:text-white">{{ __('Private Collection') }}</p>
                                    <p class="text-xs text-muted">{{ __('Only you can view and modify this collection') }}</p>
                                </div>
                                <input type="checkbox" x-model="newCollectionPrivate" class="form-checkbox">
                            </label>
                        </div>

                        {{-- Actions --}}
                        <div class="pt-4 border-t border-warm dark:border-warm flex items-center justify-end gap-2">
                            <button type="button" @click="collectionModalOpen = false" class="btn btn-secondary text-xs px-4 py-2 rounded-xl font-medium">
                                {{ __('Cancel') }}
                            </button>
                            <button type="button" @click="saveToCollection()" class="btn btn-primary text-xs px-5 py-2 rounded-xl font-bold shadow-sm">
                                {{ __('Save to Collection') }}
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
