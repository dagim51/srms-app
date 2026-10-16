<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Create New User Account') }}</x-slot>

    @section('breadcrumb')
        <a href="/dashboard/admin/users" class="hover:text-muted dark:hover:text-muted">{{ __('Users') }}</a>
        <span class="mx-1.5 text-muted dark:text-muted">/</span>
        <span class="text-muted dark:text-dark-subtle">{{ __('Create User') }}</span>
    @endsection

    <div class="max-w-4xl mx-auto space-y-6">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('dashboard.admin.users') }}" class="inline-flex items-center gap-1.5 text-xs text-orange-600 hover:text-orange-700 font-bold mb-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                    {{ __('Back to User Directory') }}
                </a>
                <h1 class="text-3xl font-extrabold font-display text-muted dark:text-white tracking-tight">
                    {{ __('Add New User & Set Permissions') }}
                </h1>
                <p class="text-muted dark:text-muted mt-1 text-sm">
                    {{ __('Provision credentials, associate organization branch, and define operational access.') }}
                </p>
            </div>
        </div>

        @if (session('error'))
            <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-sm font-semibold">
                {{ session('error') }}
            </div>
        @endif

        {{-- Form Card --}}
        <div class="card bg-white dark:bg-[#111827] border border-[#EBDBC9] dark:border-[#273244] rounded-3xl p-6 sm:p-8 shadow-xs">
            <form action="{{ route('dashboard.admin.users.store') }}" method="POST" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label class="form-label form-label-required font-bold">{{ __('Full Name') }}</label>
                        <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Abebe Kebede" class="form-input">
                        @error('name') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="form-label form-label-required font-bold">{{ __('Email Address') }}</label>
                        <input type="email" name="email" value="{{ old('email') }}" required placeholder="name@domain.et" class="form-input">
                        @error('email') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="form-label font-bold">{{ __('Phone Number (Ethiopia)') }}</label>
                        <input type="text" name="phone" value="{{ old('phone', '+251 ') }}" placeholder="+251 911 000 000" class="form-input">
                        @error('phone') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="form-label form-label-required font-bold">{{ __('Role Assignment') }}</label>
                        <select name="role" required class="form-input cursor-pointer font-medium">
                            @foreach($roles as $r)
                                <option value="{{ $r->name }}">{{ $r->name }}</option>
                            @endforeach
                        </select>
                        <p class="form-helper">{{ __('Determines base permissions and functional dashboard capabilities.') }}</p>
                    </div>

                    <div>
                        <label class="form-label form-label-required font-bold">{{ __('Initial Password') }}</label>
                        <input type="text" name="password" value="password123" required class="form-input font-mono text-sm">
                        <p class="form-helper">{{ __('User will be prompted to reset upon first login.') }}</p>
                    </div>

                    <div>
                        <label class="form-label font-bold">{{ __('Organization Scope') }}</label>
                        <select name="organization_id" class="form-input cursor-pointer font-medium">
                            <option value="">{{ __('Global / None (Independent)') }}</option>
                            @foreach($organizations as $org)
                                <option value="{{ $org->id }}">{{ $org->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="p-4 rounded-2xl bg-[#FFEFE0] dark:bg-[#172033] border border-[#EBDBC9] dark:border-[#273244] flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <input type="checkbox" id="send_invite_page" name="send_invite" checked class="rounded border-[#EBDBC9] text-orange-500 focus:ring-orange-500">
                        <div>
                            <label for="send_invite_page" class="text-xs font-bold text-muted dark:text-white cursor-pointer">
                                {{ __('Send Welcome & Activation Email') }}
                            </label>
                            <p class="text-xs text-muted dark:text-muted mt-0.5">
                                {{ __('Dispatches secure login credentials with instructions on setting up Two-Factor Authentication.') }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#EBDBC9] dark:border-[#273244]">
                    <a href="{{ route('dashboard.admin.users') }}" class="btn btn-secondary px-5 py-2.5 text-xs">
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit" class="btn btn-primary px-6 py-2.5 font-bold shadow-xs">
                        {{ __('Create Account & Assign Role') }}
                    </button>
                </div>
            </form>
        </div>

    </div>
</x-layouts.dashboard>
