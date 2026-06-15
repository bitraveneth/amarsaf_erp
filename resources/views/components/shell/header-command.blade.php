@props([
    'items' => [],
])

@php
    $searchItems = collect($items)->values();
@endphp

<div class="shell-command" data-command-root>
    <label class="shell-command__field">
        <span class="sr-only">{{ __('app.search_placeholder') }}</span>
        <svg class="shell-command__field-icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M3.04175 9.37363C3.04175 5.87693 5.87711 3.04199 9.37508 3.04199C12.8731 3.04199 15.7084 5.87693 15.7084 9.37363C15.7084 12.8703 12.8731 15.7053 9.37508 15.7053C5.87711 15.7053 3.04175 12.8703 3.04175 9.37363ZM9.37508 1.54199C5.04902 1.54199 1.54175 5.04817 1.54175 9.37363C1.54175 13.6991 5.04902 17.2053 9.37508 17.2053C11.2674 17.2053 13.003 16.5344 14.357 15.4176L17.177 18.238C17.4699 18.5309 17.9448 18.5309 18.2377 18.238C18.5306 17.9451 18.5306 17.4703 18.2377 17.1774L15.418 14.3573C16.5365 13.0033 17.2084 11.2669 17.2084 9.37363C17.2084 5.04817 13.7011 1.54199 9.37508 1.54199Z" />
        </svg>
        <input
            type="search"
            class="shell-command__input"
            placeholder="{{ __('app.search_placeholder') }}"
            data-command-search
            data-tour="command-search"
            data-search-index='@json($searchItems)'
            data-entity-search-url="{{ route('admin.search.suggest') }}"
            autocomplete="off"
            spellcheck="false"
        />
        <span class="shell-command__kbd" data-command-shortcut aria-hidden="true">
            <span data-shortcut-mod>⌘</span>
            <span>K</span>
        </span>
        <div data-command-results class="shell-command__results hidden"></div>
    </label>
</div>
