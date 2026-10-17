@props([
    'name',
    'id' => null,
    'value' => null,
    'required' => false,
    'error' => false,
])

@php
    $id = $id ?? $name;
@endphp

<div class="space-y-4" x-data="{
    region: '',
    zone: '',
    woreda: '',
    search: '',
    open: false,
    regions: [
        { id: '1', name: 'Addis Ababa', localName: 'አዲስ አበባ' },
        { id: '2', name: 'Oromia', localName: 'Oromiyaa' },
        { id: '3', name: 'Amhara', localName: 'አማራ' },
    ],
    zones: [
        { id: '1-1', region_id: '1', name: 'Bole', localName: 'ቦሌ' },
        { id: '1-2', region_id: '1', name: 'Kirkos', localName: 'ቂርቆስ' },
        { id: '2-1', region_id: '2', name: 'Shewa', localName: 'Shawaa' },
    ],
    woredas: [
        { id: '1-1-1', zone_id: '1-1', name: 'Woreda 03', localName: 'ወረዳ 03' },
        { id: '1-1-2', zone_id: '1-1', name: 'Woreda 04', localName: 'ወረዳ 04' },
    ],
    
    get availableZones() {
        if (!this.region) return [];
        return this.zones.filter(z => z.region_id === this.region);
    },
    
    get availableWoredas() {
        if (!this.zone) return [];
        return this.woredas.filter(w => w.zone_id === this.zone);
    },
    
    resetBelow(level) {
        if (level === 'region') {
            this.zone = '';
            this.woreda = '';
        } else if (level === 'zone') {
            this.woreda = '';
        }
    }
}">

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        {{-- Region --}}
        <div>
            <label class="form-label text-xs">{{ __('Region / City') }}</label>
            <select x-model="region" @change="resetBelow('region')" class="form-select w-full {{ $error ? 'input-error' : '' }}">
                <option value="">{{ __('Select Region...') }}</option>
                <template x-for="r in regions" :key="r.id">
                    <option :value="r.id" x-text="r.name + ' (' + r.localName + ')'"></option>
                </template>
            </select>
        </div>

        {{-- Zone / Sub-city --}}
        <div>
            <label class="form-label text-xs">{{ __('Zone / Sub-city') }}</label>
            <select x-model="zone" @change="resetBelow('zone')" x-bind:disabled="!region" class="form-select w-full disabled:opacity-50 {{ $error ? 'input-error' : '' }}">
                <option value="">{{ __('Select Zone...') }}</option>
                <template x-for="z in availableZones" :key="z.id">
                    <option :value="z.id" x-text="z.name + ' (' + z.localName + ')'"></option>
                </template>
            </select>
        </div>

        {{-- Woreda --}}
        <div>
            <label class="form-label text-xs">{{ __('Woreda') }}</label>
            <select x-model="woreda" x-bind:disabled="!zone" class="form-select w-full disabled:opacity-50 {{ $error ? 'input-error' : '' }}">
                <option value="">{{ __('Select Woreda...') }}</option>
                <template x-for="w in availableWoredas" :key="w.id">
                    <option :value="w.id" x-text="w.name + ' (' + w.localName + ')'"></option>
                </template>
            </select>
        </div>
    </div>
    
    {{-- Hidden input for form submission (combining the values or storing final leaf ID) --}}
    <input type="hidden" name="{{ $name }}" id="{{ $id }}" :value="woreda || zone || region">
</div>
