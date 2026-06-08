<style>
    @page { size: A4; margin: 12mm; }
    * { box-sizing: border-box; }
    body {
        font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
        font-size: 11px;
        color: #111827;
        background: #ffffff;
        margin: 0;
    }
    .doc-sheet { max-width: 720px; margin: 0 auto; }
    .doc-header {
        display: table;
        width: 100%;
        margin-bottom: 18px;
        padding-bottom: 12px;
        border-bottom: 2px solid {{ $company['primary'] ?? '#5F4BFF' }};
    }
    .doc-header-left, .doc-header-right { display: table-cell; vertical-align: top; }
    .doc-header-right { text-align: right; width: 42%; }
    .doc-logo {
        width: auto;
        max-width: 120px;
        height: 36px;
        object-fit: contain;
        margin-right: 10px;
        vertical-align: middle;
    }
    .doc-logo-fallback {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        border-radius: 10px;
        background: {{ $company['primary'] ?? '#5F4BFF' }};
        color: #fff;
        font-weight: 700;
        font-size: 13px;
        margin-right: 10px;
        vertical-align: middle;
    }
    .doc-company-name { font-size: 16px; font-weight: 700; color: #111827; }
    .doc-company-meta { font-size: 10px; color: #6b7280; line-height: 1.5; margin-top: 4px; }
    .doc-title { font-size: 20px; font-weight: 700; color: {{ $company['primary'] ?? '#5F4BFF' }}; }
    .doc-subtitle { font-size: 11px; color: #6b7280; margin-top: 4px; }
    .doc-badge {
        display: inline-block;
        margin-top: 8px;
        padding: 3px 8px;
        border-radius: 999px;
        background: #eef2ff;
        color: #4338ca;
        font-size: 10px;
        font-weight: 600;
    }
    .doc-grid {
        display: table;
        width: 100%;
        margin-bottom: 16px;
    }
    .doc-grid-col { display: table-cell; vertical-align: top; width: 50%; padding-right: 10px; }
    .doc-grid-col:last-child { padding-right: 0; padding-left: 10px; }
    .doc-card {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 10px 12px;
        min-height: 72px;
    }
    .doc-card-label {
        font-size: 9px;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #6b7280;
        margin-bottom: 6px;
        font-weight: 600;
    }
    .doc-card-name { font-size: 12px; font-weight: 700; color: #111827; }
    .doc-card-line { font-size: 10px; color: #4b5563; margin-top: 2px; }
    .doc-meta-table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
    .doc-meta-table td {
        padding: 5px 8px;
        border: 1px solid #e5e7eb;
        font-size: 10px;
    }
    .doc-meta-table td:first-child {
        width: 34%;
        background: #f9fafb;
        color: #6b7280;
        font-weight: 600;
    }
    .doc-lines { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
    .doc-lines th {
        text-align: left;
        padding: 7px 8px;
        background: #f3f4f6;
        border-bottom: 1px solid #d1d5db;
        font-size: 9px;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: #4b5563;
    }
    .doc-lines td {
        padding: 7px 8px;
        border-bottom: 1px solid #e5e7eb;
        vertical-align: top;
    }
    .doc-lines tr.doc-lines-row--alt td { background: #fcfcfd; }
    .doc-totals-wrap { text-align: right; margin-bottom: 16px; }
    .doc-totals { min-width: 260px; border-collapse: collapse; }
    .doc-totals td { padding: 4px 0; font-size: 10px; }
    .doc-totals td:first-child { color: #6b7280; padding-right: 16px; }
    .doc-totals td:last-child { text-align: right; font-weight: 600; }
    .doc-totals tr:last-child td {
        border-top: 1px solid #d1d5db;
        padding-top: 8px;
        font-size: 11px;
        color: {{ $company['primary'] ?? '#5F4BFF' }};
    }
    .doc-notes {
        border-left: 3px solid {{ $company['primary'] ?? '#5F4BFF' }};
        background: #f9fafb;
        padding: 10px 12px;
        margin-bottom: 16px;
        font-size: 10px;
        color: #374151;
    }
    .doc-signatures {
        display: table;
        width: 100%;
        margin-top: 28px;
    }
    .doc-signature {
        display: table-cell;
        width: 33%;
        padding-right: 12px;
        vertical-align: bottom;
    }
    .doc-signature-line {
        border-top: 1px solid #9ca3af;
        margin-top: 42px;
        padding-top: 6px;
        font-size: 10px;
        color: #4b5563;
    }
    .doc-footer {
        margin-top: 18px;
        padding-top: 10px;
        border-top: 1px solid #e5e7eb;
        font-size: 9px;
        color: #9ca3af;
        text-align: center;
    }
    .doc-generated {
        font-size: 9px;
        color: #9ca3af;
        margin-top: 6px;
    }
</style>
