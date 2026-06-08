<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Accounting\MonthEndExportPackService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MonthEndExportController extends Controller
{
    public function download(Request $request, MonthEndExportPackService $pack): StreamedResponse
    {
        return $pack->download($request);
    }
}
