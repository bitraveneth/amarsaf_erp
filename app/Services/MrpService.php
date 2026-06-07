<?php

namespace App\Services;

use App\Models\BillOfMaterial;
use App\Models\Product;
use App\Models\PurchaseOrderItem;
use App\Models\StockEntry;
use Illuminate\Support\Collection;

class MrpService
{
    public function suggestions(): Collection
    {
        $products = Product::stockTracked()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $availableByProduct = StockEntry::query()
            ->selectRaw('product_id, SUM(quantity) as total')
            ->where('status', 'available')
            ->groupBy('product_id')
            ->pluck('total', 'product_id');

        $openPoByProduct = PurchaseOrderItem::query()
            ->selectRaw('product_id, SUM(quantity - received_quantity) as open_qty')
            ->whereHas('purchaseOrder', fn ($q) => $q->whereIn('status', ['draft', 'approved']))
            ->groupBy('product_id')
            ->pluck('open_qty', 'product_id');

        return $products->map(function (Product $product) use ($availableByProduct, $openPoByProduct) {
            $available = (float) ($availableByProduct[$product->id] ?? 0);
            $reorderLevel = (float) ($product->reorder_level ?? 0);
            $openPo = max(0.0, (float) ($openPoByProduct[$product->id] ?? 0));
            $shortage = max(0.0, $reorderLevel - $available - $openPo);

            return [
                'product' => $product,
                'available' => $available,
                'reorder_level' => $reorderLevel,
                'open_po_qty' => $openPo,
                'suggested_qty' => $shortage > 0 ? ceil($shortage) : 0,
                'reason' => $shortage > 0 ? 'Below reorder level' : 'OK',
            ];
        })->filter(fn (array $row) => $row['suggested_qty'] > 0)->values();
    }

    public function materialRequirementsFromBom(): Collection
    {
        $activeBoms = BillOfMaterial::where('is_active', true)
            ->with(['items.component', 'product'])
            ->get();

        $requirements = [];

        foreach ($activeBoms as $bom) {
            foreach ($bom->items as $item) {
                $component = $item->component;
                if (! $component || ! $component->isStockTracked()) {
                    continue;
                }

                $componentId = (int) $component->id;
                $requirements[$componentId] = ($requirements[$componentId] ?? 0) + (float) $item->quantity;
            }
        }

        if ($requirements === []) {
            return collect();
        }

        $availableByProduct = StockEntry::query()
            ->selectRaw('product_id, SUM(quantity) as total')
            ->whereIn('product_id', array_keys($requirements))
            ->where('status', 'available')
            ->groupBy('product_id')
            ->pluck('total', 'product_id');

        return collect($requirements)->map(function (float $required, int $productId) use ($availableByProduct) {
            $product = Product::find($productId);
            $available = (float) ($availableByProduct[$productId] ?? 0);
            $shortage = max(0.0, $required - $available);

            return [
                'product' => $product,
                'required_for_bom' => $required,
                'available' => $available,
                'suggested_qty' => $shortage > 0 ? ceil($shortage) : 0,
                'reason' => 'BOM component shortage',
            ];
        })->filter(fn (array $row) => $row['suggested_qty'] > 0 && $row['product'])->values();
    }
}
