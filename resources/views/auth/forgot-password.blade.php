<x-layouts.auth>
    <x-slot name="title">{{ __('Forgot Password') }}</x-slot>

    <div class="card bg-white dark:bg-[#1E293B] border border-slate-200 dark:border-slate-700 rounded-3xl p-7 sm:p-9">
        {{-- Header --}}
        <div class="text-center mb-7">
            <div class="w-12 h-12 rounded-2xl bg-orange-100 dark:bg-orange-950/40 text-orange-600 dark:text-orange-400 flex items-center justify-center mx-auto mb-3 shadow-xs">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>
            </div>
            <h1 class="font-display font-black text-[#000000] dark:text-white text-2xl mb-1.5">{{ __('Reset Password') }}</h1>
            <p class="text-xs sm:text-sm text-[#000000] dark:text-[#CBD5E1] font-medium leading-relaxed">{{ __("Enter your registered email address and we'll send you an encrypted reset link.") }}</p>
        </div>

        @if (session('status'))
            <div class="mb-6 p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-300 dark:border-emerald-800 text-emerald-900 dark:text-emerald-300 text-xs font-bold flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <form method="POST" action="/forgot-password" novalidate x-data="{ loading: false }" @submit="loading = true" class="space-y-5">
            @csrf

            {{-- Email Input --}}
            <div>
                <label for="email" class="block text-xs font-extrabold uppercase tracking-wider text-[#000000] dark:text-white mb-1.5">
                    {{ __('Registered Email Address') }}
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-orange-500">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" /></svg>
                    </div>
                    <input type="email" 
                           name="email" 
                           id="email" 
                           value="{{ old('email') }}" 
                           required 
                           autofocus
                           autocomplete="email" 
                           placeholder="you@example.com" 
                           class="w-full pl-10 pr-3.5 py-3 text-xs sm:text-sm font-bold rounded-xl bg-white dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 text-[#111827] dark:text-white placeholder:text-[#9CA3AF] focus:border-orange-500 outline-none transition-colors">
                </div>
                @if($errors->has('email'))
                    <p class="text-rose-600 text-xs mt-1 font-bold">{{ $errors->first('email') }}</p>
                @endif
            </div>

            <button type="submit" 
                    class="w-full py-3.5 rounded-2xl text-xs sm:text-sm font-extrabold bg-gradient-to-r from-orange-500 via-orange-600 to-amber-600 hover:from-orange-600 hover:to-amber-700 text-white shadow-lg shadow-orange-500/25 transition-all duration-200 active:scale-95 cursor-pointer flex items-center justify-center gap-2 mt-2">
                <span :class="{ 'hidden': loading }">{{ __('Send Reset Link') }}</span>
                <span :class="{ 'hidden': !loading }" x-cloak class="flex items-center gap-2">
                    <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span>{{ __('Sending link...') }}</span>
                </span>
            </button>
        </form>

        <div class="h-px bg-slate-200 dark:bg-slate-700 my-6"></div>

        <p class="text-center text-xs sm:text-sm">
            <a href="/login" class="text-orange-600 dark:text-orange-400 font-extrabold hover:underline inline-flex items-center gap-1.5" id="back-to-login">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                <span>{{ __('Back to Sign In') }}</span>
            </a>
        </p>
    </div>
</x-layouts.auth>