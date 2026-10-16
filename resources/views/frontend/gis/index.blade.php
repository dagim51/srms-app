<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Advanced Map Search') }}</x-slot>

    <!-- Full-bleed map container minus sidebar -->
    <div class="h-[calc(100vh-6rem)] -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 flex flex-col md:flex-row relative">
        
        <!-- Map Sidebar (Filters & Results) -->
        <div class="w-full md:w-96 bg-white dark:bg-[#1E293B] border-r border-warm dark:border-warm flex flex-col h-1/2 md:h-full z-10 shadow-xl overflow-hidden shrink-0 absolute md:static bottom-0 left-0">
            <!-- Filter Header -->
            <div class="p-4 border-b border-warm dark:border-warm sticky top-0 bg-white dark:bg-[#1E293B] z-20">
                <h2 class="font-bold text-muted dark:text-white mb-3">{{ __('Map Filters') }}</h2>
                <input type="text" placeholder="{{ __('Search neighborhood or zip...') }}" class="input w-full mb-2">
                <div class="flex gap-2 mb-3">
                    <select class="input text-xs py-1.5 px-2 h-auto flex-1">
                        <option>{{ __('Any Type') }}</option>
                        <option>{{ __('Apartment') }}</option>
                        <option>{{ __('Villa') }}</option>
                    </select>
                    <select class="input text-xs py-1.5 px-2 h-auto flex-1">
                        <option>{{ __('Any Price') }}</option>
                        <option>&lt; 20k ETB</option>
                        <option>{{ __('20k - 50k ETB') }}</option>
                        <option>&gt; 50k ETB</option>
                    </select>
                </div>
                <div class="flex items-center justify-between text-xs font-medium">
                    <span class="text-muted">{{ __('124 Results in view') }}</span>
                    <button class="text-primary-600 hover:text-primary-700">{{ __('Clear Area') }}</button>
                </div>
            </div>

            <!-- Property List (Visible on desktop or when expanded on mobile) -->
            <div class="flex-1 overflow-y-auto p-4 space-y-4">
                @foreach($properties as $prop)
                    @if(!$prop['cluster'])
                    <div class="border border-warm dark:border-warm rounded-xl p-3 hover:shadow-md transition-shadow bg-surface dark:bg-surface/30 cursor-pointer">
                        <div class="h-24 bg-surface-subtle dark:bg-surface rounded-lg mb-2 overflow-hidden">
                            <!-- Placeholder image -->
                        </div>
                        <h3 class="font-bold text-sm text-muted dark:text-white line-clamp-1">{{ $prop['title'] }}</h3>
                        <p class="font-black text-primary-600 text-sm">{{ $prop['price'] }}</p>
                    </div>
                    @endif
                @endforeach
            </div>
        </div>

        <!-- The Map Area -->
        <div class="flex-1 bg-surface-subtle dark:bg-surface relative">
            <!-- Simulated Map Background -->
            <div class="absolute inset-0 bg-[#e5e7eb] dark:bg-[#111827]" style="background-image: radial-gradient(circle, #cbd5e1 1px, transparent 1px); background-size: 20px 20px; opacity: 0.3;"></div>
            
            <!-- Map Controls (Drawing Tools etc) -->
            <div class="absolute top-4 right-4 bg-white dark:bg-[#1E293B] rounded-lg shadow-lg border border-warm dark:border-warm p-1 flex flex-col gap-1 z-10">
                <button class="p-2 text-muted hover:text-primary-600 hover:bg-surface dark:hover:bg-surface rounded-md" title="Draw Polygon Area">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4L4 8l8 4 8-4-8-4z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12l8 4 8-4M4 16l8 4 8-4" /></svg>
                </button>
                <button class="p-2 text-muted hover:text-primary-600 hover:bg-surface dark:hover:bg-surface rounded-md" title="Draw Radius">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle cx="12" cy="12" r="10" stroke-width="2"></circle><circle cx="12" cy="12" r="2" fill="currentColor"></circle></svg>
                </button>
                <button class="p-2 text-muted hover:text-primary-600 hover:bg-surface dark:hover:bg-surface rounded-md" title="Toggle Property Density Mode">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                </button>
            </div>

            <!-- Simulated Map Markers -->
            <div class="absolute inset-0 z-0">
                <!-- Single Marker -->
                <div class="absolute top-[30%] left-[40%] -translate-x-1/2 -translate-y-full cursor-pointer group">
                    <div class="bg-white dark:bg-[#1E293B] border-2 border-primary-600 rounded-full py-1 px-3 shadow-md text-sm font-bold text-muted dark:text-white group-hover:scale-110 transition-transform">
                        45k
                    </div>
                    <div class="w-0 h-0 border-l-8 border-r-8 border-t-8 border-transparent border-t-primary-600 absolute left-1/2 -translate-x-1/2 -bottom-2"></div>
                </div>

                <!-- Single Marker -->
                <div class="absolute top-[50%] left-[60%] -translate-x-1/2 -translate-y-full cursor-pointer group">
                    <div class="bg-white dark:bg-[#1E293B] border-2 border-primary-600 rounded-full py-1 px-3 shadow-md text-sm font-bold text-muted dark:text-white group-hover:scale-110 transition-transform">
                        85k
                    </div>
                    <div class="w-0 h-0 border-l-8 border-r-8 border-t-8 border-transparent border-t-primary-600 absolute left-1/2 -translate-x-1/2 -bottom-2"></div>
                </div>

                <!-- Cluster Marker -->
                <div class="absolute top-[65%] left-[30%] -translate-x-1/2 -translate-y-1/2 cursor-pointer group">
                    <div class="w-12 h-12 bg-primary-600/90 text-white rounded-full flex items-center justify-center font-bold shadow-lg border-2 border-white dark:border-[#1E293B] group-hover:bg-primary-500 transition-colors">
                        12
                    </div>
                    <!-- Outer pulse for large clusters -->
                    <div class="absolute inset-0 bg-primary-600 rounded-full animate-ping opacity-20"></div>
                </div>

                <!-- Cluster Marker -->
                <div class="absolute top-[20%] left-[70%] -translate-x-1/2 -translate-y-1/2 cursor-pointer group">
                    <div class="w-10 h-10 bg-primary-600/90 text-white rounded-full flex items-center justify-center font-bold shadow-lg border-2 border-white dark:border-[#1E293B] group-hover:bg-primary-500 transition-colors">
                        8
                    </div>
                </div>
                
                <!-- Simulated Drawn Polygon Area -->
                <svg class="absolute inset-0 w-full h-full pointer-events-none" viewBox="0 0 100 100" preserveAspectRatio="none">
                    <polygon points="30,20 75,15 80,60 40,80" fill="rgba(59, 130, 246, 0.1)" stroke="rgba(59, 130, 246, 0.8)" stroke-width="0.5" stroke-dasharray="1 1" />
                </svg>
            </div>
            
            <!-- Map Fallback / Loading state overlay -->
            <div class="absolute inset-0 bg-white/50 dark:bg-black/50 backdrop-blur-sm z-30 hidden flex-col items-center justify-center">
                <svg class="w-10 h-10 text-error-500 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                <p class="font-bold text-muted dark:text-white">{{ __('Map Failed to Load') }}</p>
                <p class="text-sm text-muted">{{ __('Please check your connection and try again.') }}</p>
                <button class="btn btn-secondary mt-4">{{ __('Switch to List View') }}</button>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
