<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->number }}</title>
    <style>
        @page {
            size: A4;
            margin: 0;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: #ffffff;
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
        }
        @include('admin.finance.partials.saf-invoice-styles')
        .saf-invoice-body { padding: 24px 32px 0; }
        .saf-title { font-size: 32px; }
        .saf-kicker { margin-top: 16px; }
        .saf-name { font-size: 24px; margin-top: 4px; }
        .saf-items { margin-top: 14px; }
        .saf-items td { padding: 6px 8px 6px 0; }
        .saf-bottom { margin-top: 12px; }
        .saf-pay { padding-top: 8px; }
        .saf-terms { margin-top: 10px; }
        .saf-footer { margin-top: 14px; }
        .saf-footer td { padding: 12px 14px; font-size: 11px; }
        .saf-mark { width: 78px; height: 78px; }
        .saf-mark img { width: 46px; height: 34px; margin-top: 10px; }
        .saf-mark-word { font-size: 15px; }
    </style>
</head>
<body>
@php
    $sheet = \App\Support\Documents\SalesInvoicePresenter::forInvoice($invoice, true);
@endphp
@include('admin.finance.partials.saf-invoice-sheet')
</body>
</html>
