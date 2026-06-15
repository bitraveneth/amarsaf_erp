<div {{ $attributes->merge(['class' => 'shell-header__utilities']) }}>
    <x-locale-toggle class="locale-toggle--header" />

    <span class="shell-header__utility-divider" aria-hidden="true"></span>

    <x-layout.header-theme-toggle />
</div>
