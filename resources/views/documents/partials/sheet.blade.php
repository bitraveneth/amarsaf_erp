@php
    $columns = $payload['columns'] ?? [];
    $rows = $payload['rows'] ?? [];
    $aligns = $payload['aligns'] ?? [];
    $widths = $payload['widths'] ?? [];
    $columnAlign = function (int $index) use ($aligns, $rows) {
        if (! empty($aligns[$index])) {
            return $aligns[$index];
        }

        $sample = trim((string) ($rows[0][$index] ?? ''));

        return preg_match('/^-?[\d,]+(?:\.\d+)?(?:\s+(?:Dr|Cr|BDT))?$/i', $sample) === 1
            ? 'right'
            : 'left';
    };
    $cellAlign = function (int $index, string $text) use ($aligns) {
        if (! empty($aligns[$index])) {
            return $aligns[$index];
        }

        return preg_match('/^-?[\d,]+(?:\.\d+)?(?:\s+(?:Dr|Cr|BDT))?$/i', trim($text)) === 1
            ? 'right'
            : 'left';
    };
@endphp
<div class="doc-sheet">
    @include('documents.partials.company-header', ['company' => $company, 'payload' => $payload, 'forPdf' => $forPdf ?? false])

    @if(!empty($payload['parties']) || !empty($payload['meta']))
        <table class="doc-info">
            <tr>
                @if(!empty($payload['parties']))
                    <td class="doc-info-parties">
                        <table class="doc-party-table">
                            <tr>
                                @foreach($payload['parties'] as $party)
                                    <td>
                                        <div class="doc-card">
                                            <div class="doc-card-label">{{ $party['label'] ?? '' }}</div>
                                            <div class="doc-card-name">{{ $party['name'] ?? '—' }}</div>
                                            @foreach($party['lines'] ?? [] as $line)
                                                <div class="doc-card-line">{{ $line }}</div>
                                            @endforeach
                                        </div>
                                    </td>
                                @endforeach
                            </tr>
                        </table>
                    </td>
                @endif
                @if(!empty($payload['meta']))
                    <td class="doc-info-meta">
                        <table class="doc-meta-table">
                            @foreach($payload['meta'] as $metaRow)
                                <tr>
                                    <td>{{ $metaRow[0] ?? '' }}</td>
                                    <td>{{ $metaRow[1] ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </table>
                    </td>
                @endif
            </tr>
        </table>
    @endif

    @if(!empty($payload['intro']))
        <table class="doc-bands">
            <tr>
                @foreach($payload['intro'] as $block)
                    <td>
                        <div class="doc-card-label">{{ $block['label'] ?? '' }}</div>
                        @foreach($block['lines'] ?? [] as $line)
                            <div class="doc-card-line">{{ $line }}</div>
                        @endforeach
                    </td>
                @endforeach
            </tr>
        </table>
    @endif

    @if($columns !== [])
        <table class="doc-lines">
            <thead>
                <tr>
                    @foreach($columns as $index => $column)
                        <th class="doc-align-{{ $columnAlign($index) }}" @if(!empty($widths[$index])) style="width: {{ $widths[$index] }}" @endif>{{ $column }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $rowIndex => $row)
                    <tr @class(['doc-lines-row--alt' => $rowIndex % 2 === 1])>
                        @foreach($row as $cellIndex => $cell)
                            @php $text = is_scalar($cell) || $cell === null ? (string) ($cell ?? '') : ''; @endphp
                            <td class="doc-align-{{ $cellAlign((int) $cellIndex, $text) }}">{{ $text }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td class="doc-empty" colspan="{{ max(count($columns), 1) }}">{{ $payload['empty_text'] ?? __('documents.labels.no_lines') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif

    @if(!empty($payload['notes']) || !empty($payload['totals']))
        <table class="doc-summary">
            <tr>
                <td class="doc-summary-notes">
                    @if(!empty($payload['notes']))
                        <div class="doc-notes">
                            <strong>{{ __('documents.labels.notes') }}</strong>
                            {{ $payload['notes'] }}
                        </div>
                    @endif
                </td>
                @if(!empty($payload['totals']))
                    <td class="doc-summary-totals">
                        <table class="doc-totals">
                            @foreach($payload['totals'] as $totalRow)
                                <tr>
                                    <td>{{ $totalRow[0] ?? '' }}</td>
                                    <td>{{ $totalRow[1] ?? '' }}</td>
                                </tr>
                            @endforeach
                        </table>
                    </td>
                @endif
            </tr>
        </table>
    @endif

    @if(!empty($payload['blocks']))
        <table class="doc-bands">
            <tr>
                @foreach($payload['blocks'] as $block)
                    <td>
                        <div class="doc-card-label">{{ $block['label'] ?? '' }}</div>
                        @foreach($block['lines'] ?? [] as $line)
                            <div class="doc-card-line">{{ $line }}</div>
                        @endforeach
                    </td>
                @endforeach
            </tr>
        </table>
    @endif

    @if(!empty($payload['signatures']))
        <table class="doc-signatures">
            <tr>
                @foreach($payload['signatures'] as $signature)
                    <td>
                        <div class="doc-signature-line">{{ $signature[0] ?? '' }}</div>
                    </td>
                @endforeach
            </tr>
        </table>
    @endif

    @if(!empty($payload['footer']) || !empty($payload['generated_at']))
        <div class="doc-footer">
            @if(!empty($payload['footer']))
                <div>{{ $payload['footer'] }}</div>
            @endif
            <div class="doc-generated">
                {{ __('documents.labels.generated') }} {{ optional($payload['generated_at'] ?? now())->format('d M Y, H:i') }}
            </div>
        </div>
    @endif
</div>
