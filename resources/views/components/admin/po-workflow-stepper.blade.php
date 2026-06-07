@props([
    'step' => 1,
    'grnInProgress' => false,
])

<x-admin.order-workflow
    type="purchase"
    :step="$step"
    :in-progress="$grnInProgress"
    variant="mini"
    {{ $attributes }}
/>
