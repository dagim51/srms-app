<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Document Center') }}</x-slot>

    <div class="max-w-7xl mx-auto page-enter space-y-6">
        <x-page-header
            :title="__('Document Center')"
            :description="__('Secure file storage, identity verifications, contracts, and digital document management.')"
            icon="document"
        >
            <x-slot:actions>
                <button class="btn btn-primary shadow-xs hover:shadow transition-all inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    <span>{{ __('Upload Document') }}</span>
                </button>
            </x-slot:actions>
        </x-page-header>

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
            <div class="p-6 border-b border-slate-200/80 dark:border-slate-800/80">
                <form method="GET" action="{{ route('dashboard.documents.index') }}" class="flex flex-wrap items-center gap-3">
                    <div class="relative flex-1 min-w-56">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </div>
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="{{ __('Search files by ID, name, owner, or status...') }}"
                               class="form-input search-input pl-9 pr-8 w-full text-sm">
                        @if(request('search'))
                            <a href="{{ route('dashboard.documents.index') }}" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </a>
                        @endif
                    </div>
                    @if(request('search'))
                        <a href="{{ route('dashboard.documents.index') }}" class="text-xs text-orange-600 hover:text-orange-700 dark:text-orange-400 font-medium px-2 py-1.5">{{ __('Reset') }}</a>
                    @endif
                </form>
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
