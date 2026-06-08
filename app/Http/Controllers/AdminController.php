<?php

namespace App\Http\Controllers;

use App\Helpers\Permission;
use App\Models\Agent;
use App\Notifications\SystemAlertNotification;
use App\Services\NotificationActionResolver;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductionRun;
use App\Models\SalesTarget;
use App\Models\StockEntry;
use App\Support\InvoiceRevenueMetrics;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AdminController extends Controller
{
    /**
     * Display a simple admin dashboard.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Role/permission-based dashboard routing:
        // non-admin users should land on their functional dashboard.
        if ($user && ! $user->hasAnyRole(['admin', 'super_admin'])) {
            if (Permission::can($user, 'sales.manage')) {
                return redirect()->route('admin.sales.dashboard');
            }

            if (Permission::can($user, 'manufacturing.manage')) {
                return redirect()->route('admin.manufacturing.dashboard');
            }

            if (Permission::can($user, 'accounting.manage')) {
                return redirect()->route('admin.accounting.dashboard');
            }

            if (Permission::can($user, 'reports.view')) {
                return redirect()->route('admin.reports.dashboard');
            }

            return view('admin.dashboard-not-implemented', [
                'roleKeys' => $user->roleKeys(),
                'userName' => $user->name,
            ]);
        }

        $agentReady = Schema::hasTable('agents');
        $orderReady = Schema::hasTable('orders');
        $invoiceReady = Schema::hasTable('invoices');
        $productionReady = Schema::hasTable('production_runs');
        $salesTargetReady = Schema::hasTable('sales_targets');
        $stockReady = Schema::hasTable('stock_entries');
        $productReady = Schema::hasTable('products');

        $agentCount = $agentReady ? Agent::where('is_active', true)->count() : 0;
        $totalOrderCount = $orderReady
            ? Order::where('order_type', '!=', 'return')->count()
            : 0;
        $returnOrderCount = $orderReady
            ? Order::where('order_type', 'return')->count()
            : 0;
        $pendingDeliveryCount = $orderReady
            ? Order::where('order_type', '!=', 'return')
                ->whereIn('status', ['confirmed', 'picked', 'packed', 'dispatched'])
                ->count()
            : 0;
        $currencyCode = config('app.currency', 'BDT');
        $today = Carbon::today();
        $targetMonth = $request->query('target_month');
        $currentMonthStart = $targetMonth
            ? Carbon::parse($targetMonth . '-01')->startOfMonth()
            : $today->copy()->startOfMonth();
        $currentMonthEnd = $currentMonthStart->isSameMonth($today) && $currentMonthStart->isSameYear($today)
            ? $today->copy()->endOfDay()
            : $currentMonthStart->copy()->endOfMonth();
        $currentMonthLabel = $currentMonthStart->format('F Y');
        $salesRangeMonths = (int) $request->query('sales_range', 12);
        if (! in_array($salesRangeMonths, [3, 6, 12], true)) {
            $salesRangeMonths = 12;
        }
        $metrics = [
            [
                'label' => 'Active agents',
                'value' => number_format($agentCount),
                'detail' => 'Selling partners in your network',
            ],
        ];

        $monthlySalesTarget = 0.0;
        $monthlyTargetBasis = 'No monthly sales target configured';
        if ($salesTargetReady) {
            $activeTargets = SalesTarget::query()
                ->whereDate('period_start', '<=', $currentMonthEnd->toDateString())
                ->whereDate('period_end', '>=', $currentMonthStart->toDateString())
                ->get(['agent_id', 'employee_id', 'target_value']);

            $agentTargetTotal = (float) $activeTargets
                ->whereNotNull('agent_id')
                ->sum('target_value');
            $employeeTargetTotal = (float) $activeTargets
                ->whereNull('agent_id')
                ->whereNotNull('employee_id')
                ->sum('target_value');

            if ($agentTargetTotal > 0) {
                $monthlySalesTarget = round($agentTargetTotal, 2);
                $monthlyTargetBasis = 'Based on active agent sales targets';
            } elseif ($employeeTargetTotal > 0) {
                $monthlySalesTarget = round($employeeTargetTotal, 2);
                $monthlyTargetBasis = 'Based on active employee sales targets';
            }
        }

        $monthlyAchieved = 0.0;
        $todayAchieved = 0.0;
        $outstandingReceivables = 0.0;
        $collectionsThisMonth = 0.0;
        $overdueInvoiceCount = 0;
        $todayProductionQty = 0.0;
        $pendingQcCount = 0;
        $todayOrderCount = 0;
        $monthOrderCount = 0;
        $monthReturnCount = 0;
        $lowStockAlertCount = 0;
        if ($invoiceReady) {
            $monthlyAchieved = round(InvoiceRevenueMetrics::sumNetSalesAfterCreditsInRange(
                $currentMonthStart,
                $currentMonthEnd,
                $currentMonthStart,
                $currentMonthEnd
            ), 2);

            $todayAchieved = round(InvoiceRevenueMetrics::sumNetSalesAfterCreditsInRange(
                $today,
                $today,
                $today,
                $today
            ), 2);

            $outstandingReceivables = InvoiceRevenueMetrics::sumOutstandingAsOf($today->copy()->endOfDay());

            $collectionsThisMonth = round(InvoiceRevenueMetrics::sumReceiptsInRange(
                $currentMonthStart,
                $currentMonthEnd
            ), 2);

            $overdueInvoiceCount = InvoiceRevenueMetrics::countOverdueInvoices($today);
        }

        if ($orderReady) {
            $todayOrderCount = Order::query()
                ->where('order_type', '!=', 'return')
                ->whereDate('delivery_date', $today)
                ->count();

            $monthOrderCount = Order::query()
                ->where('order_type', '!=', 'return')
                ->whereYear('delivery_date', $currentMonthStart->year)
                ->whereMonth('delivery_date', $currentMonthStart->month)
                ->whereDate('delivery_date', '<=', $currentMonthEnd->toDateString())
                ->count();

            $monthReturnCount = Order::query()
                ->where('order_type', 'return')
                ->whereBetween('created_at', [$currentMonthStart, $currentMonthEnd])
                ->count();
        }

        if ($productionReady) {
            $todayProductionQty = round((float) ProductionRun::query()
                ->where('qc_status', 'approved')
                ->whereDate('created_at', $today)
                ->sum('quantity'), 2);

            $pendingQcCount = ProductionRun::query()
                ->where('qc_status', '!=', 'approved')
                ->count();
        }

        if ($stockReady && $productReady) {
            $availableByProduct = StockEntry::query()
                ->selectRaw('product_id, SUM(quantity) as qty')
                ->where('status', 'available')
                ->groupBy('product_id')
                ->pluck('qty', 'product_id');

            $trackedProducts = Product::query()
                ->sellable()
                ->stockTracked()
                ->get(['id', 'reorder_level']);

            $lowStockAlertCount = $trackedProducts
                ->filter(function ($product) use ($availableByProduct) {
                    $threshold = (int) ($product->reorder_level ?? 0);
                    if ($threshold <= 0) {
                        $threshold = 10;
                    }

                    return (float) ($availableByProduct[$product->id] ?? 0) <= $threshold;
                })
                ->count();
        }

        $targetGap = max(0, round($monthlySalesTarget - $monthlyAchieved, 2));
        $collectionRate = $monthlyAchieved > 0
            ? round(min(100, ($collectionsThisMonth / $monthlyAchieved) * 100), 1)
            : 0.0;

        $opsAlerts = collect([
            [
                'label' => 'Pending QC approval',
                'value' => $pendingQcCount,
                'caption' => 'Production runs waiting for quality check',
                'tone' => $pendingQcCount > 0 ? 'warning' : 'neutral',
                'href' => route('admin.production.index'),
            ],
            [
                'label' => 'Low stock SKUs',
                'value' => $lowStockAlertCount,
                'caption' => 'Sellable items at or below reorder level',
                'tone' => $lowStockAlertCount > 0 ? 'error' : 'neutral',
                'href' => route('admin.inventory.low-stock'),
            ],
            [
                'label' => 'Overdue invoices',
                'value' => $overdueInvoiceCount,
                'caption' => 'Open invoices past due date',
                'tone' => $overdueInvoiceCount > 0 ? 'error' : 'neutral',
                'href' => route('admin.finance.index'),
            ],
            [
                'label' => 'Pending deliveries',
                'value' => $pendingDeliveryCount,
                'caption' => 'Orders confirmed through dispatched',
                'tone' => $pendingDeliveryCount > 0 ? 'warning' : 'neutral',
                'href' => route('admin.deliveries.index'),
            ],
        ])->filter(fn (array $alert) => (int) $alert['value'] > 0)->values();

        $monthlyTargetProgress = $monthlySalesTarget > 0
            ? round(min(100, ($monthlyAchieved / $monthlySalesTarget) * 100), 2)
            : 0.0;
        $monthlyTargetProgressRaw = $monthlySalesTarget > 0
            ? round(($monthlyAchieved / $monthlySalesTarget) * 100, 2)
            : 0.0;

        $yesterdayAchieved = 0.0;
        if ($invoiceReady) {
            $yesterday = $today->copy()->subDay();
            $yesterdayAchieved = round(InvoiceRevenueMetrics::sumNetSalesAfterCreditsInRange(
                $yesterday,
                $yesterday,
                $yesterday,
                $yesterday
            ), 2);
        }

        $todayChangePercent = $yesterdayAchieved > 0
            ? round((($todayAchieved - $yesterdayAchieved) / $yesterdayAchieved) * 100, 1)
            : null;
        $targetMonthOptions = collect(range(0, 11))
            ->map(function (int $offset) use ($today) {
                $month = $today->copy()->startOfMonth()->subMonths($offset);

                return [
                    'value' => $month->format('Y-m'),
                    'label' => $month->format('F Y'),
                ];
            })
            ->all();

        $monthLabels = [];
        $monthlyOrders = [];
        $monthlyRevenue = [];

        if ($orderReady || $invoiceReady) {
            for ($offset = $salesRangeMonths - 1; $offset >= 0; $offset--) {
                $month = $today->copy()->startOfMonth()->subMonths($offset);
                $periodEnd = $month->isSameMonth($today) && $month->isSameYear($today)
                    ? $today->copy()->endOfDay()
                    : $month->copy()->endOfMonth();
                $monthLabels[] = $month->format('M Y');

                $monthlyOrders[] = $orderReady
                    ? Order::where('order_type', '!=', 'return')
                        ->whereYear('delivery_date', $month->year)
                        ->whereMonth('delivery_date', $month->month)
                        ->whereDate('delivery_date', '<=', $periodEnd->toDateString())
                        ->count()
                    : 0;

                $monthlyRevenue[] = $invoiceReady
                    ? round(InvoiceRevenueMetrics::sumNetSalesAfterCreditsInRange(
                        $month->copy()->startOfMonth(),
                        $periodEnd,
                        $month->copy()->startOfMonth(),
                        $periodEnd
                    ), 2)
                    : 0;
            }
        }

        $chartDays = collect();
        if ($orderReady || $invoiceReady || $productionReady) {
            for ($i = 6; $i >= 0; $i--) {
                $day = $today->copy()->subDays($i);

                $ordersForDay = $orderReady
                    ? Order::where('order_type', '!=', 'return')
                        ->whereDate('delivery_date', $day)
                        ->count()
                    : 0;

                $revenueForDay = $invoiceReady
                    ? round(InvoiceRevenueMetrics::sumNetSalesAfterCreditsInRange(
                        $day,
                        $day,
                        $day,
                        $day
                    ), 2)
                    : 0;

                $productionForDay = $productionReady
                    ? (float) ProductionRun::where('qc_status', 'approved')
                        ->whereDate('created_at', $day)
                        ->sum('quantity')
                    : 0;

                $chartDays->push([
                    'label' => $day->format('d M'),
                    'orders' => $ordersForDay,
                    'revenue' => round($revenueForDay, 2),
                    'production' => round($productionForDay, 2),
                ]);
            }
        }

        $recentOrders = $orderReady
            ? Order::with('agent')
                ->where('order_type', '!=', 'return')
                ->orderByDesc('id')
                ->take(10)
                ->get()
            : collect();

        if ($request->ajax() || $request->boolean('ajax')) {
            $section = $request->query('section');

            $payload = [];

            if (! $section || $section === 'sales') {
                $payload['monthlySale'] = [
                    'labels' => $monthLabels,
                    'orders' => $monthlyOrders,
                    'rangeMonths' => $salesRangeMonths,
                ];
            }

            if (! $section || $section === 'target') {
                $payload['monthlyTarget'] = [
                    'periodLabel' => $currentMonthLabel,
                    'targetBasis' => $monthlyTargetBasis,
                    'targetValue' => round($monthlySalesTarget, 2),
                    'achievedValue' => round($monthlyAchieved, 2),
                    'todayValue' => round($todayAchieved, 2),
                    'progressValue' => round($monthlyTargetProgress, 2),
                    'progressValueRaw' => round($monthlyTargetProgressRaw, 2),
                    'todayChangePercent' => $todayChangePercent,
                ];
            }

            return response()->json($payload);
        }

        return view('admin.dashboard', compact(
            'metrics',
            'chartDays',
            'agentCount',
            'totalOrderCount',
            'returnOrderCount',
            'pendingDeliveryCount',
            'monthLabels',
            'monthlyOrders',
            'monthlyRevenue',
            'salesRangeMonths',
            'recentOrders',
            'currencyCode',
            'currentMonthLabel',
            'monthlySalesTarget',
            'monthlyTargetBasis',
            'monthlyAchieved',
            'todayAchieved',
            'todayProductionQty',
            'lowStockAlertCount',
            'monthlyTargetProgress',
            'monthlyTargetProgressRaw',
            'yesterdayAchieved',
            'todayChangePercent',
            'outstandingReceivables',
            'targetMonthOptions',
            'collectionsThisMonth',
            'overdueInvoiceCount',
            'pendingQcCount',
            'todayOrderCount',
            'monthOrderCount',
            'monthReturnCount',
            'targetGap',
            'collectionRate',
            'opsAlerts'
        ));
    }

    /**
     * Show a dedicated notifications view listing all alerts.
     */
    public function notifications()
    {
        $alerts = collect();
        $userNotifications = collect();
        $totalUnreadCount = 0;

        if (Schema::hasTable('notifications') && auth()->check()) {
            $user = auth()->user();
            $totalUnreadCount = $user->unreadNotifications()->count();

            $systemNotifications = $user->notifications()
                ->where('type', SystemAlertNotification::class)
                ->latest()
                ->take(20)
                ->get();

            $alerts = $systemNotifications
                ->map(function ($notification) {
                    $presented = NotificationActionResolver::present($notification);

                    return [
                        'id' => $presented['id'],
                        'key' => $presented['dedupe_key'] ?? $notification->id,
                        'message' => $presented['message'],
                        'variant' => $presented['variant'],
                        'source' => $presented['source'],
                        'created_at' => $presented['created_at'],
                        'is_read' => $presented['is_read'],
                        'action_url' => $presented['action_url'],
                        'open_url' => $presented['open_url'],
                    ];
                })
                ->values();

            $userNotifications = $user->notifications()
                ->where('type', '!=', SystemAlertNotification::class)
                ->latest()
                ->take(20)
                ->get();
        }

        return view('admin.notifications.index', [
            'alerts' => $alerts->all(),
            'userNotifications' => $userNotifications,
            'totalUnreadCount' => $totalUnreadCount,
        ]);
    }

    public function headerNotifications()
    {
        if (!auth()->check() || !Schema::hasTable('notifications')) {
            return response()->json([
                'success' => true,
                'unread_count' => 0,
                'alerts' => [],
            ]);
        }

        $user = auth()->user();

        $alerts = $user->unreadNotifications()
            ->latest()
            ->take(10)
            ->get()
            ->map(fn ($notification) => NotificationActionResolver::present($notification))
            ->values();

        return response()->json([
            'success' => true,
            'unread_count' => $user->unreadNotifications()->count(),
            'alerts' => $alerts,
        ]);
    }

    public function openNotification(string $notificationId)
    {
        $notification = auth()->user()
            ->notifications()
            ->whereKey($notificationId)
            ->firstOrFail();

        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        $data = is_array($notification->data) ? $notification->data : [];
        $url = NotificationActionResolver::resolve($data, $notification->type);

        return redirect($url);
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

        if (request()->ajax() || request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'unread_count' => auth()->user()->unreadNotifications()->count(),
            ]);
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

        if (request()->ajax() || request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'unread_count' => auth()->user()->unreadNotifications()->count(),
            ]);
        }

        return back()->with('status', 'Notification marked as unread.');
    }

    public function markAllNotificationsRead()
    {
        $user = auth()->user();
        $user->unreadNotifications->markAsRead();

        if (request()->ajax() || request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'unread_count' => auth()->user()->unreadNotifications()->count(),
            ]);
        }

        return back()->with('status', 'All notifications marked as read.');
    }

    public function markAllNotificationsUnread()
    {
        $user = auth()->user();
        $user->readNotifications()->update(['read_at' => null]);

        if (request()->ajax() || request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'unread_count' => auth()->user()->unreadNotifications()->count(),
            ]);
        }

        return back()->with('status', 'All notifications marked as unread.');
    }
}
