@props([
    'location', // An object/array containing the location hierarchy
    'showLocal' => true,
    'icon' => true,
])

@php
    $parts = [];
    $localParts = [];

    if (isset($location['woreda'])) {
        $parts[] = $location['woreda']['name'] ?? '';
        $localParts[] = $location['woreda']['local_name'] ?? '';
    }
    
    if (isset($location['zone'])) {
        $parts[] = $location['zone']['name'] ?? '';
        $localParts[] = $location['zone']['local_name'] ?? '';
    }
    
    if (isset($location['region'])) {
        $parts[] = $location['region']['name'] ?? '';
        $localParts[] = $location['region']['local_name'] ?? '';
    }

    $parts = array_filter($parts);
    $localParts = array_filter($localParts);
    
    $primaryText = implode(', ', $parts) ?: 'Unknown Location';
    $secondaryText = implode(' / ', $localParts);
@endphp

<div {{ $attributes->merge(['class' => 'flex items-start gap-1.5']) }}>
    @if($icon)
        <svg class="w-4 h-4 text-muted mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
        </svg>
    @endif
    
    <div>
        <span class="text-sm font-medium text-muted dark:text-muted">
            {{ $primaryText }}
        </span>
        @if($showLocal && $secondaryText)
            <span class="text-xs text-muted dark:text-muted ml-1">
                ({{ $secondaryText }})
            </span>
        @endif
    </div>
</div>
