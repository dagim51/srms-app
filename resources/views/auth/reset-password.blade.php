<x-layouts.auth>
    <x-slot name="title">{{ __('Create New Password') }}</x-slot>

    <div class="card p-8 page-enter">
        <div class="text-center mb-8">
            <h1 class="font-display font-bold text-muted dark:text-white text-2xl mb-2">{{ __('Create new password') }}</h1>
            <p class="text-muted dark:text-muted text-sm">{{ __('Please enter your new password below.') }}</p>
        </div>

        <form method="POST" action="/reset-password" novalidate x-data="{ loading: false }" @submit="loading = true">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') ?? '' }}">

            <div class="space-y-5">
                <x-input
                    type="email"
                    name="email"
                    id="email"
                    :value="old('email', $request->email ?? '')"
                    label="{{ __('Email address') }}"
                    required
                    :error="$errors->first('email')"
                />

                <div x-data="{ show: false }">
                    <x-input
                        :type="show ? 'text' : 'password'"
                        name="password"
                        id="password"
                        label="{{ __('Password') }}"
                        required
                        :error="$errors->first('password')"
                    />
                    <button type="button" @click="show = !show"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-muted hover:text-muted transition-colors"
                            :aria-label="show ? 'Hide password' : 'Show password'">
                        <svg x-show="!show" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        <svg x-show="show" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" x-cloak><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                    </button>
                </div>
                @error('password')
                    <p class="form-error" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <x-input
                type="password"
                name="password_confirmation"
                id="password_confirmation"
                label="{{ __('Confirm Password') }}"
                required
                autocomplete="new-password"
            />
            </div>

            <x-button type="submit" variant="primary" size="lg" class="w-full mt-7" @loading="loading" @disabled="loading">
                <span :class="{ 'hidden': loading }">{{ __('Reset Password') }}</span>
                <span :class="{ 'hidden': !loading }}" x-cloak>{{ __('Resetting...') }}</span>
            </x-button>
        </form>
    </div>
</x-layouts.auth>