@props(['amount', 'currency' => null, 'decimals' => 0])

@php
    $locale = app()->getLocale();
    $currency = $currency ?? ($locale === 'am' ? 'ብር' : 'ETB');
@endphp

<span {{ $attributes->merge(['class' => 'font-display font-semibold']) }}>
    @if($locale === 'am')
        {{ number_format($amount, $decimals) }} {{ $currency }}
    @else
        {{ $currency }} {{ number_format($amount, $decimals) }}
    @endif
</span>
