<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Access Control & Permissions Matrix') }}</x-slot>

    <div x-data="{
        viewMode: 'role', // 'role' or 'user'
        selectedRoleId: '{{ $selectedRole->id ?? '' }}',
        activePermissions: {{ json_encode($rolePermissions ?? []) }},
        selectedUserId: '{{ $allUsers[0]['id'] ?? '' }}',
        dirty: false,

        togglePermission(code) {
            if (this.activePermissions.includes(code)) {
                this.activePermissions = this.activePermissions.filter(c => c !== code);
            } else {
                this.activePermissions.push(code);
            }
            this.dirty = true;
        },

        grantCategory(codes) {
            codes.forEach(c => {
                if (!this.activePermissions.includes(c)) {
                    this.activePermissions.push(c);
                }
            });
            this.dirty = true;
        },

        revokeCategory(codes) {
            this.activePermissions = this.activePermissions.filter(c => !codes.includes(c));
            this.dirty = true;
        },

        isGranted(code) {
            return this.activePermissions.includes(code);
        }
    }" class="max-w-7xl mx-auto space-y-6">

        {{-- Flash Alerts --}}
        @if (session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span class="text-sm font-semibold">{{ session('success') }}</span>
                </div>
                <button type="button" @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">&times;</button>
            </div>
        @endif

        {{-- Page Header --}}
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-orange-600 dark:text-orange-400 mb-1">
                    <span class="w-2 h-2 rounded-full bg-orange-500"></span>
                    {{ __('Role-Based Access Control (RBAC)') }}
                </div>
                <h1 class="text-3xl font-extrabold font-display text-muted dark:text-white tracking-tight">
                    {{ __('Access Control & Permissions Matrix') }}
                </h1>
                <p class="text-muted dark:text-muted mt-1.5 text-sm max-w-2xl">
                    {{ __('Determine exactly what roles and users are allowed to do across properties, marketplace listings, escrow finances, and system operations.') }}
                </p>
            </div>
            
            {{-- Mode Switcher Pill --}}
            <div class="flex items-center p-1 rounded-xl bg-white dark:bg-[#111827] border border-[#EBDBC9] dark:border-[#273244] shadow-xs">
                <button type="button" @click="viewMode = 'role'"
                        :class="viewMode === 'role' ? 'bg-[#FFEFE0] dark:bg-[#172033] text-orange-700 dark:text-orange-300 font-bold shadow-2xs' : 'text-muted dark:text-muted hover:text-muted'"
                        class="px-3.5 py-1.5 rounded-lg text-xs transition-all">
                    {{ __('Role Matrix') }}
                </button>
                <button type="button" @click="viewMode = 'user'"
                        :class="viewMode === 'user' ? 'bg-[#FFEFE0] dark:bg-[#172033] text-orange-700 dark:text-orange-300 font-bold shadow-2xs' : 'text-muted dark:text-muted hover:text-muted'"
                        class="px-3.5 py-1.5 rounded-lg text-xs transition-all">
                    {{ __('User Inspector') }}
                </button>
            </div>
        </div>

        @include('frontend.admin.partials.tabs')

        {{-- ═══════════════════════════════════════════════════════════
             MODE 1: ROLE CAPABILITIES MATRIX
             ═══════════════════════════════════════════════════════════ --}}
        <div x-show="viewMode === 'role'" class="space-y-6">

            {{-- Role Selector Cards --}}
            <div class="card bg-white dark:bg-[#111827] border border-[#EBDBC9] dark:border-[#273244] p-5 rounded-2xl shadow-xs">
                <div class="flex items-center justify-between mb-3">
                    <label class="text-xs font-bold uppercase tracking-wider text-muted dark:text-muted">
                        {{ __('Select Role to Configure') }}
                    </label>
                    <span class="text-xs text-muted dark:text-muted">
                        {{ count($roles) }} {{ __('system roles registered') }}
                    </span>
                </div>

                <div class="flex items-center gap-2.5 overflow-x-auto pb-1">
                    @foreach($roles as $role)
                        <a href="{{ route('dashboard.admin.permissions', ['role_id' => $role->id]) }}"
                           class="flex-shrink-0 px-4 py-2 rounded-xl text-xs sm:text-sm font-bold border transition-all inline-flex items-center gap-2
                                  {{ ($selectedRole && $selectedRole->id === $role->id)
                                      ? 'bg-orange-500 border-orange-500 text-white shadow-sm shadow-orange-500/25'
                                      : 'bg-[#FFF5EB]/60 dark:bg-[#172033]/60 border-[#EBDBC9] dark:border-[#273244] text-muted dark:text-dark-subtle hover:border-orange-300' }}">
                            <span>{{ $role->name }}</span>
                            @if($role->name === 'Administrator')
                                <span class="px-1.5 py-0.2 rounded text-xs bg-white/20 text-white">{{ __('Full Access') }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Role Summary & Bulk Action Bar --}}
            <form action="{{ route('dashboard.admin.permissions.update') }}" method="POST">
                @csrf
                <input type="hidden" name="role_id" value="{{ $selectedRole->id ?? '' }}">

                <div class="p-4 sm:p-5 rounded-2xl bg-[#FFEFE0] dark:bg-[#172033] border border-[#EBDBC9] dark:border-[#273244] flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 shadow-xs">
                    <div class="flex items-center gap-3.5 min-w-0">
                        <div class="w-10 h-10 rounded-xl bg-orange-500 text-white flex items-center justify-center font-bold flex-shrink-0 shadow-sm shadow-orange-500/30">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h2 class="text-base font-bold text-muted dark:text-white">
                                    {{ __('Role:') }} <span class="text-orange-600 dark:text-orange-400">{{ $selectedRole->name ?? 'Role' }}</span>
                                </h2>
                                <span class="badge badge-brand text-xs font-bold" x-text="activePermissions.length + ' Allowed Capabilities'"></span>
                            </div>
                            <p class="text-xs text-muted dark:text-muted mt-0.5">
                                {{ __('Toggle specific capabilities on or off below. All users with this role will immediately inherit these permissions.') }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 self-end sm:self-center flex-shrink-0">
                        <button type="submit" class="btn btn-primary btn-sm px-5 py-2 font-bold shadow-xs inline-flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                            {{ __('Save Role Permissions') }}
                        </button>
                    </div>
                </div>

                {{-- Hidden Inputs for Active Permissions --}}
                <template x-for="code in activePermissions" :key="code">
                    <input type="hidden" name="permissions[]" :value="code">
                </template>

                {{-- Categories Grid --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    @foreach($categories as $catKey => $category)
                        @php
                            $catCodes = collect($category['permissions'])->pluck('code')->toArray();
                        @endphp
                        <div class="card bg-white dark:bg-[#111827] border border-[#EBDBC9] dark:border-[#273244] rounded-2xl overflow-hidden shadow-xs flex flex-col justify-between">
                            
                            {{-- Category Header --}}
                            <div>
                                <div class="p-4 sm:p-5 border-b border-[#EBDBC9] dark:border-[#273244] bg-[#FFF5EB]/40 dark:bg-[#172033]/40 flex items-center justify-between">
                                    <div class="min-w-0">
                                        <h3 class="text-sm font-bold text-muted dark:text-white flex items-center gap-2">
                                            <span>{{ __($category['name']) }}</span>
                                        </h3>
                                        <p class="text-xs text-muted dark:text-muted mt-0.5 truncate">
                                            {{ __($category['description']) }}
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-2 flex-shrink-0">
                                        <button type="button" @click="grantCategory({{ json_encode($catCodes) }})" class="text-xs font-bold text-primary-600 dark:text-primary-400 hover:underline">
                                            {{ __('Grant All') }}
                                        </button>
                                        <span class="text-muted">|</span>
                                        <button type="button" @click="revokeCategory({{ json_encode($catCodes) }})" class="text-xs font-bold text-muted hover:text-muted dark:hover:text-muted hover:underline">
                                            {{ __('Revoke') }}
                                        </button>
                                    </div>
                                </div>

                                {{-- Permission List Items --}}
                                <div class="divide-y divide-[#EBDBC9]/50 dark:divide-[#273244]">
                                    @foreach($category['permissions'] as $perm)
                                        <div class="p-4 flex items-start justify-between gap-4 hover:bg-[#FFF5EB]/30 dark:hover:bg-[#172033]/20 transition-colors">
                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <span class="text-sm font-bold text-muted dark:text-white">
                                                        {{ __($perm['name']) }}
                                                    </span>
                                                    <code class="text-xs px-1.5 py-0.5 rounded bg-surface-subtle dark:bg-surface text-muted dark:text-muted font-mono">
                                                        {{ $perm['code'] }}
                                                    </code>
                                                </div>
                                                <p class="text-xs text-muted dark:text-muted mt-1 leading-relaxed">
                                                    {{ __($perm['desc']) }}
                                                </p>
                                            </div>

                                            {{-- Interactive Toggle Switch --}}
                                            <div class="flex items-center gap-2.5 flex-shrink-0 pt-0.5">
                                                <span class="text-xs font-semibold"
                                                      :class="isGranted('{{ $perm['code'] }}') ? 'text-emerald-600 dark:text-emerald-400' : 'text-muted dark:text-muted'"
                                                      x-text="isGranted('{{ $perm['code'] }}') ? '{{ __('Allowed') }}' : '{{ __('Restricted') }}'"></span>

                                                <button type="button"
                                                        @click="togglePermission('{{ $perm['code'] }}')"
                                                        :class="isGranted('{{ $perm['code'] }}') ? 'bg-orange-500' : 'bg-surface-subtle dark:bg-surface'"
                                                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-hidden focus:ring-2 focus:ring-orange-500 focus:ring-offset-2"
                                                        role="switch"
                                                        :aria-checked="isGranted('{{ $perm['code'] }}')">
                                                    <span :class="isGranted('{{ $perm['code'] }}') ? 'translate-x-5' : 'translate-x-0'"
                                                          class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-sm ring-0 transition duration-200 ease-in-out"></span>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                        </div>
                    @endforeach
                </div>

                {{-- Bottom Sticky Save Bar --}}
                <div x-show="dirty" class="sticky bottom-4 z-30 mt-6 p-4 rounded-2xl bg-white dark:bg-[#111827] border border-orange-300 dark:border-orange-500/50 shadow-xl flex items-center justify-between gap-4">
                    <div class="flex items-center gap-2 text-xs text-muted dark:text-dark-subtle">
                        <span class="w-2.5 h-2.5 rounded-full bg-orange-500 animate-pulse"></span>
                        <span class="font-bold">{{ __('You have unsaved permission changes for') }} {{ $selectedRole->name ?? 'this role' }}.</span>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm px-6 font-bold shadow-xs">
                        {{ __('Save Permissions Now') }}
                    </button>
                </div>
            </form>
        </div>

        {{-- ═══════════════════════════════════════════════════════════
             MODE 2: INDIVIDUAL USER ACCESS INSPECTOR
             ═══════════════════════════════════════════════════════════ --}}
        <div x-show="viewMode === 'user'" class="space-y-6" x-cloak>
            
            <div class="card bg-white dark:bg-[#111827] border border-[#EBDBC9] dark:border-[#273244] p-6 rounded-2xl shadow-xs">
                <div class="max-w-xl">
                    <label class="form-label form-label-required text-sm font-bold">
                        {{ __('Select User to Inspect Effective Permissions') }}
                    </label>
                    <select x-model="selectedUserId" class="form-input mt-1.5 cursor-pointer font-medium text-sm">
                        @foreach($allUsers as $u)
                            <option value="{{ $u['id'] }}">
                                {{ $u['name'] }} ({{ $u['email'] }}) — Role: {{ $u['role'] }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-muted dark:text-muted mt-2">
                        {{ __('Inspect the exact capabilities granted to this user through their assigned role and custom tenant overrides.') }}
                    </p>
                </div>
            </div>

            {{-- User Abilities Visual Cloud --}}
            <div class="card bg-white dark:bg-[#111827] border border-[#EBDBC9] dark:border-[#273244] p-6 sm:p-8 rounded-2xl shadow-xs space-y-6">
                <div class="flex items-center justify-between border-b border-[#EBDBC9] dark:border-[#273244] pb-4">
                    <div>
                        <h3 class="text-lg font-bold font-display text-muted dark:text-white">
                            {{ __('Effective Capabilities Breakdown') }}
                        </h3>
                        <p class="text-xs text-muted dark:text-muted mt-0.5">
                            {{ __('Real-time authorization matrix evaluated against the HasRoles engine.') }}
                        </p>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200/50">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        {{ __('Authorized Active User') }}
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach($categories as $category)
                        <div class="p-4 rounded-xl bg-[#FFF5EB]/60 dark:bg-[#172033]/60 border border-[#EBDBC9] dark:border-[#273244] space-y-3">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-orange-600 dark:text-orange-400">
                                {{ __($category['name']) }}
                            </h4>
                            <ul class="space-y-2">
                                @foreach($category['permissions'] as $perm)
                                    <li class="flex items-center justify-between text-xs">
                                        <span class="text-muted dark:text-dark-subtle">{{ __($perm['name']) }}</span>
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300">
                                            {{ __('Granted') }}
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>

    </div>
</x-layouts.dashboard>
