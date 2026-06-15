<?php

namespace App\Support\Seeding;

use App\Models\Agent;
use App\Models\AgentAdvance;
use App\Models\AgentAdvanceApplication;
use App\Models\AgentCommissionSettlement;
use App\Models\Batch;
use App\Models\BillOfMaterial;
use App\Models\CreditNote;
use App\Models\Delivery;
use App\Models\DeliveryItem;
use App\Models\DeliveryRoute;
use App\Models\Employee;
use App\Models\EmployeeOvertime;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\LedgerEntry;
use App\Models\LogisticsBill;
use App\Models\LogisticsBillLine;
use App\Models\LogisticsBillPayment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductionRun;
use App\Models\Receipt;
use App\Models\SalaryDistribution;
use App\Models\SalesTarget;
use App\Models\BillPayment;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\PurchaseBill;
use App\Models\PurchaseBillItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockEntry;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\WarehouseLocation;
use App\Models\TransportCarrier;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VisitPlan;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Console\OutputStyle;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SalesDemoTimelineBuilder
{
    protected const VAT_RATE = 0.15;

    protected const TAG_PREFIX = 'DEMO-TIMELINE-';

    protected const PRODUCTION_PREFIX = 'DEMO-YR-';

    protected ?OutputStyle $output = null;

    /** @var EloquentCollection<int, Product> */
    protected EloquentCollection $finishedProducts;

    /** @var EloquentCollection<int, Agent> */
    protected EloquentCollection $agents;

    /** @var EloquentCollection<int, Employee> */
    protected EloquentCollection $employees;

    protected ?Warehouse $factory = null;

    protected ?User $adminUser = null;

    /** @var array<string, int> */
    protected array $volume;

    public function __construct(?OutputStyle $output = null)
    {
        $this->output = $output;
        $this->volume = SalesDemoConfig::volumeProfile();
    }

    public function run(): void
    {
        if (! SalesDemoConfig::enabled()) {
            return;
        }

        if (! $this->bootstrap()) {
            return;
        }

        $this->line('Seeding 1-year sales demo timeline…');

        $this->seedProductionYear();
        $this->seedSalesTimeline();
        $this->seedProcurementYear();
        $this->seedReturns();
        $this->seedCustomerReturnMovements();
        $this->seedPendingProductionReceipts();
        $this->seedLowStockScenario();
        $this->seedPayrollYear();
        $this->seedExpensesYear();
        $this->seedSalesTargets();
        $this->seedCommissionSettlements();
        $this->seedAgentAdvances();
        $this->seedCreditNotes();
        $this->seedLogistics();
        $this->seedVisitPlans();
        $this->seedOvertime();
        $this->seedBankReconciliationFlags();

        $this->line('Sales demo timeline complete.');
    }

    protected function bootstrap(): bool
    {
        $this->finishedProducts = Product::query()
            ->where('is_active', true)
            ->where(function ($query) {
                $query->where('product_type', 'finished')->orWhereNull('product_type');
            })
            ->orderBy('id')
            ->get();

        if ($this->finishedProducts->isEmpty()) {
            $this->finishedProducts = Product::query()
                ->where('is_active', true)
                ->where(function ($q) {
                    $q->whereNull('product_type')->orWhere('product_type', 'finished');
                })
                ->get();
        }

        $this->agents = Agent::query()->where('is_active', true)->orderBy('id')->get();
        $this->employees = Employee::query()->orderBy('id')->get();
        $this->factory = Warehouse::where('name', 'Factory')->first();
        $this->adminUser = User::where('role', 'admin')->first();

        if ($this->finishedProducts->isEmpty() || $this->agents->isEmpty()) {
            return false;
        }

        $this->ensureProductEconomics($this->finishedProducts);

        return true;
    }

    protected function ensureProductEconomics(Collection $products): void
    {
        foreach ($products as $product) {
            $updates = [];

            if ((float) $product->base_price <= 0) {
                $updates['base_price'] = $this->hashInt("price-{$product->sku}", 420, 980);
            }

            if ((float) $product->standard_cost <= 0) {
                $base = (float) ($updates['base_price'] ?? $product->base_price);
                $updates['standard_cost'] = round($base * 0.62, 2);
            }

            if ($updates !== []) {
                $product->update($updates);
            }
        }
    }

    protected function seedProductionYear(): void
    {
        if (! $this->factory) {
            return;
        }

        $supervisor = Employee::where('name', 'Production Manager')->first();
        $qcUser = User::where('role', 'qc_officer')->first() ?: $this->adminUser;
        $warehouseUser = User::where('role', 'warehouse_officer')->first() ?: $this->adminUser;
        $today = Carbon::today();
        $totalDays = SalesDemoConfig::totalDays();
        $runsPerWeek = $this->volume['productionPerWeek'];
        $seq = (int) ProductionRun::where('order_number', 'like', self::PRODUCTION_PREFIX . '%')->count();

        for ($dayOffset = $totalDays; $dayOffset >= 0; $dayOffset -= 7) {
            $weekDate = $today->copy()->subDays($dayOffset);

            for ($slot = 1; $slot <= $runsPerWeek; $slot++) {
                $seq++;
                $product = $this->finishedProducts[($seq + $weekDate->weekOfYear) % $this->finishedProducts->count()];
                $runDate = $weekDate->copy()->addDays($slot % 5);
                $orderNumber = self::PRODUCTION_PREFIX . str_pad((string) $seq, 5, '0', STR_PAD_LEFT);

                if (ProductionRun::where('order_number', $orderNumber)->exists()) {
                    continue;
                }

                $quantity = $this->hashInt("prod-qty-{$orderNumber}", 6000, 24000);
                $batchCode = 'DEMO-B-' . $runDate->format('ymd') . '-' . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);

                $batch = Batch::firstOrCreate(
                    ['batch_code' => $batchCode],
                    [
                        'product_id' => $product->id,
                        'production_date' => $runDate,
                        'expiry_date' => $runDate->copy()->addMonths(6),
                        'qc_status' => 'approved',
                        'notes' => 'Sales demo production batch',
                    ]
                );

                $materialUnitCost = (float) BillOfMaterial::query()
                    ->where('product_id', $product->id)
                    ->where('is_active', true)
                    ->value('material_unit_cost');

                if ($materialUnitCost <= 0) {
                    $materialUnitCost = (float) $product->standard_cost * 0.55;
                }

                $run = ProductionRun::create([
                    'order_number' => $orderNumber,
                    'product_id' => $product->id,
                    'batch_id' => $batch->id,
                    'warehouse_id' => $this->factory->id,
                    'line' => ['Line 1', 'Line 2', 'Line 3'][$seq % 3],
                    'shift' => ['Morning', 'Evening', 'Night'][$seq % 3],
                    'quantity' => $quantity,
                    'status' => 'completed',
                    'supervisor_id' => $supervisor?->id,
                    'qc_status' => 'approved',
                    'qc_passed_quantity' => $quantity,
                    'qc_rejected_quantity' => 0,
                    'approved_by' => $qcUser?->id,
                    'approved_at' => $runDate->copy()->setTime(14, 0),
                    'material_unit_cost' => $materialUnitCost,
                    'material_total_cost' => round($materialUnitCost * $quantity, 2),
                    'stock_confirmed_at' => $runDate->copy()->setTime(16, 0),
                    'stock_confirmed_by' => $warehouseUser?->id,
                    'notes' => 'Sales demo production run',
                ]);

                $run->created_at = $runDate->copy()->setTime(8, 0);
                $run->updated_at = $run->created_at;
                $run->saveQuietly();

                StockEntry::firstOrCreate(
                    [
                        'warehouse_id' => $this->factory->id,
                        'product_id' => $product->id,
                        'batch_id' => $batch->id,
                    ],
                    [
                        'quantity' => $quantity,
                        'status' => 'available',
                    ]
                );
            }

            if ($weekDate->weekOfYear % 4 === 0) {
                $this->line('  Production through ' . $weekDate->format('M Y') . '…');
            }
        }
    }

    protected function seedSalesTimeline(): void
    {
        $today = Carbon::today();
        $totalDays = SalesDemoConfig::totalDays();
        $productList = $this->finishedProducts->values();

        for ($monthsAgo = (SalesDemoConfig::years() * 12) - 1; $monthsAgo >= 0; $monthsAgo--) {
            $monthStart = $today->copy()->subMonths($monthsAgo)->startOfMonth();
            $year = $monthStart->year;
            $month = $monthStart->month;

            DB::transaction(function () use ($year, $month, $monthStart, $productList): void {
                foreach ($this->agents as $agentIndex => $agent) {
                    $ordersThisMonth = $this->hashInt(
                        "orders-{$agent->id}-{$year}-{$month}",
                        $this->volume['ordersMin'],
                        $this->volume['ordersMax']
                    );

                    for ($slot = 1; $slot <= $ordersThisMonth; $slot++) {
                        $product = $productList[($agentIndex + $slot + $month) % $productList->count()];
                        $day = min(28, $this->hashInt("day-{$agent->id}-{$year}-{$month}-{$slot}", 2, 27));
                        $deliveryDate = Carbon::create($year, $month, $day);
                        $orderType = $this->hashInt("type-{$agent->id}-{$year}-{$month}-{$slot}", 1, 100) <= 28 ? 'bulk' : 'regular';
                        $paymentRoll = $this->hashInt("pay-{$agent->id}-{$year}-{$month}-{$slot}", 1, 100);
                        $paymentMode = match (true) {
                            $paymentRoll <= 32 => 'cash',
                            $paymentRoll <= 52 => 'bank_transfer',
                            $paymentRoll <= 72 => 'credit',
                            $paymentRoll <= 88 => 'bkash',
                            default => 'bank_transfer',
                        };

                        $qtyMin = $orderType === 'bulk' ? 350 : 60;
                        $qtyMax = $orderType === 'bulk' ? 1600 : 520;
                        $quantity = $this->hashInt("qty-{$agent->id}-{$product->id}-{$year}-{$month}-{$slot}", $qtyMin, $qtyMax);
                        $tag = self::TAG_PREFIX . "M{$year}" . str_pad((string) $month, 2, '0', STR_PAD_LEFT) . "-A{$agent->id}-S{$slot}";
                        $delivered = $this->hashInt("deliver-{$tag}", 1, 100) <= 85;

                        $this->createSalesBundle(
                            $agent,
                            $product,
                            $orderType,
                            $quantity,
                            $paymentMode,
                            $paymentMode === 'credit',
                            $deliveryDate,
                            $tag,
                            $delivered ? 'delivered' : 'confirmed',
                            $delivered
                        );
                    }
                }
            });

            $this->line('  Sales ' . $monthStart->format('M Y') . '…');
        }

        for ($daysAgo = min(89, $totalDays - 1); $daysAgo >= 0; $daysAgo--) {
            $deliveryDate = $today->copy()->subDays($daysAgo);

            foreach ($this->agents as $agentIndex => $agent) {
                if ($this->hashInt("daily-skip-{$agent->id}-{$deliveryDate->format('Ymd')}", 1, 100) > $this->volume['dailyChance']) {
                    continue;
                }

                $product = $productList[($agentIndex + $daysAgo) % $productList->count()];
                $quantity = $this->hashInt("daily-qty-{$agent->id}-{$product->id}-{$deliveryDate->format('Ymd')}", 40, 280);
                $tag = self::TAG_PREFIX . 'D' . $deliveryDate->format('Ymd') . "-A{$agent->id}";

                $this->createSalesBundle(
                    $agent,
                    $product,
                    'regular',
                    $quantity,
                    $daysAgo % 3 === 0 ? 'cash' : 'bank_transfer',
                    false,
                    $deliveryDate,
                    $tag,
                    'delivered',
                    true
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
        string $status,
        bool $withDelivery,
    ): void {
        $unitPrice = (float) $product->base_price;
        if ($unitPrice <= 0) {
            return;
        }

        $total = round($quantity * $unitPrice, 2);
        $commissionRate = $orderType === 'bulk' ? 4.0 : 2.0;
        $commissionTotal = round($total * ($commissionRate / 100), 2);

        $order = Order::updateOrCreate(
            [
                'agent_id' => $agent->id,
                'delivery_date' => $deliveryDate->toDateString(),
                'notes' => $tag,
            ],
            [
                'order_type' => $orderType,
                'agent_reference' => $agent->location_code,
                'delivery_contact_name' => $agent->name,
                'delivery_contact_phone' => $agent->phone,
                'delivery_address' => trim(($agent->area ?? '') . ', ' . ($agent->zone ?? '')),
                'status' => $status,
                'total' => $total,
                'commission_total' => $commissionTotal,
                'is_credit_used' => $isCredit,
                'payment_mode' => $paymentMode,
            ]
        );

        $item = OrderItem::updateOrCreate(
            [
                'order_id' => $order->id,
                'product_id' => $product->id,
            ],
            [
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'order_type' => $orderType,
                'commission_rate' => $commissionRate,
                'commission_amount' => $commissionTotal,
            ]
        );

        $invoiceNumber = 'DEMO-' . strtoupper(substr(hash('sha256', $tag), 0, 10));
        $issuedAt = $deliveryDate->copy();

        $invoice = Invoice::updateOrCreate(
            ['number' => $invoiceNumber],
            [
                'order_id' => $order->id,
                'issued_at' => $issuedAt->toDateString(),
                'due_at' => $issuedAt->copy()->addDays($isCredit ? 30 : 7)->toDateString(),
                'net_total' => $total,
                'vat_amount' => round($total * self::VAT_RATE, 2),
                'withholding' => 0,
                'status' => 'issued',
            ]
        );

        InvoiceItem::updateOrCreate(
            [
                'invoice_id' => $invoice->id,
                'product_id' => $product->id,
            ],
            [
                'description' => $product->name,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'line_total' => $total,
            ]
        );

        $this->ensureInvoiceLedger($invoice, $order, $item, $product, $issuedAt);
        $this->seedReceiptForInvoice($invoice, $issuedAt, $tag, $isCredit);

        if ($withDelivery && $status === 'delivered') {
            $this->createDeliveryForOrder($order, $item, $product, $quantity);
        }
    }

    protected function createDeliveryForOrder(Order $order, OrderItem $item, Product $product, int $quantity): void
    {
        $route = DeliveryRoute::query()->inRandomOrder()->first();
        $vehicle = Vehicle::query()->inRandomOrder()->first();

        $delivery = Delivery::firstOrCreate(
            ['order_id' => $order->id],
            [
                'route_id' => $route?->id,
                'vehicle_id' => $vehicle?->id,
                'status' => 'delivered',
                'sequence' => 1,
            ]
        );

        DeliveryItem::updateOrCreate(
            [
                'delivery_id' => $delivery->id,
                'order_item_id' => $item->id,
            ],
            [
                'product_id' => $product->id,
                'batch_id' => StockEntry::query()
                    ->where('warehouse_id', $this->factory?->id)
                    ->where('product_id', $product->id)
                    ->where('status', 'available')
                    ->whereNotNull('batch_id')
                    ->orderByDesc('id')
                    ->value('batch_id'),
                'qty_dispatched' => $quantity,
                'qty_delivered' => $quantity,
                'qty_short' => 0,
                'qty_damaged' => 0,
            ]
        );
    }

    protected function ensureInvoiceLedger(
        Invoice $invoice,
        Order $order,
        OrderItem $item,
        Product $product,
        Carbon $issuedAt,
    ): void {
        if (LedgerEntry::where('invoice_id', $invoice->id)->where('account', 'Sales Revenue')->exists()) {
            return;
        }

        $netTotal = (float) $invoice->net_total;
        $vatAmount = (float) $invoice->vat_amount;
        $gross = $netTotal + $vatAmount;
        $unitCost = (float) ($product->standard_cost ?? 0);
        $cogs = round($unitCost * (float) $item->quantity, 2);
        $commission = (float) $order->commission_total;

        DB::transaction(function () use ($invoice, $order, $issuedAt, $netTotal, $vatAmount, $gross, $cogs, $commission): void {
            $this->createLedgerEntry($issuedAt, [
                'account' => 'Accounts Receivable',
                'description' => 'Invoice ' . $invoice->number,
                'debit' => $gross,
                'credit' => 0,
                'order_id' => $order->id,
                'invoice_id' => $invoice->id,
            ]);

            $this->createLedgerEntry($issuedAt, [
                'account' => 'Sales Revenue',
                'description' => 'Invoice ' . $invoice->number,
                'debit' => 0,
                'credit' => $netTotal,
                'order_id' => $order->id,
                'invoice_id' => $invoice->id,
            ]);

            if ($vatAmount > 0) {
                $this->createLedgerEntry($issuedAt, [
                    'account' => 'VAT Payable',
                    'description' => 'VAT on ' . $invoice->number,
                    'debit' => 0,
                    'credit' => $vatAmount,
                    'order_id' => $order->id,
                    'invoice_id' => $invoice->id,
                ]);
            }

            if ($cogs > 0) {
                $this->createLedgerEntry($issuedAt, [
                    'account' => 'Cost of Goods Sold',
                    'description' => 'COGS for ' . $invoice->number,
                    'debit' => $cogs,
                    'credit' => 0,
                    'order_id' => $order->id,
                    'invoice_id' => $invoice->id,
                ]);

                $this->createLedgerEntry($issuedAt, [
                    'account' => 'Inventory',
                    'description' => 'Inventory relief ' . $invoice->number,
                    'debit' => 0,
                    'credit' => $cogs,
                    'order_id' => $order->id,
                    'invoice_id' => $invoice->id,
                ]);
            }

            if ($commission > 0) {
                $this->createLedgerEntry($issuedAt, [
                    'account' => 'Commission Expense',
                    'description' => 'Commission on ' . $invoice->number,
                    'debit' => $commission,
                    'credit' => 0,
                    'order_id' => $order->id,
                    'invoice_id' => $invoice->id,
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

        if ($isCredit && $roll <= 35) {
            return;
        }

        if (! $isCredit && $roll <= 12) {
            return;
        }

        $receivedAt = $issuedAt->copy()->addDays($this->hashInt('rcpt-days-' . $tag, 1, 18));
        $amount = $roll <= 58 || $isCredit ? round($gross, 2) : round($gross * 0.6, 2);

        Receipt::updateOrCreate(
            [
                'invoice_id' => $invoice->id,
                'received_at' => $receivedAt->toDateString(),
            ],
            [
                'amount' => $amount,
                'payment_method' => $isCredit ? 'bank_transfer' : match ($roll % 4) {
                    0 => 'cash',
                    1 => 'bank_transfer',
                    2 => 'bkash',
                    default => 'bank_transfer',
                },
                'notes' => 'Sales demo receipt',
            ]
        );
    }

    protected function seedReturns(): void
    {
        $delivered = Order::query()
            ->where('notes', 'like', self::TAG_PREFIX . '%')
            ->where('status', 'delivered')
            ->with('items')
            ->orderBy('id')
            ->get();

        foreach ($delivered as $index => $order) {
            if ($index % 33 !== 0) {
                continue;
            }

            $item = $order->items->first();
            if (! $item) {
                continue;
            }

            $returnQty = max(10, (int) round((float) $item->quantity * 0.08));
            $unitPrice = (float) $item->unit_price;
            $total = round($returnQty * $unitPrice * -1, 2);
            $tag = self::TAG_PREFIX . 'RET-' . $order->id;

            Order::updateOrCreate(
                [
                    'agent_id' => $order->agent_id,
                    'notes' => $tag,
                ],
                [
                    'order_type' => 'return',
                    'delivery_date' => Carbon::parse($order->delivery_date)->addDays(4)->toDateString(),
                    'status' => 'confirmed',
                    'total' => $total,
                    'commission_total' => 0,
                    'is_credit_used' => true,
                    'payment_mode' => 'credit',
                ]
            );

            $returnOrder = Order::where('notes', $tag)->first();
            if (! $returnOrder) {
                continue;
            }

            OrderItem::updateOrCreate(
                [
                    'order_id' => $returnOrder->id,
                    'product_id' => $item->product_id,
                ],
                [
                    'quantity' => $returnQty * -1,
                    'unit_price' => $unitPrice,
                    'order_type' => 'return',
                    'commission_rate' => 0,
                    'commission_amount' => 0,
                ]
            );
        }
    }

    protected function seedPayrollYear(): void
    {
        if ($this->employees->isEmpty()) {
            return;
        }

        $patterns = [
            ['base_salary' => 35000, 'bonus' => 5000, 'ta_allowances' => 2000, 'da_allowances' => 1500, 'commission' => 0],
            ['base_salary' => 28000, 'bonus' => 3000, 'ta_allowances' => 1500, 'da_allowances' => 1200, 'commission' => 0],
            ['base_salary' => 42000, 'bonus' => 8000, 'ta_allowances' => 2500, 'da_allowances' => 2000, 'commission' => 3500],
            ['base_salary' => 32000, 'bonus' => 4000, 'ta_allowances' => 1800, 'da_allowances' => 1400, 'commission' => 1200],
        ];

        for ($monthsAgo = (SalesDemoConfig::years() * 12) - 1; $monthsAgo >= 0; $monthsAgo--) {
            $start = Carbon::today()->subMonths($monthsAgo)->startOfMonth();
            $end = $start->copy()->endOfMonth();

            foreach ($this->employees as $index => $employee) {
                $pattern = $patterns[$index % count($patterns)];

                SalaryDistribution::updateOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'period_start' => $start->toDateString(),
                        'period_end' => $end->toDateString(),
                    ],
                    $pattern + [
                        'payment_method' => $index % 2 === 0 ? 'bank' : 'cash',
                        'document_path' => null,
                        'remarks' => 'Sales demo payroll — ' . $start->format('M Y'),
                    ]
                );
            }
        }
    }

    protected function seedExpensesYear(): void
    {
        $categories = [
            ['marketing', 12000, 42000],
            ['utilities', 8000, 18000],
            ['travel', 4000, 14000],
            ['general', 10000, 32000],
            ['other', 1500, 7000],
            ['rent', 6000, 22000],
        ];

        for ($monthsAgo = (SalesDemoConfig::years() * 12) - 1; $monthsAgo >= 0; $monthsAgo--) {
            $month = Carbon::today()->subMonths($monthsAgo)->startOfMonth();

            foreach ($categories as $index => [$category, $min, $max]) {
                $entries = SalesDemoConfig::isLarge() ? 2 : 1;

                for ($entry = 1; $entry <= $entries; $entry++) {
                    $amount = $this->hashInt("exp-{$category}-{$month->format('Ym')}-{$entry}", $min, $max);
                    $day = min(28, $this->hashInt("exp-day-{$index}-{$entry}-{$month->format('Ym')}", 3, 26));

                    Expense::updateOrCreate(
                        [
                            'reference' => 'DEMO-EXP-' . $month->format('Ym') . '-' . str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) . "-{$entry}",
                        ],
                        [
                            'date' => $month->copy()->day($day)->toDateString(),
                            'category' => $category,
                            'description' => 'Sales demo ' . str_replace('_', ' ', $category) . " — {$month->format('F Y')}",
                            'amount' => $amount,
                            'status' => 'recorded',
                        ]
                    );
                }
            }
        }
    }

    protected function seedSalesTargets(): void
    {
        for ($monthsAgo = (SalesDemoConfig::years() * 12) - 1; $monthsAgo >= 0; $monthsAgo--) {
            $start = Carbon::today()->subMonths($monthsAgo)->startOfMonth();
            $end = $start->copy()->endOfMonth();

            foreach ($this->agents as $agentIndex => $agent) {
                $target = 1800000 + ($agentIndex * 250000) + ($monthsAgo * 35000);

                SalesTarget::updateOrCreate(
                    [
                        'agent_id' => $agent->id,
                        'employee_id' => null,
                        'period_start' => $start->toDateString(),
                        'period_end' => $end->toDateString(),
                    ],
                    ['target_value' => $target]
                );
            }
        }
    }

    protected function seedCommissionSettlements(): void
    {
        for ($monthsAgo = (SalesDemoConfig::years() * 12) - 1; $monthsAgo >= 0; $monthsAgo--) {
            $start = Carbon::today()->subMonths($monthsAgo)->startOfMonth();
            $end = $start->copy()->endOfMonth();

            foreach ($this->agents as $agent) {
                $orders = Order::query()
                    ->where('agent_id', $agent->id)
                    ->whereBetween('delivery_date', [$start->toDateString(), $end->toDateString()])
                    ->where('order_type', '!=', 'return')
                    ->get();

                if ($orders->isEmpty()) {
                    continue;
                }

                AgentCommissionSettlement::updateOrCreate(
                    [
                        'agent_id' => $agent->id,
                        'period_start' => $start->toDateString(),
                        'period_end' => $end->toDateString(),
                    ],
                    [
                        'sales_total' => $orders->sum('total'),
                        'commission_total' => $orders->sum('commission_total'),
                        'status' => $monthsAgo === 0 ? 'open' : 'paid',
                    ]
                );
            }
        }
    }

    protected function seedAgentAdvances(): void
    {
        foreach ($this->agents->take(8) as $index => $agent) {
            AgentAdvance::updateOrCreate(
                [
                    'agent_id' => $agent->id,
                    'reference' => 'DEMO-ADV-' . $agent->id,
                ],
                [
                    'amount' => 25000 + ($index * 5000),
                    'applied_amount' => 0,
                    'advanced_at' => Carbon::today()->subDays(40 + $index)->toDateString(),
                    'payment_method' => 'bank_transfer',
                    'notes' => 'Sales demo agent advance',
                    'status' => 'open',
                ]
            );
        }
    }

    protected function seedCreditNotes(): void
    {
        $candidates = Invoice::query()
            ->where('number', 'like', 'DEMO-%')
            ->orderBy('id')
            ->limit(120)
            ->get();

        foreach ($candidates as $index => $invoice) {
            if ($index % 9 !== 0) {
                continue;
            }

            $amount = round((float) $invoice->net_total * 0.06, 2);
            if ($amount <= 0) {
                continue;
            }

            CreditNote::updateOrCreate(
                ['number' => 'CN-' . $invoice->number],
                [
                    'invoice_id' => $invoice->id,
                    'order_id' => $invoice->order_id,
                    'issued_at' => Carbon::parse($invoice->issued_at)->addDays(6)->toDateString(),
                    'amount' => $amount,
                    'reason' => 'Sales demo return allowance',
                ]
            );
        }
    }

    protected function seedLogistics(): void
    {
        $carrier = TransportCarrier::firstOrCreate(
            ['name' => 'Demo Freight Lines'],
            [
                'contact_person' => 'Karim Hossain',
                'phone' => '01711-000111',
                'email' => 'freight@demo.local',
                'is_active' => true,
            ]
        );

        $route = DeliveryRoute::query()->first();

        for ($monthsAgo = 11; $monthsAgo >= 0; $monthsAgo--) {
            $billDate = Carbon::today()->subMonths($monthsAgo)->startOfMonth()->addDays(10);
            $number = 'DEMO-LB-' . $billDate->format('Ym');

            $bill = LogisticsBill::updateOrCreate(
                ['number' => $number],
                [
                    'transport_carrier_id' => $carrier->id,
                    'bill_date' => $billDate->toDateString(),
                    'due_date' => $billDate->copy()->addDays(20)->toDateString(),
                    'service_type' => LogisticsBill::SERVICE_EXTERNAL_FREIGHT,
                    'delivery_route_id' => $route?->id,
                    'trip_date' => $billDate->toDateString(),
                    'net_total' => $this->hashInt("lb-{$number}", 18000, 65000),
                    'vat_amount' => 0,
                    'status' => $monthsAgo <= 1 ? 'open' : 'paid',
                    'notes' => 'Sales demo logistics bill',
                ]
            );

            LogisticsBillLine::updateOrCreate(
                [
                    'logistics_bill_id' => $bill->id,
                    'description' => 'Hired truck — regional delivery',
                ],
                [
                    'amount' => (float) $bill->net_total,
                ]
            );

            if ($bill->status === 'paid') {
                LogisticsBillPayment::updateOrCreate(
                    [
                        'logistics_bill_id' => $bill->id,
                        'paid_at' => $billDate->copy()->addDays(15)->toDateString(),
                    ],
                    [
                        'amount' => (float) $bill->net_total,
                        'method' => 'bank_transfer',
                        'batch_reference' => 'DEMO-PAY-' . $number,
                    ]
                );
            }
        }
    }

    protected function seedVisitPlans(): void
    {
        $salesReps = $this->employees->filter(fn (Employee $e) => str_contains(strtolower($e->job_position ?? ''), 'sales'))->values();

        if ($salesReps->isEmpty()) {
            return;
        }

        for ($daysAgo = 60; $daysAgo >= 0; $daysAgo -= 3) {
            $date = Carbon::today()->subDays($daysAgo);
            $rep = $salesReps[$daysAgo % $salesReps->count()];
            $agent = $this->agents->values()[$daysAgo % $this->agents->count()];

            VisitPlan::updateOrCreate(
                [
                    'employee_id' => $rep->id,
                    'agent_id' => $agent->id,
                    'date' => $date->toDateString(),
                ],
                [
                    'status' => $daysAgo > 2 ? 'completed' : 'planned',
                    'title' => 'Route visit — ' . ($agent->zone ?? 'Territory'),
                    'notes' => 'Sales demo visit plan',
                ]
            );
        }
    }

    protected function seedOvertime(): void
    {
        $productionStaff = $this->employees->filter(function (Employee $employee) {
            $department = strtolower($employee->department ?? '');

            return str_contains($department, 'production') || str_contains($department, 'inventory');
        })->values();

        if ($productionStaff->isEmpty()) {
            return;
        }

        for ($monthsAgo = 5; $monthsAgo >= 0; $monthsAgo--) {
            foreach ($productionStaff->take(6) as $index => $employee) {
                $workDate = Carbon::today()->subMonths($monthsAgo)->startOfMonth()->addDays(5 + $index);
                $hours = $this->hashInt("ot-{$employee->id}-{$workDate->format('Ymd')}", 2, 8);
                $hourly = 180 + ($index * 15);

                EmployeeOvertime::updateOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'work_date' => $workDate->toDateString(),
                    ],
                    [
                        'hours' => $hours,
                        'rate_multiplier' => $hours >= 6 ? 2.0 : 1.5,
                        'hourly_rate' => $hourly,
                        'amount' => round($hours * $hourly * ($hours >= 6 ? 2.0 : 1.5), 2),
                        'reason' => 'Sales demo production overtime',
                        'status' => $monthsAgo === 0 ? EmployeeOvertime::STATUS_PENDING : EmployeeOvertime::STATUS_PAID,
                    ]
                );
            }
        }
    }

    protected function seedProcurementYear(): void
    {
        if (! $this->factory) {
            return;
        }

        $centralDepot = Warehouse::where('name', 'Central Depot')->first();
        $warehouse = $centralDepot ?: $this->factory;
        $location = WarehouseLocation::query()->where('warehouse_id', $warehouse->id)->orderBy('id')->first();
        $creator = $this->adminUser;

        $suppliers = Supplier::query()->orderBy('name')->get()->keyBy('name');
        $rawMaterials = Product::query()
            ->where('product_type', 'raw')
            ->where('is_active', true)
            ->orderBy('sku')
            ->get()
            ->keyBy('sku');

        if ($rawMaterials->isEmpty() || $suppliers->isEmpty()) {
            return;
        }

        $utilitiesProduct = Product::where('sku', 'SV-UTIL')->first();

        for ($monthsAgo = (SalesDemoConfig::years() * 12) - 1; $monthsAgo >= 0; $monthsAgo--) {
            $month = Carbon::today()->subMonths($monthsAgo)->startOfMonth();
            $ym = $month->format('Ym');

            $poProfiles = [
                ['supplier' => 'ABC Plastics', 'sku' => 'RM-PREF', 'qty' => 45000, 'price' => 5.05],
                ['supplier' => 'CartonCo', 'sku' => 'RM-CTN-24X500', 'qty' => 8000, 'price' => 20.50],
                ['supplier' => 'XYZ Labels', 'sku' => 'RM-BOPP', 'qty' => 90000, 'price' => 0.62],
            ];

            foreach ($poProfiles as $index => $profile) {
                $supplier = $suppliers->get($profile['supplier']);
                $product = $rawMaterials->get($profile['sku']);

                if (! $supplier || ! $product) {
                    continue;
                }

                $poNumber = 'DEMO-PO-' . $ym . '-' . str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT);
                $orderDate = $month->copy()->day(min(26, 5 + ($index * 4)));
                $statusRoll = $this->hashInt("po-status-{$poNumber}", 1, 100);

                $status = match (true) {
                    $monthsAgo === 0 && $index === 2 => 'draft',
                    $monthsAgo <= 1 && $statusRoll <= 35 => 'approved',
                    $statusRoll <= 55 => 'partial_received',
                    default => 'received',
                };

                $receivedFraction = match ($status) {
                    'received' => 1.0,
                    'partial_received' => 0.55,
                    default => 0.0,
                };

                $quantity = (float) $profile['qty'];
                $receivedQty = round($quantity * $receivedFraction, 2);

                $po = PurchaseOrder::updateOrCreate(
                    ['number' => $poNumber],
                    [
                        'supplier_id' => $supplier->id,
                        'order_date' => $orderDate->toDateString(),
                        'expected_date' => $orderDate->copy()->addDays(7)->toDateString(),
                        'status' => $status,
                        'notes' => 'Sales demo procurement PO',
                    ]
                );

                $poItem = PurchaseOrderItem::updateOrCreate(
                    [
                        'purchase_order_id' => $po->id,
                        'product_id' => $product->id,
                    ],
                    [
                        'description' => $product->name,
                        'quantity' => $quantity,
                        'unit_price' => $profile['price'],
                        'line_total' => round($quantity * $profile['price'], 2),
                        'received_quantity' => $receivedQty,
                    ]
                );

                $billNumber = 'DEMO-PB-' . $ym . '-' . str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT);
                $bill = PurchaseBill::updateOrCreate(
                    ['number' => $billNumber],
                    [
                        'supplier_id' => $supplier->id,
                        'bill_date' => $orderDate->copy()->addDays(3)->toDateString(),
                        'due_date' => $orderDate->copy()->addDays(23)->toDateString(),
                        'warehouse_id' => $warehouse->id,
                        'net_total' => round($quantity * $profile['price'], 2),
                        'vat_amount' => 0,
                        'status' => $receivedFraction >= 1.0 && $monthsAgo > 1 ? 'paid' : 'open',
                    ]
                );

                PurchaseBillItem::updateOrCreate(
                    [
                        'purchase_bill_id' => $bill->id,
                        'product_id' => $product->id,
                    ],
                    [
                        'description' => $product->name,
                        'quantity' => $quantity,
                        'unit_price' => $profile['price'],
                        'line_total' => round($quantity * $profile['price'], 2),
                    ]
                );

                if ($bill->status === 'paid') {
                    BillPayment::updateOrCreate(
                        [
                            'purchase_bill_id' => $bill->id,
                            'paid_at' => $orderDate->copy()->addDays(18)->toDateString(),
                        ],
                        [
                            'amount' => (float) $bill->net_total,
                            'method' => 'bank_transfer',
                            'batch_reference' => 'DEMO-PAY-' . $billNumber,
                        ]
                    );
                }

                if ($receivedQty > 0) {
                    $grnNumber = 'DEMO-GRN-' . $ym . '-' . str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT);

                    $receipt = GoodsReceipt::updateOrCreate(
                        ['grn_number' => $grnNumber],
                        [
                            'purchase_order_id' => $po->id,
                            'purchase_bill_id' => $bill->id,
                            'supplier_id' => $supplier->id,
                            'warehouse_id' => $warehouse->id,
                            'created_by' => $creator?->id,
                            'received_at' => $orderDate->copy()->addDays(8)->setTime(11, 0),
                            'status' => 'posted',
                            'notes' => 'Sales demo goods receipt',
                        ]
                    );

                    $grnItem = GoodsReceiptItem::updateOrCreate(
                        [
                            'goods_receipt_id' => $receipt->id,
                            'purchase_order_item_id' => $poItem->id,
                        ],
                        [
                            'product_id' => $product->id,
                            'warehouse_location_id' => $location?->id,
                            'quantity' => $receivedQty,
                            'unit_cost' => $profile['price'],
                            'line_total' => round($receivedQty * $profile['price'], 2),
                            'qc_status' => 'approved',
                            'remarks' => 'Demo GRN line',
                        ]
                    );

                    StockEntry::updateOrCreate(
                        [
                            'goods_receipt_id' => $receipt->id,
                            'warehouse_id' => $warehouse->id,
                            'product_id' => $product->id,
                        ],
                        [
                            'purchase_bill_id' => $bill->id,
                            'warehouse_location_id' => $location?->id,
                            'quantity' => $receivedQty,
                            'status' => 'available',
                        ]
                    );

                    unset($grnItem);
                }
            }

            $operatingBills = [
                ['supplier' => 'Local Utility', 'suffix' => 'ELEC', 'description' => 'Factory electricity bill', 'amount' => 92000, 'product_id' => $utilitiesProduct?->id],
                ['supplier' => 'Catering Plus', 'suffix' => 'FOOD', 'description' => 'Staff cafeteria & food cost', 'amount' => 38000, 'product_id' => null],
                ['supplier' => 'Guest & Honor Committee', 'suffix' => 'GUEST', 'description' => 'Guest honor & hospitality', 'amount' => 24000, 'product_id' => null],
            ];

            foreach ($operatingBills as $billIndex => $operating) {
                $supplier = $suppliers->get($operating['supplier']);

                if (! $supplier) {
                    continue;
                }

                $billNumber = 'DEMO-PB-' . $operating['suffix'] . '-' . $ym;
                $billDate = $month->copy()->day(min(28, 10 + $billIndex));

                $bill = PurchaseBill::updateOrCreate(
                    ['number' => $billNumber],
                    [
                        'supplier_id' => $supplier->id,
                        'bill_date' => $billDate->toDateString(),
                        'due_date' => $billDate->copy()->addDays(20)->toDateString(),
                        'net_total' => $operating['amount'],
                        'vat_amount' => 0,
                        'status' => $monthsAgo > 2 && $billIndex === 0 ? 'paid' : ($monthsAgo === 0 ? 'open' : 'part_paid'),
                    ]
                );

                PurchaseBillItem::updateOrCreate(
                    ['purchase_bill_id' => $bill->id, 'description' => $operating['description']],
                    [
                        'product_id' => $operating['product_id'],
                        'quantity' => 1,
                        'unit_price' => $operating['amount'],
                        'line_total' => $operating['amount'],
                    ]
                );

                if (in_array($bill->status, ['paid', 'part_paid'], true)) {
                    $paidAmount = $bill->status === 'paid'
                        ? (float) $bill->net_total
                        : round((float) $bill->net_total * 0.5, 2);

                    BillPayment::updateOrCreate(
                        [
                            'purchase_bill_id' => $bill->id,
                            'paid_at' => $billDate->copy()->addDays(12)->toDateString(),
                        ],
                        [
                            'amount' => $paidAmount,
                            'method' => 'bank_transfer',
                            'batch_reference' => 'DEMO-PAY-' . $billNumber,
                        ]
                    );
                }
            }
        }

        $this->line('  Procurement POs, GRNs, and bills…');
    }

    protected function seedCustomerReturnMovements(): void
    {
        if (! $this->factory) {
            return;
        }

        $delivered = Order::query()
            ->where('notes', 'like', self::TAG_PREFIX . '%')
            ->where('status', 'delivered')
            ->with(['items', 'deliveries.items'])
            ->orderBy('id')
            ->get();

        foreach ($delivered as $index => $order) {
            if ($index % 18 !== 0) {
                continue;
            }

            $item = $order->items->first();
            $deliveryItem = $order->deliveries->first()?->items->first();

            if (! $item || ! $deliveryItem) {
                continue;
            }

            $returnQty = max(5.0, round((float) $deliveryItem->qty_delivered * 0.04, 2));
            $batchId = $deliveryItem->batch_id;

            $entry = StockEntry::query()
                ->where('warehouse_id', $this->factory->id)
                ->where('product_id', $item->product_id)
                ->where('status', 'available')
                ->when(
                    $batchId,
                    fn ($query) => $query->where('batch_id', $batchId),
                    fn ($query) => $query->whereNull('batch_id')
                )
                ->first();

            if (! $entry) {
                $entry = StockEntry::create([
                    'warehouse_id' => $this->factory->id,
                    'product_id' => $item->product_id,
                    'batch_id' => $batchId,
                    'quantity' => 0,
                    'status' => 'available',
                ]);
            }

            if (StockMovement::where('order_id', $order->id)->where('type', 'customer-return')->exists()) {
                continue;
            }

            $entry->quantity = (float) $entry->quantity + $returnQty;
            $entry->save();

            StockMovement::recordFor(
                $entry,
                'customer-return',
                $returnQty,
                self::TAG_PREFIX . 'customer return',
                $order->id
            );
        }

        $this->line('  Customer return stock movements…');
    }

    protected function seedPendingProductionReceipts(): void
    {
        if (! $this->factory) {
            return;
        }

        $template = ProductionRun::with('product', 'batch')
            ->whereNotNull('stock_confirmed_at')
            ->orderByDesc('id')
            ->first();

        if (! $template) {
            return;
        }

        $supervisor = Employee::where('name', 'Production Manager')->first();
        $qcUser = User::where('role', 'qc_officer')->first() ?: $this->adminUser;
        $today = Carbon::today();
        $targetCount = SalesDemoConfig::isLarge() ? 10 : 6;

        for ($slot = 1; $slot <= $targetCount; $slot++) {
            $orderNumber = 'DEMO-PEND-' . $today->format('Ymd') . '-' . str_pad((string) $slot, 2, '0', STR_PAD_LEFT);

            if (ProductionRun::where('order_number', $orderNumber)->exists()) {
                continue;
            }

            ProductionRun::create([
                'order_number' => $orderNumber,
                'product_id' => $template->product_id,
                'batch_id' => $template->batch_id,
                'warehouse_id' => $this->factory->id,
                'line' => ['Line 1', 'Line 2', 'Line 3'][$slot % 3],
                'shift' => ['Morning', 'Evening'][$slot % 2],
                'quantity' => $this->hashInt("pend-qty-{$orderNumber}", 9000, 22000),
                'status' => 'confirmed',
                'supervisor_id' => $supervisor?->id,
                'qc_status' => 'approved',
                'qc_passed_quantity' => $this->hashInt("pend-pass-{$orderNumber}", 9000, 22000),
                'qc_rejected_quantity' => 0,
                'approved_by' => $qcUser?->id,
                'approved_at' => $today->copy()->subDays($slot % 3)->setTime(13, 30),
                'material_unit_cost' => (float) ($template->material_unit_cost ?? 0),
                'material_total_cost' => (float) ($template->material_total_cost ?? 0),
                'stock_confirmed_at' => null,
                'stock_confirmed_by' => null,
                'notes' => 'Sales demo — awaiting warehouse stock confirmation',
            ]);
        }

        $this->line('  Pending production receipts…');
    }

    protected function seedLowStockScenario(): void
    {
        if (! $this->factory) {
            return;
        }

        $candidates = Product::query()
            ->where('product_type', 'raw')
            ->where('is_active', true)
            ->orderBy('sku')
            ->limit(8)
            ->get();

        foreach ($candidates as $index => $product) {
            $reorderLevel = 5000 + ($index * 1200);
            $onHand = $index < 6 ? max(50, (int) round($reorderLevel * 0.08)) : $reorderLevel + 4000;

            $product->update(['reorder_level' => $reorderLevel]);

            StockEntry::updateOrCreate(
                [
                    'warehouse_id' => $this->factory->id,
                    'product_id' => $product->id,
                    'batch_id' => null,
                ],
                [
                    'quantity' => $onHand,
                    'status' => 'available',
                ]
            );
        }

        $this->line('  Low-stock alert scenario…');
    }

    protected function seedBankReconciliationFlags(): void
    {
        Receipt::query()
            ->where('notes', 'Sales demo receipt')
            ->orderBy('id')
            ->chunkById(200, function ($receipts): void {
                foreach ($receipts as $index => $receipt) {
                    $receipt->reconciled = $index % 3 !== 2;
                    $receipt->saveQuietly();
                }
            });
    }

    protected function hashInt(string $key, int $min, int $max): int
    {
        $range = max(1, $max - $min + 1);

        return $min + (hexdec(substr(hash('sha256', $key), 0, 8)) % $range);
    }

    protected function line(string $message): void
    {
        $this->output?->writeln($message);
    }
}
