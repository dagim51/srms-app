<x-layouts.dashboard>
    <x-slot name="page-title">{{ $provider['name'] }} - Profile</x-slot>

    <div class="max-w-4xl mx-auto">
        <div class="mb-8">
            <a href="{{ route('dashboard.services.index') }}" class="text-sm font-medium text-muted hover:text-muted dark:hover:text-white flex items-center gap-2 mb-4 transition-colors w-max">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Back to Marketplace
            </a>
            
            <div class="flex flex-col sm:flex-row justify-between sm:items-end gap-6">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="badge badge-neutral uppercase text-xs">{{ $provider['category'] }}</span>
                        <span class="badge badge-success uppercase text-xs flex items-center gap-1">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                            Verified Provider
                        </span>
                    </div>
                    <h1 class="text-3xl font-extrabold font-display text-muted dark:text-white tracking-tight">{{ $provider['name'] }}</h1>
                    <div class="flex items-center gap-3 mt-2">
                        <span class="flex items-center gap-1 text-sm font-bold text-muted dark:text-white">
                            <svg class="w-4 h-4 text-warning-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            {{ $provider['rating'] }} 
                        </span>
                        <span class="text-sm text-muted underline cursor-pointer">{{ $provider['reviews'] }} Reviews</span>
                        <span class="text-muted dark:text-muted">•</span>
                        <span class="text-sm font-black text-primary-600">{{ $provider['rate'] }}</span>
                    </div>
                </div>
                <button class="btn btn-primary px-8">{{ __('Contact Provider') }}</button>
            </div>
        </div>

        <div class="space-y-8">
            <!-- About -->
            <div class="card p-6 md:p-8">
                <h2 class="card-title text-xl mb-4">{{ __('About') }}</h2>
                <p class="text-muted dark:text-dark-subtle leading-relaxed">{{ $provider['about'] }}</p>
            </div>

            <!-- Services Offered -->
            <div class="card p-6 md:p-8">
                <h2 class="card-title text-xl mb-6">{{ __('Services Offered') }}</h2>
                <div class="space-y-4">
                    @foreach($provider['services'] as $service)
                        <div class="card-nested p-4 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 hover:border-primary-300 dark:hover:border-primary-700/60 transition-colors">
                            <div>
                                <h3 class="font-bold text-muted dark:text-white">{{ $service['name'] }}</h3>
                                <p class="text-sm text-muted dark:text-muted flex items-center gap-1 mt-1">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    Est. {{ $service['duration'] }}
                                </p>
                            </div>
                            <div class="flex items-center gap-4 w-full md:w-auto justify-between md:justify-end">
                                <span class="font-black text-primary-600 dark:text-primary-400 text-base">{{ $service['price'] }}</span>
                                <button class="btn btn-secondary border-primary-200 text-primary-700 hover:bg-primary-50 dark:border-primary-800 dark:text-primary-300 px-6">{{ __('Request') }}</button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
