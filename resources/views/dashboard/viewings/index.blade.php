<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('My Viewings') }}</x-slot>
    <x-slot:breadcrumb>
        <x-breadcrumb :items="[
            ['label' => __('Dashboard'), 'url' => route('dashboard')],
            ['label' => __('Viewings')]
        ]" />
    </x-slot:breadcrumb>

    <div class="space-y-6">
        <x-page-header
            title="Property Viewings"
            description="Coordinate and manage scheduled on-site appointments, virtual tours, and viewing feedback."
            icon="calendar"
            badge="Appointments">
            <x-slot:actions>
                <div class="bg-surface-subtle dark:bg-[#1E293B] p-1 rounded-xl flex border border-slate-200/80 dark:border-slate-800">
                    <button class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-white dark:bg-surface shadow-xs text-slate-800 dark:text-white">{{ __('List') }}</button>
                    <button class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-500 hover:text-slate-900 dark:hover:text-white">{{ __('Calendar') }}</button>
                </div>
            </x-slot:actions>
        </x-page-header>

        {{-- Top KPI Stat Cards --}}
        {{-- Top KPI Stat Cards --}}
        @php
            $totalViewings = \App\Models\Viewing::where(fn($q) => $q->where('requester_id', auth()->id())->orWhere('agent_id', auth()->id()))->count();
            $confirmedViewings = \App\Models\Viewing::where(fn($q) => $q->where('requester_id', auth()->id())->orWhere('agent_id', auth()->id()))->where('status', 'confirmed')->count();
            $requestedViewings = \App\Models\Viewing::where(fn($q) => $q->where('requester_id', auth()->id())->orWhere('agent_id', auth()->id()))->where('status', 'requested')->count();
            $completedViewings = \App\Models\Viewing::where(fn($q) => $q->where('requester_id', auth()->id())->orWhere('agent_id', auth()->id()))->where('status', 'completed')->count();
        @endphp
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="cinematic-fade-up cinematic-stagger-1">
                <x-card-stat
                    :label="__('Total Appointments')"
                    :value="$totalViewings"
                    iconColor="orange"
                    icon="calendar"
                    :badge="__('Schedule')"
                    badgeVariant="brand"
                    caption="{{ __('All property viewing requests') }}"
                />
            </div>
            <div class="cinematic-fade-up cinematic-stagger-2">
                <x-card-stat
                    :label="__('Confirmed')"
                    :value="$confirmedViewings"
                    iconColor="emerald"
                    icon="check"
                    :badge="__('Confirmed')"
                    badgeVariant="success"
                    caption="{{ __('Buyer & agent confirmed') }}"
                />
            </div>
            <div class="cinematic-fade-up cinematic-stagger-3">
                <x-card-stat
                    :label="__('Pending Requests')"
                    :value="$requestedViewings"
                    iconColor="amber"
                    icon="clock"
                    :badge="__('Pending')"
                    badgeVariant="warning"
                    caption="{{ __('Awaiting scheduling response') }}"
                />
            </div>
            <div class="cinematic-fade-up cinematic-stagger-4">
                <x-card-stat
                    :label="__('Completed Tours')"
                    :value="$completedViewings"
                    iconColor="blue"
                    icon="eye"
                    :badge="__('Done')"
                    badgeVariant="info"
                    caption="{{ __('Past verified appointments') }}"
                />
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 text-emerald-800 text-sm font-medium border border-emerald-200">
                {{ session('success') }}
            </div>
        @endif

        {{-- Actions & Filter Toolbar --}}
        <div class="card cinematic-fade-up cinematic-stagger-2 bg-white dark:bg-[#1E293B] rounded-xl p-4 border border-warm dark:border-warm space-y-4 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-muted dark:text-white">{{ __('Appointments & Guided Viewings') }}</h2>
                    <p class="text-xs text-muted dark:text-muted">{{ __('In-person property tours, verified agent schedules, and buyer requests') }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('properties.index') }}" class="btn btn-primary btn-sm flex items-center gap-1.5 shadow-sm shadow-orange-500/20">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        {{ __('Explore & Book Viewing') }}
                    </a>
                </div>
            </div>

            {{-- Filter Form --}}
            <form method="GET" action="{{ route('dashboard.viewings.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-12 gap-3 pt-2 border-t border-warm dark:border-warm items-center">
                <div class="md:col-span-6 relative">
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="{{ __('Search by property title, address, agent, or requester...') }}"
                           class="form-input search-input w-full pl-9 pr-8 text-xs">
                    <svg class="w-4 h-4 text-muted absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                    @if(request('search'))
                        <a href="{{ route('dashboard.viewings.index', request()->except('search')) }}" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200" title="{{ __('Clear search') }}">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </a>
                    @endif
                </div>

                <div class="md:col-span-4">
                    <select name="status" onchange="this.form.submit()" class="form-select w-full text-xs">
                        <option value="">{{ __('All Statuses') }}</option>
                        <option value="requested" {{ request('status') === 'requested' ? 'selected' : '' }}>{{ __('Requested') }}</option>
                        <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>{{ __('Confirmed') }}</option>
                        <option value="rescheduled" {{ request('status') === 'rescheduled' ? 'selected' : '' }}>{{ __('Rescheduled') }}</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>{{ __('Completed') }}</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>{{ __('Cancelled') }}</option>
                    </select>
                </div>

                <div class="md:col-span-2 flex items-center gap-2">
                    <button type="submit" class="btn btn-secondary btn-sm text-xs py-2 px-3 w-full justify-center">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                        <span>{{ __('Filter') }}</span>
                    </button>

                    @if(request()->hasAny(['search', 'status']))
                        <a href="{{ route('dashboard.viewings.index') }}" class="p-2 text-muted hover:text-orange-600 dark:hover:text-orange-400" title="{{ __('Reset Filters') }}">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        @if($viewings->isEmpty())
            @if(request('search') || request('status'))
                <div class="card p-12 text-center cinematic-fade-up">
                    <svg class="w-12 h-12 text-muted dark:text-muted mb-4 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                    <h4 class="font-display font-semibold text-muted dark:text-white text-lg mb-2">{{ __('No matching appointments') }}</h4>
                    <p class="text-muted dark:text-muted text-sm mb-6 max-w-sm mx-auto">{{ __('No property viewings match your search and filter criteria.') }}</p>
                    <a href="{{ route('dashboard.viewings.index') }}" class="btn btn-secondary btn-sm">
                        {{ __('Clear All Filters') }}
                    </a>
                </div>
            @else
                <x-empty-state
                    icon="calendar"
                    title="{{ __('No upcoming viewings') }}"
                    description="{{ __('When you request to view a property or someone requests to view yours, they will appear here.') }}"
                    action="{{ route('properties.index') }}"
                    actionText="{{ __('Explore Properties') }}"
                />
            @endif
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 cinematic-fade-up cinematic-stagger-2">
                @foreach($viewings as $viewing)
                    @php
                        $isAgent = $viewing->agent_id === auth()->id();
                        $statusColors = [
                            'requested' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300',
                            'confirmed' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300',
                            'rescheduled' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300',
                            'completed' => 'bg-surface-subtle text-muted dark:bg-surface dark:text-dark-subtle',
                            'cancelled' => 'bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-300',
                        ];
                        $badgeColor = $statusColors[$viewing->status] ?? $statusColors['requested'];
                    @endphp
                    
                    <a href="{{ route('dashboard.viewings.show', $viewing) }}" class="card card-hover block group overflow-hidden">
                        <div class="h-2 w-full {{ $viewing->status === 'confirmed' ? 'bg-emerald-500' : ($viewing->status === 'requested' ? 'bg-amber-500' : 'bg-surface-subtle dark:bg-surface') }}"></div>
                        
                        <div class="p-5">
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <x-badge variant="{{ $viewing->status === 'confirmed' ? 'success' : ($viewing->status === 'requested' ? 'warning' : ($viewing->status === 'cancelled' ? 'error' : 'neutral')) }}">
                                        {{ __($viewing->status) }}
                                    </x-badge>
                                </div>
                                <div class="text-right">
                                    <div class="text-2xl font-bold font-display text-muted dark:text-white leading-none">{{ $viewing->scheduled_at->format('d') }}</div>
                                    <div class="text-xs font-bold text-muted uppercase tracking-wide">{{ $viewing->scheduled_at->format('M, Y') }}</div>
                                </div>
                            </div>

                            <h3 class="font-bold text-muted dark:text-white text-base mb-1 group-hover:text-primary-600 transition-colors line-clamp-1">
                                {{ $viewing->listing->property->title ?? __('Property Details') }}
                            </h3>
                            
                            <div class="flex items-center text-sm text-muted dark:text-muted mb-4 gap-2">
                                <svg class="w-4 h-4 text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                {{ $viewing->scheduled_at->format('g:i A') }}
                            </div>
                            
                            <div class="pt-4 border-t border-warm dark:border-warm flex items-center justify-between text-xs">
                                <span class="text-muted font-medium">
                                    {{ $isAgent ? __('Requested by') : __('Agent') }}:
                                    <span class="text-muted dark:text-white font-bold ml-1">
                                        {{ $isAgent ? ($viewing->requester->name ?? __('User')) : ($viewing->agent->name ?? __('Agent')) }}
                                    </span>
                                </span>
                                
                                <span class="text-primary-600 font-bold group-hover:underline flex items-center gap-1">
                                    {{ __('Details') }}
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                </span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            @if($viewings->hasPages())
                <div class="mt-6">
                    {{ $viewings->links() }}
                </div>
            @endif
        @endif
    </div>
</x-layouts.dashboard>
