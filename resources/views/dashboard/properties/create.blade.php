<x-layouts.dashboard>
    <x-slot name="title">{{ __('Add Property') }}</x-slot>

    @section('page-title', __('Add Property'))
    @section('breadcrumb')
        <x-breadcrumb :items="[
            ['label' => __('Dashboard'), 'url' => '/dashboard'],
            ['label' => __('Properties'), 'url' => '/dashboard/properties'],
            ['label' => __('Add New')]
        ]" />
    @endsection

    <div class="max-w-4xl mx-auto page-enter pb-12">
        <x-page-header
            title="Register New Property"
            description="Register a new real estate property with PostGIS coordinates, category specifications, amenities, and title verification documents."
            icon="building"
            badge="Registration" />

        <form action="/dashboard/properties" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            {{-- Basic Information --}}
            <x-card padding="p-6">
                <div class="border-b border-warm dark:border-[#273244] pb-4 mb-5">
                    <h3 class="font-display font-semibold text-lg text-muted dark:text-white">{{ __('Basic Information') }}</h3>
                    <p class="text-sm text-muted dark:text-muted">{{ __('Core details about the property.') }}</p>
                </div>

                <div class="space-y-5">
                    <div>
                        <x-label for="title" required>{{ __('Property Title') }}</x-label>
                        <x-input type="text" id="title" name="title" placeholder="{{ __('e.g. Modern Villa in Bole') }}" class="w-full" required />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <x-label for="type" required>{{ __('Property Type') }}</x-label>
                            <select id="type" name="type" class="form-select w-full">
                                <option value="">{{ __('Select type...') }}</option>
                                <option value="apartment">{{ __('Apartment') }}</option>
                                <option value="villa">{{ __('Villa') }}</option>
                                <option value="condominium">{{ __('Condominium') }}</option>
                                <option value="commercial">{{ __('Commercial Space') }}</option>
                                <option value="land">{{ __('Land') }}</option>
                            </select>
                        </div>
                        <div>
                            <x-label for="status" required>{{ __('Initial Status') }}</x-label>
                            <select id="status" name="status" class="form-select w-full">
                                <option value="draft">{{ __('Draft') }}</option>
                                <option value="available">{{ __('Available') }}</option>
                                <option value="under_construction">{{ __('Under Construction') }}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </x-card>

            {{-- Location --}}
            <x-card padding="p-6">
                <div class="border-b border-warm dark:border-[#273244] pb-4 mb-5">
                    <h3 class="font-display font-semibold text-lg text-muted dark:text-white">{{ __('Location') }}</h3>
                    <p class="text-sm text-muted dark:text-muted">{{ __('Where is this property located?') }}</p>
                </div>
                
                <x-location-picker name="location_id" />

                <div class="mt-5 mb-6">
                    <x-label for="address">{{ __('Street Address') }}</x-label>
                    <x-input type="text" id="address" name="address" placeholder="{{ __('e.g. Rwanda Street, near X') }}" class="w-full" />
                </div>

                {{-- Interactive Map Pin & Coordinates (Phase 10 GIS) --}}
                <div class="pt-4 border-t border-warm dark:border-[#273244]">
                    <div class="mb-3">
                        <x-label>{{ __('GPS Coordinates & Map Pin') }}</x-label>
                        <p class="text-xs text-muted dark:text-muted">{{ __('Click anywhere on the map or drag the pin to set the exact property coordinates.') }}</p>
                    </div>
                    <x-coordinate-picker nameLat="latitude" nameLng="longitude" />
                </div>
            </x-card>

            {{-- Details --}}
            <x-card padding="p-6">
                <div class="border-b border-warm dark:border-[#273244] pb-4 mb-5">
                    <h3 class="font-display font-semibold text-lg text-muted dark:text-white">{{ __('Property Details') }}</h3>
                    <p class="text-sm text-muted dark:text-muted">{{ __('Size and room information.') }}</p>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
                    <div>
                        <x-label for="bedrooms">{{ __('Bedrooms') }}</x-label>
                        <x-input type="number" id="bedrooms" name="bedrooms" min="0" class="w-full" />
                    </div>
                    <div>
                        <x-label for="bathrooms">{{ __('Bathrooms') }}</x-label>
                        <x-input type="number" id="bathrooms" name="bathrooms" min="0" step="0.5" class="w-full" />
                    </div>
                    <div>
                        <x-label for="area">{{ __('Area (m²)') }}</x-label>
                        <x-input type="number" id="area" name="area" min="0" class="w-full" />
                    </div>
                    <div>
                        <x-label for="year_built">{{ __('Year Built') }}</x-label>
                        <x-input type="number" id="year_built" name="year_built" min="1900" max="{{ date('Y') + 5 }}" class="w-full" />
                    </div>
                </div>
            </x-card>

            {{-- Property Media (Phase 09) --}}
            <x-card padding="p-6">
                <div class="border-b border-warm dark:border-[#273244] pb-4 mb-5 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <h3 class="font-display font-semibold text-lg text-muted dark:text-white">{{ __('Property Media & Gallery') }}</h3>
                        <p class="text-sm text-muted dark:text-muted">
                            {{ __('Upload photos, architectural floor plans, and video tours. First photo will be set as cover.') }}
                        </p>
                    </div>
                    <span class="text-xs text-orange-600 dark:text-orange-400 font-medium bg-orange-50 dark:bg-orange-950/30 px-2.5 py-1 rounded-full">
                        {{ __('Up to 20MB per file') }}
                    </span>
                </div>

                <x-media-uploader name="photos" />
            </x-card>

            {{-- Amenities (Phase 08) --}}
            <x-card padding="p-6">
                <div class="border-b border-warm dark:border-[#273244] pb-4 mb-5 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <h3 class="font-display font-semibold text-lg text-muted dark:text-white">{{ __('Amenities') }}</h3>
                        <p class="text-sm text-muted dark:text-muted">{{ __('Select utility, security, indoor, and outdoor amenities available at this property.') }}</p>
                    </div>
                    <a href="/dashboard/amenities" target="_blank" class="text-xs text-orange-600 hover:text-orange-700 dark:text-orange-400 font-medium inline-flex items-center gap-1 flex-shrink-0">
                        <span>{{ __('Manage catalog') }}</span>
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                    </a>
                </div>

                <x-amenity-selector name="amenities" />
            </x-card>

            {{-- Architectural Features & Finishes (Phase 08) --}}
            <x-card padding="p-6">
                <div class="border-b border-warm dark:border-[#273244] pb-4 mb-5 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <h3 class="font-display font-semibold text-lg text-muted dark:text-white">{{ __('Features & Finishes') }}</h3>
                        <p class="text-sm text-muted dark:text-muted">{{ __('Architectural elements, premium materials, and interior finishes.') }}</p>
                    </div>
                    <a href="/dashboard/features" target="_blank" class="text-xs text-orange-600 hover:text-orange-700 dark:text-orange-400 font-medium inline-flex items-center gap-1 flex-shrink-0">
                        <span>{{ __('Manage catalog') }}</span>
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                    </a>
                </div>

                <x-feature-selector name="features" />
            </x-card>

            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="/dashboard/properties" class="btn btn-ghost">{{ __('Cancel') }}</a>
                <x-button type="submit" variant="primary">{{ __('Save Property') }}</x-button>
            </div>
        </form>
    </div>
</x-layouts.dashboard>
