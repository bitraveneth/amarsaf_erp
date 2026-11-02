<style>
    @page { size: A4; margin: 0; }
    .inv {
        color: #1f2937;
        font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
    }
    .inv-for-pdf { padding: 10mm 14mm 16mm; }
    .inv-accent { color: #6A2ACD; }
    .inv-head, .inv-intro, .inv-lines, .inv-bottom, .inv-bill, .inv-meta, .inv-pay, .inv-contact {
        width: 100%;
        border-collapse: collapse;
    }
    .inv-head td, .inv-intro td, .inv-bottom td { vertical-align: top; }
    .inv-logo { display: block; height: 48px; width: auto; }
    .inv-identity { padding-top: 16px; }
    .inv-company {
        margin: 0;
        font-size: 16px;
        font-weight: 700;
        color: #111827;
    }
    .inv-contact { width: auto; margin-top: 6px; border-collapse: collapse; }
    .inv-contact td { vertical-align: top; font-size: 14px; color: #374151; line-height: 1.45; }
    .inv-ico-cell { width: 22px; padding: 2px 8px 3px 0; }
    .inv-contact-text { padding: 1px 0 3px; }
    .inv-ico { width: 14px; height: 14px; margin-top: 2px; }
    .inv-contact-line {
        text-align: left;
        font-size: 14px;
        color: #374151;
        line-height: 1.45;
        margin-top: 3px;
    }
    .inv-generated {
        margin-top: 10px;
        text-align: right;
        font-size: 12px;
        color: #6b7280;
    }
    .inv-details { width: 100%; margin-top: 8px; border: 1px solid #eadff8; border-collapse: collapse; }
    .inv-details td { border: 1px solid #eadff8; padding: 7px 8px; font-size: 13px; white-space: nowrap; }
    .inv-details .lbl { width: 1%; color: #6b7280; background: #ffffff; }
    .inv-details .val { font-weight: 700; color: #111827; text-align: right; background: #ffffff; }
    .inv-details tr.inv-alt td { background: #f6f0ff; }
    .inv-dot {
        display: inline-block;
        width: 6px;
        height: 6px;
        margin-right: 6px;
        background: #6A2ACD;
        border-radius: 6px;
        vertical-align: middle;
    }
    .inv-intro { margin-top: 14px; }
    .inv-kicker {
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #111827;
    }
    .inv-bill-name {
        margin: 2px 0 8px;
        font-size: 20px;
        font-weight: 700;
        color: #111827;
    }
    .inv-bill td { font-size: 14px; padding: 3px 0; vertical-align: top; }
    .inv-bill .lbl { width: 84px; color: #6b7280; }
    .inv-title {
        font-size: 40px;
        font-weight: 700;
        letter-spacing: .5px;
        color: #6A2ACD;
        text-align: right;
        line-height: .95;
        text-transform: uppercase;
    }
    .inv-title-stack { font-size: 38px; }
    .inv-title-line { display: block; }
    .inv-copy {
        margin-top: 6px;
        text-align: right;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #6A2ACD;
    }
    .inv-bill-line { font-size: 14px; color: #4b5563; line-height: 1.45; }
    .inv-meta { width: auto; margin-top: 12px; margin-left: auto; }
    .inv-meta td { font-size: 14px; padding: 3px 0; white-space: nowrap; }
    .inv-meta .lbl { color: #6b7280; text-align: left; padding-right: 28px; }
    .inv-meta .val { text-align: right; font-weight: 700; color: #111827; }
    .inv-lines { margin-top: 12px; }
    .inv-lines th {
        background: #6A2ACD;
        color: #ffffff;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        padding: 9px 8px;
        border: 1px solid #6A2ACD;
        text-align: center;
    }
    .inv-lines th.inv-right, .inv-lines td.inv-right { text-align: right; }
    .inv-lines th.inv-center, .inv-lines td.inv-center { text-align: center; }
    .inv-lines td {
        padding: 7px 8px;
        border: 1px solid #efe6f8;
        font-size: 14px;
        color: #1f2937;
        background: #ffffff;
        text-align: center;
    }
    .inv-lines tr.inv-alt td { background: #f6f0ff; }
    .inv-right { text-align: right; }
    .inv-center { text-align: center; }
    .inv-totals-wrap { width: 100%; margin-top: 16px; border-collapse: collapse; }
    .inv-totals-side { width: 22%; }
    .inv-totals-hold { width: 56%; }
    .inv-totals { width: 100%; border-collapse: separate; border-spacing: 0; }
    .inv-totals td {
        width: 50%;
        text-align: center;
        font-size: 14px;
        font-weight: 700;
        color: #374151;
        background: #f6f0ff;
        border: none;
        border-bottom: 1px solid #ffffff;
        padding: 8px 12px;
        white-space: nowrap;
    }
    .inv-totals td.val { color: #111827; }
    .inv-totals tr.inv-grand td {
        background: #6A2ACD;
        color: #ffffff;
        font-size: 16px;
        padding: 10px 12px;
        border-bottom: none;
    }
    .inv-bottom { margin-top: 12px; }
    .inv-section {
        font-size: 13px;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: #111827;
        margin: 0 0 8px;
    }
    .inv-pay { width: 92%; border: 1px solid #eadff8; }
    .inv-pay td {
        border: 1px solid #eadff8;
        padding: 5px 8px;
        font-size: 13px;
    }
    .inv-pay .lbl { width: 46%; color: #4b5563; background: #ffffff; }
    .inv-pay .val { font-weight: 700; color: #111827; background: #ffffff; }
    .inv-pay tr.inv-alt td { background: #f6f0ff; }
    .inv-notes { margin-top: 8px; }
    .inv-notes ol { margin: 0; padding-left: 18px; }
    .inv-notes li { font-size: 13px; color: #4b5563; line-height: 1.4; margin: 0 0 3px; }
    .inv-bottom td.inv-sign-cell { vertical-align: bottom; }
    .inv-sign { width: 210px; margin-left: auto; border-collapse: collapse; }
    .inv-sign-line { border-bottom: 1px dashed #9ca3af; height: 28px; }
    .inv-sign-name {
        padding-top: 8px;
        text-align: center;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .02em;
        text-transform: uppercase;
        color: #111827;
    }
    .inv-sign-meta { text-align: center; font-size: 12px; color: #4b5563; line-height: 1.35; }
    .inv-signs { width: 100%; margin-top: 6px; border-collapse: collapse; page-break-inside: avoid; }
    .inv-signs td { width: 33%; padding: 0 10px; vertical-align: bottom; text-align: center; }
    .inv-footer {
        margin-top: 14px;
        background: #6A2ACD;
        color: #ffffff;
        text-align: center;
        font-size: 12px;
        padding: 10px 12px;
    }
    .inv-for-pdf .inv-footer {
        position: fixed;
        left: 0;
        right: 0;
        bottom: 0;
        margin: 0;
    }
    .doc-preview-body:has(.inv) .doc-preview-page {
        width: 210mm;
        max-width: 210mm;
        min-height: 0;
        margin: 16px auto 32px;
        padding: 0;
        background: transparent;
        border-radius: 0;
        box-shadow: none;
    }
    .doc-preview-body:has(.inv) .doc-copy {
        width: 210mm;
        min-height: 297mm;
        box-sizing: border-box;
        margin: 0 auto 16px;
        padding: 10mm 14mm 16mm;
        background: #ffffff;
        position: relative;
        box-shadow: 0 12px 40px rgba(15, 23, 42, 0.12);
    }
    .doc-preview-body:has(.inv) .inv-footer {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        margin: 0;
    }
    @media print {
        .doc-preview-body:has(.inv) .doc-preview-page {
            width: auto;
            max-width: none;
            margin: 0;
        }
        .doc-preview-body:has(.inv) .doc-copy {
            width: 210mm;
            min-height: 297mm;
            margin: 0;
            box-shadow: none;
        }
        .inv-footer {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            margin: 0;
        }
    }
</style>
