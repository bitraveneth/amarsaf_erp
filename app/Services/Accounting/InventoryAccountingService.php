<?php

namespace App\Services\Accounting;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\InventoryGlPost;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\ProductionMaterialIssue;
use App\Models\ProductionMaterialIssueItem;
use App\Models\ProductionRun;
use App\Models\PurchaseBill;
use App\Models\StockEntry;
use App\Models\StockMovement;
use Illuminate\Support\Carbon;

class InventoryAccountingService
{
    public function __construct(
        protected AccountingService $accounting,
        protected InventoryCostingService $costing
    ) {
    }

    public function enabled(): bool
    {
        return (bool) config('accounting.inventory_gl_enabled', true);
    }

    public function postGoodsReceipt(GoodsReceipt $receipt): void
    {
        if (! $this->enabled() || $this->hasPosted('grn', GoodsReceipt::class, $receipt->id)) {
            return;
        }

        $receipt->loadMissing(['items.product', 'warehouse']);
        $entryDate = Carbon::parse($receipt->received_at);
        $lines = [];
        $total = 0.0;

        foreach ($receipt->items as $item) {
            if ($item->qc_status !== 'approved' || ! $item->product_id) {
                continue;
            }

            $product = $item->product;
            if (! $product || ! $product->isStockTracked()) {
                continue;
            }

            $unitCost = $this->resolveGrnUnitCost($item);
            if ($unitCost <= 0) {
                continue;
            }

            $qty = (float) $item->quantity;
            $value = round($qty * $unitCost, 2);
            if ($value <= 0) {
                continue;
            }

            $this->costing->receive((int) $item->product_id, (int) $receipt->warehouse_id, $qty, $unitCost);
            $inventoryAccount = $this->costing->inventoryAccountFor($product);

            $lines[] = ['account' => $inventoryAccount, 'debit' => $value, 'credit' => 0, 'description' => $receipt->grn_number . ' · ' . $product->name];
            $total += $value;
        }

        if ($total <= 0 || empty($lines)) {
            return;
        }

        $lines[] = [
            'account' => config('accounting.accounts.grni_accrual'),
            'debit' => 0,
            'credit' => $total,
            'description' => 'GRNI for ' . $receipt->grn_number,
        ];

        $journal = $this->accounting->post('inventory_grn', $entryDate, $lines, [
            'description' => 'Inventory receipt ' . $receipt->grn_number,
            'source_type' => GoodsReceipt::class,
            'source_id' => $receipt->id,
        ]);

        $this->markPosted('grn', GoodsReceipt::class, $receipt->id, $journal->id);
    }

    public function postGoodsReceiptReversal(GoodsReceipt $receipt): void
    {
        if (! $this->enabled()) {
            return;
        }

        $event = 'grn_reversal';
        if ($this->hasPosted($event, GoodsReceipt::class, $receipt->id)) {
            return;
        }

        $original = InventoryGlPost::where('event_type', 'grn')
            ->where('source_type', GoodsReceipt::class)
            ->where('source_id', $receipt->id)
            ->first();

        if ($original?->journalEntry) {
            $reversal = $this->accounting->reverse($original->journalEntry, 'GRN reversal ' . $receipt->grn_number);
            $this->markPosted($event, GoodsReceipt::class, $receipt->id, $reversal->id);

            return;
        }

        $receipt->loadMissing(['items.product']);
        $entryDate = Carbon::today();
        $lines = [];
        $total = 0.0;

        foreach ($receipt->items as $item) {
            if ($item->qc_status !== 'approved' || ! $item->product_id) {
                continue;
            }

            $qty = (float) $item->quantity;
            $cost = $this->costing->issue((int) $item->product_id, (int) $receipt->warehouse_id, $qty);
            if ($cost <= 0) {
                continue;
            }

            $inventoryAccount = $this->costing->inventoryAccountFor($item->product);
            $lines[] = ['account' => $inventoryAccount, 'debit' => 0, 'credit' => $cost];
            $total += $cost;
        }

        if ($total <= 0) {
            return;
        }

        $lines[] = ['account' => config('accounting.accounts.grni_accrual'), 'debit' => $total, 'credit' => 0];

        $journal = $this->accounting->post('inventory_grn_reversal', $entryDate, $lines, [
            'description' => 'GRN reversal ' . $receipt->grn_number,
            'source_type' => GoodsReceipt::class,
            'source_id' => $receipt->id,
        ]);

        $this->markPosted($event, GoodsReceipt::class, $receipt->id, $journal->id);
    }

