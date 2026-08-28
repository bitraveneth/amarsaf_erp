@php
    $locale = app()->getLocale() === 'bn' ? 'bn' : 'en';
@endphp

<form
    method="POST"
    action="{{ route('locale.update') }}"
    {{ $attributes->class(['locale-toggle']) }}
    data-locale-menu
>
    @csrf
    <span class="sr-only">{{ __('app.language.switch_label') }}</span>
    <button
        type="button"
        class="locale-toggle__trigger"
        aria-expanded="false"
        aria-haspopup="listbox"
        data-locale-trigger
    >
        <svg class="locale-toggle__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
            <circle cx="12" cy="12" r="9"/>
            <path d="M3 12h18M12 3c2.5 3 3.7 6 3.7 9s-1.2 6-3.7 9c-2.5-3-3.7-6-3.7-9S9.5 6 12 3z" stroke-linecap="round"/>
        </svg>
        <span class="locale-toggle__value">{{ $locale === 'bn' ? 'বাংলা' : 'English' }}</span>
        <svg class="locale-toggle__chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M6 9l6 6 6-6" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </button>

    <div class="locale-toggle__menu" hidden role="listbox" aria-label="{{ __('app.language.switch_label') }}" data-locale-panel>
        <button
            type="submit"
            name="locale"
            value="en"
            class="locale-toggle__option{{ $locale === 'en' ? ' is-active' : '' }}"
            role="option"
        >English</button>
        <button
            type="submit"
            name="locale"
            value="bn"
            class="locale-toggle__option{{ $locale === 'bn' ? ' is-active' : '' }}"
            role="option"
        >বাংলা</button>
    </div>
</form>
