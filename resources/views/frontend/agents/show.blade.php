<x-layouts.app>
    <x-slot name="title">{{ $agent['name'] }} - {{ $agent['title'] }} | SRMS</x-slot>

    <div class="page-container pt-32 pb-24 max-w-7xl relative z-10 page-enter">
        
        <x-page-header
            :title="$agent['name'] . ' - ' . $agent['title']"
            :description="'Licensed Real Estate Agent • ' . count($listings) . ' Active Listings'"
            icon="user"
            badge="Agent Profile">
            <x-slot:actions>
                <button class="btn btn-primary">{{ __('Contact Agent') }}</button>
            </x-slot:actions>
        </x-page-header>
        <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-3xl overflow-hidden shadow-sm mb-8">
            <div class="h-32 bg-primary-600 w-full relative">
                <!-- Cover Image / Brand Color -->
            </div>
            
            <div class="px-8 pb-8 relative">
                <div class="flex flex-col sm:flex-row gap-6 items-start sm:items-end -mt-16 mb-6">
                    <div class="w-32 h-32 rounded-full border-4 border-white dark:border-[#1E293B] bg-surface-subtle dark:bg-surface overflow-hidden shadow-md shrink-0 flex items-center justify-center text-4xl font-bold text-muted">
                        {{ substr($agent['name'], 0, 1) }}
                    </div>
                    
                    <div class="flex-1">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div>
                                <h1 class="text-3xl font-black text-muted dark:text-white">{{ $agent['name'] }}</h1>
                                <p class="text-lg text-primary-600 dark:text-primary-400 font-medium">{{ $agent['title'] }}</p>
                            </div>
                            <div class="flex items-center gap-3 shrink-0">
                                <button class="btn btn-secondary">{{ __('Contact Agent') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
                    <div class="lg:col-span-2 space-y-8">
                        <div>
                            <h2 class="text-xl font-bold text-muted dark:text-white mb-3">About {{ $agent['name'] }}</h2>
                            <p class="text-muted dark:text-dark-subtle leading-relaxed">{{ $agent['bio'] }}</p>
                        </div>
                        
                        <div>
                            <h2 class="text-xl font-bold text-muted dark:text-white mb-4">{{ __('Active Listings') }}</h2>
                            <div class="space-y-4">
                                @foreach($listings as $listing)
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 border border-warm dark:border-warm rounded-2xl hover:border-warm transition-colors">
                                        <div class="mb-3 sm:mb-0">
                                            <h3 class="font-bold text-muted dark:text-white">{{ $listing['title'] }}</h3>
                                            <p class="text-sm text-muted">{{ $listing['type'] }} &bull; {{ $listing['status'] }}</p>
                                        </div>
                                        <div class="text-left sm:text-right">
                                            <p class="font-bold text-muted dark:text-white">{{ $listing['price'] }}</p>
                                            <a href="#" class="text-sm font-medium text-primary-600 hover:underline">{{ __('View Property') }}</a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-6">
                        <div class="bg-surface dark:bg-surface/50 p-6 rounded-2xl border border-warm dark:border-warm">
                            <h3 class="font-bold text-muted dark:text-white mb-4">{{ __('Contact Information') }}</h3>
                            <ul class="space-y-4">
                                <li class="flex items-center gap-3 text-muted dark:text-dark-subtle">
                                    <svg class="w-5 h-5 text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                    {{ $agent['organization'] }}
                                </li>
                                <li class="flex items-center gap-3 text-muted dark:text-dark-subtle">
                                    <svg class="w-5 h-5 text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                    {{ $agent['email'] }}
                                </li>
                                <li class="flex items-center gap-3 text-muted dark:text-dark-subtle">
                                    <svg class="w-5 h-5 text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                                    {{ $agent['phone'] }}
                                </li>
                            </ul>
                        </div>
                        
                        <div class="bg-surface dark:bg-surface/50 p-6 rounded-2xl border border-warm dark:border-warm">
                            <h3 class="font-bold text-muted dark:text-white mb-4">{{ __('Service Areas') }}</h3>
                            <div class="flex flex-wrap gap-2">
                                @foreach($agent['service_areas'] as $area)
                                    <span class="badge badge-neutral">{{ $area }}</span>
                                @endforeach
                            </div>
                        </div>
                        
                        <div class="bg-surface dark:bg-surface/50 p-6 rounded-2xl border border-warm dark:border-warm">
                            <h3 class="font-bold text-muted dark:text-white mb-4">{{ __('Agent Rating') }}</h3>
                            <div class="flex items-center gap-2">
                                <span class="text-3xl font-black text-muted dark:text-white">{{ $agent['rating'] }}</span>
                                <div>
                                    <div class="flex text-amber-400">
                                        @for($i=0; $i<5; $i++)
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                        @endfor
                                    </div>
                                    <span class="text-sm text-muted">{{ $agent['reviews_count'] }} reviews</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-layouts.app>
