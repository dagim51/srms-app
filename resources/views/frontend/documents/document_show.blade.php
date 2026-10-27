<x-layouts.dashboard>
    <x-slot name="page-title">Document: {{ $document['name'] }}</x-slot>

    <div class="max-w-7xl mx-auto page-enter space-y-6">
        <x-page-header
            :title="$document['name']"
            :description="__('Status:') . ' ' . __($document['status']) . ' • ' . __('Type:') . ' ' . __($document['type']) . ' • ' . __('Uploaded') . ' ' . $document['date']"
            icon="document"
            :badge="$document['status']">
            <x-slot:actions>
                <div class="flex items-center gap-2 flex-wrap">
                    <a href="{{ route('dashboard.documents.index') }}" class="btn btn-secondary text-xs flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                        {{ __('Back to Documents') }}
                    </a>
                    <a href="{{ $document['download_url'] ?? '#' }}" class="btn btn-secondary text-primary-600 border-primary-200 hover:bg-primary-50 text-xs flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                        {{ __('Secure Download') }}
                    </a>
                    <button class="btn btn-primary text-xs">{{ __('Upload New Version') }}</button>
                </div>
            </x-slot:actions>
        </x-page-header>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-8">
                <!-- Info -->
                <div class="card p-6 md:p-8">
                    <h2 class="card-title text-lg mb-4">{{ __('Metadata') }}</h2>
                    <p class="text-sm text-muted dark:text-dark-subtle mb-6">{{ $document['description'] }}</p>
                    
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-6 pt-6 border-t border-[#EBDBC9] dark:border-[#273244]">
                        <div>
                            <p class="card-kv-label">{{ __('Owner') }}</p>
                            <p class="card-kv-value mt-1">{{ $document['owner'] }}</p>
                        </div>
                        <div>
                            <p class="card-kv-label">{{ __('Size & Type') }}</p>
                            <p class="card-kv-value font-medium mt-1">{{ $document['size'] }} ({{ $document['mime'] }})</p>
                        </div>
                        <div>
                            <p class="card-kv-label">{{ __('Last Updated') }}</p>
                            <p class="card-kv-value font-medium mt-1">{{ date('M d, Y H:i', strtotime($document['updated_at'])) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Preview Area -->
                <div class="card flex items-center justify-center p-12 min-h-[16rem]">
                    <div class="text-center text-muted">
                        <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                        <p class="font-bold text-muted dark:text-white">{{ __('Document Preview') }}</p>
                        <p class="text-xs text-muted">{{ __('Secure Viewer initialized.') }}</p>
                    </div>
                </div>
            </div>

            <!-- Version History & Logs -->
            <div class="space-y-8">
                <!-- Versions -->
                <div class="card p-6">
                    <h3 class="card-title text-base mb-4">{{ __('Version History') }}</h3>
                    <div class="space-y-4">
                        @foreach($versions as $v)
                            <div class="card-nested p-3 flex items-start gap-3">
                                <div class="w-8 h-8 rounded bg-surface-subtle dark:bg-surface flex items-center justify-center font-bold text-xs shrink-0 text-muted dark:text-dark-subtle">{{ $v['version'] }}</div>
                                <div>
                                    <p class="text-sm font-bold text-muted dark:text-white">{{ $v['changes'] }}</p>
                                    <p class="text-xs text-muted">{{ $v['user'] }} &bull; {{ date('M d, H:i', strtotime($v['date'])) }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                
                <!-- Access Logs -->
                <div class="card p-6">
                    <h3 class="card-title text-base mb-4">{{ __('Access Logs') }}</h3>
                    <ul class="space-y-3">
                        @foreach($accessLogs as $log)
                            <li class="flex justify-between items-center text-sm border-b border-[#EBDBC9]/60 dark:border-[#273244]/60 pb-2 last:border-0">
                                <div>
                                    <span class="font-medium text-muted dark:text-white">{{ $log['user'] }}</span>
                                    <span class="text-muted"> {{ strtolower($log['action']) }}</span>
                                </div>
                                <span class="text-xs text-muted">{{ date('M d', strtotime($log['date'])) }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
