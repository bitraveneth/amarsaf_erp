@php
    use App\Helpers\MenuHelper;
    $menuGroups = MenuHelper::getMenuGroups();
    $currentPath = request()->path();
@endphp

<aside id="sidebar"
    class="sidebar fixed inset-y-0 left-0 z-40 flex flex-col border-r border-gray-200 bg-white shadow-theme-lg transition-transform duration-300 dark:border-gray-800 dark:bg-gray-900 xl:z-30 xl:shadow-none"
    x-data="{
        openSubmenus: {},
        init() {
            this.initializeActiveMenus();
        },
        initializeActiveMenus() {
            const currentPath = '{{ $currentPath }}';
            @foreach ($menuGroups as $groupIndex => $menuGroup)
                @foreach ($menuGroup['items'] as $itemIndex => $item)
                    @if (isset($item['subItems']))
                        @foreach ($item['subItems'] as $subItem)
                            if (currentPath === '{{ ltrim($subItem['path'], '/') }}' ||
                                window.location.pathname === '{{ $subItem['path'] }}') {
                                this.openSubmenus['{{ $groupIndex }}-{{ $itemIndex }}'] = true;
                            }
                        @endforeach
                    @endif
                @endforeach
            @endforeach
        },
        toggleSubmenu(groupIndex, itemIndex) {
            const key = groupIndex + '-' + itemIndex;
            const newState = !this.openSubmenus[key];
            if (newState) {
                this.openSubmenus = {};
            }
            this.openSubmenus[key] = newState;
        },
        isSubmenuOpen(groupIndex, itemIndex) {
            const key = groupIndex + '-' + itemIndex;
            return this.openSubmenus[key] || false;
        },
        isActive(path) {
            return window.location.pathname === path || '{{ $currentPath }}' === path.replace(/^\//, '');
        }
    }"
    :class="{
        'w-[290px]': $store.sidebar.isExpanded || $store.sidebar.isMobileOpen || $store.sidebar.isHovered,
        'w-[90px]': !$store.sidebar.isExpanded && !$store.sidebar.isHovered,
        'translate-x-0': $store.sidebar.isMobileOpen,
        '-translate-x-full xl:translate-x-0': !$store.sidebar.isMobileOpen
    }"
    @mouseenter="if (!$store.sidebar.isExpanded) $store.sidebar.setHovered(true)"
    @mouseleave="$store.sidebar.setHovered(false)">

    <div class="flex h-16 items-center gap-2 px-4">
        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-500 text-sm font-semibold text-white">
            SF
        </div>
        <div class="flex flex-col"
             x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen">
            <span class="text-sm font-semibold text-gray-900 dark:text-white">SAFERP</span>
            <span class="text-xs text-gray-500 dark:text-gray-400">ERP System</span>
        </div>
    </div>

    <nav class="flex-1 overflow-y-auto px-3 pb-6 pt-4 custom-scrollbar">
        <div class="mb-6 flex flex-col gap-4">
            @foreach ($menuGroups as $groupIndex => $menuGroup)
                <div>
                    <h2 class="mb-3 flex text-[11px] uppercase leading-[20px] tracking-[0.12em] text-gray-500/90"
                        x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen">
                        {{ $menuGroup['title'] }}
                    </h2>
                    <ul class="flex flex-col gap-1">
                        @foreach ($menuGroup['items'] as $itemIndex => $item)
                            <li>
                                @if (isset($item['subItems']))
                                    <button
                                        @click="toggleSubmenu({{ $groupIndex }}, {{ $itemIndex }})"
                                        class="menu-item menu-item-inactive group w-full">
                                        <span class="menu-item-icon"
                                              :class="isSubmenuOpen({{ $groupIndex }}, {{ $itemIndex }})
                                                  ? 'menu-item-icon-active'
                                                  : 'menu-item-icon-inactive'">
                                            {!! MenuHelper::getIconSvg($item['icon']) !!}
                                        </span>
                                        <span class="menu-item-text flex items-center gap-2"
                                              x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen">
                                            {{ $item['name'] }}
                                        </span>
                                        <svg class="ml-auto h-4 w-4 transition-transform"
                                             :class="{
                                                'rotate-180 text-brand-500': isSubmenuOpen({{ $groupIndex }}, {{ $itemIndex }}),
                                                'text-gray-500': !isSubmenuOpen({{ $groupIndex }}, {{ $itemIndex }})
                                             }"
                                             viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M5 8L10 13L15 8" stroke="currentColor" stroke-width="1.5"
                                                  stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </button>
                                    <div x-show="isSubmenuOpen({{ $groupIndex }}, {{ $itemIndex }}) && ($store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen)">
                                        <ul class="mt-2 space-y-1 pl-7">
                                            @foreach ($item['subItems'] as $subItem)
                                                <li>
                                                    <a href="{{ $subItem['path'] }}"
                                                       class="menu-dropdown-item"
                                                       :class="isActive('{{ $subItem['path'] }}')
                                                            ? 'menu-dropdown-item-active'
                                                            : 'menu-dropdown-item-inactive'">
                                                        {{ $subItem['name'] }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @else
                                    <a href="{{ $item['path'] }}"
                                       class="menu-item group"
                                       :class="isActive('{{ $item['path'] }}')
                                            ? 'menu-item-active'
                                            : 'menu-item-inactive'">
                                        <span class="menu-item-icon"
                                              :class="isActive('{{ $item['path'] }}')
                                                    ? 'menu-item-icon-active'
                                                    : 'menu-item-icon-inactive'">
                                            {!! MenuHelper::getIconSvg($item['icon']) !!}
                                        </span>
                                        <span class="menu-item-text"
                                              x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen">
                                            {{ $item['name'] }}
                                        </span>
                                    </a>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </nav>
</aside>
