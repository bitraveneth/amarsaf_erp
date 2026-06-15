@props([
    'title',
    'empty' => 'No data for this period.',
    'currency' => null,
    'items' => [],
])

<div {{ $attributes->merge(['class' => 'erp-dash-rank']) }}>
    @if($title !== '')
        <h3 class="erp-dash-rank__title">{{ $title }}</h3>
    @endif

    @if(empty($items))
        <p class="erp-body text-gray-500 dark:text-gray-400">{{ $empty }}</p>
    @else
        <ul class="erp-dash-rank__list">
            @foreach($items as $index => $item)
                @php
                    $label = is_array($item) ? ($item['label'] ?? $item['name'] ?? '—') : (string) $item;
                    $value = is_array($item) ? ($item['value'] ?? $item['net'] ?? $item['net_sales'] ?? 0) : 0;
                    $meta = is_array($item) ? ($item['meta'] ?? $item['hint'] ?? null) : null;
                    $href = is_array($item) ? ($item['href'] ?? null) : null;
                    $formatted = $currency
                        ? $currency . ' ' . number_format((float) $value, 0)
                        : number_format((float) $value, 0);
                @endphp
                <li class="erp-dash-rank__item">
                    <span class="erp-dash-rank__index">{{ $index + 1 }}</span>
                    <div class="erp-dash-rank__content">
                        @if($href)
                            <a href="{{ $href }}" class="erp-dash-rank__label erp-link">{{ $label }}</a>
                        @else
                            <span class="erp-dash-rank__label">{{ $label }}</span>
                        @endif
                        @if($meta)
                            <span class="erp-dash-rank__meta">{{ $meta }}</span>
                        @endif
                    </div>
                    <span class="erp-dash-rank__value">{{ $formatted }}</span>
                </li>
            @endforeach
        </ul>
    @endif
</div>
