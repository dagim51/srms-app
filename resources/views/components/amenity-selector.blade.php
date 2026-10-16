@props([
    'name' => 'amenities',
    'selected' => [],
    'amenities' => null,
])

@php
    $catalog = $amenities ?? \App\Models\Amenity::where('status', 'active')->orderBy('category')->orderBy('name_en')->get();
    
    // Normalize initial selected items
    $initialSelected = [];
    if (is_array($selected)) {
        foreach ($selected as $key => $val) {
            if (is_array($val) && isset($val['id'])) {
                $initialSelected[] = [
                    'id' => (string) $val['id'],
                    'value' => $val['value'] ?? '',
                ];
            } elseif (is_numeric($key) && is_string($val)) {
                $initialSelected[] = [
                    'id' => (string) $val,
                    'value' => '',
                ];
            } elseif (is_string($key)) {
                $initialSelected[] = [
                    'id' => (string) $key,
                    'value' => is_string($val) ? $val : '',
                ];
            }
        }
    } elseif ($selected instanceof \Illuminate\Support\Collection) {
        foreach ($selected as $item) {
            $initialSelected[] = [
                'id' => (string) $item->id,
                'value' => $item->pivot->value ?? '',
            ];
        }
    }

    $catalogData = $catalog->map(function ($a) {
        return [
            'id' => (string) $a->id,
            'code' => $a->code,
            'name' => $a->name_en,
            'category' => $a->category ?? 'General',
        ];
    })->values();

    $categories = $catalog->pluck('category')->unique()->filter()->values();
@endphp

