<x-layouts.dashboard>
    <x-slot:title>{{ __('Notification Preferences - SRMS') }}</x-slot:title>

    <div class="max-w-4xl space-y-6">
        {{-- Breadcrumb Navigation --}}
        <div>
            <a href="{{ route('dashboard.notifications.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-500 hover:text-orange-600 dark:hover:text-orange-400 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                <span>{{ __('Back to Notification Center') }}</span>
            </a>
        </div>

        <x-page-header
            title="Notification Preferences"
            description="Configure how and when you receive automated emails, topbar popovers, and browser push alerts."
            icon="cog"
            badge="Preferences" />

        {{-- Preferences Form Card --}}
        <div class="card p-6 sm:p-8">
            <form method="POST" action="{{ route('dashboard.preferences.update') }}" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- Channel 1: In-App Notifications --}}
                <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-700/80 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-orange-500/10 text-orange-600 dark:text-orange-400 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                                {{ __('In-App & Topbar Badges') }}
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 leading-relaxed">
                                {{ __('Receive instant unread indicator badges, topbar flyout previews, and dashboard activity items.') }}
                            </p>
                        </div>
                    </div>

                    <label class="relative inline-flex items-center cursor-pointer flex-shrink-0 self-end sm:self-center">
                        <input type="checkbox" name="in_app_notifications" value="1" class="sr-only peer" {{ $preference->in_app_notifications ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-orange-600"></div>
                    </label>
                </div>

                {{-- Channel 2: Email Notifications --}}
                <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-700/80 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                                {{ __('Email Digest & Transaction Updates') }}
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 leading-relaxed">
                                {{ __('Send deal confirmations, escrow funding alerts, official title deed logs, and legal receipts directly to your email.') }}
                            </p>
                        </div>
                    </div>

                    <label class="relative inline-flex items-center cursor-pointer flex-shrink-0 self-end sm:self-center">
                        <input type="checkbox" name="email_notifications" value="1" class="sr-only peer" {{ $preference->email_notifications ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-orange-600"></div>
                    </label>
                </div>

                {{-- Channel 3: Browser Push Notifications --}}
                <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-700/80 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-sky-500/10 text-sky-600 dark:text-sky-400 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                                {{ __('Browser Push & SMS Urgent Alerts') }}
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 leading-relaxed">
                                {{ __('Send high-priority SMS or browser push notifications for time-sensitive escrow lockups or urgent inquiries.') }}
                            </p>
                        </div>
                    </div>

                    <label class="relative inline-flex items-center cursor-pointer flex-shrink-0 self-end sm:self-center">
                        <input type="checkbox" name="push_notifications" value="1" class="sr-only peer" {{ $preference->push_notifications ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-slate-600 peer-checked:bg-orange-600"></div>
                    </label>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <a href="{{ route('dashboard.notifications.index') }}" class="btn btn-secondary py-2.5 px-4 text-xs font-bold border-slate-200 dark:border-slate-700">
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit" class="btn btn-primary py-2.5 px-6 text-xs font-extrabold bg-[#C2410C] hover:bg-[#9A3412] text-white shadow-md shadow-orange-500/25 cursor-pointer">
                        {{ __('Save Preferences') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.dashboard>
