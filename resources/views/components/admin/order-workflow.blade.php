@props([
    'type' => 'purchase',
    'step' => 1,
    'inProgress' => false,
    'variant' => 'hero',
    'label' => null,
])

@php
    $presets = [
        'purchase' => [
            'label' => 'Procurement flow',
            'steps' => [
                ['label' => 'Draft', 'hint' => 'Create PO'],
                ['label' => 'Approve', 'hint' => 'Confirm order'],
                ['label' => 'GRN', 'hint' => 'Receive stock'],
            ],
        ],
        'sales' => [
            'label' => 'Sales flow',
            'steps' => [
                ['label' => 'Draft', 'hint' => 'Create order'],
                ['label' => 'Confirm', 'hint' => 'Reserve stock'],
                ['label' => 'Pick', 'hint' => 'Pull from shelf'],
                ['label' => 'Pack', 'hint' => 'Ready to ship'],
                ['label' => 'Deliver', 'hint' => 'POD complete'],
            ],
        ],
        'delivery' => [
            'label' => 'Delivery flow',
            'steps' => [
                ['label' => 'Scheduled', 'hint' => 'Queued'],
                ['label' => 'Loaded', 'hint' => 'On vehicle'],
                ['label' => 'Transit', 'hint' => 'On route'],
                ['label' => 'POD', 'hint' => 'Complete'],
            ],
        ],
        'bom' => [
            'label' => 'Recipe setup flow',
            'steps' => [
                ['label' => 'Product', 'hint' => 'Finished good'],
                ['label' => 'Components', 'hint' => 'Materials per unit'],
                ['label' => 'Review', 'hint' => 'Cost & quantities'],
                ['label' => 'Activate', 'hint' => 'Ready for production'],
            ],
        ],
        'manufacturing' => [
            'label' => 'Manufacturing flow',
            'steps' => [
                ['label' => 'Recipe', 'hint' => 'Active BOM'],
                ['label' => 'Batch', 'hint' => 'Lot / trace ID'],
                ['label' => 'Produce', 'hint' => 'Run quantity'],
                ['label' => 'QC', 'hint' => 'Approve lot'],
                ['label' => 'Stock', 'hint' => 'Finished goods in'],
            ],
        ],
    ];

    $preset = $presets[$type] ?? $presets['purchase'];
    $steps = $preset['steps'];
    $flowLabel = $label ?? $preset['label'];
    $activeStep = min(max((int) $step, 1), count($steps) + 1);
    $allComplete = $activeStep > count($steps);
    $isHero = $variant === 'hero';
    $isForm = $variant === 'form';
    $isProcurement = $variant === 'procurement';
    $isMini = $variant === 'mini';
    $isDash = $variant === 'dash';

    $flowClass = match (true) {
        $isHero => 'erp-order-flow--hero',
        $isForm => 'erp-order-flow--form',
        $isProcurement => 'erp-order-flow--procurement',
        $isDash => 'erp-order-flow--dash',
        default => 'erp-order-flow--mini',
    };
@endphp

<nav {{ $attributes->merge(['class' => 'erp-order-flow ' . $flowClass]) }}
     aria-label="{{ $flowLabel }}">
    @if($isHero || $isForm || $isProcurement)
        <div class="erp-order-flow__head">
            <p class="erp-order-flow__eyebrow">{{ $flowLabel }}</p>
            <p class="erp-order-flow__hint">Track where this order sits in your process</p>
        </div>
    @elseif($isMini)
        <p class="erp-order-flow__mini-label">{{ $flowLabel }}</p>
    @endif

    <ol @class([
        'erp-order-flow__track',
        'erp-order-flow__track--balanced' => $isProcurement,
    ])>
        @foreach($steps as $index => $stepDef)
            @php
                $num = $index + 1;
                $isComplete = $allComplete || $activeStep > $num;
                $isActive = ! $allComplete && $activeStep === $num;
                $isPulse = $isActive && $inProgress;
                $connectorFilled = $allComplete || $activeStep > $num;
            @endphp

            @if($isProcurement && $index > 0)
                @php
                    $bridgeFilled = $allComplete || $activeStep > $index;
                @endphp
                <li class="erp-order-flow__bridge" aria-hidden="true">
                    <div class="erp-order-flow__connector">
                        <span @class(['erp-order-flow__connector-fill', 'is-filled' => $bridgeFilled])></span>
                    </div>
                </li>
            @endif

            <li @class([
                'erp-order-flow__step',
                'erp-order-flow__step--centered' => $isProcurement,
            ])>
                @if($isProcurement)
                    <div class="erp-order-flow__node-stack">
                        <div @class([
                            'erp-order-flow__node',
                            'is-complete' => $isComplete,
                            'is-active' => $isActive && ! $isPulse,
                            'is-pulse' => $isPulse,
                            'is-upcoming' => ! $isComplete && ! $isActive,
                        ])>
                            @if($isComplete)
                                <svg class="erp-order-flow__check" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                            @else
                                <span>{{ $num }}</span>
                            @endif
                        </div>

                        <div class="erp-order-flow__copy">
                            <p @class([
                                'erp-order-flow__title',
                                'is-active' => $isActive,
                                'is-complete' => $isComplete,
                            ])>{{ $stepDef['label'] }}</p>
                            <p class="erp-order-flow__sub">{{ $stepDef['hint'] }}</p>
                        </div>
                    </div>
                @else
                    <div class="erp-order-flow__step-row">
                        <div class="erp-order-flow__node-stack">
                            <div @class([
                                'erp-order-flow__node',
                                'is-complete' => $isComplete,
                                'is-active' => $isActive && ! $isPulse,
                                'is-pulse' => $isPulse,
                                'is-upcoming' => ! $isComplete && ! $isActive,
                            ])>
                                @if($isComplete)
                                    <svg class="erp-order-flow__check" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                @else
                                    <span>{{ $num }}</span>
                                @endif
                            </div>

                            <div class="erp-order-flow__copy">
                                <p @class([
                                    'erp-order-flow__title',
                                    'is-active' => $isActive,
                                    'is-complete' => $isComplete,
                                ])>{{ $stepDef['label'] }}</p>
                                @if($isHero || $isForm || $isDash)
                                    <p class="erp-order-flow__sub">{{ $stepDef['hint'] }}</p>
                                @endif
                            </div>
                        </div>

                        @if(! $loop->last)
                            <div class="erp-order-flow__connector" aria-hidden="true">
                                <span @class(['erp-order-flow__connector-fill', 'is-filled' => $connectorFilled])></span>
                            </div>
                        @endif
                    </div>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
