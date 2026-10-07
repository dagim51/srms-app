@props(['date', 'format' => 'M d, Y'])

@php
    $parsedDate = $date instanceof \Carbon\Carbon ? $date : \Carbon\Carbon::parse($date);
@endphp

<time datetime="{{ $parsedDate->toIso8601String() }}" {{ $attributes->merge(['class' => 'text-sm text-gray-500']) }}>
    {{ $parsedDate->format($format) }}
</time>


