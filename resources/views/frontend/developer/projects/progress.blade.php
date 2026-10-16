<x-layouts.dashboard>
    <x-slot name="page-title">{{ $project['name'] }} - Progress</x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="mb-8 flex justify-between items-end">
            <div>
                <a href="{{ route('dashboard.projects.index') }}" class="text-sm font-medium text-muted hover:text-muted dark:hover:text-white flex items-center gap-2 mb-4 transition-colors w-max">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    Back to Projects
                </a>
                <h1 class="text-3xl font-extrabold font-display text-muted dark:text-white tracking-tight">{{ $project['name'] }}</h1>
            </div>
            <button class="btn btn-primary">{{ __('Update Progress') }}</button>
        </div>

        @include('frontend.developer.projects.partials.tabs')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2">
                <div class="card p-6 md:p-8">
                    <h2 class="card-title text-xl mb-6">{{ __('Construction Timeline') }}</h2>
                    
                    <div class="space-y-6 relative before:absolute before:inset-0 before:ml-5 before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-gradient-to-b before:from-transparent before:via-[#EBDBC9] dark:before:via-[#273244] before:to-transparent">
                        @foreach($milestones as $index => $item)
                            <div class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
                                <!-- Icon -->
                                <div class="flex items-center justify-center w-10 h-10 rounded-full border-4 border-white dark:border-[#111827] {{ $item['status'] === 'completed' ? 'bg-primary-500 text-white' : ($item['status'] === 'in_progress' ? 'bg-amber-500 text-white' : 'bg-surface-subtle dark:bg-surface text-muted') }} shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 shadow-xs z-10">
                                    @if($item['status'] === 'completed')
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    @elseif($item['status'] === 'in_progress')
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    @else
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    @endif
                                </div>
                                
                                <!-- Card -->
                                <div class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] card-nested p-5 {{ $item['status'] === 'in_progress' ? 'ring-2 ring-primary-500/20 border-primary-500' : '' }}">
                                    <div class="flex items-center justify-between mb-2">
                                        <h3 class="font-bold text-muted dark:text-white">{{ $item['title'] }}</h3>
                                    </div>
                                    <div class="text-sm text-muted mb-3">
                                        {{ $item['date'] }}
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <div class="flex-1 bg-surface-subtle dark:bg-surface rounded-full h-1.5">
                                            <div class="bg-primary-500 h-1.5 rounded-full" style="width: {{ $item['status'] === 'completed' ? '100' : ($item['status'] === 'in_progress' ? '50' : '0') }}%"></div>
                                        </div>
                                        <span class="text-xs font-bold text-muted dark:text-dark-subtle">{{ $item['percentage'] }}%</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            
            <div>
                <div class="card p-6 sticky top-6">
                    <h3 class="card-title text-base mb-4">{{ __('Overall Progress') }}</h3>
                    
                    <div class="flex justify-center mb-6">
                        <div class="relative w-32 h-32 flex items-center justify-center rounded-full bg-surface-subtle dark:bg-surface">
                            <!-- SVG pie chart simulation -->
                            <div class="absolute inset-0 bg-primary-500 rounded-full" style="clip-path: polygon(50% 50%, 50% 0%, 100% 0%, 100% 100%, 0% 100%, 0% 80%);"></div>
                            <div class="absolute inset-2 bg-surface dark:bg-[#111827] rounded-full flex items-center justify-center flex-col">
                                <span class="text-2xl font-black text-muted dark:text-white">{{ $project['progress'] }}%</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-4 text-sm">
                        <div class="flex justify-between pb-2 border-b border-[#EBDBC9] dark:border-[#273244]">
                            <span class="text-muted">{{ __('Current Phase') }}</span>
                            <span class="font-bold text-muted dark:text-white">{{ __('Structural') }}</span>
                        </div>
                        <div class="flex justify-between pb-2 border-b border-[#EBDBC9] dark:border-[#273244]">
                            <span class="text-muted">{{ __('Expected Completion') }}</span>
                            <span class="font-bold text-muted dark:text-white">{{ date('M d, Y', strtotime($project['completion_date'])) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted">{{ __('Status') }}</span>
                            <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ __('On Schedule') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
