<x-layouts.dashboard>
    <x-slot name="title">{{ __('Property Features Catalog') }}</x-slot>

    @section('page-title', 'Features Catalog')
    <x-slot:breadcrumb>
        <x-breadcrumb :items="[
            ['label' => __('Dashboard'), 'url' => '/dashboard'],
            ['label' => __('Features')],
        ]" />
    </x-slot:breadcrumb>

    <div x-data="{
        showCreateModal: false,
        showEditModal: false,
        showDeleteModal: false,
        activeFeature: { id: '', name_en: '', code: '', status: 'active' },
        deleteUrl: '',
        
        openEdit(item) {
            this.activeFeature = Object.assign({}, item);
            this.showEditModal = true;
        },

        confirmDelete(id, name) {
            this.activeFeature = { id: id, name_en: name };
            this.deleteUrl = '/dashboard/features/' + id;
            this.showDeleteModal = true;
        }
    }" class="space-y-6 page-enter pb-12 font-sans">

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
            :title="__('Property Features Catalog')"
            :description="__('Configure architectural and structural property attributes, system codes, and listing specifications.')"
            icon="cube"
            badge="Features"
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
                    <span>{{ __('Add Feature') }}</span>
                </button>
            </x-slot:actions>
        </x-page-header>

        {{-- Top Bar & Stats --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="cinematic-fade-up cinematic-stagger-1">
                <x-card-stat
                    :label="__('Total Features')"
                    :value="$stats['total']"
                    iconColor="orange"
                    icon="cube"
                    :badge="__('Catalog')"
                    badgeVariant="brand"
                    caption="{{ __('Defined feature items') }}"
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
                    caption="{{ __('Selectable in properties') }}"
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
                    caption="{{ __('Archived specifications') }}"
                />
            </div>
        </div>

        {{-- Filter & Actions Card --}}
        <div class="cinematic-fade-up cinematic-stagger-2">
            <x-card padding="p-5">
            <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
                {{-- Search & Filter Form --}}
                <form method="GET" action="{{ route('dashboard.features.index') }}" class="flex flex-wrap items-center gap-3 flex-1">
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
                            placeholder="Search features (e.g. Marble, Ceilings)..." 
                            class="form-input pl-9 w-full text-sm"
                        >
                    </div>

                    <select name="status" onchange="this.form.submit()" class="form-select text-sm min-w-30">
                        <option value="">{{ __('All Statuses') }}</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                    </select>

                    @if(request('search') || request('status'))
                        <a href="{{ route('dashboard.features.index') }}" class="text-xs text-orange-600 hover:text-orange-700 dark:text-orange-400 font-medium px-2 py-1.5">{{ __('Reset filters') }}</a>
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
                        <span>{{ __('Add Feature') }}</span>
                    </button>
                </div>
            </div>
        </x-card>
        </div>

        {{-- Features Table --}}
        <div class="cinematic-fade-up cinematic-stagger-3">
            <x-card padding="p-0" class="overflow-hidden">
            <div class="table-container">
                <table class="data-table w-full text-left text-sm">
                    <thead>
                        <tr>
                            <th class="py-3.5 px-4">{{ __('Feature Name') }}</th>
                            <th class="py-3.5 px-4">{{ __('System Code') }}</th>
                            <th class="py-3.5 px-4">{{ __('Status') }}</th>
                            <th class="py-3.5 px-4 text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($features as $item)
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
                                    <code class="font-mono text-xs px-2 py-0.5 bg-slate-100 dark:bg-slate-900 text-slate-700 dark:text-slate-300 rounded border border-slate-200 dark:border-slate-800">
                                        {{ $item->code }}
                                    </code>
                                </td>
                                <td class="py-3.5 px-4">
                                    <form method="POST" action="{{ route('dashboard.features.toggle', $item->id) }}" class="inline-block">
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
                                            title="Edit feature"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                            </svg>
                                        </button>
                                        <button 
                                            type="button" 
                                            @click="confirmDelete('{{ $item->id }}', '{{ addslashes($item->name_en) }}')" 
                                            class="p-1.5 text-slate-500 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 rounded-md transition-colors cursor-pointer"
                                            title="Delete feature"
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
                                <td colspan="4" class="p-8">
                                    <x-empty-state
                                        icon="cube"
                                        :title="__('No features found')"
                                        :description="__('Try adjusting your search criteria or add a new feature.')">
                                        <x-slot:actions>
                                            <button 
                                                type="button" 
                                                @click="showCreateModal = true" 
                                                class="btn btn-outline text-xs"
                                            >{{ __('Add First Feature') }}</button>
                                        </x-slot:actions>
                                    </x-empty-state>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($features->hasPages())
                <div class="p-4 border-t border-warm dark:border-[#273244]">
                    {{ $features->links() }}
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
                        <h3 class="font-display font-semibold text-lg text-slate-900 dark:text-white">{{ __('Add New Feature') }}</h3>
                        <button type="button" @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <form action="{{ route('dashboard.features.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <x-label for="create_feature_name_en" required>Feature Name (English)</x-label>
                            <x-input type="text" id="create_feature_name_en" name="name_en" placeholder="{{ __('e.g. Italian Marble Flooring, High Ceilings') }}" required class="w-full" />
                        </div>

                        <div>
                            <x-label for="create_feature_code">System Code (Optional)</x-label>
                            <x-input type="text" id="create_feature_code" name="code" placeholder="e.g. italian_marble (auto-generated if empty)" class="w-full font-mono text-xs" />
                        </div>

                        <div>
                            <x-label for="create_feature_status" required>Status</x-label>
                            <select id="create_feature_status" name="status" class="form-select w-full">
                                <option value="active" selected>{{ __('Active') }}</option>
                                <option value="inactive">{{ __('Inactive') }}</option>
                            </select>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-200 dark:border-slate-800">
                            <button type="button" @click="showCreateModal = false" class="btn btn-ghost">{{ __('Cancel') }}</button>
                            <x-button type="submit" variant="primary">Create Feature</x-button>
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
                        <h3 class="font-display font-semibold text-lg text-slate-900 dark:text-white">{{ __('Edit Feature') }}</h3>
                        <button type="button" @click="showEditModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <form :action="'/dashboard/features/' + activeFeature.id" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')
                        <div>
                            <x-label for="edit_feature_name_en" required>Feature Name (English)</x-label>
                            <input type="text" id="edit_feature_name_en" name="name_en" x-model="activeFeature.name_en" required class="form-input w-full">
                        </div>

                        <div>
                            <x-label for="edit_feature_code" required>System Code</x-label>
                            <input type="text" id="edit_feature_code" name="code" x-model="activeFeature.code" required class="form-input w-full font-mono text-xs">
                        </div>

                        <div>
                            <x-label for="edit_feature_status" required>Status</x-label>
                            <select id="edit_feature_status" name="status" x-model="activeFeature.status" class="form-select w-full">
                                <option value="active">{{ __('Active') }}</option>
                                <option value="inactive">{{ __('Inactive') }}</option>
                            </select>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-200 dark:border-slate-800">
                            <button type="button" @click="showEditModal = false" class="btn btn-ghost">{{ __('Cancel') }}</button>
                            <x-button type="submit" variant="primary">Update Feature</x-button>
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
                        <h3 class="font-display font-semibold text-base text-slate-900 dark:text-white">{{ __('Delete Feature?') }}</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                            Are you sure you want to remove <strong class="text-slate-900 dark:text-white" x-text="activeFeature.name_en"></strong>? This will detach it from any linked properties.
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
