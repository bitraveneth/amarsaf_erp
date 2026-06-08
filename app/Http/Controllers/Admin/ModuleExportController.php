<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Permission;
use App\Http\Controllers\Controller;
use App\Support\ExportDateRange;
use App\Support\ModuleExportRegistry;
use App\Support\TabularExport;
use Illuminate\Http\Request;

class ModuleExportController extends Controller
{
    public function preview(Request $request, string $module)
    {
        abort_if(ModuleExportRegistry::isSpecialExport($module), 404);

        $export = ModuleExportRegistry::resolve($module, $request);

        if (! Permission::can($request->user(), $export['permission'])) {
            abort(403);
        }

        $rows = collect($export['rows']);
        $limit = 50;
        $query = ExportDateRange::queryParams($request);
        $enrichment = $this->enrichTabularPreview($export, $rows);

        return response()->json(array_merge([
            'kind' => 'tabular',
            'title' => $export['title'],
            'columns' => $export['columns'],
            'rows' => $rows->take($limit)->values()->all(),
            'total_rows' => $rows->count(),
            'truncated' => $rows->count() > $limit,
            'preview_limit' => $limit,
            'csv_url' => ModuleExportRegistry::exportUrl($module, 'csv', $query),
            'pdf_url' => ModuleExportRegistry::exportUrl($module, 'pdf', $query),
        ], $enrichment));
    }

    public function __invoke(Request $request, string $module, string $format)
    {
        abort_unless(in_array($format, ['csv', 'pdf'], true), 404);

        $export = ModuleExportRegistry::resolve($module, $request);

        if (! Permission::can($request->user(), $export['permission'])) {
            abort(403);
        }

        return TabularExport::download(
            $format,
            $export['title'],
            $export['filename'],
            $export['columns'],
            $export['rows']
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function enrichTabularPreview(array $export, \Illuminate\Support\Collection $rows): array
    {
        $columns = $export['columns'];
        $numericColumns = $this->numericColumnIndices($columns);
        $highlights = [];
        $rowStyles = [];

        $firstColumn = strtolower((string) ($columns[0] ?? ''));

        if (in_array($firstColumn, ['line', 'section'], true)) {
            foreach ($rows as $index => $row) {
                $label = strtolower(trim((string) ($row[0] ?? '')));
                $amount = (string) ($row[count($columns) - 1] ?? '');

                if ($label === '') {
                    $rowStyles[$index] = 'spacer';

                    continue;
                }

                if (in_array($label, ['assets', 'liabilities', 'equity', 'revenue'], true)) {
                    $rowStyles[$index] = 'section';

                    continue;
                }

                if (str_contains($label, 'total')
                    || str_contains($label, 'net profit')
                    || str_contains($label, 'gross profit')
                    || str_contains($label, 'net cash')
                    || str_contains($label, 'owner equity')) {
                    $rowStyles[$index] = 'total';

                    if ($amount !== '') {
                        $highlights[] = ['label' => $row[0], 'value' => $amount];
                    }

                    continue;
                }

                if (in_array($label, ['period', 'currency', 'as of', 'accounts', 'account breakdown'], true)) {
                    $rowStyles[$index] = 'meta';

                    continue;
                }

                if (in_array($label, ['net sales', 'gross profit', 'net profit', 'net cash movement', 'sales revenue'], true)
                    && $amount !== '') {
                    $highlights[] = ['label' => $row[0], 'value' => $amount];
                }
            }
        }

        return [
            'highlights' => array_slice($highlights, 0, 4),
            'row_styles' => $rowStyles,
            'numeric_columns' => $numericColumns,
        ];
    }

    /**
     * @return array<int, int>
     */
    protected function numericColumnIndices(array $columns): array
    {
        $indices = [];

        foreach ($columns as $index => $column) {
            $normalized = strtolower((string) $column);

            if (str_contains($normalized, 'amount')
                || str_contains($normalized, 'debit')
                || str_contains($normalized, 'credit')
                || str_contains($normalized, 'outstanding')
                || str_contains($normalized, 'paid')
                || str_contains($normalized, 'net')
                || str_contains($normalized, 'vat')
                || str_contains($normalized, 'gross')
                || str_contains($normalized, 'taxable')
                || str_contains($normalized, 'withholding')
                || str_contains($normalized, 'rate')
                || str_contains($normalized, 'days')) {
                $indices[] = $index;
            }
        }

        return $indices;
    }
}
