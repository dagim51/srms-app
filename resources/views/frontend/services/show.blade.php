<x-layouts.dashboard>
    <x-slot name="page-title">{{ $provider['name'] }} - Profile</x-slot>

    <div class="max-w-4xl mx-auto page-enter">
        <div class="mb-4">
            <a href="{{ route('dashboard.services.index') }}" class="text-sm font-medium text-muted hover:text-muted dark:hover:text-white flex items-center gap-2 transition-colors w-max">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Back to Marketplace
            </a>
        </div>

        <x-page-header
            :title="$provider['name']"
            :description="$provider['category'] . ' • Rating: ' . $provider['rating'] . ' (' . $provider['reviews'] . ' reviews) • ' . $provider['rate']"
            icon="users"
            :badge="strtoupper($provider['category'])">
            <x-slot:actions>
                <button class="btn btn-primary px-8">{{ __('Contact Provider') }}</button>
            </x-slot:actions>
        </x-page-header>

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
