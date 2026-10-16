<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('All Leads') }}</x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="mb-8 flex justify-between items-end">
            <div>
                <h1 class="text-3xl font-extrabold font-display text-muted dark:text-white tracking-tight">{{ __('Leads') }}</h1>
                <p class="text-muted dark:text-muted mt-2">{{ __('Manage all prospective clients and inquiries.') }}</p>
            </div>
            <button class="btn btn-primary">{{ __('Add Lead') }}</button>
        </div>

        @include('frontend.crm.partials.tabs')

        <div class="card">
            <div class="card-header flex gap-4">
                <input type="text" placeholder="{{ __('Search leads...') }}" class="form-input w-full max-w-md">
                <select class="form-input max-w-xs">
                    <option>{{ __('All Statuses') }}</option>
                    <option>{{ __('New Lead') }}</option>
                    <option>{{ __('Contacted') }}</option>
                </select>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#FFF5EB]/60 dark:bg-[#172033]/60 border-b border-[#EBDBC9] dark:border-[#273244]">
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Name') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Property/Interest') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Value') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Assigned To') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider">{{ __('Last Contact') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-muted uppercase tracking-wider text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EBDBC9]/60 dark:divide-[#273244]">
                        @foreach($leads as $lead)
                            <tr class="hover:bg-surface dark:hover:bg-surface/30 transition-colors">
                                <td class="p-4 font-bold text-muted dark:text-white">
                                    <a href="{{ route('dashboard.crm.leads.show', $lead['id']) }}" class="hover:text-primary-600">{{ $lead['name'] }}</a>
                                </td>
                                <td class="p-4 text-muted dark:text-dark-subtle">{{ $lead['property'] }}</td>
                                <td class="p-4 font-medium text-muted dark:text-white">{{ $lead['value'] }}</td>
                                <td class="p-4">
                                    <span class="badge badge-neutral uppercase">{{ __($lead['status']) }}</span>
                                </td>
                                <td class="p-4 text-muted dark:text-dark-subtle">{{ $lead['assigned_to'] }}</td>
                                <td class="p-4 text-muted dark:text-dark-subtle">{{ $lead['last_contact'] }}</td>
                                <td class="p-4 text-right">
                                    <a href="{{ route('dashboard.crm.leads.show', $lead['id']) }}" class="text-primary-600 hover:text-primary-700 font-medium text-sm">{{ __('View') }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
