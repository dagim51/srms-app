<x-layouts.dashboard>
    <x-slot name="page-title">{{ $project['name'] }} - Units</x-slot>

    <div class="max-w-7xl mx-auto page-enter">
        <div class="mb-4">
            <a href="{{ route('dashboard.projects.index') }}" class="text-sm font-medium text-muted hover:text-muted dark:hover:text-white flex items-center gap-2 transition-colors w-max">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Back to Projects
            </a>
        </div>

        <x-page-header
            :title="$project['name'] . ' - Units'"
            description="Manage individual apartments, penthouses, duplex units, floor plans, and pricing."
            icon="building"
            badge="Units">
            <x-slot:actions>
                <button class="btn btn-primary">{{ __('Add Unit') }}</button>
            </x-slot:actions>
        </x-page-header>

        @include('frontend.developer.projects.partials.tabs')

        <div class="card bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-3xl overflow-hidden">
            <div class="p-6 border-b border-warm dark:border-warm">
                <form method="GET" action="{{ url()->current() }}" class="flex flex-wrap items-center gap-3">
                    <div class="relative flex-1 min-w-56">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </div>
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="{{ __('Search units by number, type, or status...') }}"
                               class="form-input search-input pl-9 pr-8 w-full text-sm">
                        @if(request('search'))
                            <a href="{{ url()->current() }}" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </a>
                        @endif
                    </div>
                    <select name="status" onchange="this.form.submit()" class="form-select max-w-xs text-sm">
                        <option value="">{{ __('All Statuses') }}</option>
                        <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>{{ __('Available') }}</option>
                        <option value="reserved" {{ request('status') === 'reserved' ? 'selected' : '' }}>{{ __('Reserved') }}</option>
                        <option value="sold" {{ request('status') === 'sold' ? 'selected' : '' }}>{{ __('Sold') }}</option>
                    </select>
                    @if(request('search') || request('status'))
                        <a href="{{ url()->current() }}" class="text-xs text-orange-600 hover:text-orange-700 dark:text-orange-400 font-medium px-2 py-1.5">{{ __('Reset') }}</a>
                    @endif
                </form>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface dark:bg-surface/50 border-b border-warm dark:border-warm">
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Unit Number') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Type') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Floor') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Price') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-warm dark:divide-dark-border">
                        @foreach($units as $unit)
                            <tr class="hover:bg-surface dark:hover:bg-surface/30 transition-colors">
                                <td class="p-4 font-bold text-muted dark:text-white">{{ $unit['number'] }}</td>
                                <td class="p-4 text-muted dark:text-dark-subtle">{{ $unit['type'] }}</td>
                                <td class="p-4 text-muted dark:text-dark-subtle">{{ $unit['floor'] }}</td>
                                <td class="p-4 font-bold text-muted dark:text-white">{{ $unit['price'] }}</td>
                                <td class="p-4">
                                    <span class="badge {{ $unit['status'] === 'available' ? 'badge-success' : ($unit['status'] === 'reserved' ? 'badge-warning' : 'badge-error') }} uppercase text-xs">{{ $unit['status'] }}</span>
                                </td>
                                <td class="p-4 text-right">
                                    <button class="text-primary-600 hover:text-primary-700 font-medium text-sm">{{ __('Edit') }}</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
