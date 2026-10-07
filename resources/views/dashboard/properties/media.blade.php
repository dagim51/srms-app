<x-layouts.dashboard>
    <x-slot name="title">Property Media Manager</x-slot>

    @section('page-title', 'Media Manager')
    @section('breadcrumb')
        <a href="/dashboard/properties" class="hover:text-gray-700 dark:hover:text-gray-300">Properties</a>
        <span class="mx-1.5 text-gray-300 dark:text-gray-600">/</span>
        <span class="text-gray-700 dark:text-gray-300">{{ $property->title ?? 'Property' }}</span>
        <span class="mx-1.5 text-gray-300 dark:text-gray-600">/</span>
        <span class="text-gray-700 dark:text-gray-300">Media</span>
    @endsection

    <div class="max-w-6xl mx-auto page-enter pb-12 space-y-6 font-sans">

        {{-- Flash Alerts --}}
        @if (session('success'))
            <x-alert type="success" dismissible="true">
                {{ session('success') }}
            </x-alert>
        @endif

        {{-- Property Header Banner --}}
        <x-card padding="p-5" class="bg-gradient-to-r from-orange-50/50 via-white to-white dark:from-orange-950/20 dark:via-[#111827] dark:to-[#111827]">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-orange-500/10 dark:bg-orange-500/20 text-orange-600 dark:text-orange-400 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="font-display font-bold text-lg text-gray-900 dark:text-white">{{ $property->title }}</h2>
                            <span class="badge badge-brand text-[10px] uppercase">{{ $property->status ?? 'Draft' }}</span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            {{ $property->bedrooms ? $property->bedrooms . ' Beds · ' : '' }}
                            {{ $property->bathrooms ? $property->bathrooms . ' Baths · ' : '' }}
                            {{ ($property->building_area ?? $property->area) ? ($property->building_area ?? $property->area) . ' m²' : '' }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 self-end sm:self-center">
                    <a href="/dashboard/properties" class="btn btn-outline text-xs">
                        Back to Properties
                    </a>
                </div>
            </div>
        </x-card>

        {{-- Main Media Uploader & Manager Card --}}
        <x-card padding="p-6">
            <div class="border-b border-gray-100 dark:border-[#273244] pb-4 mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <h3 class="font-display font-semibold text-lg text-gray-900 dark:text-white">Property Gallery & Documents</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Upload high-resolution images, video tours, and floor plans. Reorder photos or set the primary listing cover.
                    </p>
                </div>
                <div class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400">
                    <span class="inline-flex items-center gap-1.5 bg-gray-100 dark:bg-gray-800 px-2.5 py-1 rounded-lg">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        Auto-saves changes
                    </span>
                </div>
            </div>

            {{-- Reusable Component --}}
            <x-media-uploader :property="$property" name="photos" />
        </x-card>

        {{-- Photography & Media Guidelines Card --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-card padding="p-4" class="border-t-2 border-t-orange-500">
                <div class="flex items-center gap-2 text-orange-600 dark:text-orange-400 mb-1">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" /></svg>
                    <h4 class="text-xs font-semibold uppercase tracking-wider">Photo Guidelines</h4>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Use landscape orientation with high-resolution imagery (1920×1080 minimum). Bright daylight photos capture up to 3x more inquiries.
                </p>
            </x-card>

            <x-card padding="p-4" class="border-t-2 border-t-blue-500">
                <div class="flex items-center gap-2 text-blue-600 dark:text-blue-400 mb-1">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                    <h4 class="text-xs font-semibold uppercase tracking-wider">Floor Plans & Blueprints</h4>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Upload architectural layouts and room dimensions in PDF or clear PNG format to help prospective buyers visualize space.
                </p>
            </x-card>

            <x-card padding="p-4" class="border-t-2 border-t-purple-500">
                <div class="flex items-center gap-2 text-purple-600 dark:text-purple-400 mb-1">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                    <h4 class="text-xs font-semibold uppercase tracking-wider">Cover Selection</h4>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    The item in position #1 is your primary showcase photo across marketplace search results, social previews, and cards.
                </p>
            </x-card>
        </div>

    </div>
</x-layouts.dashboard>
