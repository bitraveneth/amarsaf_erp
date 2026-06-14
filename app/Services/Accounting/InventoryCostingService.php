<?php

namespace App\Services\Accounting;

use App\Models\InventoryValuation;
use App\Models\JournalEntryLine;
use App\Models\Product;
use Illuminate\Support\Collection;

class InventoryCostingService
{
    public function __construct(
        protected ProductLedgerResolver $productLedgers
    ) {
    }

    public function receive(int $productId, int $warehouseId, float $quantity, float $unitCost): InventoryValuation
    {
        if ($quantity <= 0 || $unitCost < 0) {
            throw new \InvalidArgumentException('Invalid inventory receipt quantities.');
        }

        $valuation = InventoryValuation::firstOrNew([
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
        ]);

        $incomingValue = round($quantity * $unitCost, 2);
        $newQty = round((float) $valuation->quantity_on_hand + $quantity, 4);
        $newValue = round((float) $valuation->total_value + $incomingValue, 2);

        $valuation->quantity_on_hand = $newQty;
        $valuation->total_value = $newValue;
        $valuation->avg_unit_cost = $newQty > 0 ? round($newValue / $newQty, 4) : 0;
        $valuation->save();

        return $valuation;
    }

    public function issue(int $productId, int $warehouseId, float $quantity): float
    {
        if ($quantity <= 0) {
            return 0.0;
        }

        $valuation = InventoryValuation::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->lockForUpdate()
            ->first();

        if (! $valuation || (float) $valuation->quantity_on_hand <= 0) {
            $fallback = $this->fallbackUnitCost($productId);

            return round($quantity * $fallback, 2);
        }

        $avgCost = (float) $valuation->avg_unit_cost;
        $availableQty = (float) $valuation->quantity_on_hand;
        $issueQty = min($quantity, $availableQty);
        $issueValue = round($issueQty * $avgCost, 2);

        $valuation->quantity_on_hand = round($availableQty - $issueQty, 4);
        $valuation->total_value = round(max(0, (float) $valuation->total_value - $issueValue), 2);

        if ((float) $valuation->quantity_on_hand <= 0.0001) {
            $valuation->quantity_on_hand = 0;
            $valuation->total_value = 0;
            $valuation->avg_unit_cost = 0;
        } else {
            $valuation->avg_unit_cost = round((float) $valuation->total_value / (float) $valuation->quantity_on_hand, 4);
        }

        $valuation->save();

        if ($issueQty < $quantity) {
            $remaining = $quantity - $issueQty;
            $issueValue += round($remaining * $this->fallbackUnitCost($productId), 2);
        }

        return $issueValue;
    }

    public function issueAcrossWarehouses(int $productId, float $quantity): array
    {
        $remaining = $quantity;
        $totalCost = 0.0;
        $breakdown = [];

        while ($remaining > 0.0001) {
            $valuation = InventoryValuation::where('product_id', $productId)
                ->where('quantity_on_hand', '>', 0)
                ->orderBy('warehouse_id')
                ->lockForUpdate()
                ->first();

            if (! $valuation) {
                $fallbackCost = round($remaining * $this->fallbackUnitCost($productId), 2);
                $totalCost += $fallbackCost;
                $breakdown[] = ['warehouse_id' => null, 'quantity' => $remaining, 'cost' => $fallbackCost];
                break;
            }

            $take = min($remaining, (float) $valuation->quantity_on_hand);
            $cost = $this->issue($productId, (int) $valuation->warehouse_id, $take);
            $totalCost += $cost;
            $remaining -= $take;
            $breakdown[] = [
                'warehouse_id' => (int) $valuation->warehouse_id,
                'quantity' => $take,
                'cost' => $cost,
            ];
        }

        return [
            'total_cost' => round($totalCost, 2),
            'breakdown' => $breakdown,
        ];
    }

    public function inventoryAccountFor(?Product $product): string
    {
        return $this->inventoryAccountKey($product);
    }

    public function inventoryAccountKey(?Product $product): string
    {
        return $this->productLedgers->inventoryAccountKey($product);
    }

    public function valuationReport(): Collection
    {
        return InventoryValuation::with(['product', 'warehouse'])
            ->where('quantity_on_hand', '>', 0)
            ->orderBy('warehouse_id')
            ->orderBy('product_id')
            ->get()
            ->map(function (InventoryValuation $row) {
                return [
                    'product' => $row->product,
                    'warehouse' => $row->warehouse,
                    'quantity' => (float) $row->quantity_on_hand,
                    'avg_unit_cost' => (float) $row->avg_unit_cost,
                    'total_value' => (float) $row->total_value,
                    'inventory_account' => $this->inventoryAccountFor($row->product),
                ];
            });
    }

    public function operationalStockValue(): float
    {
        return (float) InventoryValuation::sum('total_value');
    }

    public function ledgerInventoryBalance(): float
    {
        $accounts = ['raw_materials', 'finished_goods', 'wip'];

        $accountIds = \App\Models\Account::query()
            ->whereIn('slug', array_map(fn ($key) => config("accounting.accounts.{$key}"), $accounts))
            ->pluck('id');

        $debits = (float) JournalEntryLine::whereIn('account_id', $accountIds)
            ->whereHas('journalEntry', fn ($q) => $q->where('status', 'posted'))
            ->sum('debit');

        $credits = (float) JournalEntryLine::whereIn('account_id', $accountIds)
            ->whereHas('journalEntry', fn ($q) => $q->where('status', 'posted'))
            ->sum('credit');

        return round($debits - $credits, 2);
    }

    protected function fallbackUnitCost(int $productId): float
    {
        $product = Product::find($productId);

        return (float) ($product?->standard_cost ?? 0);
    }
}
