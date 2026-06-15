@php
    $display = $appBrandDisplay ?? ['title' => $appBrandName ?? 'ERP', 'tagline' => null, 'full' => $appBrandName ?? 'ERP'];
    $monogram = mb_strtoupper(mb_substr($display['title'] ?: $display['full'], 0, 1));
@endphp

<div
    class="sidebar-brand"
    :class="$store.sidebar.isExpanded ? 'sidebar-brand--expanded' : 'sidebar-brand--collapsed'"
>
    <a
        href="{{ route('admin.dashboard') }}"
        data-tour="sidebar-brand"
        class="sidebar-brand__home"
        :title="@js($display['full'])"
    >
        <span x-show="!$store.sidebar.isExpanded" x-cloak class="sidebar-brand__monogram" aria-hidden="true">
            {{ $monogram }}
        </span>

        <span x-show="$store.sidebar.isExpanded" x-cloak class="sidebar-brand__logo-shell">
            <x-brand-mark
                variant="sidebar"
                name-mode="none"
                class="sidebar-brand__logo"
            />
        </span>
    </a>
</div>
