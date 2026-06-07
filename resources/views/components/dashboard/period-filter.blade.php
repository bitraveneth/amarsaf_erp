@props([
    'action' => url()->current(),
    'range' => 'month',
    'from' => null,
    'to' => null,
    'rangeOptions' => [],
])

<form method="GET" action="{{ $action }}" {{ $attributes->merge(['class' => 'erp-dash-filter']) }}>
    <div class="erp-dash-filter__header">
        <div>
            <p class="erp-eyebrow">Reporting period</p>
            <p class="erp-caption mt-1">Filter KPIs and charts for the selected window.</p>
        </div>
        <span class="erp-dash-filter__badge">{{ $rangeOptions[$range] ?? 'Custom range' }}</span>
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
        @if(request()->filled('range') || request()->filled('from') || request()->filled('to'))
            <a href="{{ $action }}" class="erp-btn-secondary">Reset</a>
        @endif
        <button type="submit" class="erp-btn-primary">Apply</button>
    </div>
</form>
