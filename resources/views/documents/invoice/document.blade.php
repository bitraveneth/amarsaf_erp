@php
    $columns = array_values($invoice['columns'] ?? []);
    $aligns = array_values($invoice['aligns'] ?? []);
    $widths = array_values($invoice['widths'] ?? []);
    $rows = array_values($invoice['rows'] ?? []);
    $totals = array_values($invoice['totals'] ?? []);
    $grand = $invoice['grand'] ?? null;
    $payments = array_values($invoice['payments'] ?? []);
    $notes = array_values($invoice['notes'] ?? []);
    $signatures = array_values($invoice['signatures'] ?? []);
    $signatureCount = count($signatures);
    $colCount = max(1, count($columns));
    $sumSpan = max(0, $colCount - min(2, $colCount));
    $title = (string) ($invoice['title'] ?? __('documents.invoice_sheet.title'));
    $alignClass = function (?string $align): string {
        return match ($align) {
            'right' => 'inv-right',
            'center' => 'inv-center',
            default => '',
        };
    };
@endphp

<div @class(['inv', 'inv-for-pdf' => $forPdf ?? false])>
    <table class="inv-head">
        <tr>
            <td style="width: 56%;">
                @if(!empty($logoSrc))
                    <img src="{{ $logoSrc }}" alt="" class="inv-logo">
                @endif
                @if(!empty($company['legal_name']))
                    <div class="inv-company">{{ $company['legal_name'] }}</div>
                @endif
            </td>
            <td style="width: 44%;">
                @foreach($contact as $line)
                    <div class="inv-contact-line"><span class="inv-dot"></span>{{ $line }}</div>
                @endforeach
            </td>
        </tr>
    </table>

    <table class="inv-intro">
        <tr>
            <td style="width: 56%;">
                <div class="inv-kicker">{{ $bill['label'] ?? __('documents.invoice_sheet.billed_to') }}</div>
                <div class="inv-bill-name">{{ $bill['name'] ?? '—' }}</div>
                @foreach($bill['lines'] ?? [] as $line)
                    <div class="inv-bill-line">{{ $line }}</div>
                @endforeach
            </td>
            <td style="width: 44%;">
                <div @class(['inv-title', 'inv-title-long' => mb_strlen($title) > 10])>{{ $title }}</div>
                @if(($payload['copy_count'] ?? 1) > 1 && !empty($payload['subtitle']))
                    <div class="inv-copy">{{ $payload['subtitle'] }}</div>
                @endif
                <table class="inv-meta" align="right">
                    @foreach($invoice['meta'] ?? [] as $row)
                        <tr>
                            <td class="lbl">{{ $row['label'] }}:</td>
                            <td class="val">{{ $row['value'] }}</td>
                        </tr>
                    @endforeach
                </table>
            </td>
        </tr>
    </table>

    <table class="inv-lines">
        <thead>
            <tr>
                @foreach($columns as $index => $column)
                    <th class="{{ $alignClass($aligns[$index] ?? null) }}" @if(!empty($widths[$index])) style="width: {{ $widths[$index] }};" @endif>{{ $column }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr @class(['inv-alt' => $loop->iteration % 2 === 0])>
                    @foreach($columns as $index => $column)
                        <td class="{{ $alignClass($aligns[$index] ?? null) }}">{{ $row[$index] ?? '' }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $colCount }}" class="inv-center">{{ __('documents.labels.no_lines') }}</td>
                </tr>
            @endforelse
            @foreach($totals as $total)
                <tr @class(['inv-sum', 'inv-sum-alt' => $loop->first])>
                    @if($sumSpan > 0)
                        <td colspan="{{ $sumSpan }}"></td>
                    @endif
                    @if($colCount === 1)
                        <td class="lbl">{{ $total['label'] }} {{ $total['amount'] }}</td>
                    @else
                        <td class="lbl">{{ $total['label'] }}</td>
                        <td class="val">{{ $total['amount'] }}</td>
                    @endif
                </tr>
            @endforeach
            @if(!empty($grand))
                <tr class="inv-grand">
                    @if($sumSpan > 0)
                        <td colspan="{{ $sumSpan }}"></td>
                    @endif
                    @if($colCount === 1)
                        <td class="inv-right">{{ $grand['label'] }} {{ $grand['amount'] }}</td>
                    @else
                        <td class="inv-right">{{ $grand['label'] }}</td>
                        <td class="inv-right">{{ $grand['amount'] }}</td>
                    @endif
                </tr>
            @endif
        </tbody>
    </table>

    <table class="inv-bottom">
        <tr>
            <td style="width: {{ $signatureCount > 1 ? '100%' : '62%' }};{{ $signatureCount > 1 ? '' : ' padding-right: 16px;' }}">
                @if($payments !== [])
                    <div class="inv-section">{{ $invoice['details_label'] ?? __('documents.labels.details') }}</div>
                    <table class="inv-pay">
                        @foreach($payments as $row)
                            <tr @class(['inv-alt' => $loop->iteration % 2 === 0])>
                                <td class="lbl">{{ $row['label'] }}:</td>
                                <td class="val">{{ $row['value'] }}</td>
                            </tr>
                        @endforeach
                    </table>
                @endif

                @if($notes !== [])
                    <div class="inv-notes">
                        <div class="inv-section">{{ __('documents.invoice_sheet.notes') }}</div>
                        <ol>
                            @foreach($notes as $note)
                                <li>{{ $note }}</li>
                            @endforeach
                        </ol>
                    </div>
                @endif
            </td>
            @if($signatureCount <= 1)
                <td class="inv-sign-cell" style="width: 38%;">
                    <table class="inv-sign">
                        <tr><td class="inv-sign-line"></td></tr>
                        <tr><td class="inv-sign-name">{{ $signatures[0] ?? __('documents.invoice_sheet.signatory_fallback') }}</td></tr>
                    </table>
                </td>
            @endif
        </tr>
    </table>

    @if($signatureCount > 1)
        <table class="inv-signs">
            <tr>
                @foreach($signatures as $label)
                    <td style="width: {{ round(100 / $signatureCount, 2) }}%;">
                        <div class="inv-sign-line"></div>
                        <div class="inv-sign-name">{{ $label }}</div>
                    </td>
                @endforeach
            </tr>
        </table>
    @endif

    @if(!empty($invoice['footer']))
        <div class="inv-footer">{{ $invoice['footer'] }}</div>
    @endif
</div>
