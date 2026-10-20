<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Tasks & Follow-ups') }}</x-slot>

    <div class="max-w-7xl mx-auto page-enter space-y-6">
        <x-page-header
            title="CRM Tasks & Follow-ups"
            description="Schedule call reminders, client site visits, contract signings, and critical milestone alerts."
            icon="calendar"
            badge="Tasks">
            <x-slot:actions>
                <button class="btn btn-primary">{{ __('Create Task') }}</button>
            </x-slot:actions>
        </x-page-header>

        @include('frontend.crm.partials.tabs')

        <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-3xl p-6 shadow-sm">
            <h2 class="text-lg font-bold text-muted dark:text-white mb-6">{{ __('Upcoming Tasks') }}</h2>
            
            <div class="space-y-4">
                @foreach($tasks as $task)
                    <div class="flex items-start gap-4 p-4 border border-warm dark:border-warm rounded-2xl hover:border-warm transition-colors">
                        <div class="pt-1">
                            <input type="checkbox" class="w-5 h-5 rounded border-warm text-primary-600 focus:ring-primary-500" {{ $task['status'] === 'completed' ? 'checked' : '' }}>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-muted dark:text-white {{ $task['status'] === 'completed' ? 'line-through text-muted' : '' }}">{{ $task['title'] }}</h3>
                            <div class="flex items-center gap-3 mt-2 text-xs">
                                <span class="text-muted">{{ __('Due') }}: {{ $task['due_date'] }}</span>
                                <span class="badge {{ $task['priority'] === 'high' ? 'badge-error' : ($task['priority'] === 'medium' ? 'badge-warning' : 'badge-neutral') }} uppercase text-xs">{{ __($task['priority']) }} {{ __('priority') }}</span>
                            </div>
                        </div>
                        <button class="text-muted hover:text-muted"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" /></svg></button>
                    </div>
                @endforeach
            </div>
        </div>

    </div>
</x-layouts.dashboard>
