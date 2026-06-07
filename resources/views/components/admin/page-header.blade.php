@props([
    'title',
    'subtitle' => null,
    'icon' => null,
    'variant' => 'module',
    'eyebrow' => null,
    'period' => null,
])

@php
    $titleClass = $variant === 'dashboard' ? 'erp-dash-h1' : 'erp-page-title';
@endphp

<div {{ $attributes->merge(['class' => 'erp-page-header']) }}>
    <div class="flex items-start gap-3">
        @if($icon)
            <x-admin.page-icon :name="$icon" />
        @endif
        <div>
            @if($eyebrow)
                <p class="erp-eyebrow">{{ $eyebrow }}</p>
            @endif
            <h1 @class([$titleClass, 'mt-1' => $eyebrow])>{{ $title }}</h1>
            @if($subtitle)
                <p class="erp-page-subtitle">{{ $subtitle }}</p>
            @endif
            @if($period)
                <p class="erp-caption mt-1">{{ $period }}</p>
            @endif
        </div>
    </div>

    @isset($actions)
        <div class="flex flex-wrap items-center gap-2">
            {{ $actions }}
        </div>
    @endisset
</div>
