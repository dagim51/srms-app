<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Professional Services') }}</x-slot>

    <div class="max-w-7xl mx-auto">
        <x-page-header
            :title="__('Professional Services')"
            :description="__('Find and hire verified real estate lawyers, certified land surveyors, architects, and engineering consultants.')"
            icon="users"
            badge="Marketplace"
        >
            <x-slot:actions>
                <a href="{{ route('dashboard.services.dashboard') }}" class="btn btn-secondary border-orange-200 text-[#C2410C] hover:bg-orange-50 dark:hover:bg-orange-950/30 font-semibold shadow-xs">
                    {{ __('Switch to Provider View') }}
                </a>
            </x-slot:actions>
        </x-page-header>

        <!-- Search & Categories -->
        <div class="mb-8 space-y-4">
            <div class="flex gap-4">
                <input type="text" placeholder="{{ __('Search for a service or professional...') }}" class="input flex-1">
                <button class="btn btn-primary">{{ __('Search') }}</button>
            </div>
            <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-hide">
                <a href="{{ route('dashboard.services.index') }}" class="btn {{ !request('category') ? 'bg-surface text-white' : 'bg-white dark:bg-surface text-muted dark:text-dark-subtle border-warm dark:border-warm hover:bg-surface' }} border-none rounded-full whitespace-nowrap px-6">{{ __('All Categories') }}</a>
                @foreach($categories as $cat)
                    <a href="{{ route('dashboard.services.index', ['category' => $cat]) }}" class="btn {{ request('category') == $cat ? 'bg-surface text-white border-none' : 'bg-white dark:bg-surface text-muted dark:text-dark-subtle border-warm dark:border-warm hover:bg-surface' }} rounded-full whitespace-nowrap px-6">{{ $cat }}</a>
                @endforeach
            </div>
        </div>

        <!-- Professionals Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($professionals as $pro)
                <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-3xl p-6 shadow-sm hover:shadow-md transition-shadow group flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-start mb-4">
                            <span class="badge badge-neutral uppercase text-xs">{{ $pro['category'] }}</span>
                            <span class="flex items-center gap-1 text-sm font-bold text-muted dark:text-white">
                                <svg class="w-4 h-4 text-warning-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                {{ $pro['rating'] }} <span class="text-muted font-normal">({{ $pro['reviews'] }})</span>
                            </span>
                        </div>
                        <h3 class="font-bold text-lg text-muted dark:text-white mb-1">{{ $pro['name'] }}</h3>
                        <p class="text-sm font-black text-primary-600 mb-6">{{ $pro['rate'] }}</p>
                    </div>
                    <a href="{{ route('dashboard.services.show', $pro['id']) }}" class="btn btn-secondary w-full justify-center group-hover:bg-primary-50 dark:group-hover:bg-primary-900/10 group-hover:text-primary-600 group-hover:border-primary-200 transition-colors">{{ __('View Profile') }}</a>
                </div>
            @endforeach
        </div>

    </div>
</x-layouts.dashboard>
