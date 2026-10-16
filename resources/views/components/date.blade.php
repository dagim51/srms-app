@props(['date', 'format' => 'M d, Y'])

@php
    $parsedDate = $date instanceof \Carbon\Carbon ? $date : \Carbon\Carbon::parse($date);
    $locale = app()->getLocale();
@endphp

<time datetime="{{ $parsedDate->toIso8601String() }}" {{ $attributes->merge(['class' => 'text-sm text-muted']) }}>
    {{ $parsedDate->locale($locale)->translatedFormat($format) }}
</time>
