<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Commissions') }}</x-slot>

    <div class="max-w-7xl mx-auto page-enter space-y-6">
        <x-page-header
            title="Commissions Summary"
            description="Track real estate agent sales commissions, payout statuses, milestone releases, and split accounting."
            icon="cash"
            badge="Commissions" />

        @include('frontend.crm.partials.tabs')

        <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-warm rounded-3xl overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface dark:bg-surface/50 border-b border-warm dark:border-warm">
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Property') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Client') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Amount') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Date') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-warm dark:divide-dark-border">
                        @foreach($commissions as $commission)
                            <tr class="hover:bg-surface dark:hover:bg-surface/30 transition-colors">
                                <td class="p-4 font-bold text-muted dark:text-white">{{ $commission['property'] }}</td>
                                <td class="p-4 text-muted dark:text-dark-subtle">{{ $commission['client'] }}</td>
                                <td class="p-4 font-black text-muted dark:text-white">{{ $commission['amount'] }}</td>
                                <td class="p-4 text-muted text-sm">{{ $commission['date'] }}</td>
                                <td class="p-4">
                                    <span class="badge {{ $commission['status'] === 'paid' ? 'badge-success' : 'badge-warning' }} uppercase">
                                        {{ __($commission['status']) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
