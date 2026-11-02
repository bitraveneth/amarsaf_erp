<?php

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\Batch;
use App\Models\Delivery;
use App\Models\DeliveryItem;
use App\Models\DeliveryPod;
use App\Models\DeliveryRoute;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\LogisticsBill;
use App\Models\LogisticsBillLine;
use App\Models\LogisticsBillPayment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductionMaterialIssue;
use App\Models\ProductionMaterialIssueItem;
use App\Models\ProductionRun;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Receipt;
use App\Models\Supplier;
use App\Models\TransportCarrier;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Ten sample records for every printable document.
 *
 * Numbers are stable (INV-PDF-01 … INV-PDF-10 and the same pattern for
 * the other documents), so running this seeder again does not duplicate them.
 */
class PrintableDocumentSampleSeeder extends Seeder
{
    public const COUNT = 10;

    public function run(): void
    {
        $masters = $this->masters();

        for ($n = 1; $n <= self::COUNT; $n++) {
            DB::transaction(function () use ($n, $masters): void {
                $this->seedSet($n, $masters);
            });
        }

        $this->command?->info('Printable samples are ready. Ten of each document, numbered 01 to 10.');
        $this->command?->info('Invoices INV-PDF-01 … INV-PDF-10');
        $this->command?->info('Sales orders and picking lists SO-PDF-01 … SO-PDF-10');
        $this->command?->info('Delivery challans, packing slips, and proof of delivery use those orders.');
        $this->command?->info('Purchase orders PO-PDF-01 … PO-PDF-10');
        $this->command?->info('Goods receipts GRN-PDF-01 … GRN-PDF-10');
        $this->command?->info('Production orders PR-PDF-01 … PR-PDF-10');
        $this->command?->info('Logistics bills LB-PDF-01 … LB-PDF-10');
    }

