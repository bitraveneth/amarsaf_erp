@props([
    'method' => 'GET',
    'action' => null,
])

<form
    method="{{ $method }}"
    @if($action) action="{{ $action }}" @endif
    {{ $attributes->merge(['class' => 'erp-filter-panel']) }}
>
    {{ $slot }}
</form>
