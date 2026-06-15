@php
    $calc = $calculation ?? [];
    $steps = $calc['steps'] ?? [];
    $margins = $calc['margins'] ?? [];
@endphp

<section class="is-doc__calc print:hidden">
    <h2 class="is-doc__calc-title">How these figures are calculated</h2>
    <p class="is-doc__calc-intro">{{ $calc['source'] ?? 'Posted ledger activity for the selected period.' }}</p>

    <div class="is-doc__calc-steps">
        @foreach($steps as $step)
            <div class="is-doc__calc-step">
                <div class="is-doc__calc-step-head">
                    <span class="is-doc__calc-step-label">{{ $step['label'] ?? '' }}</span>
                    <span class="is-doc__calc-step-result">{{ $calc['currency'] ?? '' }} {{ $step['result'] ?? '' }}</span>
                </div>
                <p class="is-doc__calc-formula">{{ $step['formula'] ?? '' }}</p>
                <p class="is-doc__calc-expression">{{ $step['expression'] ?? '' }}</p>
            </div>
        @endforeach
    </div>

    @if(!empty($margins))
        <div class="is-doc__calc-margins">
            <h3 class="is-doc__calc-subtitle">Margins</h3>
            @foreach($margins as $margin)
                <div class="is-doc__calc-margin">
                    <span class="font-medium">{{ $margin['label'] ?? '' }}</span>
                    <span class="text-gray-500">{{ $margin['formula'] ?? '' }}</span>
                    <span class="font-semibold tabular-nums">{{ $margin['expression'] ?? '' }}</span>
                </div>
            @endforeach
        </div>
    @endif
</section>
