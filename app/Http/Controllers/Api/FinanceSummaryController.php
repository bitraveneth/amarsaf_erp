<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\StockEntry;
use App\Services\Accounting\AgingReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class FinanceSummaryController extends Controller
{
    public function summary(Request $request)
    {
        $user = $request->user();
        abort_unless($user && $user->hasAnyRole(['admin', 'super_admin']), 403);

        $asOf = Carbon::today()->endOfDay();
        $aging = app(AgingReportService::class)->receivableAging($asOf);

        $openInvoices = Invoice::whereIn('status', ['issued', 'adjusted'])->count();
        $stockValue = (float) StockEntry::where('status', 'available')->selectRaw('SUM(quantity) as qty')->value('qty');

        return response()->json([
            'as_of' => $asOf->toDateString(),
            'accounts_receivable' => $aging['subledgerTotal'],
            'gl_accounts_receivable' => $aging['glBalance'],
            'open_invoices' => $openInvoices,
            'available_stock_units' => $stockValue,
        ]);
    }
}
