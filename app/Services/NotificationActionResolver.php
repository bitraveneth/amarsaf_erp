<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Route;

class NotificationActionResolver
{
    public static function resolve(array $data, ?string $notificationType = null): string
    {
        $key = (string) ($data['dedupe_key'] ?? '');

        if ($key !== '') {
            if (preg_match('/^po_approved_(\d+)$/', $key, $matches)) {
                return self::resolvePurchaseOrderReceiveUrl((int) $matches[1]);
            }

            if (preg_match('/^grn_pending_(\d+)$/', $key, $matches) && Route::has('admin.goods-receipts.show')) {
                return route('admin.goods-receipts.show', $matches[1]);
            }

            if (preg_match('/^bom_(?:draft|activated)_(\d+)$/', $key, $matches) && Route::has('admin.boms.show')) {
                return route('admin.boms.show', $matches[1]);
            }
        }

        if (! empty($data['link']) && self::isSafeUrl((string) $data['link'])) {
            return (string) $data['link'];
        }

        if (! empty($data['batch_id']) && Route::has('admin.batches.show')) {
            return route('admin.batches.show', $data['batch_id']);
        }

        if ($key !== '') {
            if (str_starts_with($key, 'expiring_batches_') && Route::has('admin.batches.index')) {
                return route('admin.batches.index');
            }

            if (str_starts_with($key, 'low_stock_') && Route::has('admin.inventory.low-stock')) {
                return route('admin.inventory.low-stock');
            }

            if (str_starts_with($key, 'delivery_exceptions_') && Route::has('admin.deliveries.index')) {
                return route('admin.deliveries.index');
            }

            if (str_starts_with($key, 'orders_due_') && Route::has('admin.orders.index')) {
                return route('admin.orders.index', ['delivery_date' => now()->toDateString()]);
            }

            if (str_starts_with($key, 'receivables_') && Route::has('admin.finance.index')) {
                return route('admin.finance.index');
            }

            if (str_starts_with($key, 'missing_bom_') && Route::has('admin.boms.create')) {
                return route('admin.boms.index');
            }

            if (str_starts_with($key, 'pending_production_qc_') && Route::has('admin.production.index')) {
                return route('admin.production.index');
            }
        }

        if (Route::has('admin.dashboard')) {
            return route('admin.dashboard') . '#needs-attention';
        }

        return Route::has('admin.notifications.index')
            ? route('admin.notifications.index')
            : url('/admin');
    }

    /**
     * @return array<string, mixed>
     */
    public static function present(DatabaseNotification $notification): array
    {
        $data = is_array($notification->data) ? $notification->data : [];
        $isSystem = $notification->type === \App\Notifications\SystemAlertNotification::class;

        return [
            'id' => $notification->id,
            'title' => $data['title'] ?? ($data['sender_name'] ?? ($data['source'] ?? 'Notification')),
            'message' => $data['message'] ?? '',
            'variant' => $data['type'] ?? 'info',
            'source' => $data['sender_name'] ?? ($data['source'] ?? ($isSystem ? 'System' : 'General')),
            'time_label' => $notification->created_at?->diffForHumans() ?? 'Now',
            'created_at' => $notification->created_at,
            'action_url' => self::resolve($data, $notification->type),
            'open_url' => route('admin.notifications.open', $notification->id),
            'is_read' => ! is_null($notification->read_at),
            'dedupe_key' => $data['dedupe_key'] ?? null,
        ];
    }

    public static function attachLink(array $alert): array
    {
        $alert['link'] = self::resolve([
            'dedupe_key' => $alert['key'] ?? null,
            'link' => $alert['link'] ?? null,
            'source' => $alert['source'] ?? null,
        ]);

        return $alert;
    }

    protected static function isSafeUrl(string $url): bool
    {
        if (str_starts_with($url, '/')) {
            return true;
        }

        $appUrl = rtrim((string) config('app.url'), '/');

        return $appUrl !== '' && str_starts_with($url, $appUrl);
    }

    protected static function resolvePurchaseOrderReceiveUrl(int $purchaseOrderId): string
    {
        $purchaseOrder = PurchaseOrder::with('items')->find($purchaseOrderId);

        if (! $purchaseOrder) {
            return Route::has('admin.purchase-orders.index')
                ? route('admin.purchase-orders.index')
                : url('/admin/purchase-orders');
        }

        $isReceivable = in_array($purchaseOrder->status, ['approved', 'partial_received'], true)
            && $purchaseOrder->items->contains(function ($item) {
                return max((float) $item->quantity - (float) $item->received_quantity, 0) > 0;
            });

        if ($isReceivable && Route::has('admin.purchase-orders.receive')) {
            return route('admin.purchase-orders.receive', $purchaseOrder);
        }

        if (Route::has('admin.purchase-orders.show')) {
            return route('admin.purchase-orders.show', $purchaseOrder);
        }

        return Route::has('admin.purchase-orders.index')
            ? route('admin.purchase-orders.index')
            : url('/admin/purchase-orders');
    }
}
