<?php

namespace Database\Seeders\Manufacturing;

use App\Models\BillOfMaterial;
use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * Generate plausible BOMs for finished products that lack a recipe (e.g. after legacy import).
 */
class DemoBomGeneratorSeeder extends Seeder
{
    public function run(): void
    {
        $finished = Product::query()
            ->where('is_active', true)
            ->where(function ($query) {
                $query->where('product_type', 'finished')->orWhereNull('product_type');
            })
            ->orderBy('id')
            ->get();

        if ($finished->isEmpty()) {
            return;
        }

        $rawByCategory = Product::query()
            ->where('product_type', 'raw')
            ->where('is_active', true)
            ->with('materialCategory')
            ->get()
            ->groupBy(fn (Product $p) => strtolower($p->materialCategory?->code ?? 'misc'));

        $fallbackRaw = Product::query()->where('product_type', 'raw')->where('is_active', true)->get();

        foreach ($finished as $product) {
            if (BillOfMaterial::where('product_id', $product->id)->where('is_active', true)->exists()) {
                continue;
            }

            $lines = $this->pickLines($rawByCategory, $fallbackRaw);

            if ($lines === []) {
                continue;
            }

            $bom = BillOfMaterial::create([
                'product_id' => $product->id,
                'name' => $product->name . ' — demo BOM',
                'is_active' => true,
                'notes' => 'Auto-generated BOM for sales demo seeding.',
            ]);

            $materialUnitCost = 0.0;
            $items = [];

            foreach ($lines as $line) {
                $qty = $line['qty'];
                $unitCost = (float) ($line['product']->standard_cost ?? 0);
                $materialUnitCost += $unitCost * $qty;

                $items[] = [
                    'component_product_id' => $line['product']->id,
                    'quantity' => $qty,
                    'unit_cost' => $unitCost,
                    'unit' => $line['unit'],
                ];
            }

            $bom->items()->createMany($items);

            if ($materialUnitCost > 0) {
                $bom->material_unit_cost = $materialUnitCost;
                $bom->save();
            }
        }
    }

    /**
     * @param  \Illuminate\Support\Collection<string, \Illuminate\Support\Collection<int, Product>>  $rawByCategory
     * @param  \Illuminate\Support\Collection<int, Product>  $fallbackRaw
     * @return array<int, array{product: Product, qty: float, unit: string}>
     */
    protected function pickLines($rawByCategory, $fallbackRaw): array
    {
        $pick = function (array $codes, float $qty, string $unit) use ($rawByCategory, $fallbackRaw): ?array {
            foreach ($codes as $code) {
                $product = $rawByCategory->get(strtolower($code))?->first();
                if ($product) {
                    return ['product' => $product, 'qty' => $qty, 'unit' => $unit];
                }
            }

            $product = $fallbackRaw->first();
            if ($product) {
                return ['product' => $product, 'qty' => $qty, 'unit' => $unit];
            }

            return null;
        };

        $lines = array_filter([
            $pick(['PREF', 'RM-PREF'], 24, 'bottle'),
            $pick(['CAP-STD', 'CAP'], 24, 'cap'),
            $pick(['BOPP', 'LBL'], 24, 'label'),
            $pick(['WATER', 'RO-WATER'], 12, 'liter'),
            $pick(['LAB', 'SV-LAB'], 0.08, 'day'),
            $pick(['UTIL', 'SV-UTIL'], 0.04, 'shift'),
        ]);

        return array_values($lines);
    }
}
