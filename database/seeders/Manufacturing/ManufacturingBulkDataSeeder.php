<?php

namespace Database\Seeders\Manufacturing;

use App\Models\Batch;
use App\Models\BillOfMaterial;
use App\Models\Employee;
use App\Models\Product;
use App\Models\ProductionRun;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Adds 100 demo production runs (and matching batches) so manufacturing
 * screens — production orders, pending receipts, batches, and analysis —
 * have enough history to paginate and chart realistically.
 */
class ManufacturingBulkDataSeeder extends Seeder
{
    private const TARGET_COUNT = 100;

    private const ORDER_PREFIX = 'DEMO-PO-';

    public function run(): void
    {
        $existing = ProductionRun::where('order_number', 'like', self::ORDER_PREFIX . '%')->count();

        if ($existing >= self::TARGET_COUNT) {
            return;
        }

        $products = Product::query()
            ->where('is_active', true)
            ->where(function ($query) {
                $query->where('product_type', 'finished')
                    ->orWhereNull('product_type');
            })
            ->whereIn('sku', ['SAF-500ML-CTN', 'SAF-500ML', 'SAF-1L-CTN', 'SAF-20L-JAR'])
            ->get();

        if ($products->isEmpty()) {
            $products = Product::where('sku', 'SAF-500ML-CTN')->get();
        }

        $factory = Warehouse::where('name', 'Factory')->first();

        if ($products->isEmpty() || ! $factory) {
            return;
        }

        $supervisor = Employee::where('name', 'Production Manager')->first();
        $adminUser = User::where('role', 'admin')->first();
        $qcUser = User::where('role', 'qc_officer')->first();
        $warehouseUser = User::where('role', 'warehouse_officer')->first();
        $approverUserId = optional($qcUser ?: $adminUser)->id;
        $confirmerUserId = optional($warehouseUser ?: $adminUser)->id;

        $bomCosts = BillOfMaterial::query()
            ->whereIn('product_id', $products->pluck('id'))
            ->where('is_active', true)
            ->pluck('material_unit_cost', 'product_id');

        $lines = ['Line 1', 'Line 2', 'Line 3'];
        $shifts = ['Morning', 'Evening', 'Night'];
        $today = Carbon::today();

        for ($seq = $existing + 1; $seq <= self::TARGET_COUNT; $seq++) {
            $product = $products[$seq % $products->count()];
            $daysAgo = ($seq * 17) % 180;
            $runDate = $today->copy()->subDays($daysAgo);
            $orderNumber = self::ORDER_PREFIX . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);

            if (ProductionRun::where('order_number', $orderNumber)->exists()) {
                continue;
            }

            $batchCode = 'DEMO-' . str_replace(['SAF-', '-'], ['', ''], $product->sku)
                . '-' . $runDate->format('ymd') . '-' . str_pad((string) $seq, 3, '0', STR_PAD_LEFT);

            $batch = Batch::firstOrCreate(
                ['batch_code' => $batchCode],
                [
                    'product_id' => $product->id,
                    'production_date' => $runDate,
                    'expiry_date' => $runDate->copy()->addMonths(6),
                    'qc_status' => $this->batchQcStatus($seq),
                    'notes' => 'Bulk demo batch #' . $seq,
                ]
            );

            $profile = $this->runProfile($seq);
            $quantity = 8000 + (($seq * 137) % 22000);
            $line = $lines[$seq % count($lines)];
            $shift = $shifts[$seq % count($shifts)];
            $startedAt = $runDate->copy()->setTime(6 + ($seq % 4), ($seq * 11) % 60);

            $run = new ProductionRun([
                'order_number' => $orderNumber,
                'product_id' => $product->id,
                'batch_id' => $batch->id,
                'warehouse_id' => $factory->id,
                'line' => $line,
                'shift' => $shift,
                'quantity' => $quantity,
                'status' => $profile['status'],
                'qc_status' => $profile['qc_status'],
                'supervisor_id' => optional($supervisor)->id,
                'materials_reserved' => 'Bulk demo production run #' . $seq,
                'created_at' => $startedAt,
                'updated_at' => $startedAt,
            ]);

            if ($profile['qc_status'] === 'partial') {
                $passed = (int) round($quantity * 0.92);
                $run->qc_passed_quantity = $passed;
                $run->qc_rejected_quantity = max(0, $quantity - $passed);
                $run->qc_notes = 'Partial QC — minor label variance on a subset of units.';
            }

            if (in_array($profile['qc_status'], ['approved', 'partial'], true)) {
                $run->approved_by = $approverUserId;
                $run->approved_at = $runDate->copy()->setTime(14 + ($seq % 3), 15);
            }

            if ($profile['stock_confirmed']) {
                $run->stock_confirmed_by = $confirmerUserId;
                $run->stock_confirmed_at = $runDate->copy()->setTime(16 + ($seq % 2), 30);
            }

            $unitCost = $bomCosts[$product->id] ?? null;
            if ($unitCost !== null && (float) $unitCost > 0 && $profile['status'] === 'completed') {
                $run->material_unit_cost = $unitCost;
                $run->material_total_cost = (float) $unitCost * (float) $quantity;
            }

            $run->save();
        }
    }

    /**
     * @return array{status: string, qc_status: string, stock_confirmed: bool}
     */
    private function runProfile(int $seq): array
    {
        $bucket = $seq % 20;

        if ($bucket < 11) {
            return ['status' => 'completed', 'qc_status' => 'approved', 'stock_confirmed' => true];
        }

        if ($bucket < 14) {
            return ['status' => 'confirmed', 'qc_status' => 'approved', 'stock_confirmed' => false];
        }

        if ($bucket < 16) {
            return ['status' => 'confirmed', 'qc_status' => 'pending', 'stock_confirmed' => false];
        }

        if ($bucket < 18) {
            return ['status' => 'completed', 'qc_status' => 'partial', 'stock_confirmed' => true];
        }

        return ['status' => 'confirmed', 'qc_status' => 'rejected', 'stock_confirmed' => false];
    }

    private function batchQcStatus(int $seq): string
    {
        $bucket = $seq % 20;

        if ($bucket < 11 || $bucket === 17) {
            return 'approved';
        }

        if ($bucket < 16) {
            return 'pending';
        }

        return 'rejected';
    }
}
