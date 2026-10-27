@props([
    'name' => 'features',
    'selected' => [],
    'features' => null,
])

@php
    $catalog = $features ?? \App\Models\PropertyFeature::where('status', 'active')->orderBy('name_en')->get();

    // Normalize initial selected
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

    $catalogData = $catalog->map(function ($f) {
        return [
            'id' => (string) $f->id,
            'code' => $f->code,
            'name' => $f->name_en,
        ];
    })->values();
@endphp

<div x-data="{
    search: '',
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
                value: s.value
            };
        });
    },

    get filteredCatalog() {
        if (!this.search.trim()) return this.catalog;
        const q = this.search.toLowerCase();
        return this.catalog.filter(item => item.name.toLowerCase().includes(q) || item.code.toLowerCase().includes(q));
    }
}" class="space-y-4 font-sans">

    {{-- Hidden form inputs for submission --}}
    <template x-for="(item, idx) in selectedList" :key="item.id">
        <div>
            <input type="hidden" :name="'{{ $name }}[' + idx + '][id]'" :value="item.id">
            <input type="hidden" :name="'{{ $name }}[' + idx + '][value]'" :value="item.value">
        </div>
    </template>

    {{-- Search & Controls Header --}}
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
                placeholder="{{ __('Search features (e.g. Marble, Ceilings, Solar)...') }}" 
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

    {{-- Interactive Feature Badges / Chips Grid --}}
    <div class="card flex flex-wrap gap-2 max-h-64 overflow-y-auto p-2 border border-warm dark:border-[#273244] rounded-xl bg-surface/50 dark:bg-[#0B0F19]/40">
        <template x-for="item in filteredCatalog" :key="item.id">
            <button 
                type="button"
                @click="toggle(item.id)"
                class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-medium border transition-all cursor-pointer select-none group"
                :class="isSelected(item.id) 
                    ? 'bg-orange-600 border-orange-600 text-white shadow-sm shadow-orange-600/30' 
                    : 'bg-white dark:bg-[#111827] border-warm dark:border-[#273244] text-muted dark:text-dark-subtle hover:border-orange-300 dark:hover:border-orange-500/40'"
            >
                <div 
                    class="w-4 h-4 rounded-full flex items-center justify-center transition-colors flex-shrink-0"
                    :class="isSelected(item.id) 
                        ? 'bg-white/20 text-white' 
                        : 'border border-warm dark:border-warm group-hover:border-orange-400 text-transparent'"
                >
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                </div>
                <span x-text="item.name"></span>
            </button>
        </template>

        {{-- Empty Search Results --}}
        <div x-show="filteredCatalog.length === 0" class="w-full py-6 text-center text-muted dark:text-muted" x-cloak>
            <p class="text-sm font-medium">No features match "<span x-text="search"></span>"</p>
            <p class="text-xs text-muted mt-1">{{ __('Try another search or manage features in the catalog.') }}</p>
        </div>
    </div>

    {{-- Selected Features Custom Specifications --}}
    <div x-show="selectedList.length > 0" class="border border-orange-200 dark:border-orange-900/40 rounded-xl p-3.5 bg-orange-50/40 dark:bg-orange-950/20 space-y-3" x-cloak>
        <div class="flex items-center justify-between">
            <h4 class="text-xs font-semibold text-muted dark:text-white uppercase tracking-wider flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-orange-500"></span>
                Selected Features & Custom Specifications (<span x-text="selectedList.length"></span>)
            </h4>
            <span class="text-xs text-muted dark:text-muted">Optional notes (e.g. brand, material, finish)</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
            <template x-for="item in selectedList" :key="item.id">
                <div class="card flex items-center justify-between gap-2 bg-white dark:bg-[#111827] border border-orange-200 dark:border-orange-800/60 rounded-lg p-2 text-xs -2xs">
                    <span class="font-medium text-muted dark:text-white truncate flex-1" x-text="item.name"></span>
                    
                    <input 
                        type="text" 
                        placeholder="{{ __('Material / brand spec') }}" 
                        :value="item.value" 
                        @input="updateValue(item.id, $event.target.value)" 
                        class="w-36 text-xs py-1 px-2 border border-warm dark:border-warm rounded bg-surface dark:bg-[#172033] focus:border-orange-500 focus:outline-none"
                    >

                    <button 
                        type="button" 
                        @click="remove(item.id)" 
                        class="text-muted hover:text-red-500 p-1 transition-colors flex-shrink-0 cursor-pointer"
                        title="Remove feature"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </template>
        </div>
    </div>

</div>
