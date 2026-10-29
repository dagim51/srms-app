<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Role Management') }}</x-slot>

    <div x-data="{
        createModalOpen: false,
        editModalOpen: false,
        deleteModalOpen: false,
        selectedRole: null,
        editForm: { id: '', name: '', description: '', is_system: false },
        deleteForm: { id: '', name: '' },

        openEditModal(role) {
            this.selectedRole = role;
            this.editForm = {
                id: role.id,
                name: role.name,
                description: role.description || '',
                is_system: role.is_system
            };
            this.editModalOpen = true;
        },

        openDeleteModal(role) {
            this.deleteForm = { id: role.id, name: role.name };
            this.deleteModalOpen = true;
        }
    }" class="max-w-7xl mx-auto page-enter space-y-6">

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

        @if (session('error'))
            <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-rose-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 7.5h.008v.008H12v-.008z" /></svg>
                    <span class="text-sm font-semibold">{{ session('error') }}</span>
                </div>
                <button type="button" @click="$el.parentElement.remove()" class="text-rose-500 hover:text-rose-700">&times;</button>
            </div>
        @endif

        {{-- Standardized Page Header Card --}}
        <x-page-header
            title="System Roles & Governance"
            description="Manage all 17 canonical platform roles and custom operational profiles, user assignments, and capability boundaries."
            icon="shield"
            badge="Access Control">
            <x-slot:actions>
                <button type="button" @click="createModalOpen = true"
                        class="px-4 py-2.5 rounded-xl bg-orange-600 hover:bg-orange-700 text-white font-bold text-xs sm:text-sm shadow-sm transition-all flex items-center gap-2 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    <span>{{ __('Create Custom Role') }}</span>
                </button>
            </x-slot:actions>
        </x-page-header>

        @include('frontend.admin.partials.tabs')

        {{-- Search and Summary Bar --}}
        <div class="card bg-white dark:bg-[#111827] border border-[#EBDBC9] dark:border-[#273244] p-4 rounded-2xl flex flex-col sm:flex-row items-center justify-between gap-4">
            <form method="GET" action="{{ route('dashboard.admin.roles') }}" class="w-full sm:w-80 relative">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="{{ __('Search roles or descriptions...') }}"
                       class="w-full pl-9 pr-4 py-2 text-xs rounded-xl bg-[#FFF5EB]/60 dark:bg-[#172033]/60 border border-[#EBDBC9] dark:border-[#273244] text-[#1F2937] dark:text-[#F3F4F6] focus:outline-none focus:ring-1 focus:ring-orange-500">
                <svg class="w-4 h-4 absolute left-3 top-2.5 text-muted dark:text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
            </form>
            <div class="flex items-center gap-3 text-xs text-muted dark:text-muted">
                <span><strong>{{ $roles->count() }}</strong> {{ __('total roles') }}</span>
                <span>•</span>
                <span><strong>{{ $roles->where('is_system', true)->count() }}</strong> {{ __('system-critical') }}</span>
                <span>•</span>
                <span><strong>{{ $roles->where('is_system', false)->count() }}</strong> {{ __('custom') }}</span>
            </div>
        </div>

        {{-- Roles Table --}}
        <div class="card bg-white dark:bg-[#111827] border border-[#EBDBC9] dark:border-[#273244] rounded-2xl overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-[#FFF5EB]/80 dark:bg-[#172033]/80 border-b border-[#EBDBC9] dark:border-[#273244] text-muted dark:text-muted font-bold uppercase tracking-wider">
                            <th class="py-3.5 px-4">{{ __('Role Name') }}</th>
                            <th class="py-3.5 px-4">{{ __('Description') }}</th>
                            <th class="py-3.5 px-4 text-center">{{ __('Users') }}</th>
                            <th class="py-3.5 px-4 text-center">{{ __('Permissions') }}</th>
                            <th class="py-3.5 px-4 text-center">{{ __('Type') }}</th>
                            <th class="py-3.5 px-4 text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EBDBC9]/50 dark:divide-[#273244]/50">
                        @forelse($roles as $role)
                            <tr class="hover:bg-[#FFF5EB]/30 dark:hover:bg-[#172033]/30 transition-colors">
                                <td class="py-3.5 px-4 font-bold text-foreground dark:text-foreground">
                                    <div class="flex items-center gap-2">
                                        <span>{{ $role->name }}</span>
                                        @if($role->name === 'Administrator')
                                            <span class="px-1.5 py-0.5 rounded text-[10px] bg-red-100 text-red-700 dark:bg-red-950/60 dark:text-red-300 font-extrabold uppercase">Super Admin</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-muted dark:text-dark-subtle max-w-md truncate">
                                    {{ $role->description ?: 'No description provided.' }}
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <a href="{{ route('dashboard.admin.users', ['role' => $role->name]) }}"
                                       class="inline-block px-2.5 py-1 rounded-full font-bold bg-[#FFEFE0] dark:bg-[#172033] text-orange-700 dark:text-orange-300 hover:underline">
                                        {{ $role->users_count }}
                                    </a>
                                </td>
                                <td class="py-3.5 px-4 text-center font-bold text-muted dark:text-muted">
                                    <a href="{{ route('dashboard.admin.permissions', ['role_id' => $role->id]) }}"
                                       class="inline-flex items-center gap-1 hover:text-orange-600 transition-colors">
                                        <span>{{ $role->permissions_count }}</span>
                                        <svg class="w-3.5 h-3.5 text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                    </a>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($role->isSystem())
                                        <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-blue-50 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                            {{ __('System') }}
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                            {{ __('Custom') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('dashboard.admin.permissions', ['role_id' => $role->id]) }}"
                                           title="{{ __('Edit Permissions') }}"
                                           class="p-1.5 rounded-lg text-muted hover:text-orange-600 hover:bg-[#FFEFE0] dark:hover:bg-[#172033] transition-colors">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" /></svg>
                                        </a>

                                        <button type="button" @click="openEditModal({{ json_encode($role) }})"
                                                title="{{ __('Edit Details') }}"
                                                class="p-1.5 rounded-lg text-muted hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-950/50 transition-colors cursor-pointer">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                        </button>

                                        @if(!$role->isSystem())
                                            <button type="button" @click="openDeleteModal({{ json_encode($role) }})"
                                                    title="{{ __('Delete Role') }}"
                                                    class="p-1.5 rounded-lg text-muted hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/50 transition-colors cursor-pointer">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                            </button>
                                        @else
                                            <span class="p-1.5 text-muted/40" title="{{ __('Protected System Role') }}">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-muted dark:text-muted">
                                    {{ __('No roles found matching your query.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════
             MODAL 1: CREATE CUSTOM ROLE
             ═══════════════════════════════════════════════════════════ --}}
        <div x-show="createModalOpen" style="display: none;"
             class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.away="createModalOpen = false"
                 class="bg-white dark:bg-[#111827] border border-[#EBDBC9] dark:border-[#273244] w-full max-w-md rounded-2xl p-6 shadow-xl space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-[#EBDBC9] dark:border-[#273244]">
                    <h3 class="text-base font-bold text-foreground dark:text-foreground">{{ __('Create Custom Role') }}</h3>
                    <button type="button" @click="createModalOpen = false" class="text-muted hover:text-foreground text-lg">&times;</button>
                </div>

                <form method="POST" action="{{ route('dashboard.admin.roles.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-muted dark:text-muted mb-1">{{ __('Role Name') }} *</label>
                        <input type="text" name="name" required placeholder="e.g. Regional Inspector"
                               class="w-full px-3 py-2 text-xs rounded-xl bg-[#FFF5EB]/60 dark:bg-[#172033]/60 border border-[#EBDBC9] dark:border-[#273244] focus:outline-none focus:ring-1 focus:ring-orange-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-muted dark:text-muted mb-1">{{ __('Description') }}</label>
                        <textarea name="description" rows="3" placeholder="Describe the responsibilities and scope of this role..."
                                  class="w-full px-3 py-2 text-xs rounded-xl bg-[#FFF5EB]/60 dark:bg-[#172033]/60 border border-[#EBDBC9] dark:border-[#273244] focus:outline-none focus:ring-1 focus:ring-orange-500"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-[#EBDBC9] dark:border-[#273244]">
                        <button type="button" @click="createModalOpen = false"
                                class="px-3.5 py-1.5 rounded-xl border border-[#EBDBC9] dark:border-[#273244] text-xs font-semibold text-muted hover:bg-[#FFF5EB] dark:hover:bg-[#172033]">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit"
                                class="px-4 py-1.5 rounded-xl bg-orange-600 hover:bg-orange-700 text-white font-bold text-xs shadow-xs">
                            {{ __('Save Role') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════
             MODAL 2: EDIT ROLE
             ═══════════════════════════════════════════════════════════ --}}
        <div x-show="editModalOpen" style="display: none;"
             class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.away="editModalOpen = false"
                 class="bg-white dark:bg-[#111827] border border-[#EBDBC9] dark:border-[#273244] w-full max-w-md rounded-2xl p-6 shadow-xl space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-[#EBDBC9] dark:border-[#273244]">
                    <h3 class="text-base font-bold text-foreground dark:text-foreground">{{ __('Edit Role Details') }}</h3>
                    <button type="button" @click="editModalOpen = false" class="text-muted hover:text-foreground text-lg">&times;</button>
                </div>

                <form method="POST" :action="'/dashboard/admin/roles/' + editForm.id" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-bold text-muted dark:text-muted mb-1">{{ __('Role Name') }}</label>
                        <input type="text" name="name" x-model="editForm.name" :readonly="editForm.is_system" required
                               :class="editForm.is_system ? 'opacity-60 cursor-not-allowed bg-neutral-100 dark:bg-neutral-800' : ''"
                               class="w-full px-3 py-2 text-xs rounded-xl bg-[#FFF5EB]/60 dark:bg-[#172033]/60 border border-[#EBDBC9] dark:border-[#273244] focus:outline-none focus:ring-1 focus:ring-orange-500">
                        <template x-if="editForm.is_system">
                            <span class="text-[11px] text-amber-600 dark:text-amber-400 mt-0.5 block">
                                {{ __('System role names are immutable to protect application contracts.') }}
                            </span>
                        </template>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-muted dark:text-muted mb-1">{{ __('Description') }}</label>
                        <textarea name="description" x-model="editForm.description" rows="3"
                                  class="w-full px-3 py-2 text-xs rounded-xl bg-[#FFF5EB]/60 dark:bg-[#172033]/60 border border-[#EBDBC9] dark:border-[#273244] focus:outline-none focus:ring-1 focus:ring-orange-500"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-[#EBDBC9] dark:border-[#273244]">
                        <button type="button" @click="editModalOpen = false"
                                class="px-3.5 py-1.5 rounded-xl border border-[#EBDBC9] dark:border-[#273244] text-xs font-semibold text-muted hover:bg-[#FFF5EB] dark:hover:bg-[#172033]">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit"
                                class="px-4 py-1.5 rounded-xl bg-orange-600 hover:bg-orange-700 text-white font-bold text-xs shadow-xs">
                            {{ __('Update Role') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════
             MODAL 3: DELETE CONFIRMATION
             ═══════════════════════════════════════════════════════════ --}}
        <div x-show="deleteModalOpen" style="display: none;"
             class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.away="deleteModalOpen = false"
                 class="bg-white dark:bg-[#111827] border border-[#EBDBC9] dark:border-[#273244] w-full max-w-sm rounded-2xl p-6 shadow-xl space-y-4">
                <div class="flex items-center gap-3 text-rose-600">
                    <svg class="w-6 h-6 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
                    <h3 class="text-base font-bold text-foreground dark:text-foreground">{{ __('Delete Custom Role') }}</h3>
                </div>

                <p class="text-xs text-muted dark:text-dark-subtle leading-relaxed">
                    {{ __('Are you sure you want to delete role') }} <strong class="text-foreground dark:text-foreground" x-text="deleteForm.name"></strong>?
                    {{ __('This action cannot be undone and will detach all permission mappings.') }}
                </p>

                <form method="POST" :action="'/dashboard/admin/roles/' + deleteForm.id" class="flex items-center justify-end gap-2 pt-2">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="deleteModalOpen = false"
                            class="px-3.5 py-1.5 rounded-xl border border-[#EBDBC9] dark:border-[#273244] text-xs font-semibold text-muted hover:bg-[#FFF5EB] dark:hover:bg-[#172033]">
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit"
                            class="px-4 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-xs">
                        {{ __('Confirm Delete') }}
                    </button>
                </form>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
