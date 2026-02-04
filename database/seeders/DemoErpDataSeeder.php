<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Agent;
use App\Models\AgentCommissionRule;
use App\Models\AgentPriceList;
use App\Models\Batch;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PackagingType;
use App\Models\Product;
use App\Models\Receipt;
use App\Models\StockEntry;
use App\Models\TaxClass;
use App\Models\Warehouse;
use App\Models\Vehicle;
use App\Models\DeliveryRoute;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DemoErpDataSeeder extends Seeder
{
    /**
     * Seed a small end‑to‑end data set so the SAF ERP UI is usable
     * immediately after `php artisan migrate:fresh --seed`.
     */
    public function run(): void
    {
        // --- Masters: Tax, Packaging, Products --------------------------------

        $vat15 = TaxClass::firstOrCreate(
            ['name' => 'Standard VAT 15%'],
            [
                'hsn_code' => '2201',
                'local_tax_code' => 'BD-VAT-15',
                'rate' => 15,
            ]
        );

        $vat0 = TaxClass::firstOrCreate(
            ['name' => 'VAT exempt'],
            [
                'rate' => 0,
            ]
        );

        $bottle1L = PackagingType::firstOrCreate(
            ['name' => 'Bottle 1L'],
            [
                'unit' => 'bottle',
                'description' => 'Single 1 litre PET bottle',
            ]
        );

        $carton12x1L = PackagingType::firstOrCreate(
            ['name' => 'Carton 12 x 1L'],
            [
                'unit' => 'carton',
                'description' => 'Carton containing 12 x 1L bottles',
            ]
        );

        // Finished product (ডেমো হিসেবে শুধু একটাই বিক্রিযোগ্য প্রোডাক্ট রাখছি)
        $finishedProduct = Product::firstOrCreate(
            ['sku' => 'SAF-1L-CTN'],
            [
                'name' => 'SAF Mineral Water 1L – Carton (12 bottles)',
                'description' => 'Premium mineral water, 1L x 12 bottles',
                'size' => '1L carton',
                'volume_ml' => 1000,
                'packaging_type_id' => $carton12x1L->id,
                'tax_class_id' => $vat15->id,
                'mineral_source' => 'SAF Plant',
                'ph' => 7.2,
                'tds' => 150,
                'base_price' => 600, // base list price per carton
            ]
        );

        // --- Warehouses --------------------------------------------------------

        $factory = Warehouse::firstOrCreate(
            ['name' => 'Factory'],
            [
                'address' => 'Tongi, Gazipur',
                'type' => 'factory',
            ]
        );

        $centralDepot = Warehouse::firstOrCreate(
            ['name' => 'Central Depot'],
            [
                'address' => 'Mirpur, Dhaka',
                'type' => 'depot',
            ]
        );

        // --- Batch & stock at factory -----------------------------------------

        $today = Carbon::today();

        $batch = Batch::firstOrCreate(
            ['batch_code' => '1L-CTN-' . $today->format('ymd') . '-A'],
            [
                'product_id' => $finishedProduct->id,
                'production_date' => $today,
                'expiry_date' => $today->copy()->addMonths(6),
                'qc_status' => 'approved',
                'notes' => 'Seeded demo batch',
            ]
        );

        // 1,000 cartons approved and available at Factory
        StockEntry::firstOrCreate(
            [
                'warehouse_id' => $factory->id,
                'product_id' => $finishedProduct->id,
                'batch_id' => $batch->id,
                'status' => 'available',
            ],
            [
                'warehouse_location_id' => null,
                'quantity' => 1000,
            ]
        );

        // --- Agent & pricing --------------------------------------------------

        $agent = Agent::firstOrCreate(
            ['name' => 'Dhaka North Dealer 01'],
            [
                'email' => 'agent@demo.local',
                'phone' => '01712-345678',
                'area' => 'Mirpur',
                'zone' => 'Dhaka North',
                'location_code' => 'DN-MIR-01',
                'credit_limit' => 200000,
                'is_active' => true,
            ]
        );

        // Agent specific price (slightly discounted)
        AgentPriceList::firstOrCreate(
            [
                'agent_id' => $agent->id,
                'product_id' => $finishedProduct->id,
            ],
            [
                'price' => 580,
                'notes' => 'Preferred Dhaka North dealer price',
            ]
        );

        // Simple commission rule – 2% on all regular orders
        AgentCommissionRule::firstOrCreate(
            [
                'agent_id' => $agent->id,
                'sku' => $finishedProduct->sku,
                'type' => 'percentage',
                'frequency' => 'per_order',
            ],
            [
                'value' => 2,
                'order_type' => 'regular',
            ]
        );

        // --- Accounts / chart of accounts ------------------------------------

        Account::firstOrCreate(
            ['code' => '1000'],
            ['name' => 'Bank', 'type' => 'asset', 'is_active' => true]
        );

        Account::firstOrCreate(
            ['code' => '1100'],
            ['name' => 'Accounts Receivable', 'type' => 'asset', 'is_active' => true]
        );

        Account::firstOrCreate(
            ['code' => '2100'],
            ['name' => 'VAT Payable', 'type' => 'liability', 'is_active' => true]
        );

        Account::firstOrCreate(
            ['code' => '4000'],
            ['name' => 'Sales Revenue', 'type' => 'income', 'is_active' => true]
        );

        Account::firstOrCreate(
            ['code' => '4005'],
            ['name' => 'Sales Returns', 'type' => 'income', 'is_active' => true]
        );

        Account::firstOrCreate(
            ['code' => '5100'],
            ['name' => 'Commission Expense', 'type' => 'expense', 'is_active' => true]
        );

        // --- Fleet / delivery routes -----------------------------------------

        $truck1 = Vehicle::firstOrCreate(
            ['name' => 'Truck 1'],
            [
                'type' => 'truck',
                'license_plate' => 'DHAKA-METRO-TA-1234',
                'driver' => 'Abdul Karim',
                'capacity_crates' => 200,
            ]
        );

        $truck2 = Vehicle::firstOrCreate(
            ['name' => 'Truck 2'],
            [
                'type' => 'truck',
                'license_plate' => 'DHAKA-METRO-TA-5678',
                'driver' => 'Rahim Uddin',
                'capacity_crates' => 180,
            ]
        );

        DeliveryRoute::firstOrCreate(
            ['name' => 'Dhaka North Route 1'],
            [
                'zone' => 'Dhaka North',
                'day' => 'Sunday',
                'vehicle_id' => $truck1->id,
                'driver' => $truck1->driver,
            ]
        );

        DeliveryRoute::firstOrCreate(
            ['name' => 'Dhaka North Route 2'],
            [
                'zone' => 'Dhaka North',
                'day' => 'Wednesday',
                'vehicle_id' => $truck2->id,
                'driver' => $truck2->driver,
            ]
        );

        // --- One demo sales cycle --------------------------------------------

        // Move some stock to Central Depot so deliveries look realistic
        StockEntry::firstOrCreate(
            [
                'warehouse_id' => $centralDepot->id,
                'product_id' => $finishedProduct->id,
                'batch_id' => $batch->id,
                'status' => 'available',
            ],
            [
                'warehouse_location_id' => null,
                'quantity' => 200, // 200 cartons at depot
            ]
        );

        // Sales order for the agent
        $order = Order::firstOrCreate(
            [
                'agent_id' => $agent->id,
                'order_type' => 'regular',
                'delivery_date' => $today->copy()->addDays(2),
            ],
            [
                'status' => 'delivered',
                'total' => 0,
                'commission_total' => 0,
                'notes' => 'Seed demo order for SAF-1L-CTN',
                'is_credit_used' => false,
            ]
        );

        if ($order->items()->count() === 0) {
            $qty = 120; // cartons
            $unitPrice = 580; // agent price
            $lineTotal = $qty * $unitPrice;

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $finishedProduct->id,
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'order_type' => 'regular',
                'commission_rate' => 2,
                'commission_amount' => $lineTotal * 0.02,
            ]);

            $order->update([
                'total' => $lineTotal,
                'commission_total' => $lineTotal * 0.02,
            ]);
        }

        // Invoice for that order
        $netTotal = $order->total;
        $vatAmount = round($netTotal * 0.15, 2);

        $invoice = Invoice::firstOrCreate(
            ['order_id' => $order->id],
            [
                'number' => 'INV-000001',
                'issued_at' => $today,
                'due_at' => $today->copy()->addDays(7),
                'net_total' => $netTotal,
                'vat_amount' => $vatAmount,
                'withholding' => 0,
                'status' => 'issued',
            ]
        );

        if ($invoice->items()->count() === 0) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'product_id' => $finishedProduct->id,
                'description' => $finishedProduct->name,
                'quantity' => 120,
                'unit_price' => 580,
                'line_total' => $netTotal,
            ]);
        }

        // Partial receipt so যে‑টা help page‑এর example এর সঙ্গে মিলে যায়
        if ($invoice->receipts()->count() === 0) {
            Receipt::create([
                'invoice_id' => $invoice->id,
                'amount' => $netTotal + $vatAmount - 2760, // কিছু বকেয়া রেখে দিন
                'payment_method' => 'Bank transfer',
                'received_at' => $today,
                'notes' => 'Seed demo receipt',
            ]);
        }
    }
}
