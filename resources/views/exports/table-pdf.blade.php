@php
    $company = $company ?? \App\Support\Documents\CompanyDocumentContext::make();
    $generatedAt = $generatedAt ?? now();
    $note = null;

    if ($truncated ?? false) {
        $note = 'PDF export is limited to the first 500 rows. Download CSV for the full '.number_format((int) $totalRows).' records.';
    }

    $payload = [
        'title' => $title,
        'subtitle' => __('documents.labels.report_copy'),
        'number' => $generatedAt->format('d M Y'),
        'generated_at' => $generatedAt,
        'columns' => $columns,
        'rows' => $rows,
        'empty_text' => 'No records to export.',
        'notes' => $note,
        'footer' => __('documents.footer.report'),
    ];
@endphp
@include('documents.layouts.pdf')
