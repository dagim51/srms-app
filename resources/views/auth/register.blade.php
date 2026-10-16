<x-layouts.auth>
    <x-slot name="title">{{ __('Create Account') }}</x-slot>

    <div class="card bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-3xl p-7 sm:p-9 shadow-xl shadow-slate-950/5">
        {{-- Header --}}
        <div class="text-center mb-7">
            <div class="w-12 h-12 rounded-2xl bg-orange-100 dark:bg-orange-950/40 text-orange-600 dark:text-orange-400 flex items-center justify-center mx-auto mb-3 shadow-xs">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.765z" /></svg>
            </div>
            <h1 class="font-display font-black text-slate-900 dark:text-white text-2xl mb-1.5">{{ __('Create your account') }}</h1>
            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 font-medium">{{ __('Join thousands discovering verified properties across Ethiopia') }}</p>
        </div>

        <form method="POST" action="/register" novalidate x-data="{ loading: false, showPass: false, passVal: '' }" @submit="loading = true" class="space-y-4">
            @csrf

            {{-- Full Name --}}
            <div>
                <label for="name" class="block text-xs font-extrabold uppercase tracking-wider text-slate-800 dark:text-slate-200 mb-1">
                    {{ __('Full Name') }}
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-orange-500">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                    </div>
                    <input type="text" 
                           name="name" 
                           id="name" 
                           value="{{ old('name') }}" 
                           required 
                           autocomplete="name" 
                           placeholder="{{ __('Abebe Bikila') }}" 
                           class="w-full pl-10 pr-3.5 py-2.5 text-xs sm:text-sm font-bold rounded-xl bg-white dark:bg-[#1E293B] border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white placeholder:text-slate-400 focus:border-orange-500 outline-none transition-colors">
                </div>
                @if($errors->has('name'))
                    <p class="text-rose-600 text-xs mt-1 font-bold">{{ $errors->first('name') }}</p>
                @endif
            </div>

            {{-- Email Address --}}
            <div>
                <label for="email" class="block text-xs font-extrabold uppercase tracking-wider text-slate-800 dark:text-slate-200 mb-1">
                    {{ __('Email Address') }}
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
                           class="w-full pl-10 pr-3.5 py-2.5 text-xs sm:text-sm font-bold rounded-xl bg-white dark:bg-[#1E293B] border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white placeholder:text-slate-400 focus:border-orange-500 outline-none transition-colors">
                </div>
                @if($errors->has('email'))
                    <p class="text-rose-600 text-xs mt-1 font-bold">{{ $errors->first('email') }}</p>
                @endif
            </div>

            {{-- Phone Number --}}
            <div>
                <label for="phone" class="block text-xs font-extrabold uppercase tracking-wider text-slate-800 dark:text-slate-200 mb-1">
                    {{ __('Phone Number') }}
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-orange-500">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" /></svg>
                    </div>
                    <input type="tel" 
                           name="phone" 
                           id="phone" 
                           value="{{ old('phone') }}" 
                           autocomplete="tel" 
                           placeholder="+251 91 123 4567" 
                           class="w-full pl-10 pr-3.5 py-2.5 text-xs sm:text-sm font-bold rounded-xl bg-white dark:bg-[#1E293B] border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white placeholder:text-slate-400 focus:border-orange-500 outline-none transition-colors">
                </div>
            </div>

            {{-- Password --}}
            <div>
                <label for="password" class="block text-xs font-extrabold uppercase tracking-wider text-slate-800 dark:text-slate-200 mb-1">
                    {{ __('Password') }}
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-orange-500">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>
                    </div>
                    <input :type="showPass ? 'text' : 'password'" 
                           name="password" 
                           id="password" 
                           x-model="passVal"
                           required 
                           autocomplete="new-password" 
                           placeholder="{{ __('At least 8 characters') }}" 
                           class="w-full pl-10 pr-10 py-2.5 text-xs sm:text-sm font-bold rounded-xl bg-white dark:bg-[#1E293B] border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white placeholder:text-slate-400 focus:border-orange-500 outline-none transition-colors">
                    
                    <button type="button" @click="showPass = !showPass" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-500 hover:text-slate-900 dark:hover:text-white cursor-pointer" aria-label="Toggle password visibility">
                        <svg x-show="!showPass" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        <svg x-show="showPass" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" x-cloak><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                    </button>
                </div>
                @if($errors->has('password'))
                    <p class="text-rose-600 text-xs mt-1 font-bold">{{ $errors->first('password') }}</p>
                @endif
            </div>

            {{-- Confirm Password --}}
            <div>
                <label for="password_confirmation" class="block text-xs font-extrabold uppercase tracking-wider text-slate-800 dark:text-slate-200 mb-1">
                    {{ __('Confirm Password') }}
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-orange-500">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <input type="password" 
                           name="password_confirmation" 
                           id="password_confirmation" 
                           required 
                           autocomplete="new-password" 
                           placeholder="{{ __('Repeat your password') }}" 
                           class="w-full pl-10 pr-3.5 py-2.5 text-xs sm:text-sm font-bold rounded-xl bg-white dark:bg-[#1E293B] border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white placeholder:text-slate-400 focus:border-orange-500 outline-none transition-colors">
                </div>
            </div>

            {{-- Terms Agreement --}}
            <div class="flex items-start gap-2.5 pt-1">
                <input type="checkbox" id="terms" name="terms" required
                       class="w-4 h-4 mt-0.5 rounded border-slate-300 text-orange-500 focus:ring-orange-500 cursor-pointer accent-orange-500">
                <label for="terms" class="text-xs font-bold text-slate-700 dark:text-slate-300 cursor-pointer leading-relaxed">
                    {{ __("I agree to SRMS's") }}
                    <a href="/terms" class="text-orange-600 dark:text-orange-400 hover:underline" target="_blank">{{ __('Terms of Service') }}</a>
                    {{ __('and') }}
                    <a href="/privacy" class="text-orange-600 dark:text-orange-400 hover:underline" target="_blank">{{ __('Privacy Policy') }}</a>.
                </label>
            </div>

            {{-- Submit Button --}}
            <button type="submit" 
                    class="btn btn-primary w-full py-3.5 rounded-2xl text-xs sm:text-sm font-extrabold bg-gradient-to-r from-orange-500 via-orange-600 to-amber-600 hover:from-orange-600 hover:to-amber-700 text-white shadow-lg shadow-orange-500/25 transition-all duration-200 active:scale-95 cursor-pointer flex items-center justify-center gap-2 mt-4">
                <span :class="{ 'hidden': loading }">{{ __('Create Account') }}</span>
                <span :class="{ 'hidden': !loading }" x-cloak class="flex items-center gap-2">
                    <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span>{{ __('Creating account...') }}</span>
                </span>
            </button>
        </form>

        <div class="h-px bg-slate-200 dark:bg-slate-800 my-6"></div>

        <p class="text-center text-xs sm:text-sm text-slate-700 dark:text-slate-300 font-bold">
            {{ __('Already have an account?') }}
            <a href="/login" class="text-orange-600 dark:text-orange-400 font-extrabold hover:underline ml-1" id="goto-login">
                {{ __('Sign In') }}
            </a>
        </p>
    </div>
</x-layouts.auth>