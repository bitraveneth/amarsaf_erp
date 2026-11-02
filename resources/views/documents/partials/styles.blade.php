@php
    $brand = $company['primary'] ?? '#5F4BFF';
@endphp
<style>
    @page { size: A4; margin: 12mm; }
    * { box-sizing: border-box; }
    body {
        font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
        font-size: 10px;
        color: #111827;
        background: #ffffff;
        margin: 0;
    }
    .doc-sheet { width: 100%; }
    .doc-copy-next { page-break-before: always; }
    .doc-copy-break { display: none; }
    .doc-banner { width: 100%; border-collapse: collapse; border-top: 6px solid {{ $brand }}; border-bottom: 1px solid #e5e7eb; margin: 0 0 12px; }
    .doc-banner-brand, .doc-banner-doc { vertical-align: top; padding: 10px 0 12px; }
    .doc-banner-doc { width: 42%; text-align: right; }
    .doc-brand-table { border-collapse: collapse; }
    .doc-logo-cell { width: 52px; vertical-align: middle; padding-right: 8px; }
    .doc-logo { width: auto; max-width: 72px; height: 40px; }
    .doc-logo-fallback {
        width: 42px;
        height: 42px;
        background: {{ $brand }};
        color: #ffffff;
        font-weight: 700;
        font-size: 13px;
        text-align: center;
        line-height: 42px;
    }
    .doc-company-name { font-size: 15px; font-weight: 700; color: #111827; }
    .doc-company-meta { font-size: 9px; color: #6b7280; line-height: 1.45; margin-top: 3px; }
    .doc-title { font-size: 18px; font-weight: 700; color: {{ $brand }}; line-height: 1.15; }
    .doc-copy-pill {
        display: inline-block;
        margin-top: 6px;
        padding: 3px 8px;
        background: {{ $brand }};
        color: #ffffff;
        font-size: 8px;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
    }
    .doc-number { margin-top: 6px; font-size: 12px; font-weight: 700; color: #111827; }
    .doc-status { margin-top: 2px; font-size: 9px; color: #6b7280; }
    .doc-info, .doc-party-table, .doc-bands, .doc-summary, .doc-signatures, .doc-lines, .doc-meta-table, .doc-totals { width: 100%; border-collapse: collapse; }
    .doc-info { margin-bottom: 12px; }
    .doc-info-parties, .doc-info-meta, .doc-summary-notes, .doc-summary-totals { vertical-align: top; }
    .doc-info-meta, .doc-summary-totals { width: 46%; }
    .doc-summary-notes { padding-right: 12px; }
    .doc-party-table td { vertical-align: top; padding-right: 8px; }
    .doc-party-table td:last-child { padding-right: 0; }
    .doc-card { border: 1px solid #e5e7eb; border-left: 3px solid {{ $brand }}; padding: 8px 10px; }
    .doc-card-label, .doc-bands .doc-card-label {
        font-size: 8px;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #6b7280;
        margin-bottom: 4px;
    }
    .doc-card-name { font-size: 12px; font-weight: 700; color: #111827; }
    .doc-card-line { font-size: 9px; color: #4b5563; margin-top: 2px; }
    .doc-meta-table td { padding: 4px 6px; border: 1px solid #e5e7eb; font-size: 9px; }
    .doc-meta-table td:first-child {
        width: 42%;
        background: #f9fafb;
        color: #6b7280;
        font-weight: 700;
        font-size: 8px;
        letter-spacing: .04em;
        text-transform: uppercase;
    }
    .doc-bands { margin-bottom: 12px; }
    .doc-bands td { border: 1px solid #e5e7eb; background: #f8f7ff; padding: 8px 10px; vertical-align: top; }
    .doc-lines { margin-bottom: 12px; }
    .doc-lines th {
        background: {{ $brand }};
        color: #ffffff;
        font-size: 8px;
        font-weight: 700;
        letter-spacing: .05em;
        text-transform: uppercase;
        padding: 7px 6px;
        border: 1px solid {{ $brand }};
    }
    .doc-lines td { padding: 6px; border: 1px solid #e5e7eb; vertical-align: top; font-size: 9px; }
    .doc-lines tr.doc-lines-row--alt td { background: #f8f7ff; }
    .doc-empty { text-align: center; color: #6b7280; padding: 12px; }
    .doc-align-right { text-align: right; }
    .doc-align-center { text-align: center; }
    .doc-align-left { text-align: left; }
    .doc-summary { margin-bottom: 8px; }
    .doc-notes { border-left: 3px solid {{ $brand }}; background: #f9fafb; padding: 8px 10px; font-size: 9px; color: #374151; }
    .doc-notes strong { display: block; margin-bottom: 3px; font-size: 8px; letter-spacing: .06em; text-transform: uppercase; color: #6b7280; }
    .doc-totals td { padding: 3px 0; font-size: 10px; }
    .doc-totals td:first-child { color: #6b7280; padding-right: 12px; }
    .doc-totals td:last-child { text-align: right; font-weight: 700; }
    .doc-totals tr:last-child td { border-top: 2px solid {{ $brand }}; padding-top: 6px; font-size: 12px; color: {{ $brand }}; }
    .doc-signatures { margin-top: 8px; }
    .doc-signatures td { vertical-align: bottom; padding-right: 14px; }
    .doc-signature-line { border-top: 1px solid #9ca3af; margin-top: 34px; padding-top: 4px; font-size: 8px; color: #4b5563; }
    .doc-footer { margin-top: 12px; border-top: 1px solid #e5e7eb; padding-top: 6px; text-align: center; font-size: 8px; color: #9ca3af; }
    .doc-generated { margin-top: 2px; }
</style>