    public function postProductionRun(ProductionRun $run): void
    {
        if (! $this->enabled() || ! $run->warehouse_id || $this->hasPosted('production', ProductionRun::class, $run->id)) {
            return;
        }

        $run->loadMissing(['product', 'materialIssues.items.component']);
        $issue = $run->materialIssues()->latest('id')->with('items.component')->first();

        $rawCost = 0.0;
        if ($issue) {
            foreach ($issue->items as $line) {
                $qty = (float) $line->quantity;
                if ($qty <= 0 || ! $line->component_product_id) {
                    continue;
                }

                $rawCost += $this->costing->issue(
                    (int) $line->component_product_id,
                    (int) $run->warehouse_id,
                    $qty
                );
            }
        }

        if ($rawCost <= 0 && (float) $run->material_total_cost > 0) {
            $rawCost = (float) $run->material_total_cost;
        }

        if ($rawCost <= 0) {
            return;
        }

        $fgQty = (float) $run->quantity;
        if ($fgQty > 0) {
            $this->costing->receive((int) $run->product_id, (int) $run->warehouse_id, $fgQty, $rawCost / $fgQty);
        }

        $entryDate = Carbon::parse($run->stock_confirmed_at ?? now());
        $journal = $this->accounting->post('inventory_production', $entryDate, [
            ['account' => config('accounting.accounts.finished_goods_inventory'), 'debit' => $rawCost, 'credit' => 0],
            ['account' => config('accounting.accounts.raw_materials_inventory'), 'debit' => 0, 'credit' => $rawCost],
        ], [
            'description' => 'Production capitalization ' . ($run->order_number ?? ('#' . $run->id)),
            'source_type' => ProductionRun::class,
            'source_id' => $run->id,
        ]);

        $this->markPosted('production', ProductionRun::class, $run->id, $journal->id);
    }

    public function postInvoiceCogs(Invoice $invoice): void
    {
        if (! $this->enabled() || config('accounting.cogs_recognition') !== 'invoice') {
            return;
        }

        if ($this->hasPosted('invoice_cogs', Invoice::class, $invoice->id)) {
            return;
        }

        $invoice->loadMissing(['items.product']);
        $entryDate = Carbon::parse($invoice->issued_at);
        $totalCogs = 0.0;

        foreach ($invoice->items as $item) {
            if (! $item->product_id) {
                continue;
            }

            $issue = $this->costing->issueAcrossWarehouses((int) $item->product_id, (float) $item->quantity);
            $totalCogs += $issue['total_cost'];
        }

        $totalCogs = round($totalCogs, 2);
        if ($totalCogs <= 0) {
            return;
        }

        $journal = $this->accounting->post('inventory_cogs', $entryDate, [
            ['account' => config('accounting.accounts.cogs'), 'debit' => $totalCogs, 'credit' => 0],
            ['account' => config('accounting.accounts.finished_goods_inventory'), 'debit' => 0, 'credit' => $totalCogs],
        ], [
            'description' => 'COGS on ' . $invoice->number,
            'source_type' => Invoice::class,
            'source_id' => $invoice->id,
            'invoice_id' => $invoice->id,
            'order_id' => $invoice->order_id,
        ]);

        $this->markPosted('invoice_cogs', Invoice::class, $invoice->id, $journal->id);
    }

    public function postWriteOff(StockEntry $entry, float $quantity, string $notes, int $movementId): void
    {
        if (! $this->enabled() || $quantity <= 0 || ! $entry->product_id || ! $entry->warehouse_id) {
            return;
        }

        if ($this->hasPosted('write_off', StockMovement::class, $movementId)) {
            return;
        }

        $entry->loadMissing('product');
        $cost = $this->costing->issue((int) $entry->product_id, (int) $entry->warehouse_id, $quantity);
        if ($cost <= 0) {
            return;
        }

        $inventoryAccount = $this->costing->inventoryAccountFor($entry->product);

        $journal = $this->accounting->post('inventory_write_off', Carbon::today(), [
            ['account' => config('accounting.accounts.inventory_write_off'), 'debit' => $cost, 'credit' => 0],
            ['account' => $inventoryAccount, 'debit' => 0, 'credit' => $cost],
        ], [
            'description' => $notes ?: 'Inventory write-off',
            'source_type' => StockMovement::class,
            'source_id' => $movementId,
        ]);

        $this->markPosted('write_off', StockMovement::class, $movementId, $journal->id);
    }

    public function refreshPurchaseBillLedger(PurchaseBill $bill, float $netTotal, float $vatTotal): array
    {
        if (! $this->enabled() || ! $this->billHasStockItems($bill)) {
            return [
                ['account' => config('accounting.accounts.purchases'), 'debit' => $netTotal, 'credit' => 0],
            ];
        }

        return [
            ['account' => config('accounting.accounts.grni_accrual'), 'debit' => $netTotal, 'credit' => 0],
        ];
    }

    protected function billHasStockItems(PurchaseBill $bill): bool
    {
        $bill->loadMissing('items.product');

        return $bill->items->contains(function ($item) {
            $product = $item->product;

            return $product && $product->isStockTracked();
        });
    }

    protected function resolveGrnUnitCost(GoodsReceiptItem $item): float
    {
        if ($item->unit_cost !== null && (float) $item->unit_cost > 0) {
            return (float) $item->unit_cost;
        }

        if ($item->line_total !== null && (float) $item->quantity > 0) {
            return round((float) $item->line_total / (float) $item->quantity, 4);
        }

        $poItem = $item->purchaseOrderItem;
        if ($poItem && (float) $poItem->unit_price > 0) {
            return (float) $poItem->unit_price;
        }

        return (float) ($item->product?->standard_cost ?? 0);
    }

    protected function hasPosted(string $eventType, string $sourceType, int $sourceId): bool
    {
        return InventoryGlPost::where('event_type', $eventType)
            ->where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->exists();
    }

    protected function markPosted(string $eventType, string $sourceType, int $sourceId, int $journalEntryId): void
    {
        InventoryGlPost::create([
            'event_type' => $eventType,
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'journal_entry_id' => $journalEntryId,
        ]);
    }
}
