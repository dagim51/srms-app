<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Members & Agents') }}</x-slot>

    <div class="max-w-7xl mx-auto page-enter">
        <x-page-header
            title="Members & Agents"
            description="Manage staff, authorized real estate agents, and credentials associated with your organization."
            icon="users"
            badge="Staff" />

        @include('frontend.organization.partials.tabs')

        <div class="card overflow-hidden">
            <div class="card-header flex items-center justify-between">
                <h2 class="card-title text-xl">{{ __('All Members') }}</h2>
                <button class="btn btn-primary btn-sm">{{ __('Invite Member') }}</button>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-warm-50/50 dark:bg-[#1E293B]/50 border-b border-[#EBDBC9] dark:border-[#273244]">
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Name') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Role') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Branch') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EBDBC9]/50 dark:divide-[#273244]">
                        @foreach($members as $member)
                            <tr class="hover:bg-surface dark:hover:bg-surface/30 transition-colors">
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <div class="avatar avatar-sm avatar-brand">{{ substr($member['name'], 0, 1) }}</div>
                                        <div>
                                            <p class="font-bold text-muted dark:text-white">{{ $member['name'] }}</p>
                                            @if($member['role'] === 'Agent')
                                                <a href="{{ route('agent.show', $member['id']) }}" class="text-xs text-primary-600 hover:underline">{{ __('View Public Profile') }}</a>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4 text-muted dark:text-dark-subtle">{{ $member['role'] }}</td>
                                <td class="p-4 text-muted dark:text-dark-subtle">{{ $member['branch'] }}</td>
                                <td class="p-4">
                                    <span class="badge {{ $member['status'] === 'active' ? 'badge-success' : 'badge-neutral' }} uppercase">
                                        {{ $member['status'] }}
                                    </span>
                                </td>
                                <td class="p-4 text-right">
                                    <button class="text-muted hover:text-muted dark:hover:text-white font-medium text-sm">{{ __('Options') }}</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.dashboard>
