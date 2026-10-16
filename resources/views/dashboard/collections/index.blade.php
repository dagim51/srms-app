<x-layouts.dashboard>
    <x-slot name="title">{{ __('My Collections') }} - {{ __('Ethiopian Real Estate Platform') }}</x-slot>

    <div class="space-y-6"
         x-data="{
             createModalOpen: false,
             editModalOpen: false,
             deleteModalOpen: false,
             selectedCollection: null,
             editName: '',
             editPrivate: true,
             newName: '',
             newPrivate: true,

             openEdit(col) {
                 this.selectedCollection = col;
                 this.editName = col.name;
                 this.editPrivate = Boolean(col.is_private);
                 this.editModalOpen = true;
             },

             openDelete(col) {
                 this.selectedCollection = col;
                 this.deleteModalOpen = true;
             }
         }">

        {{-- Page Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-warm dark:border-warm pb-5">
            <div>
                <nav class="flex items-center gap-2 text-xs text-muted mb-1">
                    <a href="/dashboard" class="hover:text-primary-600 transition-colors">{{ __('Dashboard') }}</a>
                    <span>/</span>
                    <span class="text-muted dark:text-white font-medium">{{ __('Collections') }}</span>
                </nav>
                <h1 class="text-2xl sm:text-3xl font-display font-extrabold text-muted dark:text-white tracking-tight flex items-center gap-3">
                    <span>{{ __('My Property Collections') }}</span>
                    <span class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-orange-100 dark:bg-orange-950/60 text-primary-600 dark:text-primary-400 border border-orange-200 dark:border-orange-900/40">
                        {{ $collections->total() }} {{ __('Collections') }}
                    </span>
                </h1>
                <p class="text-xs sm:text-sm text-muted dark:text-muted mt-1">
                    {{ __('Organize your saved homes, commercial investments, and land plots into private or shared collections.') }}
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('dashboard.favorites.index') }}" class="btn btn-secondary text-xs flex items-center gap-1.5" id="view-favorites-link">
                    <svg class="w-4 h-4 text-rose-500 fill-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" /></svg>
                    <span>{{ __('All Favorites') }}</span>
                </a>
                <button type="button" 
                        @click="createModalOpen = true" 
                        class="btn btn-primary text-xs flex items-center gap-2 shadow-sm"
                        id="create-collection-btn">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    <span>{{ __('New Collection') }}</span>
                </button>
            </div>
        </div>

        {{-- COLLECTIONS GRID --}}
        @if($collections->isEmpty())
            {{-- EMPTY STATE --}}
            <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-3xl p-12 text-center max-w-xl mx-auto my-8 shadow-sm">
                <div class="w-20 h-20 rounded-full bg-orange-100 dark:bg-orange-950/40 text-primary-600 dark:text-primary-400 mx-auto flex items-center justify-center mb-5">
                    <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121 12v.75m-8.625-1.5v3.75m-3.75-3.75v3.75m6-3.75v3.75M3 18.75V18a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 18v.75" /></svg>
                </div>

                <h3 class="font-display font-extrabold text-2xl text-muted dark:text-white mb-2">
                    {{ __('No Collections Yet') }}
                </h3>

                <p class="text-sm text-muted dark:text-muted mb-6 max-w-md mx-auto leading-relaxed">
                    {{ __('Collections let you group your favorite properties together, such as "Dream Homes in Bole", "Affordable 2-Beds", or "Vacation Rentals".') }}
                </p>

                <div class="flex flex-wrap items-center justify-center gap-3">
                    <button type="button" @click="createModalOpen = true" class="btn btn-primary px-6 py-2.5 rounded-xl text-sm font-bold shadow-md shadow-orange-500/20">
                        {{ __('Create Your First Collection') }}
                    </button>
                    <a href="{{ route('dashboard.favorites.index') }}" class="btn btn-secondary px-6 py-2.5 rounded-xl text-sm font-bold">
                        {{ __('Browse Saved Favorites') }}
                    </a>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6" id="collections-grid">
                @foreach($collections as $col)
                    @php
                        $firstProps = $col->properties->take(3);
                    @endphp
                    <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-2xl overflow-hidden shadow-sm hover:shadow-lg transition-all duration-200 group flex flex-col justify-between"
                         id="collection-card-{{ $col->id }}">
                        
                        <div>
                            {{-- Preview Collages --}}
                            <div class="relative h-44 bg-surface-subtle dark:bg-surface grid grid-cols-3 gap-0.5 overflow-hidden">
                                @if($firstProps->count() > 0)
                                    @foreach($firstProps as $pItem)
                                        @php
                                            $thumb = $pItem->property?->media->first()?->url ?? $pItem->property?->media->first()?->storage_key ?? 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=400&q=80';
                                        @endphp
                                        <div class="h-full overflow-hidden">
                                            <img src="{{ $thumb }}" alt="Property" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                        </div>
                                    @endforeach
                                    @for($i = $firstProps->count(); $i < 3; $i++)
                                        <div class="h-full bg-surface-subtle dark:bg-surface/50 flex items-center justify-center text-muted">
                                            <svg class="w-6 h-6 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg>
                                        </div>
                                    @endfor
                                @else
                                    <div class="col-span-3 flex flex-col items-center justify-center text-muted">
                                        <svg class="w-8 h-8 opacity-40 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                        <span class="text-xs">{{ __('Empty Collection') }}</span>
                                    </div>
                                @endif

                                {{-- Privacy Badge --}}
                                <div class="absolute top-3 left-3 z-10">
                                    @if($col->is_private)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-black/75 backdrop-blur-sm text-muted">
                                            <svg class="w-3 h-3 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>
                                            {{ __('Private') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-600/90 backdrop-blur-sm text-white">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-.778.099-1.533.284-2.253" /></svg>
                                            {{ __('Shared') }}
                                        </span>
                                    @endif
                                </div>

                                {{-- Property Count Pill --}}
                                <div class="absolute bottom-3 right-3 z-10">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-black/70 backdrop-blur-sm text-white">
                                        {{ $col->properties_count }} {{ __('properties') }}
                                    </span>
                                </div>
                            </div>

                            {{-- Collection Info --}}
                            <div class="p-5 space-y-2">
                                <h3 class="font-display font-bold text-muted dark:text-white text-lg line-clamp-1 group-hover:text-primary-600 transition-colors">
                                    <a href="{{ route('dashboard.collections.show', $col->id) }}">
                                        {{ $col->name }}
                                    </a>
                                </h3>

                                <p class="text-xs text-muted dark:text-muted">
                                    {{ __('Created') }} {{ $col->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>

                        {{-- Card Actions --}}
                        <div class="p-4 bg-surface dark:bg-surface/50 border-t border-warm dark:border-warm flex items-center justify-between gap-2">
                            <div class="flex items-center gap-1">
                                <button type="button" 
                                        @click="openEdit({ id: '{{ $col->id }}', name: '{{ addslashes($col->name) }}', is_private: {{ $col->is_private ? 'true' : 'false' }} })"
                                        class="p-1.5 rounded-lg text-muted hover:text-muted dark:hover:text-muted hover:bg-surface-subtle/50 transition-colors"
                                        title="{{ __('Edit collection') }}">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" /></svg>
                                </button>
                                <button type="button" 
                                        @click="openDelete({ id: '{{ $col->id }}', name: '{{ addslashes($col->name) }}' })"
                                        class="p-1.5 rounded-lg text-muted hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-colors"
                                        title="{{ __('Delete collection') }}">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                </button>
                            </div>

                            <a href="{{ route('dashboard.collections.show', $col->id) }}" class="btn btn-primary btn-sm text-xs font-bold px-3 py-1.5 rounded-lg flex items-center gap-1">
                                <span>{{ __('Open') }}</span>
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
                            </a>
                        </div>

                    </div>
                @endforeach
            </div>

            <div class="mt-8 pt-4 border-t border-warm dark:border-warm flex justify-center">
                {{ $collections->links() }}
            </div>
        @endif

        {{-- CREATE COLLECTION MODAL --}}
        <div x-show="createModalOpen" 
             class="fixed inset-0 z-50 overflow-y-auto" 
             aria-labelledby="create-collection-modal-title" 
             role="dialog" 
             aria-modal="true"
             x-cloak>
            
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="createModalOpen" 
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     @click="createModalOpen = false"
                     class="fixed inset-0 bg-surface/60 backdrop-blur-xs transition-opacity"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="createModalOpen"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block align-bottom bg-white dark:bg-[#1E293B] rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-warm dark:border-warm">
                    
                    <form action="{{ route('dashboard.collections.store') }}" method="POST" class="p-6 space-y-5">
                        @csrf
                        <div class="flex items-center justify-between pb-3 border-b border-warm dark:border-warm">
                            <h3 class="font-display font-bold text-lg text-muted dark:text-white" id="create-collection-modal-title">
                                {{ __('Create New Collection') }}
                            </h3>
                            <button type="button" @click="createModalOpen = false" class="p-1 rounded-lg text-muted hover:text-muted">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="form-label text-xs font-semibold">{{ __('Collection Name') }}</label>
                                <input type="text" name="name" x-model="newName" required placeholder="{{ __('e.g. Villas in Bole, Addis Penthouse Shortlist...') }}" class="form-input text-xs rounded-xl" id="create-collection-name-input">
                            </div>

                            <label class="flex items-center justify-between p-3 rounded-xl border border-warm dark:border-warm cursor-pointer hover:bg-surface dark:hover:bg-surface">
                                <div>
                                    <p class="text-xs font-bold text-muted dark:text-white">{{ __('Private Collection') }}</p>
                                    <p class="text-xs text-muted">{{ __('Only you can view and manage this collection') }}</p>
                                </div>
                                <input type="checkbox" name="is_private" value="1" x-model="newPrivate" class="rounded text-primary-500 focus:ring-primary-500 w-4 h-4">
                            </label>
                        </div>

                        <div class="pt-4 border-t border-warm dark:border-warm flex items-center justify-end gap-2">
                            <button type="button" @click="createModalOpen = false" class="btn btn-secondary text-xs px-4 py-2 rounded-xl font-medium">
                                {{ __('Cancel') }}
                            </button>
                            <button type="submit" class="btn btn-primary text-xs px-5 py-2 rounded-xl font-bold shadow-sm" id="create-collection-submit-btn">
                                {{ __('Create Collection') }}
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

        {{-- EDIT COLLECTION MODAL --}}
        <div x-show="editModalOpen" 
             class="fixed inset-0 z-50 overflow-y-auto" 
             aria-labelledby="edit-collection-modal-title" 
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
                    
                    <form :action="'/dashboard/collections/' + (selectedCollection?.id || '')" method="POST" class="p-6 space-y-5">
                        @csrf
                        @method('PUT')
                        <div class="flex items-center justify-between pb-3 border-b border-warm dark:border-warm">
                            <h3 class="font-display font-bold text-lg text-muted dark:text-white" id="edit-collection-modal-title">
                                {{ __('Edit Collection') }}
                            </h3>
                            <button type="button" @click="editModalOpen = false" class="p-1 rounded-lg text-muted hover:text-muted">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="form-label text-xs font-semibold">{{ __('Collection Name') }}</label>
                                <input type="text" name="name" x-model="editName" required class="form-input text-xs rounded-xl">
                            </div>

                            <label class="flex items-center justify-between p-3 rounded-xl border border-warm dark:border-warm cursor-pointer hover:bg-surface dark:hover:bg-surface">
                                <div>
                                    <p class="text-xs font-bold text-muted dark:text-white">{{ __('Private Collection') }}</p>
                                    <p class="text-xs text-muted">{{ __('Only you can view and manage this collection') }}</p>
                                </div>
                                <input type="checkbox" name="is_private" value="1" x-model="editPrivate" class="rounded text-primary-500 focus:ring-primary-500 w-4 h-4">
                            </label>
                        </div>

                        <div class="pt-4 border-t border-warm dark:border-warm flex items-center justify-end gap-2">
                            <button type="button" @click="editModalOpen = false" class="btn btn-secondary text-xs px-4 py-2 rounded-xl font-medium">
                                {{ __('Cancel') }}
                            </button>
                            <button type="submit" class="btn btn-primary text-xs px-5 py-2 rounded-xl font-bold shadow-sm">
                                {{ __('Save Changes') }}
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

        {{-- DELETE COLLECTION CONFIRMATION MODAL --}}
        <div x-show="deleteModalOpen" 
             class="fixed inset-0 z-50 overflow-y-auto" 
             aria-labelledby="delete-collection-modal-title" 
             role="dialog" 
             aria-modal="true"
             x-cloak>
            
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="deleteModalOpen" 
                     @click="deleteModalOpen = false"
                     class="fixed inset-0 bg-surface/60 backdrop-blur-xs transition-opacity"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="deleteModalOpen"
                     class="inline-block align-bottom bg-white dark:bg-[#1E293B] rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-warm dark:border-warm p-6 space-y-4">
                    
                    <div class="w-12 h-12 rounded-full bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center mx-auto mb-2">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                    </div>

                    <h3 class="font-display font-bold text-lg text-muted dark:text-white text-center" id="delete-collection-modal-title">
                        {{ __('Delete Collection?') }}
                    </h3>

                    <p class="text-xs text-muted dark:text-muted text-center leading-relaxed">
                        {{ __('Are you sure you want to delete this collection? The saved properties themselves will remain in your favorites.') }}
                    </p>

                    <form :action="'/dashboard/collections/' + (selectedCollection?.id || '')" method="POST" class="pt-2 flex items-center justify-center gap-2">
                        @csrf
                        @method('DELETE')
                        <button type="button" @click="deleteModalOpen = false" class="btn btn-secondary text-xs px-4 py-2 rounded-xl font-medium">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit" class="btn btn-danger text-xs px-5 py-2 rounded-xl font-bold shadow-sm" id="confirm-delete-collection-btn">
                            {{ __('Delete Collection') }}
                        </button>
                    </form>

                </div>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
