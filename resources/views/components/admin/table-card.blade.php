@props([
    'title' => null,
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'erp-table-card']) }}>
    @if($title)
        <div class="erp-table-card-header">
            <h2 class="erp-table-card-title">{{ $title }}</h2>
            @if($description)
                <p class="erp-table-card-description">{{ $description }}</p>
            @endif
        </div>
    @endif
    <div class="erp-table-wrap">
        {{ $slot }}
    </div>
</div>
