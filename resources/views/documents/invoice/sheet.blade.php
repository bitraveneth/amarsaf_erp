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
    if ($contact === []) {
        $contact = array_values(array_filter([
            $company['phone'] ?? null,
            $company['email'] ?? null,
            $company['address'] ?? null,
        ]));
    }
    $title = ($invoice['layout'] ?? '') === 'document'
        ? (string) ($invoice['title'] ?? __('documents.invoice_sheet.title'))
        : __('documents.invoice_sheet.title');
    $generatedAt = $payload['generated_at'] ?? null;
    $generatedLabel = $invoice['generated'] ?? null;
    if ($generatedLabel === null && $generatedAt instanceof \DateTimeInterface) {
        $generatedLabel = $generatedAt->timezone('Asia/Dhaka')->format('d M Y, h:i A');
    }
    $iconUri = function (string $name) use ($forPdf): ?string {
        $path = public_path('images/brand/icon-'.$name.'.png');
        if (! is_file($path)) {
            return null;
        }

        return ($forPdf ?? false)
            ? \App\Support\PdfDocumentBuilder::fileUri($path)
            : asset('images/brand/icon-'.$name.'.png');
    };
    $contactLines = [];
    foreach ([
        'phone' => $company['phone'] ?? null,
        'email' => $company['email'] ?? null,
        'pin' => $company['address'] ?? null,
    ] as $icon => $text) {
        if (is_string($text) && trim($text) !== '') {
            $contactLines[] = ['icon' => $iconUri($icon), 'text' => trim($text)];
        }
    }
    if ($contactLines === []) {
        foreach ($contact as $text) {
            $contactLines[] = ['icon' => null, 'text' => $text];
        }
    }
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

@if(($invoice['layout'] ?? '') === 'document')
    @include('documents.invoice.document')
@else
<div @class(['inv', 'inv-for-pdf' => $forPdf ?? false])>
    @include('documents.invoice.letterhead')

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
                <div class="inv-kicker">{{ __('documents.labels.invoice_details') }}</div>
                <table class="inv-details">
                    <tr>
                        <td class="lbl">{{ __('documents.invoice_sheet.invoice_no') }}:</td>
                        <td class="val">{{ $invoice['number'] ?? ($payload['number'] ?? '—') }}</td>
                    </tr>
                    <tr class="inv-alt">
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
                <th style="width: 16%;" class="inv-center">{{ __('documents.invoice_sheet.rate') }}</th>
                <th style="width: 18%;" class="inv-center">{{ __('documents.invoice_sheet.amount') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($lines as $line)
                <tr @class(['inv-alt' => $loop->iteration % 2 === 0])>
                    <td class="inv-center">{{ $line['sl'] }}</td>
                    <td>{{ $line['description'] }}</td>
                    <td class="inv-center">{{ $line['qty'] }}</td>
                    <td class="inv-center">{{ $line['rate'] }}</td>
                    <td class="inv-center">{{ $line['amount'] }}</td>
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
@endif
