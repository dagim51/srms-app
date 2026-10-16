<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Lease') }}: {{ $lease['id'] }}</x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="mb-8">
            <a href="{{ route('dashboard.rental.leases') }}" class="text-sm font-medium text-muted hover:text-muted dark:hover:text-white flex items-center gap-2 mb-4 transition-colors w-max">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                {{ __('Back to Leases') }}
            </a>
            
            <div class="flex flex-col sm:flex-row justify-between sm:items-end gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold font-display text-muted dark:text-white tracking-tight">{{ __('Lease') }} {{ $lease['id'] }}</h1>
                    <div class="flex items-center gap-3 mt-2">
                        <span class="badge badge-success uppercase">{{ __($lease['status']) }}</span>
                        <span class="text-muted">&bull;</span>
                        <span class="text-muted dark:text-dark-subtle font-medium">{{ $lease['unit'] }}</span>
                        <span class="text-muted">&bull;</span>
                        <span class="text-muted dark:text-dark-subtle font-medium">{{ $lease['tenant'] }}</span>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button class="btn btn-secondary">{{ __('Renew Lease') }}</button>
                    <button class="btn btn-secondary text-error-600 border-error-200 hover:bg-error-50 dark:hover:bg-error-900/20">{{ __('Terminate') }}</button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-8">
                <!-- Lease Terms -->
                <div class="card p-6 md:p-8">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="card-title text-xl">{{ __('Lease Terms') }}</h2>
                        <button class="text-primary-600 hover:text-primary-700 font-medium text-sm flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                            {{ __('Download PDF') }}
                        </button>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                        <div>
                            <p class="card-kv-label">{{ __('Start Date') }}</p>
                            <p class="card-kv-value font-medium mt-1"><x-ethiopian-date :date="$lease['start_date']" :dual="true" /></p>
                        </div>
                        <div>
                            <p class="card-kv-label">{{ __('End Date') }}</p>
                            <p class="card-kv-value font-medium mt-1"><x-ethiopian-date :date="$lease['end_date']" :dual="true" /></p>
                        </div>
                        <div>
                            <p class="card-kv-label">{{ __('Rent') }}</p>
                            <p class="card-kv-value mt-1">{{ $lease['rent'] }}/mo</p>
                        </div>
                        <div>
                            <p class="card-kv-label">{{ __('Deposit') }}</p>
                            <p class="card-kv-value mt-1">{{ $lease['deposit'] }}</p>
                        </div>
                    </div>
                </div>

                <!-- Rent Schedule -->
                <div class="card p-6 md:p-8">
                    <h2 class="card-title text-xl mb-6">{{ __('Rent Schedule') }}</h2>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-[#EBDBC9] dark:border-[#273244]">
                                    <th class="py-3 pr-4 text-xs font-bold text-muted dark:text-muted uppercase">{{ __('Period') }}</th>
                                    <th class="py-3 px-4 text-xs font-bold text-muted dark:text-muted uppercase">{{ __('Due Date') }}</th>
                                    <th class="py-3 px-4 text-xs font-bold text-muted dark:text-muted uppercase">{{ __('Amount') }}</th>
                                    <th class="py-3 pl-4 text-xs font-bold text-muted dark:text-muted uppercase text-right">{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#EBDBC9]/50 dark:divide-[#273244]">
                                @foreach($schedule as $item)
                                    <tr>
                                        <td class="py-4 pr-4 font-bold text-muted dark:text-white">{{ $item['month'] }}</td>
                                        <td class="py-4 px-4 text-muted dark:text-dark-subtle">{{ date('M d, Y', strtotime($item['due_date'])) }}</td>
                                        <td class="py-4 px-4 font-bold text-muted dark:text-white">{{ $item['amount'] }}</td>
                                        <td class="py-4 pl-4 text-right">
                                            @if($item['status'] === 'paid')
                                                <span class="badge badge-success uppercase text-xs">Paid ({{ date('M d', strtotime($item['paid_date'])) }})</span>
                                            @else
                                                <button class="btn btn-sm btn-primary">{{ __('Record Payment') }}</button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Context Info -->
            <div>
                <div class="card p-6 mb-6">
                    <h3 class="card-title text-base mb-4">{{ __('Tenant') }}</h3>
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 flex items-center justify-center font-bold text-lg">
                            {{ substr($lease['tenant'], 0, 1) }}
                        </div>
                        <div>
                            <h4 class="font-bold text-muted dark:text-white">{{ $lease['tenant'] }}</h4>
                        </div>
                    </div>
                    <a href="{{ route('dashboard.rental.tenants.show', 1) }}" class="btn btn-secondary w-full justify-center">{{ __('View Profile') }}</a>
                </div>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
