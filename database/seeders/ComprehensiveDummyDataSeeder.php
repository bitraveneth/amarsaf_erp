<?php

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\AgentAdvance;
use App\Models\AgentAdvanceApplication;
use App\Models\AgentCommissionSettlement;
use App\Models\CreditNote;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\LedgerEntry;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Receipt;
use App\Models\SalesTarget;
use App\Support\Seeding\SalesDemoConfig;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Rich dummy dataset for demos: orders, invoices, receipts, credit limits,
 * revenue, profit-related ledger entries, advances, and targets.
 *
 * Idempotent — safe to re-run. Skips production unless SEED_COMPREHENSIVE_DUMMY=true.
 */
class ComprehensiveDummyDataSeeder extends Seeder
{
    protected const VAT_RATE = 0.15;

    protected const FINISHED_SKUS = [
        'SAF-500ML-CTN',
        'SAF-500ML',
        'SAF-1L-CTN',
        'SAF-20L-JAR',
    ];

    protected const STANDARD_COSTS = [
        'SAF-500ML-CTN' => 380,
        'SAF-500ML'     => 28,
        'SAF-1L-CTN'    => 620,
        'SAF-20L-JAR'   => 165,
    ];

    public function run(): void
    {
        if (SalesDemoConfig::enabled()) {
            return;
        }

        if ($this->shouldSkip()) {
            return;
        }

        $products = Product::query()
            ->whereIn('sku', self::FINISHED_SKUS)
            ->where('is_active', true)
            ->get()
            ->keyBy('sku');

        if ($products->isEmpty()) {
            return;
        }

        $agents = Agent::query()->where('is_active', true)->orderBy('id')->get();
        if ($agents->isEmpty()) {
            return;
        }

        $this->seedProductCosts($products);
        $this->seedMonthlySales($agents, $products);
        $this->seedDailySales($agents, $products);
        $this->seedCreditNotes();
        $this->seedAgentAdvances($agents);
        $this->seedMonthlyExpenses();
        $this->seedSalesTargets($agents);
        $this->seedCommissionSettlements($agents);
    }

    protected function shouldSkip(): bool
    {
        if (app()->environment(['local', 'testing'])) {
            return false;
        }

        return ! filter_var((string) env('SEED_COMPREHENSIVE_DUMMY', false), FILTER_VALIDATE_BOOLEAN);
    }

    protected function seedProductCosts($products): void
    {
        foreach (self::STANDARD_COSTS as $sku => $cost) {
            $product = $products->get($sku);
            if (! $product) {
                continue;
            }

            if ((float) $product->standard_cost <= 0) {
                $product->update(['standard_cost' => $cost]);
            }
        }
    }

    protected function seedMonthlySales($agents, $products): void
    {
        $today = Carbon::today();
        $productList = $products->values();

        for ($monthsAgo = 11; $monthsAgo >= 0; $monthsAgo--) {
            $monthStart = $today->copy()->subMonths($monthsAgo)->startOfMonth();
            $year = $monthStart->year;
            $month = $monthStart->month;

            foreach ($agents as $agentIndex => $agent) {
                $ordersThisMonth = $this->hashInt("orders-{$agent->id}-{$year}-{$month}", 2, 4);

                for ($slot = 1; $slot <= $ordersThisMonth; $slot++) {
                    $product = $productList[($agentIndex + $slot + $month) % $productList->count()];
                    $day = min(28, $this->hashInt("day-{$agent->id}-{$year}-{$month}-{$slot}", 3, 26));
                    $deliveryDate = Carbon::create($year, $month, $day);

                    $orderType = $this->hashInt("type-{$agent->id}-{$year}-{$month}-{$slot}", 1, 100) <= 25
                        ? 'bulk'
                        : 'regular';
                    $paymentRoll = $this->hashInt("pay-{$agent->id}-{$year}-{$month}-{$slot}", 1, 100);
                    $paymentMode = match (true) {
                        $paymentRoll <= 35 => 'cash',
                        $paymentRoll <= 55 => 'bank_transfer',
                        $paymentRoll <= 75 => 'credit',
                        $paymentRoll <= 90 => 'bkash',
                        default            => 'bank_transfer',
                    };
                    $isCredit = $paymentMode === 'credit';

                    $qtyMin = $orderType === 'bulk' ? 400 : 80;
                    $qtyMax = $orderType === 'bulk' ? 1800 : 600;
                    $quantity = $this->hashInt("qty-{$agent->id}-{$product->id}-{$year}-{$month}-{$slot}", $qtyMin, $qtyMax);

                    $tag = "BULK-M{$year}" . str_pad((string) $month, 2, '0', STR_PAD_LEFT) . "-A{$agent->id}-S{$slot}";

                    $this->createSalesBundle(
                        $agent,
                        $product,
                        $orderType,
                        $quantity,
                        $paymentMode,
                        $isCredit,
                        $deliveryDate,
                        $tag,
                        $slot === 1 ? 'delivered' : 'confirmed'
                    );
                }
            }
        }
    }

