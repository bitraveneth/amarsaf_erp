@props([
    'vanilla' => false,
])

<div
    {{ $attributes->merge(['class' => 'header-theme-toggle']) }}
    role="group"
    aria-label="{{ __('app.theme.switch_label') }}"
    @unless($vanilla) x-data @endunless
>
    <button
        type="button"
        class="header-theme-toggle__btn"
        data-theme="light"
        @if($vanilla)
            aria-pressed="{{ false }}"
            onclick="window.erpTheme.applyTheme('light'); window.erpTheme.setStoredTheme('light');"
        @else
            :aria-pressed="$store.theme.theme === 'light'"
            @click="$store.theme.setTheme('light')"
        @endif
    >
        <svg class="header-theme-toggle__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <circle cx="12" cy="12" r="4"/>
            <path d="M12 2v2M12 20v2M4 12H2M20 12h-2M19.07 4.93l-1.41 1.41M6.34 17.66l-1.41 1.41M19.07 19.07l-1.41-1.41M6.34 6.34L4.93 4.93" stroke-linecap="round"/>
        </svg>
        <span class="header-theme-toggle__label">{{ __('app.theme.light_short') }}</span>
    </button>
    <button
        type="button"
        class="header-theme-toggle__btn"
        data-theme="dark"
        @if($vanilla)
            aria-pressed="{{ false }}"
            onclick="window.erpTheme.applyTheme('dark'); window.erpTheme.setStoredTheme('dark');"
        @else
            :aria-pressed="$store.theme.theme === 'dark'"
            @click="$store.theme.setTheme('dark')"
        @endif
    >
        <svg class="header-theme-toggle__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <path d="M12 3a9 9 0 109 9c0-1.2-.2-2.4-.6-3.5A7 7 0 0112 3z" stroke-linecap="round"/>
        </svg>
        <span class="header-theme-toggle__label">{{ __('app.theme.dark_short') }}</span>
    </button>
</div>
