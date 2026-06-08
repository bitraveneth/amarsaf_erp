@props([
    'title',
    'subtitle' => null,
    'backUrl' => null,
    'backLabel' => 'Back',
])

<header class="erp-order-toolbar">
    <div class="erp-order-toolbar__lead">
        @if($backUrl)
            <a href="{{ $backUrl }}" class="erp-order-toolbar__back">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                {{ $backLabel }}
            </a>
        @endif

        <div class="erp-order-toolbar__main">
            <h1 class="erp-order-toolbar__title">{{ $title }}</h1>
            @if($subtitle)
                <p class="erp-order-toolbar__subtitle">{{ $subtitle }}</p>
            @endif
        </div>
    </div>

    @isset($actions)
        <div class="erp-order-toolbar__actions">{{ $actions }}</div>
    @endisset
</header>
