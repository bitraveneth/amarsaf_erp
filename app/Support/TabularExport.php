<?php

namespace App\Support;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TabularExport
{
    public static function download(string $format, string $title, string $filename, array $columns, iterable $rows): StreamedResponse|\Illuminate\Http\Response
    {
        $normalizedRows = self::normalizeRows($rows);

        return match ($format) {
            'csv' => self::csv($filename, $columns, $normalizedRows),
            'pdf' => self::pdf($title, $filename, $columns, $normalizedRows),
            default => abort(404),
        };
    }

    protected static function normalizeRows(iterable $rows): array
    {
        if ($rows instanceof Collection) {
            return $rows->values()->all();
        }

        return is_array($rows) ? array_values($rows) : iterator_to_array($rows);
    }

    protected static function csv(string $filename, array $columns, array $rows): StreamedResponse
    {
        $safeName = self::safeFilename($filename) . '.csv';

        return response()->streamDownload(function () use ($columns, $rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $columns);

            foreach ($rows as $row) {
                fputcsv($handle, array_map(fn ($value) => self::stringify($value), (array) $row));
            }

            fclose($handle);
        }, $safeName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    protected static function pdf(string $title, string $filename, array $columns, array $rows): \Illuminate\Http\Response
    {
        $truncated = false;
        $totalRows = count($rows);

        if ($totalRows > 500) {
            $rows = array_slice($rows, 0, 500);
            $truncated = true;
        }

        $pdf = Pdf::loadView('exports.table-pdf', [
            'title' => $title,
            'columns' => $columns,
            'rows' => $rows,
            'generatedAt' => now(),
            'truncated' => $truncated,
            'totalRows' => $totalRows,
        ])->setPaper('a4', count($columns) > 6 ? 'landscape' : 'portrait');

        return $pdf->download(self::safeFilename($filename) . '.pdf');
    }

    protected static function safeFilename(string $filename): string
    {
        $filename = trim($filename);
        $filename = preg_replace('/[^A-Za-z0-9._-]+/', '-', $filename) ?: 'export';

        return trim($filename, '-');
    }

    protected static function stringify(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        return json_encode($value, JSON_UNESCAPED_UNICODE) ?: '';
    }
}
