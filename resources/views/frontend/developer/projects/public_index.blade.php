<x-layouts.app>
    <x-slot name="title">{{ __('Developer Projects & Masterplan Developments | Aura Estates SRMS') }}</x-slot>
    <x-slot name="description">{{ __('Discover premier master-planned communities, luxury high-rises, and verified off-plan residential developments in Addis Ababa and Ethiopia.') }}</x-slot>

    <div class="py-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-12 min-h-screen space-y-8 pt-28">
        {{-- Clean Page Header (Aura Estates Style) --}}
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-slate-200 dark:border-slate-800 pb-6 cinematic-fade-up">
            <div class="max-w-3xl space-y-2">
                <nav class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 font-medium">
                    <a href="/" class="hover:text-orange-600 dark:hover:text-orange-400 transition-colors">{{ __('Home') }}</a>
                    <span>/</span>
                    <span class="text-slate-900 dark:text-white font-bold">{{ __('Developer Projects') }}</span>
                </nav>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-orange-100/80 dark:bg-orange-950/60 border border-orange-200 dark:border-orange-800/80 text-orange-700 dark:text-orange-300 text-xs font-semibold uppercase tracking-wider">
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-orange-500 animate-ping"></span>
                    <span>{{ __('Verified Off-Plan Developments') }}</span>
                </div>
                <h1 class="text-3xl sm:text-5xl font-serif font-bold tracking-tight text-slate-900 dark:text-white leading-tight">
                    {{ __('Masterplan Developments &') }} <span class="italic font-normal text-transparent bg-clip-text bg-gradient-to-r from-orange-600 to-amber-500 dark:from-orange-400 dark:to-amber-300">{{ __('Architectural') }}</span> {{ __('Estates') }}
                </h1>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed font-normal max-w-2xl">
                    {{ __('Explore high-rise luxury towers, gated communities, and smart compounds by Ethiopia\'s premier certified developers with milestone-linked escrow protection.') }}
                </p>
            </div>
        </div>

        {{-- Status Filter Bar --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 dark:border-slate-800 pb-4 cinematic-fade-up cinematic-stagger-1">
            <div class="flex items-center gap-2 overflow-x-auto pb-1">
                <a href="{{ route('developers.index', ['status' => 'all']) }}" 
                    class="px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition-all {{ $currentStatus === 'all' ? 'bg-orange-600 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:text-orange-600' }}">
                    {{ __('All Developments') }} ({{ count($projects) }})
                </a>
                <a href="{{ route('developers.index', ['status' => 'under_construction']) }}" 
                    class="px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition-all {{ $currentStatus === 'under_construction' ? 'bg-orange-600 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:text-orange-600' }}">
                    {{ __('Under Construction') }}
                </a>
                <a href="{{ route('developers.index', ['status' => 'planning']) }}" 
                    class="px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition-all {{ $currentStatus === 'planning' ? 'bg-orange-600 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:text-orange-600' }}">
                    {{ __('Planning Phase') }}
                </a>
            </div>

            <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">
                {{ __('Showing') }} <span class="font-bold text-slate-900 dark:text-white">{{ count($projects) }}</span> {{ __('active developments') }}
            </p>
        </div>

        {{-- Projects Grid (Aura Estates Cards) --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-8">
            @forelse($projects as $project)
                <div class="card bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 hover:border-orange-500/40 rounded-3xl overflow-hidden hover: transition-all duration-500 group flex flex-col justify-between cinematic-fade-up cinematic-stagger-{{ ($loop->index % 3) + 1 }}">
                    <div>
                        {{-- Project Media Preview --}}
                        <div class="relative aspect-[16/9] overflow-hidden bg-slate-900">
                            <img src="{{ $project['image'] }}" 
                                 alt="{{ $project['name'] }}" 
                                 loading="lazy"
                                 onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?auto=format&fit=crop&w=800&q=80';"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/85 via-slate-950/30 to-transparent"></div>
                            
                            {{-- Top Badges --}}
                            <div class="absolute top-4 left-4 right-4 flex justify-between items-center">
                                <span class="px-3 py-1 rounded-full text-xs font-bold shadow-md {{ $project['status'] === 'under_construction' ? 'bg-amber-500 text-slate-950' : 'bg-orange-600 text-white' }} uppercase tracking-wider text-xs">
                                    {{ str_replace('_', ' ', $project['status']) }}
                                </span>
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-950/80 backdrop-blur-md text-white text-xs">
                                    {{ __('Est.') }} {{ date('M Y', strtotime($project['completion_date'])) }}
                                </span>
                            </div>

                            {{-- Bottom Info on Image --}}
                            <div class="absolute bottom-4 left-4 right-4">
                                <h2 class="text-2xl font-bold font-serif text-white tracking-tight drop-shadow-sm">{{ $project['name'] }}</h2>
                                <p class="text-white/80 text-xs flex items-center gap-1.5 mt-1">
                                    <svg class="w-4 h-4 text-orange-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    <span>{{ $project['location'] }}</span>
                                </p>
                            </div>
                        </div>

                        {{-- Card Details --}}
                        <div class="p-6 space-y-5">
                            <div class="flex items-center justify-between text-xs pb-4 border-b border-slate-100 dark:border-slate-800">
                                <div>
                                    <p class="text-xs uppercase font-bold text-slate-400">{{ __('Developer') }}</p>
                                    <p class="font-bold text-slate-900 dark:text-white mt-0.5">{{ $project['developer'] }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs uppercase font-bold text-slate-400">{{ __('Inventory') }}</p>
                                    <p class="font-bold text-slate-900 dark:text-white mt-0.5">{{ $project['sold_units'] }} Sold / {{ $project['total_units'] }} Units</p>
                                </div>
                            </div>

                            {{-- Construction Progress Bar --}}
                            <div class="space-y-1.5">
                                <div class="flex justify-between items-center text-xs">
                                    <span class="font-semibold text-slate-600 dark:text-slate-300">{{ __('Construction Milestone Progress') }}</span>
                                    <span class="text-orange-600 dark:text-orange-400 font-bold">{{ $project['progress'] }}%</span>
                                </div>
                                <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2.5 overflow-hidden">
                                    <div class="bg-gradient-to-r from-orange-600 to-orange-500 h-2.5 rounded-full transition-all duration-1000" style="width: {{ $project['progress'] }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Actions Bar --}}
                    <div class="p-6 pt-0 flex items-center gap-3">
                        <a href="{{ route('projects.show', $project['id']) }}" class="btn btn-primary flex-1 text-xs font-semibold py-3 px-4 rounded-xl bg-gradient-to-r from-orange-600 to-orange-500 hover:from-orange-500 hover:to-orange-600 text-white shadow-md shadow-orange-500/20 flex items-center justify-center gap-2 transition-all">
                            <span>{{ __('Explore Project') }}</span>
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                        </a>
                        <a href="{{ route('dashboard.projects.show', $project['id']) }}" class="btn btn-secondary text-xs font-semibold py-3 px-4 rounded-xl border border-slate-200 dark:border-slate-700 hover:border-orange-500 text-slate-700 dark:text-slate-300 transition-colors flex items-center justify-center gap-1.5" title="View Units & Documents">
                            <span>{{ __('Units') }}</span>
                        </a>
                    </div>
                </div>
            @empty
                <div class="card col-span-2 text-center py-16 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800">
                    <p class="text-base text-slate-500">{{ __('No projects match the selected filter.') }}</p>
                    <a href="{{ route('developers.index') }}" class="btn btn-primary btn-sm mt-4 px-4 py-2 rounded-xl bg-orange-600 text-white font-semibold">{{ __('View All Developments') }}</a>
                </div>
            @endforelse
        </div>

        {{-- Off-Plan Trust Protections Banner (Obsidian Luxury Card) --}}
        <div class="card bg-slate-900 dark:bg-slate-950 text-white border border-slate-800 rounded-3xl p-8 sm:p-10 cinematic-fade-up cinematic-stagger-2">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl bg-orange-500/20 text-orange-400 flex items-center justify-center flex-shrink-0 font-serif font-bold text-lg">1</div>
                    <div>
                        <h3 class="text-base font-serif font-bold text-white">{{ __('Milestone Escrow Releases') }}</h3>
                        <p class="text-xs text-slate-300 mt-1 leading-relaxed">{{ __('Funds remain protected in verified banking escrow accounts and release only upon certified engineer milestone sign-offs.') }}</p>
                    </div>
                </div>
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl bg-orange-500/20 text-orange-400 flex items-center justify-center flex-shrink-0 font-serif font-bold text-lg">2</div>
                    <div>
                        <h3 class="text-base font-serif font-bold text-white">{{ __('Cadastral Title Verification') }}</h3>
                        <p class="text-xs text-slate-300 mt-1 leading-relaxed">{{ __('Every development site undergoes rigorous land bureau title checks to ensure unencumbered ownership.') }}</p>
                    </div>
                </div>
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl bg-orange-500/20 text-orange-400 flex items-center justify-center flex-shrink-0 font-serif font-bold text-lg">3</div>
                    <div>
                        <h3 class="text-base font-serif font-bold text-white">{{ __('Live Progress Audits') }}</h3>
                        <p class="text-xs text-slate-300 mt-1 leading-relaxed">{{ __('Monthly site inspection logs, drone photographic proof, and concrete curing tests published openly.') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
