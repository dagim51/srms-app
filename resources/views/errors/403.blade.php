<x-layouts.dashboard>
    <x-slot name="title">{{ __('Access Denied') }}</x-slot>

    @section('page-title', 'Unauthorized')

    <div class="flex items-center justify-center min-h-[60vh] page-enter">
        <div class="text-center max-w-md">
            <div class="w-20 h-20 bg-rose-50 dark:bg-rose-500/10 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                </svg>
            </div>
            <h1 class="font-display font-bold text-muted dark:text-white text-3xl mb-2">{{ __('Access Denied') }}</h1>
            <p class="text-muted dark:text-muted mb-8">{{ __('You don\'t have permission to view this page or perform this action. If you believe this is a mistake, contact your administrator.') }}</p>
            
            <div class="flex items-center justify-center gap-4">
                <a href="{{ url()->previous() !== url()->current() ? url()->previous() : '/dashboard' }}" class="btn btn-secondary">
                    Go Back
                </a>
                <a href="/dashboard" class="btn btn-primary">{{ __('Dashboard') }}</a>
            </div>
        </div>
    </div>
</x-layouts.dashboard>
