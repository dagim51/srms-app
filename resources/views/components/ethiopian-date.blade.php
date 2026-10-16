@props([
    'date',
    'dual' => false,
])

@php
    $formatted = \App\Support\EthiopianCalendar::format($date, app()->getLocale(), (bool)$dual);
    $iso = $date ? (\Carbon\Carbon::parse($date)->toISOString()) : null;
    $greg = $date ? (\Carbon\Carbon::parse($date)->format('M j, Y')) : '';
@endphp

@if($date)
    <time datetime="{{ $iso }}" {{ $attributes->merge(['class' => 'inline-flex items-center text-inherit font-inherit']) }} title="{{ $greg }}">
        {{ $formatted }}
    </time>
@endif
