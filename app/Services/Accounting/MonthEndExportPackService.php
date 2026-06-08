<?php

namespace App\Services\Accounting;

use App\Helpers\Permission;
use App\Models\User;
use App\Services\TallyExportService;
use App\Support\ExportDateRange;
use App\Support\ModuleExportRegistry;
use App\Support\TabularExport;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class MonthEndExportPackService
{
    /** @var array<int, string> */
    protected const PACK_MODULES = [
        'profit-loss',
        'balance-sheet',
        'cash-flow',
        'trial-balance',
        'general-ledger',
        'ar-aging',
        'ap-aging',
        'vat-report',
        'sales-register',
        'outstanding-invoices',
        'outstanding-bills',
    ];

    public function download(Request $request): StreamedResponse
    {
        abort_unless(class_exists(ZipArchive::class), 503, 'Month-end ZIP requires the PHP zip extension. Enable extension=zip in php.ini and restart your web server.');

        abort_unless(Permission::can($request->user(), 'reports.view'), 403);

        [$from, $to] = $this->resolveRange($request);
        $suffix = ExportDateRange::filenameSuffix($request);
        $zipName = 'month-end-pack' . ($suffix ?: '-' . $from->format('Ymd') . '-' . $to->format('Ymd')) . '.zip';

        return response()->streamDownload(function () use ($request, $from, $to) {
            $tempPath = tempnam(sys_get_temp_dir(), 'saf-month-end-');
            $zipPath = $tempPath . '.zip';
            @unlink($tempPath);

            $zip = new ZipArchive();
            $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

            $readme = $this->readmeLines($from, $to);
            $zip->addFromString('README.txt', implode(PHP_EOL, $readme) . PHP_EOL);

            foreach (self::PACK_MODULES as $slug) {
                if (! $this->canExport($request->user(), $slug)) {
                    continue;
                }

                $export = ModuleExportRegistry::resolve($slug, $request);
                $filename = $export['filename'] . ExportDateRange::filenameSuffix($request) . '.csv';

                $zip->addFromString(
                    $filename,
                    TabularExport::csvContent($export['columns'], $export['rows'])
                );
            }

            if (Permission::can($request->user(), 'accounting.manage')) {
                $tally = app(TallyExportService::class);
                $zip->addFromString(
                    'tally-export' . ExportDateRange::filenameSuffix($request) . '.xml',
                    $tally->toXml($from, $to)
                );
            }

            $zip->close();

            readfile($zipPath);
            @unlink($zipPath);
        }, $zipName, [
            'Content-Type' => 'application/zip',
        ]);
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    protected function resolveRange(Request $request): array
    {
        $resolved = ExportDateRange::resolve($request);

        if ($resolved && $resolved['from'] && $resolved['to']) {
            return [$resolved['from']->copy()->startOfDay(), $resolved['to']->copy()->endOfDay()];
        }

        $now = now();

        return [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()];
    }

    protected function canExport(?User $user, string $slug): bool
    {
        $definition = ModuleExportRegistry::definitions()[$slug] ?? null;

        if (! $definition) {
            return false;
        }

        return Permission::can($user, $definition['permission']);
    }

    /**
     * @return array<int, string>
     */
    protected function readmeLines(Carbon $from, Carbon $to): array
    {
        return [
            'Saf ERP — Month-end export pack',
            'Generated: ' . now()->format('d M Y H:i'),
            'Period: ' . $from->format('d M Y') . ' – ' . $to->format('d M Y'),
            '',
            'Contents:',
            '- CSV files for financial statements, ledgers, aging, VAT, and open balances',
            '- tally-export*.xml when accounting.manage permission is granted',
            '',
            'Import CSVs into Excel or your audit workflow. Use Tally XML for external GL import.',
        ];
    }
}
