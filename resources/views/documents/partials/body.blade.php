@php
    $copies = array_values(array_filter($payload['copies'] ?? [], fn ($label) => is_string($label) && $label !== ''));
    if ($copies === []) {
        $copies = [null];
    }
@endphp

@foreach($copies as $copyIndex => $copyLabel)
    @if($copyIndex > 0)
        <div class="doc-copy-break">{{ __('documents.labels.cut_here') }}</div>
    @endif
    <div @class(['doc-copy', 'doc-copy-next' => $copyIndex > 0])>
        @php
            $sheetPayload = $payload;
            $sheetPayload['copy_index'] = $copyIndex + 1;
            $sheetPayload['copy_count'] = count($copies);
            if (is_string($copyLabel) && $copyLabel !== '') {
                $sheetPayload['subtitle'] = $copyLabel;
            }
        @endphp
        @include(($sheetPayload['sheet'] ?? '') === 'invoice' ? 'documents.invoice.sheet' : 'documents.partials.sheet', [
            'company' => $company,
            'payload' => $sheetPayload,
            'forPdf' => $forPdf ?? false,
        ])
    </div>
@endforeach
