<x-layouts.dashboard>
    <x-slot name="title">Publish Listing</x-slot>

    @section('page-title', 'Publish Listing')
    @section('breadcrumb')
        <a href="/dashboard/listings" class="hover:text-gray-700 dark:hover:text-gray-300">Listings</a>
        <span class="mx-1.5 text-gray-300 dark:text-gray-600">/</span>
        <span class="text-gray-700 dark:text-gray-300">New</span>
    @endsection

    <div class="max-w-4xl mx-auto page-enter pb-12">
        <form action="/dashboard/listings" method="POST" class="space-y-6">
            @csrf

            <x-alert type="info" class="mb-6">
                Listings make your properties visible on the public marketplace. Make sure your property details are up to date before publishing.
            </x-alert>

            <x-card padding="p-6">
                <div class="border-b border-gray-100 dark:border-[#273244] pb-4 mb-5">
                    <h3 class="font-display font-semibold text-lg text-gray-900 dark:text-white">Select Property</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Choose a property from your portfolio to list.</p>
                </div>

                <div>
                    <x-label for="property_id" required>Property</x-label>
                    <select id="property_id" name="property_id" class="form-input w-full" required>
                        <option value="">Select property...</option>
                        <option value="1">Modern Family Home - Bole, Addis Ababa</option>
                        <option value="2">Luxury Apartment - Kazanchis, Addis Ababa</option>
                    </select>
                </div>
            </x-card>

            <x-card padding="p-6">
                <div class="border-b border-gray-100 dark:border-[#273244] pb-4 mb-5">
                    <h3 class="font-display font-semibold text-lg text-gray-900 dark:text-white">Listing Details</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Pricing and marketplace visibility.</p>
                </div>

                <div class="space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <x-label for="listing_type" required>Listing Type</x-label>
                            <select id="listing_type" name="listing_type" class="form-input w-full" required>
                                <option value="sale">For Sale</option>
                                <option value="rent">For Rent</option>
                                <option value="short_stay">Short Stay / Vacation</option>
                            </select>
                        </div>
                        <div>
                            <x-label for="price" required>Price (ETB)</x-label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">ETB</span>
                                </div>
                                <x-input type="number" id="price" name="price" class="w-full pl-12" placeholder="0.00" required />
                            </div>
                        </div>
                    </div>

                    <div>
                        <x-label for="description">Public Description</x-label>
                        <textarea id="description" name="description" rows="4" class="form-input w-full" placeholder="Describe the property to potential buyers or renters..."></textarea>
                    </div>
                </div>
            </x-card>

            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="/dashboard/listings" class="btn btn-ghost">Cancel</a>
                <x-button type="submit" variant="primary">Publish Listing</x-button>
            </div>
        </form>
    </div>
</x-layouts.dashboard>
