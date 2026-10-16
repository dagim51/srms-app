<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Rent Payments') }}</x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="mb-8 flex justify-between items-end">
            <div>
                <h1 class="text-3xl font-extrabold font-display text-muted dark:text-white tracking-tight">{{ __('Payments & Receipts') }}</h1>
                <p class="text-muted dark:text-muted mt-2">{{ __('Manage rent collection and payment records.') }}</p>
            </div>
            <button class="btn btn-primary">{{ __('Record Payment') }}</button>
        </div>

        @include('frontend.rental.partials.tabs')

        <div class="card overflow-hidden">
            <div class="p-6 border-b border-[#EBDBC9] dark:border-[#273244] flex flex-wrap gap-4">
                <input type="text" placeholder="{{ __('Search by Receipt ID or Tenant...') }}" class="input w-full max-w-md">
                <select class="input max-w-xs">
                    <option>{{ __('All Statuses') }}</option>
                    <option>{{ __('Completed') }}</option>
                    <option>{{ __('Pending Verification') }}</option>
                    <option>{{ __('Failed') }}</option>
                </select>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-warm-50/50 dark:bg-[#1E293B]/50 border-b border-[#EBDBC9] dark:border-[#273244]">
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Receipt ID') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Tenant & Unit') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Amount') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Date') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Method') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EBDBC9]/50 dark:divide-[#273244]">
                        @foreach($payments as $payment)
                            <tr class="hover:bg-surface dark:hover:bg-surface/30 transition-colors">
                                <td class="p-4 font-bold text-muted dark:text-white">{{ $payment['id'] }}</td>
                                <td class="p-4">
                                    <p class="font-medium text-muted dark:text-white">{{ $payment['tenant'] }}</p>
                                    <p class="text-xs text-muted">{{ $payment['unit'] }}</p>
                                </td>
                                <td class="p-4 font-bold text-muted dark:text-white">{{ $payment['amount'] }}</td>
                                <td class="p-4 text-muted dark:text-dark-subtle">{{ date('M d, Y', strtotime($payment['date'])) }}</td>
                                <td class="p-4 text-muted dark:text-dark-subtle">{{ $payment['method'] }}</td>
                                <td class="p-4">
                                    <span class="badge {{ $payment['status'] === 'completed' ? 'badge-success' : 'badge-warning' }} uppercase text-xs">{{ __(str_replace('_', ' ', $payment['status'])) }}</span>
                                </td>
                                <td class="p-4 text-right">
                                    <button class="text-primary-600 hover:text-primary-700 font-medium text-sm">{{ __('Download Receipt') }}</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
