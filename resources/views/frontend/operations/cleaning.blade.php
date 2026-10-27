<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Cleaning &amp; Readiness') }}</x-slot>

    <div class="max-w-7xl mx-auto page-enter space-y-6" x-data="{ showSchedule: false }">
        <x-page-header
            title="Housekeeping &amp; Turnover Readiness"
            description="Manage unit turnover inspections, cleaning rosters, ready-for-occupancy verification, and sanitization logs."
            icon="ticket"
            badge="Housekeeping">
            <x-slot:actions>
                <button @click="showSchedule = true" class="btn btn-primary">{{ __('Schedule Cleaning') }}</button>
            </x-slot:actions>
        </x-page-header>

        @if(session('success'))
            <div class="px-4 py-3 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 text-sm border border-emerald-200 dark:border-emerald-800 flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ session('success') }}
            </div>
        @endif

        @include('frontend.operations.partials.tabs')

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6 mb-8">
            <x-card-stat :label="__('Pending Checkouts')" :value="$cleaningStats['pending_checkouts']" iconColor="amber" :badge="__('Pending')" badgeVariant="warning" icon="clock" />
            <x-card-stat :label="__('Active Cleaning')" :value="$cleaningStats['active_cleaning']" iconColor="blue" :badge="__('Active')" badgeVariant="info" icon="sparkles" />
            <x-card-stat :label="__('Awaiting Inspection')" :value="$cleaningStats['awaiting_inspection']" iconColor="orange" :badge="__('Inspection')" badgeVariant="brand" icon="wrench" />
            <x-card-stat :label="__('Units Ready')" :value="$cleaningStats['units_ready']" iconColor="emerald" :badge="__('Ready')" badgeVariant="success" icon="check" />
        </div>

        <div class="card overflow-hidden">
            <div class="p-6 border-b border-slate-200/80 dark:border-slate-800/80 flex justify-between items-center">
                <h2 class="card-title text-xl">{{ __('Active Tasks') }}</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-warm-50/50 dark:bg-[#1E293B]/50 border-b border-[#EBDBC9] dark:border-[#273244]">
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Task ID') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Unit') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Type') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Scheduled Date') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Assigned') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EBDBC9]/50 dark:divide-[#273244]">
                        @forelse($tasks as $task)
                            <tr class="hover:bg-surface dark:hover:bg-surface/30 transition-colors">
                                <td class="p-4 font-bold text-muted dark:text-white">{{ $task['label'] }}</td>
                                <td class="p-4 font-medium text-muted dark:text-white">{{ $task['unit'] }}</td>
                                <td class="p-4 text-muted dark:text-dark-subtle">{{ $task['type'] }}</td>
                                <td class="p-4 text-muted dark:text-dark-subtle">{{ date('M d, Y', strtotime($task['date'])) }}</td>
                                <td class="p-4 text-muted dark:text-dark-subtle">{{ $task['assigned'] }}</td>
                                <td class="p-4">
                                    @php
                                        $sClass = match($task['status']) {
                                            'in_progress' => 'badge-primary',
                                            'inspection' => 'badge-warning',
                                            'ready' => 'badge-success',
                                            default => 'badge-neutral',
                                        };
                                    @endphp
                                    <span class="badge {{ $sClass }} uppercase text-xs">{{ __(str_replace('_', ' ', $task['status'])) }}</span>
                                </td>
                                <td class="p-4 text-right">
                                    @if($task['status'] === 'inspection')
                                        <button class="text-warning-600 hover:text-warning-700 font-bold text-sm">{{ __('Perform Inspection') }}</button>
                                    @elseif($task['status'] === 'ready')
                                        <span class="text-success-600 font-medium text-sm">{{ __('Verified') }}</span>
                                    @else
                                        <button class="text-primary-600 hover:text-primary-700 font-medium text-sm">{{ __('Update') }}</button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-8 text-center text-muted">{{ __('No cleaning tasks found.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(method_exists($tasks, 'links'))
                <div class="p-4 border-t border-[#EBDBC9] dark:border-[#273244]">
                    {{ $tasks->links() }}
                </div>
            @endif
        </div>

        {{-- Schedule Cleaning Modal --}}
        <div x-show="showSchedule" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="absolute inset-0 bg-black/50" @click="showSchedule = false"></div>
            <div class="relative bg-white dark:bg-dark-card rounded-2xl shadow-2xl w-full max-w-lg p-6 z-10">
                <div class="flex justify-between items-center mb-5">
                    <h3 class="text-lg font-bold text-muted dark:text-white">{{ __('Schedule Cleaning Task') }}</h3>
                    <button @click="showSchedule = false" class="text-muted hover:text-muted/70">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form method="POST" action="{{ route('dashboard.operations.cleaning.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-muted dark:text-dark-subtle mb-1">{{ __('Property / Unit') }} <span class="text-red-500">*</span></label>
                        <select name="property_id" class="input w-full" required>
                            <option value="">{{ __('— Select Property —') }}</option>
                            @foreach($properties as $prop)
                                <option value="{{ $prop->id }}">{{ $prop->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-muted dark:text-dark-subtle mb-1">{{ __('Assign Cleaner') }}</label>
                        <select name="assigned_cleaner_id" class="input w-full">
                            <option value="">{{ __('— Unassigned —') }}</option>
                            @foreach($staffUsers as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-muted dark:text-dark-subtle mb-1">{{ __('Notes') }}</label>
                        <textarea name="notes" rows="3" class="input w-full" placeholder="{{ __('Special instructions...') }}"></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="showSchedule = false" class="btn btn-secondary">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Schedule') }}</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
