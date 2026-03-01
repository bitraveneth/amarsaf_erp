<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Receipt;
use Carbon\Carbon;

class SalesDashboardController extends Controller
{
    public function __invoke()
    {
        $today = Carbon::today();
        $startOfMonth = $today->copy()->startOfMonth();
        $endOfMonth = $today->copy()->endOfMonth();

        $invoices = Invoice::query()
            ->select(['id', 'order_id', 'issued_at', 'net_total', 'vat_amount', 'withholding'])
            ->with(['order.agent'])
            ->withSum('receipts', 'amount')
            ->withSum('creditNotes', 'amount')
            ->whereBetween('issued_at', [$startOfMonth, $endOfMonth])
            ->orderByDesc('issued_at')
            ->get();

        $totalInvoices = $invoices->count();
        $netSales = $invoices->sum('net_total');
        $vatTotal = $invoices->sum('vat_amount');
        $withholdingTotal = $invoices->sum('withholding');

        $collected = Receipt::whereBetween('received_at', [$startOfMonth, $endOfMonth])->sum('amount');

        $outstanding = $invoices->sum(function (Invoice $invoice) {
            $grossTotal = $invoice->net_total + $invoice->vat_amount;
            $cashTotal = $grossTotal - $invoice->withholding;
            $credited = (float) ($invoice->credit_notes_sum_amount ?? 0);
            $paid = (float) ($invoice->receipts_sum_amount ?? 0);

            return max($cashTotal - $credited - $paid, 0);
        });

        // Simple breakdowns
        $topAgents = $invoices
            ->filter(fn ($invoice) => $invoice->order && $invoice->order->agent)
            ->groupBy(fn ($invoice) => $invoice->order->agent->name)
            ->map(fn ($group) => $group->sum('net_total'))
            ->sortDesc()
            ->take(5);

        $topProducts = collect();

        if ($invoices->isNotEmpty()) {
            $invoiceIds = $invoices->pluck('id');
            $items = \App\Models\InvoiceItem::with('product')
                ->whereIn('invoice_id', $invoiceIds)
                ->get();

            $topProducts = $items
                ->groupBy(fn ($item) => $item->product?->name ?? 'Unknown product')
                ->map(fn ($group) => [
                    'qty' => $group->sum('quantity'),
                    'net' => $group->sum('line_total'),
                ])
                ->sortByDesc('net')
                ->take(5);
        }

        $recentOrders = Order::with('agent')
            ->where('order_type', '!=', 'return')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('admin.sales.dashboard', [
            'periodLabel' => $startOfMonth->format('d M Y') . ' – ' . $endOfMonth->format('d M Y'),
            'totalInvoices' => $totalInvoices,
            'netSales' => $netSales,
            'vatTotal' => $vatTotal,
            'withholdingTotal' => $withholdingTotal,
            'collected' => $collected,
            'outstanding' => $outstanding,
            'topAgents' => $topAgents,
            'topProducts' => $topProducts,
            'recentOrders' => $recentOrders,
        ]);
    }
}
