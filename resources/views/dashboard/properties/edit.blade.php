<x-layouts.dashboard>
    <x-slot name="title">{{ __('Edit Property') }} - {{ $property->title }}</x-slot>

    @section('page-title', __('Edit Property'))
    @section('breadcrumb')
        <x-breadcrumb :items="[
            ['label' => __('Dashboard'), 'url' => '/dashboard'],
            ['label' => __('Properties'), 'url' => '/dashboard/properties'],
            ['label' => $property->title, 'url' => '/dashboard/properties/' . $property->id . '/edit'],
            ['label' => __('Edit')]
        ]" />
    @endsection

    <div class="max-w-4xl mx-auto page-enter pb-12 font-sans" x-data="{
        selectedCategory: '{{ old('category_id', $property->category_id ?? '') }}',
        categories: {{ Js::from($categories ?? []) }},
        types: {{ Js::from($types ?? []) }},
        get filteredTypes() {
            if (!this.selectedCategory) return this.types;
            return this.types.filter(t => !t.category_id || t.category_id === this.selectedCategory);
        }
    }">
        <x-page-header
            :title="__('Edit Property') . ': ' . $property->title"
            :description="__('Update real estate property details, category classifications, amenities, and geographic coordinates.')"
            icon="building"
            :badge="$property->property_code">
            <x-slot:actions>
                <a href="{{ route('dashboard.properties.media', $property->id) }}" class="btn btn-outline text-xs">
                    <svg class="w-4 h-4 mr-1 text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    {{ __('Media Gallery') }} ({{ $property->media->count() }})
                </a>
            </x-slot:actions>
        </x-page-header>

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

        <form action="{{ route('dashboard.properties.update', $property->id) }}" method="POST" class="space-y-6 mt-4">
            @csrf
            @method('PUT')

            {{-- Basic Information --}}
            <x-card padding="p-6">
                <div class="border-b border-warm dark:border-[#273244] pb-4 mb-5">
                    <h3 class="font-display font-semibold text-lg text-slate-900 dark:text-white">{{ __('Basic Information') }}</h3>
                    <p class="text-xs text-muted dark:text-muted">{{ __('Core details and classifications of the property.') }}</p>
                </div>

                <div class="space-y-5">
                    <div>
                        <x-label for="title" required>{{ __('Property Title') }}</x-label>
                        <x-input type="text" id="title" name="title" value="{{ old('title', $property->title) }}" class="w-full" required />
                    </div>

                    <div>
                        <x-label for="description">{{ __('Property Description') }}</x-label>
                        <textarea id="description" name="description" rows="3" class="form-input w-full text-sm rounded-lg">{{ old('description', $property->description) }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div>
                            <x-label for="category_id">{{ __('Property Category') }}</x-label>
                            <select id="category_id" name="category_id" x-model="selectedCategory" class="form-select w-full text-sm">
                                <option value="">{{ __('All Categories (Optional)...') }}</option>
                                @foreach($categories ?? [] as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $property->category_id) === $category->id ? 'selected' : '' }}>
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
                                    <option :value="t.id" x-text="t.name_en" :selected="t.id === '{{ old('type_id', $property->type_id) }}'"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <x-label for="status" required>{{ __('Status') }}</x-label>
                            <select id="status" name="status" class="form-select w-full text-sm">
                                <option value="draft" {{ old('status', $property->status) === 'draft' ? 'selected' : '' }}>{{ __('Draft') }}</option>
                                <option value="available" {{ old('status', $property->status) === 'available' ? 'selected' : '' }}>{{ __('Available') }}</option>
                                <option value="under_construction" {{ old('status', $property->status) === 'under_construction' ? 'selected' : '' }}>{{ __('Under Construction') }}</option>
                                <option value="verified" {{ old('status', $property->status) === 'verified' ? 'selected' : '' }}>{{ __('Verified') }}</option>
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

                <div class="mb-5">
                    <x-label for="address">{{ __('Street Address') }}</x-label>
                    <x-input type="text" id="address" name="address" value="{{ old('address', $property->address) }}" class="w-full" />
                </div>

                <div class="pt-4 border-t border-warm dark:border-[#273244]">
                    <div class="mb-3">
                        <x-label>{{ __('GPS Coordinates & Map Pin') }}</x-label>
                        <p class="text-xs text-muted dark:text-muted">{{ __('Click anywhere on the map or drag the pin to set the exact property coordinates.') }}</p>
                    </div>
                    <x-coordinate-picker nameLat="latitude" nameLng="longitude" :initialLat="$property->latitude" :initialLng="$property->longitude" />
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
                        <x-input type="number" id="bedrooms" name="bedrooms" min="0" value="{{ old('bedrooms', $property->bedrooms) }}" class="w-full" />
                    </div>
                    <div>
                        <x-label for="bathrooms">{{ __('Bathrooms') }}</x-label>
                        <x-input type="number" id="bathrooms" name="bathrooms" min="0" step="0.5" value="{{ old('bathrooms', $property->bathrooms) }}" class="w-full" />
                    </div>
                    <div>
                        <x-label for="building_area">{{ __('Building Area (m²)') }}</x-label>
                        <x-input type="number" id="building_area" name="building_area" min="0" value="{{ old('building_area', $property->building_area) }}" class="w-full" />
                    </div>
                    <div>
                        <x-label for="land_area">{{ __('Land Area (m²)') }}</x-label>
                        <x-input type="number" id="land_area" name="land_area" min="0" value="{{ old('land_area', $property->land_area) }}" class="w-full" />
                    </div>
                </div>
            </x-card>

            {{-- Amenities (Phase 08) --}}
            <x-card padding="p-6">
                <div class="border-b border-warm dark:border-[#273244] pb-4 mb-5 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <h3 class="font-display font-semibold text-lg text-slate-900 dark:text-white">{{ __('Amenities') }}</h3>
                        <p class="text-xs text-muted dark:text-muted">{{ __('Select utility, security, indoor, and outdoor amenities available at this property.') }}</p>
                    </div>
                </div>

                <x-amenity-selector name="amenities" :selected="$property->amenities" :amenities="$amenities ?? null" />
            </x-card>

            {{-- Architectural Features & Finishes (Phase 08) --}}
            <x-card padding="p-6">
                <div class="border-b border-warm dark:border-[#273244] pb-4 mb-5 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <h3 class="font-display font-semibold text-lg text-slate-900 dark:text-white">{{ __('Features & Finishes') }}</h3>
                        <p class="text-xs text-muted dark:text-muted">{{ __('Architectural elements, premium materials, and interior finishes.') }}</p>
                    </div>
                </div>

                <x-feature-selector name="features" :selected="$property->features" :features="$features ?? null" />
            </x-card>

            <div class="flex items-center justify-between gap-3 pt-2">
                <button type="button" onclick="if(confirm('Are you sure you want to delete this property?')) { document.getElementById('delete-property-form').submit(); }" class="btn bg-red-600 hover:bg-red-700 text-white text-xs px-4 py-2 rounded-lg cursor-pointer">
                    {{ __('Delete Property') }}
                </button>
                <div class="flex items-center gap-3">
                    <a href="{{ route('dashboard.properties.index') }}" class="btn btn-ghost">{{ __('Cancel') }}</a>
                    <x-button type="submit" variant="primary">{{ __('Update Property') }}</x-button>
                </div>
            </div>
        </form>

        <form id="delete-property-form" action="{{ route('dashboard.properties.destroy', $property->id) }}" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    </div>
</x-layouts.dashboard>
