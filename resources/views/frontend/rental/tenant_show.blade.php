<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Tenant Profile') }}: {{ $tenant['name'] }}</x-slot>

    <div class="max-w-7xl mx-auto page-enter space-y-6">
        <x-page-header
            :title="$tenant['name']"
            :description="$tenant['email'] . ' • ' . $tenant['phone'] . ' • ' . __('Status:') . ' ' . $tenant['status']"
            icon="users"
            :badge="$tenant['status']">
            <x-slot:actions>
                <div class="flex items-center gap-2">
                    <a href="{{ route('dashboard.rental.tenants') }}" class="btn btn-secondary text-xs flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                        {{ __('Back to Tenants') }}
                    </a>
                </div>
            </x-slot:actions>
        </x-page-header>

        @if(session('success'))
            <div class="px-4 py-3 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 text-sm border border-emerald-200 dark:border-emerald-800 flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-8">
                <!-- Current Lease -->
                <div class="card p-6 md:p-8">
                    <h2 class="card-title text-xl mb-6">{{ __('Current Lease') }}</h2>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
                        <div>
                            <p class="card-kv-label">{{ __('Unit') }}</p>
                            <p class="card-kv-value mt-1">{{ $tenant['unit'] }}</p>
                        </div>
                        <div>
                            <p class="card-kv-label">{{ __('Lease End Date') }}</p>
                            <p class="card-kv-value font-medium mt-1">{{ $tenant['lease_end'] }}</p>
                        </div>
                        <div>
                            <p class="card-kv-label">{{ __('Outstanding Balance') }}</p>
                            <p class="card-kv-value {{ $tenant['balance'] !== '0 ETB' ? 'text-error-600' : 'text-success-600' }} mt-1">{{ $tenant['balance'] }}</p>
                        </div>
                    </div>
                    @if($lease)
                        <div class="mt-6 pt-6 border-t border-[#EBDBC9] dark:border-[#273244]">
                            <a href="{{ route('dashboard.rental.leases.show', $lease->id) }}" class="btn btn-primary">{{ __('View Full Lease Details') }}</a>
                        </div>
                    @endif
                </div>

                <!-- Recent Payments -->
                <div class="card p-6 md:p-8">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="card-title text-xl">{{ __('Verified Rent Payments') }}</h2>
                        <a href="{{ route('dashboard.rental.payments') }}" class="text-primary-600 hover:text-primary-700 font-medium text-sm">{{ __('View All') }}</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-[#EBDBC9] dark:border-[#273244]">
                                    <th class="py-3 pr-4 text-xs font-bold text-muted dark:text-muted uppercase">{{ __('Receipt ID') }}</th>
                                    <th class="py-3 px-4 text-xs font-bold text-muted dark:text-muted uppercase">{{ __('Date') }}</th>
                                    <th class="py-3 px-4 text-xs font-bold text-muted dark:text-muted uppercase">{{ __('Amount') }}</th>
                                    <th class="py-3 px-4 text-xs font-bold text-muted dark:text-muted uppercase">{{ __('Method') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#EBDBC9]/50 dark:divide-[#273244]">
                                @forelse($payments as $payment)
                                    <tr>
                                        <td class="py-4 pr-4 font-bold text-muted dark:text-white font-mono text-xs">{{ $payment['id'] }}</td>
                                        <td class="py-4 px-4 text-muted dark:text-dark-subtle">{{ date('M d, Y', strtotime($payment['date'])) }}</td>
                                        <td class="py-4 px-4 font-bold text-muted dark:text-white">{{ $payment['amount'] }}</td>
                                        <td class="py-4 px-4 text-muted dark:text-dark-subtle">{{ $payment['method'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="p-6 text-center text-muted">{{ __('No verified payment records found.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Documents -->
            <div>
                <div class="card p-6 sticky top-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="card-title text-base">{{ __('Tenant Documents') }}</h3>
                        <span class="badge badge-success text-xs">{{ __('Verified') }}</span>
                    </div>
                    <div class="space-y-3">
                        @foreach($documents as $doc)
                            <div class="card-nested flex items-center gap-3 p-3">
                                <div class="w-10 h-10 rounded-lg bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-bold text-muted dark:text-white truncate">{{ $doc['name'] }}</p>
                                    <p class="text-xs text-muted">{{ date('M d, Y', strtotime($doc['date'])) }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
