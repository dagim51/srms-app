<x-layouts.dashboard>
    <x-slot name="title">Listing Inspection | Moderation Workbench</x-slot>

    @section('page-title', 'Listing Review')
    @section('breadcrumb')
        <a href="{{ route('dashboard.moderation.listings.index') }}" class="hover:text-muted dark:hover:text-muted">{{ __('Moderation Queue') }}</a>
        <span class="mx-1.5 text-muted dark:text-muted">/</span>
        <span class="text-muted dark:text-dark-subtle">Inspect {{ $listing->property->property_code ?? 'Listing' }}</span>
    @endsection

    @php
        $property = $listing->property;
        $cover = $property && $property->media->first() 
            ? ($property->media->first()->url ?? $property->media->first()->storage_key) 
            : 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=1200&q=80';
        $mediaList = $property ? $property->media : collect();
    @endphp

    <div class="max-w-7xl mx-auto page-enter pb-16 space-y-6" x-data="{ selectedAction: 'approve', reasonText: '' }">
        <x-page-header
            title="Listing Inspection Workbench"
            :description="__('Detailed moderation review, document checks, and approval workbench for') . ' ' . ($listing->property->title ?? 'this listing') . '.'"
            icon="shield"
            badge="Trust & Safety" />

        {{-- Flash Messages --}}
        @if(session('error'))
            <x-alert type="error">
                {{ session('error') }}
            </x-alert>
        @endif

        {{-- Inspection Header --}}
        <div class="card p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <img src="{{ $cover }}" class="w-16 h-16 rounded-xl object-cover shadow-sm flex-shrink-0" alt="">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded text-xs font-mono bg-primary-100 text-primary-800 dark:bg-primary-950/40 dark:text-primary-300 font-bold">
                            {{ $property->property_code ?? 'PROP' }}
                        </span>
                        <h2 class="card-title text-xl">{{ $property->title ?? 'Untitled Property' }}</h2>
                    </div>
                    <p class="card-subtitle text-xs mt-1">
                        Current Status: <span class="font-bold uppercase text-primary-600 dark:text-primary-400">{{ $listing->status }}</span> · 
                        Submitted: {{ $listing->updated_at->diffForHumans() }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('dashboard.listings.preview', $listing) }}" target="_blank" class="btn btn-outline text-xs">{{ __('View Public Card') }}</a>
                <a href="{{ route('dashboard.moderation.listings.index') }}" class="btn btn-ghost text-xs">{{ __('Back to Queue') }}</a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            {{-- LEFT COLUMN: Property & Listing Asset Evidence --}}
            <div class="lg:col-span-8 space-y-6">
                {{-- Media Gallery Preview --}}
                <div class="card overflow-hidden">
                    <div class="p-4 border-b border-[#EBDBC9] dark:border-[#273244] flex items-center justify-between">
                        <h3 class="card-title text-sm">Photographic Media ({{ $mediaList->count() }} Files)</h3>
                        <span class="text-xs text-muted">{{ __('Click image to inspect') }}</span>
                    </div>

                    <div class="relative aspect-video bg-surface">
                        <img src="{{ $cover }}" class="w-full h-full object-cover" alt="">
                        <div class="absolute bottom-3 left-3 bg-black/60 backdrop-blur-sm px-3 py-1 rounded-lg text-white text-xs font-semibold">
                            Primary Cover Photo
                        </div>
                    </div>

                    @if($mediaList->count() > 1)
                        <div class="p-4 grid grid-cols-4 sm:grid-cols-6 gap-2 border-t border-[#EBDBC9] dark:border-[#273244]">
                            @foreach($mediaList as $m)
                                <img src="{{ $m->url ?? $m->storage_key }}" class="h-16 w-full object-cover rounded-lg border border-[#EBDBC9] dark:border-[#273244] hover:opacity-80 cursor-pointer" alt="">
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Property Specifications & Commercial Terms --}}
                <div class="card p-6 md:p-8 space-y-6">
                    <div>
                        <h3 class="card-title text-base mb-4">{{ __('Commercial Terms & Pricing') }}</h3>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                            <div class="card-nested p-3">
                                <span class="card-kv-label">{{ __('Mode') }}</span>
                                <p class="card-kv-value text-sm mt-0.5">{{ $listing->purpose }}</p>
                            </div>
                            <div class="card-nested p-3">
                                <span class="card-kv-label">{{ __('Asking Price') }}</span>
                                <p class="card-kv-value text-primary-600 dark:text-primary-400 text-sm mt-0.5">{{ $listing->formatted_price }}</p>
                            </div>
                            <div class="card-nested p-3">
                                <span class="card-kv-label">{{ __('Currency') }}</span>
                                <p class="card-kv-value text-sm mt-0.5">{{ $listing->currency }}</p>
                            </div>
                            <div class="card-nested p-3">
                                <span class="card-kv-label">{{ __('Visibility') }}</span>
                                <p class="card-kv-value text-sm mt-0.5 capitalize">{{ $listing->visibility }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Canonical Specs --}}
                    <div>
                        <h3 class="card-title text-base mb-4">{{ __('Property Dimensions & Specs') }}</h3>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                            <x-card-stat
                                :label="__('Bedrooms')"
                                :value="$property->bedrooms ?? '-'"
                                iconColor="blue"
                                :badge="__('Rooms')"
                                badgeVariant="info">
                                <x-slot:icon>
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
                                </x-slot:icon>
                            </x-card-stat>
                            <x-card-stat
                                :label="__('Bathrooms')"
                                :value="$property->bathrooms ?? '-'"
                                iconColor="blue"
                                :badge="__('Baths')"
                                badgeVariant="info">
                                <x-slot:icon>
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" /></svg>
                                </x-slot:icon>
                            </x-card-stat>
                            <x-card-stat
                                :label="__('Building Area')"
                                :value="$property->building_area ? $property->building_area . ' m²' : '-'"
                                iconColor="orange"
                                icon="building"
                                :badge="__('Interior')"
                                badgeVariant="brand"
                            />
                            <x-card-stat
                                :label="__('Land Area')"
                                :value="$property->land_area ? $property->land_area . ' m²' : '-'"
                                iconColor="emerald"
                                icon="building"
                                :badge="__('Plot')"
                                badgeVariant="success"
                            />
                        </div>
                    </div>

                    {{-- Description --}}
                    <div>
                        <h3 class="card-title text-base mb-2">{{ __('Description Review') }}</h3>
                        <div class="card-nested p-4 text-xs leading-relaxed whitespace-pre-line text-muted dark:text-dark-subtle">
                            {{ $property->description ?? 'No description provided.' }}
                        </div>
                    </div>

                    {{-- Amenities --}}
                    <div>
                        <h3 class="card-title text-base mb-3">{{ __('Amenities & Features') }}</h3>
                        <div class="flex flex-wrap gap-2 text-xs">
                            @foreach($property->amenities as $am)
                                <span class="px-2.5 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 border border-emerald-100 dark:border-emerald-800/40">
                                    ✓ {{ $am->name }}
                                </span>
                            @endforeach
                            @foreach($property->features as $ft)
                                <span class="px-2.5 py-1 rounded-lg bg-surface-subtle dark:bg-surface text-muted dark:text-dark-subtle">
                                    {{ $ft->name }}
                                </span>
                            @endforeach
                        </div>
                    </div>

                    {{-- Map Location --}}
                    @if($property->latitude && $property->longitude)
                        <div>
                            <h3 class="card-title text-base mb-3">{{ __('GIS Map Location Pin') }}</h3>
                            <x-property-location-map :latitude="$property->latitude" :longitude="$property->longitude" :title="$property->title" />
                        </div>
                    @endif
                </div>
            </div>

            {{-- RIGHT COLUMN: Trust & Safety Decision Console --}}
            <div class="lg:col-span-4 space-y-6">
                {{-- Automated Validation Engine Results --}}
                <div class="card p-5 space-y-3">
                    <h3 class="card-kv-label">{{ __('Automated Integrity System') }}</h3>
                    <div class="space-y-2 text-xs">
                        @foreach($automatedChecks as $check)
                            <div class="p-2.5 rounded-xl border flex items-center justify-between {{ $check['passed'] ? 'bg-emerald-50/40 border-emerald-100 dark:bg-emerald-950/20 dark:border-emerald-900/30' : 'bg-rose-50/40 border-rose-100 dark:bg-rose-950/20 dark:border-rose-900/30' }}">
                                <div>
                                    <p class="font-bold {{ $check['passed'] ? 'text-emerald-900 dark:text-emerald-300' : 'text-rose-900 dark:text-rose-300' }}">
                                        {{ $check['name'] }}
                                    </p>
                                    <p class="text-xs text-muted dark:text-muted">{{ $check['desc'] }}</p>
                                </div>
                                <span class="text-sm">{{ $check['passed'] ? '✅' : '⚠️' }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Quality Score Gauge --}}
                <x-listing-quality :listing="$listing" />

                {{-- MODERATION DECISION PANEL --}}
                <div class="card p-6 space-y-5 border-2 border-primary-500/30">
                    <div class="border-b border-[#EBDBC9] dark:border-[#273244] pb-3">
                        <h3 class="card-title text-base">{{ __('Moderator Decision') }}</h3>
                        <p class="card-subtitle text-xs mt-0.5">{{ __('Select verdict and record justification.') }}</p>
                    </div>

                    <form action="{{ route('dashboard.moderation.listings.decision', $listing) }}" method="POST" class="space-y-4">
                        @csrf

                        {{-- Action Choices --}}
                        <div class="grid grid-cols-3 gap-2">
                            <label @click="selectedAction = 'approve'" :class="{ 'border-emerald-500 bg-emerald-50 dark:bg-emerald-950/30 ring-2 ring-emerald-500/20 text-emerald-800 dark:text-emerald-300': selectedAction === 'approve', 'border-warm dark:border-warm': selectedAction !== 'approve' }"
                                   class="border rounded-xl p-3 text-center cursor-pointer font-bold text-xs transition-all flex flex-col items-center gap-1">
                                <input type="radio" name="action" value="approve" x-model="selectedAction" class="sr-only">
                                <span>✓ Approve</span>
                            </label>

                            <label @click="selectedAction = 'reject'" :class="{ 'border-rose-500 bg-rose-50 dark:bg-rose-950/30 ring-2 ring-rose-500/20 text-rose-800 dark:text-rose-300': selectedAction === 'reject', 'border-warm dark:border-warm': selectedAction !== 'reject' }"
                                   class="border rounded-xl p-3 text-center cursor-pointer font-bold text-xs transition-all flex flex-col items-center gap-1">
                                <input type="radio" name="action" value="reject" x-model="selectedAction" class="sr-only">
                                <span>✕ Reject</span>
                            </label>

                            <label @click="selectedAction = 'suspend'" :class="{ 'border-warm bg-surface-subtle dark:bg-surface text-muted dark:text-muted': selectedAction === 'suspend', 'border-warm dark:border-warm': selectedAction !== 'suspend' }"
                                   class="border rounded-xl p-3 text-center cursor-pointer font-bold text-xs transition-all flex flex-col items-center gap-1">
                                <input type="radio" name="action" value="suspend" x-model="selectedAction" class="sr-only">
                                <span>⏸ Suspend</span>
                            </label>
                        </div>

                        {{-- Rejection Templates (Visible if reject or suspend) --}}
                        <div x-show="selectedAction === 'reject' || selectedAction === 'suspend'" class="space-y-2 pt-2">
                            <label class="block text-xs font-semibold text-muted dark:text-dark-subtle">{{ __('Quick Reason Templates') }}</label>
                            <div class="flex flex-col gap-1.5 text-xs">
                                <button type="button" @click="reasonText = 'Low resolution or insufficient photo gallery. At least 3 high-resolution photos required.'" class="text-left p-2 rounded-lg bg-surface dark:bg-surface hover:bg-surface-subtle text-muted dark:text-dark-subtle">
                                    • Insufficient photo gallery
                                </button>
                                <button type="button" @click="reasonText = 'Asking price is unrealistic for the specified neighborhood and building area.'" class="text-left p-2 rounded-lg bg-surface dark:bg-surface hover:bg-surface-subtle text-muted dark:text-dark-subtle">
                                    • Unrealistic asking price
                                </button>
                                <button type="button" @click="reasonText = 'Incomplete property description. Please outline rooms, finishing, and terms.'" class="text-left p-2 rounded-lg bg-surface dark:bg-surface hover:bg-surface-subtle text-muted dark:text-dark-subtle">
                                    • Incomplete description
                                </button>
                            </div>
                        </div>

                        {{-- Comments Box --}}
                        <div>
                            <label class="block text-xs font-semibold text-muted dark:text-dark-subtle mb-1.5">
                                <span x-text="selectedAction === 'reject' ? 'Mandatory Rejection Explanation' : 'Moderator Notes & Log'"></span>
                            </label>
                            <textarea name="comments" x-model="reasonText" rows="3" class="form-input w-full text-xs" 
                                      :placeholder="selectedAction === 'reject' ? 'Explain what the submitter must fix...' : 'Optional approval audit remarks...'"></textarea>
                        </div>

                        {{-- Submit Button --}}
                        <button type="submit" 
                                :class="{ 'btn-primary': selectedAction === 'approve', 'btn-danger': selectedAction === 'reject', 'btn-secondary': selectedAction === 'suspend' }"
                                class="btn w-full text-xs font-bold py-2.5 shadow-md">
                            <span x-text="selectedAction === 'approve' ? 'Approve & Publish to Marketplace' : (selectedAction === 'reject' ? 'Reject Submission' : 'Suspend Listing')"></span>
                        </button>
                    </form>
                </div>

                {{-- Moderation Audit Log Box --}}
                <div class="card p-5 space-y-3">
                    <h3 class="card-kv-label">{{ __('Audit History') }}</h3>
                    @if($listing->moderations->count() > 0)
                        <div class="space-y-2 text-xs">
                            @foreach($listing->moderations as $ev)
                                <div class="card-nested p-2.5">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-muted dark:text-white capitalize">{{ $ev->action }}</span>
                                        <span class="text-xs text-muted">{{ $ev->created_at->diffForHumans() }}</span>
                                    </div>
                                    @if($ev->comments)
                                        <p class="text-xs text-muted dark:text-dark-subtle mt-1 leading-relaxed">{{ $ev->comments }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-xs text-muted italic">{{ __('No previous decisions recorded.') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layouts.dashboard>
