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
                ['label' => 'Confirm', 'hint' => 'Agent OK'],
                ['label' => 'Fulfill', 'hint' => 'Pick & pack'],
                ['label' => 'Deliver', 'hint' => 'POD complete'],
            ],
        ],
    ];

    $preset = $presets[$type] ?? $presets['purchase'];
    $steps = $preset['steps'];
    $flowLabel = $label ?? $preset['label'];
    $activeStep = min(max((int) $step, 1), count($steps) + 1);
    $allComplete = $activeStep > count($steps);
    $isHero = $variant === 'hero';
    $isMini = $variant === 'mini';
    $isDash = $variant === 'dash';

    $flowClass = match (true) {
        $isHero => 'erp-order-flow--hero',
        $isDash => 'erp-order-flow--dash',
        default => 'erp-order-flow--mini',
    };
@endphp

<nav {{ $attributes->merge(['class' => 'erp-order-flow ' . $flowClass]) }}
     aria-label="{{ $flowLabel }}">
    @if($isHero)
        <div class="erp-order-flow__head">
            <p class="erp-order-flow__eyebrow">{{ $flowLabel }}</p>
            <p class="erp-order-flow__hint">Track where this order sits in your process</p>
        </div>
    @elseif($isMini)
        <p class="erp-order-flow__mini-label">{{ $flowLabel }}</p>
    @endif

    <ol class="erp-order-flow__track">
        @foreach($steps as $index => $stepDef)
            @php
                $num = $index + 1;
                $isComplete = $allComplete || $activeStep > $num;
                $isActive = ! $allComplete && $activeStep === $num;
                $isPulse = $isActive && $inProgress;
                $connectorFilled = $allComplete || $activeStep > $index;
            @endphp

            <li class="erp-order-flow__step">
                @if($index > 0)
                    <div class="erp-order-flow__connector" aria-hidden="true">
                        <span @class(['erp-order-flow__connector-fill', 'is-filled' => $connectorFilled])></span>
                    </div>
                @endif

                <div class="erp-order-flow__node-wrap">
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
                        @if($isHero || $isDash)
                            <p class="erp-order-flow__sub">{{ $stepDef['hint'] }}</p>
                        @endif
                    </div>
                </div>
            </li>
        @endforeach
    </ol>
</nav>
