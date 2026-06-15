@props([
    'action' => url()->current(),
    'range' => 'month',
    'from' => null,
    'to' => null,
    'rangeOptions' => [],
    'variant' => 'default',
    'periodLabel' => null,
])

@php
    $rangeLabel = $rangeOptions[$range] ?? 'Custom range';
    $hasCustomFilter = request()->filled('range') || request()->filled('from') || request()->filled('to');
@endphp

@if($variant === 'compact')
    <div {{ $attributes->merge(['class' => 'erp-dash-filter-compact relative']) }}
         x-data="{ open: false }"
         @keydown.escape.window="open = false">
        <button type="button"
                @click="open = !open"
                class="erp-dash-filter-compact__trigger"
                aria-haspopup="dialog"
                :aria-expanded="open.toString()"
                title="{{ $periodLabel ?: 'Change reporting period' }}">
            <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span class="erp-dash-filter-compact__label">{{ $rangeLabel }}</span>
            @if($periodLabel)
                <span class="erp-dash-filter-compact__dates hidden sm:inline">{{ $periodLabel }}</span>
            @endif
            <svg class="h-3.5 w-3.5 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
            </svg>
        </button>

        <div x-show="open"
             x-cloak
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-1"
             @click.outside="open = false"
             class="erp-dash-filter-compact__panel"
             role="dialog"
             aria-label="Reporting period">
            <p class="erp-dash-filter-compact__panel-title">Reporting period</p>
            <p class="erp-dash-filter-compact__panel-hint">Updates KPIs and charts in this section.</p>

            <form method="GET" action="{{ $action }}" class="mt-3 space-y-3">
                @foreach(request()->except(['range', 'from', 'to', 'page']) as $key => $param)
                    @if(is_scalar($param) && $param !== '')
                        <input type="hidden" name="{{ $key }}" value="{{ $param }}">
                    @endif
                @endforeach
                <label class="erp-dash-filter-compact__field">
                    <span class="erp-label">Range</span>
                    <select name="range" class="erp-select erp-select--sm">
                        @foreach($rangeOptions as $value => $label)
                            <option value="{{ $value }}" @selected($range === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <div class="grid grid-cols-2 gap-2">
                    <label class="erp-dash-filter-compact__field">
                        <span class="erp-label">From</span>
                        <input type="date" name="from" value="{{ $from }}" class="erp-input erp-input--sm">
                    </label>
                    <label class="erp-dash-filter-compact__field">
                        <span class="erp-label">To</span>
                        <input type="date" name="to" value="{{ $to }}" class="erp-input erp-input--sm">
                    </label>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-gray-100 pt-3 dark:border-gray-800">
                    @if($hasCustomFilter)
                        <a href="{{ $action }}" class="erp-btn-secondary !px-2.5 !py-1.5 !text-xs">Reset</a>
                    @endif
                    <button type="submit" class="erp-btn-primary !px-2.5 !py-1.5 !text-xs" @click="open = false">Apply</button>
                </div>
            </form>
        </div>
    </div>
@else
    <form method="GET" action="{{ $action }}" {{ $attributes->merge(['class' => 'erp-dash-filter']) }}>
        <div class="erp-dash-filter__header">
            <div>
                <p class="erp-eyebrow">Reporting period</p>
                <p class="erp-caption mt-1">Filter KPIs and charts for the selected window.</p>
            </div>
            <span class="erp-dash-filter__badge">{{ $rangeLabel }}</span>
        </div>

        <div class="erp-dash-filter__fields">
            <label class="erp-dash-filter__field">
                <span class="erp-label">Range</span>
                <select name="range" class="erp-select">
                    @foreach($rangeOptions as $value => $label)
                        <option value="{{ $value }}" @selected($range === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>

            <label class="erp-dash-filter__field">
                <span class="erp-label">From</span>
                <input type="date" name="from" value="{{ $from }}" class="erp-input">
            </label>

            <label class="erp-dash-filter__field">
                <span class="erp-label">To</span>
                <input type="date" name="to" value="{{ $to }}" class="erp-input">
            </label>
        </div>

        <div class="erp-dash-filter__actions">
            @if($hasCustomFilter)
                <a href="{{ $action }}" class="erp-btn-secondary">Reset</a>
            @endif
            <button type="submit" class="erp-btn-primary">Apply</button>
        </div>
    </form>
@endif
