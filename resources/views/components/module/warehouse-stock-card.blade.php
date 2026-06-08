@props([
    'name',
    'tone' => 'brand',
    'icon' => 'production',
    'href' => null,
    'totalQty' => 0,
    'finishedQty' => 0,
    'rawQty' => 0,
    'otherQty' => 0,
    'finishedPct' => 0,
    'rawPct' => 0,
    'otherPct' => 0,
    'filterAware' => true,
    'materialsHref' => null,
])

@php
    $total = (float) $totalQty;
    $finished = (float) $finishedQty;
    $raw = (float) $rawQty;
    $other = (float) $otherQty;
    $hasMix = $total > 0 && ($finishedPct + $rawPct + $otherPct) > 0;
    $tag = $attributes->merge([
        'class' => 'dash-snapshot-metric dash-snapshot-metric--' . $tone . ' wh-stock-metric group flex h-full w-full flex-col',
    ]);
@endphp

<div {{ $tag }}>
    @if($href)
        <a href="{{ $href }}" class="wh-stock-metric__body">
    @else
        <div class="wh-stock-metric__body">
    @endif
        <div class="dash-snapshot-metric-head">
            <p class="dash-snapshot-metric-label">{{ $name }}</p>
            <span class="dash-snapshot-icon dash-snapshot-icon--{{ $tone }}">
                @switch($icon)
                    @case('delivery')
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a49.902 49.902 0 0 0-2.654-9.874A3.75 3.75 0 0 0 17.25 6H9.75a3.75 3.75 0 0 0-3.548 2.524A49.902 49.902 0 0 0 3.506 18.376c-.039.62.469 1.124 1.09 1.124H8.25Z" />
                        </svg>
                        @break
                    @case('orders')
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                        </svg>
                        @break
                    @case('returns')
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                        </svg>
                        @break
                    @default
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008H18V10.5Zm0 3h.008v.008H18V13.5Zm0 3h.008v.008H18V16.5Z" />
                        </svg>
                @endswitch
            </span>
        </div>

        @if($filterAware)
            <p class="dash-snapshot-metric-value wh-stock-metric__value"
               x-text="(() => {
                   const qty = $root.stockFilter === 'finished' ? {{ $finished }}
                       : ($root.stockFilter === 'raw' ? {{ $raw }} : {{ $total }});
                   return qty.toLocaleString();
               })()"></p>
            <p class="dash-snapshot-metric-caption wh-stock-metric__caption"
               x-text="$root.stockFilter === 'finished' ? 'Finished goods on hand'
                   : ($root.stockFilter === 'raw' ? 'Raw materials on hand' : 'Available units on hand')"></p>
        @else
            <p class="dash-snapshot-metric-value wh-stock-metric__value">{{ number_format($total, 0) }}</p>
            <p class="dash-snapshot-metric-caption wh-stock-metric__caption">Available units on hand</p>
        @endif
    @if($href)
        </a>
    @else
        </div>
    @endif

    <div class="wh-stock-metric__footer">
        @if($hasMix)
            <div class="wh-stock-mix" aria-hidden="true">
                @if($finishedPct > 0)
                    <span class="wh-stock-mix__seg wh-stock-mix__seg--finished" style="width: {{ $finishedPct }}%"></span>
                @endif
                @if($rawPct > 0)
                    <span class="wh-stock-mix__seg wh-stock-mix__seg--raw" style="width: {{ $rawPct }}%"></span>
                @endif
                @if($otherPct > 0)
                    <span class="wh-stock-mix__seg wh-stock-mix__seg--other" style="width: {{ $otherPct }}%"></span>
                @endif
            </div>
        @endif

        <dl class="wh-stock-metric__split">
            <div class="wh-stock-metric__split-row">
                <dt>Finished</dt>
                <dd>{{ number_format($finished, 0) }}</dd>
            </div>
            <div class="wh-stock-metric__split-row">
                <dt>Raw</dt>
                <dd>{{ number_format($raw, 0) }}</dd>
            </div>
        </dl>

        @if($materialsHref)
            <a href="{{ $materialsHref }}" class="wh-stock-metric__materials">View materials →</a>
        @endif
    </div>
</div>
