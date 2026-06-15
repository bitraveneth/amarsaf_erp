<div {{ $attributes->merge(['class' => 'header-utilities']) }}>
    <x-layout.header-date compact="toolbar" class="header-utilities__date hidden lg:flex" />

    <span class="header-utilities__divider hidden lg:block" aria-hidden="true"></span>

    <x-locale-toggle class="locale-toggle--header" />

    <span class="header-utilities__divider" aria-hidden="true"></span>

    <x-layout.header-theme-toggle />
</div>
