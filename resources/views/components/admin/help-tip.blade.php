@props([
    'label' => 'More information',
])

<span
    {{ $attributes->class(['erp-help-tip']) }}
    x-data="{ open: false, pinned: false }"
    @mouseenter="open = true"
    @mouseleave="if (!pinned) open = false"
    @click.outside="open = false; pinned = false"
>
    <button
        type="button"
        class="erp-help-tip__btn"
        aria-label="{{ $label }}"
        :aria-expanded="open.toString()"
        @click.stop="pinned = !pinned; open = pinned"
    >
        ?
    </button>
    <span
        role="tooltip"
        class="erp-help-tip__panel"
        x-show="open"
        x-cloak
        x-transition.opacity.duration.120ms
    >
        {{ $slot }}
    </span>
</span>
