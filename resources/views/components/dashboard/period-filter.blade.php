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
    $actionBase = strtok($action, '?') ?: $action;
    $preservedQuery = collect(request()->except(['range', 'from', 'to', 'page']))
        ->filter(fn ($value) => is_scalar($value) && $value !== '')
        ->all();
    $presetOptions = collect($rangeOptions)->except('custom');
    // Lifetime uses a synthetic early from-date (2000-01-01) for queries only.
    // Do not seed the calendar picker with year 2000 — clicking a day there and
    // pressing Apply would create a custom range with no journals (all zeros).
    $isLifetimeRange = in_array($range, ['lifetime', 'all'], true)
        || ($from && \Illuminate\Support\Carbon::parse($from)->year <= 2000);
    $fromValue = $isLifetimeRange
        ? now()->startOfMonth()->toDateString()
        : ($from ?: now()->startOfMonth()->toDateString());
    $toValue = $isLifetimeRange
        ? now()->toDateString()
        : ($to ?: now()->toDateString());
@endphp

@if($variant === 'compact')
    <div {{ $attributes->merge(['class' => 'erp-dash-filter-compact relative']) }}
         x-data="{
            open: false,
            from: @js($fromValue),
            to: @js($toValue),
            view: @js(\Illuminate\Support\Carbon::parse($fromValue)->startOfMonth()->toDateString()),
            today: @js(now()->toDateString()),
            anchor: null,
            iso(date) {
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return date.getFullYear() + '-' + month + '-' + day;
            },
            parse(value) {
                const [year, month, day] = value.split('-').map(Number);
                return new Date(year, month - 1, day);
            },
            monthTitle() {
                return this.parse(this.view).toLocaleDateString('en-GB', { month: 'long', year: 'numeric' });
            },
            shiftMonth(step) {
                const date = this.parse(this.view);
                this.view = this.iso(new Date(date.getFullYear(), date.getMonth() + step, 1));
            },
            cells() {
                const first = this.parse(this.view);
                const start = new Date(first);
                start.setDate(1 - ((first.getDay() + 6) % 7));
                return Array.from({ length: 42 }, (_, index) => {
                    const day = new Date(start);
                    day.setDate(start.getDate() + index);
                    return {
                        iso: this.iso(day),
                        n: day.getDate(),
                        inMonth: day.getMonth() === first.getMonth(),
                    };
                });
            },
            pick(iso) {
                if (!this.anchor) {
                    this.anchor = iso;
                    this.from = iso;
                    this.to = iso;
                    return;
                }
                this.from = iso < this.anchor ? iso : this.anchor;
                this.to = iso < this.anchor ? this.anchor : iso;
                this.anchor = null;
            },
            inRange(iso) {
                return this.from && this.to && iso >= this.from && iso <= this.to;
            },
            label(iso) {
                return this.parse(iso).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
            },
         }"
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
                <span class="erp-dash-filter-compact__dates">{{ $periodLabel }}</span>
            @endif
            <svg class="h-4 w-4 shrink-0 text-gray-400 transition" :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
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
             class="erp-dash-filter-compact__panel erp-period-cal"
             role="dialog"
             aria-label="Reporting period">
            <form method="GET" action="{{ $actionBase }}" class="erp-period-cal__month">
                @foreach($preservedQuery as $key => $param)
                    <input type="hidden" name="{{ $key }}" value="{{ $param }}">
                @endforeach
                <input type="hidden" name="from" :value="from">
                <input type="hidden" name="to" :value="to">

                <div class="erp-period-cal__nav">
                    <button type="button" class="erp-period-cal__step" @click="shiftMonth(-1)" aria-label="Previous month">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <p class="erp-period-cal__title" x-text="monthTitle()"></p>
                    <button type="button" class="erp-period-cal__step" @click="shiftMonth(1)" aria-label="Next month">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>

                <div class="erp-period-cal__grid" aria-hidden="true">
                    <span class="erp-period-cal__dow">Mo</span>
                    <span class="erp-period-cal__dow">Tu</span>
                    <span class="erp-period-cal__dow">We</span>
                    <span class="erp-period-cal__dow">Th</span>
                    <span class="erp-period-cal__dow">Fr</span>
                    <span class="erp-period-cal__dow">Sa</span>
                    <span class="erp-period-cal__dow">Su</span>
                </div>
                <div class="erp-period-cal__grid">
                    <template x-for="cell in cells()" :key="cell.iso">
                        <button type="button"
                                class="erp-period-cal__day"
                                :class="{
                                    'is-out': !cell.inMonth,
                                    'is-in': inRange(cell.iso),
                                    'is-edge': cell.iso === from || cell.iso === to,
                                    'is-today': cell.iso === today,
                                }"
                                @click="pick(cell.iso)"
                                :aria-label="label(cell.iso)"
                                x-text="cell.n"></button>
                    </template>
                </div>

                <div class="erp-period-cal__foot">
                    <p class="erp-caption" x-text="label(from) + ' – ' + label(to)"></p>
                    <button type="submit" name="range" value="custom" class="erp-btn-primary !px-2.5 !py-1.5 !text-xs">Apply dates</button>
                </div>
            </form>

            <div class="erp-period-cal__side">
                <p class="erp-period-cal__side-label">Jump to</p>
                @foreach($presetOptions as $value => $label)
                    <a href="{{ $actionBase . '?' . http_build_query(array_merge($preservedQuery, ['range' => $value])) }}"
                       class="erp-period-cal__preset {{ $range === $value ? 'is-active' : '' }}">{{ $label }}</a>
                @endforeach
                @if($hasCustomFilter)
                    <a href="{{ $actionBase . ($preservedQuery ? '?' . http_build_query($preservedQuery) : '') }}" class="erp-period-cal__preset">Reset</a>
                @endif
            </div>
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
