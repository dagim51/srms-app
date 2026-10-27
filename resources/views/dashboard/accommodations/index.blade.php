<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Hosting & Accommodations') }}</x-slot>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
            ['label' => __('Dashboard'), 'url' => route('dashboard')],
            ['label' => __('Hosting')],
        ]" />
    </x-slot>

    <div class="space-y-6">
        <x-page-header
            title="Short-Stay Accommodations"
            description="Manage serviced apartments, guest rooms, night rates, and hospitality occupancy settings."
            icon="key"
            badge="Accommodations">
            <x-slot:actions>
                <a href="{{ route('dashboard.accommodations.create') }}" class="btn btn-primary shadow-sm shadow-orange-500/20 flex items-center gap-2 text-xs py-2 px-3.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    {{ __('Set up new Accommodation') }}
                </a>
            </x-slot:actions>
        </x-page-header>

        @if(session('success'))
            <x-alert type="success">
                {{ session('success') }}
            </x-alert>
        @endif

        {{-- Actions & Filter Toolbar --}}
        <div class="card cinematic-fade-up cinematic-stagger-2 bg-white dark:bg-[#1E293B] rounded-xl p-4 border border-warm dark:border-warm space-y-4 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-muted dark:text-white">{{ __('Short-Stay Accommodations') }}</h2>
                    <p class="text-xs text-muted dark:text-muted">{{ __('Serviced guest apartments, suites, and short-stay vacation stays') }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('dashboard.accommodations.create') }}" class="btn btn-primary btn-sm flex items-center gap-1.5 shadow-sm shadow-orange-500/20">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        {{ __('New Accommodation') }}
                    </a>
                </div>
            </div>

            {{-- Filter Form --}}
            <form method="GET" action="{{ route('dashboard.accommodations.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-12 gap-3 pt-2 border-t border-warm dark:border-warm items-center">
                <div class="md:col-span-6 relative">
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="{{ __('Search accommodations by property, address, or code...') }}"
                           class="form-input search-input w-full pl-9 pr-8 text-xs">
                    <svg class="w-4 h-4 text-muted absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                    @if(request('search'))
                        <a href="{{ route('dashboard.accommodations.index', request()->except('search')) }}" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200" title="{{ __('Clear search') }}">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </a>
                    @endif
                </div>

                <div class="md:col-span-4">
                    <select name="status" onchange="this.form.submit()" class="form-select w-full text-xs">
                        <option value="">{{ __('All Statuses') }}</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                    </select>
                </div>

                <div class="md:col-span-2 flex items-center gap-2">
                    <button type="submit" class="btn btn-secondary btn-sm text-xs py-2 px-3 w-full justify-center">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                        <span>{{ __('Filter') }}</span>
                    </button>

                    @if(request()->hasAny(['search', 'status']))
                        <a href="{{ route('dashboard.accommodations.index') }}" class="p-2 text-muted hover:text-orange-600 dark:hover:text-orange-400" title="{{ __('Reset Filters') }}">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        @if($accommodations->isEmpty())
            @if(request('search') || request('status'))
                <div class="card p-12 text-center cinematic-fade-up">
                    <svg class="w-12 h-12 text-muted dark:text-muted mb-4 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                    <h4 class="font-display font-semibold text-muted dark:text-white text-lg mb-2">{{ __('No matching accommodations') }}</h4>
                    <p class="text-muted dark:text-muted text-sm mb-6 max-w-sm mx-auto">{{ __('No accommodations match your current search and filter criteria.') }}</p>
                    <a href="{{ route('dashboard.accommodations.index') }}" class="btn btn-secondary btn-sm">
                        {{ __('Clear All Filters') }}
                    </a>
                </div>
            @else
                <x-empty-state
                    icon="home"
                    :title="__('No Accommodations Set Up')"
                    :description="__('You haven\'t converted any of your properties into bookable accommodations yet.')"
                    size="lg">
                    <x-slot:actions>
                        <a href="{{ route('dashboard.accommodations.create') }}" class="btn btn-primary shadow-md">{{ __('Get Started') }}</a>
                    </x-slot:actions>
                </x-empty-state>
            @endif
        @else
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 cinematic-fade-up cinematic-stagger-2">
                @foreach($accommodations as $acc)
                    <div class="card card-hover overflow-hidden flex flex-col">
                        @if($acc->property && $acc->property->media->count())
                            @php $primaryImg = $acc->property->media->first()->url ?? $acc->property->media->first()->storage_key; @endphp
                            <div class="h-48 w-full relative">
                                <img src="{{ $primaryImg }}" alt="{{ $acc->property->title }}" class="w-full h-full object-cover">
                                <div class="absolute top-3 right-3">
                                    <x-badge :variant="$acc->is_active ? 'success' : 'neutral'" size="xs" dot>
                                        {{ $acc->is_active ? __('Active') : __('Inactive') }}
                                    </x-badge>
                                </div>
                            </div>
                        @else
                            <div class="h-48 w-full bg-surface-subtle dark:bg-surface flex items-center justify-center text-muted">
                                <svg class="w-12 h-12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            </div>
                        @endif
                        
                        <div class="p-5 flex-1 flex flex-col">
                            <h3 class="font-bold text-muted dark:text-white text-lg mb-1 line-clamp-1">{{ $acc->property->title ?? __('Accommodation') }}</h3>
                            
                            <div class="flex items-center gap-4 text-xs text-muted dark:text-muted mt-3 mb-4">
                                <div class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                    {{ $acc->max_guests }} {{ __('Guests') }}
                                </div>
                                <span>•</span>
                                <div class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                                    {{ $acc->bedrooms }} {{ __('Beds') }}
                                </div>
                            </div>

                            <div class="mt-auto pt-4 border-t border-warm dark:border-warm grid grid-cols-2 gap-2">
                                <a href="{{ route('dashboard.accommodations.show', $acc) }}" class="btn btn-outline btn-sm w-full">{{ __('Manage') }}</a>
                                <a href="{{ route('dashboard.accommodations.edit', $acc) }}" class="btn btn-outline btn-sm w-full">{{ __('Edit Specs') }}</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($accommodations->hasPages())
                <div class="mt-6">
                    {{ $accommodations->links() }}
                </div>
            @endif
        @endif
    </div>
</x-layouts.dashboard>
