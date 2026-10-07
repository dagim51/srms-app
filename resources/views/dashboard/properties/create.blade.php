<x-layouts.dashboard>
    <x-slot name="title">Add Property</x-slot>

    @section('page-title', 'Add Property')
    @section('breadcrumb')
        <a href="/dashboard/properties" class="hover:text-gray-700 dark:hover:text-gray-300">Properties</a>
        <span class="mx-1.5 text-gray-300 dark:text-gray-600">/</span>
        <span class="text-gray-700 dark:text-gray-300">Add New</span>
    @endsection

    <div class="max-w-4xl mx-auto page-enter pb-12">
        <form action="/dashboard/properties" method="POST" class="space-y-6">
            @csrf

            {{-- Basic Information --}}
            <x-card padding="p-6">
                <div class="border-b border-gray-100 dark:border-[#273244] pb-4 mb-5">
                    <h3 class="font-display font-semibold text-lg text-gray-900 dark:text-white">Basic Information</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Core details about the property.</p>
                </div>

                <div class="space-y-5">
                    <div>
                        <x-label for="title" required>Property Title</x-label>
                        <x-input type="text" id="title" name="title" placeholder="e.g. Modern Villa in Bole" class="w-full" required />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <x-label for="type" required>Property Type</x-label>
                            <select id="type" name="type" class="form-input w-full">
                                <option value="">Select type...</option>
                                <option value="apartment">Apartment</option>
                                <option value="villa">Villa</option>
                                <option value="condominium">Condominium</option>
                                <option value="commercial">Commercial Space</option>
                                <option value="land">Land</option>
                            </select>
                        </div>
                        <div>
                            <x-label for="status" required>Initial Status</x-label>
                            <select id="status" name="status" class="form-input w-full">
                                <option value="draft">Draft</option>
                                <option value="available">Available</option>
                                <option value="under_construction">Under Construction</option>
                            </select>
                        </div>
                    </div>
                </div>
            </x-card>

            {{-- Location --}}
            <x-card padding="p-6">
                <div class="border-b border-gray-100 dark:border-[#273244] pb-4 mb-5">
                    <h3 class="font-display font-semibold text-lg text-gray-900 dark:text-white">Location</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Where is this property located?</p>
                </div>
                
                <x-location-picker name="location_id" />

                <div class="mt-5">
                    <x-label for="address">Street Address</x-label>
                    <x-input type="text" id="address" name="address" placeholder="e.g. Rwanda Street, near X" class="w-full" />
                </div>
            </x-card>

            {{-- Details --}}
            <x-card padding="p-6">
                <div class="border-b border-gray-100 dark:border-[#273244] pb-4 mb-5">
                    <h3 class="font-display font-semibold text-lg text-gray-900 dark:text-white">Property Details</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Size and room information.</p>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
                    <div>
                        <x-label for="bedrooms">Bedrooms</x-label>
                        <x-input type="number" id="bedrooms" name="bedrooms" min="0" class="w-full" />
                    </div>
                    <div>
                        <x-label for="bathrooms">Bathrooms</x-label>
                        <x-input type="number" id="bathrooms" name="bathrooms" min="0" step="0.5" class="w-full" />
                    </div>
                    <div>
                        <x-label for="area">Area (m²)</x-label>
                        <x-input type="number" id="area" name="area" min="0" class="w-full" />
                    </div>
                    <div>
                        <x-label for="year_built">Year Built</x-label>
                        <x-input type="number" id="year_built" name="year_built" min="1900" max="{{ date('Y') + 5 }}" class="w-full" />
                    </div>
                </div>
            </x-card>

            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="/dashboard/properties" class="btn btn-ghost">Cancel</a>
                <x-button type="submit" variant="primary">Save Property</x-button>
            </div>
        </form>
    </div>
</x-layouts.dashboard>
