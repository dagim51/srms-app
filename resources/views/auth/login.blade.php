<x-layouts.auth>
    <x-slot name="title">Sign In</x-slot>

    <div class="card p-8 page-enter">
        {{-- Header --}}
        <div class="text-center mb-8">
            <h1 class="font-display font-bold text-gray-900 dark:text-white text-2xl mb-2">Welcome back</h1>
            <p class="text-gray-500 dark:text-gray-400 text-sm">Sign in to your SRMS account</p>
        </div>

        {{-- Session Error --}}
        @if (session('error'))
            <div class="alert alert-error mb-6" role="alert">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        {{-- Login Form --}}
        <form method="POST" action="/login" novalidate x-data="{ loading: false }" @submit="loading = true">
            @csrf

            <div class="space-y-5">
                {{-- Email --}}
                <div>
                    <label for="email" class="form-label form-label-required">Email address</label>
                    <input type="email" id="email" name="email"
                           value="{{ old('email') }}"
                           autocomplete="email"
                           required
                           class="form-input {{ $errors->has('email') ? 'input-error' : '' }}"
                           placeholder="you@example.com">
                    @error('email')
                        <p class="form-error" role="alert">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" /></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Password --}}
                <div x-data="{ show: false }">
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="form-label form-label-required mb-0">Password</label>
                        <a href="/forgot-password" class="text-xs text-orange-500 hover:text-orange-600 transition-colors" id="forgot-password-link">Forgot password?</a>
                    </div>
                    <div class="relative">
                        <input :type="show ? 'text' : 'password'" id="password" name="password"
                               autocomplete="current-password"
                               required
                               class="form-input pr-10 {{ $errors->has('password') ? 'input-error' : '' }}"
                               placeholder="••••••••">
                        <button type="button" @click="show = !show"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
                                :aria-label="show ? 'Hide password' : 'Show password'">
                            <svg x-show="!show" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            <svg x-show="show" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" x-cloak><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="form-error" role="alert">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" /></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Remember --}}
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}
                           class="w-4 h-4 rounded border-gray-300 text-orange-500 focus:ring-orange-500 cursor-pointer accent-orange-500">
                    <label for="remember" class="text-sm text-gray-600 dark:text-gray-400 cursor-pointer">Remember me for 30 days</label>
                </div>
            </div>

            {{-- Submit --}}
            <button type="submit" id="login-submit"
                    class="btn btn-primary w-full mt-7"
                    :class="{ 'btn-loading': loading }"
                    :disabled="loading">
                <span x-show="!loading">Sign In</span>
                <span x-show="loading" x-cloak>Signing in...</span>
            </button>
        </form>

        <div class="divider my-7"></div>

        <p class="text-center text-sm text-gray-500 dark:text-gray-400">
            Don't have an account?
            <a href="/register" class="text-orange-500 font-semibold hover:text-orange-600 transition-colors ml-1" id="goto-register">Create one free</a>
        </p>
    </div>
</x-layouts.auth>
