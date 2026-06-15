@props([
    'items' => [],
    'currencyCode' => 'BDT',
])

@if(count($items) > 0)
    <section {{ $attributes->merge(['class' => 'dash-insights-section']) }}>
        <x-dashboard.section-header
            title="Month over month"
            description="How this month compares to last month."
            class="mb-4"
        />

        <div class="dash-month-compare">
            @foreach($items as $item)
                @php
                    $direction = $item['direction'] ?? 'flat';
                    $delta = $item['delta'] ?? 0;
                    $current = $item['current'] ?? 0;
                    $display = ! empty($item['is_currency'])
                        ? $currencyCode . ' ' . number_format((float) $current, 0)
                        : number_format((float) $current, 0);
                @endphp
                <article class="dash-month-compare__item">
                    <p class="dash-month-compare__label">{{ $item['label'] ?? 'Metric' }}</p>
                    <p class="dash-month-compare__value">{{ $display }}</p>
                    <p class="dash-month-compare__delta dash-month-compare__delta--{{ $direction }}">
                        @if($direction === 'up')
                            ↑ +{{ number_format(abs($delta), 1) }}%
                        @elseif($direction === 'down')
                            ↓ {{ number_format($delta, 1) }}%
                        @else
                            — flat
                        @endif
                        <span class="dash-month-compare__hint">vs last month</span>
                    </p>
                </article>
            @endforeach
        </div>
    </section>
@endif
