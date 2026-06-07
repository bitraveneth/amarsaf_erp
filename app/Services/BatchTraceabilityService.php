<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\DeliveryItem;
use App\Models\GoodsReceiptItem;
use App\Models\InvoiceItem;
use App\Models\ProductionRun;

class BatchTraceabilityService
{
    public function trace(Batch $batch): array
    {
        $batch->loadMissing(['product', 'productionRuns.warehouse', 'productionRuns.materialIssues.items.component']);

        $productionRuns = $batch->productionRuns->map(fn (ProductionRun $run) => [
            'id' => $run->id,
            'order_number' => $run->order_number,
            'quantity' => (float) $run->quantity,
            'qc_status' => $run->qc_status,
            'warehouse' => $run->warehouse?->name,
            'material_cost' => (float) $run->material_total_cost,
            'confirmed_at' => $run->stock_confirmed_at?->format('Y-m-d H:i'),
        ]);

        $grnItems = GoodsReceiptItem::with(['goodsReceipt.supplier', 'product'])
            ->where('batch_id', $batch->id)
            ->get()
            ->map(fn (GoodsReceiptItem $item) => [
                'grn_number' => $item->goodsReceipt?->grn_number,
                'supplier' => $item->goodsReceipt?->supplier?->name,
                'product' => $item->product?->name,
                'quantity' => (float) $item->quantity,
                'received_at' => $item->goodsReceipt?->received_at?->format('Y-m-d'),
            ]);

        $deliveryItems = DeliveryItem::with(['delivery.order.agent', 'product'])
            ->where('batch_id', $batch->id)
            ->get()
            ->map(fn (DeliveryItem $item) => [
                'delivery_id' => $item->delivery_id,
                'order_id' => $item->delivery?->order_id,
                'agent' => $item->delivery?->order?->agent?->name,
                'product' => $item->product?->name,
                'quantity' => (float) $item->quantity,
                'status' => $item->delivery?->status,
            ]);

        $invoiceItems = InvoiceItem::with(['invoice.order.agent', 'product'])
            ->whereHas('invoice.order.delivery.items', function ($query) use ($batch) {
                $query->where('batch_id', $batch->id);
            })
            ->get()
            ->map(fn (InvoiceItem $item) => [
                'invoice_number' => $item->invoice?->number,
                'agent' => $item->invoice?->order?->agent?->name,
                'product' => $item->product?->name ?? $item->description,
                'quantity' => (float) $item->quantity,
                'line_total' => (float) $item->line_total,
            ]);

        return [
            'batch' => $batch,
            'productionRuns' => $productionRuns,
            'grnItems' => $grnItems,
            'deliveryItems' => $deliveryItems,
            'invoiceItems' => $invoiceItems,
        ];
    }
}
