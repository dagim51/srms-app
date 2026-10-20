<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Integration History') }}</x-slot>

    <div class="max-w-7xl mx-auto page-enter">
        <x-page-header
            title="Integration History"
            description="Audit log for webhook deliveries, sync states, and API errors."
            icon="document"
            badge="Audit Log" />

        @include('frontend.integrations.partials.tabs')

        <!-- Security Notice -->
        <div class="bg-primary-50 dark:bg-primary-900/10 border-l-4 border-primary-500 p-4 rounded-r-lg mb-8 text-primary-900 dark:text-primary-100 flex gap-3">
            <svg class="w-5 h-5 flex-shrink-0 text-primary-600 dark:text-primary-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
            <div class="text-sm">
                <p class="font-bold">{{ __('Admin Diagnostics Protected') }}</p>
                <p class="mt-1">{{ __('Sensitive secrets and authentication tokens are masked or stripped from these logs automatically to maintain security.') }}</p>
            </div>
        </div>

        <div class="card p-6 md:p-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="card-title text-xl">{{ __('Audit Log') }}</h2>
                <button class="btn btn-secondary border-warm dark:border-warm">{{ __('Export Logs') }}</button>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-[#EBDBC9] dark:border-[#273244] text-xs font-semibold text-muted uppercase tracking-wider">
                            <th class="py-3 font-semibold">{{ __('Log ID') }}</th>
                            <th class="py-3 font-semibold">{{ __('Service') }}</th>
                            <th class="py-3 font-semibold">{{ __('Event') }}</th>
                            <th class="py-3 font-semibold">{{ __('Status') }}</th>
                            <th class="py-3 font-semibold">{{ __('Timestamp') }}</th>
                            <th class="py-3 font-semibold text-right">{{ __('Details') }}</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-[#EBDBC9]/50 dark:divide-[#273244]/60">
                        @foreach($logs as $log)
                            <tr class="hover:bg-surface dark:hover:bg-surface/50 transition-colors">
                                <td class="py-4 font-mono text-xs text-muted">{{ $log['id'] }}</td>
                                <td class="py-4 font-bold text-muted dark:text-white">{{ $log['service'] }}</td>
                                <td class="py-4 text-muted dark:text-white">{{ $log['event'] }}</td>
                                <td class="py-4">
                                    @if($log['status'] === 'success')
                                        <span class="badge badge-success uppercase text-xs">{{ __('Success') }}</span>
                                    @elseif($log['status'] === 'warning')
                                        <span class="badge badge-warning uppercase text-xs">{{ __('Warning') }}</span>
                                    @else
                                        <span class="badge badge-error uppercase text-xs">{{ __('Failed') }}</span>
                                    @endif
                                </td>
                                <td class="py-4 text-muted">{{ $log['timestamp'] }}</td>
                                <td class="py-4 text-right">
                                    <span class="text-xs text-muted font-mono bg-surface-subtle dark:bg-surface px-2 py-1 rounded">{{ $log['details'] }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
