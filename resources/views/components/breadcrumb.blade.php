@props([
    'items' => [],
])

<nav aria-label="Breadcrumb" {{ $attributes->merge(['class' => 'breadcrumb-nav']) }}>
    @if(!empty($items))
        @foreach($items as $index => $item)
            @if(!$loop->last && !empty($item['url']))
                <a href="{{ $item['url'] }}" class="breadcrumb-link">{{ $item['label'] }}</a>
                <svg class="w-3 h-3 breadcrumb-separator" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            @else
                <span class="breadcrumb-current">{{ $item['label'] }}</span>
            @endif
        @endforeach
    @else
        {{ $slot }}
    @endif
</nav>
