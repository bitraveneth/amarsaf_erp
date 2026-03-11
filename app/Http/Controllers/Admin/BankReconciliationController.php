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
        [$from, $to] = $this->resolvePeriod($request);

        $receipts = Receipt::with('invoice')
            ->whereBetween('received_at', [$from, $to])
            ->orderBy('received_at')
            ->get();

        return view('admin.finance.reconciliation', compact('receipts', 'from', 'to'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date',
            'reconciled' => 'array',
            'reconciled.*' => 'integer|exists:receipts,id',
        ]);

        [$from, $to] = $this->resolvePeriod($request);

        $visibleIds = Receipt::query()
            ->whereBetween('received_at', [$from, $to])
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
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
            ->route('admin.finance.reconciliation', [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ])
            ->with('status', 'Receipt reconciliation updated.');
    }

    protected function resolvePeriod(Request $request): array
    {
        $from = $request->input('from')
            ? Carbon::parse($request->input('from'))
            : Carbon::now()->startOfMonth();
        $to = $request->input('to')
            ? Carbon::parse($request->input('to'))
            : Carbon::now()->endOfMonth();

        return [$from, $to];
    }
}
