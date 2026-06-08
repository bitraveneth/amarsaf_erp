<div class="doc-sheet">
    @include('documents.partials.company-header', compact('company', 'payload', 'forPdf'))

    @if(!empty($payload['parties']))
        <div class="doc-grid">
            @foreach($payload['parties'] as $party)
                <div class="doc-grid-col">
                    <div class="doc-card">
                        <div class="doc-card-label">{{ $party['label'] ?? '' }}</div>
                        <div class="doc-card-name">{{ $party['name'] ?? '—' }}</div>
                        @foreach($party['lines'] ?? [] as $line)
                            <div class="doc-card-line">{{ $line }}</div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @if(!empty($payload['meta']))
        <table class="doc-meta-table">
            @foreach($payload['meta'] as $row)
                <tr>
                    <td>{{ $row[0] ?? '' }}</td>
                    <td>{{ $row[1] ?? '—' }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    @if(!empty($payload['columns']) && !empty($payload['rows']))
        <table class="doc-lines">
            <thead>
                <tr>
                    @foreach($payload['columns'] as $column)
                        <th>{{ $column }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($payload['rows'] as $rowIndex => $row)
                    <tr @class(['doc-lines-row--alt' => $rowIndex % 2 === 1])>
                        @foreach($row as $cell)
                            <td>{{ $cell }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if(!empty($payload['totals']))
        <div class="doc-totals-wrap">
            <table class="doc-totals">
                @foreach($payload['totals'] as $index => $row)
                    <tr>
                        <td>{{ $row[0] ?? '' }}</td>
                        <td>{{ $row[1] ?? '' }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    @endif

    @if(!empty($payload['notes']))
        <div class="doc-notes">
            <strong>{{ __('documents.labels.notes') ?? 'Notes' }}:</strong> {{ $payload['notes'] }}
        </div>
    @endif

    @if(!empty($payload['signatures']))
        <div class="doc-signatures">
            @foreach($payload['signatures'] as $signature)
                <div class="doc-signature">
                    <div class="doc-signature-line">{{ $signature[0] ?? '' }}</div>
                </div>
            @endforeach
        </div>
    @endif

    @if(!empty($payload['footer']))
        <div class="doc-footer">{{ $payload['footer'] }}</div>
    @endif
</div>
