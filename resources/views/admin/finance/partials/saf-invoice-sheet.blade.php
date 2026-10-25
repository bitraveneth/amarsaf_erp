@php
    $sheet = $sheet ?? \App\Support\Documents\SalesInvoicePresenter::forInvoice($invoice);
@endphp
<div class="saf-invoice-canvas">
    <article class="saf-invoice">
        <div class="saf-invoice-body">
            <table class="saf-top">
                <tr>
                    <td style="width:110px; vertical-align:top;">
                        <div class="saf-mark">
                            @if($sheet['logo_src'])
                                <img src="{{ $sheet['logo_src'] }}" alt="SAF">
                            @endif
                            <div class="saf-mark-word">SAF</div>
                        </div>
                    </td>
                    <td></td>
                    <td class="saf-title-cell">
                        <div class="saf-title">INVOICE</div>
                        <table class="saf-meta">
                            <tr>
                                <td>Invoice No</td>
                                <td class="colon">:</td>
                                <td class="value">{{ $sheet['number'] }}</td>
                            </tr>
                            <tr>
                                <td>Invoice Date</td>
                                <td class="colon">:</td>
                                <td class="value">{{ $sheet['date'] }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <div class="saf-kicker">INVOICE TO</div>
            <div class="saf-name">{{ $sheet['customer'] }}</div>
            <table class="saf-contact">
                <tr>
                    <td class="lbl">Phone</td>
                    <td class="colon">:</td>
                    <td class="value">{{ $sheet['phone'] }}</td>
                </tr>
                <tr>
                    <td class="lbl">E-mail</td>
                    <td class="colon">:</td>
                    <td class="value">{{ $sheet['email'] }}</td>
                </tr>
                <tr>
                    <td class="lbl">Address</td>
                    <td class="colon">:</td>
                    <td class="value">{{ $sheet['address'] }}</td>
                </tr>
            </table>

            <table class="saf-items">
                <thead>
                    <tr>
                        <th class="idx">#</th>
                        <th>DESCRIPTION</th>
                        <th class="qty">QUANTITY</th>
                        <th class="unit">UNIT</th>
                        <th class="rate">RATE</th>
                        <th class="amt">AMOUNT</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sheet['lines'] as $line)
                        <tr>
                            <td class="idx">{{ $line['index'] }}</td>
                            <td>{{ $line['description'] }}</td>
                            <td>{{ $line['quantity'] }}</td>
                            <td>{{ $line['unit'] }}</td>
                            <td>{{ $line['rate'] }}</td>
                            <td>{{ $line['amount'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td class="idx">01</td>
                            <td>—</td>
                            <td>—</td>
                            <td>—</td>
                            <td>—</td>
                            <td>—</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <table class="saf-totals-wrap">
                <tr>
                    <td class="spacer"></td>
                    <td style="width:292px;">
                        <table class="saf-totals">
                            <tr>
                                <td>Sub Total</td>
                                <td class="num">{{ $sheet['sub_total'] }}</td>
                            </tr>
                            <tr>
                                <td>Discount (-)</td>
                                <td class="num">{{ $sheet['discount'] }}</td>
                            </tr>
                            <tr>
                                <td>VAT (+)</td>
                                <td class="num">{{ $sheet['vat'] }}</td>
                            </tr>
                            <tr class="rule">
                                <td>Invoice Total</td>
                                <td class="num">{{ $sheet['invoice_total'] }}</td>
                            </tr>
                            <tr class="gap">
                                <td>Paid advance (-)</td>
                                <td class="num">{{ $sheet['paid_advance'] }}</td>
                            </tr>
                            <tr class="saf-due">
                                <td class="label">Total Due</td>
                                <td class="num">
                                    @if($sheet['taka_src'])
                                        <img class="saf-taka" src="{{ $sheet['taka_src'] }}" alt="{{ $sheet['currency_symbol'] }}">
                                    @else
                                        {{ $sheet['currency_symbol'] }}
                                    @endif
                                    {{ $sheet['total_due'] }}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <table class="saf-bottom">
                <tr>
                    <td class="saf-pay">
                        <div class="saf-section-title">Payment Method</div>
                        <table class="saf-pay-table">
                            <tr class="strong">
                                <td class="lbl">Account ID</td>
                                <td class="colon">:</td>
                                <td>{{ $sheet['payment']['account_id'] }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">Account Name</td>
                                <td class="colon">:</td>
                                <td>{{ $sheet['payment']['account_name'] }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">Branch</td>
                                <td class="colon">:</td>
                                <td>{{ $sheet['payment']['branch'] }}</td>
                            </tr>
                            <tr>
                                <td class="lbl">Routing</td>
                                <td class="colon">:</td>
                                <td>{{ $sheet['payment']['routing'] }}</td>
                            </tr>
                        </table>
                    </td>
                    <td class="saf-sign">
                        @if($sheet['signature_src'])
                            <img class="saf-sign-img" src="{{ $sheet['signature_src'] }}" alt="">
                        @endif
                        <div class="saf-sign-name">{{ $sheet['signatory_name'] }}</div>
                        <div class="saf-sign-role">{{ $sheet['signatory_title'] }}</div>
                    </td>
                </tr>
            </table>

            <div class="saf-terms">
                <div class="saf-section-title">Terms &amp; Conditions</div>
                <p>{{ $sheet['terms'] }}</p>
            </div>
        </div>

        <table class="saf-footer">
            <tr>
                <td class="foot-phone">
                    @if($sheet['icon_phone'])
                        <img class="icon" src="{{ $sheet['icon_phone'] }}" alt="">
                    @endif
                    <span class="foot-text">{{ $sheet['footer_phone'] }}</span>
                </td>
                <td class="foot-mail">
                    @if($sheet['icon_mail'])
                        <img class="icon" src="{{ $sheet['icon_mail'] }}" alt="">
                    @endif
                    <span class="foot-text">{{ $sheet['footer_email'] }}</span>
                </td>
                <td class="foot-address">
                    @if($sheet['icon_pin'])
                        <img class="icon" src="{{ $sheet['icon_pin'] }}" alt="">
                    @endif
                    <span class="foot-text">
                        @foreach($sheet['footer_address_lines'] as $addressLine)
                            <span class="addr-line">{{ $addressLine }}</span>@if(! $loop->last)<br>@endif
                        @endforeach
                    </span>
                </td>
            </tr>
        </table>
    </article>
</div>
