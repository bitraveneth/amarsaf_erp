<style>
    @page { size: A4; margin: 0; }
    .inv {
        color: #1f2937;
        font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
    }
    .inv-for-pdf { padding: 22px 28px 54px; }
    .inv-accent { color: #6A2ACD; }
    .inv-head, .inv-intro, .inv-lines, .inv-bottom, .inv-bill, .inv-meta, .inv-pay, .inv-contact {
        width: 100%;
        border-collapse: collapse;
    }
    .inv-head td, .inv-intro td, .inv-bottom td { vertical-align: top; }
    .inv-logo { display: block; height: 46px; width: auto; }
    .inv-company {
        margin-top: 6px;
        font-size: 14px;
        font-weight: 700;
        color: #111827;
    }
    .inv-contact-line {
        text-align: right;
        font-size: 9px;
        color: #374151;
        line-height: 1.55;
    }
    .inv-dot {
        display: inline-block;
        width: 6px;
        height: 6px;
        margin-right: 6px;
        background: #6A2ACD;
        border-radius: 6px;
        vertical-align: middle;
    }
    .inv-intro { margin-top: 22px; }
    .inv-kicker {
        font-size: 10px;
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
    .inv-bill td { font-size: 10px; padding: 1px 0; vertical-align: top; }
    .inv-bill .lbl { width: 62px; color: #6b7280; }
    .inv-title {
        font-size: 36px;
        font-weight: 700;
        letter-spacing: 1.5px;
        color: #6A2ACD;
        text-align: right;
        line-height: 1.05;
        text-transform: uppercase;
    }
    .inv-title-long { font-size: 22px; letter-spacing: .04em; }
    .inv-copy {
        margin-top: 6px;
        text-align: right;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #6A2ACD;
    }
    .inv-bill-line { font-size: 10px; color: #4b5563; line-height: 1.45; }
    .inv-meta { width: auto; margin-top: 12px; margin-left: auto; }
    .inv-meta td { font-size: 10px; padding: 2px 0; white-space: nowrap; }
    .inv-meta .lbl { color: #6b7280; text-align: left; padding-right: 28px; }
    .inv-meta .val { text-align: right; font-weight: 700; color: #111827; }
    .inv-lines { margin-top: 16px; }
    .inv-lines th {
        background: #6A2ACD;
        color: #ffffff;
        font-size: 9px;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        padding: 8px 8px;
        border: 1px solid #6A2ACD;
        text-align: left;
    }
    .inv-lines th.inv-right, .inv-lines td.inv-right, .inv-grand td.inv-right { text-align: right; }
    .inv-lines th.inv-center, .inv-lines td.inv-center { text-align: center; }
    .inv-lines td {
        padding: 7px 8px;
        border: 1px solid #efe6f8;
        font-size: 10px;
        color: #1f2937;
        background: #ffffff;
    }
    .inv-lines tr.inv-alt td { background: #f6f0ff; }
    .inv-right { text-align: right; }
    .inv-center { text-align: center; }
    .inv-sum td {
        border: none;
        background: #ffffff;
        padding-top: 5px;
        padding-bottom: 5px;
    }
    .inv-sum.inv-sum-alt td.lbl,
    .inv-sum.inv-sum-alt td.val { background: #f6f0ff; }
    .inv-sum td.lbl { text-align: right; color: #374151; }
    .inv-sum td.val { text-align: right; font-weight: 700; }
    .inv-grand td {
        background: #6A2ACD;
        color: #ffffff;
        border: 1px solid #6A2ACD;
        font-weight: 700;
        font-size: 12px;
        padding: 8px;
    }
    .inv-bottom { margin-top: 18px; }
    .inv-section {
        font-size: 11px;
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
        font-size: 10px;
    }
    .inv-pay .lbl { width: 46%; color: #4b5563; background: #ffffff; }
    .inv-pay .val { font-weight: 700; color: #111827; background: #ffffff; }
    .inv-pay tr.inv-alt td { background: #f6f0ff; }
    .inv-notes { margin-top: 14px; }
    .inv-notes ol { margin: 0; padding-left: 16px; }
    .inv-notes li { font-size: 9px; color: #4b5563; line-height: 1.45; margin: 0 0 3px; }
    .inv-bottom td.inv-sign-cell { vertical-align: bottom; }
    .inv-sign { width: 210px; margin-left: auto; border-collapse: collapse; }
    .inv-sign-line { border-bottom: 1px dashed #9ca3af; height: 36px; }
    .inv-sign-name {
        padding-top: 8px;
        text-align: center;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: #111827;
    }
    .inv-sign-meta { text-align: center; font-size: 9px; color: #4b5563; line-height: 1.35; }
    .inv-signs { width: 100%; margin-top: 8px; border-collapse: collapse; }
    .inv-signs td { width: 33%; padding: 0 10px; vertical-align: bottom; text-align: center; }
    .inv-footer {
        margin-top: 22px;
        background: #6A2ACD;
        color: #ffffff;
        text-align: center;
        font-size: 9px;
        padding: 9px 12px;
    }
    .inv-for-pdf .inv-footer {
        position: fixed;
        left: 0;
        right: 0;
        bottom: 0;
        margin: 0;
    }
    .doc-preview-page:has(.inv) {
        position: relative;
        min-height: 1040px;
        padding-bottom: 72px;
    }
    .doc-preview-body .inv-footer {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        margin: 0;
    }
    @media print {
        .doc-preview-page:has(.inv) {
            min-height: 0;
            padding: 0;
        }
        .inv { padding: 0 0 48px; }
        .inv-footer {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            margin: 0;
        }
    }
</style>
