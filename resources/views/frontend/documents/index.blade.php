<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Document Center') }}</x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="mb-8 flex justify-between items-end">
            <div>
                <h1 class="text-3xl font-extrabold font-display text-muted dark:text-white tracking-tight">{{ __('Document Center') }}</h1>
                <p class="text-muted dark:text-muted mt-2">{{ __('Secure file storage and identity verifications.') }}</p>
            </div>
            <button class="btn btn-primary">{{ __('Upload Document') }}</button>
        </div>

        @include('frontend.documents.partials.tabs')

        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6 mb-8">
            <x-card-stat
                :label="__('Total Files')"
                :value="$stats['total_documents']"
                iconColor="blue"
                :badge="__('Total')"
                badgeVariant="info"
                icon="document"
            />
            <x-card-stat
                :label="__('Pending Review')"
                :value="$stats['pending_verifications']"
                iconColor="amber"
                :badge="__('Pending')"
                badgeVariant="warning"
                icon="clock"
            />
            <x-card-stat
                :label="__('Updated (7d)')"
                :value="$stats['recently_updated']"
                iconColor="emerald"
                :badge="__('Recent')"
                badgeVariant="success"
                icon="check"
            />
            <x-card-stat
                :label="__('Storage Used')"
                :value="$stats['storage_used']"
                iconColor="purple"
                :badge="__('Storage')"
                badgeVariant="neutral"
                icon="database"
            />
        </div>

        <div class="card overflow-hidden">
            <div class="p-6 border-b border-slate-200/80 dark:border-slate-800/80 flex gap-4 flex-wrap">
                <input type="text" placeholder="{{ __('Search files...') }}" class="input w-full max-w-sm">
                <select class="input max-w-xs">
                    <option>{{ __('All Types') }}</option>
                    <option>{{ __('Identity Document') }}</option>
                    <option>{{ __('Contract') }}</option>
                    <option>{{ __('Financial Document') }}</option>
                </select>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-warm-50/50 dark:bg-[#1E293B]/50 border-b border-[#EBDBC9] dark:border-[#273244]">
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Document Name') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Owner') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Date') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Size') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EBDBC9]/50 dark:divide-[#273244]">
                        @foreach($documents as $doc)
                            <tr class="hover:bg-surface dark:hover:bg-surface/30 transition-colors">
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-surface-subtle dark:bg-surface flex items-center justify-center text-muted">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                                        </div>
                                        <div>
                                            <p class="font-bold text-muted dark:text-white">
                                                <a href="{{ route('dashboard.documents.show', $doc['id']) }}" class="hover:text-primary-600">{{ $doc['name'] }}</a>
                                            </p>
                                            <p class="text-xs text-muted">{{ $doc['type'] }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4 text-sm font-medium text-muted dark:text-white">{{ $doc['owner'] }}</td>
                                <td class="p-4 text-sm text-muted dark:text-dark-subtle">{{ date('M d, Y', strtotime($doc['date'])) }}</td>
                                <td class="p-4 text-sm text-muted dark:text-dark-subtle">{{ $doc['size'] }}</td>
                                <td class="p-4">
                                    @php
                                        $sClass = 'badge-neutral';
                                        if ($doc['status'] === 'verified' || $doc['status'] === 'active') $sClass = 'badge-success';
                                        if ($doc['status'] === 'pending') $sClass = 'badge-warning';
                                        if ($doc['status'] === 'rejected') $sClass = 'badge-error';
                                    @endphp
                                    <span class="badge {{ $sClass }} uppercase text-xs">{{ $doc['status'] }}</span>
                                </td>
                                <td class="p-4 text-right">
                                    <a href="{{ route('dashboard.documents.show', $doc['id']) }}" class="text-primary-600 hover:text-primary-700 font-medium text-sm">{{ __('View') }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
