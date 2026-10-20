<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Integrations Status') }}</x-slot>

    <div class="max-w-7xl mx-auto page-enter">
        <x-page-header
            title="External System Integrations"
            description="Manage PostGIS mapping providers, payment gateway webhooks, SMS notification channels, and banking APIs."
            icon="cube"
            badge="Integrations" />

        @include('frontend.integrations.partials.tabs')

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($services as $service)
                <div class="card p-6 flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-start mb-4">
                            <div class="w-12 h-12 rounded-xl bg-surface dark:bg-surface flex items-center justify-center text-muted dark:text-muted border border-[#EBDBC9] dark:border-[#273244]">
                                @if($service['icon'] === 'card')
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                                @elseif($service['icon'] === 'mobile')
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                                @elseif($service['icon'] === 'bank')
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" /></svg>
                                @elseif($service['icon'] === 'map')
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" /></svg>
                                @else
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" /></svg>
                                @endif
                            </div>
                            
                            @if($service['status'] === 'connected')
                                <span class="badge badge-success uppercase text-xs flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-success-500 animate-pulse"></span> Connected</span>
                            @elseif($service['status'] === 'error')
                                <span class="badge badge-error uppercase text-xs flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-error-500"></span> Error</span>
                            @else
                                <span class="badge badge-neutral uppercase text-xs flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-surface-subtle"></span> Disconnected</span>
                            @endif
                        </div>
                        
                        <h3 class="card-title text-lg">{{ $service['name'] }}</h3>
                        <p class="card-subtitle text-sm mb-4">{{ $service['type'] }}</p>
                        
                        @if(isset($service['error']))
                            <div class="card-nested bg-error-50 dark:bg-error-950/20 text-error-700 dark:text-error-400 p-3 text-xs mb-4 border-error-200 dark:border-error-900/30">
                                <strong>Integration Error:</strong> {{ $service['error'] }}
                            </div>
                        @endif
                    </div>
                    
                    <div>
                        <p class="text-xs text-muted font-medium mb-3">Last Sync: {{ $service['last_sync'] }}</p>
                        <div class="flex gap-2">
                            @if($service['status'] === 'connected')
                                <button class="btn btn-secondary flex-1">{{ __('Configure') }}</button>
                                <button class="btn btn-secondary border-error-200 text-error-700 hover:bg-error-50 dark:hover:bg-error-900/20">{{ __('Disconnect') }}</button>
                            @else
                                <button class="btn btn-primary w-full">{{ __('Connect') }}</button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

    </div>
</x-layouts.dashboard>
