<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Receipt;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class BankReconciliationController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->query('from')
            ? Carbon::parse($request->query('from'))
            : Carbon::now()->startOfMonth();
        $to = $request->query('to')
            ? Carbon::parse($request->query('to'))
            : Carbon::now()->endOfMonth();

        $receipts = Receipt::with('invoice')
            ->whereBetween('received_at', [$from, $to])
            ->orderBy('received_at')
            ->get();

        return view('admin.finance.reconciliation', compact('receipts', 'from', 'to'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'visible_receipts' => 'array',
            'visible_receipts.*' => 'integer|exists:receipts,id',
            'reconciled' => 'array',
            'reconciled.*' => 'integer|exists:receipts,id',
        ]);

        $visibleIds = collect($data['visible_receipts'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $reconciledIds = collect($data['reconciled'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->intersect($visibleIds)
            ->values();

        if ($visibleIds->isNotEmpty()) {
            Receipt::whereIn('id', $visibleIds)->update(['reconciled' => false]);
            Receipt::whereIn('id', $reconciledIds)->update(['reconciled' => true]);
        }

        return redirect()
            ->route('admin.finance.reconciliation')
            ->with('status', 'Receipt reconciliation updated.');
    }
}
