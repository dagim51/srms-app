<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Global Settings') }}</x-slot>

    <div class="max-w-7xl mx-auto page-enter space-y-6">
        <x-page-header
            title="Global System Settings"
            description="Configure platform-wide financial thresholds, regional currencies, notification channels, and operational integrations."
            icon="cog"
            badge="Administration">
            <x-slot:actions>
                <a href="{{ route('dashboard.settings', ['tab' => 'platform']) }}" class="btn btn-primary font-bold shadow-xs">{{ __('Open Full Platform Settings') }}</a>
            </x-slot:actions>
        </x-page-header>

        @include('frontend.admin.partials.tabs')

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="md:col-span-1">
                <h3 class="text-lg font-bold text-muted dark:text-white">{{ __('Financial Configuration') }}</h3>
                <p class="text-sm text-muted mt-1">{{ __('Set platform fees, tax rates, and payout schedules.') }}</p>
            </div>
            <div class="md:col-span-2">
                <div class="bg-white dark:bg-[#111827] border border-[#EBDBC9] dark:border-[#273244] rounded-3xl p-6 shadow-xs space-y-6">
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-muted dark:text-dark-subtle mb-2">Platform Fee (%)</label>
                            <input type="number" value="5" class="input w-full">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-muted dark:text-dark-subtle mb-2">VAT Rate (%)</label>
                            <input type="number" value="15" class="input w-full">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-muted dark:text-dark-subtle mb-2">{{ __('Payout Schedule') }}</label>
                        <select class="input w-full">
                            <option>{{ __('Daily') }}</option>
                            <option>{{ __('Weekly') }}</option>
                            <option selected>Monthly (1st of Month)</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="divider my-8"></div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="md:col-span-1">
                <h3 class="text-lg font-bold text-muted dark:text-white">{{ __('Security & Access') }}</h3>
                <p class="text-sm text-muted mt-1">{{ __('Configure MFA requirements and session limits.') }}</p>
            </div>
            <div class="md:col-span-2">
                <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-3xl p-6 shadow-sm space-y-6">
                    <label class="flex items-start gap-3">
                        <input type="checkbox" checked class="mt-1 w-4 h-4 text-primary-600 rounded border-warm">
                        <div>
                            <span class="block text-sm font-bold text-muted dark:text-white">{{ __('Require MFA for Admins') }}</span>
                            <span class="block text-xs text-muted mt-0.5">{{ __('Enforce multi-factor authentication for all users with Admin roles.') }}</span>
                        </div>
                    </label>
                    <label class="flex items-start gap-3">
                        <input type="checkbox" class="mt-1 w-4 h-4 text-primary-600 rounded border-warm">
                        <div>
                            <span class="block text-sm font-bold text-muted dark:text-white">{{ __('Strict IP Allowlist') }}</span>
                            <span class="block text-xs text-muted mt-0.5">{{ __('Only allow admin access from predefined corporate IPs.') }}</span>
                        </div>
                    </label>
                </div>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
