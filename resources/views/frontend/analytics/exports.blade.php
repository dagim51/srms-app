<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Export Status') }}</x-slot>

    <div class="max-w-7xl mx-auto page-enter">
        <x-page-header
            title="Export Status"
            description="Monitor asynchronous report generation and bulk downloads."
            icon="document"
            badge="Exports" />

        @include('frontend.analytics.partials.tabs')

        <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-3xl p-6 shadow-sm">
            <div class="space-y-4">
                @foreach($exports as $export)
                    <div class="border border-warm dark:border-warm rounded-xl p-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <div class="flex items-center gap-4 w-full sm:w-auto">
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0 
                                {{ $export['status'] === 'completed' ? 'bg-success-50 text-success-600 border border-success-100' : 
                                  ($export['status'] === 'processing' ? 'bg-primary-50 text-primary-600 border border-primary-100' : 'bg-error-50 text-error-600 border border-error-100') }}">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            </div>
                            <div>
                                <p class="font-bold text-muted dark:text-white flex items-center gap-2">
                                    {{ $export['report'] }}
                                    <span class="badge badge-neutral text-xs">{{ $export['format'] }}</span>
                                </p>
                                <p class="text-xs text-muted mt-1">{{ __('Requested') }}: {{ date('M d, H:i', strtotime($export['requested_at'])) }}</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-4 w-full sm:w-auto justify-between sm:justify-end">
                            <div class="flex items-center gap-2 min-w-24">
                                @if($export['status'] === 'processing')
                                    <svg class="w-4 h-4 text-primary-600 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    <span class="text-sm font-bold text-primary-600">{{ $export['progress'] }}</span>
                                @elseif($export['status'] === 'completed')
                                    <span class="text-sm font-bold text-success-600">{{ __('Ready') }}</span>
                                @else
                                    <span class="text-sm font-bold text-error-600">{{ __('Failed') }}</span>
                                @endif
                            </div>
                            @if($export['status'] === 'completed')
                                <button class="btn btn-primary btn-sm">{{ __('Download') }}</button>
                            @else
                                <button class="btn btn-secondary btn-sm" disabled>{{ __('Download') }}</button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>
</x-layouts.dashboard>
