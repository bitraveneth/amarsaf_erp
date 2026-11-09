@props([
    'action' => url()->current(),
    'accounts' => [],
    'selected' => null,
    'label' => 'Ledger',
    'activeIds' => [],
])

@php
    $accountList = collect($accounts)->map(function ($account) use ($activeIds) {
        $id = is_array($account) ? ($account['id'] ?? null) : $account->id;
        $code = is_array($account) ? ($account['code'] ?? '') : $account->code;
        $name = is_array($account) ? ($account['name'] ?? '') : $account->name;

        return [
            'id' => (int) $id,
            'code' => (string) $code,
            'name' => (string) $name,
            'label' => trim($code . ' — ' . $name),
            'active' => in_array((int) $id, array_map('intval', $activeIds), true),
        ];
    })->values()->all();

    $selectedLabel = $selected
        ? $selected->code . ' — ' . $selected->name
        : 'Search ledger';
@endphp

<div
    {{ $attributes->merge(['class' => 'erp-dash-filter-compact relative']) }}
    x-data="{
        open: false,
        query: '',
        accounts: {{ Js::from($accountList) }},
        selectedId: {{ Js::from(optional($selected)->id) }},
        get filtered() {
            const needle = this.query.trim().toLowerCase();
            if (!needle) return this.accounts;
            return this.accounts.filter((account) =>
                account.label.toLowerCase().includes(needle)
                || account.code.toLowerCase().includes(needle)
                || account.name.toLowerCase().includes(needle)
            );
        },
        toggle() {
            this.open = !this.open;
            if (this.open) {
                this.$nextTick(() => this.$refs.search?.focus());
            }
        },
        pick(id) {
            this.selectedId = id;
            this.open = false;
            this.query = '';
            this.$refs.form.querySelector('[name=account_id]').value = id;
            this.$refs.form.submit();
        },
    }"
    @keydown.escape.window="open = false"
>
    <button
        type="button"
        @click="toggle()"
        class="erp-dash-filter-compact__trigger"
        aria-haspopup="dialog"
        :aria-expanded="open.toString()"
        title="Change ledger account"
    >
        <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z"/>
        </svg>
        <span class="erp-dash-filter-compact__label">{{ $label }}</span>
        <span class="erp-dash-filter-compact__dates hidden max-w-[16rem] truncate sm:inline">{{ $selectedLabel }}</span>
        <svg class="h-3.5 w-3.5 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
        </svg>
    </button>

    <div
        x-show="open"
        x-cloak
        x-transition
        @click.outside="open = false"
        class="erp-dash-filter-compact__panel erp-dash-filter-compact__panel--wide"
        role="dialog"
        aria-label="Ledger account"
    >
        <p class="erp-dash-filter-compact__panel-title">Ledger account</p>
        <p class="erp-dash-filter-compact__panel-hint">Type a code or name. All postable ledgers are listed.</p>

        <form x-ref="form" method="GET" action="{{ $action }}" class="mt-3 space-y-3">
            @foreach(request()->except(['account_id', 'page']) as $key => $param)
                @if(is_scalar($param) && $param !== '')
                    <input type="hidden" name="{{ $key }}" value="{{ $param }}">
                @endif
            @endforeach
            <input type="hidden" name="account_id" value="{{ optional($selected)->id }}">

            <label class="erp-dash-filter-compact__field">
                <span class="erp-label">Search</span>
                <input
                    x-ref="search"
                    type="search"
                    class="erp-input erp-input--sm"
                    placeholder="Code or name"
                    x-model="query"
                    autocomplete="off"
                >
            </label>

            <ul class="max-h-64 space-y-1 overflow-y-auto rounded-xl border border-gray-100 p-1 dark:border-gray-800">
                <template x-for="account in filtered" :key="account.id">
                    <li>
                        <button
                            type="button"
                            class="flex w-full items-center gap-2 rounded-lg px-2.5 py-2 text-left hover:bg-gray-50 dark:hover:bg-white/5"
                            :class="Number(account.id) === Number(selectedId) ? 'bg-brand-50 ring-1 ring-brand-200 dark:bg-brand-500/10 dark:ring-brand-500/30' : ''"
                            @click="pick(account.id)"
                        >
                            <span class="shrink-0 rounded-md bg-gray-100 px-1.5 py-0.5 font-mono text-[11px] font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300" x-text="account.code"></span>
                            <span class="min-w-0 flex-1 truncate text-sm font-medium text-gray-800 dark:text-gray-200" x-text="account.name"></span>
                            <span
                                class="shrink-0 rounded-full bg-success-50 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-success-700 dark:bg-success-500/10 dark:text-success-400"
                                x-show="account.active"
                            >Posted</span>
                        </button>
                    </li>
                </template>
                <li x-show="filtered.length === 0" class="erp-caption px-3 py-4">No ledgers match.</li>
            </ul>
        </form>
    </div>
</div>
