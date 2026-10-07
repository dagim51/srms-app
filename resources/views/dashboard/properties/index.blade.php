<x-layouts.dashboard>
    <x-slot name="title">Properties</x-slot>

    @section('page-title', 'Properties')
    @section('breadcrumb')
        <a href="/dashboard" class="hover:text-gray-700 dark:hover:text-gray-300">Dashboard</a>
        <span class="mx-1.5 text-gray-300 dark:text-gray-600">/</span>
        <span class="text-gray-700 dark:text-gray-300">Properties</span>
    @endsection

    <div class="space-y-6 page-enter pb-12 font-sans">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold font-display text-gray-900 dark:text-white">All Properties</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400">Manage real estate inventory, media, and features.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('dashboard.properties.media.demo') }}" class="btn btn-outline text-xs">
                    <svg class="w-4 h-4 mr-1 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    Media Manager
                </a>
                <a href="/dashboard/properties/create" class="btn btn-primary text-xs shadow-sm shadow-orange-500/20">
                    <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    Add Property
                </a>
            </div>
        </div>

        <x-card padding="p-0" class="overflow-hidden">
            <div class="border-b border-gray-100 dark:border-[#273244] p-4 flex flex-wrap items-center justify-between gap-3 bg-gray-50/50 dark:bg-[#172033]/50">
                <div class="relative flex-1 min-w-[220px]">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                    </div>
                    <x-input type="text" placeholder="Search properties by title or address..." class="pl-9 w-full text-xs" />
                </div>
            </div>

            @if(isset($properties) && $properties->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 dark:bg-[#172033] text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-[#273244] text-xs uppercase tracking-wider">
                            <tr>
                                <th class="py-3 px-4 font-semibold">Property</th>
                                <th class="py-3 px-4 font-semibold">Details</th>
                                <th class="py-3 px-4 font-semibold">Media Gallery</th>
                                <th class="py-3 px-4 font-semibold">Status</th>
                                <th class="py-3 px-4 font-semibold text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-[#273244]">
                            @foreach($properties as $property)
                                @php
                                    $cover = $property->media->where('kind', 'image')->sortBy('sort_order')->first();
                                    $mediaCount = $property->media->count();
                                @endphp
                                <tr class="hover:bg-gray-50/80 dark:hover:bg-[#172033]/50 transition-colors">
                                    <td class="py-3 px-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-12 h-12 rounded-lg bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 overflow-hidden flex-shrink-0 flex items-center justify-center">
                                                @if($cover && $cover->url)
                                                    <img src="{{ $cover->url }}" alt="{{ $property->title }}" class="w-full h-full object-cover">
                                                @else
                                                    <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" /></svg>
                                                @endif
                                            </div>
                                            <div class="min-w-0">
                                                <p class="font-semibold text-gray-900 dark:text-white truncate text-xs">{{ $property->title }}</p>
                                                <p class="text-[11px] text-gray-400 truncate">{{ $property->address ?? 'Location not set' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-xs text-gray-600 dark:text-gray-300">
                                        <span>{{ $property->bedrooms ?? 0 }} Beds</span> · 
                                        <span>{{ $property->bathrooms ?? 0 }} Baths</span>
                                        @if($property->area)
                                            · <span>{{ $property->area }} m²</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        <a href="{{ route('dashboard.properties.media', $property->id) }}" class="inline-flex items-center gap-1.5 text-xs text-orange-600 hover:text-orange-700 dark:text-orange-400 font-medium bg-orange-50 dark:bg-orange-950/40 px-2.5 py-1 rounded-lg border border-orange-200 dark:border-orange-800/50 hover:bg-orange-100 transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                            <span>{{ $mediaCount }} files</span>
                                        </a>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 capitalize">
                                            {{ $property->status ?? 'Draft' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <div class="inline-flex items-center gap-2">
                                            <a href="{{ route('dashboard.properties.media', $property->id) }}" class="p-1.5 text-gray-500 hover:text-orange-600 dark:hover:text-orange-400 rounded transition-colors" title="Manage Media">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($properties->hasPages())
                    <div class="p-4 border-t border-gray-100 dark:border-[#273244]">
                        {{ $properties->links() }}
                    </div>
                @endif
            @else
                <div class="empty-state py-16 text-center">
                    <svg class="w-12 h-12 text-gray-200 dark:text-gray-700 mb-4 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" /></svg>
                    <h4 class="font-display font-semibold text-gray-900 dark:text-white text-lg mb-2">No properties found</h4>
                    <p class="text-gray-500 dark:text-gray-400 text-sm mb-6 max-w-xs mx-auto">You haven't added any properties to your portfolio yet.</p>
                    <a href="/dashboard/properties/create" class="btn btn-primary btn-sm">
                        Add First Property
                    </a>
                </div>
            @endif
        </x-card>
    </div>
</x-layouts.dashboard>
