<x-layouts.dashboard>
    <x-slot name="title">Create Listing | Step-by-Step Editor</x-slot>

    @section('page-title', 'Create Listing')
    @section('breadcrumb')
        <a href="{{ route('dashboard.listings.index') }}" class="hover:text-muted dark:hover:text-muted">{{ __('Listings') }}</a>
        <span class="mx-1.5 text-muted dark:text-muted">/</span>
        <span class="text-muted dark:text-dark-subtle">{{ __('New Listing') }}</span>
    @endsection

    @php
        $initialPropertyId = $selectedProperty ? $selectedProperty->id : ($properties->first() ? $properties->first()->id : '');
        $propsJson = $properties->map(function ($p) {
            $cover = $p->media->first() ? ($p->media->first()->url ?? $p->media->first()->storage_key) : 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=800&q=80';
            return [
                'id' => $p->id,
                'title' => $p->title,
                'code' => $p->property_code,
                'description' => $p->description,
                'bedrooms' => $p->bedrooms,
                'bathrooms' => $p->bathrooms,
                'area' => $p->building_area ?? $p->land_area ?? 0,
                'lat' => $p->latitude,
                'lng' => $p->longitude,
                'cover' => $cover,
                'media_count' => $p->media->count(),
                'amenities_count' => $p->amenities->count(),
                'features_count' => $p->features->count(),
            ];
        });
    @endphp

    <div class="max-w-6xl mx-auto page-enter pb-16" 
         x-data="{
            step: 1,
            properties: {{ json_encode($propsJson) }},
            selectedPropId: '{{ $initialPropertyId }}',
            purpose: 'For Sale',
            price: 15000000,
            currency: 'ETB',
            visibility: 'public',
            headline: '',
            description: '',
            isNegotiable: false,
            rentFrequency: 'Monthly',
            depositMonths: 2,
            minLeaseMonths: 12,
            cleaningFee: 0,
            privacyMode: 'exact',

            get currentProperty() {
                return this.properties.find(p => p.id === this.selectedPropId) || null;
            },

            get formattedPrice() {
                return this.currency + ' ' + Number(this.price || 0).toLocaleString();
            },

            get qualityScore() {
                let score = 0;
                if (this.price > 0) score += 20;
                if (this.purpose) score += 15;
                if (this.currentProperty) {
                    if (this.currentProperty.title && this.currentProperty.title.length >= 10) score += 10;
                    if (this.description && this.description.length >= 40) score += 10;
                    else if (this.currentProperty.description && this.currentProperty.description.length >= 40) score += 10;
                    
                    if (this.currentProperty.media_count >= 3) score += 20;
                    else if (this.currentProperty.media_count > 0) score += 10;

                    if (this.currentProperty.lat && this.currentProperty.lng) score += 15;
                    if (this.currentProperty.bedrooms > 0 || this.currentProperty.area > 0) score += 10;
                }
                return Math.min(100, score);
            },

            get qualityTier() {
                if (this.qualityScore >= 80) return { label: 'Excellent', class: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-400' };
                if (this.qualityScore >= 50) return { label: 'Good', class: 'bg-amber-100 text-amber-800 dark:bg-amber-950/40 dark:text-amber-400' };
                return { label: 'Needs Work', class: 'bg-rose-100 text-rose-800 dark:bg-rose-950/40 dark:text-rose-400' };
            },

            setStep(s) {
                if (s >= 1 && s <= 9) this.step = s;
            }
         }">

        {{-- Standardized Page Header Card --}}
        <x-page-header
            title="Publish New Listing"
            description="Publish inventory properties to the marketplace with purpose types, pricing tiers, and inspection dates."
            icon="tag"
            badge="Publisher" />

        {{-- Step Navigation Indicator --}}
        <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-2xl p-4 shadow-sm mb-6">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h2 class="text-base font-bold text-muted dark:text-white">{{ __('Listing Editor Flow') }}</h2>
                    <p class="text-xs text-muted dark:text-muted">Step <span x-text="step"></span> of 9</p>
                </div>
                {{-- Live Quality Badge --}}
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-muted dark:text-muted">Quality:</span>
                    <div class="w-16 bg-surface-subtle dark:bg-surface h-2 rounded-full overflow-hidden">
                        <div class="bg-primary-500 h-full transition-all duration-300" :style="'width: ' + qualityScore + '%'"></div>
                    </div>
                    <span class="text-xs font-bold font-mono text-primary-600 dark:text-primary-400" x-text="qualityScore + '%'"></span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold" :class="qualityTier.class" x-text="qualityTier.label"></span>
                </div>
            </div>

            <div class="grid grid-cols-3 sm:grid-cols-5 md:grid-cols-9 gap-1.5 text-center text-xs">
                @php
                    $steps = [
                        1 => '1. Property',
                        2 => '2. Mode',
                        3 => '3. Pricing',
                        4 => '4. Description',
                        5 => '5. Media',
                        6 => '6. Features',
                        7 => '7. Location',
                        8 => '8. Visibility',
                        9 => '9. Preview',
                    ];
                @endphp
                @foreach($steps as $num => $label)
                    <button type="button" @click="setStep({{ $num }})" 
                            :class="{ 
                                'bg-[#C2410C] text-white font-bold shadow-md shadow-orange-500/20': step === {{ $num }},
                                'bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 font-medium': step > {{ $num }},
                                'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700': step < {{ $num }}
                            }"
                            class="py-2.5 px-2 rounded-xl transition-all truncate text-xs min-h-[44px] flex items-center justify-center cursor-pointer">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        <form action="{{ route('dashboard.listings.store') }}" method="POST" id="listingForm">
            @csrf

            <input type="hidden" name="property_id" :value="selectedPropId">
            <input type="hidden" name="purpose" :value="purpose">
            <input type="hidden" name="price" :value="price">
            <input type="hidden" name="currency" :value="currency">
            <input type="hidden" name="visibility" :value="visibility">

            {{-- STEP 1: PROPERTY SELECTION --}}
            <div x-show="step === 1" class="space-y-6">
                <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-2xl p-6 shadow-sm">
                    <div class="border-b border-warm dark:border-warm pb-4 mb-5">
                        <h3 class="font-display font-bold text-lg text-muted dark:text-white">{{ __('1. Link Canonical Property') }}</h3>
                        <p class="text-xs text-muted dark:text-muted mt-1">{{ __('Listings are commercial offers attached directly to an existing real estate record. No duplicate property record will be created.') }}</p>
                    </div>

                    @if($properties->count() > 0)
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-muted dark:text-dark-subtle mb-1.5">{{ __('Select from Your Properties') }}</label>
                                <select x-model="selectedPropId" class="form-input w-full text-sm">
                                    <template x-for="p in properties" :key="p.id">
                                        <option :value="p.id" x-text="p.title + ' (' + p.code + ')'"></option>
                                    </template>
                                </select>
                            </div>

                            {{-- Canonical Property Preview Card --}}
                            <template x-if="currentProperty">
                                <div class="bg-surface dark:bg-surface/50 border border-warm dark:border-warm rounded-xl p-4 flex flex-col sm:flex-row items-start sm:items-center gap-4 mt-4">
                                    <img :src="currentProperty.cover" class="w-full sm:w-28 h-24 rounded-lg object-cover flex-shrink-0" alt="">
                                    <div class="flex-1 space-y-1">
                                        <div class="flex items-center gap-2">
                                            <span class="px-2 py-0.5 rounded text-xs font-mono bg-primary-100 text-primary-800 dark:bg-primary-950/40 dark:text-primary-300 font-bold" x-text="currentProperty.code"></span>
                                            <h4 class="font-bold text-sm text-muted dark:text-white" x-text="currentProperty.title"></h4>
                                        </div>
                                        <p class="text-xs text-muted dark:text-muted line-clamp-1" x-text="currentProperty.description"></p>
                                        <div class="flex items-center gap-3 text-xs text-muted dark:text-dark-subtle pt-1">
                                            <span x-text="currentProperty.bedrooms + ' Beds'"></span> •
                                            <span x-text="currentProperty.bathrooms + ' Baths'"></span> •
                                            <span x-text="currentProperty.area + ' m²'"></span> •
                                            <span x-text="currentProperty.media_count + ' Photos'"></span>
                                        </div>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <a :href="'/dashboard/properties/' + currentProperty.id + '/media'" target="_blank" class="btn btn-outline text-xs">{{ __('Manage Media') }}</a>
                                    </div>
                                </div>
                            </template>
                        </div>
                    @else
                        <div class="p-6 text-center bg-surface dark:bg-surface/40 rounded-xl">
                            <p class="text-sm text-muted dark:text-muted mb-3">{{ __('You don\'t have any canonical properties yet.') }}</p>
                            <a href="{{ url('/dashboard/properties/create') }}" class="btn btn-primary text-xs">{{ __('Create Property First') }}</a>
                        </div>
                    @endif
                </div>
            </div>

            {{-- STEP 2: LISTING MODE --}}
            <div x-show="step === 2" class="space-y-6">
                <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-2xl p-6 shadow-sm">
                    <div class="border-b border-warm dark:border-warm pb-4 mb-5">
                        <h3 class="font-display font-bold text-lg text-muted dark:text-white">{{ __('2. Listing Mode & Transaction Intent') }}</h3>
                        <p class="text-xs text-muted dark:text-muted mt-1">{{ __('Select the transaction format for this commercial offer.') }}</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        {{-- For Sale --}}
                        <div @click="purpose = 'For Sale'" 
                             :class="{ 'border-primary-500 bg-primary-50/30 dark:bg-primary-950/20 ring-2 ring-primary-500': purpose === 'For Sale', 'border-warm dark:border-warm hover:border-warm': purpose !== 'For Sale' }"
                             class="border rounded-2xl p-5 cursor-pointer transition-all flex flex-col justify-between">
                            <div>
                                <div class="w-10 h-10 rounded-xl bg-primary-100 dark:bg-primary-950/50 text-primary-600 dark:text-primary-400 flex items-center justify-center mb-3">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                                </div>
                                <h4 class="font-bold text-muted dark:text-white text-base">{{ __('For Sale') }}</h4>
                                <p class="text-xs text-muted dark:text-muted mt-1">{{ __('Outright ownership transfer, title deed assignment, mortgage or cash purchase.') }}</p>
                            </div>
                            <span class="mt-4 text-xs font-bold text-primary-600" x-show="purpose === 'For Sale'">✓ Selected</span>
                        </div>

                        {{-- For Rent --}}
                        <div @click="purpose = 'For Rent'" 
                             :class="{ 'border-primary-500 bg-primary-50/30 dark:bg-primary-950/20 ring-2 ring-primary-500': purpose === 'For Rent', 'border-warm dark:border-warm hover:border-warm': purpose !== 'For Rent' }"
                             class="border rounded-2xl p-5 cursor-pointer transition-all flex flex-col justify-between">
                            <div>
                                <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 flex items-center justify-center mb-3">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                </div>
                                <h4 class="font-bold text-muted dark:text-white text-base">{{ __('For Rent') }}</h4>
                                <p class="text-xs text-muted dark:text-muted mt-1">{{ __('Long-term residential or commercial tenancy with periodic lease agreements.') }}</p>
                            </div>
                            <span class="mt-4 text-xs font-bold text-primary-600" x-show="purpose === 'For Rent'">✓ Selected</span>
                        </div>

                        {{-- Short Stay --}}
                        <div @click="purpose = 'Short Stay'" 
                             :class="{ 'border-primary-500 bg-primary-50/30 dark:bg-primary-950/20 ring-2 ring-primary-500': purpose === 'Short Stay', 'border-warm dark:border-warm hover:border-warm': purpose !== 'Short Stay' }"
                             class="border rounded-2xl p-5 cursor-pointer transition-all flex flex-col justify-between">
                            <div>
                                <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-950/50 text-purple-600 dark:text-purple-400 flex items-center justify-center mb-3">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" /></svg>
                                </div>
                                <h4 class="font-bold text-muted dark:text-white text-base">{{ __('Short Stay / Vacation') }}</h4>
                                <p class="text-xs text-muted dark:text-muted mt-1">{{ __('Nightly or weekly booking for tourists, expats, and business travelers.') }}</p>
                            </div>
                            <span class="mt-4 text-xs font-bold text-primary-600" x-show="purpose === 'Short Stay'">✓ Selected</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- STEP 3: PRICING & TERMS --}}
            <div x-show="step === 3" class="space-y-6">
                <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-2xl p-6 shadow-sm">
                    <div class="border-b border-warm dark:border-warm pb-4 mb-5 flex items-center justify-between">
                        <div>
                            <h3 class="font-display font-bold text-lg text-muted dark:text-white">{{ __('3. Commercial Pricing & Terms') }}</h3>
                            <p class="text-xs text-muted dark:text-muted mt-1">{{ __('Define asking amount and payment conditions.') }}</p>
                        </div>
                        <div class="text-right">
                            <span class="text-xs text-muted">Preview:</span>
                            <p class="font-display font-bold text-lg text-primary-600 dark:text-primary-400" x-text="formattedPrice"></p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        {{-- Currency --}}
                        <div>
                            <label class="block text-xs font-semibold text-muted dark:text-dark-subtle mb-1.5">{{ __('Currency') }}</label>
                            <select x-model="currency" class="form-input w-full text-sm">
                                <option value="ETB">ETB - Ethiopian Birr (Default)</option>
                                <option value="USD">{{ __('USD - US Dollar') }}</option>
                                <option value="EUR">{{ __('EUR - Euro') }}</option>
                            </select>
                        </div>

                        {{-- Base Amount --}}
                        <div>
                            <label class="block text-xs font-semibold text-muted dark:text-dark-subtle mb-1.5">
                                Asking Amount (<span x-text="currency"></span>)
                            </label>
                            <input type="number" x-model.number="price" min="0" step="1000" class="form-input w-full text-sm font-semibold" required>
                        </div>
                    </div>

                    {{-- Conditional Terms based on Purpose --}}
                    <div class="mt-6 pt-5 border-t border-warm dark:border-warm">
                        <template x-if="purpose === 'For Sale'">
                            <div class="space-y-4">
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" id="negCheck" x-model="isNegotiable" class="rounded text-primary-600 focus:ring-primary-500">
                                    <label for="negCheck" class="text-xs text-muted dark:text-dark-subtle font-medium">{{ __('Price is Negotiable') }}</label>
                                </div>
                                <template x-if="currentProperty && currentProperty.area > 0">
                                    <div class="bg-surface dark:bg-surface/50 p-3 rounded-xl flex items-center justify-between text-xs">
                                        <span class="text-muted">Calculated Rate per m²:</span>
                                        <span class="font-bold text-muted dark:text-white" x-text="currency + ' ' + Math.round(price / currentProperty.area).toLocaleString() + ' / m²'"></span>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <template x-if="purpose === 'For Rent'">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-muted dark:text-dark-subtle mb-1">{{ __('Billing Cycle') }}</label>
                                    <select x-model="rentFrequency" class="form-input w-full text-xs">
                                        <option value="Monthly">{{ __('Monthly') }}</option>
                                        <option value="Quarterly">Quarterly (3 Months)</option>
                                        <option value="Bi-Annually">Bi-Annually (6 Months)</option>
                                        <option value="Annually">Annually (1 Year Advance)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-muted dark:text-dark-subtle mb-1">{{ __('Security Deposit') }}</label>
                                    <select x-model="depositMonths" class="form-input w-full text-xs">
                                        <option value="1">{{ __('1 Month Rent') }}</option>
                                        <option value="2">{{ __('2 Months Rent') }}</option>
                                        <option value="3">{{ __('3 Months Rent') }}</option>
                                        <option value="0">{{ __('No Deposit') }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-muted dark:text-dark-subtle mb-1">{{ __('Min. Lease Duration') }}</label>
                                    <select x-model="minLeaseMonths" class="form-input w-full text-xs">
                                        <option value="6">{{ __('6 Months') }}</option>
                                        <option value="12">12 Months (1 Year)</option>
                                        <option value="24">24 Months (2 Years)</option>
                                    </select>
                                </div>
                            </div>
                        </template>

                        <template x-if="purpose === 'Short Stay'">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-muted dark:text-dark-subtle mb-1">Cleaning Fee (<span x-text="currency"></span>)</label>
                                    <input type="number" x-model.number="cleaningFee" min="0" class="form-input w-full text-xs" placeholder="{{ __('e.g. 1500') }}">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-muted dark:text-dark-subtle mb-1">Minimum Stay (Nights)</label>
                                    <input type="number" value="1" min="1" class="form-input w-full text-xs">
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            {{-- STEP 4: DESCRIPTION & MARKETING --}}
            <div x-show="step === 4" class="space-y-6">
                <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-2xl p-6 shadow-sm">
                    <div class="border-b border-warm dark:border-warm pb-4 mb-5">
                        <h3 class="font-display font-bold text-lg text-muted dark:text-white">{{ __('4. Marketing Headline & Description') }}</h3>
                        <p class="text-xs text-muted dark:text-muted mt-1">{{ __('Craft engaging copywriting that highlights key selling points.') }}</p>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-muted dark:text-dark-subtle mb-1.5">Custom Marketing Headline (Optional)</label>
                            <input type="text" x-model="headline" name="marketing_headline" class="form-input w-full text-sm" placeholder="{{ __('e.g. Rare Prime Bole Atlas Corner Villa with Unobstructed Views') }}">
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-semibold text-muted dark:text-dark-subtle">{{ __('Detailed Marketplace Description') }}</label>
                                <span class="text-xs text-muted" x-text="(description.length || (currentProperty ? currentProperty.description.length : 0)) + ' characters'"></span>
                            </div>
                            <textarea x-model="description" name="notes" rows="6" class="form-input w-full text-xs leading-relaxed" 
                                      :placeholder="currentProperty ? currentProperty.description : 'Describe the architectural highlights, natural lighting, neighborhood proximity...'"></textarea>
                            <p class="text-xs text-muted mt-1">{{ __('Tip: Mention proximity to international schools, embassies, or shopping malls to attract prime tenants.') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- STEP 5: MEDIA REVIEW --}}
            <div x-show="step === 5" class="space-y-6">
                <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-2xl p-6 shadow-sm">
                    <div class="border-b border-warm dark:border-warm pb-4 mb-5 flex items-center justify-between">
                        <div>
                            <h3 class="font-display font-bold text-lg text-muted dark:text-white">{{ __('5. Property Media & Photography') }}</h3>
                            <p class="text-xs text-muted dark:text-muted mt-1">{{ __('Review the gallery photos linked from the canonical property asset.') }}</p>
                        </div>
                        <template x-if="currentProperty">
                            <a :href="'/dashboard/properties/' + currentProperty.id + '/media'" target="_blank" class="btn btn-primary text-xs">{{ __('Upload / Organize Media') }}</a>
                        </template>
                    </div>

                    <template x-if="currentProperty">
                        <div>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                <div class="relative aspect-video rounded-xl overflow-hidden border-2 border-primary-500 shadow-sm">
                                    <img :src="currentProperty.cover" class="w-full h-full object-cover" alt="">
                                    <span class="absolute bottom-2 left-2 px-2 py-0.5 rounded text-xs font-bold bg-primary-600 text-white">{{ __('Cover Photo') }}</span>
                                </div>
                            </div>
                            <p class="text-xs text-muted dark:text-muted mt-3">
                                Canonical property has <span class="font-bold text-muted dark:text-white" x-text="currentProperty.media_count"></span> photo(s). 
                                High resolution photos provide a +20% boost to your listing quality score.
                            </p>
                        </div>
                    </template>
                </div>
            </div>

            {{-- STEP 6: FEATURES & AMENITIES --}}
            <div x-show="step === 6" class="space-y-6">
                <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-2xl p-6 shadow-sm">
                    <div class="border-b border-warm dark:border-warm pb-4 mb-5">
                        <h3 class="font-display font-bold text-lg text-muted dark:text-white">{{ __('6. Features & Amenities Verification') }}</h3>
                        <p class="text-xs text-muted dark:text-muted mt-1">{{ __('These amenities will be displayed on the public marketplace card.') }}</p>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                        <div class="p-3 rounded-xl bg-surface dark:bg-surface/50 flex items-center gap-2 border border-warm dark:border-warm">
                            <span class="text-emerald-500">✓</span> Backup Generator
                        </div>
                        <div class="p-3 rounded-xl bg-surface dark:bg-surface/50 flex items-center gap-2 border border-warm dark:border-warm">
                            <span class="text-emerald-500">✓</span> Reserve Water Tank
                        </div>
                        <div class="p-3 rounded-xl bg-surface dark:bg-surface/50 flex items-center gap-2 border border-warm dark:border-warm">
                            <span class="text-emerald-500">✓</span> 24/7 Gated Security
                        </div>
                        <div class="p-3 rounded-xl bg-surface dark:bg-surface/50 flex items-center gap-2 border border-warm dark:border-warm">
                            <span class="text-emerald-500">✓</span> Dedicated Parking
                        </div>
                    </div>
                </div>
            </div>

            {{-- STEP 7: LOCATION & PRIVACY --}}
            <div x-show="step === 7" class="space-y-6">
                <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-2xl p-6 shadow-sm">
                    <div class="border-b border-warm dark:border-warm pb-4 mb-5">
                        <h3 class="font-display font-bold text-lg text-muted dark:text-white">{{ __('7. Location & Map Privacy') }}</h3>
                        <p class="text-xs text-muted dark:text-muted mt-1">{{ __('Control how precise the map marker appears to public marketplace visitors.') }}</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div @click="privacyMode = 'exact'" 
                             :class="{ 'border-primary-500 bg-primary-50/20 ring-2 ring-primary-500': privacyMode === 'exact', 'border-warm dark:border-warm': privacyMode !== 'exact' }"
                             class="border rounded-xl p-4 cursor-pointer">
                            <h4 class="font-bold text-xs text-muted dark:text-white">{{ __('Exact Pin Location') }}</h4>
                            <p class="text-xs text-muted dark:text-muted mt-1">{{ __('Displays the exact GPS coordinate pin on search map results.') }}</p>
                        </div>

                        <div @click="privacyMode = 'approximate'" 
                             :class="{ 'border-primary-500 bg-primary-50/20 ring-2 ring-primary-500': privacyMode === 'approximate', 'border-warm dark:border-warm': privacyMode !== 'approximate' }"
                             class="border rounded-xl p-4 cursor-pointer">
                            <h4 class="font-bold text-xs text-muted dark:text-white">Approximate (~400m Privacy Circle)</h4>
                            <p class="text-xs text-muted dark:text-muted mt-1">{{ __('Shows general neighborhood circle to protect owner privacy until an inspection viewing is booked.') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- STEP 8: VISIBILITY & SUBMISSION --}}
            <div x-show="step === 8" class="space-y-6">
                <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-2xl p-6 shadow-sm">
                    <div class="border-b border-warm dark:border-warm pb-4 mb-5">
                        <h3 class="font-display font-bold text-lg text-muted dark:text-white">{{ __('8. Marketplace Visibility & Schedule') }}</h3>
                        <p class="text-xs text-muted dark:text-muted mt-1">{{ __('Determine public discoverability and expiration settings.') }}</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-semibold text-muted dark:text-dark-subtle mb-1.5">{{ __('Visibility Mode') }}</label>
                            <select x-model="visibility" name="visibility" class="form-input w-full text-xs">
                                <option value="public">Public (Visible in search engines & marketplace)</option>
                                <option value="private">Private (Only visible to authenticated clients)</option>
                                <option value="unlisted">Unlisted (Accessible only via direct link)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-muted dark:text-dark-subtle mb-1.5">Listing Expiration Date (Optional)</label>
                            <input type="date" name="expires_at" class="form-input w-full text-xs">
                        </div>
                    </div>
                </div>
            </div>

            {{-- STEP 9: LIVE PREVIEW & FINAL CONFIRMATION --}}
            <div x-show="step === 9" class="space-y-6">
                <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-2xl p-6 shadow-sm space-y-6">
                    <div class="border-b border-warm dark:border-warm pb-4">
                        <h3 class="font-display font-bold text-lg text-[#0F172A] dark:text-white">{{ __('9. Final Marketplace Preview') }}</h3>
                        <p class="text-xs text-[#4B5563] dark:text-[#CBD5E1] mt-1">{{ __('Review how your listing appears to buyers and tenants before publishing.') }}</p>
                    </div>

                    {{-- Card Simulation --}}
                    <div class="max-w-sm mx-auto bg-white dark:bg-[#0F172A] border border-warm dark:border-warm rounded-2xl overflow-hidden shadow-lg">
                        <div class="relative aspect-[4/3] bg-surface-subtle dark:bg-surface">
                            <img :src="currentProperty ? currentProperty.cover : ''" class="w-full h-full object-cover" alt="">
                            <div class="absolute top-3 left-3 flex gap-1.5">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-primary-600 text-white shadow-sm" x-text="purpose"></span>
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-500 text-white shadow-sm">{{ __('Ready') }}</span>
                            </div>
                            <div class="absolute bottom-0 inset-x-0 p-4 bg-gradient-to-t from-black/80 via-black/40 to-transparent">
                                <p class="font-display font-extrabold text-white text-xl" x-text="formattedPrice"></p>
                            </div>
                        </div>
                        <div class="p-5">
                            <h4 class="font-bold text-[#0F172A] dark:text-white text-base" x-text="headline || (currentProperty ? currentProperty.title : 'Property Title')"></h4>
                            <p class="text-xs text-[#4B5563] dark:text-[#CBD5E1] mt-1 line-clamp-2" x-text="description || (currentProperty ? currentProperty.description : '')"></p>
                            <div class="flex items-center gap-3 text-xs text-[#6B7280] dark:text-[#94A3B8] mt-4 pt-3 border-t border-warm dark:border-warm" x-show="currentProperty">
                                <span x-text="currentProperty ? currentProperty.bedrooms + ' Beds' : ''"></span> •
                                <span x-text="currentProperty ? currentProperty.bathrooms + ' Baths' : ''"></span> •
                                <span x-text="currentProperty ? currentProperty.area + ' m²' : ''"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Wizard Navigation Footer --}}
            <div class="flex items-center justify-between pt-6 border-t border-warm dark:border-warm">
                <button type="button" @click="if(step > 1) step--" x-show="step > 1" class="btn btn-ghost text-xs flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                    Previous Step
                </button>
                <div x-show="step === 1"></div>

                <div class="flex items-center gap-3">
                    <button type="submit" name="action" value="draft" class="btn btn-outline text-xs">{{ __('Save as Draft') }}</button>

                    <template x-if="step < 9">
                        <button type="button" @click="step++" class="btn btn-primary text-xs flex items-center gap-1.5">
                            Continue
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </button>
                    </template>

                    <template x-if="step === 9">
                        <button type="submit" name="action" value="publish" class="btn btn-primary text-xs flex items-center gap-1.5 font-bold shadow-md">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            Publish Listing to Marketplace
                        </button>
                    </template>
                </div>
            </div>
        </form>
    </div>
</x-layouts.dashboard>
