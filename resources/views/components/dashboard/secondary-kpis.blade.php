@props([
    'todayOrderCount' => 0,
    'todayProductionQty' => 0,
    'monthReturnCount' => 0,
    'agentCount' => 0,
])

@php
    $items = [
        [
            'label' => 'Orders today',
            'value' => number_format($todayOrderCount ?? 0),
            'caption' => 'Deliveries scheduled',
            'href' => route('admin.orders.index'),
            'tone' => 'neutral',
        ],
        [
            'label' => 'Production today',
            'value' => number_format((float) ($todayProductionQty ?? 0), 0),
            'caption' => 'Approved quantity',
            'href' => route('admin.production.index'),
            'tone' => 'neutral',
        ],
        [
            'label' => 'Returns MTD',
            'value' => number_format($monthReturnCount ?? 0),
            'caption' => 'Return orders',
            'href' => route('admin.orders.index', ['type' => 'return']),
            'tone' => ($monthReturnCount ?? 0) > 0 ? 'error' : 'neutral',
        ],
        [
            'label' => 'Active agents',
            'value' => number_format($agentCount ?? 0),
            'caption' => 'Selling partners',
            'href' => route('admin.agents.index'),
            'tone' => 'neutral',
        ],
    ];
@endphp

<div class="dash-ops-strip">
    @foreach($items as $item)
        <a href="{{ $item['href'] }}" class="dash-ops-item">
            <p class="text-[11px] font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">{{ $item['label'] }}</p>
            <p @class([
                'mt-1 text-lg font-bold tabular-nums text-gray-900 dark:text-white',
                'text-error-600 dark:text-error-500' => $item['tone'] === 'error',
            ])>{{ $item['value'] }}</p>
            <p class="mt-0.5 text-[11px] text-gray-400 dark:text-gray-500">{{ $item['caption'] }}</p>
        </a>
    @endforeach
</div>
