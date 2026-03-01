<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Batch;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\Product;
use App\Models\PackagingType;
use App\Models\TaxClass;
use App\Models\Invoice;
use App\Models\ProductionRun;
use App\Models\Receipt;
use App\Models\User;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class AdminController extends Controller
{
    /**
     * Display a simple admin dashboard.
     */
    public function index()
    {
        $usersTableReady = Schema::hasTable('users');
        $productTableReady = Schema::hasTable('products');
        $packagingReady = Schema::hasTable('packaging_types');
        $taxReady = Schema::hasTable('tax_classes');
        $batchReady = Schema::hasTable('batches');
        $agentReady = Schema::hasTable('agents');
        $orderReady = Schema::hasTable('orders');
        $deliveryReady = Schema::hasTable('deliveries');
        $warehouseReady = Schema::hasTable('warehouses');
        $invoiceReady = Schema::hasTable('invoices');
        $receiptReady = Schema::hasTable('receipts');
        $productionReady = Schema::hasTable('production_runs');

        $userCount = $usersTableReady ? User::count() : 0;

        $productCount = $productTableReady ? Product::count() : 0;
        $recentProducts = $productTableReady
            ? Product::with(['packagingType', 'taxClass'])->orderByDesc('created_at')->take(6)->get()
            : collect();
        $packagingCount = $packagingReady ? PackagingType::count() : 0;
        $taxClassCount = $taxReady ? TaxClass::count() : 0;
        $batchCount = $batchReady ? Batch::count() : 0;
        $agentCount = $agentReady ? Agent::count() : 0;
        // Treat "orders" on the dashboard as sales orders only – exclude return
        // orders so that the high-level metric reflects outbound sales. Returns
        // are surfaced separately in a dedicated card.
        $totalOrderCount = $orderReady
            ? Order::where('order_type', '!=', 'return')->count()
            : 0;
        $returnOrderCount = $orderReady
            ? Order::where('order_type', 'return')->count()
            : 0;
        $openOrderCount = $orderReady
            ? Order::whereIn('status', ['draft', 'confirmed', 'picked', 'packed', 'dispatched'])->count()
            : 0;
        $todayOrders = $orderReady
            ? Order::whereDate('delivery_date', Carbon::today())->count()
            : 0;
        $inTransitDeliveries = $deliveryReady
            ? Delivery::whereIn('status', ['scheduled', 'in_transit'])->count()
            : 0;
        $exceptionDeliveriesToday = $deliveryReady
            ? Delivery::where('status', 'exception')->whereDate('updated_at', Carbon::today())->count()
            : 0;
        $warehouseCount = $warehouseReady ? Warehouse::count() : 0;

        $expiringSoonCount = $batchReady
            ? Batch::whereNotNull('expiry_date')
                ->whereBetween('expiry_date', [Carbon::today(), Carbon::today()->copy()->addDays(30)])
                ->count()
            : 0;

        $todayProductionQty = $productionReady
            ? ProductionRun::where('qc_status', 'approved')
                ->whereDate('created_at', Carbon::today())
                ->sum('quantity')
            : 0;

        $outstandingReceivables = 0;
        if ($invoiceReady) {
            $openInvoices = Invoice::with('receipts')
                ->whereIn('status', ['issued', 'adjusted'])
                ->get();

            $outstandingReceivables = $openInvoices->sum(function (Invoice $invoice) {
                $gross = ($invoice->net_total + $invoice->vat_amount) - $invoice->withholding;
                $paid = $invoice->receipts->sum('amount');

                return max($gross - $paid, 0);
            });
        }

        $todayReceipts = $receiptReady
            ? Receipt::whereDate('received_at', Carbon::today())->sum('amount')
            : 0;

        // Monthly aggregates for the current year
        $monthLabels = [];
        $monthlyOrders = [];
        $monthlyReceipts = [];

        if ($orderReady || $receiptReady) {
            for ($m = 1; $m <= 12; $m++) {
                $monthLabels[] = Carbon::create(null, $m, 1)->format('M');

                $monthlyOrders[] = $orderReady
                    ? Order::where('order_type', '!=', 'return')
                        ->whereYear('delivery_date', Carbon::today()->year)
                        ->whereMonth('delivery_date', $m)
                        ->count()
                    : 0;

                $monthlyReceipts[] = $receiptReady
                    ? Receipt::whereYear('received_at', Carbon::today()->year)
                        ->whereMonth('received_at', $m)
                        ->sum('amount')
                    : 0;
            }
        }

        // Simple 7‑day time‑series for dashboard charts
        $chartDays = collect();
        if ($orderReady || $receiptReady) {
            for ($i = 6; $i >= 0; $i--) {
                $day = Carbon::today()->copy()->subDays($i);

                $ordersForDay = $orderReady
                    ? Order::where('order_type', '!=', 'return')
                        ->whereDate('delivery_date', $day)
                        ->count()
                    : 0;

                $receiptsForDay = $receiptReady
                    ? Receipt::whereDate('received_at', $day)->sum('amount')
                    : 0;

                $chartDays->push([
                    'label' => $day->format('d M'),
                    'orders' => $ordersForDay,
                    'receipts' => $receiptsForDay,
                ]);
            }
        }

        $metrics = [];

        if ($productTableReady) {
            $metrics[] = [
                'label' => 'Products',
                'value' => number_format($productCount),
                'detail' => 'Active SKUs in the catalog',
            ];
        }

        if ($packagingReady) {
            $metrics[] = [
                'label' => 'Packaging types',
                'value' => number_format($packagingCount),
                'detail' => 'Bottle/crate/carton definitions',
            ];
        }

        if ($agentReady) {
            $metrics[] = [
                'label' => 'Active agents',
                'value' => number_format($agentCount),
                'detail' => 'Selling partners in your network',
            ];
        }

        if ($orderReady) {
            $metrics[] = [
                'label' => 'Open orders',
                'value' => number_format($openOrderCount),
                'detail' => 'Draft / confirmed / in fulfilment',
            ];
        }

        if ($deliveryReady) {
            $metrics[] = [
                'label' => 'Deliveries in pipeline',
                'value' => number_format($inTransitDeliveries),
                'detail' => 'Scheduled or in transit',
            ];
        }

        if ($warehouseReady) {
            $metrics[] = [
                'label' => 'Warehouses',
                'value' => number_format($warehouseCount),
                'detail' => 'Plants, depots & consignment',
            ];
        }

        $masterSummary = [
            'products' => $productCount,
            'packaging' => $packagingCount,
            'taxClasses' => $taxClassCount,
            'batches' => $batchCount,
        ];

        $alerts = [];

        if ($expiringSoonCount > 0) {
            $alerts[] = [
                'message' => "{$expiringSoonCount} batches expiring within 30 days",
                'variant' => 'error', // bad / urgent
            ];
        }

        if ($exceptionDeliveriesToday > 0) {
            $alerts[] = [
                'message' => "{$exceptionDeliveriesToday} deliveries marked as exception today",
                'variant' => 'error', // bad / exception
            ];
        }

        if ($todayOrders > 0) {
            $alerts[] = [
                'message' => "{$todayOrders} orders scheduled for delivery today",
                'variant' => 'success', // good news
            ];
        }

        if ($outstandingReceivables > 0) {
            $alerts[] = [
                'message' => 'Outstanding receivables of BDT ' . number_format($outstandingReceivables, 2),
                'variant' => 'error', // bad / attention needed
            ];
        }

        // Recent orders (for dashboard table)
        $recentOrders = $orderReady
            ? Order::with('agent')->orderByDesc('id')->take(10)->get()
            : collect();

        return view('admin.dashboard', compact(
            'metrics',
            'usersTableReady',
            'masterSummary',
            'productTableReady',
            'packagingReady',
            'taxReady',
            'batchCount',
            'todayOrders',
            'expiringSoonCount',
            'todayProductionQty',
            'outstandingReceivables',
            'todayReceipts',
            'alerts',
            'chartDays',
            'agentCount',
            'totalOrderCount',
            'returnOrderCount',
            'monthLabels',
            'monthlyOrders',
            'monthlyReceipts',
            'recentOrders'
        ));
    }

    /**
     * Show a dedicated notifications view listing all alerts.
     */
    public function notifications()
    {
        $alerts = [];

        $batchReady = Schema::hasTable('batches');
        $orderReady = Schema::hasTable('orders');
        $deliveryReady = Schema::hasTable('deliveries');
        $invoiceReady = Schema::hasTable('invoices');
        $receiptReady = Schema::hasTable('receipts');

        if ($batchReady) {
            $expiringSoonCount = Batch::whereNotNull('expiry_date')
                ->whereBetween('expiry_date', [Carbon::today(), Carbon::today()->copy()->addDays(30)])
                ->count();

            if ($expiringSoonCount > 0) {
                $alerts[] = [
                    'message' => "{$expiringSoonCount} batches expiring within 30 days",
                    'variant' => 'error',
                ];
            }
        }

        if ($deliveryReady) {
            $exceptionDeliveriesToday = Delivery::where('status', 'exception')
                ->whereDate('updated_at', Carbon::today())
                ->count();

            if ($exceptionDeliveriesToday > 0) {
                $alerts[] = [
                    'message' => "{$exceptionDeliveriesToday} deliveries marked as exception today",
                    'variant' => 'error',
                ];
            }
        }

        if ($orderReady) {
            $todayOrders = Order::whereDate('delivery_date', Carbon::today())->count();

            if ($todayOrders > 0) {
                $alerts[] = [
                    'message' => "{$todayOrders} orders scheduled for delivery today",
                    'variant' => 'success',
                ];
            }
        }

        if ($invoiceReady && $receiptReady) {
            $openInvoices = Invoice::with('receipts')
                ->whereIn('status', ['issued', 'adjusted'])
                ->get();

            $outstandingReceivables = $openInvoices->sum(function (Invoice $invoice) {
                $gross = ($invoice->net_total + $invoice->vat_amount) - $invoice->withholding;
                $paid = $invoice->receipts->sum('amount');

                return max($gross - $paid, 0);
            });

            if ($outstandingReceivables > 0) {
                $alerts[] = [
                    'message' => 'Outstanding receivables of BDT ' . number_format($outstandingReceivables, 2),
                    'variant' => 'error',
                ];
            }
        }

        // Load user-specific notifications (e.g. new batches) if the notifications table exists
        $userNotifications = collect();
        if (Schema::hasTable('notifications') && auth()->check()) {
            $userNotifications = auth()->user()
                ->notifications()
                ->latest()
                ->take(20)
                ->get();
        }

        return view('admin.notifications.index', [
            'alerts' => $alerts,
            'userNotifications' => $userNotifications,
        ]);
    }

    public function markNotificationRead(string $notificationId)
    {
        $notification = auth()->user()
            ->notifications()
            ->whereKey($notificationId)
            ->firstOrFail();

        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('status', 'Notification marked as read.');
    }

    public function markNotificationUnread(string $notificationId)
    {
        $notification = auth()->user()
            ->notifications()
            ->whereKey($notificationId)
            ->firstOrFail();

        if (!is_null($notification->read_at)) {
            $notification->markAsUnread();
        }

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('status', 'Notification marked as unread.');
    }

    public function markAllNotificationsRead()
    {
        $user = auth()->user();
        $user->unreadNotifications->markAsRead();

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('status', 'All notifications marked as read.');
    }

    public function markAllNotificationsUnread()
    {
        $user = auth()->user();
        $user->readNotifications()->update(['read_at' => null]);

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('status', 'All notifications marked as unread.');
    }
}
