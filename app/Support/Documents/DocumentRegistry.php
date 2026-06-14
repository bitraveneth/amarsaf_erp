<?php

namespace App\Support\Documents;

use App\Models\Delivery;
use App\Models\GoodsReceipt;
use App\Models\Invoice;
use App\Models\LogisticsBill;
use App\Models\Order;
use App\Models\ProductionRun;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Route;
use InvalidArgumentException;

class DocumentRegistry
{
    public const TYPES = [
        'invoice' => [
            'model' => Invoice::class,
            'permission' => 'accounting.manage',
            'view' => 'documents.types.invoice',
            'back_route' => 'admin.finance.show',
            'filename' => 'invoice',
        ],
        'sales-order' => [
            'model' => Order::class,
            'permission' => 'sales.manage',
            'view' => 'documents.types.sales-order',
            'back_route' => 'admin.orders.show',
            'filename' => 'sales-order',
        ],
        'picking-list' => [
            'model' => Order::class,
            'permission' => 'sales.manage',
            'view' => 'documents.types.picking-list',
            'back_route' => 'admin.orders.show',
            'filename' => 'picking-list',
        ],
        'purchase-order' => [
            'model' => PurchaseOrder::class,
            'permission' => 'control.suppliers',
            'view' => 'documents.types.purchase-order',
            'back_route' => 'admin.purchase-orders.show',
            'filename' => 'purchase-order',
        ],
        'grn' => [
            'model' => GoodsReceipt::class,
            'permission' => 'inventory.manage',
            'view' => 'documents.types.grn',
            'back_route' => 'admin.goods-receipts.show',
            'filename' => 'grn',
        ],
        'production-order' => [
            'model' => ProductionRun::class,
            'permission' => 'manufacturing.manage',
            'view' => 'documents.types.production-order',
            'back_route' => 'admin.production.show',
            'filename' => 'production-order',
        ],
        'delivery-challan' => [
            'model' => Delivery::class,
            'permission' => 'control.warehouses',
            'view' => 'documents.types.delivery-challan',
            'back_route' => 'admin.deliveries.index',
            'filename' => 'delivery-challan',
        ],
        'packing-slip' => [
            'model' => Delivery::class,
            'permission' => 'control.warehouses',
            'view' => 'documents.types.packing-slip',
            'back_route' => 'admin.deliveries.index',
            'filename' => 'packing-slip',
        ],
        'pod' => [
            'model' => Delivery::class,
            'permission' => 'control.warehouses',
            'view' => 'documents.types.pod',
            'back_route' => 'admin.deliveries.index',
            'filename' => 'proof-of-delivery',
        ],
        'logistics-bill' => [
            'model' => LogisticsBill::class,
            'permission' => 'control.warehouses',
            'view' => 'documents.types.logistics-bill',
            'back_route' => 'admin.logistics-bills.show',
            'filename' => 'logistics-bill',
        ],
    ];

    public static function get(string $type): array
    {
        if (! isset(self::TYPES[$type])) {
            throw new InvalidArgumentException("Unknown document type [{$type}].");
        }

        return self::TYPES[$type];
    }

    public static function has(string $type): bool
    {
        return isset(self::TYPES[$type]);
    }

    public static function types(): array
    {
        return array_keys(self::TYPES);
    }

    public static function resolveModel(string $type, int $id): ?object
    {
        $definition = self::get($type);
        $class = $definition['model'];

        return $class::query()->find($id);
    }

    public static function filename(string $type, object $model): string
    {
        $definition = self::get($type);
        $prefix = $definition['filename'];

        $suffix = match ($type) {
            'invoice' => $model->number ?? (string) $model->getKey(),
            'purchase-order' => $model->number ?? (string) $model->getKey(),
            'grn' => $model->grn_number ?? (string) $model->getKey(),
            'production-order' => $model->order_number ?? (string) $model->getKey(),
            'sales-order', 'picking-list' => 'order-' . $model->getKey(),
            'delivery-challan', 'packing-slip', 'pod' => 'delivery-' . $model->getKey(),
            'logistics-bill' => $model->document_number ?? (string) $model->getKey(),
            default => (string) $model->getKey(),
        };

        return $prefix . '-' . preg_replace('/[^A-Za-z0-9._-]+/', '-', (string) $suffix);
    }

    public static function backUrl(string $type, object $model): ?string
    {
        $definition = self::get($type);
        $route = $definition['back_route'] ?? null;

        if (! is_string($route) || ! Route::has($route)) {
            return null;
        }

        return route($route, $model);
    }
}
