<x-layouts.app>
    <x-slot name="title">{{ $project['name'] }} | Addis Developers</x-slot>

    <!-- Hero Section -->
    <section class="relative h-[60vh] min-h-[500px] w-full mt-16 md:mt-20 lg:mt-24">
        <img src="{{ $project['image'] }}" alt="{{ $project['name'] }}" class="absolute inset-0 w-full h-full object-cover">
        <div class="absolute inset-0 bg-black/50"></div>
        
        <div class="absolute inset-0 flex items-center">
            <div class="max-w-7xl mx-auto px-4 w-full">
                <div class="max-w-3xl">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary-500/20 border border-primary-500/30 backdrop-blur-md text-primary-300 text-xs font-bold uppercase tracking-wider mb-6">
                        <span class="w-2 h-2 rounded-full bg-primary-400"></span>
                        {{ str_replace('_', ' ', $project['status']) }}
                    </div>
                    <h1 class="text-4xl md:text-6xl font-black text-white font-display leading-tight mb-4">{{ $project['name'] }}</h1>
                    <p class="text-xl text-white/80 flex items-center gap-2 mb-8">
                        <svg class="w-5 h-5 text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        {{ $project['location'] }}
                    </p>
                    <div class="flex gap-4">
                        <button class="btn bg-primary-600 hover:bg-primary-700 text-white border-0 text-lg px-8 py-3 h-auto">{{ __('Register Interest') }}</button>
                        <button class="btn bg-white/10 hover:bg-white/20 text-white border-0 backdrop-blur-md text-lg px-8 py-3 h-auto">{{ __('View Units') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Overview Section -->
    <section class="py-20 bg-surface dark:bg-[#0B1120]">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-16">
                <div class="lg:col-span-2">
                    <h2 class="text-3xl font-extrabold text-[#0F172A] dark:text-white font-display mb-6">{{ __('About The Project') }}</h2>
                    <p class="text-lg text-[#4B5563] dark:text-[#CBD5E1] mb-6 leading-relaxed">
                        {{ $project['name'] }} sets a new standard for luxury living in {{ $project['location'] }}. Designed with modern aesthetics and sustainable principles, this development offers unparalleled comfort, breathtaking views, and world-class amenities.
                    </p>
                    <p class="text-lg text-[#4B5563] dark:text-[#CBD5E1] leading-relaxed">
                        Developed by {{ $project['developer'] }}, this project spans multiple high-rise towers featuring smart home technology, lush green spaces, and a vibrant community atmosphere right at your doorstep.
                    </p>
                </div>
                <div>
                    <div class="bg-white dark:bg-[#1E293B] rounded-3xl p-8 border border-warm dark:border-warm shadow-xl shadow-orange-950/5 dark:shadow-none">
                        <h3 class="text-xl font-bold text-[#0F172A] dark:text-white mb-6">{{ __('Project Details') }}</h3>
                        <ul class="space-y-6">
                            <li>
                                <p class="text-xs font-bold text-[#6B7280] dark:text-[#94A3B8] uppercase tracking-wider">{{ __('Developer') }}</p>
                                <p class="font-medium text-[#111827] dark:text-white mt-1 text-lg">{{ $project['developer'] }}</p>
                            </li>
                            <li>
                                <p class="text-xs font-bold text-[#6B7280] dark:text-[#94A3B8] uppercase tracking-wider">{{ __('Total Units') }}</p>
                                <p class="font-medium text-[#111827] dark:text-white mt-1 text-lg">{{ $project['total_units'] }} Units</p>
                            </li>
                            <li>
                                <p class="text-xs font-bold text-[#6B7280] dark:text-[#94A3B8] uppercase tracking-wider">{{ __('Expected Completion') }}</p>
                                <p class="font-medium text-[#111827] dark:text-white mt-1 text-lg">{{ date('F Y', strtotime($project['completion_date'])) }}</p>
                            </li>
                            <li>
                                <p class="text-xs font-bold text-[#6B7280] dark:text-[#94A3B8] uppercase tracking-wider">{{ __('Construction Progress') }}</p>
                                <div class="flex items-center gap-3 mt-1">
                                    <div class="flex-1 bg-surface-subtle dark:bg-surface rounded-full h-2">
                                        <div class="bg-primary-500 h-2 rounded-full" style="width: {{ $project['progress'] }}%"></div>
                                    </div>
                                    <span class="font-bold text-[#111827] dark:text-white">{{ $project['progress'] }}%</span>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

</x-layouts.app>
