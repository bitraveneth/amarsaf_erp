@props([
    'title' => 'Workspaces',
    'description' => null,
    'workspaces' => [],
])

<section {{ $attributes->merge(['class' => 'module-workspace']) }}>
    <div class="module-workspace__head">
        <div>
            <p class="module-workspace__eyebrow">{{ $title }}</p>
            @if($description)
                <p class="module-workspace__desc">{{ $description }}</p>
            @endif
        </div>
    </div>

    <div class="module-workspace__grid">
        @foreach($workspaces as $workspace)
            <a
                href="{{ $workspace['href'] }}"
                @class([
                    'module-workspace__card',
                    'module-workspace__card--' . ($workspace['tone'] ?? 'brand'),
                    'module-workspace__card--primary' => ! empty($workspace['primary']),
                ])
            >
                <div class="module-workspace__card-main">
                    <p class="module-workspace__card-title">{{ $workspace['title'] }}</p>
                    <p class="module-workspace__card-desc">{{ $workspace['description'] }}</p>
                </div>
                <div class="module-workspace__card-aside">
                    <p class="module-workspace__card-metric">{{ number_format($workspace['metric'] ?? 0) }}</p>
                    <p class="module-workspace__card-metric-label">{{ $workspace['metricLabel'] ?? '' }}</p>
                    <span class="module-workspace__card-arrow" aria-hidden="true">→</span>
                </div>
            </a>
        @endforeach
    </div>
</section>
