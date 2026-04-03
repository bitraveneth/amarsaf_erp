<?php

namespace App\Http\Controllers;

use App\Helpers\Permission;
use App\Models\Agent;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\ProductionRun;
use App\Models\SalesTarget;
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

        $agentCount = $agentReady ? Agent::where('is_active', true)->count() : 0;
        $totalOrderCount = $orderReady
            ? Order::where('order_type', '!=', 'return')->count()
            : 0;
        $returnOrderCount = $orderReady
            ? Order::where('order_type', 'return')->count()
            : 0;
        $currencyCode = config('app.currency', 'BDT');
        $today = Carbon::today();
        $targetMonth = $request->query('target_month');
        $currentMonthStart = $targetMonth
            ? Carbon::parse($targetMonth . '-01')->startOfMonth()
            : $today->copy()->startOfMonth();
        $currentMonthEnd = $currentMonthStart->copy()->endOfMonth();
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
        if ($invoiceReady) {
            $monthlyInvoices = Invoice::query()
                ->with(['creditNotes', 'receipts', 'advanceApplications'])
                ->whereBetween('issued_at', [$currentMonthStart->toDateString(), $currentMonthEnd->toDateString()])
                ->get();

            $monthlyAchieved = round((float) $monthlyInvoices->sum(function (Invoice $invoice) {
                return $invoice->net_sales_after_credits;
            }), 2);

            $todayAchieved = round((float) Invoice::query()
                ->with('creditNotes')
                ->whereDate('issued_at', $today)
                ->get()
                ->sum(function (Invoice $invoice) {
                    return $invoice->net_sales_after_credits;
                }), 2);

            $openInvoices = Invoice::query()
                ->with(['receipts', 'creditNotes', 'advanceApplications'])
                ->whereIn('status', ['issued', 'adjusted'])
                ->get();

            $outstandingReceivables = round((float) $openInvoices->sum(function (Invoice $invoice) {
                return $invoice->outstanding;
            }), 2);
        }

        $monthlyTargetProgress = $monthlySalesTarget > 0
            ? round(min(100, ($monthlyAchieved / $monthlySalesTarget) * 100), 2)
            : 0.0;
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
                $monthLabels[] = $month->format('M Y');

                $monthlyOrders[] = $orderReady
                    ? Order::where('order_type', '!=', 'return')
                        ->whereYear('delivery_date', $month->year)
                        ->whereMonth('delivery_date', $month->month)
                        ->count()
                    : 0;

                $monthlyRevenue[] = $invoiceReady
                    ? (float) Invoice::query()
                        ->with('creditNotes')
                        ->whereYear('issued_at', $month->year)
                        ->whereMonth('issued_at', $month->month)
                        ->get()
                        ->sum(function (Invoice $invoice) {
                            return $invoice->net_sales_after_credits;
                        })
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
                    ? (float) Invoice::query()
                        ->with('creditNotes')
                        ->whereDate('issued_at', $day)
                        ->get()
                        ->sum(function (Invoice $invoice) {
                            return $invoice->net_sales_after_credits;
                        })
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

        return view('admin.dashboard', compact(
            'metrics',
            'chartDays',
            'agentCount',
            'totalOrderCount',
            'returnOrderCount',
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
            'monthlyTargetProgress',
            'outstandingReceivables',
            'targetMonthOptions'
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
                    $data = $notification->data;

                    return [
                        'id' => $notification->id,
                        'key' => $data['dedupe_key'] ?? $notification->id,
                        'message' => $data['message'] ?? '',
                        'variant' => $data['type'] ?? 'info',
                        'source' => $data['source'] ?? 'System',
                        'created_at' => $notification->created_at,
                        'is_read' => !is_null($notification->read_at),
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
            ->map(function ($notification) {
                $data = $notification->data;
                $variant = $data['type'] ?? 'info';
                $source = $data['sender_name'] ?? $data['source'] ?? 'System';

                return [
                    'id' => $notification->id,
                    'message' => $data['message'] ?? '',
                    'variant' => $variant,
                    'source' => $source,
                    'time_label' => $notification->created_at?->diffForHumans() ?? 'Now',
                    'read_url' => route('admin.notifications.mark-read', $notification->id),
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'unread_count' => $user->unreadNotifications()->count(),
            'alerts' => $alerts,
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
