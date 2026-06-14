<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\BillOfMaterial;
use App\Models\Delivery;
use App\Models\Invoice;
use App\Models\NotificationDispatchLog;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductionRun;
use App\Models\User;
use App\Notifications\SystemAlertNotification;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class ErpNotificationService
{
    public function buildSystemAlerts(): array
    {
        $alerts = [];

        if (Schema::hasTable('batches')) {
            $expiringSoonCount = Batch::whereNotNull('expiry_date')
                ->whereBetween('expiry_date', [Carbon::today(), Carbon::today()->copy()->addDays(30)])
                ->count();

            if ($expiringSoonCount > 0) {
                $alerts[] = NotificationActionResolver::attachLink([
                    'key' => 'expiring_batches_' . Carbon::today()->toDateString() . '_' . $expiringSoonCount,
                    'message' => "{$expiringSoonCount} batches expiring within 30 days",
                    'variant' => 'error',
                    'source' => 'Inventory',
                    'title' => 'Inventory alert',
                    'context' => ['expiring_batches' => $expiringSoonCount],
                ]);
            }
        }

        if (Schema::hasTable('products') && Schema::hasColumn('products', 'reorder_level')) {
            $availableByProduct = \App\Models\StockEntry::query()
                ->selectRaw('product_id, SUM(quantity) as total')
                ->where('status', 'available')
                ->groupBy('product_id')
                ->pluck('total', 'product_id');

            $lowStockCount = \App\Models\Product::stockTracked()
                ->where('is_active', true)
                ->whereNotNull('reorder_level')
                ->get()
                ->filter(function ($product) use ($availableByProduct) {
                    return (float) ($availableByProduct[$product->id] ?? 0) < (float) $product->reorder_level;
                })
                ->count();

            if ($lowStockCount > 0) {
                $alerts[] = NotificationActionResolver::attachLink([
                    'key' => 'low_stock_' . Carbon::today()->toDateString() . '_' . $lowStockCount,
                    'message' => "{$lowStockCount} products are below reorder level",
                    'variant' => 'warning',
                    'source' => 'Inventory',
                    'title' => 'Low stock alert',
                    'context' => ['low_stock_products' => $lowStockCount],
                ]);
            }
        }

        if (Schema::hasTable('deliveries')) {
            $exceptionDeliveriesToday = Delivery::where('status', 'exception')
                ->whereDate('updated_at', Carbon::today())
                ->count();

            if ($exceptionDeliveriesToday > 0) {
                $alerts[] = NotificationActionResolver::attachLink([
                    'key' => 'delivery_exceptions_' . Carbon::today()->toDateString() . '_' . $exceptionDeliveriesToday,
                    'message' => "{$exceptionDeliveriesToday} deliveries marked as exception today",
                    'variant' => 'error',
                    'source' => 'Delivery',
                    'title' => 'Delivery alert',
                    'context' => ['delivery_exceptions_today' => $exceptionDeliveriesToday],
                ]);
            }
        }

        if (Schema::hasTable('orders')) {
            $todayOrders = Order::whereDate('delivery_date', Carbon::today())->count();

            if ($todayOrders > 0) {
                $alerts[] = NotificationActionResolver::attachLink([
                    'key' => 'orders_due_' . Carbon::today()->toDateString() . '_' . $todayOrders,
                    'message' => "{$todayOrders} orders scheduled for delivery today",
                    'variant' => 'success',
                    'source' => 'Sales',
                    'title' => 'Sales update',
                    'context' => ['orders_due_today' => $todayOrders],
                ]);
            }
        }

        if (Schema::hasTable('invoices') && Schema::hasTable('receipts')) {
            $openInvoices = Invoice::with(['receipts', 'creditNotes', 'advanceApplications'])
                ->whereIn('status', ['issued', 'adjusted'])
                ->get();

            $outstandingReceivables = $openInvoices->sum(function (Invoice $invoice) {
                return (float) $invoice->outstanding;
            });

            if ($outstandingReceivables > 0) {
                $formatted = number_format($outstandingReceivables, 2);
                $alerts[] = NotificationActionResolver::attachLink([
                    'key' => 'receivables_' . Carbon::today()->toDateString() . '_' . number_format($outstandingReceivables, 2, '.', ''),
                    'message' => 'Outstanding receivables of BDT ' . $formatted,
                    'variant' => 'error',
                    'source' => 'Finance',
                    'title' => 'Finance alert',
                    'context' => ['outstanding_receivables' => $outstandingReceivables],
                ]);
            }
        }

        if (Schema::hasTable('bill_of_materials') && Schema::hasTable('products')) {
            $finishedProductIds = Product::query()
                ->where(function ($query) {
                    $query->whereNull('product_type')->orWhere('product_type', 'finished');
                })
                ->where('is_active', true)
                ->pluck('id');

            $coveredProductIds = BillOfMaterial::query()
                ->where('is_active', true)
                ->whereIn('product_id', $finishedProductIds)
                ->pluck('product_id')
                ->unique();

            $missingBomCount = $finishedProductIds->diff($coveredProductIds)->count();

            if ($missingBomCount > 0) {
                $alerts[] = NotificationActionResolver::attachLink([
                    'key' => 'missing_bom_' . Carbon::today()->toDateString() . '_' . $missingBomCount,
                    'message' => "{$missingBomCount} finished product(s) have no active manufacturing recipe",
                    'variant' => 'warning',
                    'source' => 'Manufacturing',
                    'title' => 'Missing BOM alert',
                    'context' => ['missing_bom_products' => $missingBomCount],
                ]);
            }
        }

        if (Schema::hasTable('production_runs')) {
            $pendingQcCount = ProductionRun::query()
                ->where('qc_status', '!=', 'approved')
                ->count();

            if ($pendingQcCount > 0) {
                $alerts[] = NotificationActionResolver::attachLink([
                    'key' => 'pending_production_qc_' . Carbon::today()->toDateString() . '_' . $pendingQcCount,
                    'message' => "{$pendingQcCount} production run(s) awaiting QC approval",
                    'variant' => 'warning',
                    'source' => 'Manufacturing',
                    'title' => 'Production QC alert',
                    'context' => ['pending_production_qc' => $pendingQcCount],
                ]);
            }
        }

        return $alerts;
    }

    public function publishSystemAlerts(array $alerts, array $roles = ['super_admin', 'admin']): void
    {
        if (empty($alerts) || !Schema::hasTable('notifications')) {
            return;
        }

        $recipientQuery = User::query();

        if (Schema::hasTable('user_roles')) {
            $recipientQuery->with('userRoles');
        }

        $recipients = $recipientQuery
            ->get()
            ->filter(fn (User $user) => $user->hasAnyRole($roles))
            ->values();

        foreach ($recipients as $user) {
            foreach ($alerts as $alert) {
                $dedupeKey = (string) ($alert['key'] ?? '');
                if ($dedupeKey === '') {
                    continue;
                }

                $alert = NotificationActionResolver::attachLink($alert);
                $source = $alert['source'] ?? 'System';
                $alert['title'] = $alert['title'] ?? ($source . ' alert');
                $alert['sender_name'] = $alert['sender_name'] ?? $source;

                $dispatchLog = NotificationDispatchLog::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'dedupe_key' => $dedupeKey,
                    ],
                    [
                        'sent_at' => now(),
                    ]
                );

                if (!$dispatchLog->wasRecentlyCreated) {
                    continue;
                }

                $user->notify(new SystemAlertNotification($alert));
            }
        }
    }

    public function unreadSystemNotificationsForUser(User $user, int $limit = 10): Collection
    {
        return $user->unreadNotifications()
            ->where('type', SystemAlertNotification::class)
            ->latest()
            ->take($limit)
            ->get();
    }
}
