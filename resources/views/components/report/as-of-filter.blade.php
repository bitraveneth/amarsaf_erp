@props([
    'action' => url()->current(),
    'name' => 'as_of',
    'value' => null,
    'label' => 'As of',
])

@php
    $display = $value
        ? \Illuminate\Support\Carbon::parse($value)->format('d M Y')
        : 'Pick date';
@endphp

<div {{ $attributes->merge(['class' => 'erp-dash-filter-compact relative']) }}
     x-data="{ open: false }"
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
        <span class="erp-dash-filter-compact__dates hidden sm:inline">{{ $display }}</span>
        <svg class="h-3.5 w-3.5 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
        </svg>
    </button>

    <div x-show="open"
         x-cloak
         x-transition
         @click.outside="open = false"
         class="erp-dash-filter-compact__panel"
         role="dialog"
         aria-label="As-of date">
        <p class="erp-dash-filter-compact__panel-title">As-of date</p>
        <p class="erp-dash-filter-compact__panel-hint">Snapshot balances on this date.</p>

        <form method="GET" action="{{ $action }}" class="mt-3 space-y-3">
            @foreach(request()->except([$name, 'page']) as $key => $param)
                @if(is_scalar($param) && $param !== '')
                    <input type="hidden" name="{{ $key }}" value="{{ $param }}">
                @endif
            @endforeach

            <label class="erp-dash-filter-compact__field">
                <span class="erp-label">Date</span>
                <input type="date" name="{{ $name }}" value="{{ $value }}" class="erp-input erp-input--sm">
            </label>

            <div class="flex items-center justify-end gap-2 border-t border-gray-100 pt-3 dark:border-gray-800">
                <button type="submit" class="erp-btn-primary !px-2.5 !py-1.5 !text-xs" @click="open = false">Apply</button>
            </div>
        </form>
    </div>
</div>
