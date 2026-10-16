<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('User Management') }}</x-slot>

    <div x-data="{
        showCreateModal: false,
        searchQuery: '',
        roleFilter: '',
        statusFilter: '',
        newUser: {
            name: '',
            email: '',
            phone: '+251 ',
            password: 'password123',
            role: '{{ $roles[0] ?? 'User' }}',
            status: 'active',
            send_invite: true
        }
    }" class="max-w-7xl mx-auto space-y-6">

        {{-- Flash alerts --}}
        @if (session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span class="text-sm font-semibold">{{ session('success') }}</span>
                </div>
                <button type="button" @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">&times;</button>
            </div>
        @endif

        @if (session('error'))
            <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-rose-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
                    <span class="text-sm font-semibold">{{ session('error') }}</span>
                </div>
                <button type="button" @click="$el.parentElement.remove()" class="text-rose-500 hover:text-rose-700">&times;</button>
            </div>
        @endif

        {{-- Header & Primary Actions --}}
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div>
                <h1 class="text-3xl font-extrabold font-display text-muted dark:text-white tracking-tight">{{ __('User & Team Management') }}</h1>
                <p class="text-muted dark:text-muted mt-1.5 text-sm">{{ __('Provision accounts, assign role permissions, and control tenant access levels.') }}</p>
            </div>
            <div class="flex items-center gap-3 flex-wrap">
                <a href="{{ route('dashboard.admin.permissions') }}" class="btn btn-secondary btn-sm inline-flex items-center gap-2">
                    <svg class="w-4 h-4 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg>
                    {{ __('Permissions Matrix') }}
                </a>
                <button @click="showCreateModal = true" class="btn btn-primary btn-sm inline-flex items-center gap-2 shadow-xs" id="btn-add-user">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    {{ __('Add New User') }}
                </button>
            </div>
        </div>

        @include('frontend.admin.partials.tabs')

        {{-- Filter Bar & Table Card --}}
        <div class="card bg-white dark:bg-[#111827] border border-[#EBDBC9] dark:border-[#273244] rounded-2xl overflow-hidden shadow-xs">
            <div class="p-4 sm:p-5 border-b border-[#EBDBC9] dark:border-[#273244] flex flex-col sm:flex-row gap-3 items-stretch sm:items-center justify-between bg-[#FFF5EB]/30 dark:bg-[#172033]/40">
                <div class="relative flex-1 max-w-md">
                    <input type="text" x-model="searchQuery" placeholder="{{ __('Search by name, email, or role...') }}" class="form-input text-xs sm:text-sm pl-9">
                    <svg class="w-4 h-4 text-muted absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                </div>
                <div class="flex items-center gap-2.5 flex-wrap">
                    <select x-model="roleFilter" class="form-input text-xs py-2 pr-8 w-auto">
                        <option value="">{{ __('All Roles') }}</option>
                        @foreach($roles as $r)
                            <option value="{{ $r }}">{{ $r }}</option>
                        @endforeach
                    </select>
                    <select x-model="statusFilter" class="form-input text-xs py-2 pr-8 w-auto">
                        <option value="">{{ __('All Statuses') }}</option>
                        <option value="active">{{ __('Active') }}</option>
                        <option value="suspended">{{ __('Suspended') }}</option>
                    </select>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#FFEFE0] dark:bg-[#172033] border-b border-[#EBDBC9] dark:border-[#273244]">
                            <th class="p-4 text-xs font-bold text-muted dark:text-dark-subtle uppercase tracking-wider">{{ __('User Profile') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-dark-subtle uppercase tracking-wider">{{ __('Assigned Role') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-dark-subtle uppercase tracking-wider">{{ __('Permissions Scope') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-dark-subtle uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-dark-subtle uppercase tracking-wider">{{ __('Created') }}</th>
                            <th class="p-4 text-xs font-bold text-muted dark:text-dark-subtle uppercase tracking-wider text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EBDBC9]/60 dark:divide-[#273244]">
                        @forelse($users as $user)
                            <tr x-show="(searchQuery === '' || '{{ strtolower($user['name'] . ' ' . $user['email'] . ' ' . $user['role']) }}'.includes(searchQuery.toLowerCase())) &&
                                        (roleFilter === '' || '{{ $user['role'] }}' === roleFilter) &&
                                        (statusFilter === '' || '{{ $user['status'] }}' === statusFilter)"
                                class="hover:bg-[#FFF5EB]/60 dark:hover:bg-[#1e293b]/50 transition-colors">
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-orange-100 dark:bg-orange-950/50 text-orange-700 dark:text-orange-300 flex items-center justify-center font-bold text-sm flex-shrink-0 shadow-2xs">
                                            {{ strtoupper(substr($user['name'], 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <a href="{{ route('dashboard.admin.users.show', $user['id']) }}" class="font-bold text-sm text-muted dark:text-white hover:text-orange-600 transition-colors truncate block">
                                                {{ $user['name'] }}
                                            </a>
                                            <div class="flex items-center gap-2 text-xs text-muted dark:text-muted mt-0.5">
                                                <span>{{ $user['email'] }}</span>
                                                @if(isset($user['phone']))
                                                    <span>&bull;</span>
                                                    <span>{{ $user['phone'] }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-orange-50 dark:bg-orange-950/40 text-orange-700 dark:text-orange-300 border border-orange-200/60 dark:border-orange-800/60">
                                        {{ $user['role'] }}
                                    </span>
                                </td>
                                <td class="p-4">
                                    <a href="{{ route('dashboard.admin.permissions') }}" class="text-xs text-primary-600 dark:text-primary-400 hover:underline font-medium inline-flex items-center gap-1">
                                        <span>{{ __('Inspect Permissions') }}</span>
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                                    </a>
                                </td>
                                <td class="p-4">
                                    @if($user['status'] === 'active')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-300 border border-emerald-200/50">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            {{ __('Active') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 dark:bg-rose-950/30 text-rose-700 dark:text-rose-300 border border-rose-200/50">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            {{ __('Suspended') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="p-4 text-xs text-muted dark:text-muted font-medium">
                                    {{ $user['joined'] ?? '2026-10-08' }}
                                </td>
                                <td class="p-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('dashboard.admin.users.show', $user['id']) }}" class="btn btn-secondary btn-sm text-xs py-1 px-2.5" title="View details">
                                            {{ __('View') }}
                                        </a>
                                        <a href="{{ route('dashboard.admin.permissions') }}" class="btn btn-outline btn-sm text-xs py-1 px-2.5 text-orange-600" title="Configure abilities">
                                            {{ __('Access') }}
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-muted">
                                    {{ __('No users found in directory.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════
             MODAL: CREATE NEW USER
             ═══════════════════════════════════════════════════════════ --}}
        <div x-show="showCreateModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4"
             x-cloak>
            
            <div @click.outside="showCreateModal = false"
                 class="card bg-white dark:bg-[#111827] border border-[#EBDBC9] dark:border-[#273244] rounded-3xl w-full max-w-xl p-6 sm:p-8 shadow-2xl space-y-6 relative">
                
                {{-- Modal Header --}}
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-orange-100 dark:bg-orange-950/40 text-orange-600 dark:text-orange-400 flex items-center justify-center font-bold">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.765z" /></svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold font-display text-muted dark:text-white">{{ __('Create New User') }}</h2>
                            <p class="text-xs text-muted dark:text-muted mt-0.5">{{ __('Provision credentials and grant role permissions.') }}</p>
                        </div>
                    </div>
                    <button @click="showCreateModal = false" class="text-muted hover:text-muted dark:hover:text-muted text-2xl font-light leading-none">&times;</button>
                </div>

                {{-- User Creation Form --}}
                <form action="{{ route('dashboard.admin.users.store') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label class="form-label form-label-required">{{ __('Full Name') }}</label>
                        <input type="text" name="name" x-model="newUser.name" required placeholder="e.g. Dawit Haile" class="form-input">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label form-label-required">{{ __('Email Address') }}</label>
                            <input type="email" name="email" x-model="newUser.email" required placeholder="name@company.et" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">{{ __('Ethiopian Phone Number') }}</label>
                            <input type="text" name="phone" x-model="newUser.phone" placeholder="+251 911 000 000" class="form-input">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label form-label-required">{{ __('Role Assignment') }}</label>
                            <select name="role" x-model="newUser.role" class="form-input cursor-pointer font-medium">
                                @foreach($roles as $r)
                                    <option value="{{ $r }}">{{ $r }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label form-label-required">{{ __('Initial Password') }}</label>
                            <input type="text" name="password" x-model="newUser.password" required class="form-input font-mono text-sm">
                        </div>
                    </div>

                    {{-- Role Permissions Preview Badge --}}
                    <div class="p-3 rounded-xl bg-[#FFEFE0] dark:bg-[#172033] border border-[#EBDBC9] dark:border-[#273244] text-xs text-muted dark:text-dark-subtle">
                        <span class="font-bold text-orange-600 dark:text-orange-400">{{ __('Permission Scope:') }}</span>
                        <span x-text="'Assigned ' + newUser.role + ' privileges. Configure granular capabilities in the Permissions Matrix.'"></span>
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <input type="checkbox" id="send_invite" name="send_invite" checked class="rounded border-[#EBDBC9] text-orange-500 focus:ring-orange-500">
                        <label for="send_invite" class="text-xs text-muted dark:text-dark-subtle font-medium cursor-pointer">
                            {{ __('Send login credentials and welcome instructions via email') }}
                        </label>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#EBDBC9] dark:border-[#273244]">
                        <button type="button" @click="showCreateModal = false" class="btn btn-secondary text-xs px-4 py-2">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit" class="btn btn-primary text-xs px-5 py-2 font-bold shadow-xs">
                            {{ __('Create User Account') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
