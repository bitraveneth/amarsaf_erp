<?php

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Receipt;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Extra demo data specifically to make analytics
 * charts (monthly sales, statistics, targets) look
 * meaningful for walkthroughs.
 *
 * Safe to run multiple times – it only appends data.
 */
class DemoAnalyticsSeeder extends Seeder
{
    public function run(): void
    {
        $product = Product::where('sku', 'SAF-500ML-CTN')->first();
        if (! $product) {
            return;
        }

        $agents = Agent::whereIn('name', [
            'Dhaka North Dealer 01',
            'Dhaka South Dealer 01',
        ])->get();

        if ($agents->isEmpty()) {
            return;
        }

        $unitPrice = $product->base_price ?? 550;

        // Seed one or two orders per month for the current year
        // (used by the monthly sales bar chart).
        $year = Carbon::today()->year;

        for ($month = 1; $month <= 12; $month++) {
            // Use a consistent mid‑month delivery date.
            $deliveryDate = Carbon::create($year, $month, 15);

            foreach ($agents as $agent) {
                // Create a small spread of quantities so bars look different.
                $quantity = random_int(200, 800);
                $total    = $quantity * $unitPrice;

                $order = Order::create([
                    'agent_id'               => $agent->id,
                    'order_type'             => 'regular',
                    'agent_reference'        => $agent->location_code ?? null,
                    'delivery_date'          => $deliveryDate->copy(),
                    'delivery_contact_name'  => $agent->name,
                    'delivery_contact_phone' => $agent->phone,
                    'delivery_address'       => trim(($agent->area ?? '') . ', ' . ($agent->zone ?? '')),
                    'status'                 => 'confirmed',
                    'total'                  => $total,
                    'commission_total'       => 0,
                    'notes'                  => 'Demo analytics order',
                    'is_credit_used'         => false,
                    'payment_mode'           => 'bank_transfer',
                ]);

                OrderItem::create([
                    'order_id'          => $order->id,
                    'product_id'        => $product->id,
                    'quantity'          => $quantity,
                    'unit_price'        => $unitPrice,
                    'order_type'        => 'regular',
                    'commission_rate'   => 0,
                    'commission_amount' => 0,
                ]);

                // Simple invoice for this order
                $netTotal   = $total;
                $vatRate    = 0.15;
                $vatAmount  = round($netTotal * $vatRate, 2);
                $invoiceNum = 'DEMO-' . $year . '-' . $month . '-' . $order->id;

                $invoice = Invoice::create([
                    'order_id'   => $order->id,
                    'number'     => $invoiceNum,
                    'issued_at'  => $deliveryDate->copy(),
                    'due_at'     => $deliveryDate->copy()->addDays(7),
                    'net_total'  => $netTotal,
                    'vat_amount' => $vatAmount,
                    'withholding'=> 0,
                    'status'     => 'issued',
                ]);

                InvoiceItem::create([
                    'invoice_id'  => $invoice->id,
                    'product_id'  => $product->id,
                    'description' => $product->name,
                    'quantity'    => $quantity,
                    'unit_price'  => $unitPrice,
                    'line_total'  => $netTotal,
                ]);

                // Roughly 70% of invoices get a receipt to make
                // receipts / targets charts look realistic.
                if (random_int(1, 100) <= 70) {
                    $paidAmount = round($netTotal + $vatAmount, 2);

                    Receipt::create([
                        'invoice_id'    => $invoice->id,
                        'amount'        => $paidAmount,
                        'payment_method'=> 'bank_transfer',
                        'received_at'   => $deliveryDate->copy()->addDays(3),
                        'notes'         => 'Demo analytics receipt',
                    ]);
                }
            }
        }

        // Additionally seed orders / receipts for the last 7 days so
        // the daily statistics chart never looks empty in a fresh demo.
        $today = Carbon::today();

        foreach (range(6, 0) as $offset) {
            $deliveryDate = $today->copy()->subDays($offset);

            foreach ($agents as $agent) {
                $quantity = random_int(100, 300);
                $total    = $quantity * $unitPrice;

                $order = Order::create([
                    'agent_id'               => $agent->id,
                    'order_type'             => 'regular',
                    'agent_reference'        => $agent->location_code ?? null,
                    'delivery_date'          => $deliveryDate->copy(),
                    'delivery_contact_name'  => $agent->name,
                    'delivery_contact_phone' => $agent->phone,
                    'delivery_address'       => trim(($agent->area ?? '') . ', ' . ($agent->zone ?? '')),
                    'status'                 => 'confirmed',
                    'total'                  => $total,
                    'commission_total'       => 0,
                    'notes'                  => 'Demo daily analytics order',
                    'is_credit_used'         => false,
                    'payment_mode'           => 'cash',
                ]);

                OrderItem::create([
                    'order_id'          => $order->id,
                    'product_id'        => $product->id,
                    'quantity'          => $quantity,
                    'unit_price'        => $unitPrice,
                    'order_type'        => 'regular',
                    'commission_rate'   => 0,
                    'commission_amount' => 0,
                ]);

                $netTotal   = $total;
                $vatRate    = 0.15;
                $vatAmount  = round($netTotal * $vatRate, 2);
                $invoiceNum = 'DEMO-DAY-' . $deliveryDate->format('Ymd') . '-' . $order->id;

                $invoice = Invoice::create([
                    'order_id'   => $order->id,
                    'number'     => $invoiceNum,
                    'issued_at'  => $deliveryDate->copy(),
                    'due_at'     => $deliveryDate->copy()->addDays(7),
                    'net_total'  => $netTotal,
                    'vat_amount' => $vatAmount,
                    'withholding'=> 0,
                    'status'     => 'issued',
                ]);

                InvoiceItem::create([
                    'invoice_id'  => $invoice->id,
                    'product_id'  => $product->id,
                    'description' => $product->name,
                    'quantity'    => $quantity,
                    'unit_price'  => $unitPrice,
                    'line_total'  => $netTotal,
                ]);

                // Assume full payment for these recent invoices
                Receipt::create([
                    'invoice_id'    => $invoice->id,
                    'amount'        => round($netTotal + $vatAmount, 2),
                    'payment_method'=> 'cash',
                    'received_at'   => $deliveryDate->copy()->addDays(1),
                    'notes'         => 'Demo daily analytics receipt',
                ]);
            }
        }
    }
}
