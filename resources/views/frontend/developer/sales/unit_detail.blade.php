<x-layouts.dashboard>
    <x-slot name="page-title">Unit {{ $unit['number'] }}</x-slot>

    <div class="max-w-7xl mx-auto page-enter">
        <div class="mb-4">
            <a href="{{ route('dashboard.sales.units') }}" class="text-sm font-medium text-muted hover:text-muted dark:hover:text-white flex items-center gap-2 transition-colors w-max">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Back to Inventory
            </a>
        </div>

        <x-page-header
            :title="'Unit ' . $unit['number']"
            :description="$unit['project'] . ' — ' . $unit['building'] . ' • Status: ' . strtoupper($unit['status'])"
            icon="building"
            :badge="strtoupper($unit['status'])">
            <x-slot:actions>
                <div class="flex gap-2">
                    @if($unit['status'] === 'available')
                        <a href="{{ route('dashboard.sales.reservation', $unit['id']) }}" class="btn btn-primary">{{ __('Make Reservation') }}</a>
                        <button class="btn btn-secondary">{{ __('Block Unit') }}</button>
                    @elseif($unit['status'] === 'reserved')
                        <button class="btn btn-primary">{{ __('Generate Contract') }}</button>
                        <button class="btn btn-secondary text-error-600 border-error-200 hover:bg-error-50 dark:hover:bg-error-900/20">{{ __('Cancel Reservation') }}</button>
                    @endif
                </div>
            </x-slot:actions>
        </x-page-header>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-8">
                <div class="card p-6 md:p-8">
                    <h2 class="card-title text-xl mb-6">{{ __('Unit Specifications') }}</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                        <div>
                            <p class="card-kv-label">{{ __('Type') }}</p>
                            <p class="card-kv-value font-medium mt-1">{{ $unit['type'] }}</p>
                        </div>
                        <div>
                            <p class="card-kv-label">{{ __('Floor') }}</p>
                            <p class="card-kv-value font-medium mt-1">{{ $unit['floor'] }}</p>
                        </div>
                        <div>
                            <p class="card-kv-label">{{ __('Area') }}</p>
                            <p class="card-kv-value font-medium mt-1">{{ __('125 sqm') }}</p>
                        </div>
                        <div>
                            <p class="card-kv-label">{{ __('Price') }}</p>
                            <p class="card-kv-value mt-1">{{ $unit['price'] }}</p>
                        </div>
                    </div>
                </div>

                @if($paymentPlan)
                <div class="card p-6 md:p-8">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="card-title text-xl">{{ __('Payment Plan') }}</h2>
                        <span class="badge badge-primary">{{ $paymentPlan['name'] }}</span>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-[#EBDBC9] dark:border-[#273244]">
                                    <th class="py-3 pr-4 text-xs font-bold text-muted dark:text-muted uppercase">{{ __('Installment') }}</th>
                                    <th class="py-3 px-4 text-xs font-bold text-muted dark:text-muted uppercase">{{ __('Amount') }}</th>
                                    <th class="py-3 px-4 text-xs font-bold text-muted dark:text-muted uppercase">{{ __('Due Date') }}</th>
                                    <th class="py-3 pl-4 text-xs font-bold text-muted dark:text-muted uppercase text-right">{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#EBDBC9]/50 dark:divide-[#273244]">
                                @foreach($installments as $installment)
                                    <tr>
                                        <td class="py-4 pr-4 font-medium text-muted dark:text-white">{{ $installment['description'] }}</td>
                                        <td class="py-4 px-4 font-bold text-muted dark:text-white">{{ $installment['amount'] }}</td>
                                        <td class="py-4 px-4 text-muted dark:text-dark-subtle">{{ date('M d, Y', strtotime($installment['due_date'])) }}</td>
                                        <td class="py-4 pl-4 text-right">
                                            @if($installment['status'] === 'paid')
                                                <span class="badge badge-success uppercase text-xs">Paid ({{ date('M d', strtotime($installment['paid_date'])) }})</span>
                                            @else
                                                <button class="btn btn-sm btn-primary">{{ __('Mark Paid') }}</button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            </div>

            <div>
                @if(isset($unit['buyer']))
                    <div class="card p-6 mb-6">
                        <h3 class="card-title text-base mb-4">{{ __('Buyer Information') }}</h3>
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-12 h-12 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 flex items-center justify-center font-bold text-lg">
                                {{ substr($unit['buyer'], 0, 1) }}
                            </div>
                            <div>
                                <h4 class="font-bold text-muted dark:text-white">{{ $unit['buyer'] }}</h4>
                                <p class="text-sm text-muted">+251 911 234567</p>
                            </div>
                        </div>
                        <a href="{{ route('dashboard.sales.buyers.show', 1) }}" class="btn btn-secondary w-full justify-center">{{ __('View Profile') }}</a>
                    </div>
                @endif
            </div>
        </div>

    </div>
</x-layouts.dashboard>
