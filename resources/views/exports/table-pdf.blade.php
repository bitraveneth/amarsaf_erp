@php
    use App\Support\Documents\DocumentDataFactory;
    use App\Support\Documents\CompanyDocumentContext;

    $company = $company ?? CompanyDocumentContext::make();
    $generatedAt = $generatedAt ?? now();
    $rawTitle = trim((string) ($title ?? 'Report'));
    $periodLabel = $periodLabel ?? null;
    $shortTitle = $rawTitle;

    if (preg_match('/^(.*?)\s*\((.+)\)$/u', $rawTitle, $matches) === 1) {
        $shortTitle = trim($matches[1]);
        $periodLabel = $periodLabel ?: trim($matches[2]);
    }

    $notes = [];
    if ($truncated ?? false) {
        $notes[] = 'PDF export is limited to the first 500 rows. Download CSV for the full '.number_format((int) $totalRows).' records.';
    }
    if (! empty($note)) {
        $notes[] = (string) $note;
    }

    $aligns = $aligns ?? [];
    if ($aligns === [] && ! empty($columns)) {
        foreach (array_values($columns) as $index => $column) {
            $sample = trim((string) ($rows[0][$index] ?? ''));
            $looksMoney = preg_match('/^-?[\d,]+(?:\.\d+)?(?:\s*(?:Dr|Cr|BDT))?$/i', $sample) === 1;
            $label = strtolower((string) $column);
            $aligns[$index] = $looksMoney
                || str_contains($label, 'debit')
                || str_contains($label, 'credit')
                || str_contains($label, 'amount')
                || str_contains($label, 'balance')
                || str_contains($label, 'total')
                || str_contains($label, 'qty')
                ? 'right'
                : 'left';
        }
    }

    $meta = $meta ?? [];
    array_unshift($meta, ['Document', __('documents.labels.report_copy')]);
    if ($periodLabel) {
        array_unshift($meta, ['Period', $periodLabel]);
    }
    $meta[] = ['Currency', $company['currency'] ?? config('app.currency', 'BDT')];
    $meta[] = ['Rows', number_format(count($rows ?? []))];

    $payload = DocumentDataFactory::designedSheet([
        'title' => $shortTitle,
        'number' => $periodLabel ?: $generatedAt->format('d M Y'),
        'generated_at' => $generatedAt,
        'parties' => [[
            'label' => 'Report',
            'name' => $shortTitle,
            'lines' => array_values(array_filter([
                __('documents.labels.report_copy'),
                $periodLabel,
            ])),
        ]],
        'meta' => $meta,
        'details_title' => 'Report details',
        'columns' => array_values($columns ?? []),
        'aligns' => array_values($aligns),
        'rows' => array_values($rows ?? []),
        'notes' => $notes,
        'signatures' => ['Prepared by', 'Checked by'],
        'footer' => __('documents.footer.report'),
    ]);
@endphp
@include('documents.layouts.pdf')
