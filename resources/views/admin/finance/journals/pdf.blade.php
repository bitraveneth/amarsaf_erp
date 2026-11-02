@php
    $company = \App\Support\Documents\CompanyDocumentContext::make();
    $currency = config('app.currency', 'BDT');
    $rows = [];

    foreach ($journal->lines as $line) {
        $account = $line->account;
        $rows[] = [
            $account ? ($account->code.' — '.$account->name) : '—',
            (string) ($line->description ?? ''),
            number_format((float) $line->debit, 2),
            number_format((float) $line->credit, 2),
        ];
    }

    $payload = [
        'title' => 'Journal voucher',
        'number' => $journal->number,
        'status' => ucfirst((string) $journal->status),
        'generated_at' => now(),
        'meta' => [
            ['Date', optional($journal->entry_date)->format('d M Y') ?: '—'],
            ['Type', ucfirst(str_replace('_', ' ', (string) ($journal->journal_type ?: 'manual')))],
            ['Status', ucfirst((string) $journal->status)],
        ],
        'columns' => ['Account', 'Description', 'Debit', 'Credit'],
        'aligns' => ['left', 'left', 'right', 'right'],
        'widths' => ['34%', '32%', '17%', '17%'],
        'rows' => $rows,
        'totals' => [
            ['Total debit', number_format($journal->totalDebit(), 2).' '.$currency],
            ['Total credit', number_format($journal->totalCredit(), 2).' '.$currency],
        ],
        'notes' => $journal->description,
        'signatures' => [
            [__('documents.signatures.prepared'), ''],
            [__('documents.signatures.approved'), ''],
        ],
        'footer' => __('documents.footer.journal'),
    ];
@endphp
@include('documents.layouts.pdf')
