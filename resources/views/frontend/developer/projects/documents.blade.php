<x-layouts.dashboard>
    <x-slot name="page-title">{{ $project['name'] }} - Documents</x-slot>

    <div class="max-w-7xl mx-auto page-enter">
        <div class="mb-4">
            <a href="{{ route('dashboard.projects.index') }}" class="text-sm font-medium text-muted hover:text-muted dark:hover:text-white flex items-center gap-2 transition-colors w-max">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Back to Projects
            </a>
        </div>

        <x-page-header
            :title="$project['name'] . ' - Documents'"
            description="Access floor plans, engineering permits, architectural blueprints, and titles."
            icon="document"
            badge="Documents">
            <x-slot:actions>
                <button class="btn btn-primary">{{ __('Upload Document') }}</button>
            </x-slot:actions>
        </x-page-header>

        @include('frontend.developer.projects.partials.tabs')

        <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-3xl overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface dark:bg-surface/50 border-b border-warm dark:border-warm">
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Document Name') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Type / Size') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Uploaded By') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Date') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-warm dark:divide-dark-border">
                        @foreach($documents as $doc)
                            <tr class="hover:bg-surface dark:hover:bg-surface/30 transition-colors">
                                <td class="p-4 font-bold text-muted dark:text-white flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                                    </div>
                                    {{ $doc['name'] }}
                                </td>
                                <td class="p-4">
                                    <span class="text-sm text-muted dark:text-white">{{ $doc['type'] }}</span>
                                    <span class="text-xs text-muted dark:text-muted ml-2">{{ $doc['size'] }}</span>
                                </td>
                                <td class="p-4 text-muted dark:text-dark-subtle">{{ $doc['uploaded_by'] }}</td>
                                <td class="p-4 text-muted dark:text-dark-subtle">{{ $doc['date'] }}</td>
                                <td class="p-4 text-right">
                                    <button class="text-primary-600 hover:text-primary-700 font-medium text-sm mr-3">{{ __('Download') }}</button>
                                    <button class="text-muted hover:text-error-600"><svg class="w-4 h-4 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
