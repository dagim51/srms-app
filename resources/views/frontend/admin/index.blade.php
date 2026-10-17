<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Admin Dashboard') }}</x-slot>

    <div class="max-w-7xl mx-auto">
        <x-page-header
            title="System Administration Console"
            description="Manage system users, assign role-based permissions, monitor security audit logs, and configure platform settings."
            icon="users"
            badge="Administration">
            <x-slot:actions>
                <button class="btn btn-secondary text-xs">{{ __('System Status') }}</button>
            </x-slot:actions>
        </x-page-header>

        @include('frontend.admin.partials.tabs')

        <!-- Stats Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6 mb-8">
            <div class="cinematic-fade-up cinematic-stagger-1">
                <x-card-stat
                    :label="__('Total Users')"
                    :value="number_format($stats['total_users'])"
                    iconColor="blue"
                    icon="users"
                    :badge="__('Users')"
                    badgeVariant="info"
                />
            </div>
            <div class="cinematic-fade-up cinematic-stagger-2">
                <x-card-stat
                    :label="__('Active Properties')"
                    :value="$stats['active_properties']"
                    iconColor="orange"
                    icon="building"
                    :badge="__('Active')"
                    badgeVariant="brand"
                />
            </div>
            <div class="cinematic-fade-up cinematic-stagger-3">
                <x-card-stat
                    :label="__('Monthly Revenue')"
                    :value="$stats['monthly_revenue']"
                    iconColor="emerald"
                    icon="banknotes"
                    :badge="__('Revenue')"
                    badgeVariant="success"
                />
            </div>
            <div class="cinematic-fade-up cinematic-stagger-4">
                <x-card-stat
                    :label="__('System Health')"
                    :value="$stats['system_health']"
                    iconColor="emerald"
                    icon="check"
                    :badge="__('Operational')"
                    badgeVariant="success"
                    :caption="__('All services operational')"
                />
            </div>
        </div>

        <div class="cinematic-fade-up cinematic-stagger-2 card overflow-hidden">
            <div class="card-header">
                <h2 class="card-title text-xl">{{ __('Recent Signups') }}</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200/80 dark:border-slate-800/80">
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('User ID') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Name') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Role') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Date') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/80 dark:divide-slate-800/80">
                        @foreach($recentSignups as $user)
                            <tr class="hover:bg-surface dark:hover:bg-surface/30 transition-colors">
                                <td class="p-4 font-bold text-muted dark:text-white">{{ $user['id'] }}</td>
                                <td class="p-4 font-medium text-muted dark:text-white">{{ $user['name'] }}</td>
                                <td class="p-4 text-sm text-muted dark:text-dark-subtle">{{ $user['role'] }}</td>
                                <td class="p-4 text-sm text-muted dark:text-dark-subtle">{{ date('M d, Y', strtotime($user['date'])) }}</td>
                                <td class="p-4 text-right">
                                    <a href="{{ route('dashboard.admin.users.show', $user['id']) }}" class="text-primary-600 hover:text-primary-700 font-medium text-sm">{{ __('Manage') }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
