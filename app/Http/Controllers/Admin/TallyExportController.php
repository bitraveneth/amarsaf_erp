<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\TallyExportService;
use App\Support\ExportDateRange;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TallyExportController extends Controller
{
    public function redirect(Request $request)
    {
        return redirect()->route('admin.export-center', array_filter(array_merge(
            ['module' => 'tally-xml'],
            ExportDateRange::queryParams($request)
        )));
    }

    public function preview(Request $request, TallyExportService $tally)
    {
        [$from, $to] = $this->resolveRange($request);
        $query = ExportDateRange::queryParams($request);

        return response()->json(array_merge($tally->preview($from, $to), [
            'download_url' => route('admin.exports.tally.download', $query),
        ]));
    }

    public function download(Request $request, TallyExportService $tally): StreamedResponse
    {
        [$from, $to] = $this->resolveRange($request);
        $xml = $tally->toXml($from, $to);
        $filename = 'tally-export-' . $from->format('Ymd') . '-' . $to->format('Ymd') . '.xml';

        return response()->streamDownload(function () use ($xml) {
            echo $xml;
        }, $filename, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }

    protected function resolveRange(Request $request): array
    {
        $resolved = ExportDateRange::resolve($request);

        if ($resolved) {
            return [$resolved['from'], $resolved['to']];
        }

        $from = $request->filled('from')
            ? Carbon::parse($request->input('from'))->startOfDay()
            : Carbon::now()->startOfMonth();
        $to = $request->filled('to')
            ? Carbon::parse($request->input('to'))->endOfDay()
            : Carbon::now()->endOfMonth();

        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return [$from, $to];
    }
}