    protected function seedDailySales($agents, $products): void
    {
        $today = Carbon::today();
        $productList = $products->values();

        foreach (range(29, 0) as $daysAgo) {
            $deliveryDate = $today->copy()->subDays($daysAgo);

            foreach ($agents as $agentIndex => $agent) {
                if ($this->hashInt("daily-skip-{$agent->id}-{$deliveryDate->format('Ymd')}", 1, 100) > 65) {
                    continue;
                }

                $product = $productList[($agentIndex + $daysAgo) % $productList->count()];
                $quantity = $this->hashInt("daily-qty-{$agent->id}-{$product->id}-{$deliveryDate->format('Ymd')}", 50, 350);
                $tag = 'BULK-D' . $deliveryDate->format('Ymd') . "-A{$agent->id}";

                $this->createSalesBundle(
                    $agent,
                    $product,
                    'regular',
                    $quantity,
                    $daysAgo % 3 === 0 ? 'cash' : 'bank_transfer',
                    false,
                    $deliveryDate,
                    $tag,
                    'confirmed'
                );
            }
        }
    }

    protected function createSalesBundle(
        Agent $agent,
        Product $product,
        string $orderType,
        int $quantity,
        string $paymentMode,
        bool $isCredit,
        Carbon $deliveryDate,
        string $tag,
        string $status
    ): void {
        $unitPrice = (float) ($product->base_price ?? 0);
        if ($unitPrice <= 0) {
            return;
        }

        $total = round($quantity * $unitPrice, 2);
        $commissionRate = $orderType === 'bulk' ? 4.0 : 2.0;
        $commissionTotal = round($total * ($commissionRate / 100), 2);

        $order = Order::updateOrCreate(
            [
                'agent_id'      => $agent->id,
                'delivery_date' => $deliveryDate->toDateString(),
                'notes'         => $tag,
            ],
            [
                'order_type'             => $orderType,
                'agent_reference'        => $agent->location_code,
                'delivery_contact_name'  => $agent->name,
                'delivery_contact_phone' => $agent->phone,
                'delivery_address'       => trim(($agent->area ?? '') . ', ' . ($agent->zone ?? '')),
                'status'                 => $status,
                'total'                  => $total,
                'commission_total'       => $commissionTotal,
                'is_credit_used'         => $isCredit,
                'payment_mode'           => $paymentMode,
            ]
        );

        $item = OrderItem::updateOrCreate(
            [
                'order_id'   => $order->id,
                'product_id' => $product->id,
            ],
            [
                'quantity'          => $quantity,
                'unit_price'        => $unitPrice,
                'order_type'        => $orderType,
                'commission_rate'   => $commissionRate,
                'commission_amount' => $commissionTotal,
            ]
        );

        $invoiceNumber = 'BULK-' . strtoupper(substr(hash('sha256', $tag), 0, 10));
        $issuedAt = $deliveryDate->copy();

        $invoice = Invoice::updateOrCreate(
            ['number' => $invoiceNumber],
            [
                'order_id'    => $order->id,
                'issued_at'   => $issuedAt->toDateString(),
                'due_at'      => $issuedAt->copy()->addDays($isCredit ? 30 : 7)->toDateString(),
                'net_total'   => $total,
                'vat_amount'  => round($total * self::VAT_RATE, 2),
                'withholding' => 0,
                'status'      => 'issued',
            ]
        );

        InvoiceItem::updateOrCreate(
            [
                'invoice_id' => $invoice->id,
                'product_id' => $product->id,
            ],
            [
                'description' => $product->name,
                'quantity'    => $quantity,
                'unit_price'  => $unitPrice,
                'line_total'  => $total,
            ]
        );

        $this->ensureInvoiceLedger($invoice, $order, $item, $product, $issuedAt);

        $this->seedReceiptForInvoice($invoice, $issuedAt, $tag, $isCredit);
    }