    /**
     * @return array<string, mixed>
     */
    protected function masters(): array
    {
        $agent = Agent::query()->updateOrCreate(
            ['email' => 'pdf-demo-agent@saferpv.local'],
            [
                'name' => 'Gulshan Trading',
                'phone' => '01711000001',
                'area' => 'Gulshan',
                'zone' => 'Dhaka North',
                'credit_limit' => 500000,
                'is_active' => true,
            ]
        );

        $water500 = Product::query()->updateOrCreate(
            ['sku' => 'PDF-WATER-500'],
            [
                'name' => 'SAF Drinking Water',
                'product_type' => 'finished',
                'size' => '500 ml',
                'uom' => 'Carton',
                'volume_ml' => 500,
                'base_price' => 540,
                'is_active' => true,
            ]
        );

        $water1l = Product::query()->updateOrCreate(
            ['sku' => 'PDF-WATER-1L'],
            [
                'name' => 'SAF Drinking Water',
                'product_type' => 'finished',
                'size' => '1 L',
                'uom' => 'Carton',
                'volume_ml' => 1000,
                'base_price' => 780,
                'is_active' => true,
            ]
        );

        $caps = Product::query()->updateOrCreate(
            ['sku' => 'PDF-CAP-28'],
            [
                'name' => 'Bottle cap 28 mm',
                'product_type' => 'raw',
                'size' => '28 mm',
                'uom' => 'Pcs',
                'base_price' => 1.50,
                'is_active' => true,
            ]
        );

        $film = Product::query()->updateOrCreate(
            ['sku' => 'PDF-FILM-ROLL'],
            [
                'name' => 'Stretch film',
                'product_type' => 'raw',
                'size' => 'Roll',
                'uom' => 'Roll',
                'base_price' => 420,
                'is_active' => true,
            ]
        );

        $supplier = Supplier::query()->updateOrCreate(
            ['email' => 'pdf-demo-supplier@saferpv.local'],
            [
                'name' => 'Eastern Packaging Ltd',
                'contact_person' => 'Nasrin Ahmed',
                'phone' => '01712000002',
                'address' => 'Tongi Industrial Area, Gazipur',
                'tax_id' => '000111222',
                'is_one_time' => false,
            ]
        );

        $carrier = TransportCarrier::query()->updateOrCreate(
            ['name' => 'Sundarban Transport'],
            [
                'contact_person' => 'Jamal Uddin',
                'phone' => '01713000003',
                'email' => 'pdf-demo-carrier@saferpv.local',
                'address' => 'Tejgaon Truck Stand, Dhaka',
                'tax_id' => '000333444',
                'is_active' => true,
            ]
        );

        $warehouse = Warehouse::query()->firstOrCreate(
            ['name' => 'Gazipur Factory'],
            [
                'address' => 'Gazipur, Dhaka',
                'type' => 'factory',
            ]
        );

        $vehicle = Vehicle::query()->updateOrCreate(
            ['license_plate' => 'DM-PDF-1021'],
            [
                'name' => 'Dhaka Van 1',
                'type' => 'van',
                'ownership' => 'own',
                'fuel_type' => 'diesel',
                'driver' => 'Karim Hossain',
                'capacity_crates' => 120,
                'is_active' => true,
            ]
        );

        $route = DeliveryRoute::query()->updateOrCreate(
            ['name' => 'Dhaka North — Gulshan'],
            [
                'zone' => 'Dhaka North',
                'day' => 'Daily',
                'vehicle_id' => $vehicle->id,
                'driver' => 'Karim Hossain',
            ]
        );

        $batch500 = Batch::query()->firstOrCreate(
            ['product_id' => $water500->id, 'batch_code' => 'PDF-B500'],
            [
                'production_date' => now()->subDays(20)->toDateString(),
                'expiry_date' => now()->addMonths(12)->toDateString(),
                'qc_status' => 'approved',
                'notes' => 'Sample batch for print preview.',
            ]
        );

        $batch1l = Batch::query()->firstOrCreate(
            ['product_id' => $water1l->id, 'batch_code' => 'PDF-B1L'],
            [
                'production_date' => now()->subDays(18)->toDateString(),
                'expiry_date' => now()->addMonths(12)->toDateString(),
                'qc_status' => 'approved',
            ]
        );

        $batchCaps = Batch::query()->firstOrCreate(
            ['product_id' => $caps->id, 'batch_code' => 'PDF-BCAP'],
            [
                'production_date' => now()->subDays(15)->toDateString(),
                'expiry_date' => now()->addYears(2)->toDateString(),
                'qc_status' => 'approved',
            ]
        );

        return [
            'agent' => $agent,
            'water500' => $water500,
            'water1l' => $water1l,
            'caps' => $caps,
            'film' => $film,
            'supplier' => $supplier,
            'carrier' => $carrier,
            'warehouse' => $warehouse,
            'vehicle' => $vehicle,
            'route' => $route,
            'batch500' => $batch500,
            'batch1l' => $batch1l,
            'batchCaps' => $batchCaps,
            'user_id' => User::query()->orderBy('id')->value('id'),
        ];
    }

