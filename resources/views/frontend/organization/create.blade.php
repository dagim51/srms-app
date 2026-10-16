<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Register New Organization') }}</x-slot>

    @section('breadcrumb')
        <a href="/dashboard/organization" class="hover:text-muted dark:hover:text-muted">{{ __('Organization') }}</a>
        <span class="mx-1.5 text-muted dark:text-muted">/</span>
        <span class="text-muted dark:text-dark-subtle">{{ __('Create Organization') }}</span>
    @endsection

    <div class="max-w-4xl mx-auto space-y-6">

        {{-- Header --}}
        <div>
            <a href="{{ route('dashboard.organization.index') }}" class="inline-flex items-center gap-1.5 text-xs text-orange-600 hover:text-orange-700 font-bold mb-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                {{ __('Back to Organization Directory') }}
            </a>
            <h1 class="text-3xl font-extrabold font-display text-muted dark:text-white tracking-tight">
                {{ __('Register New Real Estate Entity') }}
            </h1>
            <p class="text-muted dark:text-muted mt-1 text-sm">
                {{ __('Provision a multi-tenant organization account for property developers, agencies, or management brokerages.') }}
            </p>
        </div>

        @if (session('error'))
            <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-sm font-semibold">
                {{ session('error') }}
            </div>
        @endif

        {{-- Form Card --}}
        <div class="card bg-white dark:bg-[#111827] border border-[#EBDBC9] dark:border-[#273244] rounded-3xl p-6 sm:p-8 shadow-xs">
            <form action="{{ route('dashboard.organization.store') }}" method="POST" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div class="sm:col-span-2">
                        <label class="form-label form-label-required font-bold">{{ __('Organization Legal Name') }}</label>
                        <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Flintstone Real Estate Ltd." class="form-input">
                        @error('name') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="form-label font-bold">{{ __('Entity Category') }}</label>
                        <select name="type" class="form-input cursor-pointer font-medium">
                            <option value="Real Estate Developer" selected>{{ __('Real Estate Developer') }}</option>
                            <option value="Property Management Agency">{{ __('Property Management Agency') }}</option>
                            <option value="Brokerage Firm">{{ __('Brokerage Firm') }}</option>
                            <option value="Corporate Landlord">{{ __('Corporate Landlord') }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label font-bold">{{ __('TIN (Tax Identification Number)') }}</label>
                        <input type="text" name="tin" value="{{ old('tin') }}" placeholder="0049281729" class="form-input font-mono">
                        @error('tin') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="form-label form-label-required font-bold">{{ __('Corporate Contact Email') }}</label>
                        <input type="email" name="email" value="{{ old('email') }}" required placeholder="info@company.et" class="form-input">
                        @error('email') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="form-label font-bold">{{ __('Switchboard Telephone') }}</label>
                        <input type="text" name="phone" value="{{ old('phone', '+251 11 ') }}" placeholder="+251 11 678 9000" class="form-input">
                        @error('phone') <span class="form-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="form-label font-bold">{{ __('Headquarters Physical Address') }}</label>
                        <input type="text" name="address" value="{{ old('address') }}" placeholder="Bole Subcity, Woreda 03, House No. 812, Addis Ababa" class="form-input">
                        <p class="form-helper">{{ __('Specify municipal subcity, woreda, and physical building details.') }}</p>
                    </div>

                    <div>
                        <label class="form-label font-bold">{{ __('Initial Primary Branch Name') }}</label>
                        <input type="text" name="branch_name" value="Addis Ababa HQ" class="form-input">
                        <p class="form-helper">{{ __('A default branch will be created for teams and listing inventory.') }}</p>
                    </div>

                    <div>
                        <label class="form-label font-bold">{{ __('Subscription Tier') }}</label>
                        <select name="plan" class="form-input cursor-pointer font-medium">
                            <option value="Enterprise" selected>Enterprise (Unlimited Agents & Projects)</option>
                            <option value="Professional">Professional (Up to 25 Agents)</option>
                            <option value="Starter">Starter (Up to 5 Agents)</option>
                        </select>
                    </div>
                </div>

                {{-- Tenant Switcher Integration Note --}}
                <div class="p-4 rounded-2xl bg-[#FFEFE0] dark:bg-[#172033] border border-[#EBDBC9] dark:border-[#273244] flex items-center gap-3">
                    <svg class="w-5 h-5 text-orange-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" /></svg>
                    <p class="text-xs text-muted dark:text-dark-subtle">
                        {{ __('Upon creation, this organization will be available in the tenant switcher in the sidebar, allowing you to isolate properties, branches, and financials.') }}
                    </p>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#EBDBC9] dark:border-[#273244]">
                    <a href="{{ route('dashboard.organization.index') }}" class="btn btn-secondary px-5 py-2.5 text-xs">
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit" class="btn btn-primary px-6 py-2.5 font-bold shadow-xs">
                        {{ __('Register & Activate Organization') }}
                    </button>
                </div>
            </form>
        </div>

    </div>
</x-layouts.dashboard>
