<x-layouts.dashboard>
    <x-slot name="title">Listings</x-slot>

    @section('page-title', 'Listings')

    <div class="space-y-6 page-enter">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Active Listings</h2>
            <a href="/dashboard/listings/create" class="btn btn-primary btn-sm">
                <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                Publish Listing
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {{-- Stub listing card --}}
            <div class="card p-0 overflow-hidden flex flex-col group">
                <div class="relative aspect-video bg-gray-100 dark:bg-gray-800 overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Villa" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    <div class="absolute top-3 left-3">
                        <span class="badge badge-success bg-white/90 backdrop-blur shadow-sm">Published</span>
                    </div>
                </div>
                <div class="p-4 flex-1 flex flex-col">
                    <div class="flex items-center justify-between mb-1">
                        <p class="text-xs font-semibold tracking-wider text-gray-500 uppercase">For Sale</p>
                        <p class="font-display font-bold text-gray-900 dark:text-white">ETB 15,000,000</p>
                    </div>
                    <h3 class="font-display font-semibold text-gray-900 dark:text-white text-base mb-1">Modern Family Home</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.242-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        Bole, Addis Ababa
                    </p>
                    <div class="mt-auto pt-4 border-t border-gray-100 dark:border-[#273244] flex items-center justify-between">
                        <div class="text-xs text-gray-500">Listed 2 days ago</div>
                        <div class="flex gap-2">
                            <button class="btn btn-ghost btn-sm btn-icon" title="Edit"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg></button>
                            <button class="btn btn-ghost btn-sm btn-icon text-gray-400 hover:text-gray-900 dark:hover:text-white" title="Options"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" /></svg></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.dashboard>
