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

    <div class="max-w-4xl mx-auto page-enter pb-12 font-sans" x-data="{
        selectedCategory: '{{ old('category_id', '') }}',
        categories: {{ Js::from($categories ?? []) }},
        types: {{ Js::from($types ?? []) }},
        get filteredTypes() {
            if (!this.selectedCategory) return this.types;
            return this.types.filter(t => !t.category_id || t.category_id === this.selectedCategory);
        }
    }">
        <x-page-header
            title="Register New Property"
            description="Register a new real estate property with PostGIS coordinates, category specifications, amenities, and title verification documents."
            icon="building"
            badge="Registration" />

        @if ($errors->any())
            <div class="mt-4">
                <x-alert type="error" dismissible="true">
                    <ul class="list-disc list-inside space-y-1 text-xs">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </x-alert>
            </div>
        @endif

        <form action="{{ route('dashboard.properties.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6 mt-4">
            @csrf

            {{-- Basic Information --}}
            <x-card padding="p-6">
                <div class="border-b border-warm dark:border-[#273244] pb-4 mb-5">
                    <h3 class="font-display font-semibold text-lg text-slate-900 dark:text-white">{{ __('Basic Information') }}</h3>
                    <p class="text-xs text-muted dark:text-muted">{{ __('Core details and classifications of the property.') }}</p>
                </div>

                <div class="space-y-5">
                    <div>
                        <x-label for="title" required>{{ __('Property Title') }}</x-label>
                        <x-input type="text" id="title" name="title" value="{{ old('title') }}" placeholder="{{ __('e.g. Modern Luxury Villa in Bole') }}" class="w-full" required />
                    </div>

                    <div>
                        <x-label for="description">{{ __('Property Description') }}</x-label>
                        <textarea id="description" name="description" rows="3" placeholder="{{ __('Comprehensive description of the property, architecture, environment, and features...') }}" class="form-input w-full text-sm rounded-lg">{{ old('description') }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div>
                            <x-label for="category_id">{{ __('Property Category') }}</x-label>
                            <select id="category_id" name="category_id" x-model="selectedCategory" class="form-select w-full text-sm">
                                <option value="">{{ __('All Categories (Optional)...') }}</option>
                                @foreach($categories ?? [] as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') === $category->id ? 'selected' : '' }}>
                                        {{ $category->name_en }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <x-label for="type_id" required>{{ __('Property Type') }}</x-label>
                            <select id="type_id" name="type_id" class="form-select w-full text-sm">
                                <option value="">{{ __('Select property type...') }}</option>
                                <template x-for="t in filteredTypes" :key="t.id">
                                    <option :value="t.id" x-text="t.name_en" :selected="t.id === '{{ old('type_id') }}'"></option>
                                </template>
                                @if(empty($types) || count($types) === 0)
                                    <option value="apartment">{{ __('Apartment') }}</option>
                                    <option value="villa">{{ __('Villa') }}</option>
                                    <option value="condominium">{{ __('Condominium') }}</option>
                                    <option value="commercial">{{ __('Commercial Space') }}</option>
                                    <option value="land">{{ __('Land') }}</option>
                                @endif
                            </select>
                        </div>

                        <div>
                            <x-label for="status" required>{{ __('Initial Status') }}</x-label>
                            <select id="status" name="status" class="form-select w-full text-sm">
                                <option value="draft" {{ old('status', 'draft') === 'draft' ? 'selected' : '' }}>{{ __('Draft') }}</option>
                                <option value="available" {{ old('status') === 'available' ? 'selected' : '' }}>{{ __('Available') }}</option>
                                <option value="under_construction" {{ old('status') === 'under_construction' ? 'selected' : '' }}>{{ __('Under Construction') }}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </x-card>

            {{-- Location --}}
            <x-card padding="p-6">
                <div class="border-b border-warm dark:border-[#273244] pb-4 mb-5">
                    <h3 class="font-display font-semibold text-lg text-slate-900 dark:text-white">{{ __('Location & Coordinates') }}</h3>
                    <p class="text-xs text-muted dark:text-muted">{{ __('Cadastral location, physical street address, and interactive GPS pin.') }}</p>
                </div>
                
                <x-location-picker name="location_id" />

                <div class="mt-5 mb-6">
                    <x-label for="address">{{ __('Street Address') }}</x-label>
                    <x-input type="text" id="address" name="address" value="{{ old('address') }}" placeholder="{{ __('e.g. Rwanda Street, Bole Sub-City, Near Edna Mall') }}" class="w-full" />
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

            {{-- Physical Specifications --}}
            <x-card padding="p-6">
                <div class="border-b border-warm dark:border-[#273244] pb-4 mb-5">
                    <h3 class="font-display font-semibold text-lg text-slate-900 dark:text-white">{{ __('Physical Specifications') }}</h3>
                    <p class="text-xs text-muted dark:text-muted">{{ __('Floor areas, room counts, and construction attributes.') }}</p>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
                    <div>
                        <x-label for="bedrooms">{{ __('Bedrooms') }}</x-label>
                        <x-input type="number" id="bedrooms" name="bedrooms" min="0" value="{{ old('bedrooms') }}" class="w-full" placeholder="0" />
                    </div>
                    <div>
                        <x-label for="bathrooms">{{ __('Bathrooms') }}</x-label>
                        <x-input type="number" id="bathrooms" name="bathrooms" min="0" step="0.5" value="{{ old('bathrooms') }}" class="w-full" placeholder="0" />
                    </div>
                    <div>
                        <x-label for="area">{{ __('Area (m²)') }}</x-label>
                        <x-input type="number" id="area" name="area" min="0" value="{{ old('area', old('building_area')) }}" class="w-full" placeholder="e.g. 240" />
                    </div>
                    <div>
                        <x-label for="year_built">{{ __('Year Built') }}</x-label>
                        <x-input type="number" id="year_built" name="year_built" min="1900" max="{{ date('Y') + 5 }}" value="{{ old('year_built', old('construction_year')) }}" class="w-full" placeholder="{{ date('Y') }}" />
                    </div>
                </div>
            </x-card>

            {{-- Property Media (Phase 09) --}}
            <x-card padding="p-6">
                <div class="border-b border-warm dark:border-[#273244] pb-4 mb-5 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <h3 class="font-display font-semibold text-lg text-slate-900 dark:text-white">{{ __('Property Media & Gallery') }}</h3>
                        <p class="text-xs text-muted dark:text-muted">
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
                        <h3 class="font-display font-semibold text-lg text-slate-900 dark:text-white">{{ __('Amenities') }}</h3>
                        <p class="text-xs text-muted dark:text-muted">{{ __('Select utility, security, indoor, and outdoor amenities available at this property.') }}</p>
                    </div>
                    <a href="/dashboard/amenities" target="_blank" class="text-xs text-orange-600 hover:text-orange-700 dark:text-orange-400 font-medium inline-flex items-center gap-1 flex-shrink-0">
                        <span>{{ __('Manage catalog') }}</span>
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                    </a>
                </div>

                <x-amenity-selector name="amenities" :selected="old('amenities', [])" :amenities="$amenities ?? null" />
            </x-card>

            {{-- Architectural Features & Finishes (Phase 08) --}}
            <x-card padding="p-6">
                <div class="border-b border-warm dark:border-[#273244] pb-4 mb-5 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <h3 class="font-display font-semibold text-lg text-slate-900 dark:text-white">{{ __('Features & Finishes') }}</h3>
                        <p class="text-xs text-muted dark:text-muted">{{ __('Architectural elements, premium materials, and interior finishes.') }}</p>
                    </div>
                    <a href="/dashboard/features" target="_blank" class="text-xs text-orange-600 hover:text-orange-700 dark:text-orange-400 font-medium inline-flex items-center gap-1 flex-shrink-0">
                        <span>{{ __('Manage catalog') }}</span>
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                    </a>
                </div>

                <x-feature-selector name="features" :selected="old('features', [])" :features="$features ?? null" />
            </x-card>

            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('dashboard.properties.index') }}" class="btn btn-ghost">{{ __('Cancel') }}</a>
                <x-button type="submit" variant="primary">{{ __('Save Property') }}</x-button>
            </div>
        </form>
    </div>
</x-layouts.dashboard>
