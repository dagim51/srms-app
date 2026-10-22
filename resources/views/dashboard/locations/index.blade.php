<x-layouts.dashboard>
    <x-slot name="title">{{ __('Location Hierarchy Management') }}</x-slot>

    @section('page-title', __('Location Hierarchy'))
    <x-slot:breadcrumb>
        <x-breadcrumb :items="[
            ['label' => __('Dashboard'), 'url' => '/dashboard'],
            ['label' => __('Locations')],
        ]" />
    </x-slot:breadcrumb>

    <div x-data="{
        currentTab: '{{ $tab }}',
        showCreateSubCityModal: false,
        showCreateZoneModal: false,
        showCreateWoredaModal: false,
        showEditModal: false,
        showDeleteModal: false,

        editItem: {
            id: '',
            type: '',
            name_en: '',
            name_am: '',
            name_om: '',
            city_id: '',
            region_id: '',
            sub_city_id: '',
            zone_id: '',
            parent_type: 'sub_city',
            status: 'active'
        },
        deleteType: '',
        deleteId: '',
        deleteName: '',

        openEdit(type, item) {
            this.editItem.id = item.id;
            this.editItem.type = type;
            this.editItem.name_en = item.name_en || '';
            this.editItem.name_am = item.name_am || '';
            this.editItem.name_om = item.name_om || '';
            this.editItem.status = item.status || 'active';

            if (type === 'subcity') {
                this.editItem.city_id = item.city_id || '';
            } else if (type === 'zone') {
                this.editItem.region_id = item.region_id || '';
            } else if (type === 'woreda') {
                if (item.sub_city_id) {
                    this.editItem.parent_type = 'sub_city';
                    this.editItem.sub_city_id = item.sub_city_id;
                    this.editItem.zone_id = '';
                } else {
                    this.editItem.parent_type = 'zone';
                    this.editItem.zone_id = item.zone_id;
                    this.editItem.sub_city_id = '';
                }
            }
            this.showEditModal = true;
        },

        confirmDelete(type, id, name) {
            this.deleteType = type;
            this.deleteId = id;
            this.deleteName = name;
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
            :title="__('Location Hierarchy Management')"
            :description="__('Manage Ethiopian cadastral and administrative boundaries: Chartered Sub-Cities, Regional Zones, and Woredas.')"
            icon="map-pin"
            badge="Cadastre"
        >
            <x-slot:actions>
                <div class="flex items-center gap-2">
                    <button 
                        type="button" 
                        @click="showCreateSubCityModal = true" 
                        class="btn btn-secondary text-xs shadow-xs hover:shadow transition-all inline-flex items-center gap-1.5 cursor-pointer"
                    >
                        <svg class="w-4 h-4 text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        <span>{{ __('Add Sub-City') }}</span>
                    </button>
                    <button 
                        type="button" 
                        @click="showCreateZoneModal = true" 
                        class="btn btn-secondary text-xs shadow-xs hover:shadow transition-all inline-flex items-center gap-1.5 cursor-pointer"
                    >
                        <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        <span>{{ __('Add Zone') }}</span>
                    </button>
                    <button 
                        type="button" 
                        @click="showCreateWoredaModal = true" 
                        class="btn btn-primary text-xs shadow-xs hover:shadow transition-all inline-flex items-center gap-1.5 cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        <span>{{ __('Add Woreda') }}</span>
                    </button>
                </div>
            </x-slot:actions>
        </x-page-header>

        {{-- Top Bar & Stats --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <x-card-stat
                :label="__('Sub-Cities')"
                :value="$totalSubCities"
                iconColor="orange"
                icon="building"
                :badge="__('Chartered')"
                badgeVariant="brand"
                caption="{{ __('Addis Ababa & urban divisions') }}"
            />
            <x-card-stat
                :label="__('Regional Zones')"
                :value="$totalZones"
                iconColor="blue"
                icon="globe"
                :badge="__('Regional')"
                badgeVariant="neutral"
                caption="{{ __('Administrative Zones') }}"
            />
            <x-card-stat
                :label="__('Woredas (Districts)')"
                :value="$totalWoredas"
                iconColor="purple"
                icon="map-pin"
                :badge="__('Municipal')"
                badgeVariant="brand"
                caption="{{ __('Local woreda units') }}"
            />
            <x-card-stat
                :label="__('Active Units')"
                :value="$activeUnits"
                iconColor="emerald"
                icon="check"
                :badge="__('Published')"
                badgeVariant="success"
                caption="{{ __('Enabled in property forms') }}"
            />
        </div>

        {{-- Actions & Filter Toolbar --}}
        <div class="cinematic-fade-up cinematic-stagger-2 bg-white dark:bg-[#1E293B] rounded-xl p-4 border border-warm dark:border-warm shadow-sm space-y-4 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-muted dark:text-white">{{ __('Geographic Hierarchy & Administrative Boundaries') }}</h2>
                    <p class="text-xs text-muted dark:text-muted">{{ __('Federal regions, chartered sub-cities, municipal zones, and localized woredas') }}</p>
                </div>
                {{-- Tab navigation buttons in header --}}
                <div class="flex items-center gap-1.5 p-1 bg-surface-subtle dark:bg-slate-800/80 rounded-lg border border-warm dark:border-[#273244]">
                    <button 
                        type="button" 
                        @click="currentTab = 'subcities'" 
                        :class="currentTab === 'subcities' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white font-semibold shadow-xs border-warm dark:border-slate-600' : 'text-muted hover:text-slate-900 dark:hover:text-white border-transparent'"
                        class="px-3 py-1.5 rounded-md text-xs transition-all border cursor-pointer flex items-center gap-1.5"
                    >
                        <span>{{ __('Sub-Cities') }}</span>
                        <span class="text-[10px] px-1.5 py-0.5 rounded-full" :class="currentTab === 'subcities' ? 'bg-orange-100 text-orange-700 dark:bg-orange-950/60 dark:text-orange-400 font-bold' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-400'">{{ $totalSubCities }}</span>
                    </button>
                    <button 
                        type="button" 
                        @click="currentTab = 'zones'" 
                        :class="currentTab === 'zones' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white font-semibold shadow-xs border-warm dark:border-slate-600' : 'text-muted hover:text-slate-900 dark:hover:text-white border-transparent'"
                        class="px-3 py-1.5 rounded-md text-xs transition-all border cursor-pointer flex items-center gap-1.5"
                    >
                        <span>{{ __('Regional Zones') }}</span>
                        <span class="text-[10px] px-1.5 py-0.5 rounded-full" :class="currentTab === 'zones' ? 'bg-orange-100 text-orange-700 dark:bg-orange-950/60 dark:text-orange-400 font-bold' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-400'">{{ $totalZones }}</span>
                    </button>
                    <button 
                        type="button" 
                        @click="currentTab = 'woredas'" 
                        :class="currentTab === 'woredas' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white font-semibold shadow-xs border-warm dark:border-slate-600' : 'text-muted hover:text-slate-900 dark:hover:text-white border-transparent'"
                        class="px-3 py-1.5 rounded-md text-xs transition-all border cursor-pointer flex items-center gap-1.5"
                    >
                        <span>{{ __('Woredas') }}</span>
                        <span class="text-[10px] px-1.5 py-0.5 rounded-full" :class="currentTab === 'woredas' ? 'bg-orange-100 text-orange-700 dark:bg-orange-950/60 dark:text-orange-400 font-bold' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-400'">{{ $totalWoredas }}</span>
                    </button>
                </div>
            </div>

            {{-- Filter Form --}}
            <form method="GET" action="{{ route('dashboard.locations.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-12 gap-3 pt-2 border-t border-warm dark:border-warm items-center">
                <input type="hidden" name="tab" :value="currentTab">

                <div class="md:col-span-5 relative">
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}" 
                        placeholder="{{ __('Search by location name or translation...') }}" 
                        class="form-input search-input w-full pl-9 pr-8 text-xs"
                    >
                    <svg class="w-4 h-4 text-muted absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                    @if(request('search'))
                        <a href="{{ route('dashboard.locations.index', array_merge(request()->except('search'), ['tab' => $tab])) }}" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200" title="{{ __('Clear search') }}">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </a>
                    @endif
                </div>

                <div class="md:col-span-3">
                    <select name="region_id" onchange="this.form.submit()" class="form-select w-full text-xs">
                        <option value="">{{ __('All Regions') }}</option>
                        @foreach($regions as $reg)
                            <option value="{{ $reg->id }}" {{ request('region_id') == $reg->id ? 'selected' : '' }}>
                                {{ $reg->name_en }} ({{ $reg->name_am ?? $reg->name_om }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <select name="status" onchange="this.form.submit()" class="form-select w-full text-xs">
                        <option value="">{{ __('All Statuses') }}</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                    </select>
                </div>

                <div class="md:col-span-2 flex items-center gap-2">
                    <button type="submit" class="btn btn-secondary btn-sm text-xs py-2 px-3 w-full justify-center">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                        <span>{{ __('Filter') }}</span>
                    </button>
                    @if(request()->hasAny(['search', 'region_id', 'status']))
                        <a href="{{ route('dashboard.locations.index', ['tab' => $tab]) }}" class="btn btn-ghost btn-sm text-xs py-2 px-2.5 text-muted hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-950/30 transition-colors" title="{{ __('Reset Filters') }}">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Locations Table Container --}}
        <div class="cinematic-fade-up cinematic-stagger-3">
            <x-card padding="p-0" class="overflow-hidden">
                {{-- Tab 1: Sub-Cities Table --}}
            <div x-show="currentTab === 'subcities'" class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('Sub-City') }}</th>
                            <th>{{ __('City & Region') }}</th>
                            <th>{{ __('Local Translations') }}</th>
                            <th>{{ __('Woredas Count') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th class="text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-warm dark:divide-[#273244]">
                        @forelse($subCities as $sc)
                            <tr class="hover:bg-surface/80 dark:hover:bg-[#172033]/50 transition-colors">
                                <td class="py-3 px-4 font-semibold text-slate-900 dark:text-white text-xs">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full {{ $sc->status === 'active' ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                        <span>{{ $sc->name_en }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-xs text-muted dark:text-dark-subtle">
                                    <span>{{ $sc->city?->name_en ?? 'N/A' }}</span>
                                    @if($sc->city?->region)
                                        <span class="text-muted">· {{ $sc->city->region->name_en }}</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-xs text-muted">
                                    @if($sc->name_am)<span class="mr-2">{{ $sc->name_am }}</span>@endif
                                    @if($sc->name_om)<span class="text-muted italic">{{ $sc->name_om }}</span>@endif
                                    @if(!$sc->name_am && !$sc->name_om)—@endif
                                </td>
                                <td class="py-3 px-4 text-xs">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-surface-subtle dark:bg-surface text-slate-700 dark:text-slate-300 font-medium text-xs">
                                        {{ $sc->woredas->count() }} {{ __('woredas') }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-xs">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $sc->status === 'active' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' }}">
                                        {{ ucfirst($sc->status) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        <button 
                                            type="button" 
                                            @click="openEdit('subcity', {{ Js::from($sc) }})" 
                                            class="p-1.5 text-muted hover:text-orange-600 dark:hover:text-orange-400 rounded transition-colors" 
                                            title="{{ __('Edit Sub-City') }}"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" /></svg>
                                        </button>
                                        <form method="POST" action="{{ route('dashboard.locations.toggle', ['type' => 'subcity', 'id' => $sc->id]) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="p-1.5 text-muted hover:text-slate-900 dark:hover:text-white rounded transition-colors" title="{{ $sc->status === 'active' ? __('Deactivate') : __('Activate') }}">
                                                <svg class="w-4 h-4 {{ $sc->status === 'active' ? 'text-emerald-600' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5.636 18.364a9 9 0 010-12.728m12.728 0a9 9 0 010 12.728m-9.9-2.829a5 5 0 010-7.07m7.072 0a5 5 0 010 7.07M13 12a1 1 0 11-2 0 1 1 0 012 0z" /></svg>
                                            </button>
                                        </form>
                                        <button 
                                            type="button" 
                                            @click="confirmDelete('subcity', '{{ $sc->id }}', '{{ addslashes($sc->name_en) }}')" 
                                            class="p-1.5 text-muted hover:text-red-600 rounded transition-colors" 
                                            title="{{ __('Delete Sub-City') }}"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-xs text-muted">
                                    {{ __('No sub-cities found matching your criteria.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                @if($subCities->hasPages())
                    <div class="p-4 border-t border-warm dark:border-[#273244]">
                        {{ $subCities->links() }}
                    </div>
                @endif
            </div>

            {{-- Tab 2: Regional Zones Table --}}
            <div x-show="currentTab === 'zones'" class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('Regional Zone') }}</th>
                            <th>{{ __('Region') }}</th>
                            <th>{{ __('Local Translations') }}</th>
                            <th>{{ __('Woredas Count') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th class="text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-warm dark:divide-[#273244]">
                        @forelse($zones as $z)
                            <tr class="hover:bg-surface/80 dark:hover:bg-[#172033]/50 transition-colors">
                                <td class="py-3 px-4 font-semibold text-slate-900 dark:text-white text-xs">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full {{ $z->status === 'active' ? 'bg-blue-500' : 'bg-slate-400' }}"></span>
                                        <span>{{ $z->name_en }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-xs text-muted dark:text-dark-subtle">
                                    {{ $z->region?->name_en ?? 'N/A' }}
                                </td>
                                <td class="py-3 px-4 text-xs text-muted">
                                    @if($z->name_am)<span class="mr-2">{{ $z->name_am }}</span>@endif
                                    @if($z->name_om)<span class="text-muted italic">{{ $z->name_om }}</span>@endif
                                    @if(!$z->name_am && !$z->name_om)—@endif
                                </td>
                                <td class="py-3 px-4 text-xs">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-surface-subtle dark:bg-surface text-slate-700 dark:text-slate-300 font-medium text-xs">
                                        {{ $z->woredas->count() }} {{ __('woredas') }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-xs">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $z->status === 'active' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' }}">
                                        {{ ucfirst($z->status) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        <button 
                                            type="button" 
                                            @click="openEdit('zone', {{ Js::from($z) }})" 
                                            class="p-1.5 text-muted hover:text-blue-600 dark:hover:text-blue-400 rounded transition-colors" 
                                            title="{{ __('Edit Zone') }}"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" /></svg>
                                        </button>
                                        <form method="POST" action="{{ route('dashboard.locations.toggle', ['type' => 'zone', 'id' => $z->id]) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="p-1.5 text-muted hover:text-slate-900 dark:hover:text-white rounded transition-colors" title="{{ $z->status === 'active' ? __('Deactivate') : __('Activate') }}">
                                                <svg class="w-4 h-4 {{ $z->status === 'active' ? 'text-emerald-600' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5.636 18.364a9 9 0 010-12.728m12.728 0a9 9 0 010 12.728m-9.9-2.829a5 5 0 010-7.07m7.072 0a5 5 0 010 7.07M13 12a1 1 0 11-2 0 1 1 0 012 0z" /></svg>
                                            </button>
                                        </form>
                                        <button 
                                            type="button" 
                                            @click="confirmDelete('zone', '{{ $z->id }}', '{{ addslashes($z->name_en) }}')" 
                                            class="p-1.5 text-muted hover:text-red-600 rounded transition-colors" 
                                            title="{{ __('Delete Zone') }}"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-xs text-muted">
                                    {{ __('No regional zones found matching your criteria.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                @if($zones->hasPages())
                    <div class="p-4 border-t border-warm dark:border-[#273244]">
                        {{ $zones->links() }}
                    </div>
                @endif
            </div>

            {{-- Tab 3: Woredas Table --}}
            <div x-show="currentTab === 'woredas'" class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('Woreda (District)') }}</th>
                            <th>{{ __('Parent Classification') }}</th>
                            <th>{{ __('Region') }}</th>
                            <th>{{ __('Local Translations') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th class="text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-warm dark:divide-[#273244]">
                        @forelse($woredas as $w)
                            <tr class="hover:bg-surface/80 dark:hover:bg-[#172033]/50 transition-colors">
                                <td class="py-3 px-4 font-semibold text-slate-900 dark:text-white text-xs">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full {{ $w->status === 'active' ? 'bg-purple-500' : 'bg-slate-400' }}"></span>
                                        <span>{{ $w->name_en }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-xs">
                                    @if($w->subCity)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-orange-50 text-orange-700 dark:bg-orange-950/40 dark:text-orange-400 font-medium text-xs">
                                            {{ $w->subCity->name_en }} (Sub-City)
                                        </span>
                                    @elseif($w->zone)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400 font-medium text-xs">
                                            {{ $w->zone->name_en }} (Zone)
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-xs text-muted dark:text-dark-subtle">
                                    {{ $w->subCity?->city?->region?->name_en ?? ($w->zone?->region?->name_en ?? 'N/A') }}
                                </td>
                                <td class="py-3 px-4 text-xs text-muted">
                                    @if($w->name_am)<span class="mr-2">{{ $w->name_am }}</span>@endif
                                    @if($w->name_om)<span class="text-muted italic">{{ $w->name_om }}</span>@endif
                                    @if(!$w->name_am && !$w->name_om)—@endif
                                </td>
                                <td class="py-3 px-4 text-xs">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $w->status === 'active' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' }}">
                                        {{ ucfirst($w->status) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        <button 
                                            type="button" 
                                            @click="openEdit('woreda', {{ Js::from($w) }})" 
                                            class="p-1.5 text-muted hover:text-purple-600 dark:hover:text-purple-400 rounded transition-colors" 
                                            title="{{ __('Edit Woreda') }}"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" /></svg>
                                        </button>
                                        <form method="POST" action="{{ route('dashboard.locations.toggle', ['type' => 'woreda', 'id' => $w->id]) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="p-1.5 text-muted hover:text-slate-900 dark:hover:text-white rounded transition-colors" title="{{ $w->status === 'active' ? __('Deactivate') : __('Activate') }}">
                                                <svg class="w-4 h-4 {{ $w->status === 'active' ? 'text-emerald-600' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5.636 18.364a9 9 0 010-12.728m12.728 0a9 9 0 010 12.728m-9.9-2.829a5 5 0 010-7.07m7.072 0a5 5 0 010 7.07M13 12a1 1 0 11-2 0 1 1 0 012 0z" /></svg>
                                            </button>
                                        </form>
                                        <button 
                                            type="button" 
                                            @click="confirmDelete('woreda', '{{ $w->id }}', '{{ addslashes($w->name_en) }}')" 
                                            class="p-1.5 text-muted hover:text-red-600 rounded transition-colors" 
                                            title="{{ __('Delete Woreda') }}"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-xs text-muted">
                                    {{ __('No woredas found matching your criteria.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                @if($woredas->hasPages())
                    <div class="p-4 border-t border-warm dark:border-[#273244]">
                        {{ $woredas->links() }}
                    </div>
                @endif
            </div>
        </x-card>
        </div>

        {{-- Modals --}}

        {{-- 1. Create Sub-City Modal --}}
        <div x-show="showCreateSubCityModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="showCreateSubCityModal = false"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-lg rounded-xl bg-surface dark:bg-slate-900 border border-warm dark:border-[#273244] p-6 shadow-xl">
                    <h3 class="text-base font-semibold text-slate-900 dark:text-white mb-4">{{ __('Add New Sub-City') }}</h3>
                    <form method="POST" action="{{ route('dashboard.locations.subcities.store') }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="form-label text-xs" required>{{ __('City') }}</label>
                            <select name="city_id" class="form-select w-full text-xs" required>
                                <option value="">{{ __('Select City...') }}</option>
                                @foreach($cities as $c)
                                    <option value="{{ $c->id }}">{{ $c->name_en }} ({{ $c->region?->name_en }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label text-xs" required>{{ __('Sub-City Name (English)') }}</label>
                            <input type="text" name="name_en" required class="form-input w-full text-xs" placeholder="e.g. Bole">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="form-label text-xs">{{ __('Amharic Name') }}</label>
                                <input type="text" name="name_am" class="form-input w-full text-xs" placeholder="e.g. ቦሌ">
                            </div>
                            <div>
                                <label class="form-label text-xs">{{ __('Afaan Oromoo Name') }}</label>
                                <input type="text" name="name_om" class="form-input w-full text-xs" placeholder="e.g. Boolee">
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-3 pt-3">
                            <button type="button" @click="showCreateSubCityModal = false" class="btn btn-ghost text-xs">{{ __('Cancel') }}</button>
                            <button type="submit" class="btn btn-primary text-xs">{{ __('Save Sub-City') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- 2. Create Zone Modal --}}
        <div x-show="showCreateZoneModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="showCreateZoneModal = false"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-lg rounded-xl bg-surface dark:bg-slate-900 border border-warm dark:border-[#273244] p-6 shadow-xl">
                    <h3 class="text-base font-semibold text-slate-900 dark:text-white mb-4">{{ __('Add Regional Zone') }}</h3>
                    <form method="POST" action="{{ route('dashboard.locations.zones.store') }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="form-label text-xs" required>{{ __('Region') }}</label>
                            <select name="region_id" class="form-select w-full text-xs" required>
                                <option value="">{{ __('Select Region...') }}</option>
                                @foreach($regions as $r)
                                    <option value="{{ $r->id }}">{{ $r->name_en }} ({{ $r->name_am ?? $r->name_om }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label text-xs" required>{{ __('Zone Name (English)') }}</label>
                            <input type="text" name="name_en" required class="form-input w-full text-xs" placeholder="e.g. East Shewa">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="form-label text-xs">{{ __('Amharic Name') }}</label>
                                <input type="text" name="name_am" class="form-input w-full text-xs" placeholder="e.g. ምስራቅ ሸዋ">
                            </div>
                            <div>
                                <label class="form-label text-xs">{{ __('Afaan Oromoo Name') }}</label>
                                <input type="text" name="name_om" class="form-input w-full text-xs" placeholder="e.g. Shawaa Bahaa">
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-3 pt-3">
                            <button type="button" @click="showCreateZoneModal = false" class="btn btn-ghost text-xs">{{ __('Cancel') }}</button>
                            <button type="submit" class="btn btn-primary text-xs">{{ __('Save Zone') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- 3. Create Woreda Modal --}}
        <div x-show="showCreateWoredaModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" x-data="{ parentType: 'sub_city' }">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="showCreateWoredaModal = false"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-lg rounded-xl bg-surface dark:bg-slate-900 border border-warm dark:border-[#273244] p-6 shadow-xl">
                    <h3 class="text-base font-semibold text-slate-900 dark:text-white mb-4">{{ __('Add New Woreda (District)') }}</h3>
                    <form method="POST" action="{{ route('dashboard.locations.woredas.store') }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="form-label text-xs">{{ __('Parent Entity Type') }}</label>
                            <div class="flex items-center gap-4 mt-1">
                                <label class="inline-flex items-center gap-2 text-xs text-slate-800 dark:text-slate-200 cursor-pointer">
                                    <input type="radio" name="parent_type" value="sub_city" x-model="parentType" class="text-orange-600">
                                    <span>{{ __('Sub-City (Urban)') }}</span>
                                </label>
                                <label class="inline-flex items-center gap-2 text-xs text-slate-800 dark:text-slate-200 cursor-pointer">
                                    <input type="radio" name="parent_type" value="zone" x-model="parentType" class="text-orange-600">
                                    <span>{{ __('Regional Zone') }}</span>
                                </label>
                            </div>
                        </div>

                        <div x-show="parentType === 'sub_city'">
                            <label class="form-label text-xs" required>{{ __('Parent Sub-City') }}</label>
                            <select name="sub_city_id" class="form-select w-full text-xs">
                                <option value="">{{ __('Select Sub-City...') }}</option>
                                @foreach($subCities as $sc)
                                    <option value="{{ $sc->id }}">{{ $sc->name_en }} ({{ $sc->city?->name_en }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div x-show="parentType === 'zone'">
                            <label class="form-label text-xs" required>{{ __('Parent Zone') }}</label>
                            <select name="zone_id" class="form-select w-full text-xs">
                                <option value="">{{ __('Select Zone...') }}</option>
                                @foreach($zones as $z)
                                    <option value="{{ $z->id }}">{{ $z->name_en }} ({{ $z->region?->name_en }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="form-label text-xs" required>{{ __('Woreda Name (English)') }}</label>
                            <input type="text" name="name_en" required class="form-input w-full text-xs" placeholder="e.g. Woreda 03 or Medhanialem">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="form-label text-xs">{{ __('Amharic Name') }}</label>
                                <input type="text" name="name_am" class="form-input w-full text-xs" placeholder="e.g. ወረዳ 03">
                            </div>
                            <div>
                                <label class="form-label text-xs">{{ __('Afaan Oromoo Name') }}</label>
                                <input type="text" name="name_om" class="form-input w-full text-xs" placeholder="e.g. Aanaa 03">
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-3">
                            <button type="button" @click="showCreateWoredaModal = false" class="btn btn-ghost text-xs">{{ __('Cancel') }}</button>
                            <button type="submit" class="btn btn-primary text-xs">{{ __('Save Woreda') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- 4. Unified Edit Modal --}}
        <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="showEditModal = false"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-lg rounded-xl bg-surface dark:bg-slate-900 border border-warm dark:border-[#273244] p-6 shadow-xl">
                    <h3 class="text-base font-semibold text-slate-900 dark:text-white mb-4">
                        <span x-text="editItem.type === 'subcity' ? '{{ __('Edit Sub-City') }}' : (editItem.type === 'zone' ? '{{ __('Edit Zone') }}' : '{{ __('Edit Woreda') }}')"></span>
                    </h3>

                    {{-- Sub-city edit form --}}
                    <form x-show="editItem.type === 'subcity'" method="POST" :action="'/dashboard/locations/sub-cities/' + editItem.id" class="space-y-4">
                        @csrf
                        @method('PUT')
                        <div>
                            <label class="form-label text-xs" required>{{ __('City') }}</label>
                            <select name="city_id" x-model="editItem.city_id" class="form-select w-full text-xs" required>
                                @foreach($cities as $c)
                                    <option value="{{ $c->id }}">{{ $c->name_en }} ({{ $c->region?->name_en }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label text-xs" required>{{ __('Name (English)') }}</label>
                            <input type="text" name="name_en" x-model="editItem.name_en" required class="form-input w-full text-xs">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="form-label text-xs">{{ __('Amharic Name') }}</label>
                                <input type="text" name="name_am" x-model="editItem.name_am" class="form-input w-full text-xs">
                            </div>
                            <div>
                                <label class="form-label text-xs">{{ __('Afaan Oromoo Name') }}</label>
                                <input type="text" name="name_om" x-model="editItem.name_om" class="form-input w-full text-xs">
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-3 pt-3">
                            <button type="button" @click="showEditModal = false" class="btn btn-ghost text-xs">{{ __('Cancel') }}</button>
                            <button type="submit" class="btn btn-primary text-xs">{{ __('Update Sub-City') }}</button>
                        </div>
                    </form>

                    {{-- Zone edit form --}}
                    <form x-show="editItem.type === 'zone'" method="POST" :action="'/dashboard/locations/zones/' + editItem.id" class="space-y-4">
                        @csrf
                        @method('PUT')
                        <div>
                            <label class="form-label text-xs" required>{{ __('Region') }}</label>
                            <select name="region_id" x-model="editItem.region_id" class="form-select w-full text-xs" required>
                                @foreach($regions as $r)
                                    <option value="{{ $r->id }}">{{ $r->name_en }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label text-xs" required>{{ __('Name (English)') }}</label>
                            <input type="text" name="name_en" x-model="editItem.name_en" required class="form-input w-full text-xs">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="form-label text-xs">{{ __('Amharic Name') }}</label>
                                <input type="text" name="name_am" x-model="editItem.name_am" class="form-input w-full text-xs">
                            </div>
                            <div>
                                <label class="form-label text-xs">{{ __('Afaan Oromoo Name') }}</label>
                                <input type="text" name="name_om" x-model="editItem.name_om" class="form-input w-full text-xs">
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-3 pt-3">
                            <button type="button" @click="showEditModal = false" class="btn btn-ghost text-xs">{{ __('Cancel') }}</button>
                            <button type="submit" class="btn btn-primary text-xs">{{ __('Update Zone') }}</button>
                        </div>
                    </form>

                    {{-- Woreda edit form --}}
                    <form x-show="editItem.type === 'woreda'" method="POST" :action="'/dashboard/locations/woredas/' + editItem.id" class="space-y-4">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="parent_type" :value="editItem.parent_type">
                        <div x-show="editItem.parent_type === 'sub_city'">
                            <label class="form-label text-xs">{{ __('Parent Sub-City') }}</label>
                            <select name="sub_city_id" x-model="editItem.sub_city_id" class="form-select w-full text-xs">
                                <option value="">{{ __('Select Sub-City...') }}</option>
                                @foreach($subCities as $sc)
                                    <option value="{{ $sc->id }}">{{ $sc->name_en }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div x-show="editItem.parent_type === 'zone'">
                            <label class="form-label text-xs">{{ __('Parent Zone') }}</label>
                            <select name="zone_id" x-model="editItem.zone_id" class="form-select w-full text-xs">
                                <option value="">{{ __('Select Zone...') }}</option>
                                @foreach($zones as $z)
                                    <option value="{{ $z->id }}">{{ $z->name_en }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label text-xs" required>{{ __('Name (English)') }}</label>
                            <input type="text" name="name_en" x-model="editItem.name_en" required class="form-input w-full text-xs">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="form-label text-xs">{{ __('Amharic Name') }}</label>
                                <input type="text" name="name_am" x-model="editItem.name_am" class="form-input w-full text-xs">
                            </div>
                            <div>
                                <label class="form-label text-xs">{{ __('Afaan Oromoo Name') }}</label>
                                <input type="text" name="name_om" x-model="editItem.name_om" class="form-input w-full text-xs">
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-3 pt-3">
                            <button type="button" @click="showEditModal = false" class="btn btn-ghost text-xs">{{ __('Cancel') }}</button>
                            <button type="submit" class="btn btn-primary text-xs">{{ __('Update Woreda') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- 5. Delete Confirmation Modal --}}
        <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="showDeleteModal = false"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-sm rounded-xl bg-surface dark:bg-slate-900 border border-warm dark:border-[#273244] p-6 shadow-xl text-center">
                    <div class="w-10 h-10 rounded-full bg-red-100 dark:bg-red-950/50 text-red-600 dark:text-red-400 mx-auto flex items-center justify-center mb-3">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
                    </div>
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-1">{{ __('Delete Location Entity?') }}</h3>
                    <p class="text-xs text-muted mb-4">
                        {{ __('Are you sure you want to delete') }} <strong x-text="deleteName"></strong>? {{ __('This item will be soft-deleted.') }}
                    </p>
                    <form method="POST" :action="'/dashboard/locations/' + deleteType + '/' + deleteId">
                        @csrf
                        @method('DELETE')
                        <div class="flex items-center justify-center gap-3">
                            <button type="button" @click="showDeleteModal = false" class="btn btn-ghost text-xs">{{ __('Cancel') }}</button>
                            <button type="submit" class="btn bg-red-600 hover:bg-red-700 text-white text-xs px-4 py-2 rounded-lg cursor-pointer">{{ __('Delete') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
