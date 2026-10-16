<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Company Profile') }}</x-slot>

    <div class="max-w-7xl mx-auto">
        
        <div class="mb-8">
            <h1 class="text-3xl font-extrabold font-display text-muted dark:text-white tracking-tight">{{ __('Company Profile') }}</h1>
            <p class="text-muted dark:text-muted mt-2">{{ __('Manage your company\'s public and internal details.') }}</p>
        </div>

        @include('frontend.organization.partials.tabs')

        <div class="card overflow-hidden">
            <div class="card-header flex items-center justify-between">
                <div>
                    <h2 class="card-title text-xl">{{ __('Organization Details') }}</h2>
                    <p class="text-sm text-muted dark:text-muted mt-1">{{ __('This information will be displayed publicly on your properties.') }}</p>
                </div>
                <button class="btn btn-secondary btn-sm">{{ __('Edit Details') }}</button>
            </div>
            
            <div class="card-body">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-8">
                    <div>
                        <dt class="card-kv-label">Company Name</dt>
                        <dd class="card-kv-value mt-1 text-base">{{ $organization['name'] }}</dd>
                    </div>
                    <div>
                        <dt class="card-kv-label">Status</dt>
                        <dd class="mt-1">
                            <span class="badge {{ $organization['status'] === 'active' ? 'badge-success' : 'badge-neutral' }} uppercase">
                                {{ $organization['status'] }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="card-kv-label">Primary Email</dt>
                        <dd class="card-kv-value mt-1 text-base font-normal">{{ $organization['email'] }}</dd>
                    </div>
                    <div>
                        <dt class="card-kv-label">Primary Phone</dt>
                        <dd class="card-kv-value mt-1 text-base font-normal">{{ $organization['phone'] }}</dd>
                    </div>
                    <div class="md:col-span-2">
                        <dt class="card-kv-label">Headquarters Address</dt>
                        <dd class="card-kv-value mt-1 text-base font-normal">{{ $organization['address'] }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</x-layouts.dashboard>
