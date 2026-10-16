<x-layouts.app>
    <x-slot name="title">{{ __('Notification Preferences - SRMS') }}</x-slot>

    <div class="page-container pt-32 pb-24 max-w-3xl relative z-10">
        
        <div class="mb-8">
            <a href="{{ route('dashboard.notifications.index') }}" class="text-sm font-medium text-muted hover:text-muted dark:hover:text-white flex items-center gap-2 mb-6 transition-colors w-max">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Back to Notifications
            </a>
            <h1 class="text-3xl font-extrabold font-display text-muted dark:text-white tracking-tight">{{ __('Notification Preferences') }}</h1>
            <p class="text-muted dark:text-muted mt-2">{{ __('Control how and when you want to be notified.') }}</p>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 text-emerald-800 dark:text-emerald-300 font-medium flex items-center gap-2 border border-emerald-100 dark:border-emerald-800/50">
                <svg class="w-5 h-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ session('success') }}
            </div>
        @endif

        <div class="card p-6 md:p-8">
            <form action="{{ route('dashboard.preferences.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="space-y-8">
                    
                    {{-- Email Notifications --}}
                    <div>
                        <h3 class="card-kv-label mb-4 flex items-center gap-2">
                            <svg class="w-4 h-4 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                            {{ __('Email Notifications') }}
                        </h3>
                        
                        <div class="space-y-4">
                            <label class="card-nested p-4 flex items-start gap-4 cursor-pointer group hover:border-primary-300 dark:hover:border-primary-700/60 transition-colors">
                                <div class="relative flex items-center mt-1">
                                    <input type="checkbox" name="email_booking_updates" class="peer sr-only" {{ $preferences['email_booking_updates'] ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-surface-subtle dark:bg-surface rounded-full peer peer-checked:bg-primary-600 transition-colors duration-300"></div>
                                    <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition-transform duration-300 peer-checked:translate-x-5 shadow-sm"></div>
                                </div>
                                <div class="flex-1">
                                    <span class="block font-bold text-muted dark:text-white">{{ __('Booking Updates') }}</span>
                                    <span class="block text-sm text-muted dark:text-muted mt-0.5">{{ __('Receive emails about your reservations, check-ins, and cancellations.') }}</span>
                                </div>
                            </label>

                            <label class="card-nested p-4 flex items-start gap-4 cursor-pointer group hover:border-primary-300 dark:hover:border-primary-700/60 transition-colors">
                                <div class="relative flex items-center mt-1">
                                    <input type="checkbox" name="email_marketing" class="peer sr-only" {{ $preferences['email_marketing'] ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-surface-subtle dark:bg-surface rounded-full peer peer-checked:bg-primary-600 transition-colors duration-300"></div>
                                    <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition-transform duration-300 peer-checked:translate-x-5 shadow-sm"></div>
                                </div>
                                <div class="flex-1">
                                    <span class="block font-bold text-muted dark:text-white">{{ __('Marketing & Promos') }}</span>
                                    <span class="block text-sm text-muted dark:text-muted mt-0.5">{{ __('Receive occasional emails about new properties, features, and offers.') }}</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <hr class="border-[#EBDBC9] dark:border-[#273244]">

                    {{-- Push / SMS Notifications --}}
                    <div>
                        <h3 class="card-kv-label mb-4 flex items-center gap-2">
                            <svg class="w-4 h-4 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                            {{ __('Mobile Notifications') }}
                        </h3>
                        
                        <div class="space-y-4">
                            <label class="card-nested p-4 flex items-start gap-4 cursor-pointer group hover:border-primary-300 dark:hover:border-primary-700/60 transition-colors">
                                <div class="relative flex items-center mt-1">
                                    <input type="checkbox" name="push_messages" class="peer sr-only" {{ $preferences['push_messages'] ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-surface-subtle dark:bg-surface rounded-full peer peer-checked:bg-primary-600 transition-colors duration-300"></div>
                                    <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition-transform duration-300 peer-checked:translate-x-5 shadow-sm"></div>
                                </div>
                                <div class="flex-1">
                                    <span class="block font-bold text-muted dark:text-white">{{ __('In-App Messages') }}</span>
                                    <span class="block text-sm text-muted dark:text-muted mt-0.5">{{ __('Get push notifications when a host or guest messages you.') }}</span>
                                </div>
                            </label>

                            <label class="card-nested p-4 flex items-start gap-4 cursor-pointer group hover:border-primary-300 dark:hover:border-primary-700/60 transition-colors">
                                <div class="relative flex items-center mt-1">
                                    <input type="checkbox" name="sms_booking_updates" class="peer sr-only" {{ $preferences['sms_booking_updates'] ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-surface-subtle dark:bg-surface rounded-full peer peer-checked:bg-primary-600 transition-colors duration-300"></div>
                                    <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition-transform duration-300 peer-checked:translate-x-5 shadow-sm"></div>
                                </div>
                                <div class="flex-1">
                                    <span class="block font-bold text-muted dark:text-white">{{ __('SMS Alerts') }}</span>
                                    <span class="block text-sm text-muted dark:text-muted mt-0.5">{{ __('Receive text messages for urgent booking updates and reminders.') }}</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="pt-6 text-right">
                        <button type="submit" class="btn btn-primary px-8 py-3 w-full sm:w-auto">{{ __('Save Preferences') }}</button>
                    </div>

                </div>
            </form>
        </div>

    </div>
</x-layouts.app>
