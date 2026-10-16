<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Pipeline') }}</x-slot>

    <div class="max-w-7xl mx-auto h-[calc(100vh-140px)] flex flex-col">
        <div class="mb-6 flex-shrink-0">
            <h1 class="text-3xl font-extrabold font-display text-muted dark:text-white tracking-tight">{{ __('Sales Pipeline') }}</h1>
            <p class="text-muted dark:text-muted mt-2">{{ __('Drag and drop leads through the sales process.') }}</p>
        </div>

        @include('frontend.crm.partials.tabs')

        <div class="flex-1 overflow-x-auto overflow-y-hidden pb-4">
            <div class="flex h-full gap-4 min-w-max">
                @foreach($pipeline as $stage => $stageLeads)
                    <div class="w-80 flex flex-col bg-surface dark:bg-[#172033] rounded-2xl border border-warm dark:border-warm h-full max-h-full">
                        <div class="p-4 border-b border-warm dark:border-warm flex justify-between items-center bg-white dark:bg-[#1E293B] rounded-t-2xl">
                            <h3 class="font-bold text-muted dark:text-white">{{ __($stage) }}</h3>
                            <span class="bg-surface-subtle dark:bg-surface text-muted dark:text-dark-subtle text-xs font-bold px-2 py-1 rounded-full">{{ count($stageLeads) }}</span>
                        </div>
                        
                        <div class="p-3 flex-1 overflow-y-auto space-y-3">
                            @foreach($stageLeads as $lead)
                                <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-warm p-4 rounded-xl shadow-sm cursor-grab active:cursor-grabbing hover:border-primary-500 transition-colors">
                                    <div class="flex justify-between items-start mb-2">
                                        <a href="{{ route('dashboard.crm.leads.show', $lead['id']) }}" class="font-bold text-muted dark:text-white hover:text-primary-600">{{ $lead['name'] }}</a>
                                        <button class="text-muted hover:text-muted"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" /></svg></button>
                                    </div>
                                    <p class="text-xs text-muted dark:text-muted mb-3">{{ $lead['property'] }}</p>
                                    
                                    <div class="flex items-center justify-between mt-3 pt-3 border-t border-warm dark:border-warm">
                                        <span class="font-bold text-sm text-muted dark:text-white">{{ $lead['value'] }}</span>
                                        <div class="avatar avatar-sm avatar-brand" title="{{ __('Assigned to') }} {{ $lead['assigned_to'] }}">
                                            {{ substr($lead['assigned_to'], 0, 1) }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            
                            @if(count($stageLeads) === 0)
                                <div class="h-20 flex items-center justify-center border-2 border-dashed border-warm dark:border-warm rounded-xl text-sm font-medium text-muted">
                                    {{ __('Drop here') }}
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-layouts.dashboard>
