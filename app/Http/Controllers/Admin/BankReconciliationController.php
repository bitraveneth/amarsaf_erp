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
        $ids = $request->input('reconciled', []);
        if (!is_array($ids)) {
            $ids = [];
        }

        Receipt::whereIn('id', $ids)->update(['reconciled' => true]);

        return redirect()->route('admin.finance.reconciliation')->with('status', 'Receipts marked as reconciled.');
    }
}

