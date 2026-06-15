<div class="sidebar-rail-toggle print-hidden hidden shrink-0 border-t border-gray-200/80 p-2 dark:border-gray-800 xl:block">
    <button
        type="button"
        class="sidebar-rail-toggle__btn"
        :class="$store.sidebar.isExpanded ? 'sidebar-rail-toggle__btn--expanded' : 'sidebar-rail-toggle__btn--collapsed'"
        @click.stop="$store.sidebar.toggleExpanded()"
        :aria-label="$store.sidebar.isExpanded ? 'Collapse sidebar' : 'Expand sidebar'"
        :title="$store.sidebar.isExpanded ? 'Collapse sidebar' : 'Expand sidebar'"
    >
        <svg
            class="sidebar-rail-toggle__icon"
            :class="{ 'is-flipped': !$store.sidebar.isExpanded }"
            width="18"
            height="18"
            viewBox="0 0 20 20"
            fill="none"
            aria-hidden="true"
        >
            <path d="M12.5 5L7.5 10L12.5 15" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <span x-show="$store.sidebar.isExpanded" x-cloak class="sidebar-rail-toggle__label">Collapse</span>
        <span x-show="!$store.sidebar.isExpanded" x-cloak class="sr-only">Expand sidebar</span>
    </button>
</div>
