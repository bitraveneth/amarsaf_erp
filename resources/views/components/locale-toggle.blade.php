@props([
    'current' => null,
])

@php
    $locale = $current ?: (app()->getLocale() === 'bn' ? 'bn' : 'en');
@endphp

<div
    {{ $attributes->class(['locale-toggle']) }}
    x-data="{ open: false }"
    @keydown.escape.window="open = false"
    @click.outside="open = false"
>
    <span class="sr-only">{{ __('app.language.switch_label') }}</span>
    <button
        type="button"
        class="locale-toggle__trigger"
        :disabled="$store.learningLang?.switching"
        :aria-expanded="open.toString()"
        aria-haspopup="listbox"
        @click.stop="open = ! open"
    >
        <svg class="locale-toggle__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
            <circle cx="12" cy="12" r="9"/>
            <path d="M3 12h18M12 3c2.5 3 3.7 6 3.7 9s-1.2 6-3.7 9c-2.5-3-3.7-6-3.7-9S9.5 6 12 3z" stroke-linecap="round"/>
        </svg>
        <span
            class="locale-toggle__value"
            x-text="($store.learningLang?.code || @js($locale)) === 'bn' ? 'বাংলা' : 'English'"
        >{{ $locale === 'bn' ? 'বাংলা' : 'English' }}</span>
        <svg class="locale-toggle__chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M6 9l6 6 6-6" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </button>

    <div
        class="locale-toggle__menu"
        x-show="open"
        x-cloak
        x-transition.opacity.duration.120ms
        role="listbox"
        aria-label="{{ __('app.language.switch_label') }}"
    >
        <button
            type="button"
            class="locale-toggle__option"
            role="option"
            :class="{ 'is-active': ($store.learningLang?.code || @js($locale)) === 'en' }"
            @click="$store.learningLang.setLocale('en'); open = false"
        >English</button>
        <button
            type="button"
            class="locale-toggle__option"
            role="option"
            :class="{ 'is-active': ($store.learningLang?.code || @js($locale)) === 'bn' }"
            @click="$store.learningLang.setLocale('bn'); open = false"
        >বাংলা</button>
    </div>
</div>
