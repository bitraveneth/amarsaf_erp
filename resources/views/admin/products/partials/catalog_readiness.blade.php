@php
    $gaps = $product?->catalogReadinessGaps() ?? [];
    $toneClasses = [
        'warning' => 'bg-amber-50 text-amber-800 ring-amber-200/80 dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-500/20',
        'neutral' => 'bg-gray-100 text-gray-700 ring-gray-200/80 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-700',
        'success' => 'bg-success-50 text-success-700 ring-success-200/80 dark:bg-success-500/10 dark:text-success-300 dark:ring-success-500/20',
    ];
@endphp

@if($product && filled($product->id))
    @if($product->isCatalogReady())
        <div class="flex flex-wrap items-center gap-2 rounded-xl border border-success-200 bg-success-50/60 px-4 py-3 dark:border-success-500/25 dark:bg-success-500/5">
            <span class="inline-flex items-center rounded-full bg-success-100 px-2.5 py-0.5 text-xs font-medium text-success-700 ring-1 ring-inset ring-success-200 dark:bg-success-500/15 dark:text-success-300 dark:ring-success-500/25">
                Ready for sale
            </span>
            <span class="text-xs text-success-800 dark:text-success-300">Specs, barcode, trade price, and status look complete.</span>
        </div>
    @elseif($gaps !== [])
        <div class="rounded-xl border border-amber-200 bg-amber-50/50 px-4 py-3 dark:border-amber-500/25 dark:bg-amber-500/5">
            <p class="text-xs font-medium text-amber-900 dark:text-amber-200">Needs attention before selling</p>
            <div class="mt-2 flex flex-wrap gap-2">
                @foreach($gaps as $gap)
                    @php($classes = $toneClasses[$gap['tone']] ?? $toneClasses['neutral'])
                    @if($gap['href'])
                        <a href="{{ $gap['href'] }}"
                           class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset {{ $classes }}">
                            {{ $gap['label'] }} →
                        </a>
                    @elseif($gap['anchor'])
                        <a href="#{{ $gap['anchor'] }}"
                           class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset {{ $classes }}">
                            {{ $gap['label'] }}
                        </a>
                    @else
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset {{ $classes }}">
                            {{ $gap['label'] }}
                        </span>
                    @endif
                @endforeach
            </div>
        </div>
    @endif
@endif
