@props([
    'action' => url()->current(),
    'name' => 'as_of',
    'value' => null,
    'label' => 'As of',
])

@php
    $asOfValue = $value
        ? \Illuminate\Support\Carbon::parse($value)->toDateString()
        : now()->toDateString();
    $display = \Illuminate\Support\Carbon::parse($asOfValue)->format('d M Y');
    $actionBase = strtok($action, '?') ?: $action;
    $preservedQuery = collect(request()->except([$name, 'page']))
        ->filter(fn ($param) => is_scalar($param) && $param !== '')
        ->all();

    $today = now()->startOfDay();
    $monthEnd = $today->copy()->startOfMonth()->subDay();
    $quarterEnd = $today->copy()->startOfQuarter()->subDay();
    $yearEnd = $today->copy()->startOfYear()->subDay();

    // Jump-to mirrors the period calendar labels; open periods / Lifetime use today
    // (full history through the snapshot). Closed-period ends jump to the prior close.
    $asOfPresets = [
        ['key' => 'today', 'label' => 'Today', 'date' => $today->toDateString()],
        ['key' => 'month', 'label' => 'This month', 'date' => $today->toDateString()],
        ['key' => 'quarter', 'label' => 'This quarter', 'date' => $today->toDateString()],
        ['key' => 'year', 'label' => 'This year', 'date' => $today->toDateString()],
        ['key' => 'lifetime', 'label' => 'Lifetime', 'date' => $today->toDateString()],
        ['key' => 'month_end', 'label' => 'Month end', 'date' => $monthEnd->toDateString()],
        ['key' => 'quarter_end', 'label' => 'Quarter end', 'date' => $quarterEnd->toDateString()],
        ['key' => 'year_end', 'label' => 'Year end', 'date' => $yearEnd->toDateString()],
    ];

    $closedKeys = ['month_end', 'quarter_end', 'year_end'];
    $activePreset = collect($asOfPresets)
        ->first(fn ($preset) => $preset['date'] === $asOfValue && in_array($preset['key'], $closedKeys, true))['key']
        ?? ($asOfValue === $today->toDateString() ? 'today' : null);
@endphp

<div {{ $attributes->merge(['class' => 'erp-dash-filter-compact relative']) }}
     x-data="{
        open: false,
        asOf: @js($asOfValue),
        view: @js(\Illuminate\Support\Carbon::parse($asOfValue)->startOfMonth()->toDateString()),
        today: @js(now()->toDateString()),
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
            this.asOf = iso;
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
            title="Change as-of date">
        <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        <span class="erp-dash-filter-compact__label">{{ $label }}</span>
        <span class="erp-dash-filter-compact__dates">{{ $display }}</span>
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
         aria-label="As-of date">
        <form method="GET" action="{{ $actionBase }}" class="erp-period-cal__month">
            @foreach($preservedQuery as $key => $param)
                <input type="hidden" name="{{ $key }}" value="{{ $param }}">
            @endforeach
            <input type="hidden" name="{{ $name }}" :value="asOf">

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
                                'is-edge': cell.iso === asOf,
                                'is-today': cell.iso === today,
                            }"
                            @click="pick(cell.iso)"
                            :aria-label="label(cell.iso)"
                            x-text="cell.n"></button>
                </template>
            </div>

            <div class="erp-period-cal__foot">
                <p class="erp-caption" x-text="'Snapshot · ' + label(asOf)"></p>
                <button type="submit" class="erp-btn-primary !px-2.5 !py-1.5 !text-xs" @click="open = false">Apply date</button>
            </div>
        </form>

        <div class="erp-period-cal__side">
            <p class="erp-period-cal__side-label">Jump to</p>
            @foreach($asOfPresets as $preset)
                <a href="{{ $actionBase . '?' . http_build_query(array_merge($preservedQuery, [$name => $preset['date']])) }}"
                   class="erp-period-cal__preset {{ $activePreset === $preset['key'] ? 'is-active' : '' }}">{{ $preset['label'] }}</a>
            @endforeach
            <a href="{{ $actionBase . ($preservedQuery ? '?' . http_build_query($preservedQuery) : '') }}" class="erp-period-cal__preset">Reset</a>
        </div>
    </div>
</div>
