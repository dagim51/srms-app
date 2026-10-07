@props(['amount', 'currency' => 'ETB', 'decimals' => 0])

<span {{ $attributes->merge(['class' => 'font-display font-semibold']) }}>
    {{ $currency }} {{ number_format($amount, $decimals) }}
</span>


