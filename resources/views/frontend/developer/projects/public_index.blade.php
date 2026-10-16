<x-layouts.app>
    <x-slot name="title">{{ __('Developer Projects & New Developments in Ethiopia | SRMS') }}</x-slot>
    <x-slot name="description">Discover premier master-planned communities, luxury high-rises, and verified off-plan residential developments in Addis Ababa and Ethiopia.</x-slot>

    <div class="py-8 page-container min-h-screen space-y-6 pt-24">
        {{-- Clean Page Header --}}
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-warm dark:border-warm pb-5">
            <div class="max-w-3xl space-y-2">
                <nav class="flex items-center gap-2 text-xs text-muted dark:text-muted">
                    <a href="/" class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors">{{ __('Home') }}</a>
                    <span>/</span>
                    <span class="text-[#111827] dark:text-white font-medium">{{ __('Developer Projects') }}</span>
                </nav>
                <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full bg-orange-500/10 border border-orange-500/20 text-orange-600 dark:bg-primary-500/20 dark:border-primary-500/30 dark:text-primary-300 text-xs font-bold uppercase tracking-wider">
                    <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-orange-500"></span>
                    Verified Off-Plan Developments
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold font-display tracking-tight text-[#0F172A] dark:text-white leading-tight">
                    New Developments & <span class="text-gradient-brand">{{ __('Master-Planned') }}</span> Estates
                </h1>
                <p class="text-xs sm:text-sm text-[#4B5563] dark:text-[#CBD5E1] leading-relaxed font-normal max-w-2xl">{{ __('Explore high-rise luxury towers, gated communities, and smart estates by Ethiopia\'s premier certified developers with milestone-linked escrow protection.') }}</p>
            </div>
        </div>

        {{-- Status Filter Bar --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-warm dark:border-warm pb-4">
            <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-hide">
                <a href="{{ route('developers.index', ['status' => 'all']) }}" 
                    class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all {{ $currentStatus === 'all' ? 'bg-primary-500 text-white shadow-sm' : 'bg-surface-subtle hover:bg-surface-subtle text-[#4B5563] dark:bg-surface dark:text-[#CBD5E1] dark:hover:bg-surface' }}">
                    All Developments ({{ count($projects) }})
                </a>
                <a href="{{ route('developers.index', ['status' => 'under_construction']) }}" 
                    class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all {{ $currentStatus === 'under_construction' ? 'bg-primary-500 text-white shadow-sm' : 'bg-surface-subtle hover:bg-surface-subtle text-[#4B5563] dark:bg-surface dark:text-[#CBD5E1] dark:hover:bg-surface' }}">
                    Under Construction
                </a>
                <a href="{{ route('developers.index', ['status' => 'planning']) }}" 
                    class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all {{ $currentStatus === 'planning' ? 'bg-primary-500 text-white shadow-sm' : 'bg-surface-subtle hover:bg-surface-subtle text-[#4B5563] dark:bg-surface dark:text-[#CBD5E1] dark:hover:bg-surface' }}">
                    Planning Phase
                </a>
            </div>

            <p class="text-xs text-muted dark:text-muted font-medium">
                Showing <span class="font-bold text-[#111827] dark:text-white">{{ count($projects) }}</span> active developments
            </p>
        </div>

        {{-- Projects Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-8">
            @forelse($projects as $project)
                <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-3xl overflow-hidden shadow-xl shadow-orange-950/5 hover:shadow-2xl hover:shadow-orange-950/10 dark:shadow-black/60 dark:hover:shadow-black/80 hover:-translate-y-2 transition-all duration-300 group flex flex-col justify-between">
                    <div>
                        {{-- Project Media Preview --}}
                        <div class="relative aspect-[16/9] overflow-hidden bg-surface-subtle dark:bg-surface">
                            <img src="{{ $project['image'] }}" 
                                 alt="{{ $project['name'] }}" 
                                 loading="lazy"
                                 onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?auto=format&fit=crop&w=800&q=80';"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent"></div>
                            
                            {{-- Top Badges --}}
                            <div class="absolute top-4 left-4 right-4 flex justify-between items-center">
                                <span class="px-3 py-1 rounded-full text-xs font-bold shadow-md {{ $project['status'] === 'under_construction' ? 'bg-amber-500 text-white' : 'bg-primary-600 text-white' }} uppercase tracking-wider text-xs">
                                    {{ str_replace('_', ' ', $project['status']) }}
                                </span>
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-black/60 backdrop-blur-md text-white text-xs">
                                    Est. {{ date('M Y', strtotime($project['completion_date'])) }}
                                </span>
                            </div>

                            {{-- Bottom Info on Image --}}
                            <div class="absolute bottom-4 left-4 right-4">
                                <h2 class="text-2xl font-black font-display text-white tracking-tight drop-shadow-sm">{{ $project['name'] }}</h2>
                                <p class="text-white/80 text-xs flex items-center gap-1.5 mt-1">
                                    <svg class="w-4 h-4 text-primary-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    <span>{{ $project['location'] }}</span>
                                </p>
                            </div>
                        </div>

                        {{-- Card Details --}}
                        <div class="p-6 space-y-5">
                            <div class="flex items-center justify-between text-xs pb-4 border-b border-warm dark:border-warm">
                                <div>
                                    <p class="text-xs uppercase font-bold text-muted">{{ __('Developer') }}</p>
                                    <p class="font-bold text-[#111827] dark:text-white mt-0.5">{{ $project['developer'] }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs uppercase font-bold text-muted">{{ __('Inventory') }}</p>
                                    <p class="font-bold text-[#111827] dark:text-white mt-0.5">{{ $project['sold_units'] }} Sold / {{ $project['total_units'] }} Units</p>
                                </div>
                            </div>

                            {{-- Construction Progress Bar --}}
                            <div class="space-y-1.5">
                                <div class="flex justify-between items-center text-xs">
                                    <span class="font-semibold text-[#4B5563] dark:text-[#CBD5E1]">{{ __('Construction Milestone Progress') }}</span>
                                    <span class="text-primary-600 dark:text-primary-400 font-extrabold">{{ $project['progress'] }}%</span>
                                </div>
                                <div class="w-full bg-surface-subtle dark:bg-surface rounded-full h-2.5 overflow-hidden">
                                    <div class="bg-gradient-to-r from-orange-500 to-primary-600 h-2.5 rounded-full transition-all duration-1000" style="width: {{ $project['progress'] }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Actions Bar --}}
                    <div class="p-6 pt-0 flex items-center gap-3">
                        <a href="{{ route('projects.show', $project['id']) }}" class="btn btn-primary flex-1 text-xs font-bold py-3 flex items-center justify-center gap-2 active:scale-[0.98] transition-transform">
                            <span>{{ __('Explore Project') }}</span>
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                        </a>
                        <a href="{{ route('dashboard.projects.show', $project['id']) }}" class="btn btn-outline text-xs font-bold py-3 px-4 flex items-center justify-center gap-1.5 active:scale-[0.98] transition-transform" title="View Units & Documents">
                            <span>{{ __('Units') }}</span>
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-2 text-center py-16 bg-white dark:bg-[#1E293B] rounded-3xl border border-warm dark:border-warm">
                    <p class="text-base text-muted">{{ __('No projects match the selected filter.') }}</p>
                    <a href="{{ route('developers.index') }}" class="btn btn-primary btn-sm mt-4">{{ __('View All Developments') }}</a>
                </div>
            @endforelse
        </div>

        {{-- Off-Plan Trust Protections Banner --}}
        <div class="bg-gradient-to-br from-orange-50 to-orange-100/60 dark:from-orange-950/20 dark:to-orange-900/10 border border-orange-200 dark:border-orange-900/40 rounded-3xl p-8 sm:p-10">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-2xl bg-orange-500/10 dark:bg-orange-500/20 text-orange-600 dark:text-orange-400 flex items-center justify-center flex-shrink-0 font-bold">1</div>
                    <div>
                        <h3 class="text-base font-bold text-muted dark:text-white">{{ __('Milestone Escrow Releases') }}</h3>
                        <p class="text-xs text-muted dark:text-muted mt-1 leading-relaxed">{{ __('Funds remain protected in verified escrow accounts and release only upon certified engineer milestone sign-offs.') }}</p>
                    </div>
                </div>
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-2xl bg-orange-500/10 dark:bg-orange-500/20 text-orange-600 dark:text-orange-400 flex items-center justify-center flex-shrink-0 font-bold">2</div>
                    <div>
                        <h3 class="text-base font-bold text-muted dark:text-white">{{ __('Cadastral Title Verification') }}</h3>
                        <p class="text-xs text-muted dark:text-muted mt-1 leading-relaxed">{{ __('Every development site undergoes rigorous land bureau title checks to ensure unencumbered leasehold ownership.') }}</p>
                    </div>
                </div>
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-2xl bg-orange-500/10 dark:bg-orange-500/20 text-orange-600 dark:text-orange-400 flex items-center justify-center flex-shrink-0 font-bold">3</div>
                    <div>
                        <h3 class="text-base font-bold text-muted dark:text-white">{{ __('Live Progress Audits') }}</h3>
                        <p class="text-xs text-muted dark:text-muted mt-1 leading-relaxed">{{ __('Monthly site inspection logs, drone photographic proof, and concrete curing tests published openly.') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
