<x-layouts.dashboard>
    <x-slot name="page-title">Case: {{ $case['id'] }}</x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="mb-8">
            <a href="{{ route('dashboard.trust.index') }}" class="text-sm font-medium text-muted hover:text-muted dark:hover:text-white flex items-center gap-2 mb-4 transition-colors w-max">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Back to Trust & Safety
            </a>
            
            <div class="flex flex-col sm:flex-row justify-between sm:items-end gap-4">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        @php
                            $sClass = 'badge-neutral';
                            if ($case['status'] === 'escalated') $sClass = 'badge-error';
                            if ($case['status'] === 'investigating') $sClass = 'badge-warning';
                            if ($case['status'] === 'resolved') $sClass = 'badge-success';
                        @endphp
                        <span class="badge {{ $sClass }} uppercase">{{ $case['status'] }}</span>
                        <span class="badge badge-neutral uppercase">{{ $case['type'] }}</span>
                        @if($case['priority'] === 'high')
                            <span class="inline-flex items-center gap-1.5 py-1 px-2 rounded-md bg-error-50 dark:bg-error-900/20 text-error-700 dark:text-error-400 font-bold text-xs">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                High Priority
                            </span>
                        @endif
                    </div>
                    <h1 class="text-3xl font-extrabold font-display text-muted dark:text-white tracking-tight">{{ $case['subject'] }}</h1>
                    <div class="flex items-center gap-3 mt-2 text-sm text-muted">
                        <span>Case {{ $case['id'] }}</span>
                        <span>&bull;</span>
                        <span>Filed on {{ $case['created_at'] }}</span>
                    </div>
                </div>
                <div class="flex gap-2 flex-wrap">
                    <button class="btn btn-secondary flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                        Lock Case
                    </button>
                    <button class="btn btn-primary bg-success-600 hover:bg-success-700 border-success-600">{{ __('Resolve Case') }}</button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-8">
                <!-- Overview -->
                <div class="card p-6 md:p-8">
                    <h2 class="card-title text-xl mb-6">{{ __('Case Summary') }}</h2>
                    
                    <div class="p-4 bg-warm-50/50 dark:bg-[#172033]/60 rounded-xl border border-[#EBDBC9] dark:border-[#273244] mb-6 text-sm text-muted dark:text-dark-subtle">
                        {{ $case['description'] }}
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
                        <div>
                            <p class="card-kv-label">{{ __('Plaintiff') }}</p>
                            <p class="card-kv-value font-medium mt-1">{{ $case['plaintiff'] }}</p>
                        </div>
                        <div>
                            <p class="card-kv-label">{{ __('Defendant') }}</p>
                            <p class="card-kv-value font-medium mt-1">{{ $case['defendant'] }}</p>
                        </div>
                        @if(isset($case['amount_contested']))
                        <div>
                            <p class="card-kv-label">{{ __('Contested Amount') }}</p>
                            <p class="card-kv-value font-black text-lg text-primary-600 dark:text-primary-400 mt-1">{{ $case['amount_contested'] }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Evidence -->
                <div class="card p-6 md:p-8">
                    <h2 class="card-title text-xl mb-6">{{ __('Evidence') }}</h2>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($evidence as $item)
                            <div class="card-nested p-4 flex gap-4 items-start">
                                <div class="w-10 h-10 rounded-lg bg-white dark:bg-surface flex items-center justify-center text-primary-600 shadow-xs border border-[#EBDBC9] dark:border-[#273244] shrink-0">
                                    @if($item['type'] === 'Image')
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    @else
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <p class="font-bold text-muted dark:text-white text-sm truncate" title="{{ $item['name'] }}">{{ $item['name'] }}</p>
                                    <p class="text-xs text-muted mt-0.5">Submitted by: {{ $item['submitted_by'] }}</p>
                                    <p class="text-xs text-muted mt-0.5">{{ date('M d, H:i', strtotime($item['date'])) }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Investigation Tools & Timeline -->
            <div class="space-y-8">
                <!-- Moderator Actions -->
                <div class="card p-6">
                    <h3 class="card-title text-base mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        Moderator Actions
                    </h3>
                    <div class="space-y-3">
                        <button class="btn btn-secondary w-full justify-between">
                            Message Plaintiff
                            <svg class="w-4 h-4 text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </button>
                        <button class="btn btn-secondary w-full justify-between">
                            Message Defendant
                            <svg class="w-4 h-4 text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </button>
                        <button class="btn btn-secondary w-full justify-between text-error-600 hover:bg-error-50 border-error-200">
                            Suspend Account(s)
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                        </button>
                    </div>
                </div>

                <!-- Timeline -->
                <div class="card p-6">
                    <h3 class="card-title text-base mb-6">{{ __('Investigation Timeline') }}</h3>
                    <div class="space-y-6 relative before:absolute before:inset-0 before:ml-5 before:-translate-x-px before:h-full before:w-0.5 before:bg-[#EBDBC9] dark:before:bg-[#273244]">
                        @foreach($timeline as $event)
                            <div class="relative flex items-center justify-between group is-active">
                                <div class="flex items-center justify-center w-10 h-10 rounded-full border-4 border-white dark:border-[#111827] bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 shrink-0 shadow-xs z-10">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                                <div class="w-[calc(100%-3.5rem)] card-nested p-3">
                                    <p class="font-bold text-sm text-muted dark:text-white">{{ $event['action'] }}</p>
                                    <div class="flex justify-between items-center mt-1">
                                        <p class="text-xs text-muted uppercase">{{ $event['user'] }}</p>
                                        <p class="text-xs text-muted font-mono">{{ date('H:i', strtotime($event['date'])) }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
