<x-layouts.dashboard>
    <x-slot name="title">{{ __('Properties') }}</x-slot>

    @section('page-title', __('Properties'))
    @section('breadcrumb')
        <x-breadcrumb :items="[
            ['label' => __('Dashboard'), 'url' => '/dashboard'],
            ['label' => __('Properties')]
        ]" />
    @endsection

    <div class="space-y-6 page-enter pb-12 font-sans">
        <x-page-header
            title="Property Inventory"
            description="Manage registered real estate inventory, cadastral boundaries, architectural media galleries, and listing controls."
            icon="building"
            badge="Inventory">
            <x-slot:actions>
                <a href="{{ route('dashboard.properties.map') }}" class="btn btn-outline btn-sm text-xs text-orange-600 dark:text-orange-400 border-orange-200 dark:border-orange-800">
                    <svg class="w-4 h-4 mr-1 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" /></svg>
                    {{ __('GIS Map Explorer') }}
                </a>
                <a href="{{ route('dashboard.properties.media.demo') }}" class="btn btn-outline btn-sm text-xs">
                    <svg class="w-4 h-4 mr-1 text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    {{ __('Media Manager') }}
                </a>
                <a href="/dashboard/properties/create" class="btn btn-primary btn-sm text-xs shadow-sm shadow-orange-500/20">
                    <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    {{ __('Add Property') }}
                </a>
            </x-slot:actions>
        </x-page-header>
        
        {{-- Top KPI Stat Cards --}}
        @php
            $totalPropertiesCount = \App\Models\Property::count();
            $activeMarketCount = \App\Models\Listing::where('status', 'published')->count();
            $totalMediaAssets = \App\Models\PropertyMedia::count();
            $verifiedCount = \App\Models\Property::where('status', 'verified')->count();
        @endphp
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="cinematic-fade-up cinematic-stagger-1">
                <x-card-stat
                    :label="__('Total Properties')"
                    :value="$totalPropertiesCount"
                    iconColor="orange"
                    icon="building"
                    :badge="__('Inventory')"
                    badgeVariant="brand"
                    caption="{{ __('Cataloged real estate units') }}"
                />
            </div>
            <div class="cinematic-fade-up cinematic-stagger-2">
                <x-card-stat
                    :label="__('Market Listings')"
                    :value="$activeMarketCount"
                    iconColor="emerald"
                    icon="tag"
                    :badge="__('Published')"
                    badgeVariant="success"
                    caption="{{ __('Active on marketplace') }}"
                />
            </div>
            <div class="cinematic-fade-up cinematic-stagger-3">
                <x-card-stat
                    :label="__('Media Assets')"
                    :value="$totalMediaAssets"
                    iconColor="blue"
                    icon="sparkles"
                    :badge="__('Media')"
                    badgeVariant="info"
                    caption="{{ __('High-res photos & galleries') }}"
                />
            </div>
            <div class="cinematic-fade-up cinematic-stagger-4">
                <x-card-stat
                    :label="__('Verified Listings')"
                    :value="$verifiedCount"
                    iconColor="purple"
                    icon="shield-check"
                    :badge="__('Verified')"
                    badgeVariant="brand"
                    caption="{{ __('Audited deed certificates') }}"
                />
            </div>
        </div>

        <div class="cinematic-fade-up cinematic-stagger-2">
        <x-card padding="p-0" class="overflow-hidden">
            <div class="border-b border-warm dark:border-dark-border p-4 flex flex-wrap items-center justify-between gap-3 bg-surface/50 dark:bg-dark-surface/50">
                <div class="relative flex-1 min-w-55">
                    <x-input type="text" placeholder="{{ __('Search properties by title or address...') }}" class="w-full text-xs" icon="magnifying-glass" />
                </div>
            </div>

            @if(isset($properties) && $properties->count() > 0)
                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>{{ __('Property') }}</th>
                                <th>{{ __('Details') }}</th>
                                <th>{{ __('Media Gallery') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th class="text-right">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-warm dark:divide-[#273244]">
                            @foreach($properties as $property)
                                @php
                                    $cover = $property->media->where('kind', 'image')->sortBy('sort_order')->first();
                                    $mediaCount = $property->media->count();
                                @endphp
                                <tr class="hover:bg-surface/80 dark:hover:bg-[#172033]/50 transition-colors">
                                    <td class="py-3 px-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-12 h-12 rounded-lg bg-surface-subtle dark:bg-surface border border-warm dark:border-warm overflow-hidden flex-shrink-0 flex items-center justify-center">
                                                @if($cover && $cover->url)
                                                    <img src="{{ $cover->url }}" alt="{{ $property->title }}" class="w-full h-full object-cover">
                                                @else
                                                    <svg class="w-5 h-5 text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" /></svg>
                                                @endif
                                            </div>
                                            <div class="min-w-0">
                                                <p class="font-semibold text-muted dark:text-white truncate text-xs">{{ $property->title }}</p>
                                                <p class="text-xs text-muted truncate">{{ $property->address ?? __('Location not set') }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-xs text-muted dark:text-dark-subtle">
                                        <span>{{ $property->bedrooms ?? 0 }} {{ __('Beds') }}</span> · 
                                        <span>{{ $property->bathrooms ?? 0 }} {{ __('Baths') }}</span>
                                        @if($property->area)
                                            · <span>{{ $property->area }} m²</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        <a href="{{ route('dashboard.properties.media', $property->id) }}" class="inline-flex items-center gap-1.5 text-xs text-orange-600 hover:text-orange-700 dark:text-orange-400 font-medium bg-orange-50 dark:bg-orange-950/40 px-2.5 py-1 rounded-lg border border-orange-200 dark:border-orange-800/50 hover:bg-orange-100 transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                            <span>{{ $mediaCount }} {{ __('files') }}</span>
                                        </a>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-surface-subtle dark:bg-surface text-muted dark:text-dark-subtle capitalize">
                                            {{ __($property->status ?? 'Draft') }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <div class="inline-flex items-center gap-2">
                                            <a href="{{ route('dashboard.listings.create', ['property_id' => $property->id]) }}" class="p-1.5 text-muted hover:text-orange-600 dark:hover:text-orange-400 rounded transition-colors" title="{{ __('Create Marketplace Listing') }}">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                            </a>
                                            <a href="{{ route('dashboard.properties.media', $property->id) }}" class="p-1.5 text-muted hover:text-orange-600 dark:hover:text-orange-400 rounded transition-colors" title="{{ __('Manage Media') }}">
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
                    <div class="p-4 border-t border-warm dark:border-[#273244]">
                        {{ $properties->links() }}
                    </div>
                @endif
            @else
                <div class="empty-state py-16 text-center">
                    <svg class="w-12 h-12 text-muted dark:text-muted mb-4 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" /></svg>
                    <h4 class="font-display font-semibold text-muted dark:text-white text-lg mb-2">{{ __('No properties found') }}</h4>
                    <p class="text-muted dark:text-muted text-sm mb-6 max-w-xs mx-auto">{{ __("You haven't added any properties to your portfolio yet.") }}</p>
                    <a href="/dashboard/properties/create" class="btn btn-primary btn-sm">
                        {{ __('Add First Property') }}
                    </a>
                </div>
            @endif
        </x-card>
        </div>
    </div>
</x-layouts.dashboard>
