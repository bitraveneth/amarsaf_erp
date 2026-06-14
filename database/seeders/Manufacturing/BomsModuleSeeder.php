<?php

namespace Database\Seeders\Manufacturing;

use App\Models\BillOfMaterial;
use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * Standard BOMs for SAF carton, bottle, and jar finished goods.
 */
class BomsModuleSeeder extends Seeder
{
    public function run(): void
    {
        $pet500 = Product::where('sku', 'RM-PET-500')->first();
        $cap = Product::where('sku', 'RM-CAP-STD')->first();
        $label500 = Product::where('sku', 'RM-LABEL-500')->first();
        $carton500 = Product::where('sku', 'RM-CARTON-24X500')->first()
            ?? Product::where('sku', 'RM-CARTON-12X500')->first();
        $carton1L = Product::where('sku', 'RM-CARTON-12X1L')->first();
        $carton2L = Product::where('sku', 'RM-CARTON-6X2L')->first();
        $shrinkWrap = Product::where('sku', 'RM-SHRINK-CTN')->first();
        $water = Product::where('sku', 'RM-RO-WATER')->first();
        $labour = Product::where('sku', 'SV-LAB-FACT')->first();
        $utilities = Product::where('sku', 'SV-UTIL-SHIFT')->first();

        $definitions = [
            [
                'sku' => 'SAF-500ML-CTN',
                'name' => 'SAF-500ML standard',
                'notes' => '1 carton = 24 bottles, caps, labels, carton box, shrink wrap, RO water, labour & utilities.',
                'lines' => $this->cartonLines(24, $pet500, $cap, $label500, $carton500, $shrinkWrap, $water, 500, $labour, $utilities, 0.5, 0.2),
            ],
            [
                'sku' => 'SAF-1L-CTN',
                'name' => 'SAF-1L carton standard',
                'notes' => '1 carton = 12 x 1L bottles plus carton, shrink, water, labour & utilities.',
                'lines' => $this->cartonLines(12, $pet500, $cap, $label500, $carton1L, $shrinkWrap, $water, 1000, $labour, $utilities, 0.45, 0.18),
            ],
            [
                'sku' => 'SAF-2L-CTN',
                'name' => 'SAF-2L carton standard',
                'notes' => '1 carton = 6 x 2L bottles plus carton, shrink, water, labour & utilities.',
                'lines' => $this->cartonLines(6, $pet500, $cap, $label500, $carton2L, $shrinkWrap, $water, 2000, $labour, $utilities, 0.4, 0.16),
            ],
            [
                'sku' => 'SAF-500ML-BTL',
                'name' => 'SAF-500ML bottle standard',
                'notes' => 'Single bottle with PET, cap, label, 0.5L RO water, labour & utilities.',
                'lines' => [
                    ['product' => $pet500, 'qty' => 1, 'unit' => 'bottle'],
                    ['product' => $cap, 'qty' => 1, 'unit' => 'cap'],
                    ['product' => $label500, 'qty' => 1, 'unit' => 'label'],
                    ['product' => $water, 'qty' => 0.5, 'unit' => 'liter'],
                    ['product' => $labour, 'qty' => 0.05, 'unit' => 'day'],
                    ['product' => $utilities, 'qty' => 0.02, 'unit' => 'shift'],
                ],
            ],
            [
                'sku' => 'SAF-20L-JAR',
                'name' => 'SAF-20L jar standard',
                'notes' => '20L jar — 1 piece, no outer carton. Mostly water plus labour & utilities.',
                'lines' => [
                    ['product' => $water, 'qty' => 20.0, 'unit' => 'liter'],
                    ['product' => $labour, 'qty' => 0.25, 'unit' => 'day'],
                    ['product' => $utilities, 'qty' => 0.1, 'unit' => 'shift'],
                ],
            ],
        ];

        foreach ($definitions as $definition) {
            $this->seedBom($definition);
        }
    }

    protected function cartonLines(
        int $bottleCount,
        ?Product $pet,
        ?Product $cap,
        ?Product $label,
        ?Product $carton,
        ?Product $shrinkWrap,
        ?Product $water,
        int $volumeMl,
        ?Product $labour,
        ?Product $utilities,
        float $labourDays,
        float $utilityShifts
    ): array {
        $waterLiters = ($volumeMl / 1000) * $bottleCount;

        return array_values(array_filter([
            $pet ? ['product' => $pet, 'qty' => $bottleCount, 'unit' => 'bottle'] : null,
            $cap ? ['product' => $cap, 'qty' => $bottleCount, 'unit' => 'cap'] : null,
            $label ? ['product' => $label, 'qty' => $bottleCount, 'unit' => 'label'] : null,
            $carton ? ['product' => $carton, 'qty' => 1, 'unit' => 'carton'] : null,
            $shrinkWrap ? ['product' => $shrinkWrap, 'qty' => 1, 'unit' => 'wrap'] : null,
            $water ? ['product' => $water, 'qty' => $waterLiters, 'unit' => 'liter'] : null,
            $labour ? ['product' => $labour, 'qty' => $labourDays, 'unit' => 'day'] : null,
            $utilities ? ['product' => $utilities, 'qty' => $utilityShifts, 'unit' => 'shift'] : null,
        ]));
    }

    protected function seedBom(array $definition): void
    {
        $finished = Product::where('sku', $definition['sku'])->first();

        if (! $finished) {
            return;
        }

        $bom = BillOfMaterial::firstOrCreate(
            [
                'product_id' => $finished->id,
                'name' => $definition['name'],
            ],
            [
                'is_active' => true,
                'notes' => $definition['notes'],
            ]
        );

        $bom->update([
            'is_active' => true,
            'notes' => $definition['notes'],
        ]);

        if ($bom->items()->count() > 0) {
            $bom->items()->delete();
        }

        $items = [];

        foreach ($definition['lines'] as $line) {
            $product = $line['product'] ?? null;

            if (! $product) {
                continue;
            }

            $items[] = [
                'component_product_id' => $product->id,
                'quantity' => $line['qty'],
                'unit_cost' => $product->standard_cost,
                'unit' => $line['unit'],
            ];
        }

        if ($items === []) {
            return;
        }

        $bom->items()->createMany($items);

        $materialUnitCost = 0.0;
        foreach ($items as $item) {
            if (! empty($item['unit_cost'])) {
                $materialUnitCost += (float) $item['unit_cost'] * (float) $item['quantity'];
            }
        }

        if ($materialUnitCost > 0) {
            $bom->material_unit_cost = $materialUnitCost;
            $bom->save();
        }
    }
}
