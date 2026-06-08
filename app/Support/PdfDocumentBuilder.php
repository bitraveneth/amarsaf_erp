<?php

namespace App\Support;

use Barryvdh\DomPDF\Facade\Pdf;

class PdfDocumentBuilder
{
    public static function loadView(string $view, array $data = [], array $mergeData = [], ?string $encoding = null)
    {
        self::prepareRuntime();

        $pdf = Pdf::loadView($view, $data, $mergeData, $encoding);

        return self::applyOptions($pdf);
    }

    public static function applyOptions($pdf)
    {
        $pdf->setOption('isRemoteEnabled', false);
        $pdf->setOption('isPhpEnabled', false);
        $pdf->setOption('defaultFont', 'DejaVu Sans');

        return $pdf;
    }

    public static function prepareRuntime(): void
    {
        @ini_set('max_execution_time', '120');
        @set_time_limit(120);
    }

    public static function fileUri(?string $absolutePath): ?string
    {
        if (! is_string($absolutePath) || $absolutePath === '' || ! is_file($absolutePath)) {
            return null;
        }

        $normalized = str_replace('\\', '/', $absolutePath);

        if (preg_match('/^[A-Za-z]:\//', $normalized) === 1) {
            return 'file:///' . $normalized;
        }

        return 'file://' . $normalized;
    }
}