    protected function ensureInvoiceLedger(
        Invoice $invoice,
        Order $order,
        OrderItem $item,
        Product $product,
        Carbon $issuedAt
    ): void {
        if (LedgerEntry::where('invoice_id', $invoice->id)->where('account', 'Sales Revenue')->exists()) {
            return;
        }

        $netTotal = (float) $invoice->net_total;
        $vatAmount = (float) $invoice->vat_amount;
        $gross = $netTotal + $vatAmount;
        $unitCost = (float) ($product->standard_cost ?? self::STANDARD_COSTS[$product->sku] ?? 0);
        $cogs = round($unitCost * (float) $item->quantity, 2);
        $commission = (float) $order->commission_total;

        DB::transaction(function () use ($invoice, $order, $issuedAt, $netTotal, $vatAmount, $gross, $cogs, $commission): void {
            $this->createLedgerEntry($issuedAt, [
                'account'     => 'Accounts Receivable',
                'description' => 'Invoice ' . $invoice->number,
                'debit'       => $gross,
                'credit'      => 0,
                'order_id'    => $order->id,
                'invoice_id'  => $invoice->id,
            ]);

            $this->createLedgerEntry($issuedAt, [
                'account'     => 'Sales Revenue',
                'description' => 'Invoice ' . $invoice->number,
                'debit'       => 0,
                'credit'      => $netTotal,
                'order_id'    => $order->id,
                'invoice_id'  => $invoice->id,
            ]);

            if ($vatAmount > 0) {
                $this->createLedgerEntry($issuedAt, [
                    'account'     => 'VAT Payable',
                    'description' => 'VAT on ' . $invoice->number,
                    'debit'       => 0,
                    'credit'      => $vatAmount,
                    'order_id'    => $order->id,
                    'invoice_id'  => $invoice->id,
                ]);
            }

            if ($cogs > 0) {
                $this->createLedgerEntry($issuedAt, [
                    'account'     => 'Cost of Goods Sold',
                    'description' => 'COGS for ' . $invoice->number,
                    'debit'       => $cogs,
                    'credit'      => 0,
                    'order_id'    => $order->id,
                    'invoice_id'  => $invoice->id,
                ]);

                $this->createLedgerEntry($issuedAt, [
                    'account'     => 'Inventory',
                    'description' => 'Inventory relief ' . $invoice->number,
                    'debit'       => 0,
                    'credit'      => $cogs,
                    'order_id'    => $order->id,
                    'invoice_id'  => $invoice->id,
                ]);
            }

            if ($commission > 0) {
                $this->createLedgerEntry($issuedAt, [
                    'account'     => 'Commission Expense',
                    'description' => 'Commission on ' . $invoice->number,
                    'debit'       => $commission,
                    'credit'      => 0,
                    'order_id'    => $order->id,
                    'invoice_id'  => $invoice->id,
                ]);
            }
        });
    }

    protected function createLedgerEntry(Carbon $at, array $attributes): void
    {
        $entry = LedgerEntry::create($attributes);
        $entry->created_at = $at->copy()->setTime(10, 0, 0);
        $entry->updated_at = $entry->created_at;
        $entry->saveQuietly();
    }

