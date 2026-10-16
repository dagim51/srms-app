<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Audit Logs') }}</x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="mb-8 flex justify-between items-end">
            <div>
                <h1 class="text-3xl font-extrabold font-display text-muted dark:text-white tracking-tight">{{ __('Audit Logs') }}</h1>
                <p class="text-muted dark:text-muted mt-2">{{ __('Immutable record of system configuration changes and sensitive actions.') }}</p>
            </div>
            <button class="btn btn-secondary flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                Export CSV
            </button>
        </div>

        @include('frontend.admin.partials.tabs')

        <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-3xl overflow-hidden shadow-sm">
            <div class="p-6 border-b border-warm dark:border-warm flex gap-4 flex-wrap">
                <input type="date" class="input">
                <input type="date" class="input">
                <input type="text" placeholder="{{ __('Search by user or action...') }}" class="input w-full max-w-sm">
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse font-mono text-sm">
                    <thead>
                        <tr class="bg-surface dark:bg-surface/50 border-b border-warm dark:border-warm">
                            <th class="p-4 font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Log ID') }}</th>
                            <th class="p-4 font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Timestamp') }}</th>
                            <th class="p-4 font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('User/System') }}</th>
                            <th class="p-4 font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Action') }}</th>
                            <th class="p-4 font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('IP Address') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-warm dark:divide-dark-border">
                        @foreach($logs as $log)
                            <tr class="hover:bg-surface dark:hover:bg-surface/30 transition-colors">
                                <td class="p-4 text-muted">{{ $log['id'] }}</td>
                                <td class="p-4 text-muted dark:text-white">{{ $log['date'] }}</td>
                                <td class="p-4 font-bold {{ $log['user'] === 'System' ? 'text-primary-600' : 'text-muted dark:text-white' }}">{{ $log['user'] }}</td>
                                <td class="p-4 text-muted dark:text-dark-subtle">{{ $log['action'] }}</td>
                                <td class="p-4 text-muted">{{ $log['ip'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
