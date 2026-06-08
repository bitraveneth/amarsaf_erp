<?php

namespace App\Services\Inventory;

use App\Helpers\Permission;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\NotificationDispatchLog;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockEntry;
use App\Models\StockMovement;
use App\Models\User;
use App\Notifications\SystemAlertNotification;
use App\Services\Accounting\InventoryAccountingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GrnWorkflowService
{
    public function notifyPurchaseOrderApproved(PurchaseOrder $purchaseOrder): void
    {
        $purchaseOrder->loadMissing(['supplier', 'items']);

        $openLines = $purchaseOrder->items->filter(function (PurchaseOrderItem $item) {
            return max((float) $item->quantity - (float) $item->received_quantity, 0) > 0;
        })->count();

        $this->notifyUsersWithPermission(
            'inventory.grn.create',
            [
                'key' => 'po_approved_' . $purchaseOrder->id,
                'title' => 'PO awaiting receipt',
                'message' => "{$purchaseOrder->number} approved — {$openLines} line(s) ready for GRN ({$purchaseOrder->supplier?->name}).",
                'variant' => 'info',
                'source' => 'Procurement',
                'link' => route('admin.purchase-orders.receive', $purchaseOrder),
                'context' => [
                    'purchase_order_id' => $purchaseOrder->id,
                    'purchase_order_number' => $purchaseOrder->number,
                ],
            ]
        );
    }

    public function notifyGrnPendingApproval(GoodsReceipt $receipt): void
    {
        $receipt->loadMissing(['purchaseOrder', 'supplier']);

        $poRef = $receipt->purchaseOrder?->number;
        $suffix = $poRef ? " for {$poRef}" : '';

        $payload = [
            'key' => 'grn_pending_' . $receipt->id,
            'title' => 'GRN pending approval',
            'message' => "{$receipt->grn_number}{$suffix} needs warehouse and procurement sign-off before stock posts.",
            'variant' => 'warning',
            'source' => 'GRN',
            'link' => route('admin.goods-receipts.show', $receipt),
            'context' => [
                'goods_receipt_id' => $receipt->id,
                'grn_number' => $receipt->grn_number,
            ],
        ];

        $this->notifyUsersWithPermission('inventory.grn.approve.warehouse', $payload);
        $this->notifyUsersWithPermission('purchase.grn.approve', $payload);
    }

    public function approveWarehouse(GoodsReceipt $receipt, User $user): GoodsReceipt
    {
        if ($receipt->status !== 'pending_approval') {
            abort(422, 'Only GRNs pending approval can be signed off.');
        }

        if (! Permission::can($user, 'inventory.grn.approve.warehouse')) {
            abort(403, 'You do not have permission to approve GRNs for warehouse.');
        }

        if ($receipt->warehouse_approved_at) {
            return $receipt;
        }

        $receipt->warehouse_approved_at = now();
        $receipt->warehouse_approved_by = $user->id;
        $receipt->save();

        return $this->tryFinalizeAndPost($receipt->fresh());
    }

    public function approveProcurement(GoodsReceipt $receipt, User $user): GoodsReceipt
    {
        if ($receipt->status !== 'pending_approval') {
            abort(422, 'Only GRNs pending approval can be signed off.');
        }

        if (! Permission::can($user, 'purchase.grn.approve')) {
            abort(403, 'You do not have permission to approve GRNs for procurement.');
        }

        if ($receipt->procurement_approved_at) {
            return $receipt;
        }

        $receipt->procurement_approved_at = now();
        $receipt->procurement_approved_by = $user->id;
        $receipt->save();

        return $this->tryFinalizeAndPost($receipt->fresh());
    }

    public function tryFinalizeAndPost(GoodsReceipt $receipt): GoodsReceipt
    {
        if ($receipt->status !== 'pending_approval') {
            return $receipt;
        }

        if (! $receipt->warehouse_approved_at || ! $receipt->procurement_approved_at) {
            return $receipt;
        }

        DB::transaction(function () use ($receipt) {
            $receipt->loadMissing(['items.purchaseOrderItem', 'purchaseOrder.items']);

            foreach ($receipt->items as $grnItem) {
                if ($grnItem->qc_status !== 'approved') {
                    continue;
                }

                if ($grnItem->purchase_order_item_id && $grnItem->purchaseOrderItem) {
                    $poItem = $grnItem->purchaseOrderItem;
                    $poItem->received_quantity = (float) $poItem->received_quantity + (float) $grnItem->quantity;
                    $poItem->save();
                }

                $product = $grnItem->product;
                if ($product && $product->isStockTracked()) {
                    $entry = StockEntry::create([
                        'purchase_bill_id' => $receipt->purchase_bill_id,
                        'goods_receipt_id' => $receipt->id,
                        'warehouse_id' => $receipt->warehouse_id,
                        'warehouse_location_id' => $grnItem->warehouse_location_id,
                        'product_id' => $grnItem->product_id,
                        'batch_id' => $grnItem->batch_id,
                        'quantity' => $grnItem->quantity,
                        'status' => 'available',
                    ]);

                    StockMovement::recordFor(
                        $entry,
                        'goods-receipt',
                        (float) $grnItem->quantity,
                        'Posted from GRN ' . $receipt->grn_number
                    );
                }
            }

            if ($receipt->purchaseOrder) {
                $this->recalculatePurchaseOrderStatus($receipt->purchaseOrder->fresh(['items']));
            }

            $receipt->status = 'posted';
            $receipt->save();

            app(InventoryAccountingService::class)->postGoodsReceipt(
                $receipt->fresh(['items.product', 'warehouse'])
            );
        });

        return $receipt->fresh();
    }

    public function recalculatePurchaseOrderStatus(PurchaseOrder $purchaseOrder): void
    {
        $allReceived = $purchaseOrder->items->isNotEmpty()
            && $purchaseOrder->items->every(function (PurchaseOrderItem $item) {
                return (float) $item->received_quantity >= (float) $item->quantity;
            });

        $hasAnyReceived = $purchaseOrder->items->contains(function (PurchaseOrderItem $item) {
            return (float) $item->received_quantity > 0;
        });

        if ($allReceived) {
            $purchaseOrder->status = 'received';
        } elseif ($hasAnyReceived) {
            $purchaseOrder->status = 'partial_received';
        } else {
            $purchaseOrder->status = 'approved';
        }

        $purchaseOrder->save();
    }

    public function pendingReceiptPurchaseOrders(?array $warehouseIds = null)
    {
        return PurchaseOrder::query()
            ->with(['supplier', 'items.product'])
            ->whereIn('status', ['approved', 'partial_received'])
            ->whereHas('items', function ($query) {
                $query->whereRaw('quantity > received_quantity');
            })
            ->orderByDesc('order_date')
            ->get()
            ->filter(function (PurchaseOrder $order) {
                return $order->items->contains(function (PurchaseOrderItem $item) {
                    return max((float) $item->quantity - (float) $item->received_quantity, 0) > 0;
                });
            })
            ->values();
    }

    protected function notifyUsersWithPermission(string $permission, array $payload): void
    {
        if (! Schema::hasTable('notifications')) {
            return;
        }

        $dedupeKey = (string) ($payload['key'] ?? '');
        if ($dedupeKey === '') {
            return;
        }

        $recipients = User::query()
            ->get()
            ->filter(fn (User $user) => Permission::can($user, $permission));

        foreach ($recipients as $user) {
            $dispatchLog = NotificationDispatchLog::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'dedupe_key' => $dedupeKey,
                ],
                [
                    'sent_at' => now(),
                ]
            );

            if (! $dispatchLog->wasRecentlyCreated) {
                continue;
            }

            $user->notify(new SystemAlertNotification($payload));
        }
    }
}
