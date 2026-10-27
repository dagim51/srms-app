<x-layouts.dashboard>
    <x-slot name="page-title">{{ __('Availability & Calendar') }}</x-slot>
    <x-slot name="breadcrumb">
        <a href="{{ route('dashboard') }}" class="hover:text-primary-600 transition-colors">{{ __('Dashboard') }}</a>
        <span>/</span>
        <a href="{{ route('dashboard.accommodations.index') }}" class="hover:text-primary-600 transition-colors">{{ __('Hosting') }}</a>
        <span>/</span>
        <a href="{{ route('dashboard.accommodations.show', $accommodation) }}" class="hover:text-primary-600 transition-colors">{{ __('Manage') }}</a>
        <span>/</span>
        <span class="text-muted dark:text-white font-medium">{{ __('Calendar') }}</span>
    </x-slot>

    @php
        $daysInMonth = $currentDate->daysInMonth;
        $firstDayOfWeek = $currentDate->copy()->startOfMonth()->dayOfWeek; // 0 (Sun) - 6 (Sat)
        
        $prevMonth = $currentDate->copy()->subMonth();
        $nextMonth = $currentDate->copy()->addMonth();
    @endphp

    <div x-data="{
        showSyncModal: false,
        copied: false,
        copyFeedUrl() {
            navigator.clipboard.writeText('{{ $exportUrl }}');
            this.copied = true;
            setTimeout(() => this.copied = false, 2500);
        }
    }" class="max-w-6xl mx-auto page-enter space-y-6">
        <x-page-header
            title="Availability & Calendar"
            description="Manage reservation schedules, night blocks, dynamic seasonal pricing, and external iCal synchronization (SRS §57)."
            icon="calendar"
            badge="Calendar">
            <x-slot name="actions">
                <button @click="showSyncModal = true" class="btn btn-outline flex items-center gap-2">
                    <svg class="w-4 h-4 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>{{ __('Connect External Calendar') }}</span>
                </button>
            </x-slot>
        </x-page-header>

        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 text-emerald-800 text-sm font-medium border border-emerald-200 flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="p-4 rounded-xl bg-rose-50 text-rose-800 text-sm font-medium border border-rose-200 flex items-center gap-2">
                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif
        @if($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 text-rose-800 text-sm font-medium border border-rose-200">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            {{-- Left Column: Calendar UI & Sync Status --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="card bg-white dark:bg-[#1E293B] border border-warm dark:border-[#273244] rounded-2xl overflow-hidden shadow-sm">
                    <div class="p-6 border-b border-warm dark:border-[#273244] flex items-center justify-between">
                        <div>
                            <h2 class="text-xl font-bold font-display text-muted dark:text-white">{{ $accommodation->property->title }}</h2>
                            <p class="text-muted dark:text-dark-subtle text-sm">{{ __('Availability Calendar & External Bookings') }}</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <a href="{{ route('dashboard.accommodations.calendar', ['accommodation' => $accommodation, 'month' => $prevMonth->month, 'year' => $prevMonth->year]) }}" class="p-2 rounded-xl hover:bg-surface-subtle dark:hover:bg-surface transition text-muted dark:text-dark-subtle border border-warm dark:border-[#273244]">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                            </a>
                            <span class="font-bold text-muted dark:text-white w-32 text-center text-sm sm:text-base">{{ $currentDate->format('F Y') }}</span>
                            <a href="{{ route('dashboard.accommodations.calendar', ['accommodation' => $accommodation, 'month' => $nextMonth->month, 'year' => $nextMonth->year]) }}" class="p-2 rounded-xl hover:bg-surface-subtle dark:hover:bg-surface transition text-muted dark:text-dark-subtle border border-warm dark:border-[#273244]">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                            </a>
                        </div>
                    </div>

                    {{-- Calendar Legend --}}
                    <div class="px-6 py-3 bg-[#FFF5EB]/30 dark:bg-[#172033]/40 border-b border-warm dark:border-[#273244] flex flex-wrap items-center gap-4 text-xs font-medium text-muted dark:text-dark-subtle">
                        <div class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded bg-white dark:bg-[#1E293B] border border-warm dark:border-[#273244]"></span>
                            <span>{{ __('Available') }}</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded bg-indigo-500"></span>
                            <span>{{ __('External Sync / Reservation (iCal)') }}</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded bg-rose-500"></span>
                            <span>{{ __('Manual Block') }}</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded bg-primary-500"></span>
                            <span>{{ __('Today') }}</span>
                        </div>
                    </div>

                    <div class="p-6">
                        <div class="grid grid-cols-7 gap-1 text-center mb-2">
                            @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                                <div class="text-xs font-bold uppercase tracking-wider text-muted dark:text-dark-subtle py-2">{{ __($day) }}</div>
                            @endforeach
                        </div>
                        <div class="grid grid-cols-7 gap-2">
                            {{-- Empty cells for start of month --}}
                            @for($i = 0; $i < $firstDayOfWeek; $i++)
                                <div class="h-24 rounded-xl border border-dashed border-warm dark:border-[#273244] bg-surface/40 dark:bg-surface/10"></div>
                            @endfor

                            {{-- Days --}}
                            @for($day = 1; $day <= $daysInMonth; $day++)
                                @php
                                    $dateStr = sprintf('%04d-%02d-%02d', $currentDate->year, $currentDate->month, $day);
                                    $isPast = \Carbon\Carbon::parse($dateStr)->isPast() && !\Carbon\Carbon::parse($dateStr)->isToday();
                                    $isToday = \Carbon\Carbon::parse($dateStr)->isToday();
                                    
                                    // Check if blocked
                                    $blockInfo = null;
                                    foreach($blocks as $block) {
                                        if($dateStr >= $block->start_date && $dateStr <= $block->end_date) {
                                            $blockInfo = $block;
                                            break;
                                        }
                                    }
                                    
                                    $bgClass = 'bg-white dark:bg-[#1E293B] border-warm dark:border-[#273244]';
                                    if($isPast) $bgClass = 'bg-surface dark:bg-surface/40 border-warm dark:border-[#273244] opacity-50';
                                    if($isToday && !$blockInfo) $bgClass = 'bg-primary-50/70 dark:bg-primary-900/10 border-primary-300 dark:border-primary-700 ring-1 ring-primary-400';
                                    
                                    if($blockInfo) {
                                        if($blockInfo->type === 'external_reservation') {
                                            $bgClass = 'bg-indigo-50/90 dark:bg-indigo-950/40 border-indigo-200 dark:border-indigo-800';
                                        } else {
                                            $bgClass = 'bg-rose-50/90 dark:bg-rose-950/40 border-rose-200 dark:border-rose-800';
                                        }
                                    }
                                @endphp
                                
                                <div class="h-24 rounded-xl border {{ $bgClass }} p-2 relative group transition-all flex flex-col justify-between">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-bold {{ $isToday ? 'text-primary-600 dark:text-primary-400' : 'text-muted dark:text-dark-subtle' }}">
                                            {{ $day }}
                                        </span>
                                        @if($blockInfo && $blockInfo->type === 'external_reservation')
                                            <span class="w-2 h-2 rounded-full bg-indigo-500" title="{{ __('Imported via iCal') }}"></span>
                                        @endif
                                    </div>
                                    
                                    @if($blockInfo)
                                        <div class="mt-1">
                                            @if($blockInfo->type === 'external_reservation')
                                                <div class="text-[11px] font-bold text-indigo-700 dark:text-indigo-300 leading-tight bg-indigo-100 dark:bg-indigo-900/60 px-1.5 py-0.5 rounded truncate" title="{{ $blockInfo->reason ?? __('External Reservation') }}">
                                                    {{ $blockInfo->reason ?: __('External Res.') }}
                                                </div>
                                            @else
                                                <div class="text-[11px] font-bold text-rose-700 dark:text-rose-300 leading-tight bg-rose-100 dark:bg-rose-900/60 px-1.5 py-0.5 rounded truncate" title="{{ $blockInfo->reason ?? __('Blocked') }}">
                                                    {{ $blockInfo->reason ?: __('Blocked') }}
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>
                
                {{-- Active Blocks & Reservations List --}}
                <div class="card bg-white dark:bg-[#1E293B] border border-warm dark:border-[#273244] rounded-2xl overflow-hidden shadow-sm">
                    <div class="p-6 border-b border-warm dark:border-[#273244] flex items-center justify-between">
                        <h3 class="font-bold text-muted dark:text-white">{{ __('Active Blocks & Reservations') }} ({{ $currentDate->format('F Y') }})</h3>
                        <span class="text-xs text-muted dark:text-dark-subtle font-medium">{{ count($blocks) }} {{ __('item(s)') }}</span>
                    </div>
                    <div class="divide-y divide-warm dark:divide-[#273244]">
                        @forelse($blocks as $block)
                            <div class="p-4 flex items-center justify-between hover:bg-surface dark:hover:bg-surface/50 transition">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 {{ $block->type === 'external_reservation' ? 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400' : 'bg-rose-100 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400' }}">
                                        @if($block->type === 'external_reservation')
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                        @else
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-muted dark:text-white">
                                            {{ \Carbon\Carbon::parse($block->start_date)->format('M d') }} - {{ \Carbon\Carbon::parse($block->end_date)->format('M d, Y') }}
                                        </p>
                                        <p class="text-xs text-muted dark:text-dark-subtle">
                                            <span class="font-semibold">{{ $block->type === 'external_reservation' ? __('External Reservation') : __('Manual Block') }}</span>
                                            @if($block->reason) • {{ $block->reason }} @endif
                                        </p>
                                    </div>
                                </div>
                                <form action="{{ route('dashboard.accommodations.calendar.unblock', [$accommodation, $block]) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-rose-500 hover:text-rose-700 text-sm font-medium px-3 py-1 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-900/20 transition" onclick="return confirm('{{ __('Are you sure you want to unblock these dates?') }}');">
                                        {{ __('Unblock') }}
                                    </button>
                                </form>
                            </div>
                        @empty
                            <div class="p-8 text-center text-muted dark:text-dark-subtle text-sm">
                                {{ __('No blocks or reservations for this month.') }}
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
            
            {{-- Right Column: Controls & External Sync Manager --}}
            <div class="space-y-6">
                
                {{-- iCal Export Feed Card --}}
                <div class="card bg-white dark:bg-[#1E293B] border border-warm dark:border-[#273244] rounded-2xl overflow-hidden p-6 shadow-sm">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-9 h-9 rounded-xl bg-orange-100 dark:bg-orange-950/40 text-orange-600 dark:text-orange-400 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-muted dark:text-white">{{ __('Export iCal Feed') }}</h3>
                            <p class="text-xs text-muted dark:text-dark-subtle">{{ __('Sync SRMS bookings to external channels') }}</p>
                        </div>
                    </div>
                    
                    <p class="text-xs text-muted dark:text-dark-subtle leading-relaxed mb-4">
                        {{ __('Copy this secure iCal URL into Airbnb, Google Calendar, VRBO, or Booking.com to automatically prevent overlapping bookings on other platforms.') }}
                    </p>

                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <input type="text" readonly value="{{ $exportUrl }}" class="form-input text-xs w-full bg-surface-subtle dark:bg-[#172033] font-mono select-all rounded-xl border-warm dark:border-[#273244]" id="export-feed-input">
                            <button @click="copyFeedUrl()" type="button" class="btn btn-secondary text-xs shrink-0 px-3 py-2 flex items-center gap-1.5" title="{{ __('Copy URL to Clipboard') }}">
                                <template x-if="!copied">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                                        {{ __('Copy') }}
                                    </span>
                                </template>
                                <template x-if="copied">
                                    <span class="flex items-center gap-1 text-emerald-600 dark:text-emerald-400 font-semibold">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        {{ __('Copied!') }}
                                    </span>
                                </template>
                            </button>
                        </div>
                        <div class="flex items-center justify-between text-[11px] text-muted dark:text-dark-subtle pt-1">
                            <span class="inline-flex items-center gap-1">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                {{ __('RFC 5545 Valid Feed') }}
                            </span>
                            <a href="{{ str_replace(['https://', 'http://'], 'webcal://', $exportUrl) }}" class="text-primary-600 dark:text-primary-400 hover:underline">
                                {{ __('Subscribe via Webcal') }} &rarr;
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Connected External Feeds (Import) --}}
                <div class="card bg-white dark:bg-[#1E293B] border border-warm dark:border-[#273244] rounded-2xl overflow-hidden shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg bg-indigo-100 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            </div>
                            <h3 class="text-sm font-bold uppercase tracking-wider text-muted dark:text-white">{{ __('Import External Calendars') }}</h3>
                        </div>
                        <button @click="showSyncModal = true" class="text-xs text-primary-600 dark:text-primary-400 hover:underline font-bold">
                            + {{ __('Add') }}
                        </button>
                    </div>

                    <div class="space-y-3">
                        @forelse($calendarSyncs as $sync)
                            <div class="p-3 bg-surface-subtle dark:bg-[#172033] rounded-xl border border-warm dark:border-[#273244] space-y-2">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $sync->provider_name === 'airbnb' ? 'bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300' : ($sync->provider_name === 'google' ? 'bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300' : 'bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-300') }}">
                                            {{ $sync->provider_name }}
                                        </span>
                                        <h4 class="text-xs font-bold text-muted dark:text-white truncate max-w-[140px]">{{ $sync->name }}</h4>
                                    </div>
                                    <span class="text-[10px] px-1.5 py-0.5 rounded font-semibold {{ $sync->sync_status === 'active' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300' : 'bg-rose-100 text-rose-700 dark:bg-rose-950/50 dark:text-rose-300' }}">
                                        {{ ucfirst($sync->sync_status) }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between text-[11px] text-muted dark:text-dark-subtle">
                                    <span>
                                        {{ __('Synced') }}: {{ $sync->last_synced_at ? $sync->last_synced_at->diffForHumans() : __('Never') }}
                                        ({{ $sync->events_synced ?? 0 }} {{ __('events') }})
                                    </span>
                                </div>

                                @if($sync->last_error)
                                    <p class="text-[10px] text-rose-600 dark:text-rose-400 truncate" title="{{ $sync->last_error }}">
                                        {{ __('Error') }}: {{ $sync->last_error }}
                                    </p>
                                @endif

                                <div class="flex items-center justify-end gap-2 pt-1 border-t border-warm/60 dark:border-[#273244]/60">
                                    <form method="POST" action="{{ route('dashboard.accommodations.calendar.syncs.refresh', [$accommodation, $sync]) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-outline btn-xs text-[10px] py-1 px-2 flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                            {{ __('Sync Now') }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('dashboard.accommodations.calendar.syncs.destroy', [$accommodation, $sync]) }}" onsubmit="return confirm('{{ __('Disconnect this calendar feed? Imported reservations will be removed.') }}');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-ghost btn-xs text-rose-600 dark:text-rose-400 text-[10px] py-1 px-2">
                                            {{ __('Disconnect') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 px-2 border border-dashed border-warm dark:border-[#273244] rounded-xl text-muted dark:text-dark-subtle">
                                <p class="text-xs mb-2">{{ __('No external calendars connected.') }}</p>
                                <button @click="showSyncModal = true" type="button" class="btn btn-outline btn-xs text-xs">
                                    + {{ __('Connect Airbnb / Google') }}
                                </button>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Manual Block Form --}}
                <div class="card bg-white dark:bg-[#1E293B] border border-warm dark:border-[#273244] rounded-2xl overflow-hidden p-6 shadow-sm">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-muted dark:text-white mb-4">{{ __('Manual Date Blocking') }}</h3>
                    
                    <form action="{{ route('dashboard.accommodations.calendar.block', $accommodation) }}" method="POST" class="space-y-4">
                        @csrf
                        
                        <div>
                            <label class="block text-xs font-bold text-muted dark:text-white mb-1">{{ __('Start Date') }}</label>
                            <input type="date" name="start_date" required min="{{ date('Y-m-d') }}" class="form-input w-full text-sm rounded-xl">
                        </div>
                        
                        <div>
                            <label class="block text-xs font-bold text-muted dark:text-white mb-1">{{ __('End Date') }}</label>
                            <input type="date" name="end_date" required min="{{ date('Y-m-d') }}" class="form-input w-full text-sm rounded-xl">
                        </div>
                        
                        <div>
                            <label class="block text-xs font-bold text-muted dark:text-white mb-1">{{ __('Block Type') }}</label>
                            <select name="type" class="form-input w-full text-sm rounded-xl">
                                <option value="blocked">{{ __('Manual Block (Maintenance / Personal)') }}</option>
                                <option value="external_reservation">{{ __('External Reservation') }}</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-bold text-muted dark:text-white mb-1">{{ __('Reason / Note (Optional)') }}</label>
                            <input type="text" name="reason" placeholder="{{ __('e.g. Painting, Offline Booking...') }}" class="form-input w-full text-sm rounded-xl">
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-full shadow-md mt-2">
                            {{ __('Block Availability') }}
                        </button>
                    </form>
                </div>
            </div>
            
        </div>

        {{-- Connect External Calendar Modal --}}
        <div x-show="showSyncModal"
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
            <div @click.away="showSyncModal = false"
                 class="card max-w-lg w-full bg-white dark:bg-[#1E293B] rounded-2xl shadow-xl border border-warm dark:border-[#273244] overflow-hidden">
                <div class="p-6 border-b border-warm dark:border-[#273244] flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-orange-100 dark:bg-orange-950/40 text-orange-600 dark:text-orange-400 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-muted dark:text-white">{{ __('Connect External Calendar Feed') }}</h3>
                            <p class="text-xs text-muted dark:text-dark-subtle">{{ __('Support iCal / webcal formats (SRS §57)') }}</p>
                        </div>
                    </div>
                    <button @click="showSyncModal = false" class="text-muted dark:text-dark-subtle hover:text-slate-700 dark:hover:text-white text-lg">&times;</button>
                </div>
                
                <form method="POST" action="{{ route('dashboard.accommodations.calendar.syncs.store', $accommodation) }}" class="p-6 space-y-4">
                    @csrf
                    
                    <div>
                        <label class="block text-xs font-bold text-muted dark:text-white mb-1">{{ __('Calendar Provider') }} *</label>
                        <select name="provider_name" required class="form-input w-full text-sm rounded-xl">
                            <option value="airbnb">Airbnb</option>
                            <option value="google">Google Calendar</option>
                            <option value="vrbo">VRBO / HomeAway</option>
                            <option value="booking">Booking.com</option>
                            <option value="apple">Apple Calendar</option>
                            <option value="other">{{ __('Other iCal Feed') }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-muted dark:text-white mb-1">{{ __('Calendar / Feed Name') }} *</label>
                        <input type="text" name="name" required class="form-input w-full text-sm rounded-xl" placeholder="e.g. My Airbnb Studio Calendar">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-muted dark:text-white mb-1">{{ __('iCal Feed URL') }} *</label>
                        <input type="url" name="url" required class="form-input w-full text-sm rounded-xl font-mono text-xs" placeholder="https://www.airbnb.com/calendar/ical/....ics or webcal://...">
                        <p class="text-[11px] text-muted dark:text-dark-subtle mt-1.5 leading-relaxed">
                            {{ __('Paste the export .ics or webcal:// link from your external channel. Reservations will be imported and blocked immediately.') }}
                        </p>
                    </div>

                    <div class="bg-surface-subtle dark:bg-[#172033] p-3.5 rounded-xl border border-warm dark:border-[#273244] text-xs space-y-1 text-muted dark:text-dark-subtle">
                        <p class="font-bold text-slate-800 dark:text-slate-200">{{ __('Where to find your iCal link:') }}</p>
                        <ul class="list-disc pl-4 space-y-0.5 text-[11px]">
                            <li><strong>Airbnb:</strong> Listing &gt; Availability &gt; Connect calendars &gt; Export calendar</li>
                            <li><strong>Google Calendar:</strong> Settings &gt; Integrate calendar &gt; Secret address in iCal format</li>
                            <li><strong>VRBO:</strong> Calendars &gt; Import/Export &gt; Export calendar</li>
                        </ul>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-warm dark:border-[#273244]">
                        <button type="button" @click="showSyncModal = false" class="btn btn-secondary text-xs">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit" class="btn btn-primary text-xs shadow-md">
                            {{ __('Connect & Sync Now') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.dashboard>
