<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Audit Logs') }}</x-slot>

    <div class="max-w-7xl mx-auto page-enter space-y-6">
        <x-page-header
            title="Audit Logs"
            description="Immutable record of system configuration changes, security events, and administrative actions."
            icon="shield"
            badge="Security">
            <x-slot:actions>
                <button class="btn btn-secondary flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                    Export CSV
                </button>
            </x-slot:actions>
        </x-page-header>

        @include('frontend.admin.partials.tabs')

        <div class="card bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-3xl overflow-hidden">
            <form method="GET" action="{{ route('dashboard.admin.audit') }}" class="p-4 sm:p-5 border-b border-warm dark:border-warm flex gap-3 flex-wrap items-center">
                <div class="relative flex-1 max-w-md">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search by user, action, or IP...') }}" class="form-input search-input pl-9 pr-8 w-full text-xs sm:text-sm">
                    @if(request('search'))
                        <a href="{{ route('dashboard.admin.audit') }}" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </a>
                    @endif
                </div>
                <button type="submit" class="btn btn-secondary text-xs sm:text-sm py-2 px-4">{{ __('Filter') }}</button>
            </form>
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
                            @php
                                $isModel = $log instanceof \App\Domains\Administration\Models\AuditLog || $log instanceof \App\Models\AuditLog;
                                $logId = $isModel ? ('AUD-' . strtoupper(substr(str_replace('-', '', (string) $log->id), 0, 6))) : $log['id'];
                                $logDate = $isModel ? ($log->created_at ? $log->created_at->format('Y-m-d H:i:s') : 'N/A') : $log['date'];
                                $logUser = $isModel ? ($log->actor ? $log->actor->name : 'System') : $log['user'];
                                $logAction = $isModel ? (ucfirst($log->event) . ($log->auditable_type ? ' ' . class_basename($log->auditable_type) : '') . ($log->notes ? ' - ' . $log->notes : '')) : $log['action'];
                                $logIp = $isModel ? ($log->ip_address ?? '127.0.0.1') : $log['ip'];
                            @endphp
                            <tr class="hover:bg-surface dark:hover:bg-surface/30 transition-colors">
                                <td class="p-4 text-muted">{{ $logId }}</td>
                                <td class="p-4 text-muted dark:text-white">{{ $logDate }}</td>
                                <td class="p-4 font-bold {{ $logUser === 'System' ? 'text-primary-600' : 'text-muted dark:text-white' }}">{{ $logUser }}</td>
                                <td class="p-4 text-muted dark:text-dark-subtle">{{ $logAction }}</td>
                                <td class="p-4 text-muted">{{ $logIp }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if(method_exists($logs, 'hasPages') && $logs->hasPages())
                <div class="p-4 border-t border-warm dark:border-warm">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>

    </div>
</x-layouts.dashboard>
