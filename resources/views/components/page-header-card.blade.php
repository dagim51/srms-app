@props([
    'title' => null,
    'description' => null,
    'icon' => null,
    'badge' => null,
    'actions' => null,
])

<x-page-header
    :title="$title"
    :description="$description"
    :icon="$icon"
    :badge="$badge"
    :attributes="$attributes"
>
    @if(isset($actions) && $actions->isNotEmpty())
        <x-slot:actions>
            {{ $actions }}
        </x-slot:actions>
    @elseif(isset($slot) && $slot->isNotEmpty())
        {{ $slot }}
    @endif
</x-page-header>
