<?php

namespace App\Support\Documents;

use App\Models\Order;
use App\Models\StockEntry;

class PickingListBuilder
{
    public static function linesForOrder(Order $order): array
    {
        $order->loadMissing(['items.product']);

        $lines = [];

        foreach ($order->items as $item) {
            if (! $item->product) {
                continue;
            }

            $remaining = (int) $item->quantity;

            $entries = StockEntry::with('warehouse', 'batch')
                ->where('product_id', $item->product_id)
                ->where('status', 'available')
                ->get()
                ->sortBy(function ($entry) {
                    if ($entry->batch && $entry->batch->expiry_date) {
                        return $entry->batch->expiry_date;
                    }

                    if ($entry->batch && $entry->batch->production_date) {
                        return $entry->batch->production_date;
                    }

                    return $entry->created_at;
                });

            foreach ($entries as $entry) {
                if ($remaining <= 0) {
                    break;
                }

                $pickQty = min($remaining, (int) $entry->quantity);

                if ($pickQty <= 0) {
                    continue;
                }

                $lines[] = [
                    'sku' => $item->product->sku ?? '',
                    'name' => $item->product->name ?? '',
                    'quantity' => $pickQty,
                    'warehouse' => $entry->warehouse->name ?? null,
                    'batch' => $entry->batch->batch_code ?? null,
                ];

                $remaining -= $pickQty;
            }

            if ($remaining > 0) {
                $lines[] = [
                    'sku' => $item->product->sku ?? '',
                    'name' => $item->product->name ?? '',
                    'quantity' => $remaining,
                    'warehouse' => 'Unassigned (shortage)',
                    'batch' => null,
                ];
            }
        }

        return $lines;
    }
}
