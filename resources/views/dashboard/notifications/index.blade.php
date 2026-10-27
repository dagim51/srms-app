<x-layouts.dashboard>
    <x-slot:title>{{ __('Notification Center - SRMS') }}</x-slot:title>

    <div class="space-y-6">
        <x-page-header
            title="Notifications"
            description="Real-time transactional audit updates, escrow milestones, Diaspora inquiries, and security events."
            icon="bell"
            badge="{{ $counts['unread'] }} Unread">
            <x-slot:actions>
                @if($counts['unread'] > 0)
                    <form method="POST" action="{{ route('dashboard.notifications.mark-all-read') }}">
                        @csrf
                        <button type="submit" class="btn btn-secondary py-2.5 px-4 text-xs font-bold border-slate-200 dark:border-slate-700 hover:border-orange-500/50 flex items-center gap-2 cursor-pointer transition-all">
                            <svg class="w-4 h-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>{{ __('Mark All as Read') }}</span>
                        </button>
                    </form>
                @endif

                <a href="{{ route('dashboard.preferences') }}" class="btn btn-secondary py-2.5 px-4 text-xs font-bold border-slate-200 dark:border-slate-700 hover:border-orange-500/50 flex items-center gap-2 cursor-pointer transition-all">
                    <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 010 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.6 6.6 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 010-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>{{ __('Preferences') }}</span>
                </a>
            </x-slot:actions>
        </x-page-header>

        {{-- Filter Tabs Bar --}}
        <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
            <a href="{{ route('dashboard.notifications.index', ['filter' => 'all']) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 flex-shrink-0 {{ $currentFilter === 'all' ? 'bg-[#C2410C] text-white shadow-md shadow-orange-500/25' : 'bg-white dark:bg-[#1E293B] border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:border-orange-500/40' }}">
                <span>{{ __('All') }}</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $currentFilter === 'all' ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-500' }}">
                    {{ $counts['all'] }}
                </span>
            </a>

            <a href="{{ route('dashboard.notifications.index', ['filter' => 'unread']) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 flex-shrink-0 {{ $currentFilter === 'unread' ? 'bg-[#C2410C] text-white shadow-md shadow-orange-500/25' : 'bg-white dark:bg-[#1E293B] border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:border-orange-500/40' }}">
                <span>{{ __('Unread') }}</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $currentFilter === 'unread' ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-500' }}">
                    {{ $counts['unread'] }}
                </span>
            </a>

            <a href="{{ route('dashboard.notifications.index', ['filter' => 'deals']) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 flex-shrink-0 {{ $currentFilter === 'deals' ? 'bg-[#C2410C] text-white shadow-md shadow-orange-500/25' : 'bg-white dark:bg-[#1E293B] border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:border-orange-500/40' }}">
                <span>{{ __('Deals & Escrow') }}</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $currentFilter === 'deals' ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-500' }}">
                    {{ $counts['deals'] }}
                </span>
            </a>

            <a href="{{ route('dashboard.notifications.index', ['filter' => 'inquiries']) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 flex-shrink-0 {{ $currentFilter === 'inquiries' ? 'bg-[#C2410C] text-white shadow-md shadow-orange-500/25' : 'bg-white dark:bg-[#1E293B] border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:border-orange-500/40' }}">
                <span>{{ __('Inquiries & Leads') }}</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $currentFilter === 'inquiries' ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-500' }}">
                    {{ $counts['inquiries'] }}
                </span>
            </a>

            <a href="{{ route('dashboard.notifications.index', ['filter' => 'system']) }}"
               class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 flex-shrink-0 {{ $currentFilter === 'system' ? 'bg-[#C2410C] text-white shadow-md shadow-orange-500/25' : 'bg-white dark:bg-[#1E293B] border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:border-orange-500/40' }}">
                <span>{{ __('System & Security') }}</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $currentFilter === 'system' ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-500' }}">
                    {{ $counts['system'] }}
                </span>
            </a>
        </div>

        {{-- Notifications List Card Container --}}
        <div class="card overflow-hidden">
            @forelse($notifications as $n)
                @php
                    $isUnread = is_null($n->read_at);
                    $type = $n->type ?? '';
                    $title = $n->data['title'] ?? __('Notification');
                    $message = $n->data['message'] ?? '';
                    $actionUrl = $n->data['action_url'] ?? null;
                    $time = $n->created_at ? $n->created_at->diffForHumans() : __('Recently');
                    
                    $isDeal = str_contains($type, 'Milestone') || str_contains($type, 'Escrow') || str_contains($type, 'Deal') || str_contains($type, 'Listing');
                    $isInquiry = str_contains($type, 'Inquiry') || str_contains($type, 'Message') || str_contains($type, 'Lead');
                @endphp
                <div class="p-5 sm:p-6 border-b border-slate-100 dark:border-slate-800/80 last:border-0 flex flex-col sm:flex-row sm:items-start justify-between gap-4 transition-colors {{ $isUnread ? 'bg-orange-500/[0.02] dark:bg-orange-500/[0.03]' : '' }}">
                    <div class="flex items-start gap-4 flex-1">
                        {{-- Icon Avatar --}}
                        <div class="w-10 h-10 rounded-2xl flex items-center justify-center flex-shrink-0 mt-0.5 {{ $isDeal ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' : ($isInquiry ? 'bg-orange-500/10 text-orange-600 dark:text-orange-400 border border-orange-500/20' : 'bg-sky-500/10 text-sky-600 dark:text-sky-400 border border-sky-500/20') }}">
                            @if($isDeal)
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            @elseif($isInquiry)
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a.75.75 0 01-.734-.993l.613-2.044A8.52 8.52 0 013 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" /></svg>
                            @else
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" /></svg>
                            @endif
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 class="font-display font-extrabold text-sm sm:text-base text-slate-900 dark:text-white">
                                    {{ $title }}
                                </h3>
                                @if($isUnread)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-orange-500 text-white animate-pulse">
                                        {{ __('New') }}
                                    </span>
                                @endif
                                <span class="text-xs text-slate-400 font-mono">
                                    &bull; {{ $time }}
                                </span>
                            </div>

                            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-1 leading-relaxed">
                                {{ $message }}
                            </p>

                            @if($actionUrl)
                                <div class="mt-2.5">
                                    <a href="{{ $actionUrl }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-orange-600 dark:text-orange-400 hover:underline">
                                        <span>{{ __('View Audit Record') }}</span>
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-2 self-end sm:self-start flex-shrink-0 pt-2 sm:pt-0">
                        @if($isUnread)
                            <form method="POST" action="{{ route('dashboard.notifications.read', $n->id) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="p-2 rounded-xl text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950/30 transition-colors cursor-pointer" title="{{ __('Mark as Read') }}">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                </button>
                            </form>
                        @endif

                        <form method="POST" action="{{ route('dashboard.notifications.destroy', $n->id) }}" onsubmit="return confirm('{{ __('Remove this notification?') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-colors cursor-pointer" title="{{ __('Delete') }}">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="py-16 px-6 text-center">
                    <div class="w-16 h-16 rounded-3xl bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="font-display font-extrabold text-base sm:text-lg text-slate-900 dark:text-white">
                        {{ __('All Caught Up!') }}
                    </h3>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">
                        {{ __('There are no notifications matching the selected filter. We will automatically alert you when new escrow or lead events occur.') }}
                    </p>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($notifications->hasPages())
            <div class="pt-2">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</x-layouts.dashboard>
