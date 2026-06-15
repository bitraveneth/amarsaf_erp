@props([
    'class' => '',
])

<div class="overflow-x-auto">
    <table {{ $attributes->merge(['class' => trim('erp-dash-statement__table w-full ' . $class)]) }}>
        {{ $slot }}
    </table>
</div>
