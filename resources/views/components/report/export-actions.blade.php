@props([
    'module' => null,
    'from' => null,
    'to' => null,
    'asOf' => null,
    'exportQuery' => [],
    'showExportCenter' => true,
    'showPrint' => true,
])

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
    $centerUrl = $module ? route('admin.export-center', array_merge(['module' => $module], $exportQuery)) : null;
    $csvUrl = $module ? ModuleExportRegistry::exportUrl($module, 'csv', $exportQuery) : null;
    $pdfUrl = $module ? ModuleExportRegistry::exportUrl($module, 'pdf', $exportQuery) : null;
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-wrap items-center gap-2 print:hidden']) }} x-data="{ open: false }" @keydown.escape.window="open = false">
    @if($showPrint)
        @include('admin.finance.partials.print_button')
    @endif

    @if($module)
        <div class="relative">
            <button
                type="button"
                class="erp-btn-secondary text-sm"
                @click="open = !open"
                aria-haspopup="true"
                :aria-expanded="open"
            >
                Export
            </button>
            <div
                x-show="open"
                x-cloak
                x-transition
                @click.outside="open = false"
                class="absolute right-0 top-[calc(100%+0.35rem)] z-30 min-w-[11rem] overflow-hidden rounded-xl border border-gray-200 bg-white py-1 shadow-theme-lg dark:border-gray-700 dark:bg-gray-900"
            >
                @if($csvUrl)
                    <a href="{{ $csvUrl }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5">Download CSV</a>
                @endif
                @if($pdfUrl)
                    <a href="{{ $pdfUrl }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5">Download PDF</a>
                @endif
                @if($showExportCenter && $centerUrl)
                    <a href="{{ $centerUrl }}" class="block border-t border-gray-100 px-4 py-2 text-sm text-gray-500 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-400 dark:hover:bg-white/5">Open export center</a>
                @endif
            </div>
        </div>
    @endif

    {{ $slot }}
</div>
