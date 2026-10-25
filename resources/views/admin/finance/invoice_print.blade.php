<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $invoice->number }}</title>
    <style>
        @include('admin.finance.partials.saf-invoice-styles')
        body { margin: 0; background: #f3f3f3; }
        .invoice-print-toolbar {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            max-width: 880px;
            margin: 0 auto;
            padding: 16px 16px 0;
        }
        .invoice-print-toolbar a,
        .invoice-print-toolbar button {
            border: 1px solid #d1d5db;
            background: #fff;
            color: #111827;
            border-radius: 10px;
            padding: 8px 14px;
            font: 600 14px/1.2 Inter, "Segoe UI", Helvetica, Arial, sans-serif;
            text-decoration: none;
            cursor: pointer;
        }
        .invoice-print-toolbar a {
            background: #5f4bff;
            border-color: #5f4bff;
            color: #fff;
        }
        @media print {
            body { background: #fff; }
            .invoice-print-toolbar { display: none !important; }
            .saf-invoice-canvas { background: #fff; padding: 0; }
            .saf-invoice { box-shadow: none; }
        }
    </style>
</head>
<body>
    <div class="invoice-print-toolbar">
        <button type="button" onclick="window.print()">Print</button>
        <a href="{{ route('admin.documents.pdf', ['type' => 'invoice', 'id' => $invoice->id]) }}">Download PDF</a>
    </div>
    @include('admin.finance.partials.saf-invoice-sheet')
</body>
</html>