    protected function seedReceiptForInvoice(Invoice $invoice, Carbon $issuedAt, string $tag, bool $isCredit): void
    {
        $gross = (float) $invoice->net_total + (float) $invoice->vat_amount;
        if ($gross <= 0) {
            return;
        }

        $roll = $this->hashInt('rcpt-' . $tag, 1, 100);

        if ($isCredit && $roll <= 40) {
            return;
        }

        if (! $isCredit && $roll <= 15) {
            return;
        }

        $receivedAt = $issuedAt->copy()->addDays($this->hashInt('rcpt-days-' . $tag, 1, 14));
        $paymentMethod = $isCredit ? 'bank_transfer' : match ($roll % 4) {
            0       => 'cash',
            1       => 'bank_transfer',
            2       => 'bkash',
            default => 'bank_transfer',
        };

        $amount = $roll <= 55 || $isCredit
            ? round($gross, 2)
            : round($gross * 0.55, 2);

        Receipt::updateOrCreate(
            [
                'invoice_id'  => $invoice->id,
                'received_at' => $receivedAt->toDateString(),
            ],
            [
                'amount'         => $amount,
                'payment_method' => $paymentMethod,
                'notes'          => 'Bulk demo receipt',
            ]
        );
    }

    protected function seedCreditNotes(): void
    {
        $candidates = Invoice::query()
            ->where('number', 'like', 'BULK-%')
            ->orderBy('id')
            ->limit(40)
            ->get();

        foreach ($candidates as $index => $invoice) {
            if ($index % 7 !== 0) {
                continue;
            }

            $amount = round((float) $invoice->net_total * 0.08, 2);
            if ($amount <= 0) {
                continue;
            }

            $creditNote = CreditNote::updateOrCreate(
                ['number' => 'CN-' . $invoice->number],
                [
                    'invoice_id' => $invoice->id,
                    'order_id'   => $invoice->order_id,
                    'issued_at'  => Carbon::parse($invoice->issued_at)->addDays(5)->toDateString(),
                    'amount'     => $amount,
                    'reason'     => 'Demo return allowance — damaged cartons',
                ]
            );

            if (LedgerEntry::where('invoice_id', $invoice->id)->where('account', 'Sales Returns')->exists()) {
                continue;
            }

            $issuedAt = Carbon::parse($creditNote->issued_at);

            $this->createLedgerEntry($issuedAt, [
                'account'     => 'Sales Returns',
                'description' => 'Credit note ' . $creditNote->number,
                'debit'       => $amount,
                'credit'      => 0,
                'order_id'    => $invoice->order_id,
                'invoice_id'  => $invoice->id,
            ]);

            $this->createLedgerEntry($issuedAt, [
                'account'     => 'Accounts Receivable',
                'description' => 'Credit note ' . $creditNote->number,
                'debit'       => 0,
                'credit'      => $amount,
                'order_id'    => $invoice->order_id,
                'invoice_id'  => $invoice->id,
            ]);
        }
    }

    protected function seedAgentAdvances($agents): void
    {
        $corporate = $agents->firstWhere('name', 'Corporate Client 01') ?? $agents->last();
        $north = $agents->firstWhere('name', 'Dhaka North Dealer 01') ?? $agents->first();

        foreach ([$corporate, $north] as $agent) {
            if (! $agent) {
                continue;
            }

            $advance = AgentAdvance::updateOrCreate(
                [
                    'agent_id'  => $agent->id,
                    'reference' => 'ADV-DEMO-' . $agent->id,
                ],
                [
                    'amount'         => $agent->name === 'Corporate Client 01' ? 120000 : 45000,
                    'applied_amount' => 0,
                    'advanced_at'    => Carbon::today()->subDays(20)->toDateString(),
                    'payment_method' => 'bank_transfer',
                    'notes'          => 'Demo advance payment from dealer',
                    'status'         => 'open',
                ]
            );

            $invoice = Invoice::query()
                ->whereHas('order', fn ($q) => $q->where('agent_id', $agent->id))
                ->where('number', 'like', 'BULK-%')
                ->whereDate('issued_at', '>=', Carbon::today()->subDays(25)->toDateString())
                ->orderByDesc('issued_at')
                ->first();

            if (! $invoice || $advance->available_amount <= 0) {
                continue;
            }

            $applyAmount = min((float) $advance->available_amount, round((float) $invoice->net_total * 0.25, 2));
            if ($applyAmount <= 0) {
                continue;
            }

            AgentAdvanceApplication::updateOrCreate(
                [
                    'agent_advance_id' => $advance->id,
                    'invoice_id'       => $invoice->id,
                ],
                [
                    'amount'     => $applyAmount,
                    'applied_at' => Carbon::parse($invoice->issued_at)->addDays(2)->toDateString(),
                ]
            );

            $advance->update([
                'applied_amount' => $applyAmount,
                'status'         => $applyAmount >= (float) $advance->amount ? 'applied' : 'partial',
            ]);
        }
    }

