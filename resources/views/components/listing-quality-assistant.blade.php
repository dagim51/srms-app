@props([
    'titleField' => 'title',
    'descriptionField' => 'description',
    'priceField' => 'price',
    'initialTitle' => '',
    'initialDesc' => '',
    'initialPrice' => null,
    'initialPhotosCount' => 0,
    'initialAmenitiesCount' => 0,
    'hasCadastral' => false,
])

<div x-data="listingQualityAssistant({
        title: '{{ addslashes($initialTitle) }}',
        description: '{{ addslashes($initialDesc) }}',
        price: {{ (float) ($initialPrice ?: 0) }},
        photosCount: {{ (int) $initialPhotosCount }},
        amenitiesCount: {{ (int) $initialAmenitiesCount }},
        hasCadastral: {{ $hasCadastral ? 'true' : 'false' }}
    })"
    class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm transition-all"
    @listing-updated.window="handleExternalUpdate($event.detail)">

    <!-- Score Header -->
    <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
        <div class="flex items-center gap-3">
            <!-- Circular Score Gauge -->
            <div class="relative w-14 h-14 flex items-center justify-center">
                <svg class="w-14 h-14 transform -rotate-90" viewBox="0 0 36 36">
                    <path class="text-slate-100 dark:text-slate-800" stroke-width="3.5" stroke="currentColor" fill="none"
                          d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                    <path :class="scoreColorClass" :stroke-dasharray="totalScore + ', 100'" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none"
                          d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                </svg>
                <span class="absolute font-mono font-bold text-xs" :class="scoreColorClass" x-text="totalScore + '%'"></span>
            </div>

            <div>
                <div class="flex items-center gap-2">
                    <h4 class="text-sm font-bold text-slate-900 dark:text-white">
                        {{ __('Live Listing Quality Score') }}
                    </h4>
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider"
                          :class="tierBadgeClass"
                          x-text="tierName">
                    </span>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    <span x-text="tierRecommendation"></span>
                </p>
            </div>
        </div>

        <button type="button" 
                @click="expanded = !expanded"
                class="text-xs font-semibold text-amber-600 dark:text-amber-400 hover:underline flex items-center gap-1 focus:outline-none">
            <span x-text="expanded ? 'Hide Checklist' : 'Improve Score (+Tips)'"></span>
            <svg class="w-4 h-4 transform transition-transform" :class="{ 'rotate-180': expanded }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
    </div>

    <!-- Collapsible Checklist with Dynamic Progress -->
    <div x-show="expanded" x-collapse class="mt-4 pt-1 space-y-2.5">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
            
            <!-- 1. Descriptive Title -->
            <div class="flex items-start gap-2.5 p-2.5 rounded-xl border transition-all"
                 :class="titlePassed ? 'bg-emerald-50/40 dark:bg-emerald-950/20 border-emerald-200 dark:border-emerald-800/40 text-emerald-800 dark:text-emerald-300' : 'bg-slate-50 dark:bg-slate-800/40 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400'">
                <div class="mt-0.5">
                    <template x-if="titlePassed">
                        <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    </template>
                    <template x-if="!titlePassed">
                        <div class="w-4 h-4 rounded-full border-2 border-slate-300 dark:border-slate-600"></div>
                    </template>
                </div>
                <div>
                    <div class="font-semibold">{{ __('Informative Title (+15%)') }}</div>
                    <div class="text-[11px] opacity-80">{{ __('Clear property type and location headline (&ge;10 chars).') }}</div>
                </div>
            </div>

            <!-- 2. Detailed Description -->
            <div class="flex items-start gap-2.5 p-2.5 rounded-xl border transition-all"
                 :class="descPassed ? 'bg-emerald-50/40 dark:bg-emerald-950/20 border-emerald-200 dark:border-emerald-800/40 text-emerald-800 dark:text-emerald-300' : 'bg-slate-50 dark:bg-slate-800/40 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400'">
                <div class="mt-0.5">
                    <template x-if="descPassed">
                        <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    </template>
                    <template x-if="!descPassed">
                        <div class="w-4 h-4 rounded-full border-2 border-slate-300 dark:border-slate-600"></div>
                    </template>
                </div>
                <div>
                    <div class="font-semibold">{{ __('Detailed Story (+20%)') }}</div>
                    <div class="text-[11px] opacity-80">{{ __('Describe neighborhood perks & utilities (&ge;150 chars).') }}</div>
                </div>
            </div>

            <!-- 3. Photo Gallery -->
            <div class="flex items-start gap-2.5 p-2.5 rounded-xl border transition-all"
                 :class="photosPassed ? 'bg-emerald-50/40 dark:bg-emerald-950/20 border-emerald-200 dark:border-emerald-800/40 text-emerald-800 dark:text-emerald-300' : 'bg-slate-50 dark:bg-slate-800/40 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400'">
                <div class="mt-0.5">
                    <template x-if="photosPassed">
                        <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    </template>
                    <template x-if="!photosPassed">
                        <div class="w-4 h-4 rounded-full border-2 border-slate-300 dark:border-slate-600"></div>
                    </template>
                </div>
                <div>
                    <div class="font-semibold">{{ __('HD Photos (+20%)') }}</div>
                    <div class="text-[11px] opacity-80">{{ __('Upload 4+ interior and exterior angles.') }}</div>
                </div>
            </div>

            <!-- 4. Ethiopian Cadastral / Carta Parcel ID -->
            <div class="flex items-start gap-2.5 p-2.5 rounded-xl border transition-all"
                 :class="hasCadastral ? 'bg-emerald-50/40 dark:bg-emerald-950/20 border-emerald-200 dark:border-emerald-800/40 text-emerald-800 dark:text-emerald-300' : 'bg-slate-50 dark:bg-slate-800/40 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400'">
                <div class="mt-0.5">
                    <template x-if="hasCadastral">
                        <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    </template>
                    <template x-if="!hasCadastral">
                        <div class="w-4 h-4 rounded-full border-2 border-slate-300 dark:border-slate-600"></div>
                    </template>
                </div>
                <div>
                    <div class="font-semibold">{{ __('Cadastral Parcel / Carta (+15%)') }}</div>
                    <div class="text-[11px] opacity-80">{{ __('Provide parcel ID or deed reference to boost buyer trust.') }}</div>
                </div>
            </div>

            <!-- 5. Realistic Pricing -->
            <div class="flex items-start gap-2.5 p-2.5 rounded-xl border transition-all"
                 :class="pricePassed ? 'bg-emerald-50/40 dark:bg-emerald-950/20 border-emerald-200 dark:border-emerald-800/40 text-emerald-800 dark:text-emerald-300' : 'bg-slate-50 dark:bg-slate-800/40 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400'">
                <div class="mt-0.5">
                    <template x-if="pricePassed">
                        <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    </template>
                    <template x-if="!pricePassed">
                        <div class="w-4 h-4 rounded-full border-2 border-slate-300 dark:border-slate-600"></div>
                    </template>
                </div>
                <div>
                    <div class="font-semibold">{{ __('Valid Pricing (+15%)') }}</div>
                    <div class="text-[11px] opacity-80">{{ __('Specify transparent purchase or rent amount.') }}</div>
                </div>
            </div>

            <!-- 6. Key Amenities -->
            <div class="flex items-start gap-2.5 p-2.5 rounded-xl border transition-all"
                 :class="amenitiesPassed ? 'bg-emerald-50/40 dark:bg-emerald-950/20 border-emerald-200 dark:border-emerald-800/40 text-emerald-800 dark:text-emerald-300' : 'bg-slate-50 dark:bg-slate-800/40 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400'">
                <div class="mt-0.5">
                    <template x-if="amenitiesPassed">
                        <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    </template>
                    <template x-if="!amenitiesPassed">
                        <div class="w-4 h-4 rounded-full border-2 border-slate-300 dark:border-slate-600"></div>
                    </template>
                </div>
                <div>
                    <div class="font-semibold">{{ __('Core Amenities (+15%)') }}</div>
                    <div class="text-[11px] opacity-80">{{ __('Select backup generator, water reserve, security, etc.') }}</div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('listingQualityAssistant', (config) => ({
        title: config.title || '',
        description: config.description || '',
        price: config.price || 0,
        photosCount: config.photosCount || 0,
        amenitiesCount: config.amenitiesCount || 0,
        hasCadastral: config.hasCadastral || false,
        expanded: false,

        init() {
            // Listen to standard input changes if on same form
            const titleEl = document.querySelector('input[name="title"]');
            if (titleEl) {
                titleEl.addEventListener('input', (e) => this.title = e.target.value);
            }
            const descEl = document.querySelector('textarea[name="description"]');
            if (descEl) {
                descEl.addEventListener('input', (e) => this.description = e.target.value);
            }
            const priceEl = document.querySelector('input[name="price"]');
            if (priceEl) {
                priceEl.addEventListener('input', (e) => this.price = parseFloat(e.target.value) || 0);
            }
            const parcelEl = document.querySelector('input[name="cadastral_parcel_id"]');
            if (parcelEl) {
                parcelEl.addEventListener('input', (e) => this.hasCadastral = (e.target.value.trim().length > 3));
            }
        },

        handleExternalUpdate(detail) {
            if (detail.title !== undefined) this.title = detail.title;
            if (detail.description !== undefined) this.description = detail.description;
            if (detail.price !== undefined) this.price = detail.price;
            if (detail.photosCount !== undefined) this.photosCount = detail.photosCount;
            if (detail.amenitiesCount !== undefined) this.amenitiesCount = detail.amenitiesCount;
            if (detail.hasCadastral !== undefined) this.hasCadastral = detail.hasCadastral;
        },

        get titlePassed() {
            return this.title.trim().length >= 10;
        },

        get descPassed() {
            return this.description.trim().length >= 150;
        },

        get photosPassed() {
            return this.photosCount >= 4;
        },

        get pricePassed() {
            return this.price > 1000;
        },

        get amenitiesPassed() {
            return this.amenitiesCount >= 3;
        },

        get totalScore() {
            let s = 0;
            if (this.titlePassed) s += 15;
            if (this.descPassed) s += 20;
            if (this.photosPassed) s += 20;
            if (this.hasCadastral) s += 15;
            if (this.pricePassed) s += 15;
            if (this.amenitiesPassed) s += 15;
            return Math.min(100, s);
        },

        get tierName() {
            if (this.totalScore >= 80) return 'Top Tier (Excellent)';
            if (this.totalScore >= 50) return 'Good Quality';
            return 'Needs Polish';
        },

        get tierBadgeClass() {
            if (this.totalScore >= 80) return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-400';
            if (this.totalScore >= 50) return 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-400';
            return 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-400';
        },

        get scoreColorClass() {
            if (this.totalScore >= 80) return 'text-emerald-500';
            if (this.totalScore >= 50) return 'text-amber-500';
            return 'text-rose-500';
        },

        get tierRecommendation() {
            if (this.totalScore >= 80) return 'Outstanding! This listing is primed for search spotlight and high inquiry volume.';
            if (this.totalScore >= 50) return 'Good foundation. Adding photos or deed reference will push it to Top Tier.';
            return 'Complete key details below to ensure search visibility and avoid listing rejection.';
        }
    }));
});
</script>
