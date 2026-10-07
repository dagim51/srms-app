@props([
    'listing' => null,
    'score' => null,
    'breakdown' => null,
    'compact' => false,
    'showRecommendations' => true,
])

@php
    $scoreVal = $score ?? ($listing ? $listing->quality_score : 0);
    $items = $breakdown ?? ($listing ? $listing->quality_breakdown : []);
    
    if ($scoreVal >= 80) {
        $tier = 'Excellent';
        $colorClass = 'text-emerald-600 dark:text-emerald-400';
        $bgClass = 'bg-emerald-500';
        $badgeClass = 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800/40';
    } elseif ($scoreVal >= 50) {
        $tier = 'Good';
        $colorClass = 'text-amber-600 dark:text-amber-400';
        $bgClass = 'bg-amber-500';
        $badgeClass = 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400 border-amber-200 dark:border-amber-800/40';
    } else {
        $tier = 'Needs Work';
        $colorClass = 'text-rose-600 dark:text-rose-400';
        $bgClass = 'bg-rose-500';
        $badgeClass = 'bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-400 border-rose-200 dark:border-rose-800/40';
    }
@endphp

@if($compact)
    <div class="inline-flex items-center gap-2" title="Listing Quality Score: {{ $scoreVal }}%">
        <div class="w-12 bg-gray-200 dark:bg-gray-700 h-1.5 rounded-full overflow-hidden">
            <div class="{{ $bgClass }} h-full transition-all duration-300" style="width: {{ $scoreVal }}%"></div>
        </div>
        <span class="text-xs font-semibold {{ $colorClass }}">{{ $scoreVal }}%</span>
    </div>
@else
    <div x-data="{ expanded: false }" class="bg-white dark:bg-[#1E293B] border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm transition-all">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="relative w-12 h-12 flex items-center justify-center">
                    <svg class="w-12 h-12 transform -rotate-90" viewBox="0 0 36 36">
                        <path class="text-gray-200 dark:text-gray-700" stroke-width="3.5" stroke="currentColor" fill="none"
                              d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        <path class="{{ $colorClass }}" stroke-dasharray="{{ $scoreVal }}, 100" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none"
                              d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    </svg>
                    <span class="absolute font-display font-bold text-xs text-gray-900 dark:text-white">{{ $scoreVal }}%</span>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Listing Quality Score</h4>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border {{ $badgeClass }}">
                            {{ $tier }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Higher quality listings receive up to 3.8× more inquiries.
                    </p>
                </div>
            </div>

            @if($showRecommendations && count($items) > 0)
                <button type="button" @click="expanded = !expanded" class="text-xs text-primary-600 dark:text-primary-400 hover:underline flex items-center gap-1 font-medium focus:outline-none">
                    <span x-text="expanded ? 'Hide Details' : 'View Checklist'">View Checklist</span>
                    <svg class="w-4 h-4 transform transition-transform" :class="{ 'rotate-180': expanded }" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                </button>
            @endif
        </div>

        @if($showRecommendations && count($items) > 0)
            <div x-show="expanded" x-collapse x-cloak class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800 space-y-2.5">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Quality Checklist & Tips</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs">
                    @foreach($items as $item)
                        <div class="flex items-start gap-2 p-2 rounded-lg {{ $item['passed'] ? 'bg-emerald-50/50 dark:bg-emerald-950/20' : 'bg-gray-50 dark:bg-gray-800/50' }}">
                            @if($item['passed'])
                                <svg class="w-4 h-4 text-emerald-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            @else
                                <svg class="w-4 h-4 text-gray-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01" /></svg>
                            @endif
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium {{ $item['passed'] ? 'text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-300' }}">
                                        {{ $item['label'] }}
                                    </span>
                                    <span class="text-[10px] {{ $item['passed'] ? 'text-emerald-600 dark:text-emerald-400 font-bold' : 'text-gray-400' }}">
                                        +{{ $item['points'] }}%
                                    </span>
                                </div>
                                @if(!$item['passed'] && !empty($item['tip']))
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5 leading-relaxed">
                                        {{ $item['tip'] }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endif
