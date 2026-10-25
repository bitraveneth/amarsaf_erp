<?php

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\Receipt;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Two local sample sales invoices in the SAF sheet layout.
 * Safe to run again: it replaces invoices L779012 and L779013 only.
 */
class DemoSalesInvoicesSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            Invoice::query()
                ->whereIn('number', ['L779012', 'L779013'])
                ->each(function (Invoice $invoice) {
                    $invoice->receipts()->delete();
                    $invoice->creditNotes()->delete();
                    $invoice->items()->delete();
                    $orderId = $invoice->order_id;
                    $invoice->delete();

                    if ($orderId) {
                        Order::query()->whereKey($orderId)->delete();
                    }
                });

            $products = collect([
                ['DEMO-SAF-01', '1', 90],
                ['DEMO-SAF-02', '2', 30],
                ['DEMO-SAF-03', '1', 99],
                ['DEMO-SAF-04', '3', 60],
                ['DEMO-SAF-05', '3', 60],
            ])->map(function (array $row) {
                return Product::query()->updateOrCreate(
                    ['sku' => $row[0]],
                    [
                        'name' => 'Product details',
                        'uom' => $row[1],
                        'base_price' => $row[2],
                        'product_type' => 'finished',
                        'is_active' => true,
                    ]
                );
            });

            $megumin = Agent::query()->updateOrCreate(
                ['email' => 'megumin_ryu@mail.com'],
                [
                    'name' => 'Megumin Ryu',
                    'phone' => '(+62) 898 1234 5678',
                    'area' => 'Main street, Your Location Number 06/B',
                    'zone' => null,
                    'is_active' => true,
                ]
            );

            $rahim = Agent::query()->updateOrCreate(
                ['email' => 'rahim.traders@mail.com'],
                [
                    'name' => 'Rahim Traders',
                    'phone' => '(+880) 1712 334455',
                    'area' => 'Station Road, Khulna',
                    'zone' => null,
                    'is_active' => true,
                ]
            );

            $this->makeInvoice(
                'L779012',
                $megumin,
                '2020-12-26',
                'Main street, Your Location Number 06/B',
                [
                    [$products[0], 1, 90, 90],
                    [$products[1], 2, 30, 60],
                    [$products[2], 1, 99, 99],
                    [$products[3], 3, 60, 180],
                    [$products[4], 3, 60, 180],
                ],
                609,
                40,
                0,
                null
            );

            $pieces = Product::query()->updateOrCreate(
                ['sku' => 'DEMO-SAF-PCS'],
                [
                    'name' => 'Product details',
                    'uom' => 'pcs',
                    'base_price' => 50,
                    'product_type' => 'finished',
                    'is_active' => true,
                ]
            );

            $this->makeInvoice(
                'L779013',
                $rahim,
                '2021-01-18',
                'Station Road, Khulna',
                [
                    [$pieces, 4, 50, 200],
                    [$pieces, 2, 75, 150],
                    [$pieces, 1, 50, 50],
                ],
                400,
                60,
                20,
                150
            );
        });
    }

    protected function makeInvoice(
        string $number,
        Agent $agent,
        string $issuedAt,
        string $address,
        array $lines,
        float $net,
        float $vat,
        float $withholding,
        ?float $receipt
    ): void {
        $order = Order::query()->create([
            'agent_id' => $agent->id,
            'order_type' => 'regular',
            'delivery_date' => $issuedAt,
            'delivery_contact_name' => $agent->name,
            'delivery_contact_phone' => $agent->phone,
            'delivery_address' => $address,
            'status' => 'delivered',
            'total' => $net + $vat,
            'commission_total' => 0,
            'notes' => 'SAF demo invoice '.$number,
            'is_credit_used' => false,
            'payment_mode' => 'bank_transfer',
        ]);

        $invoice = Invoice::query()->create([
            'order_id' => $order->id,
            'number' => $number,
            'issued_at' => $issuedAt,
            'due_at' => $issuedAt,
            'net_total' => $net,
            'vat_amount' => $vat,
            'withholding' => $withholding,
            'status' => $receipt ? 'adjusted' : 'issued',
        ]);

        foreach ($lines as [$product, $qty, $rate, $amount]) {
            InvoiceItem::query()->create([
                'invoice_id' => $invoice->id,
                'product_id' => $product->id,
                'description' => 'Product details',
                'quantity' => $qty,
                'unit_price' => $rate,
                'line_total' => $amount,
            ]);
        }

        if ($receipt) {
            Receipt::query()->create([
                'invoice_id' => $invoice->id,
                'amount' => $receipt,
                'payment_method' => 'bank_transfer',
                'received_at' => $issuedAt,
                'notes' => 'Paid advance',
            ]);
        }
    }
}
