<x-layouts.dashboard>
    <x-slot name="title">Properties</x-slot>

    @section('page-title', 'Properties')

    <div class="space-y-6 page-enter">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">All Properties</h2>
            <a href="/dashboard/properties/create" class="btn btn-primary btn-sm">
                <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                Add Property
            </a>
        </div>

        <div class="card p-0 overflow-hidden">
            <div class="border-b border-gray-100 dark:border-[#273244] p-4 flex gap-4 bg-gray-50/50 dark:bg-[#172033]/50">
                <x-input type="text" placeholder="Search properties..." class="max-w-xs" />
                <x-button variant="outline" class="btn-sm text-gray-500">
                    <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" /></svg>
                    Filter
                </x-button>
            </div>
            
            <div class="empty-state py-16">
                <svg class="w-12 h-12 text-gray-200 dark:text-gray-700 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" /></svg>
                <h4 class="font-display font-semibold text-gray-900 dark:text-white text-lg mb-2">No properties found</h4>
                <p class="text-gray-500 dark:text-gray-400 text-sm mb-6 max-w-xs">You haven't added any properties to your portfolio yet.</p>
                <a href="/dashboard/properties/create" class="btn btn-primary btn-sm">
                    Add Property
                </a>
            </div>
        </div>
    </div>
</x-layouts.dashboard>
