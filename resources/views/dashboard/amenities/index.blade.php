<x-layouts.dashboard>
    <x-slot name="title">{{ __('Amenity Catalog') }}</x-slot>

    @section('page-title', 'Amenity Catalog')
    <x-slot:breadcrumb>
        <x-breadcrumb :items="[
            ['label' => __('Dashboard'), 'url' => '/dashboard'],
            ['label' => __('Amenities')],
        ]" />
    </x-slot:breadcrumb>

    <div x-data="{
        showCreateModal: {{ $errors->any() && old('_action') === 'create' ? 'true' : 'false' }},
        showEditModal: {{ $errors->any() && old('_action') === 'edit' ? 'true' : 'false' }},
        showDeleteModal: false,
        activeAmenity: { 
            id: '{{ old('id', '') }}', 
            name_en: '{{ old('name_en', '') }}', 
            category: '{{ old('category', '') }}', 
            code: '{{ old('code', '') }}', 
            status: '{{ old('status', 'active') }}' 
        },
        deleteUrl: '',
        
        openEdit(item) {
            this.activeAmenity = Object.assign({}, item);
            this.showEditModal = true;
        },

        confirmDelete(id, name) {
            this.activeAmenity = { id: id, name_en: name };
            this.deleteUrl = '/dashboard/amenities/' + id;
            this.showDeleteModal = true;
        }
    }" class="space-y-6 pb-12 font-sans">

        {{-- Success Flash Alert --}}
        @if (session('success'))
            <x-alert type="success" dismissible="true">
                {{ session('success') }}
            </x-alert>
        @endif

        @if ($errors->any())
            <x-alert type="error" dismissible="true">
                <ul class="list-disc list-inside space-y-1 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-alert>
        @endif

        <x-page-header
            :title="__('Amenity Catalog')"
            :description="__('Manage real estate property amenities, taxonomy codes, active statuses, and classification categories.')"
            icon="cube"
            badge="Catalog"
        >
            <x-slot:actions>
                <button 
                    type="button" 
                    @click="showCreateModal = true" 
                    class="btn btn-primary shadow-xs hover:shadow transition-all inline-flex items-center gap-2 whitespace-nowrap cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span>{{ __('Add Amenity') }}</span>
                </button>
            </x-slot:actions>
        </x-page-header>

        {{-- Top Bar & Stats --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="cinematic-fade-up cinematic-stagger-1">
                <x-card-stat
                    :label="__('Total Amenities')"
                    :value="$stats['total']"
                    iconColor="orange"
                    icon="cube"
                    :badge="__('Catalog')"
                    badgeVariant="brand"
                    caption="{{ __('Registered amenities') }}"
                />
            </div>
            <div class="cinematic-fade-up cinematic-stagger-2">
                <x-card-stat
                    :label="__('Active')"
                    :value="$stats['active']"
                    iconColor="emerald"
                    icon="check"
                    :badge="__('Enabled')"
                    badgeVariant="success"
                    caption="{{ __('Visible in property listings') }}"
                />
            </div>
            <div class="cinematic-fade-up cinematic-stagger-3">
                <x-card-stat
                    :label="__('Inactive')"
                    :value="$stats['inactive']"
                    iconColor="gray"
                    icon="clock"
                    :badge="__('Disabled')"
                    badgeVariant="neutral"
                    caption="{{ __('Hidden from filters') }}"
                />
            </div>
            <div class="cinematic-fade-up cinematic-stagger-4">
                <x-card-stat
                    :label="__('Categories')"
                    :value="$stats['categories_count']"
                    iconColor="blue"
                    icon="layers"
                    :badge="__('Groups')"
                    badgeVariant="info"
                    caption="{{ __('Taxonomy classifications') }}"
                />
            </div>
        </div>

        {{-- Filter & Actions Card --}}
        <div class="cinematic-fade-up cinematic-stagger-2">
            <x-card padding="p-5">
            <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
                {{-- Search & Filter Form --}}
                <form method="GET" action="{{ route('dashboard.amenities.index') }}" class="flex flex-wrap items-center gap-3 flex-1">
                    <div class="relative flex-1 min-w-52">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-muted">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </div>
                        <input 
                            type="text" 
                            name="search" 
                            value="{{ request('search') }}" 
                            placeholder="{{ __('Search amenities or codes...') }}" 
                            class="form-input search-input pl-9 pr-8 w-full text-sm"
                        >
                        @if(request('search'))
                            <a href="{{ route('dashboard.amenities.index', request()->except('search')) }}" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </a>
                        @endif
                    </div>

                    <select name="category" onchange="this.form.submit()" class="form-select text-sm min-w-36">
                        <option value="">{{ __('All Categories') }}</option>
                        @foreach($categories as $category)
                            <option value="{{ $category }}" {{ request('category') === $category ? 'selected' : '' }}>{{ $category }}</option>
                        @endforeach
                    </select>

                    <select name="status" onchange="this.form.submit()" class="form-select text-sm min-w-30">
                        <option value="">{{ __('All Statuses') }}</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                    </select>

                    @if(request('search') || request('category') || request('status'))
                        <a href="{{ route('dashboard.amenities.index') }}" class="text-xs text-orange-600 hover:text-orange-700 dark:text-orange-400 font-medium px-2 py-1.5">{{ __('Reset filters') }}</a>
                    @endif
                </form>

                {{-- Action Button --}}
                <div>
                    <button 
                        type="button" 
                        @click="showCreateModal = true" 
                        class="btn btn-primary inline-flex items-center gap-2 whitespace-nowrap cursor-pointer shadow-sm shadow-orange-500/20"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        <span>{{ __('Add Amenity') }}</span>
                    </button>
                </div>
            </div>
        </x-card>
        </div>

        {{-- Amenities Table --}}
        <div class="cinematic-fade-up cinematic-stagger-3">
            <x-card padding="p-0" class="overflow-hidden">
            <div class="table-container">
                <table class="data-table w-full text-left text-sm">
                    <thead>
                        <tr>
                            <th class="py-3.5 px-4">{{ __('Amenity Name') }}</th>
                            <th class="py-3.5 px-4">{{ __('Category') }}</th>
                            <th class="py-3.5 px-4">{{ __('System Code') }}</th>
                            <th class="py-3.5 px-4">{{ __('Status') }}</th>
                            <th class="py-3.5 px-4 text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($amenities as $item)
                            <tr>
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-orange-100 dark:bg-orange-950/40 text-orange-600 dark:text-orange-400 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                            {{ strtoupper(substr($item->name_en, 0, 2)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-semibold text-slate-900 dark:text-white truncate">{{ $item->name_en }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                        {{ $item->category ?? 'General' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <code class="font-mono text-xs px-2 py-0.5 bg-slate-100 dark:bg-slate-900 text-slate-700 dark:text-slate-300 rounded border border-slate-200 dark:border-slate-800">
                                        {{ $item->code }}
                                    </code>
                                </td>
                                <td class="py-3.5 px-4">
                                    <form method="POST" action="{{ route('dashboard.amenities.toggle', $item->id) }}" class="inline-block">
                                        @csrf
                                        @method('PATCH')
                                        <button 
                                            type="submit" 
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium transition-opacity hover:opacity-80 cursor-pointer {{ $item->status === 'active' ? 'bg-emerald-100 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }}"
                                            title="Click to toggle status"
                                         >
                                            <span class="w-1.5 h-1.5 rounded-full {{ $item->status === 'active' ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                            <span class="capitalize">{{ $item->status }}</span>
                                        </button>
                                    </form>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <button 
                                            type="button" 
                                            @click="openEdit({{ Js::from($item) }})" 
                                            class="p-1.5 text-slate-500 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-orange-950/30 rounded-md transition-colors cursor-pointer"
                                            title="Edit amenity"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                            </svg>
                                        </button>
                                        <button 
                                            type="button" 
                                            @click="confirmDelete('{{ $item->id }}', '{{ addslashes($item->name_en) }}')" 
                                            class="p-1.5 text-slate-500 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 rounded-md transition-colors cursor-pointer"
                                            title="Delete amenity"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-8">
                                    <x-empty-state
                                        icon="cube"
                                        :title="__('No amenities found')"
                                        :description="__('Try adjusting your search criteria or add a new amenity.')">
                                        <x-slot:actions>
                                            <button 
                                                type="button" 
                                                @click="showCreateModal = true" 
                                                class="btn btn-outline text-xs"
                                            >{{ __('Add First Amenity') }}</button>
                                        </x-slot:actions>
                                    </x-empty-state>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($amenities->hasPages())
                <div class="p-4 border-t border-warm dark:border-[#273244]">
                    {{ $amenities->links() }}
                </div>
            @endif
        </x-card>
        </div>

        {{-- ═══ CREATE MODAL ═══ --}}
        <div 
            x-show="showCreateModal" 
            class="fixed inset-0 z-50 overflow-y-auto" 
            x-cloak
        >
            <div class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity" @click="showCreateModal = false"></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div class="modal-content relative w-full max-w-md p-6 space-y-5" @click.stop>
                    <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
                        <h3 class="font-display font-semibold text-lg text-slate-900 dark:text-white">{{ __('Add New Amenity') }}</h3>
                        <button type="button" @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <form action="{{ route('dashboard.amenities.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="hidden" name="_action" value="create">
                        <div>
                            <x-label for="create_name_en" required>Amenity Name (English)</x-label>
                            <x-input type="text" id="create_name_en" name="name_en" value="{{ old('_action') === 'create' ? old('name_en') : '' }}" placeholder="{{ __('e.g. Swimming Pool, Solar Power') }}" required class="w-full" />
                        </div>

                        <div>
                            <x-label for="create_category" required>Category</x-label>
                            <div class="relative">
                                <input 
                                    type="text" 
                                    id="create_category" 
                                    name="category" 
                                    list="categories-list" 
                                    value="{{ old('_action') === 'create' ? old('category') : '' }}"
                                    placeholder="{{ __('e.g. Utilities, Security, Wellness') }}" 
                                    required 
                                    class="form-input w-full"
                                >
                                <datalist id="categories-list">
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat }}"></option>
                                    @endforeach
                                </datalist>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">{{ __('Select an existing category or type a new one.') }}</p>
                        </div>

                        <div>
                            <x-label for="create_code">System Code (Optional)</x-label>
                            <x-input type="text" id="create_code" name="code" value="{{ old('_action') === 'create' ? old('code') : '' }}" placeholder="e.g. swimming_pool (auto-generated if empty)" class="w-full font-mono text-xs" />
                        </div>

                        <div>
                            <x-label for="create_status" required>Status</x-label>
                            <select id="create_status" name="status" class="form-select w-full">
                                <option value="active" {{ old('_action') === 'create' && old('status') === 'inactive' ? '' : 'selected' }}>{{ __('Active') }}</option>
                                <option value="inactive" {{ old('_action') === 'create' && old('status') === 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                            </select>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-200 dark:border-slate-800">
                            <button type="button" @click="showCreateModal = false" class="btn btn-ghost">{{ __('Cancel') }}</button>
                            <x-button type="submit" variant="primary">Create Amenity</x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ═══ EDIT MODAL ═══ --}}
        <div 
            x-show="showEditModal" 
            class="fixed inset-0 z-50 overflow-y-auto" 
            x-cloak
        >
            <div class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity" @click="showEditModal = false"></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div class="modal-content relative w-full max-w-md p-6 space-y-5" @click.stop>
                    <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
                        <h3 class="font-display font-semibold text-lg text-slate-900 dark:text-white">{{ __('Edit Amenity') }}</h3>
                        <button type="button" @click="showEditModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <form :action="'/dashboard/amenities/' + activeAmenity.id" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')
                        <div>
                            <x-label for="edit_name_en" required>Amenity Name (English)</x-label>
                            <input type="text" id="edit_name_en" name="name_en" x-model="activeAmenity.name_en" required class="form-input w-full">
                        </div>

                        <div>
                            <x-label for="edit_category" required>Category</x-label>
                            <input type="text" id="edit_category" name="category" x-model="activeAmenity.category" list="edit-categories-list" required class="form-input w-full">
                            <datalist id="edit-categories-list">
                                @foreach($categories as $cat)
                                    <option value="{{ $cat }}"></option>
                                @endforeach
                            </datalist>
                        </div>

                        <div>
                            <x-label for="edit_code" required>System Code</x-label>
                            <input type="text" id="edit_code" name="code" x-model="activeAmenity.code" required class="form-input w-full font-mono text-xs">
                        </div>

                        <div>
                            <x-label for="edit_status" required>Status</x-label>
                            <select id="edit_status" name="status" x-model="activeAmenity.status" class="form-select w-full">
                                <option value="active">{{ __('Active') }}</option>
                                <option value="inactive">{{ __('Inactive') }}</option>
                            </select>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-200 dark:border-slate-800">
                            <button type="button" @click="showEditModal = false" class="btn btn-ghost">{{ __('Cancel') }}</button>
                            <x-button type="submit" variant="primary">Update Amenity</x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ═══ DELETE CONFIRMATION MODAL ═══ --}}
        <div 
            x-show="showDeleteModal" 
            class="fixed inset-0 z-50 overflow-y-auto" 
            x-cloak
        >
            <div class="fixed inset-0 bg-black/60 backdrop-blur-xs transition-opacity" @click="showDeleteModal = false"></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div class="modal-content relative w-full max-w-sm p-6 space-y-4" @click.stop>
                    <div class="w-12 h-12 rounded-full bg-red-100 dark:bg-red-950/40 text-red-600 dark:text-red-400 flex items-center justify-center mx-auto">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                    </div>

                    <div class="text-center">
                        <h3 class="font-display font-semibold text-base text-slate-900 dark:text-white">{{ __('Delete Amenity?') }}</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                            Are you sure you want to remove <strong class="text-slate-900 dark:text-white" x-text="activeAmenity.name_en"></strong>? This will detach it from any linked properties.
                        </p>
                    </div>

                    <form :action="deleteUrl" method="POST" class="flex items-center justify-center gap-3 pt-2">
                        @csrf
                        @method('DELETE')
                        <button type="button" @click="showDeleteModal = false" class="btn btn-ghost text-xs">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn bg-red-600 hover:bg-red-700 text-white text-xs px-4 py-2 rounded-lg cursor-pointer">{{ __('Delete') }}</button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
