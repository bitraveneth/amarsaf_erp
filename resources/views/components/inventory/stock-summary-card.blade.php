@props([
    'name',
    'quantity' => 0,
    'href' => null,
    'status' => 'available',
    'quantityLabel' => 'Total quantity in stock',
    'totalQty' => null,
    'finishedQty' => null,
    'rawQty' => null,
    'otherQty' => null,
    'finishedPct' => 0,
    'rawPct' => 0,
    'otherPct' => 0,
    'showBreakdown' => false,
    'filterAware' => false,
    'materialsHref' => null,
])

@php
    $statusGradients = [
        'available' => 'from-success-500 to-success-600',
        'reserved' => 'from-brand-500 to-brand-600',
        'damaged' => 'from-error-500 to-error-600',
        'quarantined' => 'from-orange-500 to-orange-600',
    ];
    $statusGradient = $statusGradients[$status] ?? 'from-gray-500 to-gray-600';

    $statusIcons = [
        'available' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        'reserved' => 'M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z',
        'damaged' => 'M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z',
        'quarantined' => 'M12 9v3.75m-1.5-2.25h3M12 15.75h.007v.008H12v-.008z',
    ];
    $statusIcon = $statusIcons[$status] ?? $statusIcons['available'];

    $resolvedTotal = (float) ($totalQty ?? $quantity);
    $resolvedFinished = (float) ($finishedQty ?? 0);
    $resolvedRaw = (float) ($rawQty ?? 0);
    $resolvedOther = (float) ($otherQty ?? 0);
    $hasMix = $showBreakdown && $resolvedTotal > 0 && ($finishedPct + $rawPct + $otherPct) > 0;
    $showOther = $showBreakdown && $resolvedOther > 0;
@endphp

<div {{ $attributes->merge(['class' => 'inv-stock-card group relative flex flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition-all hover:shadow-md dark:border-gray-800 dark:bg-gray-900']) }}>
    <div class="absolute right-0 top-0 h-20 w-20 translate-x-6 -translate-y-6 opacity-5">
        <svg class="h-full w-full text-gray-900 dark:text-white" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375 7.444 2.25 12 2.25s8.25 1.847 8.25 4.125zm0 4.5c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125m16.5 4.5c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125v-9m16.5 9v-9" />
        </svg>
    </div>

    @if($href)
        <a href="{{ $href }}" class="inv-stock-card__body relative block flex-1 p-6 transition hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
    @else
        <div class="inv-stock-card__body relative flex-1 p-6">
    @endif
        <div class="flex items-center justify-between gap-3">
            <div class="flex min-w-0 items-center gap-2">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br {{ $statusGradient }} text-white shadow-sm">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $statusIcon }}" />
                    </svg>
                </div>
                <span class="truncate text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    {{ $name }}
                </span>
            </div>
            <span class="inline-flex shrink-0 items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium capitalize text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                {{ $status }}
            </span>
        </div>

        @if($filterAware)
            <p class="mt-3 text-3xl font-bold tabular-nums text-gray-900 dark:text-white"
               x-text="(() => {
                   const qty = $root.stockFilter === 'finished' ? {{ $resolvedFinished }}
                       : ($root.stockFilter === 'raw' ? {{ $resolvedRaw }} : {{ $resolvedTotal }});
                   return qty.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
               })()"></p>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400"
               x-text="$root.stockFilter === 'finished' ? 'Finished goods on hand'
                   : ($root.stockFilter === 'raw' ? 'Raw materials on hand' : 'Total quantity in stock')"></p>
        @else
            <p class="mt-3 text-3xl font-bold tabular-nums text-gray-900 dark:text-white">
                {{ number_format($resolvedTotal, 2) }}
            </p>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                {{ $quantityLabel }}
            </p>
        @endif

        @if($showBreakdown)
            @if($hasMix)
                <div class="inv-stock-mix mt-4" aria-hidden="true">
                    @if($finishedPct > 0)
                        <span class="inv-stock-mix__seg inv-stock-mix__seg--finished" style="width: {{ $finishedPct }}%"></span>
                    @endif
                    @if($rawPct > 0)
                        <span class="inv-stock-mix__seg inv-stock-mix__seg--raw" style="width: {{ $rawPct }}%"></span>
                    @endif
                    @if($otherPct > 0)
                        <span class="inv-stock-mix__seg inv-stock-mix__seg--other" style="width: {{ $otherPct }}%"></span>
                    @endif
                </div>
                <p class="inv-stock-mix__legend mt-1.5">
                    @if($finishedPct > 0)
                        <span>{{ $finishedPct }}% finished</span>
                    @endif
                    @if($rawPct > 0)
                        <span>{{ $rawPct }}% raw</span>
                    @endif
                </p>
            @endif

            <dl class="inv-stock-breakdown mt-3 space-y-1.5 border-t border-gray-100 pt-3 dark:border-gray-800">
                <div class="inv-stock-breakdown__row">
                    <dt class="inv-stock-breakdown__label inv-stock-breakdown__label--finished">Finished</dt>
                    <dd class="inv-stock-breakdown__value">{{ number_format($resolvedFinished, 2) }}</dd>
                </div>
                <div class="inv-stock-breakdown__row">
                    <dt class="inv-stock-breakdown__label inv-stock-breakdown__label--raw">Raw</dt>
                    <dd class="inv-stock-breakdown__value">{{ number_format($resolvedRaw, 2) }}</dd>
                </div>
                @if($showOther)
                    <div class="inv-stock-breakdown__row">
                        <dt class="inv-stock-breakdown__label inv-stock-breakdown__label--other">Other</dt>
                        <dd class="inv-stock-breakdown__value">{{ number_format($resolvedOther, 2) }}</dd>
                    </div>
                @endif
            </dl>
        @endif
    @if($href)
        </a>
    @else
        </div>
    @endif

    @if($materialsHref)
        <a href="{{ $materialsHref }}" class="inv-stock-card__materials border-t border-gray-100 px-6 py-2.5 text-xs font-semibold text-brand-600 transition hover:bg-brand-50/50 hover:text-brand-700 dark:border-gray-800 dark:text-brand-400 dark:hover:bg-brand-500/10">
            View materials →
        </a>
    @endif
</div>
