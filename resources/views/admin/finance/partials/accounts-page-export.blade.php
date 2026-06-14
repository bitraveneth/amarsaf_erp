@php
    use App\Support\ModuleExportRegistry;

    $exportQuery = array_filter($exportQuery ?? [], fn ($value) => $value !== null && $value !== '' && $value !== 'structure' && $value !== 'balance');
    $csvUrl = ModuleExportRegistry::exportUrl($module, 'csv', $exportQuery);
    $pdfUrl = ModuleExportRegistry::exportUrl($module, 'pdf', $exportQuery);
@endphp

<div class="flex flex-wrap items-center gap-2 print-hidden">
    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $label }}</span>
    <a href="{{ $csvUrl }}" class="erp-btn-secondary text-sm">CSV</a>
    <a href="{{ $pdfUrl }}" class="erp-btn-secondary text-sm">PDF</a>
</div>
