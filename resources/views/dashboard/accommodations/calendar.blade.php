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

    <div class="max-w-6xl mx-auto page-enter space-y-6">
        <x-page-header
            title="Availability & Calendar"
            description="Manage reservation schedules, night blocks, dynamic seasonal pricing, and check-in availability."
            icon="calendar"
            badge="Calendar" />

        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 text-emerald-800 text-sm font-medium border border-emerald-200">
                {{ session('success') }}
            </div>
        @endif
        @if($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 text-rose-800 text-sm font-medium border border-rose-200">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            {{-- Left Column: Calendar UI --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-[#273244] rounded-2xl shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-warm dark:border-warm flex items-center justify-between">
                        <div>
                            <h2 class="text-xl font-bold font-display text-muted dark:text-white">{{ $accommodation->property->title }}</h2>
                            <p class="text-muted text-sm">{{ __('Availability Calendar') }}</p>
                        </div>
                        <div class="flex items-center gap-4">
                            <a href="{{ route('dashboard.accommodations.calendar', ['accommodation' => $accommodation, 'month' => $prevMonth->month, 'year' => $prevMonth->year]) }}" class="p-2 rounded-full hover:bg-surface-subtle dark:hover:bg-surface transition text-muted">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                            </a>
                            <span class="font-bold text-muted dark:text-white w-32 text-center">{{ $currentDate->format('F Y') }}</span>
                            <a href="{{ route('dashboard.accommodations.calendar', ['accommodation' => $accommodation, 'month' => $nextMonth->month, 'year' => $nextMonth->year]) }}" class="p-2 rounded-full hover:bg-surface-subtle dark:hover:bg-surface transition text-muted">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                            </a>
                        </div>
                    </div>

                    <div class="p-6">
                        <div class="grid grid-cols-7 gap-1 text-center mb-2">
                            @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                                <div class="text-xs font-bold uppercase tracking-wider text-muted py-2">{{ __($day) }}</div>
                            @endforeach
                        </div>
                        <div class="grid grid-cols-7 gap-2">
                            {{-- Empty cells for start of month --}}
                            @for($i = 0; $i < $firstDayOfWeek; $i++)
                                <div class="h-24 rounded-xl border border-dashed border-warm dark:border-warm/50 bg-surface/50 dark:bg-surface/20"></div>
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
                                    if($isPast) $bgClass = 'bg-surface dark:bg-surface/50 border-warm dark:border-warm opacity-50';
                                    if($isToday && !$blockInfo) $bgClass = 'bg-primary-50 dark:bg-primary-900/10 border-primary-200 dark:border-primary-800';
                                    
                                    if($blockInfo) {
                                        if($blockInfo->type === 'external_reservation') {
                                            $bgClass = 'bg-indigo-50 dark:bg-indigo-900/20 border-indigo-200 dark:border-indigo-800';
                                        } else {
                                            $bgClass = 'bg-rose-50 dark:bg-rose-900/20 border-rose-200 dark:border-rose-800';
                                        }
                                    }
                                @endphp
                                
                                <div class="h-24 rounded-xl border {{ $bgClass }} p-2 relative group transition-all">
                                    <span class="text-sm font-bold {{ $isToday ? 'text-primary-600 dark:text-primary-400' : 'text-muted dark:text-dark-subtle' }}">
                                        {{ $day }}
                                    </span>
                                    
                                    @if($blockInfo)
                                        <div class="mt-1">
                                            @if($blockInfo->type === 'external_reservation')
                                                <div class="text-xs font-bold text-indigo-700 dark:text-indigo-300 leading-tight bg-indigo-100 dark:bg-indigo-900/50 px-1.5 py-0.5 rounded w-full truncate">
                                                    {{ __('Reserved') }}
                                                </div>
                                            @else
                                                <div class="text-xs font-bold text-rose-700 dark:text-rose-300 leading-tight bg-rose-100 dark:bg-rose-900/50 px-1.5 py-0.5 rounded w-full truncate">
                                                    {{ __('Blocked') }}
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>
                
                {{-- Manage Blocks list --}}
                <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-[#273244] rounded-2xl shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-warm dark:border-warm">
                        <h3 class="font-bold text-muted dark:text-white">{{ __('Active Blocks & Reservations') }} ({{ $currentDate->format('F Y') }})</h3>
                    </div>
                    <div class="divide-y divide-warm dark:divide-dark-border">
                        @forelse($blocks as $block)
                            <div class="p-4 flex items-center justify-between hover:bg-surface dark:hover:bg-surface/50 transition">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center {{ $block->type === 'external_reservation' ? 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400' : 'bg-rose-100 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400' }}">
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
                                        <p class="text-xs text-muted">
                                            {{ $block->type === 'external_reservation' ? __('External Reservation') : __('Manual Block') }}
                                            @if($block->reason) • {{ $block->reason }} @endif
                                        </p>
                                    </div>
                                </div>
                                <form action="{{ route('dashboard.accommodations.calendar.unblock', [$accommodation, $block]) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-rose-500 hover:text-rose-700 text-sm font-medium" onclick="return confirm('{{ __('Are you sure you want to unblock these dates?') }}');">
                                        {{ __('Unblock') }}
                                    </button>
                                </form>
                            </div>
                        @empty
                            <div class="p-8 text-center text-muted text-sm">
                                {{ __('No blocks or reservations for this month.') }}
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
            
            {{-- Right Column: Controls --}}
            <div class="space-y-6">
                <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-[#273244] rounded-2xl shadow-sm overflow-hidden p-6">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-muted mb-4">{{ __('Block Dates') }}</h3>
                    
                    <form action="{{ route('dashboard.accommodations.calendar.block', $accommodation) }}" method="POST" class="space-y-4">
                        @csrf
                        
                        <div>
                            <label class="block text-sm font-semibold text-muted dark:text-dark-subtle mb-1">{{ __('Start Date') }}</label>
                            <input type="date" name="start_date" required min="{{ date('Y-m-d') }}" class="form-input w-full rounded-xl">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-muted dark:text-dark-subtle mb-1">{{ __('End Date') }}</label>
                            <input type="date" name="end_date" required min="{{ date('Y-m-d') }}" class="form-input w-full rounded-xl">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-muted dark:text-dark-subtle mb-1">{{ __('Block Type') }}</label>
                            <select name="type" class="form-input w-full rounded-xl">
                                <option value="blocked">{{ __('Manual Block (Maintenance/Personal)') }}</option>
                                <option value="external_reservation">{{ __('External Reservation') }}</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-muted dark:text-dark-subtle mb-1">{{ __('Reason / Note (Optional)') }}</label>
                            <input type="text" name="reason" placeholder="{{ __('e.g. Painting, Airbnb booking...') }}" class="form-input w-full rounded-xl">
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-full shadow-md mt-2">
                            {{ __('Block Availability') }}
                        </button>
                    </form>
                </div>
                
                <div class="bg-white dark:bg-[#1E293B] border border-warm dark:border-[#273244] rounded-2xl shadow-sm overflow-hidden p-6">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-muted mb-4">{{ __('Calendar Settings') }}</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-surface dark:bg-surface/30 border border-warm dark:border-warm rounded-xl">
                            <span class="text-muted dark:text-muted text-sm">{{ __('Sync iCal') }}</span>
                            <button class="btn btn-outline btn-sm">{{ __('Configure') }}</button>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-surface dark:bg-surface/30 border border-warm dark:border-warm rounded-xl">
                            <span class="text-muted dark:text-muted text-sm">{{ __('Advance Notice') }}</span>
                            <span class="font-bold text-muted dark:text-white">1 {{ __('Day') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</x-layouts.dashboard>
