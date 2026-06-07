<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ResolvesDashboardPeriod;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Order;
use App\Models\Receipt;
use App\Support\DashboardChartBuilder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class SalesDashboardController extends Controller
{
    use ResolvesDashboardPeriod;

    public function __invoke(Request $request)
    {
        [$from, $to, $range] = $this->resolveDashboardPeriod($request);
        $currencyCode = config('app.currency', 'BDT');

        $invoices = Invoice::query()
            ->select(['id', 'order_id', 'issued_at', 'net_total', 'vat_amount', 'withholding'])
            ->with(['order.agent', 'creditNotes', 'items.product', 'receipts', 'advanceApplications'])
            ->whereBetween('issued_at', [$from, $to])
            ->orderByDesc('issued_at')
            ->get();

        $totalInvoices = $invoices->count();
        $netSales = round((float) $invoices->sum(function (Invoice $invoice) use ($from, $to) {
            return $invoice->netSalesAfterCreditsInRange($from, $to);
        }), 2);
        $vatTotal = round((float) $invoices->sum(function (Invoice $invoice) use ($from, $to) {
            return max(0.0, (float) $invoice->vat_amount - $invoice->creditNotesVatTotalInRange($from, $to));
        }), 2);
        $withholdingTotal = $invoices->sum('withholding');
        $collected = Receipt::whereBetween('received_at', [$from, $to])->sum('amount');
        $outstanding = round((float) $invoices->sum(fn (Invoice $invoice) => (float) $invoice->outstandingAsOf($to)), 2);
        $collectionRate = $netSales > 0 ? ($collected / $netSales) * 100 : 0;

        $topAgents = DashboardChartBuilder::normalizeRankList(
            $invoices
                ->filter(fn ($invoice) => $invoice->order && $invoice->order->agent)
                ->groupBy(fn ($invoice) => (string) $invoice->order->agent->id)
                ->map(function ($group) use ($from, $to) {
                    $agent = $group->first()->order->agent;

                    return [
                        'label' => $agent->name ?? 'Unknown agent',
                        'value' => $group->sum(fn (Invoice $invoice) => $invoice->netSalesAfterCreditsInRange($from, $to)),
                        'meta' => $agent->zone ?? null,
                    ];
                })
                ->values()
        );

        $topProducts = DashboardChartBuilder::normalizeRankList(
            $this->topProductsForPeriod($invoices, $from, $to)->map(fn (array $row) => [
                'label' => $row['product_name'],
                'value' => $row['net'],
                'meta' => 'Qty ' . number_format($row['qty'], 0),
            ])
        );

        $recentOrders = Order::with('agent')
            ->where('order_type', '!=', 'return')
            ->whereBetween('delivery_date', [$from->toDateString(), $to->toDateString()])
            ->orderByDesc('delivery_date')
            ->limit(5)
            ->get()
            ->map(fn (Order $order) => [
                'label' => '#' . $order->id . ' · ' . ($order->agent?->name ?? 'Unknown'),
                'value' => (float) $order->total,
                'meta' => optional($order->delivery_date)->format('d M Y'),
                'href' => route('admin.orders.show', $order),
            ])
            ->all();

        $chart = DashboardChartBuilder::revenueAndCollectionsSeries($from, $to);

        return view('admin.sales.dashboard', [
            'from' => $from,
            'to' => $to,
            'range' => $range,
            'rangeOptions' => $this->dashboardRangeOptions(),
            'periodLabel' => $this->dashboardPeriodLabel($from, $to),
            'currencyCode' => $currencyCode,
            'totalInvoices' => $totalInvoices,
            'netSales' => $netSales,
            'vatTotal' => $vatTotal,
            'withholdingTotal' => $withholdingTotal,
            'collected' => $collected,
            'outstanding' => $outstanding,
            'collectionRate' => $collectionRate,
            'topAgents' => $topAgents,
            'topProducts' => $topProducts,
            'recentOrders' => $recentOrders,
            'chartLabels' => $chart['labels'],
            'chartRevenue' => $chart['values'],
            'chartCollections' => $chart['secondary'],
        ]);
    }

    protected function topProductsForPeriod(Collection $invoices, Carbon $from, Carbon $to): Collection
    {
        return $invoices
            ->flatMap(function (Invoice $invoice) use ($from, $to) {
                $invoiceNet = max((float) $invoice->net_total, 0.0);
                $creditNet = min($invoice->creditNotesNetTotalInRange($from, $to), $invoiceNet);

                return $invoice->items->map(function (InvoiceItem $item) use ($invoiceNet, $creditNet) {
                    $lineTotal = (float) $item->line_total;
                    $creditShare = $invoiceNet > 0
                        ? $creditNet * ($lineTotal / $invoiceNet)
                        : 0.0;

                    return [
                        'product_id' => $item->product_id,
                        'product_name' => $item->product?->name ?? 'Unknown product',
                        'qty' => (float) $item->quantity,
                        'net' => max(0.0, $lineTotal - $creditShare),
                    ];
                });
            })
            ->groupBy(fn (array $row) => (string) ($row['product_id'] ?? 'unknown'))
            ->map(function (Collection $group) {
                $first = $group->first();

                return [
                    'product_id' => $first['product_id'],
                    'product_name' => $first['product_name'],
                    'qty' => $group->sum('qty'),
                    'net' => $group->sum('net'),
                ];
            })
            ->values();
    }
}
