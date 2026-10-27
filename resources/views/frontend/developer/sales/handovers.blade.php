<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Handovers') }}</x-slot>

    <div class="max-w-7xl mx-auto page-enter space-y-6" x-data="{ selectedHandover: null }">
        <x-page-header
            title="Handovers & Snagging"
            description="Manage the final handover process and inspection snagging of completed units."
            icon="key"
            badge="Handovers" />

        @if(session('success'))
            <div class="px-4 py-3 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 text-sm border border-emerald-200 dark:border-emerald-800 flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ session('success') }}
            </div>
        @endif

        @include('frontend.developer.sales.partials.tabs')

        <div class="card bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-3xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface dark:bg-surface/50 border-b border-warm dark:border-warm">
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Unit Number') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Buyer Name') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Scheduled Date') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-warm dark:divide-dark-border">
                        @forelse($handovers as $handover)
                            <tr class="hover:bg-surface dark:hover:bg-surface/30 transition-colors">
                                <td class="p-4 font-bold text-muted dark:text-white">{{ $handover['unit'] }}</td>
                                <td class="p-4 text-muted dark:text-dark-subtle">{{ $handover['buyer'] }}</td>
                                <td class="p-4 font-medium text-muted dark:text-white">{{ date('M d, Y', strtotime($handover['scheduled_date'])) }}</td>
                                <td class="p-4">
                                    <span class="badge {{ $handover['status'] === 'completed' ? 'badge-success' : ($handover['status'] === 'scheduled' ? 'badge-primary' : 'badge-warning') }} uppercase text-xs">{{ str_replace('_', ' ', $handover['status']) }}</span>
                                </td>
                                <td class="p-4 text-right">
                                    @if($handover['status'] === 'completed')
                                        <span class="badge badge-success text-xs px-2.5 py-1">{{ __('Keys Released') }}</span>
                                    @else
                                        <button @click="selectedHandover = {{ json_encode($handover) }}"
                                                class="btn btn-primary btn-sm text-xs">
                                            {{ __('Inspect & Handover') }}
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-8 text-center text-muted">
                                    {{ __('No handovers scheduled.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(method_exists($handovers, 'links'))
                <div class="p-4 border-t border-warm dark:border-warm">
                    {{ $handovers->links() }}
                </div>
            @endif
        </div>

        {{-- Complete Handover Inspection Modal --}}
        <div x-show="selectedHandover !== null" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="absolute inset-0 bg-black/50" @click="selectedHandover = null"></div>
            <div class="relative bg-white dark:bg-dark-card rounded-2xl shadow-2xl w-full max-w-lg p-6 z-10">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-muted dark:text-white">{{ __('Final Inspection & Key Release') }}</h3>
                    <button @click="selectedHandover = null" class="text-muted hover:text-muted/70">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <template x-if="selectedHandover">
                    <form method="POST" :action="'{{ url('dashboard/sales/handovers') }}/' + selectedHandover.id + '/complete'" class="space-y-4">
                        @csrf
                        <div class="p-3 bg-surface dark:bg-surface/20 rounded-xl space-y-1 text-sm border border-warm dark:border-warm">
                            <p class="font-bold text-muted dark:text-white" x-text="'Unit: ' + selectedHandover.unit + ' • Buyer: ' + selectedHandover.buyer"></p>
                            <p class="text-xs text-muted" x-text="'Target Date: ' + selectedHandover.scheduled_date"></p>
                        </div>

                        <div class="space-y-2">
                            <p class="text-xs font-bold text-muted uppercase tracking-wider">{{ __('Snagging & Inspection Checklist') }}</p>
                            <label class="flex items-center gap-2 text-sm text-muted dark:text-dark-subtle cursor-pointer">
                                <input type="checkbox" name="plumbing" value="1" checked class="rounded border-warm text-primary-600 focus:ring-primary-600">
                                <span>{{ __('Plumbing & Fixtures Verified (No leaks, pressure optimal)') }}</span>
                            </label>
                            <label class="flex items-center gap-2 text-sm text-muted dark:text-dark-subtle cursor-pointer">
                                <input type="checkbox" name="electrical" value="1" checked class="rounded border-warm text-primary-600 focus:ring-primary-600">
                                <span>{{ __('Electrical & Circuit Outlets Verified (Breakers and lighting active)') }}</span>
                            </label>
                            <label class="flex items-center gap-2 text-sm text-muted dark:text-dark-subtle cursor-pointer">
                                <input type="checkbox" name="finishes" value="1" checked class="rounded border-warm text-primary-600 focus:ring-primary-600">
                                <span>{{ __('Interior Finishes & Paint Checked (Tiles, locks, doors, windows sealed)') }}</span>
                            </label>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-muted dark:text-dark-subtle mb-1">{{ __('Handover Notes & Meter Readings') }}</label>
                            <textarea name="notes" rows="2" class="input w-full" placeholder="{{ __('e.g. Electric meter #12344, 2 master keys released...') }}"></textarea>
                        </div>

                        <div class="flex justify-end gap-3 pt-3">
                            <button type="button" @click="selectedHandover = null" class="btn btn-secondary">{{ __('Cancel') }}</button>
                            <button type="submit" class="btn btn-primary">{{ __('Release Keys & Finalize') }}</button>
                        </div>
                    </form>
                </template>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
