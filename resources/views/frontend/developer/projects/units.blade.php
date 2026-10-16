<x-layouts.dashboard>
    <x-slot name="page-title">{{ $project['name'] }} - Units</x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="mb-8 flex justify-between items-end">
            <div>
                <a href="{{ route('dashboard.projects.index') }}" class="text-sm font-medium text-muted hover:text-muted dark:hover:text-white flex items-center gap-2 mb-4 transition-colors w-max">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    Back to Projects
                </a>
                <h1 class="text-3xl font-extrabold font-display text-muted dark:text-white tracking-tight">{{ $project['name'] }}</h1>
            </div>
            <button class="btn btn-primary">{{ __('Add Unit') }}</button>
        </div>

        @include('frontend.developer.projects.partials.tabs')

        <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-3xl overflow-hidden shadow-sm">
            <div class="p-6 border-b border-warm dark:border-warm flex gap-4">
                <input type="text" placeholder="{{ __('Search units...') }}" class="input w-full max-w-md">
                <select class="input max-w-xs">
                    <option>{{ __('All Buildings') }}</option>
                    <option>{{ __('Tower A') }}</option>
                    <option>{{ __('Tower B') }}</option>
                </select>
                <select class="input max-w-xs">
                    <option>{{ __('All Statuses') }}</option>
                    <option>{{ __('Available') }}</option>
                    <option>{{ __('Reserved') }}</option>
                    <option>{{ __('Sold') }}</option>
                </select>
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
