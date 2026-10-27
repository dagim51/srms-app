<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Rental Dashboard') }}</x-slot>

    <div class="max-w-7xl mx-auto space-y-6" x-data="{ showNewLease: false }">
        <x-page-header
            title="Rental & Lease Management"
            description="Oversee active tenant agreements, lease terms, payment schedules, and rental applications."
            icon="document"
            badge="Leases">
            <x-slot:actions>
                <button @click="showNewLease = true" class="btn btn-primary text-xs shadow-sm shadow-orange-500/20">
                    <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    {{ __('New Lease') }}
                </button>
            </x-slot:actions>
        </x-page-header>

        @if(session('success'))
            <div class="px-4 py-3 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 text-sm border border-emerald-200 dark:border-emerald-800 flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ session('success') }}
            </div>
        @endif

        @include('frontend.rental.partials.tabs')

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
            <div class="cinematic-fade-up cinematic-stagger-1">
                <x-card-stat
                    :label="__('Total Tenants')"
                    :value="$stats['total_tenants']"
                    :badge="__('Tenants')"
                    badgeVariant="info"
                    iconColor="blue">
                    <x-slot:icon>
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    </x-slot:icon>
                </x-card-stat>
            </div>
            
            <div class="cinematic-fade-up cinematic-stagger-2">
                <x-card-stat
                    :label="__('Active Leases')"
                    :value="$stats['active_leases']"
                    :badge="__('Leases')"
                    badgeVariant="success"
                    iconColor="emerald">
                    <x-slot:icon>
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    </x-slot:icon>
                </x-card-stat>
            </div>
            
            <div class="cinematic-fade-up cinematic-stagger-3">
                <x-card-stat
                    :label="__('Pending Apps')"
                    :value="$stats['pending_applications']"
                    :badge="__('Pending')"
                    badgeVariant="warning"
                    iconColor="amber">
                    <x-slot:icon>
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </x-slot:icon>
                </x-card-stat>
            </div>
            
            <div class="cinematic-fade-up cinematic-stagger-4">
                <x-card-stat
                    :label="__('Overdue Rent')"
                    :value="$stats['overdue_rent']"
                    :badge="__('Alert')"
                    badgeVariant="error"
                    iconColor="rose">
                    <x-slot:icon>
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </x-slot:icon>
                </x-card-stat>
            </div>
        </div>

        <!-- Recent Applications -->
        <div class="cinematic-fade-up cinematic-stagger-2 card">
            <div class="card-header">
                <h2 class="card-title">{{ __('Recent Applications') }}</h2>
                <a href="{{ route('dashboard.rental.applications') }}" class="btn btn-ghost btn-sm text-orange-600 dark:text-orange-400 font-semibold">{{ __('View All') }}</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface dark:bg-surface/50 border-b border-warm dark:border-warm">
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Applicant') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Unit') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Date') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider text-right">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-warm dark:divide-dark-border">
                        @forelse($recentApplications as $app)
                            <tr class="hover:bg-surface dark:hover:bg-surface/30 transition-colors">
                                <td class="p-4 font-bold text-muted dark:text-white">{{ $app['applicant'] }}</td>
                                <td class="p-4 text-muted dark:text-dark-subtle">{{ $app['unit'] }}</td>
                                <td class="p-4 text-muted dark:text-dark-subtle">{{ date('M d, Y', strtotime($app['date'])) }}</td>
                                <td class="p-4">
                                    <span class="badge {{ $app['status'] === 'approved' ? 'badge-success' : ($app['status'] === 'rejected' ? 'badge-error' : 'badge-warning') }} uppercase text-xs">{{ __($app['status']) }}</span>
                                </td>
                                <td class="p-4 text-right">
                                    <a href="{{ route('dashboard.rental.applications') }}" class="btn btn-sm btn-secondary">{{ __('Review') }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-8 text-center text-muted">{{ __('No recent applications.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Create Lease Modal --}}
        <div x-show="showNewLease" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="absolute inset-0 bg-black/50" @click="showNewLease = false"></div>
            <div class="relative bg-white dark:bg-dark-card rounded-2xl shadow-2xl w-full max-w-lg p-6 z-10">
                <div class="flex justify-between items-center mb-5">
                    <h3 class="text-lg font-bold text-muted dark:text-white">{{ __('Execute New Lease Agreement') }}</h3>
                    <button @click="showNewLease = false" class="text-muted hover:text-muted/70">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form method="POST" action="{{ route('dashboard.rental.leases.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-muted dark:text-dark-subtle mb-1">{{ __('Property Unit') }} <span class="text-red-500">*</span></label>
                        <select name="property_id" class="input w-full" required>
                            <option value="">{{ __('— Select Rental Property —') }}</option>
                            @foreach($properties as $prop)
                                <option value="{{ $prop->id }}">{{ $prop->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-muted dark:text-dark-subtle mb-1">{{ __('Tenant') }} <span class="text-red-500">*</span></label>
                        <select name="user_id" class="input w-full" required>
                            <option value="">{{ __('— Select Tenant —') }}</option>
                            @foreach($tenants as $t)
                                <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->phone ?? 'No Phone' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-muted dark:text-dark-subtle mb-1">{{ __('Start Date') }} <span class="text-red-500">*</span></label>
                            <input type="date" name="start_date" value="{{ date('Y-m-d') }}" class="input w-full" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-muted dark:text-dark-subtle mb-1">{{ __('End Date') }} <span class="text-red-500">*</span></label>
                            <input type="date" name="end_date" value="{{ date('Y-m-d', strtotime('+1 year')) }}" class="input w-full" required>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-muted dark:text-dark-subtle mb-1">{{ __('Monthly Rent (ETB)') }} <span class="text-red-500">*</span></label>
                            <input type="number" name="rent_amount" step="0.01" class="input w-full" placeholder="e.g. 25000" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-muted dark:text-dark-subtle mb-1">{{ __('Security Deposit (ETB)') }}</label>
                            <input type="number" name="deposit_amount" step="0.01" class="input w-full" placeholder="e.g. 50000">
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-3">
                        <button type="button" @click="showNewLease = false" class="btn btn-secondary">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Execute Lease') }}</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
