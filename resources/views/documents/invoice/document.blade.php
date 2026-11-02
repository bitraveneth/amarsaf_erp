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
    $title = (string) ($invoice['title'] ?? $title ?? __('documents.invoice_sheet.title'));
    $generatedLabel = $generatedLabel ?? ($invoice['generated'] ?? null);
    $heading = function (string $column): string {
        return match (strtolower(trim($column))) {
            '#', 'sl' => __('documents.invoice_sheet.sl'),
            'particulars', 'product', 'description' => __('documents.invoice_sheet.product'),
            'qty', 'quantity' => __('documents.invoice_sheet.quantity'),
            'rate', 'unit price' => __('documents.invoice_sheet.rate'),
            'amount', 'line total' => __('documents.invoice_sheet.amount'),
            default => $column,
        };
    };
    $alignClass = function (?string $align): string {
        return match ($align) {
            'right' => 'inv-right',
            'center' => 'inv-center',
            default => '',
        };
    };
@endphp

<div @class(['inv', 'inv-for-pdf' => $forPdf ?? false])>
    @include('documents.invoice.letterhead')

    <table class="inv-intro">
        <tr>
            <td style="width: 56%;">
                <div class="inv-kicker">{{ $bill['label'] ?? __('documents.invoice_sheet.billed_to') }}</div>
                <div class="inv-bill-name">{{ $bill['name'] ?? '—' }}</div>
                <table class="inv-bill">
                    @if(!empty($bill['phone']))
                        <tr>
                            <td class="lbl">{{ __('documents.invoice_sheet.phone') }}:</td>
                            <td>{{ $bill['phone'] }}</td>
                        </tr>
                    @endif
                    @if(!empty($bill['email']))
                        <tr>
                            <td class="lbl">{{ __('documents.invoice_sheet.email') }}:</td>
                            <td>{{ $bill['email'] }}</td>
                        </tr>
                    @endif
                    @if(!empty($bill['address']))
                        <tr>
                            <td class="lbl">{{ __('documents.invoice_sheet.address') }}:</td>
                            <td>{{ $bill['address'] }}</td>
                        </tr>
                    @endif
                </table>
                @foreach($bill['lines'] ?? [] as $line)
                    <div class="inv-bill-line">{{ $line }}</div>
                @endforeach
            </td>
            <td style="width: 44%;">
                @if(!empty($invoice['meta']))
                    <div class="inv-kicker">{{ $invoice['details_label'] ?? __('documents.labels.order_details') }}</div>
                    <table class="inv-details">
                        @foreach($invoice['meta'] as $row)
                            <tr @class(['inv-alt' => $loop->iteration % 2 === 0])>
                                <td class="lbl">{{ $row['label'] }}:</td>
                                <td class="val">{{ $row['value'] }}</td>
                            </tr>
                        @endforeach
                    </table>
                @endif
            </td>
        </tr>
    </table>

    <table class="inv-lines">
        <thead>
            <tr>
                @foreach($columns as $index => $column)
                    <th class="inv-center" @if(!empty($widths[$index])) style="width: {{ $widths[$index] }};" @endif>{{ $heading($column) }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr @class(['inv-alt' => $loop->iteration % 2 === 0])>
                    @foreach($columns as $index => $column)
                        <td class="inv-center">{{ $row[$index] ?? '' }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $colCount }}" class="inv-center">{{ __('documents.labels.no_lines') }}</td>
                </tr>
            @endforelse
            @foreach($totals as $total)
                <tr class="inv-sum">
                    @if($colCount === 1)
                        <td class="val">{{ $total['label'] }} {{ $total['amount'] }}</td>
                    @else
                        <td class="lbl" colspan="{{ $colCount - 1 }}">{{ $total['label'] }}</td>
                        <td class="val">{{ $total['amount'] }}</td>
                    @endif
                </tr>
            @endforeach
            @if(!empty($grand))
                <tr class="inv-grand">
                    @if($colCount === 1)
                        <td class="val">{{ $grand['label'] }} {{ $grand['amount'] }}</td>
                    @else
                        <td class="lbl" colspan="{{ $colCount - 1 }}">{{ __('documents.invoice_sheet.total') }}</td>
                        <td class="val">{{ $grand['amount'] }}</td>
                    @endif
                </tr>
            @endif
        </tbody>
    </table>

    <table class="inv-bottom">
        <tr>
            <td style="width: {{ $signatureCount > 1 ? '100%' : '62%' }};{{ $signatureCount > 1 ? '' : ' padding-right: 16px;' }}">
                @if($payments !== [])
                    <div class="inv-section">{{ __('documents.labels.details') }}</div>
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

    @php
        $inquiryDocument = strtolower($title);
        $inquiryPhone = $company['phone'] ?? null;
        $inquiry = is_string($inquiryPhone) && $inquiryPhone !== ''
            ? __('documents.invoice_sheet.inquiry_document', ['document' => $inquiryDocument, 'phone' => $inquiryPhone])
            : __('documents.invoice_sheet.inquiry_document_plain', ['document' => $inquiryDocument]);
    @endphp
    <div class="inv-footer">{{ $inquiry }}</div>
</div>
