<x-layouts.auth>
    <x-slot name="title">{{ __('Sign In') }}</x-slot>

    <div class="card bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-3xl p-7 sm:p-9 shadow-xl shadow-slate-950/5">
        {{-- Header --}}
        <div class="text-center mb-8">
            <div class="w-12 h-12 rounded-2xl bg-orange-100 dark:bg-orange-950/40 text-orange-600 dark:text-orange-400 flex items-center justify-center mx-auto mb-3 shadow-xs">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
            </div>
            <h1 class="font-display font-black text-slate-900 dark:text-white text-2xl mb-1.5">{{ __('Welcome back') }}</h1>
            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 font-medium">{{ __('Sign in to access your verified SRMS portal') }}</p>
        </div>

        {{-- Session Error --}}
        @if (session('error'))
            <div class="mb-6 p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-xs font-bold flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        {{-- Login Form --}}
        <form method="POST" action="/login" novalidate x-data="{ loading: false, showPass: false }" @submit="loading = true" class="space-y-5">
            @csrf

            {{-- Email Address --}}
            <div>
                <label for="email" class="block text-xs font-extrabold uppercase tracking-wider text-slate-800 dark:text-slate-200 mb-1.5">
                    {{ __('Email address') }}
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
                           autocomplete="email" 
                           placeholder="you@example.com" 
                           class="w-full pl-10 pr-3.5 py-3 text-xs sm:text-sm font-bold rounded-xl bg-white dark:bg-[#1E293B] border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white placeholder:text-slate-400 focus:border-orange-500 outline-none transition-colors">
                </div>
                @if($errors->has('email'))
                    <p class="text-rose-600 text-xs mt-1 font-bold">{{ $errors->first('email') }}</p>
                @endif
            </div>

            {{-- Password --}}
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="password" class="block text-xs font-extrabold uppercase tracking-wider text-slate-800 dark:text-slate-200">
                        {{ __('Password') }}
                    </label>
                    <a href="/forgot-password" class="text-xs font-bold text-orange-600 dark:text-orange-400 hover:underline" id="forgot-password-link">
                        {{ __('Forgot password?') }}
                    </a>
                </div>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-orange-500">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>
                    </div>
                    <input :type="showPass ? 'text' : 'password'" 
                           name="password" 
                           id="password" 
                           required 
                           autocomplete="current-password" 
                           placeholder="••••••••" 
                           class="w-full pl-10 pr-10 py-3 text-xs sm:text-sm font-bold rounded-xl bg-white dark:bg-[#1E293B] border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white placeholder:text-slate-400 focus:border-orange-500 outline-none transition-colors">
                    
                    <button type="button" @click="showPass = !showPass" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-500 hover:text-slate-900 dark:hover:text-white cursor-pointer" aria-label="Toggle password visibility">
                        <svg x-show="!showPass" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        <svg x-show="showPass" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" x-cloak><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                    </button>
                </div>
                @if($errors->has('password'))
                    <p class="text-rose-600 text-xs mt-1 font-bold">{{ $errors->first('password') }}</p>
                @endif
            </div>

            {{-- Remember Me --}}
            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}
                       class="w-4 h-4 rounded border-slate-300 text-orange-500 focus:ring-orange-500 cursor-pointer accent-orange-500">
                <label for="remember" class="text-xs font-bold text-slate-700 dark:text-slate-300 cursor-pointer">
                    {{ __('Remember me on this device') }}
                </label>
            </div>

            {{-- Submit Button --}}
            <button type="submit" 
                    class="btn btn-primary w-full py-3.5 rounded-2xl text-xs sm:text-sm font-extrabold bg-[#C2410C] hover:bg-[#9A3412] text-white shadow-lg shadow-orange-500/25 transition-all duration-200 active:scale-95 cursor-pointer flex items-center justify-center gap-2 mt-2">
                <span :class="{ 'hidden': loading }">{{ __('Sign In') }}</span>
                <span :class="{ 'hidden': !loading }" x-cloak class="flex items-center gap-2">
                    <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span>{{ __('Signing in...') }}</span>
                </span>
            </button>
        </form>

        <div class="h-px bg-slate-200 dark:bg-slate-800 my-7"></div>

        <p class="text-center text-xs sm:text-sm text-slate-700 dark:text-slate-300 font-bold">
            {{ __("Don't have an account?") }}
            <a href="/register" class="text-orange-600 dark:text-orange-400 font-extrabold hover:underline ml-1" id="goto-register">
                {{ __('Create one free') }}
            </a>
        </p>
    </div>
</x-layouts.auth>