<?php

namespace Database\Seeders\Control\Suppliers;

use App\Models\BillPayment;
use App\Models\Product;
use App\Models\PurchaseBill;
use App\Models\PurchaseBillItem;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

/**
 * Seed supplier purchase bills (accounts payable).
 */
class PurchaseBillsSeeder extends Seeder
{
    public function run(): void
    {
        $abcPlastics = Supplier::where('name', 'ABC Plastics')->first();
        $cartonCo = Supplier::where('name', 'CartonCo')->first();
        $xyzLabels = Supplier::where('name', 'XYZ Labels')->first();
        $packaging = Supplier::where('name', 'Packaging Ltd')->first();
        $waterSupp = Supplier::where('name', 'Local Water Provider')->first();
        $johnLabour = Supplier::where('name', 'John Contractor')->first();
        $localUtility = Supplier::where('name', 'Local Utility')->first();
        $catering = Supplier::where('name', 'Catering Plus')->first();
        $guestHonor = Supplier::where('name', 'Guest & Honor Committee')->first();

        $preform = Product::where('sku', 'RM-PREF')->first();
        $cap = Product::where('sku', 'RM-CAP-STD')->first();
        $carton = Product::where('sku', 'RM-CTN-24X500')->first();
        $label = Product::where('sku', 'RM-BOPP')->first();
        $shrink = Product::where('sku', 'RM-SHRINK')->first();
        $water = Product::where('sku', 'RM-WATER')->first();
        $labour = Product::where('sku', 'SV-SVC')->first();
        $utilities = Product::where('sku', 'SV-UTIL')->first();

        if ($abcPlastics && $preform && $cap) {
            $this->seedBill(
                'PB-20260201-001',
                $abcPlastics->id,
                now()->subDays(7)->toDateString(),
                now()->addDays(7)->toDateString(),
                'open',
                [
                    ['product_id' => $preform->id, 'description' => $preform->name, 'quantity' => 50000, 'unit_price' => 5.00],
                    ['product_id' => $cap->id, 'description' => $cap->name, 'quantity' => 50000, 'unit_price' => 0.80],
                ]
            );
        }

        if ($cartonCo && $carton) {
            $this->seedBill(
                'PB-20260201-002',
                $cartonCo->id,
                now()->subDays(5)->toDateString(),
                now()->addDays(10)->toDateString(),
                'part_paid',
                [
                    ['product_id' => $carton->id, 'description' => $carton->name, 'quantity' => 6000, 'unit_price' => 20.00],
                ],
                paidFraction: 0.45
            );
        }

        if ($xyzLabels && $label) {
            $this->seedBill(
                'PB-20260201-003',
                $xyzLabels->id,
                now()->subDays(4)->toDateString(),
                now()->addDays(12)->toDateString(),
                'open',
                [
                    ['product_id' => $label->id, 'description' => $label->name, 'quantity' => 60000, 'unit_price' => 0.60],
                ]
            );
        }

        if ($packaging && $shrink) {
            $this->seedBill(
                'PB-20260201-004',
                $packaging->id,
                now()->subDays(3)->toDateString(),
                now()->addDays(15)->toDateString(),
                'open',
                [
                    ['product_id' => $shrink->id, 'description' => $shrink->name, 'quantity' => 6000, 'unit_price' => 2.50],
                ]
            );
        }

        if ($waterSupp && $water && $johnLabour && $labour) {
            $this->seedBill(
                'PB-20260201-005',
                $waterSupp->id,
                now()->subDays(2)->toDateString(),
                now()->addDays(20)->toDateString(),
                'open',
                [
                    ['product_id' => $water->id, 'description' => $water->name, 'quantity' => 200000, 'unit_price' => 0.05],
                    ['product_id' => $labour->id, 'description' => $labour->name, 'quantity' => 10, 'unit_price' => 1200.00],
                ]
            );
        }

        if ($localUtility) {
            $this->seedBill(
                'PB-20260201-ELEC',
                $localUtility->id,
                now()->subDays(6)->toDateString(),
                now()->addDays(14)->toDateString(),
                'open',
                [
                    [
                        'product_id' => $utilities?->id,
                        'description' => 'Factory electricity — DESCO billing cycle',
                        'quantity' => 120,
                        'unit_price' => 850.00,
                    ],
                ]
            );
        }

        if ($catering) {
            $this->seedBill(
                'PB-20260201-FOOD',
                $catering->id,
                now()->subDays(8)->toDateString(),
                now()->addDays(5)->toDateString(),
                'paid',
                [
                    [
                        'product_id' => null,
                        'description' => 'Staff cafeteria & food cost — February',
                        'quantity' => 1,
                        'unit_price' => 48500.00,
                    ],
                ],
                paidFraction: 1.0
            );
        }

        if ($guestHonor) {
            $this->seedBill(
                'PB-20260201-GUEST',
                $guestHonor->id,
                now()->subDays(1)->toDateString(),
                now()->addDays(25)->toDateString(),
                'open',
                [
                    [
                        'product_id' => null,
                        'description' => 'Guest honor & protocol hospitality',
                        'quantity' => 1,
                        'unit_price' => 32000.00,
                    ],
                ]
            );
        }
    }

    /**
     * @param  array<int, array{product_id: int|null, description: string, quantity: float|int, unit_price: float}>  $lines
     */
    protected function seedBill(
        string $number,
        int $supplierId,
        string $billDate,
        string $dueDate,
        string $status,
        array $lines,
        float $paidFraction = 0.0
    ): void {
        $netTotal = 0.0;

        foreach ($lines as $line) {
            $netTotal += (float) $line['quantity'] * (float) $line['unit_price'];
        }

        $bill = PurchaseBill::updateOrCreate(
            ['number' => $number],
            [
                'supplier_id' => $supplierId,
                'bill_date' => $billDate,
                'due_date' => $dueDate,
                'net_total' => round($netTotal, 2),
                'vat_amount' => 0,
                'status' => $status,
            ]
        );

        $bill->items()->delete();

        foreach ($lines as $line) {
            PurchaseBillItem::create([
                'purchase_bill_id' => $bill->id,
                'product_id' => $line['product_id'] ?? null,
                'description' => $line['description'],
                'quantity' => $line['quantity'],
                'unit_price' => $line['unit_price'],
                'line_total' => round((float) $line['quantity'] * (float) $line['unit_price'], 2),
            ]);
        }

        if ($paidFraction > 0) {
            BillPayment::updateOrCreate(
                [
                    'purchase_bill_id' => $bill->id,
                    'paid_at' => $billDate,
                ],
                [
                    'amount' => round($netTotal * $paidFraction, 2),
                    'method' => 'bank_transfer',
                    'batch_reference' => 'SEED-PAY-' . $number,
                ]
            );

            $bill->recalculateStatus();
        }
    }
}
