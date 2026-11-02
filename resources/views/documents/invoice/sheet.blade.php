@php
    $invoice = $payload['invoice'] ?? [];
    $bill = $invoice['bill_to'] ?? [];
    $lines = $invoice['lines'] ?? [];
    $totals = $invoice['totals'] ?? [];
    $grand = $invoice['grand'] ?? ['label' => __('documents.invoice_sheet.total'), 'amount' => ''];
    $payments = $invoice['payments'] ?? [];
    $notes = $invoice['notes'] ?? [];
    $signatory = $invoice['signatory'] ?? [];
    $contact = array_values(array_filter($invoice['contact'] ?? []));
    $fullMark = public_path('images/brand/saf-logo-full-purple.png');
    $usingDefaultLogo = ! empty($company['logo_path'])
        && is_file($fullMark)
        && realpath((string) $company['logo_path']) === realpath(public_path('images/brand/saf-logo.png'));
    if ($usingDefaultLogo) {
        $logoSrc = ($forPdf ?? false)
            ? \App\Support\PdfDocumentBuilder::fileUri($fullMark)
            : asset('images/brand/saf-logo-full-purple.png');
    } elseif (($forPdf ?? false) && ! empty($company['logo_file_uri'])) {
        $logoSrc = $company['logo_file_uri'];
    } else {
        $logoSrc = $company['logo_url'] ?? null;
    }
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
                <div class="inv-kicker">{{ __('documents.invoice_sheet.billed_to') }}</div>
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
            </td>
            <td style="width: 44%;">
                <div class="inv-title">{{ __('documents.invoice_sheet.title') }}</div>
                <table class="inv-meta" align="right">
                    <tr>
                        <td class="lbl">{{ __('documents.invoice_sheet.invoice_no') }}:</td>
                        <td class="val">{{ $invoice['number'] ?? ($payload['number'] ?? '—') }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">{{ __('documents.invoice_sheet.date_issued') }}:</td>
                        <td class="val">{{ $invoice['issued'] ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">{{ __('documents.invoice_sheet.due_date') }}:</td>
                        <td class="val">{{ $invoice['due'] ?? '—' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="inv-lines">
        <thead>
            <tr>
                <th style="width: 8%;" class="inv-center">{{ __('documents.invoice_sheet.sl') }}</th>
                <th>{{ __('documents.invoice_sheet.product') }}</th>
                <th style="width: 16%;" class="inv-center">{{ __('documents.invoice_sheet.quantity') }}</th>
                <th style="width: 16%;" class="inv-right">{{ __('documents.invoice_sheet.rate') }}</th>
                <th style="width: 18%;" class="inv-right">{{ __('documents.invoice_sheet.amount') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($lines as $line)
                <tr @class(['inv-alt' => $loop->iteration % 2 === 0])>
                    <td class="inv-center">{{ $line['sl'] }}</td>
                    <td>{{ $line['description'] }}</td>
                    <td class="inv-center">{{ $line['qty'] }}</td>
                    <td class="inv-right">{{ $line['rate'] }}</td>
                    <td class="inv-right">{{ $line['amount'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="inv-center">{{ __('documents.labels.no_lines') }}</td>
                </tr>
            @endforelse
            @foreach($totals as $total)
                <tr @class(['inv-sum', 'inv-sum-alt' => $loop->first])>
                    <td colspan="3"></td>
                    <td class="lbl">{{ $total['label'] }}</td>
                    <td class="val">{{ $total['amount'] }}</td>
                </tr>
            @endforeach
            <tr class="inv-grand">
                <td colspan="3"></td>
                <td class="inv-right">{{ $grand['label'] }}</td>
                <td class="inv-right">{{ $grand['amount'] }}</td>
            </tr>
        </tbody>
    </table>

    <table class="inv-bottom">
        <tr>
            <td style="width: 62%; padding-right: 16px;">
                <div class="inv-section">{{ __('documents.invoice_sheet.payment_details') }}</div>
                <table class="inv-pay">
                    @foreach($payments as $row)
                        <tr @class(['inv-alt' => $loop->iteration % 2 === 0])>
                            <td class="lbl">{{ $row['label'] }}:</td>
                            <td class="val">{{ $row['value'] }}</td>
                        </tr>
                    @endforeach
                </table>

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
            <td class="inv-sign-cell" style="width: 38%;">
                <table class="inv-sign">
                    <tr><td class="inv-sign-line"></td></tr>
                    <tr><td class="inv-sign-name">{{ $signatory['name'] ?? '' }}</td></tr>
                    @if(!empty($signatory['title']))
                        <tr><td class="inv-sign-meta">{{ $signatory['title'] }}</td></tr>
                    @endif
                    @if(!empty($signatory['company']))
                        <tr><td class="inv-sign-meta">{{ $signatory['company'] }}</td></tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    <div class="inv-footer">{{ $invoice['footer'] ?? '' }}</div>
</div>
