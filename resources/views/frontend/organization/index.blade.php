<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Organization Dashboard') }}</x-slot>

    @section('breadcrumb')
        <span class="text-muted dark:text-dark-subtle">{{ __('Organization') }}</span>
    @endsection

    <div class="max-w-7xl mx-auto space-y-6" x-data="{ showOrgModal: false }">
        
        {{-- Standardized Page Header Card --}}
        <x-page-header
            title="Organization & Tenancy Management"
            description="Manage multi-tenant real estate brokerages, developer entities, branches, and authorized agents."
            icon="building"
            badge="Organization">
            <x-slot:actions>
                <a href="{{ route('dashboard.organization.create') }}" class="btn btn-primary shadow-sm shadow-orange-500/20 flex items-center gap-2 text-xs py-2 px-3.5" id="btn-register-org">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    <span>{{ __('Register Organization') }}</span>
                </a>
                <button type="button" @click="showOrgModal = true" class="btn btn-secondary flex items-center gap-1.5 text-xs py-2 px-3.5" id="btn-quick-org-modal">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                    <span>{{ __('Quick Add') }}</span>
                </button>
            </x-slot:actions>
        </x-page-header>

        {{-- Alerts --}}
        @if (session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-sm font-semibold flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-sm font-semibold flex items-center gap-2">
                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @include('frontend.organization.partials.tabs')

        {{-- Metric Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-card-stat
                :label="__('Total Properties')"
                :value="number_format($stats['total_properties'])"
                iconColor="orange"
                icon="building"
                :badge="__('Portfolio')"
                badgeVariant="brand"
                caption="{{ __('Properties under management') }}"
            />
            <x-card-stat
                :label="__('Active Agents')"
                :value="number_format($stats['active_agents'])"
                iconColor="emerald"
                icon="users"
                :badge="__('Staff')"
                badgeVariant="success"
                caption="{{ __('Licensed broker agents') }}"
            />
            <x-card-stat
                :label="__('Branches')"
                :value="number_format($stats['branches'])"
                iconColor="blue"
                icon="map-pin"
                :badge="__('Offices')"
                badgeVariant="info"
                caption="{{ __('Operational regional hubs') }}"
            />
            <x-card-stat
                :label="__('Registered Leads')"
                :value="number_format($stats['total_leads'])"
                iconColor="amber"
                icon="arrow-trending-up"
                :badge="__('Leads')"
                badgeVariant="warning"
                caption="{{ __('Prospective buyer inquiries') }}"
            />
        </div>

        {{-- Registered Entities Table --}}
        <div class="card overflow-hidden">
            <div class="card-header flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="card-title text-lg">{{ __('Registered Entities & Developer Tenants') }}</h2>
                    <p class="card-subtitle text-xs mt-0.5">{{ __('All corporate real estate organizations registered in the platform.') }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="badge badge-brand text-xs px-2.5 py-1">
                        {{ count($allOrganizations) }} {{ __('Organizations Active') }}
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-[#FFEFE0] dark:bg-[#172033] border-b border-[#EBDBC9] dark:border-[#273244] text-xs font-bold uppercase tracking-wider text-muted dark:text-muted">
                            <th class="py-3.5 px-6">{{ __('Organization / Legal Entity') }}</th>
                            <th class="py-3.5 px-6">{{ __('Tenant Slug') }}</th>
                            <th class="py-3.5 px-6">{{ __('Contact Details') }}</th>
                            <th class="py-3.5 px-6">{{ __('Headquarters') }}</th>
                            <th class="py-3.5 px-6">{{ __('Plan Tier') }}</th>
                            <th class="py-3.5 px-6">{{ __('Status') }}</th>
                            <th class="py-3.5 px-6 text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EBDBC9] dark:divide-[#273244]">
                        @forelse ($allOrganizations as $org)
                            <tr class="hover:bg-orange-50/40 dark:hover:bg-surface/40 transition-colors">
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-orange-100 dark:bg-orange-950/60 text-orange-600 dark:text-orange-400 flex items-center justify-center font-bold text-sm shrink-0 border border-orange-200 dark:border-orange-800/40">
                                            {{ strtoupper(substr($org['name'], 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-muted dark:text-white">{{ $org['name'] }}</div>
                                            <div class="text-xs text-muted dark:text-muted">{{ $org['email'] ?: 'No email configured' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="font-mono text-xs px-2 py-0.5 rounded bg-surface-subtle dark:bg-surface text-muted dark:text-dark-subtle">
                                        {{ $org['slug'] ?? 'org-default' }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-xs text-muted dark:text-dark-subtle">
                                    {{ $org['phone'] ?: '—' }}
                                </td>
                                <td class="py-4 px-6 text-xs text-muted dark:text-dark-subtle max-w-xs truncate">
                                    {{ $org['address'] ?: 'Addis Ababa' }}
                                </td>
                                <td class="py-4 px-6">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                        {{ $org['plan'] ?? 'Enterprise' }}
                                    </span>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="badge badge-success text-xs font-semibold">
                                        {{ ucfirst($org['status'] ?? 'active') }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('dashboard.organization.profile') }}" class="btn btn-secondary btn-sm text-xs font-semibold">
                                            {{ __('Manage') }}
                                        </a>
                                        <a href="{{ route('dashboard.organization.branches') }}" class="btn btn-secondary btn-sm text-xs font-semibold">
                                            {{ __('Branches') }}
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-muted dark:text-muted">
                                    <div class="max-w-sm mx-auto space-y-3">
                                        <div class="w-12 h-12 mx-auto rounded-full bg-orange-100 dark:bg-orange-950 text-orange-600 flex items-center justify-center">
                                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                        </div>
                                        <p class="font-bold text-muted dark:text-white">{{ __('No registered organizations yet') }}</p>
                                        <p class="text-xs text-muted dark:text-muted">{{ __('Get started by registering your first real estate entity or agency.') }}</p>
                                        <a href="{{ route('dashboard.organization.create') }}" class="btn btn-primary btn-sm inline-flex items-center gap-1.5">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                            {{ __('Register Organization') }}
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Quick Creation Modal --}}
        <div x-show="showOrgModal" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4"
             x-cloak>
            
            <div @click.outside="showOrgModal = false"
                 class="w-full max-w-lg bg-white dark:bg-[#111827] rounded-3xl border border-[#EBDBC9] dark:border-[#273244] shadow-2xl p-6 sm:p-8 space-y-6">
                
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-bold font-display text-muted dark:text-white">{{ __('Register Organization') }}</h3>
                        <p class="text-xs text-muted dark:text-muted mt-0.5">{{ __('Provision a new real estate company or agency tenant.') }}</p>
                    </div>
                    <button type="button" @click="showOrgModal = false" class="text-muted hover:text-muted dark:hover:text-muted">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form action="{{ route('dashboard.organization.store') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label class="form-label form-label-required font-bold text-xs">{{ __('Organization Legal Name') }}</label>
                        <input type="text" name="name" required placeholder="e.g. Zemen Real Estate S.C." class="form-input text-sm">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="form-label font-bold text-xs">{{ __('Category') }}</label>
                            <select name="type" class="form-input text-xs cursor-pointer">
                                <option value="Real Estate Developer">{{ __('Developer') }}</option>
                                <option value="Property Management Agency">{{ __('Property Management') }}</option>
                                <option value="Brokerage Firm">{{ __('Brokerage Firm') }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label font-bold text-xs">{{ __('Subscription Tier') }}</label>
                            <select name="plan" class="form-input text-xs cursor-pointer">
                                <option value="Enterprise" selected>Enterprise</option>
                                <option value="Professional">Professional</option>
                                <option value="Starter">Starter</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="form-label form-label-required font-bold text-xs">{{ __('Official Email') }}</label>
                        <input type="email" name="email" required placeholder="info@zemenrealty.et" class="form-input text-sm">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="form-label font-bold text-xs">{{ __('Phone Number') }}</label>
                            <input type="text" name="phone" value="+251 " class="form-input text-sm">
                        </div>
                        <div>
                            <label class="form-label font-bold text-xs">{{ __('Primary Branch') }}</label>
                            <input type="text" name="branch_name" value="Addis Ababa HQ" class="form-input text-sm">
                        </div>
                    </div>

                    <div>
                        <label class="form-label font-bold text-xs">{{ __('Address') }}</label>
                        <input type="text" name="address" placeholder="Bole Subcity, Addis Ababa" class="form-input text-sm">
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <a href="{{ route('dashboard.organization.create') }}" class="text-xs text-orange-600 hover:text-orange-700 font-bold">
                            {{ __('Open Full Registration Page →') }}
                        </a>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="showOrgModal = false" class="btn btn-secondary text-xs">
                                {{ __('Cancel') }}
                            </button>
                            <button type="submit" class="btn btn-primary text-xs font-bold">
                                {{ __('Create Entity') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