    protected function seedMonthlyExpenses(): void
    {
        $categories = [
            ['Marketing', 18000, 42000],
            ['Utilities', 9000, 16000],
            ['Travel & TA', 5000, 14000],
            ['Fuel & Logistics', 12000, 28000],
            ['Office Supplies', 2000, 8000],
        ];

        for ($monthsAgo = 11; $monthsAgo >= 0; $monthsAgo--) {
            $month = Carbon::today()->subMonths($monthsAgo)->startOfMonth();

            foreach ($categories as $index => [$category, $min, $max]) {
                $amount = $this->hashInt("exp-{$category}-{$month->format('Ym')}", $min, $max);
                $day = $this->hashInt("exp-day-{$index}-{$month->format('Ym')}", 5, 24);

                Expense::updateOrCreate(
                    [
                        'reference' => 'EXP-' . $month->format('Ym') . '-' . str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
                    ],
                    [
                        'date'        => $month->copy()->day($day)->toDateString(),
                        'category'    => $category,
                        'description' => "Demo {$category} — {$month->format('F Y')}",
                        'amount'      => $amount,
                        'status'      => 'recorded',
                    ]
                );
            }
        }
    }

    protected function seedSalesTargets($agents): void
    {
        $targetsByAgent = [
            'Dhaka North Dealer 01'  => 3500000,
            'Dhaka South Dealer 01'  => 2800000,
            'Chattogram Dealer 01'   => 2200000,
            'Sylhet Dealer 01'       => 1800000,
            'Corporate Client 01'    => 6500000,
        ];

        for ($monthsAgo = 5; $monthsAgo >= 0; $monthsAgo--) {
            $start = Carbon::today()->subMonths($monthsAgo)->startOfMonth();
            $end = $start->copy()->endOfMonth();

            foreach ($agents as $agent) {
                $base = $targetsByAgent[$agent->name] ?? 2000000;
                $target = (int) round($base * (0.92 + ($monthsAgo * 0.015)));

                SalesTarget::updateOrCreate(
                    [
                        'agent_id'     => $agent->id,
                        'employee_id'  => null,
                        'period_start' => $start->toDateString(),
                        'period_end'   => $end->toDateString(),
                    ],
                    [
                        'target_value' => $target,
                    ]
                );
            }
        }
    }

    protected function seedCommissionSettlements($agents): void
    {
        for ($monthsAgo = 2; $monthsAgo >= 0; $monthsAgo--) {
            $start = Carbon::today()->subMonths($monthsAgo)->startOfMonth();
            $end = $start->copy()->endOfMonth();

            foreach ($agents as $agent) {
                $orders = Order::query()
                    ->where('agent_id', $agent->id)
                    ->whereBetween('delivery_date', [$start->toDateString(), $end->toDateString()])
                    ->get();

                if ($orders->isEmpty()) {
                    continue;
                }

                AgentCommissionSettlement::updateOrCreate(
                    [
                        'agent_id'     => $agent->id,
                        'period_start' => $start->toDateString(),
                        'period_end'   => $end->toDateString(),
                    ],
                    [
                        'sales_total'      => $orders->sum('total'),
                        'commission_total' => $orders->sum('commission_total'),
                        'status'           => $monthsAgo === 0 ? 'open' : 'paid',
                    ]
                );
            }
        }
    }

    protected function hashInt(string $key, int $min, int $max): int
    {
        $range = max(1, $max - $min + 1);

        return $min + (hexdec(substr(hash('sha256', $key), 0, 8)) % $range);
    }
}
