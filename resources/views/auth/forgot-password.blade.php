<x-layouts.auth>
    <x-slot name="title">Forgot Password</x-slot>

    <div class="card p-8 page-enter">
        <div class="text-center mb-8">
            <h1 class="font-display font-bold text-gray-900 dark:text-white text-2xl mb-2">Reset Password</h1>
            <p class="text-gray-500 dark:text-gray-400 text-sm">Enter your email address and we'll send you a link to reset your password.</p>
        </div>

        @if (session('status'))
            <x-alert type="success" class="mb-6">
                {{ session('status') }}
            </x-alert>
        @endif

        <form method="POST" action="/forgot-password" novalidate x-data="{ loading: false }" @submit="loading = true">
            @csrf

            <div class="space-y-5">
                <div>
                    <x-label for="email" required>Email address</x-label>
                    <x-input type="email" id="email" name="email"
                             value="{{ old('email') }}"
                             autocomplete="email"
                             required autofocus
                             :error="$errors->has('email')"
                             placeholder="you@example.com" />
                    @error('email')
                        <p class="form-error" role="alert">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <x-button type="submit" class="w-full mt-7" x-bind:class="{ 'btn-loading': loading }" x-bind:disabled="loading">
                <span x-show="!loading">Send Reset Link</span>
                <span x-show="loading" x-cloak>Sending...</span>
            </x-button>
        </form>

        <div class="divider my-7"></div>

        <p class="text-center text-sm">
            <a href="/login" class="text-orange-500 font-semibold hover:text-orange-600 transition-colors flex items-center justify-center gap-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                Back to Sign in
            </a>
        </p>
    </div>
</x-layouts.auth>


