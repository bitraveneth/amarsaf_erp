<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\TallyExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TallyExportController extends Controller
{
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
