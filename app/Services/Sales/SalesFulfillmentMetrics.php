<?php

namespace App\Services\Sales;

use App\Models\Order;
use App\Models\OrderItem;

class SalesFulfillmentMetrics
{
    /**
     * @return array{
     *     ordered_qty: float,
     *     picked_qty: float,
     *     packed_qty: float,
     *     delivered_qty: float,
     *     picked_percent: float,
     *     packed_percent: float,
     *     delivered_percent: float,
     *     fulfillment_percent: float,
     *     is_fully_picked: bool,
     *     is_fully_packed: bool,
     *     is_fully_delivered: bool,
     * }
     */
    public static function summarize(Order $order): array
    {
        $items = $order->relationLoaded('items')
            ? $order->items
            : $order->items()->with('deliveryItems')->get();

        $orderedQty = 0.0;
        $pickedQty = 0.0;
        $packedQty = 0.0;
        $deliveredQty = 0.0;

        foreach ($items as $item) {
            $orderedQty += (float) $item->quantity;
            $pickedQty += (float) ($item->picked_quantity ?? 0);
            $packedQty += (float) ($item->packed_quantity ?? 0);
            $deliveredQty += self::deliveredQuantity($item);
        }

        $pickedPercent = self::percent($pickedQty, $orderedQty);
        $packedPercent = self::percent($packedQty, $orderedQty);
        $deliveredPercent = self::percent($deliveredQty, $orderedQty);

        $fulfillmentPercent = $deliveredPercent > 0
            ? $deliveredPercent
            : ($packedPercent > 0
                ? $packedPercent
                : ($pickedPercent > 0 ? $pickedPercent : 0.0));

        return [
            'ordered_qty' => $orderedQty,
            'picked_qty' => $pickedQty,
            'packed_qty' => $packedQty,
            'delivered_qty' => $deliveredQty,
            'picked_percent' => $pickedPercent,
            'packed_percent' => $packedPercent,
            'delivered_percent' => $deliveredPercent,
            'fulfillment_percent' => $fulfillmentPercent,
            'is_fully_picked' => $orderedQty > 0 && $pickedQty >= $orderedQty,
            'is_fully_packed' => $orderedQty > 0 && $packedQty >= $orderedQty,
            'is_fully_delivered' => $orderedQty > 0 && $deliveredQty >= $orderedQty,
        ];
    }

    public static function deliveredQuantity(OrderItem $item): float
    {
        $deliveryItems = $item->relationLoaded('deliveryItems')
            ? $item->deliveryItems
            : $item->deliveryItems()->get();

        return (float) $deliveryItems->sum('qty_delivered');
    }

    public static function dispatchedQuantity(OrderItem $item): float
    {
        $deliveryItems = $item->relationLoaded('deliveryItems')
            ? $item->deliveryItems
            : $item->deliveryItems()->get();

        return (float) $deliveryItems->sum('qty_dispatched');
    }

    public static function lineProgressPercent(OrderItem $item): int
    {
        $ordered = (float) $item->quantity;
        if ($ordered <= 0) {
            return 0;
        }

        $delivered = self::deliveredQuantity($item);
        if ($delivered > 0) {
            return (int) min(100, round(($delivered / $ordered) * 100));
        }

        $packed = (float) ($item->packed_quantity ?? 0);
        if ($packed > 0) {
            return (int) min(100, round(($packed / $ordered) * 100));
        }

        $picked = (float) ($item->picked_quantity ?? 0);
        if ($picked > 0) {
            return (int) min(100, round(($picked / $ordered) * 100));
        }

        return 0;
    }

    public static function applyFullPick(Order $order): void
    {
        foreach ($order->items as $item) {
            $item->update(['picked_quantity' => (float) $item->quantity]);
        }
    }

    public static function applyFullPack(Order $order): void
    {
        foreach ($order->items as $item) {
            $picked = (float) ($item->picked_quantity ?? 0);
            $target = $picked > 0 ? $picked : (float) $item->quantity;
            $item->update(['packed_quantity' => min($target, (float) $item->quantity)]);
        }
    }

    protected static function percent(float $part, float $whole): float
    {
        if ($whole <= 0) {
            return 0.0;
        }

        return min(100.0, round(($part / $whole) * 100, 1));
    }
}
