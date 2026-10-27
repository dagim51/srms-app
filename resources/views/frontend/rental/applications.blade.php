<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Rental Applications') }}</x-slot>

    <div class="max-w-7xl mx-auto page-enter space-y-6" x-data="{ showNewApp: false, selectedApp: null }">
        <x-page-header
            title="Rental Applications"
            description="Process applicant background verifications, tenant income checks, and tenancy approvals."
            icon="document"
            badge="Applications">
            <x-slot:actions>
                <button @click="showNewApp = true" class="btn btn-primary text-xs shadow-sm shadow-orange-500/20">
                    <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    {{ __('New Application') }}
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

        {{-- Actions & Filter Toolbar --}}
        <div class="card cinematic-fade-up cinematic-stagger-2 bg-white dark:bg-[#1E293B] rounded-xl p-4 border border-warm dark:border-warm space-y-4 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-muted dark:text-white">{{ __('Rental Applications Queue') }}</h2>
                    <p class="text-xs text-muted dark:text-muted">{{ __('Applicant verification, employment documentation, and tenancy review') }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <button @click="showNewApp = true" class="btn btn-primary btn-sm flex items-center gap-1.5 shadow-sm shadow-orange-500/20 text-xs">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        {{ __('New Application') }}
                    </button>
                </div>
            </div>

            {{-- Filter Form --}}
            <form method="GET" action="{{ route('dashboard.rental.applications') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-12 gap-3 pt-2 border-t border-warm dark:border-warm items-center">
                <div class="md:col-span-6 relative">
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="{{ __('Search applicants by name or unit...') }}"
                           class="form-input search-input w-full pl-9 pr-8 text-xs">
                    <svg class="w-4 h-4 text-muted absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                    @if(request('search'))
                        <a href="{{ route('dashboard.rental.applications', request()->except('search')) }}" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200" title="{{ __('Clear search') }}">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </a>
                    @endif
                </div>

                <div class="md:col-span-4">
                    <select name="status" onchange="this.form.submit()" class="form-select w-full text-xs">
                        <option value="">{{ __('All Statuses') }}</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                        <option value="under_review" {{ request('status') === 'under_review' ? 'selected' : '' }}>{{ __('Under Review') }}</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>{{ __('Approved') }}</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>{{ __('Rejected') }}</option>
                    </select>
                </div>

                <div class="md:col-span-2 flex items-center gap-2">
                    <button type="submit" class="btn btn-secondary btn-sm text-xs py-2 px-3 w-full justify-center">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                        <span>{{ __('Filter') }}</span>
                    </button>

                    @if(request()->hasAny(['search', 'status']))
                        <a href="{{ route('dashboard.rental.applications') }}" class="p-2 text-muted hover:text-orange-600 dark:hover:text-orange-400" title="{{ __('Reset Filters') }}">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-warm-50/50 dark:bg-[#1E293B]/50 border-b border-[#EBDBC9] dark:border-[#273244]">
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Applicant') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Unit & Rent') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Submitted') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EBDBC9]/50 dark:divide-[#273244]">
                        @forelse($applications as $app)
                            <tr class="hover:bg-surface dark:hover:bg-surface/30 transition-colors">
                                <td class="p-4 font-bold text-muted dark:text-white">
                                    {{ $app['applicant'] }}
                                </td>
                                <td class="p-4">
                                    <p class="text-sm font-medium text-muted dark:text-white">{{ $app['unit'] }}</p>
                                    <p class="text-xs text-muted">{{ $app['monthly_rent'] }}/mo</p>
                                </td>
                                <td class="p-4 text-muted dark:text-dark-subtle">{{ date('M d, Y', strtotime($app['date'])) }}</td>
                                <td class="p-4">
                                    <span class="badge {{ $app['status'] === 'approved' ? 'badge-success' : ($app['status'] === 'rejected' ? 'badge-error' : ($app['status'] === 'under_review' ? 'badge-primary' : 'badge-warning')) }} uppercase text-xs">{{ __(str_replace('_', ' ', $app['status'])) }}</span>
                                </td>
                                <td class="p-4 text-right">
                                    <button @click="selectedApp = {{ json_encode($app) }}" class="btn btn-sm btn-secondary text-xs">
                                        {{ __('Review Decision') }}
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-8 text-center text-muted">{{ __('No applications matching query.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($applications->hasPages())
                <div class="p-4 border-t border-warm dark:border-warm">
                    {{ $applications->links() }}
                </div>
            @endif
        </div>

        {{-- New Application Modal --}}
        <div x-show="showNewApp" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="absolute inset-0 bg-black/50" @click="showNewApp = false"></div>
            <div class="relative bg-white dark:bg-dark-card rounded-2xl shadow-2xl w-full max-w-lg p-6 z-10">
                <div class="flex justify-between items-center mb-5">
                    <h3 class="text-lg font-bold text-muted dark:text-white">{{ __('Submit New Rental Application') }}</h3>
                    <button @click="showNewApp = false" class="text-muted hover:text-muted/70">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form method="POST" action="{{ route('dashboard.rental.applications.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-muted dark:text-dark-subtle mb-1">{{ __('Rental Property') }} <span class="text-red-500">*</span></label>
                        <select name="property_id" class="input w-full" required>
                            <option value="">{{ __('— Select Property —') }}</option>
                            @foreach($properties as $prop)
                                <option value="{{ $prop->id }}">{{ $prop->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-muted dark:text-dark-subtle mb-1">{{ __('Applicant / Tenant User') }} <span class="text-red-500">*</span></label>
                        <select name="user_id" class="input w-full" required>
                            <option value="">{{ __('— Select Registered User —') }}</option>
                            @foreach($tenants as $t)
                                <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->phone ?? 'No Phone' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-muted dark:text-dark-subtle mb-1">{{ __('Proposed Rent (ETB)') }} <span class="text-red-500">*</span></label>
                            <input type="number" name="rent_amount" step="0.01" class="input w-full" placeholder="e.g. 20000" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-muted dark:text-dark-subtle mb-1">{{ __('Security Deposit (ETB)') }}</label>
                            <input type="number" name="deposit_amount" step="0.01" class="input w-full" placeholder="e.g. 40000">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-muted dark:text-dark-subtle mb-1">{{ __('Preferred Move-in') }} <span class="text-red-500">*</span></label>
                            <input type="date" name="start_date" value="{{ date('Y-m-d') }}" class="input w-full" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-muted dark:text-dark-subtle mb-1">{{ __('Lease End Date') }} <span class="text-red-500">*</span></label>
                            <input type="date" name="end_date" value="{{ date('Y-m-d', strtotime('+1 year')) }}" class="input w-full" required>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-muted dark:text-dark-subtle mb-1">{{ __('Employment Status') }}</label>
                            <input type="text" name="employment_status" class="input w-full" placeholder="e.g. Full-time Employed">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-muted dark:text-dark-subtle mb-1">{{ __('Annual Income (ETB)') }}</label>
                            <input type="number" name="annual_income" step="0.01" class="input w-full" placeholder="e.g. 300000">
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-3">
                        <button type="button" @click="showNewApp = false" class="btn btn-secondary">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Submit Application') }}</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Review Decision Modal --}}
        <div x-show="selectedApp !== null" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="absolute inset-0 bg-black/50" @click="selectedApp = null"></div>
            <div class="relative bg-white dark:bg-dark-card rounded-2xl shadow-2xl w-full max-w-md p-6 z-10" x-data="{ decisionStatus: 'approved' }">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-muted dark:text-white">{{ __('Review Application Decision') }}</h3>
                    <button @click="selectedApp = null" class="text-muted hover:text-muted/70">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <template x-if="selectedApp">
                    <form method="POST" :action="'{{ url('dashboard/rental/applications') }}/' + selectedApp.id + '/status'" class="space-y-4">
                        @csrf
                        <div class="p-3 bg-surface dark:bg-surface/20 rounded-xl space-y-1 text-sm border border-warm dark:border-warm">
                            <p class="font-bold text-muted dark:text-white" x-text="selectedApp.applicant"></p>
                            <p class="text-xs text-muted" x-text="selectedApp.unit + ' • ' + selectedApp.monthly_rent + '/mo'"></p>
                            <p class="text-xs text-muted" x-text="'Applied: ' + selectedApp.date"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-muted dark:text-dark-subtle mb-1">{{ __('Select Decision') }} <span class="text-red-500">*</span></label>
                            <select name="status" x-model="decisionStatus" class="input w-full" required>
                                <option value="approved">{{ __('Approve & Activate Tenancy') }}</option>
                                <option value="under_review">{{ __('Set Under Background Review') }}</option>
                                <option value="rejected">{{ __('Reject Application') }}</option>
                            </select>
                        </div>

                        <div class="flex justify-end gap-3 pt-3">
                            <button type="button" @click="selectedApp = null" class="btn btn-secondary">{{ __('Close') }}</button>
                            <button type="submit" class="btn btn-primary">{{ __('Save Decision') }}</button>
                        </div>
                    </form>
                </template>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
