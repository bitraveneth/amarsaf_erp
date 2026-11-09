@props([
    'action' => url()->current(),
    'name' => 'agent_id',
    'label' => 'Party',
    'options' => [],
    'selectedId' => null,
    'selectedLabel' => 'Search',
    'variant' => 'compact',
])

@php
    $partyList = collect($options)->map(function ($option) {
        if (is_array($option)) {
            return [
                'id' => (int) ($option['id'] ?? 0),
                'label' => (string) ($option['label'] ?? $option['name'] ?? ''),
                'meta' => (string) ($option['meta'] ?? ''),
            ];
        }

        return [
            'id' => (int) $option->id,
            'label' => (string) ($option->name ?? ''),
            'meta' => (string) ($option->meta ?? $option->phone ?? $option->area ?? ''),
        ];
    })->values()->all();

    $isHero = $variant === 'hero';
    $needsPick = blank($selectedId);
@endphp

<div
    {{ $attributes->merge([
        'class' => $isHero
            ? 'erp-party-filter erp-party-filter--hero relative'
            : 'erp-dash-filter-compact erp-party-filter relative',
    ]) }}
    x-data="{
        open: {{ $isHero ? 'true' : 'false' }},
        query: '',
        options: {{ Js::from($partyList) }},
        selectedId: {{ Js::from($selectedId) }},
        get filtered() {
            const needle = this.query.trim().toLowerCase();
            if (!needle) return this.options;
            return this.options.filter((row) =>
                row.label.toLowerCase().includes(needle)
                || (row.meta || '').toLowerCase().includes(needle)
            );
        },
        toggle() {
            if ({{ $isHero ? 'true' : 'false' }}) return;
            this.open = !this.open;
            if (this.open) this.$nextTick(() => this.$refs.search?.focus());
        },
        pick(id) {
            this.selectedId = id;
            this.open = {{ $isHero ? 'true' : 'false' }};
            this.query = '';
            this.$refs.form.querySelector('[name={{ $name }}]').value = id;
            this.$refs.form.submit();
        },
    }"
    @keydown.escape.window="open = false"
>
    @unless($isHero)
        <button
            type="button"
            @click="toggle()"
            @class([
                'erp-dash-filter-compact__trigger',
                'erp-party-filter__trigger--needs-pick' => $needsPick,
            ])
            aria-haspopup="dialog"
            :aria-expanded="open.toString()"
            title="Change {{ strtolower($label) }}"
        >
            <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z"/>
            </svg>
            <span class="erp-dash-filter-compact__label">{{ $label }}</span>
            <span @class([
                'erp-dash-filter-compact__dates max-w-[14rem] truncate',
                'text-brand-600 dark:text-brand-400 font-medium' => $needsPick,
            ])>{{ $selectedLabel }}</span>
            <svg class="h-3.5 w-3.5 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
            </svg>
        </button>
    @endunless

    <div
        x-show="open"
        x-cloak
        @unless($isHero)
            x-transition
            @click.outside="open = false"
        @endunless
        @class([
            'erp-dash-filter-compact__panel erp-dash-filter-compact__panel--wide' => ! $isHero,
            'erp-party-filter__hero-panel' => $isHero,
        ])
        role="dialog"
        aria-label="{{ $label }}"
    >
        @unless($isHero)
            <p class="erp-dash-filter-compact__panel-title">{{ $label }}</p>
            <p class="erp-dash-filter-compact__panel-hint">Type a name to filter the list.</p>
        @endunless

        <form x-ref="form" method="GET" action="{{ $action }}" @class([$isHero ? 'space-y-3' : 'mt-3 space-y-3'])>
            @foreach(request()->except([$name, 'page']) as $key => $param)
                @if(is_scalar($param) && $param !== '')
                    <input type="hidden" name="{{ $key }}" value="{{ $param }}">
                @endif
            @endforeach
            <input type="hidden" name="{{ $name }}" value="{{ $selectedId }}">

            <label class="erp-dash-filter-compact__field">
                <span class="erp-label">Search {{ strtolower($label) }}</span>
                <input
                    x-ref="search"
                    type="search"
                    class="erp-input"
                    placeholder="Name, area, or phone"
                    x-model="query"
                    autocomplete="off"
                    @if($isHero) autofocus @endif
                >
            </label>

            <ul @class([
                'max-h-64 space-y-1 overflow-y-auto rounded-xl border border-gray-100 p-1 dark:border-gray-800' => ! $isHero,
                'erp-party-filter__hero-list' => $isHero,
            ])>
                <template x-for="row in filtered" :key="row.id">
                    <li>
                        <button
                            type="button"
                            class="flex w-full flex-col rounded-lg px-2.5 py-2 text-left hover:bg-gray-50 dark:hover:bg-white/5"
                            :class="Number(row.id) === Number(selectedId) ? 'bg-brand-50 ring-1 ring-brand-200 dark:bg-brand-500/10 dark:ring-brand-500/30' : ''"
                            @click="pick(row.id)"
                        >
                            <span class="truncate text-sm font-medium text-gray-800 dark:text-gray-200" x-text="row.label"></span>
                            <span class="erp-caption" x-show="row.meta" x-text="row.meta"></span>
                        </button>
                    </li>
                </template>
                <li x-show="filtered.length === 0" class="erp-caption px-3 py-4">No matches.</li>
            </ul>
        </form>
    </div>
</div>
