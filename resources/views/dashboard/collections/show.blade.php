<x-layouts.dashboard>
    <x-slot name="title">{{ $collection->name }} - Collections</x-slot>

    <div class="space-y-6 page-enter"
         x-data="{
             moveModalOpen: false,
             editModalOpen: false,
             selectedPropertyId: null,
             selectedPropertyTitle: '',
             targetCollectionId: '',
             toastMessage: '',
             toastOpen: false,

             showToast(msg) {
                 this.toastMessage = msg;
                 this.toastOpen = true;
                 setTimeout(() => this.toastOpen = false, 3500);
             },

             openMoveModal(propId, title) {
                 this.selectedPropertyId = propId;
                 this.selectedPropertyTitle = title;
                 this.moveModalOpen = true;
             },

             async removeProperty(propId, cardEl) {
                 if (!confirm('Remove this property from the collection?')) return;
                 try {
                     const res = await fetch('/dashboard/collections/{{ $collection->id }}/properties/' + propId, {
                         method: 'DELETE',
                         headers: {
                             'X-CSRF-TOKEN': '{{ csrf_token() }}',
                             'Accept': 'application/json',
                             'Content-Type': 'application/json'
                         }
                     });
                     const data = await res.json();
                     this.showToast(data.message || 'Removed from collection.');
                     cardEl.remove();
                     if (document.querySelectorAll('.collection-item-card').length === 0) {
                         window.location.reload();
                     }
                 } catch (e) {
                     alert('Error removing property');
                 }
             },

             async submitMove() {
                 if (!this.targetCollectionId) {
                     alert('Please select a target collection');
                     return;
                 }
                 try {
                     const res = await fetch('/dashboard/collections/{{ $collection->id }}/properties/' + this.selectedPropertyId + '/move', {
                         method: 'POST',
                         headers: {
                             'X-CSRF-TOKEN': '{{ csrf_token() }}',
                             'Accept': 'application/json',
                             'Content-Type': 'application/json'
                         },
                         body: JSON.stringify({
                             target_collection_id: this.targetCollectionId
                         })
                     });
                     const data = await res.json();
                     this.moveModalOpen = false;
                     this.showToast(data.message || 'Property moved.');
                     setTimeout(() => window.location.reload(), 700);
                 } catch (e) {
                     alert('Error moving property');
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
             class="fixed bottom-6 right-6 z-50 bg-surface text-white px-5 py-3 rounded-2xl shadow-2xl flex items-center gap-3 border border-warm text-sm font-medium"
             x-cloak>
            <div class="w-6 h-6 rounded-full bg-primary-500 flex items-center justify-center text-white flex-shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
            </div>
            <span x-text="toastMessage"></span>
        </div>

        {{-- Standardized Page Header Card --}}
        <x-page-header
            :title="$collection->name"
            :description="__('Curated property portfolio containing') . ' ' . $collection->properties->count() . ' ' . Str::plural('property', $collection->properties->count()) . ' ' . __('for private review or collaborative sharing.')"
            icon="folder"
            badge="Collection">
            <x-slot:actions>
                <div class="flex items-center gap-2">
                    @if($isOwner)
                        <button type="button" 
                                @click="editModalOpen = true"
                                class="btn btn-secondary text-xs flex items-center gap-1.5"
                                id="edit-collection-btn">
                            <svg class="w-4 h-4 text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" /></svg>
                            <span>{{ __('Edit') }}</span>
                        </button>
                    @endif
                    <a href="{{ route('search') }}" class="btn btn-primary text-xs flex items-center gap-1.5 shadow-sm" id="collection-add-more-btn">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        <span>{{ __('Add Properties') }}</span>
                    </a>
                </div>
            </x-slot:actions>
        </x-page-header>

        {{-- PROPERTIES IN THIS COLLECTION --}}
        @if($collection->properties->isEmpty())
            {{-- EMPTY COLLECTION STATE --}}
            <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-3xl p-12 text-center max-w-xl mx-auto my-8 shadow-sm">
                <div class="w-20 h-20 rounded-full bg-orange-100 dark:bg-orange-950/40 text-primary-600 dark:text-primary-400 mx-auto flex items-center justify-center mb-5">
                    <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                </div>

                <h3 class="font-display font-extrabold text-2xl text-muted dark:text-white mb-2">{{ __('This Collection is Empty') }}</h3>

                <p class="text-sm text-muted dark:text-muted mb-6 max-w-md mx-auto leading-relaxed">
                    You haven't added any properties to <span class="font-bold text-muted dark:text-white">"{{ $collection->name }}"</span> yet. Save properties from your favorites or browse the marketplace.
                </p>

                <div class="flex flex-wrap items-center justify-center gap-3">
                    <a href="{{ route('dashboard.favorites.index') }}" class="btn btn-primary px-6 py-2.5 rounded-xl text-sm font-bold shadow-md shadow-orange-500/20">{{ __('Select from Favorites') }}</a>
                    <a href="{{ route('search') }}" class="btn btn-secondary px-6 py-2.5 rounded-xl text-sm font-bold">{{ __('Search Marketplace') }}</a>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="collection-items-grid">
                @foreach($collection->properties as $item)
                    @php
                        $prop = $item->property;
                        $listing = $prop?->listings->first();
                        $cover = $prop?->media->first()?->url ?? $prop?->media->first()?->storage_key ?? 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=800&q=80';
                        $priceFormatted = $listing ? $listing->formatted_price : 'ETB ' . number_format($prop?->price ?? 0);
                        $purpose = $listing->purpose ?? 'For Sale';
                    @endphp

                    @if($prop)
                        <div class="collection-item-card bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-2xl overflow-hidden shadow-sm hover:shadow-lg transition-all duration-200 flex flex-col justify-between"
                             id="col-property-{{ $prop->id }}">

                            <div>
                                {{-- Thumbnail & Badges --}}
                                <div class="relative aspect-[16/10] overflow-hidden bg-surface-subtle dark:bg-surface">
                                    <img src="{{ $cover }}" alt="{{ $prop->title }}" class="w-full h-full object-cover">
                                    
                                    <div class="absolute top-3 left-3 z-10 flex flex-wrap gap-1.5">
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold shadow-sm bg-orange-600 text-white">
                                            {{ $purpose }}
                                        </span>
                                        @if($prop->status === 'verified')
                                            <span class="px-2 py-0.5 rounded-full text-xs font-bold shadow-sm bg-emerald-600 text-white">{{ __('Verified') }}</span>
                                        @endif
                                    </div>

                                    @if($isOwner)
                                        {{-- Remove from Collection Button --}}
                                        <button type="button" 
                                                @click="removeProperty('{{ $prop->id }}', $el.closest('.collection-item-card'))"
                                                class="absolute top-3 right-3 z-10 w-8 h-8 rounded-full bg-black/60 hover:bg-rose-600 text-white flex items-center justify-center transition-all shadow-md"
                                                title="Remove from collection">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    @endif

                                    <span class="absolute bottom-3 left-3 text-xs font-mono font-bold bg-black/70 backdrop-blur-sm text-muted px-2 py-0.5 rounded-md">
                                        {{ $prop->property_code }}
                                    </span>
                                </div>

                                {{-- Details --}}
                                <div class="p-5 space-y-2">
                                    <p class="text-xs font-semibold text-primary-600 dark:text-primary-400 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
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

                                    {{-- Specs --}}
                                    <div class="flex items-center gap-3 text-xs text-muted dark:text-muted pt-2 border-t border-warm dark:border-warm">
                                        @if($prop->bedrooms) <span><strong>{{ $prop->bedrooms }}</strong> Beds</span> &bull; @endif
                                        @if($prop->bathrooms) <span><strong>{{ $prop->bathrooms }}</strong> Baths</span> &bull; @endif
                                        @if($prop->building_area) <span><strong>{{ $prop->building_area }}</strong> m²</span> @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="p-4 bg-surface dark:bg-surface/50 border-t border-warm dark:border-warm flex items-center justify-between gap-2">
                                @if($isOwner && $otherCollections->count() > 0)
                                    <button type="button" 
                                            @click="openMoveModal('{{ $prop->id }}', '{{ addslashes($prop->title) }}')"
                                            class="text-xs font-bold text-muted dark:text-dark-subtle hover:text-primary-600 flex items-center gap-1.5 p-1 rounded-lg">
                                        <svg class="w-4 h-4 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" /></svg>
                                        <span>{{ __('Move to...') }}</span>
                                    </button>
                                @else
                                    <div></div>
                                @endif

                                <a href="{{ route('properties.show', $listing ? $listing->id : $prop->id) }}" class="btn btn-primary btn-sm text-xs font-bold px-3 py-1.5 rounded-lg">
                                    View
                                </a>
                            </div>

                        </div>
                    @endif
                @endforeach
            </div>
        @endif

        {{-- MOVE TO ANOTHER COLLECTION MODAL --}}
        <div x-show="moveModalOpen" 
             class="fixed inset-0 z-50 overflow-y-auto" 
             aria-labelledby="move-modal-title" 
             role="dialog" 
             aria-modal="true"
             x-cloak>
            
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="moveModalOpen" 
                     @click="moveModalOpen = false"
                     class="fixed inset-0 bg-surface/60 backdrop-blur-xs transition-opacity"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="moveModalOpen"
                     class="inline-block align-bottom bg-white dark:bg-[#1E293B] rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-warm dark:border-warm p-6 space-y-5">
                    
                    <div class="flex items-center justify-between pb-3 border-b border-warm dark:border-warm">
                        <div>
                            <h3 class="font-display font-bold text-lg text-muted dark:text-white" id="move-modal-title">{{ __('Move Property') }}</h3>
                            <p class="text-xs text-muted dark:text-muted truncate max-w-xs mt-0.5" x-text="selectedPropertyTitle"></p>
                        </div>
                        <button @click="moveModalOpen = false" class="p-1 rounded-lg text-muted hover:text-muted">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <div class="space-y-3">
                        <label class="form-label text-xs font-semibold">{{ __('Select Destination Collection') }}</label>
                        <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                            @foreach($otherCollections as $oCol)
                                <label class="flex items-center justify-between p-3 rounded-xl border border-warm dark:border-warm cursor-pointer hover:bg-surface dark:hover:bg-surface/60 transition-colors has-checked:border-primary-500 has-checked:bg-orange-50/40 dark:has-checked:bg-orange-950/20">
                                    <div class="flex items-center gap-2.5">
                                        <input type="radio" name="dest_col" value="{{ $oCol->id }}" x-model="targetCollectionId" class="text-primary-500 focus:ring-primary-500">
                                        <span class="text-xs font-bold text-muted dark:text-muted">{{ $oCol->name }}</span>
                                    </div>
                                    <span class="text-xs text-muted">
                                        {{ $oCol->is_private ? 'Private' : 'Shared' }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="pt-4 border-t border-warm dark:border-warm flex items-center justify-end gap-2">
                        <button type="button" @click="moveModalOpen = false" class="btn btn-secondary text-xs px-4 py-2 rounded-xl font-medium">{{ __('Cancel') }}</button>
                        <button type="button" @click="submitMove()" class="btn btn-primary text-xs px-5 py-2 rounded-xl font-bold shadow-sm" id="confirm-move-property-btn">{{ __('Move Property') }}</button>
                    </div>

                </div>
            </div>
        </div>

        {{-- EDIT COLLECTION MODAL --}}
        @if($isOwner)
            <div x-show="editModalOpen" 
                 class="fixed inset-0 z-50 overflow-y-auto" 
                 aria-labelledby="edit-current-col-title" 
                 role="dialog" 
                 aria-modal="true"
                 x-cloak>
                
                <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div x-show="editModalOpen" 
                         @click="editModalOpen = false"
                         class="fixed inset-0 bg-surface/60 backdrop-blur-xs transition-opacity"></div>

                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <div x-show="editModalOpen"
                         class="inline-block align-bottom bg-white dark:bg-[#1E293B] rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-warm dark:border-warm">
                        
                        <form action="{{ route('dashboard.collections.update', $collection->id) }}" method="POST" class="p-6 space-y-5">
                            @csrf
                            @method('PUT')
                            <div class="flex items-center justify-between pb-3 border-b border-warm dark:border-warm">
                                <h3 class="font-display font-bold text-lg text-muted dark:text-white" id="edit-current-col-title">{{ __('Edit Collection Details') }}</h3>
                                <button type="button" @click="editModalOpen = false" class="p-1 rounded-lg text-muted hover:text-muted">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <label class="form-label text-xs font-semibold">{{ __('Collection Name') }}</label>
                                    <input type="text" name="name" value="{{ $collection->name }}" required class="form-input text-xs rounded-xl" id="edit-collection-name-input">
                                </div>

                                <label class="flex items-center justify-between p-3 rounded-xl border border-warm dark:border-warm cursor-pointer hover:bg-surface dark:hover:bg-surface">
                                    <div>
                                        <p class="text-xs font-bold text-muted dark:text-white">{{ __('Private Collection') }}</p>
                                        <p class="text-xs text-muted">{{ __('Only you can view and manage this collection') }}</p>
                                    </div>
                                    <input type="checkbox" name="is_private" value="1" {{ $collection->is_private ? 'checked' : '' }} class="rounded text-primary-500 focus:ring-primary-500 w-4 h-4">
                                </label>
                            </div>

                            <div class="pt-4 border-t border-warm dark:border-warm flex items-center justify-end gap-2">
                                <button type="button" @click="editModalOpen = false" class="btn btn-secondary text-xs px-4 py-2 rounded-xl font-medium">{{ __('Cancel') }}</button>
                                <button type="submit" class="btn btn-primary text-xs px-5 py-2 rounded-xl font-bold shadow-sm" id="save-collection-settings-btn">{{ __('Save Changes') }}</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        @endif

    </div>
</x-layouts.dashboard>