    /**
     * @param  array<string, mixed>  $masters
     */
    protected function seedSet(int $n, array $masters): void
    {
        $code = str_pad((string) $n, 2, '0', STR_PAD_LEFT);
        $areas = ['Gulshan', 'Banani', 'Dhanmondi', 'Mirpur', 'Uttara', 'Motijheel', 'Chittagong', 'Sylhet', 'Khulna', 'Rajshahi'];
        $area = $areas[$n - 1];
        $when = now()->subDays(12 - $n);
        $modes = ['cash', 'credit', 'bkash', 'bank_transfer'];
        $paymentMode = $modes[($n - 1) % count($modes)];

        $qty500 = 8 + $n;
        $qty1l = 2 + ($n % 5);
        $price500 = 540;
        $price1l = 780;
        $net = ($qty500 * $price500) + ($qty1l * $price1l);
        $vat = round($net * 0.15, 2);
        $withholding = $n === 2 ? round($net * 0.03, 2) : 0;

        $order = Order::query()->firstOrCreate(
            ['agent_reference' => 'SO-PDF-'.$code],
            [
                'agent_id' => $masters['agent']->id,
                'order_type' => 'regular',
                'delivery_date' => $when->toDateString(),
                'delivery_contact_name' => $area.' Outlet '.$n,
                'delivery_contact_phone' => '017100000'.str_pad((string) $n, 2, '0', STR_PAD_LEFT),
                'delivery_address' => 'House '.$n.', Road '.($n + 2).', '.$area,
                'status' => $n === 10 ? 'dispatched' : 'delivered',
                'total' => $net,
                'commission_total' => 0,
                'notes' => 'Sample sales order '.$code.' for challan and invoice print preview.',
                'is_credit_used' => $paymentMode === 'credit',
                'payment_mode' => $paymentMode,
            ]
        );

        if ($order->items()->doesntExist()) {
            OrderItem::query()->create([
                'order_id' => $order->id,
                'product_id' => $masters['water500']->id,
                'quantity' => $qty500,
                'picked_quantity' => $qty500,
                'packed_quantity' => $qty500,
                'unit_price' => $price500,
                'order_type' => 'regular',
                'commission_rate' => 0,
                'commission_amount' => 0,
            ]);
            OrderItem::query()->create([
                'order_id' => $order->id,
                'product_id' => $masters['water1l']->id,
                'quantity' => $qty1l,
                'picked_quantity' => $qty1l,
                'packed_quantity' => $qty1l,
                'unit_price' => $price1l,
                'order_type' => 'regular',
                'commission_rate' => 0,
                'commission_amount' => 0,
            ]);
        }

        $invoice = Invoice::query()->firstOrCreate(
            ['number' => 'INV-PDF-'.$code],
            [
                'order_id' => $order->id,
                'issued_at' => $when->toDateString(),
                'due_at' => $when->copy()->addDays(7)->toDateString(),
                'net_total' => $net,
                'vat_amount' => $vat,
                'withholding' => $withholding,
                'status' => 'issued',
            ]
        );

        if ($invoice->items()->doesntExist()) {
            InvoiceItem::query()->create([
                'invoice_id' => $invoice->id,
                'product_id' => $masters['water500']->id,
                'description' => $masters['water500']->name,
                'quantity' => $qty500,
                'unit_price' => $price500,
                'line_total' => $qty500 * $price500,
            ]);
            InvoiceItem::query()->create([
                'invoice_id' => $invoice->id,
                'product_id' => $masters['water1l']->id,
                'description' => $masters['water1l']->name,
                'quantity' => $qty1l,
                'unit_price' => $price1l,
                'line_total' => $qty1l * $price1l,
            ]);
        }

        if ($invoice->receipts()->doesntExist() && $n <= 6) {
            $cashDue = $net + $vat - $withholding;
            $paid = $n <= 3 ? $cashDue : round($cashDue / 2, 2);
            Receipt::query()->create([
                'invoice_id' => $invoice->id,
                'amount' => $paid,
                'payment_method' => $paymentMode === 'credit' ? 'bank_transfer' : $paymentMode,
                'received_at' => $when->copy()->addDay()->toDateString(),
                'notes' => 'Sample receipt for '.$invoice->number,
            ]);
            $invoice->recalculateStatus();
        }

        $delivery = Delivery::query()->firstOrCreate(
            ['order_id' => $order->id],
            [
                'route_id' => $masters['route']->id,
                'vehicle_id' => $masters['vehicle']->id,
                'status' => $n === 10 ? 'in_transit' : 'delivered',
                'sequence' => $n,
                'exception_notes' => $n === 9 ? '2 cartons of 1 L short on delivery.' : null,
            ]
        );

        if ($delivery->items()->doesntExist()) {
            $lines = $order->items()->get();
            foreach ($lines as $index => $line) {
                $dispatched = (float) $line->quantity;
                $deliveredQty = $dispatched;
                $short = 0;
                if ($n === 10) {
                    $deliveredQty = 0;
                } elseif ($n === 9 && $index === 1) {
                    $short = 2;
                    $deliveredQty = max(0, $dispatched - $short);
                }

                DeliveryItem::query()->create([
                    'delivery_id' => $delivery->id,
                    'order_item_id' => $line->id,
                    'product_id' => $line->product_id,
                    'batch_id' => $line->product_id === $masters['water500']->id
                        ? $masters['batch500']->id
                        : $masters['batch1l']->id,
                    'qty_dispatched' => $dispatched,
                    'qty_delivered' => $deliveredQty,
                    'qty_short' => $short,
                    'qty_damaged' => 0,
                    'notes' => $short > 0 ? 'Short on arrival' : null,
                ]);
            }
        }

        if ($delivery->status === 'delivered' && ! $delivery->pod()->exists()) {
            DeliveryPod::query()->create([
                'delivery_id' => $delivery->id,
                'signed_by' => $order->delivery_contact_name,
                'delivered_at' => $when->copy()->setTime(16, 30),
                'receiver_name' => $order->delivery_contact_name,
                'receiver_phone' => $order->delivery_contact_phone,
                'notes' => 'Received in good condition.',
            ]);
        }

        $capQty = 2000 + ($n * 100);
        $filmQty = 8 + $n;
        $capPrice = 1.50;
        $filmPrice = 420;
        $discount = 5;
        $poVat = 15;
        $capTotal = $this->lineTotal($capQty, $capPrice, $discount, $poVat);
        $filmTotal = $this->lineTotal($filmQty, $filmPrice, $discount, $poVat);

        $purchaseOrder = PurchaseOrder::query()->firstOrCreate(
            ['number' => 'PO-PDF-'.$code],
            [
                'supplier_id' => $masters['supplier']->id,
                'order_date' => $when->toDateString(),
                'expected_date' => $when->copy()->addDays(5)->toDateString(),
                'status' => 'partial_received',
                'notes' => 'Sample purchase order '.$code.' for print preview. Discount and VAT are set so those columns print.',
            ]
        );

        if ($purchaseOrder->items()->doesntExist()) {
            PurchaseOrderItem::query()->create([
                'purchase_order_id' => $purchaseOrder->id,
                'product_id' => $masters['caps']->id,
                'description' => $masters['caps']->name,
                'uom' => 'Pcs',
                'quantity' => $capQty,
                'unit_price' => $capPrice,
                'discount_percent' => $discount,
                'vat_rate' => $poVat,
                'line_total' => $capTotal,
                'received_quantity' => 1500,
            ]);
            PurchaseOrderItem::query()->create([
                'purchase_order_id' => $purchaseOrder->id,
                'product_id' => $masters['film']->id,
                'description' => $masters['film']->name,
                'uom' => 'Roll',
                'quantity' => $filmQty,
                'unit_price' => $filmPrice,
                'discount_percent' => $discount,
                'vat_rate' => $poVat,
                'line_total' => $filmTotal,
                'received_quantity' => $filmQty,
            ]);
        }

        $grn = GoodsReceipt::query()->firstOrCreate(
            ['grn_number' => 'GRN-PDF-'.$code],
            [
                'purchase_order_id' => $purchaseOrder->id,
                'supplier_id' => $masters['supplier']->id,
                'warehouse_id' => $masters['warehouse']->id,
                'created_by' => $masters['user_id'],
                'received_at' => $when->copy()->setTime(11, 15),
                'status' => 'posted',
                'notes' => 'Sample goods receipt '.$code.' for print preview.',
            ]
        );

        if ($grn->items()->doesntExist()) {
            $poItems = $purchaseOrder->items()->get();
            GoodsReceiptItem::query()->create([
                'goods_receipt_id' => $grn->id,
                'purchase_order_item_id' => $poItems->get(0)?->id,
                'product_id' => $masters['caps']->id,
                'batch_id' => $masters['batchCaps']->id,
                'quantity' => 1500,
                'unit_cost' => $capPrice,
                'line_total' => round(1500 * $capPrice, 2),
                'qc_status' => 'approved',
                'remarks' => 'Caps checked',
            ]);
            GoodsReceiptItem::query()->create([
                'goods_receipt_id' => $grn->id,
                'purchase_order_item_id' => $poItems->get(1)?->id,
                'product_id' => $masters['film']->id,
                'quantity' => $filmQty,
                'unit_cost' => $filmPrice,
                'line_total' => round($filmQty * $filmPrice, 2),
                'qc_status' => 'approved',
                'remarks' => 'Film rolls',
            ]);
        }

        $runQty = 100 + ($n * 20);
        $run = ProductionRun::query()->firstOrCreate(
            ['order_number' => 'PR-PDF-'.$code],
            [
                'product_id' => $masters['water500']->id,
                'batch_id' => $masters['batch500']->id,
                'warehouse_id' => $masters['warehouse']->id,
                'line' => 'Line A',
                'shift' => $n % 2 === 0 ? 'Evening' : 'Morning',
                'quantity' => $runQty,
                'status' => 'confirmed',
                'qc_status' => 'approved',
                'qc_passed_quantity' => $runQty,
                'qc_rejected_quantity' => 0,
                'material_unit_cost' => 18.5,
                'material_total_cost' => round($runQty * 18.5, 2),
                'notes' => 'Sample production order '.$code.' for print preview.',
            ]
        );

        if ($run->materialIssues()->doesntExist()) {
            $issue = ProductionMaterialIssue::query()->create([
                'production_run_id' => $run->id,
                'warehouse_id' => $masters['warehouse']->id,
                'issued_by' => $masters['user_id'],
                'issued_at' => $when->copy()->setTime(8, 0),
                'notes' => 'Bottles and caps issued for '.$run->order_number,
            ]);
            ProductionMaterialIssueItem::query()->create([
                'production_material_issue_id' => $issue->id,
                'component_product_id' => $masters['caps']->id,
                'batch_id' => $masters['batchCaps']->id,
                'quantity' => $runQty * 12,
                'unit_cost' => 1.5,
                'line_total' => round($runQty * 12 * 1.5, 2),
            ]);
            ProductionMaterialIssueItem::query()->create([
                'production_material_issue_id' => $issue->id,
                'component_product_id' => $masters['film']->id,
                'quantity' => max(1, (int) ceil($runQty / 50)),
                'unit_cost' => 420,
                'line_total' => round(max(1, (int) ceil($runQty / 50)) * 420, 2),
            ]);
        }

        $freight = 3500 + ($n * 250);
        $loading = 400;
        $billNet = $freight + $loading;
        $billVat = round($billNet * 0.15, 2);
        $bill = LogisticsBill::query()->firstOrCreate(
            ['number' => 'LB-PDF-'.$code],
            [
                'transport_carrier_id' => $masters['carrier']->id,
                'bill_date' => $when->toDateString(),
                'due_date' => $when->copy()->addDays(10)->toDateString(),
                'service_type' => LogisticsBill::SERVICE_EXTERNAL_FREIGHT,
                'delivery_route_id' => $masters['route']->id,
                'trip_date' => $when->toDateString(),
                'notes' => 'Sample logistics bill '.$code.' for the '.$area.' trip.',
                'net_total' => $billNet,
                'vat_amount' => $billVat,
                'status' => 'open',
            ]
        );

        if ($bill->lines()->doesntExist()) {
            LogisticsBillLine::query()->create([
                'logistics_bill_id' => $bill->id,
                'description' => 'Freight '.$area,
                'amount' => $freight,
            ]);
            LogisticsBillLine::query()->create([
                'logistics_bill_id' => $bill->id,
                'description' => 'Loading and unloading',
                'amount' => $loading,
            ]);
        }

        if ($n % 4 === 0 && $bill->payments()->doesntExist()) {
            LogisticsBillPayment::query()->create([
                'logistics_bill_id' => $bill->id,
                'amount' => round($billNet / 2, 2),
                'paid_at' => $when->copy()->addDays(2)->toDateString(),
                'method' => 'bank',
                'batch_reference' => 'PAY-PDF-'.$code,
            ]);
            $bill->recalculateStatus();
        }
    }

    protected function lineTotal(float $qty, float $price, float $discountPercent, float $vatRate): float
    {
        $gross = $qty * $price;
        $net = $gross * (1 - (min(100, max(0, $discountPercent)) / 100));

        return round($net * (1 + (min(100, max(0, $vatRate)) / 100)), 2);
    }
}
