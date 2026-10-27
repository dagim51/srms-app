<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Tasks & Follow-ups') }}</x-slot>

    <div x-data="{ showModal: false }" class="max-w-7xl mx-auto page-enter space-y-6">
        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-sm flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" @click="$el.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800">&times;</button>
            </div>
        @endif

        <x-page-header
            title="CRM Tasks & Follow-ups"
            description="Schedule call reminders, client site visits, contract signings, and critical milestone alerts."
            icon="calendar"
            badge="Tasks">
            <x-slot:actions>
                <button @click="showModal = true" class="btn btn-primary text-xs flex items-center gap-1.5 shadow-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>{{ __('Create Task') }}</span>
                </button>
            </x-slot:actions>
        </x-page-header>

        @include('frontend.crm.partials.tabs')

        {{-- Filters & Summary --}}
        <div class="card bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-muted uppercase tracking-wider">{{ __('Filter:') }}</span>
                <a href="{{ route('dashboard.crm.tasks') }}" class="btn btn-sm text-xs {{ !request('status') ? 'btn-primary' : 'btn-secondary' }}">{{ __('All Tasks') }}</a>
                <a href="{{ route('dashboard.crm.tasks', ['status' => 'pending']) }}" class="btn btn-sm text-xs {{ request('status') === 'pending' ? 'btn-primary' : 'btn-secondary' }}">{{ __('Pending') }}</a>
                <a href="{{ route('dashboard.crm.tasks', ['status' => 'completed']) }}" class="btn btn-sm text-xs {{ request('status') === 'completed' ? 'btn-primary' : 'btn-secondary' }}">{{ __('Completed') }}</a>
            </div>
            <div class="text-xs text-muted">
                {{ __('Scheduled follow-ups and agent pipeline milestones') }}
            </div>
        </div>

        <div class="card bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-3xl p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-bold text-muted dark:text-white">{{ __('Scheduled CRM Tasks') }}</h2>
                <button @click="showModal = true" class="btn btn-secondary btn-sm text-xs">{{ __('+ Add Task') }}</button>
            </div>
            
            <div class="space-y-4">
                @forelse($tasks as $task)
                    <div class="flex items-start gap-4 p-4 border border-warm dark:border-warm rounded-2xl hover:border-primary-500/40 transition-colors {{ $task['status'] === 'completed' ? 'bg-surface/40 dark:bg-surface/20' : 'bg-white dark:bg-[#1E293B]' }}">
                        <div class="pt-1">
                            @if($task['status'] === 'completed')
                                <div class="w-6 h-6 rounded-full bg-emerald-100 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </div>
                            @else
                                <form method="POST" action="{{ route('dashboard.crm.tasks.complete', $task['id']) }}">
                                    @csrf
                                    <button type="submit" class="w-6 h-6 rounded-lg border-2 border-warm dark:border-slate-600 hover:border-emerald-500 hover:bg-emerald-50 flex items-center justify-center transition-colors group" title="{{ __('Mark Completed') }}">
                                        <svg class="w-3.5 h-3.5 text-transparent group-hover:text-emerald-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <h3 class="font-bold text-sm text-muted dark:text-white {{ $task['status'] === 'completed' ? 'line-through text-muted/60' : '' }} truncate">
                                    {{ $task['title'] }}
                                </h3>
                                <div class="flex items-center gap-2">
                                    <span class="badge {{ $task['status'] === 'completed' ? 'badge-success' : 'badge-warning' }} uppercase text-[10px]">
                                        {{ __($task['status']) }}
                                    </span>
                                    <span class="badge badge-neutral uppercase text-[10px]">
                                        {{ __($task['type']) }}
                                    </span>
                                </div>
                            </div>
                            
                            <div class="flex flex-wrap items-center gap-4 mt-2 text-xs text-muted">
                                @if(!empty($task['lead_id']))
                                    <a href="{{ route('dashboard.crm.leads.show', $task['lead_id']) }}" class="text-primary-600 hover:underline font-medium flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        {{ $task['lead_name'] }}
                                    </a>
                                @endif
                                <span class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    {{ __('Due:') }} {{ $task['due_date'] }}
                                </span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 text-muted">
                        <div class="max-w-sm mx-auto space-y-3">
                            <div class="w-12 h-12 rounded-full bg-orange-100 dark:bg-orange-950/40 text-orange-600 dark:text-orange-400 mx-auto flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                            </div>
                            <p class="font-medium text-slate-700 dark:text-slate-300">{{ __('No tasks found in this view.') }}</p>
                            <button @click="showModal = true" class="btn btn-primary btn-sm text-xs">{{ __('Schedule a Task') }}</button>
                        </div>
                    </div>
                @endforelse
            </div>

            @if(method_exists($tasks, 'links'))
                <div class="pt-6 mt-6 border-t border-warm dark:border-warm">
                    {{ $tasks->links() }}
                </div>
            @endif
        </div>

        {{-- Create Task Modal --}}
        <div x-show="showModal"
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
            <div @click.away="showModal = false"
                 class="card max-w-md w-full bg-white dark:bg-[#1E293B] rounded-2xl shadow-xl border border-warm dark:border-warm overflow-hidden">
                <div class="p-6 border-b border-warm dark:border-warm flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-bold text-muted dark:text-white">{{ __('Create CRM Task') }}</h3>
                        <p class="text-xs text-muted">{{ __('Schedule follow-up, call reminder, or client tour.') }}</p>
                    </div>
                    <button @click="showModal = false" class="text-muted hover:text-slate-700 dark:hover:text-slate-200 text-lg">&times;</button>
                </div>
                <form method="POST" action="{{ route('dashboard.crm.tasks.store') }}" class="p-6 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-muted dark:text-white mb-1">{{ __('Associated Lead / Prospect') }} *</label>
                        <select name="lead_id" required class="form-select w-full text-sm">
                            <option value="">{{ __('— Select Lead —') }}</option>
                            @foreach($leads as $lead)
                                <option value="{{ $lead->id }}">{{ $lead->name }} ({{ $lead->status }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-muted dark:text-white mb-1">{{ __('Task Type') }} *</label>
                            <select name="type" required class="form-select w-full text-sm">
                                <option value="call">{{ __('Phone Call') }}</option>
                                <option value="viewing">{{ __('Site Viewing') }}</option>
                                <option value="meeting">{{ __('Meeting') }}</option>
                                <option value="email">{{ __('Email Follow-up') }}</option>
                                <option value="task">{{ __('General Task') }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-muted dark:text-white mb-1">{{ __('Scheduled Date') }}</label>
                            <input type="date" name="scheduled_at" class="form-input w-full text-sm" value="{{ now()->addDay()->format('Y-m-d') }}">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-muted dark:text-white mb-1">{{ __('Task Description / Objective') }} *</label>
                        <textarea name="description" required rows="3" class="form-input w-full text-sm" placeholder="Follow up on contract draft, confirm deposit transfer..."></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-warm dark:border-warm">
                        <button type="button" @click="showModal = false" class="btn btn-secondary text-sm">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary text-sm">{{ __('Schedule Task') }}</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
