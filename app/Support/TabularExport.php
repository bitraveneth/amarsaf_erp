<?php

namespace App\Support;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TabularExport
{
    public static function download(
        string $format,
        string $title,
        string $filename,
        array $columns,
        iterable $rows,
        string $disposition = 'attachment',
        array $options = [],
    ): StreamedResponse|\Illuminate\Http\Response {
        $normalizedRows = self::normalizeRows($rows);

        return match ($format) {
            'csv' => self::csv($filename, $columns, $normalizedRows),
            'pdf' => self::pdf($title, $filename, $columns, $normalizedRows, $disposition, $options),
            default => abort(404),
        };
    }

    public static function csvContent(array $columns, iterable $rows): string
    {
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, $columns);

        foreach (self::normalizeRows($rows) as $row) {
            fputcsv($handle, array_map(fn ($value) => self::stringify($value), (array) $row));
        }

        rewind($handle);
        $content = stream_get_contents($handle) ?: '';
        fclose($handle);

        return $content;
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

    protected static function pdf(
        string $title,
        string $filename,
        array $columns,
        array $rows,
        string $disposition = 'attachment',
        array $options = [],
    ): \Illuminate\Http\Response {
        // Designed DomPDF sheets are heavy; keep a ceiling and allow a temporary bump.
        $previousMemoryLimit = ini_get('memory_limit');
        if (function_exists('ini_set')) {
            @ini_set('memory_limit', '256M');
        }

        $truncated = false;
        $totalRows = count($rows);
        $maxPdfRows = 200;

        if ($totalRows > $maxPdfRows) {
            $rows = array_slice($rows, 0, $maxPdfRows);
            $truncated = true;
        }

        $rows = array_map(function ($row) {
            return array_map(fn ($value) => self::stringify($value), array_values((array) $row));
        }, $rows);

        // Only force landscape when asked (AR/AP aging) or the table is wider
        // than 6 columns — same threshold as before. Invoices do not use this path.
        $orientation = ($options['orientation'] ?? null) === 'landscape' || count($columns) > 6
            ? 'landscape'
            : 'portrait';

        $safeName = self::safeFilename($filename) . '.pdf';
        $pdf = \App\Support\PdfDocumentBuilder::loadView('exports.table-pdf', [
            'title' => $title,
            'columns' => $columns,
            'rows' => $rows,
            'generatedAt' => now(),
            'truncated' => $truncated,
            'totalRows' => $totalRows,
            'maxPdfRows' => $maxPdfRows,
            'periodLabel' => $options['periodLabel'] ?? null,
            'meta' => $options['meta'] ?? [],
            'note' => $options['note'] ?? null,
            'orientation' => $orientation,
            'company' => \App\Support\Documents\CompanyDocumentContext::make(),
        ])->setPaper('a4', $orientation);

        try {
            if ($disposition === 'inline') {
                return $pdf->stream($safeName);
            }

            return $pdf->download($safeName);
        } finally {
            if ($previousMemoryLimit !== false && function_exists('ini_set')) {
                @ini_set('memory_limit', $previousMemoryLimit);
            }
        }
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
