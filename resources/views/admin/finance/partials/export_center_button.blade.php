@php
    use App\Support\ModuleExportRegistry;

    $exportQuery = $exportQuery ?? [];

    if (! empty($asOf)) {
        $exportQuery['to'] = $asOf instanceof \Carbon\Carbon ? $asOf->toDateString() : $asOf;
    }

    if (! empty($from)) {
        $exportQuery['from'] = $from instanceof \Carbon\Carbon ? $from->toDateString() : $from;
    }

    if (! empty($to)) {
        $exportQuery['to'] = $to instanceof \Carbon\Carbon ? $to->toDateString() : $to;
    }

    $exportQuery = array_filter($exportQuery, fn ($value) => $value !== null && $value !== '');
    $centerUrl = route('admin.export-center', array_merge(['module' => $module], $exportQuery));
    $csvUrl = ModuleExportRegistry::exportUrl($module, 'csv', $exportQuery);
    $pdfUrl = ModuleExportRegistry::exportUrl($module, 'pdf', $exportQuery);
@endphp

<div class="flex flex-wrap items-center gap-2 print-hidden">
    <a href="{{ $centerUrl }}" class="erp-btn-secondary text-sm">
        Export center
    </a>
    <a href="{{ $csvUrl }}" class="erp-btn-secondary text-sm">
        CSV
    </a>
    <a href="{{ $pdfUrl }}" class="erp-btn-secondary text-sm">
        PDF
    </a>
</div>
