@props([
    'action' => url()->current(),
    'accounts' => [],
    'selected' => null,
    'label' => 'Account',
])

@php
    $selectedLabel = $selected
        ? $selected->code . ' — ' . $selected->name
        : 'Select account';
@endphp

<div {{ $attributes->merge(['class' => 'erp-dash-filter-compact relative']) }}
     x-data="{ open: false }"
     @keydown.escape.window="open = false">
    <button type="button"
            @click="open = !open"
            class="erp-dash-filter-compact__trigger"
            aria-haspopup="dialog"
            :aria-expanded="open.toString()"
            title="Change ledger account">
        <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16M4 12h10M4 17h16"/>
        </svg>
        <span class="erp-dash-filter-compact__label">{{ $label }}</span>
        <span class="erp-dash-filter-compact__dates hidden max-w-[14rem] truncate sm:inline">{{ $selectedLabel }}</span>
        <svg class="h-3.5 w-3.5 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
        </svg>
    </button>

    <div x-show="open"
         x-cloak
         x-transition
         @click.outside="open = false"
         class="erp-dash-filter-compact__panel erp-dash-filter-compact__panel--wide"
         role="dialog"
         aria-label="Ledger account">
        <p class="erp-dash-filter-compact__panel-title">Ledger account</p>
        <p class="erp-dash-filter-compact__panel-hint">Posted journal lines for one chart-of-accounts entry.</p>

        <form method="GET" action="{{ $action }}" class="mt-3 space-y-3">
            @foreach(request()->except(['account_id', 'page']) as $key => $param)
                @if(is_scalar($param) && $param !== '')
                    <input type="hidden" name="{{ $key }}" value="{{ $param }}">
                @endif
            @endforeach

            <label class="erp-dash-filter-compact__field">
                <span class="erp-label">Account</span>
                <select name="account_id" class="erp-select erp-select--sm" required>
                    <option value="">Select account</option>
                    @foreach($accounts as $account)
                        <option value="{{ $account->id }}" @selected(optional($selected)->id === $account->id)>
                            {{ $account->code }} — {{ $account->name }}
                        </option>
                    @endforeach
                </select>
            </label>

            <div class="flex items-center justify-end gap-2 border-t border-gray-100 pt-3 dark:border-gray-800">
                @if($selected)
                    <a href="{{ $action . '?' . http_build_query(request()->only(['range', 'from', 'to'])) }}"
                       class="erp-btn-secondary !px-2.5 !py-1.5 !text-xs">Clear</a>
                @endif
                <button type="submit" class="erp-btn-primary !px-2.5 !py-1.5 !text-xs" @click="open = false">Apply</button>
            </div>
        </form>
    </div>
</div>
