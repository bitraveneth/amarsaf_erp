<?php

namespace Database\Seeders\Manufacturing;

use App\Models\Batch;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Seed data for Manufacturing → Batches & lots.
 *
 * Creates one demo batch for SAF-500ML-CTN with QC pending.
 */
class BatchesLotsModuleSeeder extends Seeder
{
    public function run(): void
    {
        $product = Product::where('sku', 'SAF-500ML-CTN')->first();

        if (! $product) {
            return;
        }

        $today = Carbon::today();

        Batch::firstOrCreate(
            ['batch_code' => '500ML-CTN-' . $today->format('ymd') . '-A'],
            [
                'product_id'      => $product->id,
                'production_date' => $today,
                'expiry_date'     => $today->copy()->addMonths(6),
                'qc_status'       => 'pending',
                'notes'           => 'Seeded demo batch – QC pending',
            ]
        );
    }
}

