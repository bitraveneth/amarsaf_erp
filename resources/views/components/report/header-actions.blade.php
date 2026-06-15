@props([
    'rangeAction' => null,
    'range' => 'month',
    'from' => null,
    'to' => null,
    'rangeOptions' => [],
    'periodLabel' => null,
    'asOfAction' => null,
    'asOfName' => 'as_of',
    'asOf' => null,
    'asOfLabel' => 'As of',
])

<div {{ $attributes->merge(['class' => 'flex flex-wrap items-center gap-2 print:hidden']) }}>
    @if($rangeAction)
        <x-dashboard.period-filter
            variant="compact"
            :action="$rangeAction"
            :range="$range"
            :from="$from"
            :to="$to"
            :range-options="$rangeOptions"
            :period-label="$periodLabel"
        />
    @endif

    @if($asOfAction)
        <x-report.as-of-filter
            :action="$asOfAction"
            :name="$asOfName"
            :value="$asOf"
            :label="$asOfLabel"
        />
    @endif

    {{ $slot }}
</div>
