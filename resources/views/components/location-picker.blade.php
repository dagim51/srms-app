@props([
    'name' => 'location_id',
    'id' => null,
    'value' => null,
    'regionId' => null,
    'zoneId' => null,
    'subCityId' => null,
    'woredaId' => null,
    'required' => false,
    'error' => false,
])

@php
    $id = $id ?? $name;

    $regions = \App\Models\Region::where('status', 'active')
        ->orderBy('name_en')
        ->get(['id', 'name_en', 'name_am', 'name_om']);

    $subCities = \App\Models\SubCity::where('status', 'active')
        ->with('city:id,region_id')
        ->orderBy('name_en')
        ->get()
        ->map(function ($sc) {
            return [
                'id' => $sc->id,
                'type' => 'sub_city',
                'city_id' => $sc->city_id,
                'region_id' => $sc->city?->region_id,
                'name' => $sc->name_en . ($sc->name_am ? ' (' . $sc->name_am . ')' : '') . ' - ' . __('Sub-City'),
                'name_en' => $sc->name_en,
            ];
        });

    $zones = \App\Models\Zone::where('status', 'active')
        ->orderBy('name_en')
        ->get()
        ->map(function ($z) {
            return [
                'id' => $z->id,
                'type' => 'zone',
                'region_id' => $z->region_id,
                'name' => $z->name_en . ($z->name_am ? ' (' . $z->name_am . ')' : '') . ' - ' . __('Zone'),
                'name_en' => $z->name_en,
            ];
        });

    $woredas = \App\Models\Woreda::where('status', 'active')
        ->orderBy('name_en')
        ->get()
        ->map(function ($w) {
            return [
                'id' => $w->id,
                'zone_id' => $w->zone_id,
                'sub_city_id' => $w->sub_city_id,
                'parent_id' => $w->sub_city_id ?: $w->zone_id,
                'name' => $w->name_en . ($w->name_am ? ' (' . $w->name_am . ')' : ''),
                'name_en' => $w->name_en,
            ];
        });

    $initialParent = old('sub_city_id', $subCityId) ?: old('zone_id', $zoneId);
@endphp

<div class="space-y-4" x-data="{
    region: '{{ old('region_id', $regionId ?? '') }}',
    parentUnit: '{{ $initialParent ?? '' }}',
    subCityId: '{{ old('sub_city_id', $subCityId ?? '') }}',
    zoneId: '{{ old('zone_id', $zoneId ?? '') }}',
    woreda: '{{ old('woreda_id', $woredaId ?? '') }}',
    regions: {{ Js::from($regions) }},
    subCities: {{ Js::from($subCities) }},
    zones: {{ Js::from($zones) }},
    woredas: {{ Js::from($woredas) }},

    init() {
        if (this.parentUnit) {
            const match = this.subCities.find(s => s.id === this.parentUnit) || this.zones.find(z => z.id === this.parentUnit);
            if (match && !this.region && match.region_id) {
                this.region = match.region_id;
            }
        }
    },

    get availableParentUnits() {
        if (!this.region) return [];
        const sc = this.subCities.filter(s => s.region_id === this.region);
        const z = this.zones.filter(zn => zn.region_id === this.region);
        return [...sc, ...z];
    },

    get availableWoredas() {
        if (!this.parentUnit) return [];
        return this.woredas.filter(w => w.parent_id === this.parentUnit);
    },

    onParentUnitChange() {
        const selected = this.availableParentUnits.find(u => u.id === this.parentUnit);
        if (selected) {
            if (selected.type === 'sub_city') {
                this.subCityId = selected.id;
                this.zoneId = '';
            } else {
                this.zoneId = selected.id;
                this.subCityId = '';
            }
        } else {
            this.subCityId = '';
            this.zoneId = '';
        }
        this.woreda = '';
    },

    resetBelow(level) {
        if (level === 'region') {
            this.parentUnit = '';
            this.subCityId = '';
            this.zoneId = '';
            this.woreda = '';
        }
    }
}">

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        {{-- Region / City --}}
        <div>
            <label class="form-label text-xs font-semibold">{{ __('Region / Chartered City') }}</label>
            <select x-model="region" @change="resetBelow('region')" class="form-select w-full text-xs rounded-lg {{ $error ? 'input-error' : '' }}">
                <option value="">{{ __('Select Region / City...') }}</option>
                <template x-for="r in regions" :key="r.id">
                    <option :value="r.id" x-text="r.name_en + (r.name_am ? ' (' + r.name_am + ')' : '')"></option>
                </template>
            </select>
        </div>

        {{-- Zone / Sub-city --}}
        <div>
            <label class="form-label text-xs font-semibold">{{ __('Zone / Sub-city') }}</label>
            <select x-model="parentUnit" @change="onParentUnitChange()" x-bind:disabled="!region" class="form-select w-full text-xs rounded-lg disabled:opacity-50 {{ $error ? 'input-error' : '' }}">
                <option value="">{{ __('Select Zone or Sub-City...') }}</option>
                <template x-for="z in availableParentUnits" :key="z.id">
                    <option :value="z.id" x-text="z.name"></option>
                </template>
            </select>
        </div>

        {{-- Woreda --}}
        <div>
            <label class="form-label text-xs font-semibold">{{ __('Woreda (District)') }}</label>
            <select x-model="woreda" x-bind:disabled="!parentUnit" class="form-select w-full text-xs rounded-lg disabled:opacity-50 {{ $error ? 'input-error' : '' }}">
                <option value="">{{ __('Select Woreda...') }}</option>
                <template x-for="w in availableWoredas" :key="w.id">
                    <option :value="w.id" x-text="w.name"></option>
                </template>
            </select>
        </div>
    </div>

    {{-- Form Submission Inputs --}}
    <input type="hidden" name="region_id" :value="region">
    <input type="hidden" name="zone_id" :value="zoneId">
    <input type="hidden" name="sub_city_id" :value="subCityId">
    <input type="hidden" name="woreda_id" :value="woreda">
    <input type="hidden" name="{{ $name }}" id="{{ $id }}" :value="woreda || subCityId || zoneId || region">
</div>