<div x-data="{
    search: '',
    activeCategory: 'all',
    catalog: {{ Js::from($catalogData) }},
    selectedMap: {},
    
    init() {
        const initial = {{ Js::from($initialSelected) }};
        initial.forEach(item => {
            this.selectedMap[item.id] = { id: item.id, value: item.value || '' };
        });
    },

    isSelected(id) {
        return Boolean(this.selectedMap[id]);
    },

    toggle(id) {
        if (this.selectedMap[id]) {
            delete this.selectedMap[id];
        } else {
            this.selectedMap[id] = { id: id, value: '' };
        }
        // Trigger reactivity
        this.selectedMap = Object.assign({}, this.selectedMap);
    },

    remove(id) {
        delete this.selectedMap[id];
        this.selectedMap = Object.assign({}, this.selectedMap);
    },

    clearAll() {
        this.selectedMap = {};
    },

    updateValue(id, val) {
        if (this.selectedMap[id]) {
            this.selectedMap[id].value = val;
            this.selectedMap = Object.assign({}, this.selectedMap);
        }
    },

    get selectedList() {
        return Object.values(this.selectedMap).map(s => {
            const match = this.catalog.find(c => c.id === s.id);
            return {
                id: s.id,
                name: match ? match.name : s.id,
                category: match ? match.category : '',
                value: s.value
            };
        });
    },

    get filteredCatalog() {
        return this.catalog.filter(item => {
            const matchesSearch = this.search.trim() === '' || 
                item.name.toLowerCase().includes(this.search.toLowerCase()) ||
                item.category.toLowerCase().includes(this.search.toLowerCase());
            
            const matchesCategory = this.activeCategory === 'all' || item.category === this.activeCategory;

            return matchesSearch && matchesCategory;
        });
    },

    get countByCategory() {
        const counts = {};
        this.catalog.forEach(item => {
            counts[item.category] = (counts[item.category] || 0) + 1;
        });
        return counts;
    }
}" class="space-y-4 font-sans">

    {{-- Hidden form inputs for form submit --}}
    <template x-for="(item, idx) in selectedList" :key="item.id">
        <div>
            <input type="hidden" :name="'{{ $name }}[' + idx + '][id]'" :value="item.id">
            <input type="hidden" :name="'{{ $name }}[' + idx + '][value]'" :value="item.value">
        </div>
    </template>

    {{-- Filter & Search Header --}}
    <div class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-center justify-between">
        <div class="relative flex-1 max-w-md">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-muted dark:text-muted">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
            </div>
            <input 
                type="text" 
                x-model="search" 
                placeholder="{{ __('Search amenities (e.g. Generator, Pool, CCTV)...') }}" 
                class="form-input pl-10 pr-9 w-full text-sm rounded-lg"
            >
            <button 
                type="button" 
                x-show="search.length > 0" 
                @click="search = ''" 
                class="absolute inset-y-0 right-0 pr-3 flex items-center text-muted hover:text-muted dark:hover:text-muted"
                x-cloak
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <div class="flex items-center gap-2 text-xs text-muted dark:text-muted self-end sm:self-center">
            <span>Selected:</span>
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-orange-100 dark:bg-orange-950/40 text-orange-700 dark:text-orange-400" x-text="selectedList.length">0</span>
            <button 
                type="button" 
                x-show="selectedList.length > 0" 
                @click="clearAll()" 
                class="ml-1 text-red-500 hover:text-red-700 dark:hover:text-red-400 underline font-medium cursor-pointer"
                x-cloak
            >
                Clear all
            </button>
        </div>
    </div>

    {{-- Category Pills Bar --}}
    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-thin">
        <button 
            type="button" 
            @click="activeCategory = 'all'" 
            class="px-3 py-1.5 rounded-lg text-xs font-medium whitespace-nowrap transition-colors flex items-center gap-1.5 cursor-pointer"
            :class="activeCategory === 'all' 
                ? 'bg-orange-600 text-white shadow-sm shadow-orange-600/30' 
                : 'bg-surface-subtle hover:bg-surface-subtle dark:bg-surface dark:hover:bg-surface text-muted dark:text-dark-subtle'"
        >
            <span>{{ __('All Categories') }}</span>
            <span class="text-xs px-1.5 py-0.2 rounded-full" 
                  :class="activeCategory === 'all' ? 'bg-orange-700/60 text-white' : 'bg-surface-subtle dark:bg-surface text-muted dark:text-dark-subtle'"
                  x-text="catalog.length"></span>
        </button>

        @foreach($categories as $category)
            <button 
                type="button" 
                @click="activeCategory = '{{ $category }}'" 
                class="px-3 py-1.5 rounded-lg text-xs font-medium whitespace-nowrap transition-colors flex items-center gap-1.5 cursor-pointer"
                :class="activeCategory === '{{ $category }}' 
                    ? 'bg-orange-600 text-white shadow-sm shadow-orange-600/30' 
                    : 'bg-surface-subtle hover:bg-surface-subtle dark:bg-surface dark:hover:bg-surface text-muted dark:text-dark-subtle'"
            >
                <span>{{ $category }}</span>
                <span class="text-xs px-1.5 py-0.2 rounded-full"
                      :class="activeCategory === '{{ $category }}' ? 'bg-orange-700/60 text-white' : 'bg-surface-subtle dark:bg-surface text-muted dark:text-dark-subtle'"
                      x-text="countByCategory['{{ $category }}'] || 0"></span>
            </button>
        @endforeach
    </div>

    {{-- Amenities Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2.5 max-h-80 overflow-y-auto p-1 border border-warm dark:border-[#273244] rounded-xl bg-surface/50 dark:bg-[#0B0F19]/40">
        <template x-for="item in filteredCatalog" :key="item.id">
            <div 
                @click="toggle(item.id)"
                class="flex items-center justify-between p-3 rounded-lg border cursor-pointer transition-all duration-150 select-none group"
                :class="isSelected(item.id) 
                    ? 'bg-orange-50/90 dark:bg-orange-950/30 border-orange-400 dark:border-orange-500/60 text-muted dark:text-white shadow-xs' 
                    : 'bg-white dark:bg-[#111827] border-warm dark:border-[#273244] text-muted dark:text-dark-subtle hover:border-warm dark:hover:border-warm'"
            >
                <div class="flex items-center gap-2.5 min-w-0 pr-2">
                    <div 
                        class="w-5 h-5 rounded flex items-center justify-center transition-colors flex-shrink-0"
                        :class="isSelected(item.id) 
                            ? 'bg-orange-600 text-white' 
                            : 'border border-warm dark:border-warm group-hover:border-orange-400 text-transparent'"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold truncate leading-tight" x-text="item.name"></p>
                        <span class="text-xs text-muted dark:text-muted truncate block mt-0.5" x-text="item.category"></span>
                    </div>
                </div>

                <span 
                    x-show="isSelected(item.id)" 
                    class="badge badge-brand text-xs px-1.5 py-0 border-none bg-orange-100 dark:bg-orange-500/20 text-orange-600 dark:text-orange-400 flex-shrink-0"
                    x-cloak
                >{{ __('Included') }}</span>
            </div>
        </template>

        {{-- Empty Search Results --}}
        <div x-show="filteredCatalog.length === 0" class="col-span-full py-8 text-center text-muted dark:text-muted" x-cloak>
            <svg class="w-8 h-8 mx-auto text-muted dark:text-muted mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
            </svg>
            <p class="text-sm font-medium">No amenities match "<span x-text="search"></span>"</p>
            <p class="text-xs text-muted mt-1">{{ __('Try a different keyword or check the administration catalog.') }}</p>
        </div>
    </div>

    {{-- Selected Items Detail & Optional Specifications --}}
    <div x-show="selectedList.length > 0" class="border border-orange-200 dark:border-orange-900/40 rounded-xl p-3.5 bg-orange-50/40 dark:bg-orange-950/20 space-y-3" x-cloak>
        <div class="flex items-center justify-between">
            <h4 class="text-xs font-semibold text-muted dark:text-white uppercase tracking-wider flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-orange-500"></span>
                Configured Amenities (<span x-text="selectedList.length"></span>)
            </h4>
            <span class="text-xs text-muted dark:text-muted">Add optional specifications (e.g. capacity or count)</span>
        </div>

        <div class="flex flex-wrap gap-2">
            <template x-for="item in selectedList" :key="item.id">
                <div class="inline-flex items-center gap-1.5 bg-white dark:bg-[#111827] border border-orange-200 dark:border-orange-800/60 rounded-lg px-2.5 py-1 text-xs text-muted dark:text-muted shadow-2xs">
                    <span class="font-medium" x-text="item.name"></span>
                    
                    {{-- Optional quick value field --}}
                    <input 
                        type="text" 
                        placeholder="{{ __('Spec / count') }}" 
                        :value="item.value" 
                        @input="updateValue(item.id, $event.target.value)" 
                        @click.stop
                        class="w-24 text-xs py-0.5 px-1.5 border border-warm dark:border-warm rounded bg-surface dark:bg-[#172033] focus:border-orange-500 focus:outline-none"
                    >

                    <button 
                        type="button" 
                        @click="remove(item.id)" 
                        class="text-muted hover:text-red-500 ml-1 p-0.5 transition-colors cursor-pointer"
                        title="Remove amenity"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </template>
        </div>
    </div>

</div>
