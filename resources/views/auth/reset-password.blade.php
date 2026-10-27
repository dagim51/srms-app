<x-layouts.auth>
    <x-slot name="title">{{ __('Create New Password') }}</x-slot>

    <div class="card p-8 page-enter">
        <div class="text-center mb-8">
            <h1 class="font-display font-bold text-muted dark:text-white text-2xl mb-2">{{ __('Create new password') }}</h1>
            <p class="text-muted dark:text-muted text-sm">{{ __('Please enter your new password below.') }}</p>
        </div>

        <form method="POST" action="/reset-password" novalidate x-data="{ loading: false, show: false }" @submit="loading = true">
            @csrf
            <input type="hidden" name="token" value="{{ $token ?? $request->route('token') ?? '' }}">

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

                <div>
                    <x-input
                        type="password"
                        name="password"
                        id="password"
                        label="{{ __('Password') }}"
                        required
                        :error="$errors->first('password')"
                    />
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
                <span :class="{ 'hidden': !loading }" x-cloak>{{ __('Resetting...') }}</span>
            </x-button>
        </form>
    </div>
</x-layouts.auth>