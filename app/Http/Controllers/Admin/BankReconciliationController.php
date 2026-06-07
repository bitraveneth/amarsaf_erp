<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BillPayment;
use App\Models\Receipt;
use App\Services\Accounting\BankStatementImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class BankReconciliationController extends Controller
{
    public function index(Request $request)
    {
        [$from, $to, $range] = $this->resolvePeriod($request);

        $receipts = Receipt::with('invoice.order.agent')
            ->whereBetween('received_at', [$from, $to])
            ->orderBy('received_at')
            ->get();

        $billPayments = BillPayment::with('bill.supplier')
            ->whereBetween('paid_at', [$from, $to])
            ->orderBy('paid_at')
            ->get();

        $importResults = session('bank_import_results');

        $rangeOptions = [
            '7d' => 'Last 7 days',
            '1m' => 'Last 1 month',
            '3m' => 'Last 3 months',
            '1y' => 'Last 1 year',
            'all' => 'All time',
            'custom' => 'Custom range',
        ];

        $suggestedReceiptIds = collect(session('bank_import_receipt_ids', []))->map(fn ($id) => (int) $id)->all();
        $suggestedPaymentIds = collect(session('bank_import_payment_ids', []))->map(fn ($id) => (int) $id)->all();

        return view('admin.finance.reconciliation', compact(
            'receipts',
            'billPayments',
            'from',
            'to',
            'range',
            'rangeOptions',
            'importResults',
            'suggestedReceiptIds',
            'suggestedPaymentIds'
        ));
    }

    public function import(Request $request, BankStatementImportService $importService)
    {
        $data = $request->validate([
            'statement' => 'required|file|mimes:csv,txt|max:2048',
            'range' => 'nullable|string|in:7d,1m,3m,1y,all,custom',
            'from' => 'nullable|date',
            'to' => 'nullable|date',
        ]);

        [$from, $to, $range] = $this->resolvePeriod($request);
        $contents = file_get_contents($data['statement']->getRealPath());
        $rows = $importService->parseCsv($contents);

        if ($rows === []) {
            return redirect()
                ->route('admin.finance.reconciliation', [
                    'range' => $range,
                    'from' => $from->toDateString(),
                    'to' => $to->toDateString(),
                ])
                ->withErrors(['statement' => 'No valid rows were found in the CSV file.']);
        }

        $receiptMatches = $importService->matchReceipts($rows, $from, $to);
        $paymentMatches = $importService->matchBillPayments($rows, $from, $to);

        $matchedReceiptIds = collect($receiptMatches)
            ->pluck('match_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $matchedPaymentIds = collect($paymentMatches)
            ->pluck('match_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        return redirect()
            ->route('admin.finance.reconciliation', [
                'range' => $range,
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ])
            ->with('bank_import_results', [
                'rows' => array_merge($receiptMatches, $paymentMatches),
                'matched_receipts' => count($matchedReceiptIds),
                'matched_payments' => count($matchedPaymentIds),
                'total_rows' => count($rows),
            ])
            ->with('bank_import_receipt_ids', $matchedReceiptIds)
            ->with('bank_import_payment_ids', $matchedPaymentIds)
            ->with('status', 'Bank statement imported. Review suggested matches and save reconciliation.');
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'range' => 'nullable|string|in:7d,1m,3m,1y,all,custom',
            'from' => 'nullable|date',
            'to' => 'nullable|date',
            'reconciled' => 'array',
            'reconciled.*' => 'integer|exists:receipts,id',
            'reconciled_payments' => 'array',
            'reconciled_payments.*' => 'integer|exists:bill_payments,id',
        ]);

        [$from, $to, $range] = $this->resolvePeriod($request);

        $visibleReceiptIds = Receipt::query()
            ->whereBetween('received_at', [$from, $to])
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values();

        $reconciledReceiptIds = collect($data['reconciled'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->intersect($visibleReceiptIds)
            ->values();

        if ($visibleReceiptIds->isNotEmpty()) {
            Receipt::whereIn('id', $visibleReceiptIds)->update(['reconciled' => false]);
            Receipt::whereIn('id', $reconciledReceiptIds)->update(['reconciled' => true]);
        }

        $visiblePaymentIds = BillPayment::query()
            ->whereBetween('paid_at', [$from, $to])
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values();

        $reconciledPaymentIds = collect($data['reconciled_payments'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->intersect($visiblePaymentIds)
            ->values();

        if ($visiblePaymentIds->isNotEmpty()) {
            BillPayment::whereIn('id', $visiblePaymentIds)->update(['reconciled' => false]);
            BillPayment::whereIn('id', $reconciledPaymentIds)->update(['reconciled' => true]);
        }

        return redirect()
            ->route('admin.finance.reconciliation', [
                'range' => $range,
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ])
            ->with('status', 'Bank reconciliation updated.');
    }

    protected function resolvePeriod(Request $request): array
    {
        $range = $request->input('range');
        $hasCustomDates = $request->filled('from') || $request->filled('to');

        if ($range === 'custom' || (! $range && $hasCustomDates)) {
            $from = $request->filled('from')
                ? Carbon::parse($request->input('from'))->startOfDay()
                : Carbon::now()->startOfMonth();
            $to = $request->filled('to')
                ? Carbon::parse($request->input('to'))->endOfDay()
                : Carbon::now()->endOfMonth();

            if ($from->gt($to)) {
                [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
            }

            return [$from, $to, 'custom'];
        }

        $now = Carbon::now();

        switch ($range) {
            case '7d':
                $from = $now->copy()->subDays(6)->startOfDay();
                $to = $now->copy()->endOfDay();
                break;
            case '1m':
                $from = $now->copy()->subMonth()->startOfDay();
                $to = $now->copy()->endOfDay();
                break;
            case '3m':
                $from = $now->copy()->subMonths(3)->startOfDay();
                $to = $now->copy()->endOfDay();
                break;
            case '1y':
                $from = $now->copy()->subYear()->startOfDay();
                $to = $now->copy()->endOfDay();
                break;
            case 'all':
                $oldestReceiptDate = Receipt::query()->min('received_at');
                $newestReceiptDate = Receipt::query()->max('received_at');
                $oldestPaymentDate = BillPayment::query()->min('paid_at');
                $newestPaymentDate = BillPayment::query()->max('paid_at');

                $fromCandidates = array_filter([$oldestReceiptDate, $oldestPaymentDate]);
                $toCandidates = array_filter([$newestReceiptDate, $newestPaymentDate]);

                $from = $fromCandidates
                    ? Carbon::parse(min($fromCandidates))->startOfDay()
                    : $now->copy()->startOfMonth();
                $to = $toCandidates
                    ? Carbon::parse(max($toCandidates))->endOfDay()
                    : $now->copy()->endOfMonth();
                break;
            default:
                $from = $now->copy()->startOfMonth();
                $to = $now->copy()->endOfMonth();
                $range = 'custom';
                break;
        }

        return [$from, $to, $range];
    }
}
