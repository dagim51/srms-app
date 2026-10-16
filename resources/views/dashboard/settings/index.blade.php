<x-layouts.dashboard>
    <x-slot name="title">{{ __('Account & Platform Settings') }}</x-slot>

    @section('breadcrumb')
        <a href="/dashboard" class="hover:text-muted dark:hover:text-muted">{{ __('Dashboard') }}</a>
        <span class="mx-1.5 text-muted dark:text-muted">/</span>
        <span class="text-muted dark:text-dark-subtle">{{ __('Settings') }}</span>
    @endsection

    <div x-data="{
        currentTab: new URLSearchParams(window.location.search).get('tab') || '{{ $activeTab ?? 'profile' }}',
        setTab(tab) {
            this.currentTab = tab;
            const url = new URL(window.location);
            url.searchParams.set('tab', tab);
            window.history.replaceState({}, '', url);
        }
    }" class="max-w-6xl mx-auto space-y-6">

        {{-- Flash Alerts --}}
        @if (session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span class="text-sm font-semibold">{{ session('success') }}</span>
                </div>
                <button type="button" @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">&times;</button>
            </div>
        @endif

        @if (session('error'))
            <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-rose-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
                    <span class="text-sm font-semibold">{{ session('error') }}</span>
                </div>
                <button type="button" @click="$el.parentElement.remove()" class="text-rose-500 hover:text-rose-700">&times;</button>
            </div>
        @endif

        {{-- Page Header Card --}}
        <div class="card p-6 sm:p-8 flex flex-col sm:flex-row sm:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl bg-orange-100 dark:bg-orange-950/50 text-orange-600 dark:text-orange-400 flex items-center justify-center font-bold text-2xl flex-shrink-0 shadow-xs">
                    {{ strtoupper(substr($user->name ?? 'A', 0, 1)) }}
                </div>
                <div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h1 class="text-2xl font-extrabold font-display text-muted dark:text-white tracking-tight">
                            {{ $user->name ?? 'Account Settings' }}
                        </h1>
                        <span class="badge badge-brand text-xs font-bold px-2.5 py-0.5">
                            {{ $user->role ?? 'Administrator' }}
                        </span>
                    </div>
                    <p class="text-xs sm:text-sm text-muted dark:text-muted mt-1">
                        {{ $user->email ?? 'admin@srms.et' }} &bull; {{ __('Organization:') }} {{ $organization->name ?? 'Default Org' }}
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-3 self-start sm:self-center">
                <a href="{{ route('dashboard.admin.permissions') }}" class="btn btn-secondary btn-sm text-xs font-bold inline-flex items-center gap-2">
                    <svg class="w-4 h-4 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg>
                    {{ __('View Permissions Matrix') }}
                </a>
            </div>
        </div>

        {{-- Tab Navigation Bar --}}
        <div class="border-b border-[#EBDBC9] dark:border-[#273244] overflow-x-auto">
            <nav class="flex space-x-2 sm:space-x-6 min-w-max pb-1">
                <button type="button" @click="setTab('profile')"
                        :class="currentTab === 'profile' ? 'border-orange-500 text-orange-600 dark:text-orange-400 font-bold' : 'border-transparent text-muted hover:text-muted dark:text-muted'"
                        class="py-3 px-2 border-b-2 text-sm transition-colors inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                    {{ __('Profile & Account') }}
                </button>

                <button type="button" @click="setTab('organization')"
                        :class="currentTab === 'organization' ? 'border-orange-500 text-orange-600 dark:text-orange-400 font-bold' : 'border-transparent text-muted hover:text-muted dark:text-muted'"
                        class="py-3 px-2 border-b-2 text-sm transition-colors inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.75a1.5 1.5 0 011.5-1.5h1.5a1.5 1.5 0 011.5 1.5V21" /></svg>
                    {{ __('Organization & Agency') }}
                </button>

                <button type="button" @click="setTab('regional')"
                        :class="currentTab === 'regional' ? 'border-orange-500 text-orange-600 dark:text-orange-400 font-bold' : 'border-transparent text-muted hover:text-muted dark:text-muted'"
                        class="py-3 px-2 border-b-2 text-sm transition-colors inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918" /></svg>
                    {{ __('Regional & Localization') }}
                </button>

                <button type="button" @click="setTab('notifications')"
                        :class="currentTab === 'notifications' ? 'border-orange-500 text-orange-600 dark:text-orange-400 font-bold' : 'border-transparent text-muted hover:text-muted dark:text-muted'"
                        class="py-3 px-2 border-b-2 text-sm transition-colors inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" /></svg>
                    {{ __('Notifications') }}
                </button>

                <button type="button" @click="setTab('security')"
                        :class="currentTab === 'security' ? 'border-orange-500 text-orange-600 dark:text-orange-400 font-bold' : 'border-transparent text-muted hover:text-muted dark:text-muted'"
                        class="py-3 px-2 border-b-2 text-sm transition-colors inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>
                    {{ __('Security & Sessions') }}
                </button>

                <button type="button" @click="setTab('platform')"
                        :class="currentTab === 'platform' ? 'border-orange-500 text-orange-600 dark:text-orange-400 font-bold' : 'border-transparent text-muted hover:text-muted dark:text-muted'"
                        class="py-3 px-2 border-b-2 text-sm transition-colors inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 6a7.5 7.5 0 107.5 7.5h-7.5V6z" /></svg>
                    {{ __('Platform & Fees') }}
                </button>
            </nav>
        </div>

        {{-- ═══════════════════════════════════════════════════════════
             TAB 1: PROFILE & ACCOUNT
             ═══════════════════════════════════════════════════════════ --}}
        <div x-show="currentTab === 'profile'" class="space-y-6">
            <div class="card p-6 sm:p-8">
                <form action="{{ route('dashboard.settings.profile') }}" method="POST" class="space-y-6">
                    @csrf
                    <div>
                        <h3 class="text-lg font-bold font-display text-muted dark:text-white">{{ __('Personal Information') }}</h3>
                        <p class="text-xs text-muted dark:text-muted mt-0.5">{{ __('Update your personal credentials and communication preferences.') }}</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label class="form-label form-label-required font-bold">{{ __('Full Name') }}</label>
                            <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}" required class="form-input">
                        </div>

                        <div>
                            <label class="form-label form-label-required font-bold">{{ __('Email Address') }}</label>
                            <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" required class="form-input">
                        </div>

                        <div>
                            <label class="form-label font-bold">{{ __('Phone Number (Ethiopia)') }}</label>
                            <input type="text" name="phone" value="{{ old('phone', $user->phone ?? '+251 911 000 111') }}" class="form-input">
                        </div>

                        <div>
                            <label class="form-label form-label-required font-bold">{{ __('System Language') }}</label>
                            <select name="locale" class="form-input cursor-pointer font-medium">
                                <option value="en" {{ ($user->locale ?? 'en') === 'en' ? 'selected' : '' }}>English (US)</option>
                                <option value="am" {{ ($user->locale ?? 'en') === 'am' ? 'selected' : '' }}>አማርኛ (Amharic)</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center justify-end pt-4 border-t border-[#EBDBC9] dark:border-[#273244]">
                        <button type="submit" class="btn btn-primary font-bold px-6 py-2.5 text-xs shadow-xs">
                            {{ __('Save Profile Changes') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════
             TAB 2: ORGANIZATION & AGENCY
             ═══════════════════════════════════════════════════════════ --}}
        <div x-show="currentTab === 'organization'" class="space-y-6" x-cloak>
            <div class="card p-6 sm:p-8 space-y-6">
                <div>
                    <h3 class="text-lg font-bold font-display text-muted dark:text-white">{{ __('Real Estate Agency / Organization Details') }}</h3>
                    <p class="text-xs text-muted dark:text-muted mt-0.5">{{ __('Commercial registration and municipal licensing details.') }}</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label class="form-label font-bold">{{ __('Organization Name') }}</label>
                        <input type="text" value="{{ $organization->name ?? '' }}" class="form-input">
                    </div>

                    <div>
                        <label class="form-label font-bold">{{ __('TIN / Tax Identification Number') }}</label>
                        <input type="text" value="{{ $organization->tin ?? '0049281729' }}" class="form-input font-mono">
                    </div>

                    <div>
                        <label class="form-label font-bold">{{ __('Official Contact Email') }}</label>
                        <input type="email" value="{{ $organization->email ?? '' }}" class="form-input">
                    </div>

                    <div>
                        <label class="form-label font-bold">{{ __('Primary Switchboard Phone') }}</label>
                        <input type="text" value="{{ $organization->phone ?? '' }}" class="form-input">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="form-label font-bold">{{ __('Physical Headquarters Address') }}</label>
                        <input type="text" value="{{ $organization->address ?? '' }}" class="form-input">
                    </div>
                </div>

                <div class="flex items-center justify-end pt-4 border-t border-[#EBDBC9] dark:border-[#273244]">
                    <button type="button" class="btn btn-primary font-bold px-6 py-2.5 text-xs shadow-xs">
                        {{ __('Update Agency Profile') }}
                    </button>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════
             TAB 3: REGIONAL & LOCALIZATION
             ═══════════════════════════════════════════════════════════ --}}
        <div x-show="currentTab === 'regional'" class="space-y-6" x-cloak>
            <div class="card p-6 sm:p-8 space-y-6">
                <div>
                    <h3 class="text-lg font-bold font-display text-muted dark:text-white">{{ __('Regional Defaults & Metrics') }}</h3>
                    <p class="text-xs text-muted dark:text-muted mt-0.5">{{ __('Calibrate measurement standards and currency displays.') }}</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label class="form-label font-bold">{{ __('Default Currency Display') }}</label>
                        <select class="form-input cursor-pointer font-medium">
                            <option value="ETB" selected>Ethiopian Birr (ETB / ብር)</option>
                            <option value="USD">United States Dollar (USD $)</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label font-bold">{{ __('Area Measurement Standard') }}</label>
                        <select class="form-input cursor-pointer font-medium">
                            <option value="sqm" selected>Square Meters (m²)</option>
                            <option value="kare">Kare (ካሬ)</option>
                            <option value="sqft">Square Feet (sq ft)</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label font-bold">{{ __('Calendar & Date System') }}</label>
                        <select class="form-input cursor-pointer font-medium">
                            <option value="gregorian" selected>Gregorian Calendar (YYYY-MM-DD)</option>
                            <option value="ethiopian">Ethiopian Calendar (ዘመነ ሉቃስ)</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label font-bold">{{ __('Timezone') }}</label>
                        <input type="text" value="Africa/Addis_Ababa (EAT, UTC+3)" readonly class="form-input bg-[#FFF5EB]/60 dark:bg-[#172033]/60 cursor-not-allowed">
                    </div>
                </div>

                <div class="flex items-center justify-end pt-4 border-t border-[#EBDBC9] dark:border-[#273244]">
                    <button type="button" class="btn btn-primary font-bold px-6 py-2.5 text-xs shadow-xs">
                        {{ __('Save Localization Settings') }}
                    </button>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════
             TAB 4: NOTIFICATIONS
             ═══════════════════════════════════════════════════════════ --}}
        <div x-show="currentTab === 'notifications'" class="space-y-6" x-cloak>
            <div class="card p-6 sm:p-8">
                <form action="{{ route('dashboard.settings.notifications') }}" method="POST" class="space-y-6">
                    @csrf
                    <div>
                        <h3 class="text-lg font-bold font-display text-muted dark:text-white">{{ __('Notification Channels & Triggers') }}</h3>
                        <p class="text-xs text-muted dark:text-muted mt-0.5">{{ __('Select which channels you wish to receive operational alerts on.') }}</p>
                    </div>

                    <div class="space-y-4 divide-y divide-[#EBDBC9]/50 dark:divide-[#273244]">
                        <div class="pt-4 flex items-center justify-between">
                            <div>
                                <span class="text-sm font-bold text-muted dark:text-white">{{ __('New Property Inquiries & Leads') }}</span>
                                <p class="text-xs text-muted mt-0.5">{{ __('Instant alerts when a tenant or buyer requests a viewing or submits an inquiry.') }}</p>
                            </div>
                            <input type="checkbox" checked class="rounded border-[#EBDBC9] text-orange-500 focus:ring-orange-500 w-5 h-5">
                        </div>

                        <div class="pt-4 flex items-center justify-between">
                            <div>
                                <span class="text-sm font-bold text-muted dark:text-white">{{ __('Escrow Payments & Payout Receipts') }}</span>
                                <p class="text-xs text-muted mt-0.5">{{ __('SMS notification via Ethio Telecom whenever a Telebirr or CBE Birr deposit clears.') }}</p>
                            </div>
                            <input type="checkbox" checked class="rounded border-[#EBDBC9] text-orange-500 focus:ring-orange-500 w-5 h-5">
                        </div>

                        <div class="pt-4 flex items-center justify-between">
                            <div>
                                <span class="text-sm font-bold text-muted dark:text-white">{{ __('Lease Expirations & Rent Overdue Alerts') }}</span>
                                <p class="text-xs text-muted mt-0.5">{{ __('30-day and 15-day automated reminders for upcoming lease renewals.') }}</p>
                            </div>
                            <input type="checkbox" checked class="rounded border-[#EBDBC9] text-orange-500 focus:ring-orange-500 w-5 h-5">
                        </div>

                        <div class="pt-4 flex items-center justify-between">
                            <div>
                                <span class="text-sm font-bold text-muted dark:text-white">{{ __('Moderation & Title Deed Decisions') }}</span>
                                <p class="text-xs text-muted mt-0.5">{{ __('Immediate feedback on listing approval or required deed clarifications.') }}</p>
                            </div>
                            <input type="checkbox" checked class="rounded border-[#EBDBC9] text-orange-500 focus:ring-orange-500 w-5 h-5">
                        </div>
                    </div>

                    <div class="flex items-center justify-end pt-4 border-t border-[#EBDBC9] dark:border-[#273244]">
                        <button type="submit" class="btn btn-primary font-bold px-6 py-2.5 text-xs shadow-xs">
                            {{ __('Save Notification Preferences') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════
             TAB 5: SECURITY & SESSIONS
             ═══════════════════════════════════════════════════════════ --}}
        <div x-show="currentTab === 'security'" class="space-y-6" x-cloak>
            
            {{-- Password Change Card --}}
            <div class="card p-6 sm:p-8">
                <form action="{{ route('dashboard.settings.security') }}" method="POST" class="space-y-6">
                    @csrf
                    <div>
                        <h3 class="text-lg font-bold font-display text-muted dark:text-white">{{ __('Change Password') }}</h3>
                        <p class="text-xs text-muted dark:text-muted mt-0.5">{{ __('Protect your account with a strong, distinct passphrase.') }}</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                        <div>
                            <label class="form-label form-label-required font-bold">{{ __('Current Password') }}</label>
                            <input type="password" name="current_password" required class="form-input">
                        </div>
                        <div>
                            <label class="form-label form-label-required font-bold">{{ __('New Password') }}</label>
                            <input type="password" name="password" required class="form-input">
                        </div>
                        <div>
                            <label class="form-label form-label-required font-bold">{{ __('Confirm New Password') }}</label>
                            <input type="password" name="password_confirmation" required class="form-input">
                        </div>
                    </div>

                    <div class="flex items-center justify-end pt-4 border-t border-[#EBDBC9] dark:border-[#273244]">
                        <button type="submit" class="btn btn-primary font-bold px-6 py-2.5 text-xs shadow-xs">
                            {{ __('Update Password') }}
                        </button>
                    </div>
                </form>
            </div>

            {{-- Active Devices & Sessions --}}
            <div class="card p-6 sm:p-8 space-y-4">
                <div>
                    <h3 class="text-lg font-bold font-display text-muted dark:text-white">{{ __('Active Device Sessions') }}</h3>
                    <p class="text-xs text-muted dark:text-muted mt-0.5">{{ __('Devices currently authenticated to your SRMS account.') }}</p>
                </div>

                <div class="space-y-3">
                    @foreach($activeSessions as $session)
                        <div class="p-4 rounded-2xl bg-[#FFF5EB]/60 dark:bg-[#172033]/60 border border-[#EBDBC9] dark:border-[#273244] flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3.5">
                                <div class="w-10 h-10 rounded-xl bg-orange-100 dark:bg-orange-950/40 text-orange-600 dark:text-orange-400 flex items-center justify-center font-bold">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0H3" /></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-muted dark:text-white">{{ $session['device'] }}</p>
                                    <p class="text-xs text-muted dark:text-muted mt-0.5">{{ $session['ip'] }} &bull; {{ $session['last_active'] }}</p>
                                </div>
                            </div>
                            @if($session['current'])
                                <span class="badge badge-success text-xs font-bold">{{ __('This Device') }}</span>
                            @else
                                <button type="button" class="btn btn-outline text-xs py-1 px-3 text-rose-600 border-rose-300">
                                    {{ __('Revoke') }}
                                </button>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

        </div>

        {{-- ═══════════════════════════════════════════════════════════
             TAB 6: PLATFORM & FEES (ADMIN)
             ═══════════════════════════════════════════════════════════ --}}
        <div x-show="currentTab === 'platform'" class="space-y-6" x-cloak>
            <div class="card p-6 sm:p-8">
                <form action="{{ route('dashboard.settings.platform') }}" method="POST" class="space-y-6">
                    @csrf
                    <div>
                        <h3 class="text-lg font-bold font-display text-muted dark:text-white">{{ __('Platform Fees & Gateway Integration') }}</h3>
                        <p class="text-xs text-muted dark:text-muted mt-0.5">{{ __('Configure marketplace fee structures and national payment provider rails.') }}</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                        <div>
                            <label class="form-label font-bold">{{ __('Marketplace Commission Fee (%)') }}</label>
                            <input type="number" step="0.1" value="{{ $platformSettings['platform_fee_percent'] }}" class="form-input">
                        </div>

                        <div>
                            <label class="form-label font-bold">{{ __('National VAT Rate (%)') }}</label>
                            <input type="number" step="0.1" value="{{ $platformSettings['vat_percent'] }}" class="form-input">
                        </div>

                        <div>
                            <label class="form-label font-bold">{{ __('Escrow Payout Cycle') }}</label>
                            <select class="form-input cursor-pointer font-medium">
                                <option>{{ __('Daily Settlement') }}</option>
                                <option>{{ __('Weekly Settlement') }}</option>
                                <option selected>{{ __('Monthly (1st of Month)') }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-[#EBDBC9] dark:border-[#273244]">
                        <h4 class="text-sm font-bold text-muted dark:text-white mb-3">{{ __('Payment Rail Integrations') }}</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="p-4 rounded-2xl bg-[#FFEFE0] dark:bg-[#172033] border border-[#EBDBC9] dark:border-[#273244] flex items-center justify-between">
                                <div>
                                    <span class="text-sm font-bold text-muted dark:text-white">Telebirr (Ethio Telecom)</span>
                                    <p class="text-xs text-muted">SuperApp USSD & QR Payment Rail</p>
                                </div>
                                <span class="badge badge-success text-xs font-bold">{{ __('Connected') }}</span>
                            </div>

                            <div class="p-4 rounded-2xl bg-[#FFEFE0] dark:bg-[#172033] border border-[#EBDBC9] dark:border-[#273244] flex items-center justify-between">
                                <div>
                                    <span class="text-sm font-bold text-muted dark:text-white">Commercial Bank of Ethiopia (CBE Birr)</span>
                                    <p class="text-xs text-muted">Core Direct Gateway API</p>
                                </div>
                                <span class="badge badge-success text-xs font-bold">{{ __('Connected') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end pt-4 border-t border-[#EBDBC9] dark:border-[#273244]">
                        <button type="submit" class="btn btn-primary font-bold px-6 py-2.5 text-xs shadow-xs">
                            {{ __('Save Platform Settings') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
