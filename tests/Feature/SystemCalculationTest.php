<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\FinanceController;
use App\Http\Controllers\Admin\CommissionSettlementController;
use App\Models\Agent;
use App\Models\AgentCommissionSettlement;
use App\Models\AgentCommissionRule;
use App\Models\AgentPriceList;
use App\Models\CreditNote;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Receipt;
use App\Models\StockEntry;
use App\Models\TaxClass;
use App\Models\User;
use App\Services\ErpNotificationService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SystemCalculationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('Automated calculation tests require pdo_sqlite (in-memory test database).');
        }

        $this->bootInMemorySqlite();
        $this->createMinimalSchema();
    }

    public function test_agent_order_calculates_total_commission_and_reserves_stock(): void
    {
        $agent = Agent::create([
            'name' => 'Agent One',
            'credit_limit' => 50000,
            'withholding_rate' => 0,
            'is_active' => true,
        ]);

        $user = User::create([
            'name' => 'Agent User',
            'email' => 'agent@example.test',
            'password' => 'secret',
            'role' => 'sales_officer',
            'agent_id' => $agent->id,
        ]);

        $product = Product::create([
            'sku' => 'SKU-500',
            'name' => 'Bottle 500ml',
            'base_price' => 100,
        ]);

        AgentPriceList::create([
            'agent_id' => $agent->id,
            'product_id' => $product->id,
            'price' => 120,
        ]);

        AgentCommissionRule::create([
            'agent_id' => $agent->id,
            'sku' => null,
            'type' => 'percentage',
            'value' => 5,
            'order_type' => 'regular',
            'frequency' => 'per_order',
        ]);

        StockEntry::create([
            'warehouse_id' => 1,
            'product_id' => $product->id,
            'quantity' => 6,
            'status' => 'available',
        ]);
        StockEntry::create([
            'warehouse_id' => 1,
            'product_id' => $product->id,
            'quantity' => 5,
            'status' => 'available',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/agent/orders', [
                'order_type' => 'regular',
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 10,
                    ],
                ],
            ]);

        $response->assertCreated();

        $orderId = (int) $response->json('id');
        $this->assertGreaterThan(0, $orderId);

        $order = Order::findOrFail($orderId);
        $this->assertEquals(1200.0, (float) $order->total);
        $this->assertEquals(60.0, (float) $order->commission_total);
        $this->assertEquals('confirmed', $order->status);

        $item = OrderItem::where('order_id', $orderId)->firstOrFail();
        $this->assertEquals(120.0, (float) $item->unit_price);
        $this->assertEquals(60.0, (float) $item->commission_amount);
        $this->assertEquals(5.0, round((float) $item->commission_rate, 2));

        $availableQty = (float) StockEntry::where('product_id', $product->id)
            ->where('status', 'available')
            ->sum('quantity');
        $reservedQty = (float) StockEntry::where('order_id', $orderId)
            ->where('status', 'reserved')
            ->sum('quantity');

        $this->assertEquals(1.0, $availableQty);
        $this->assertEquals(10.0, $reservedQty);

        $this->assertDatabaseHas('order_status_history', [
            'order_id' => $orderId,
            'status' => 'confirmed',
        ]);
    }

    public function test_invoice_generation_vat_withholding_and_ledger_posting(): void
    {
        $agent = Agent::create([
            'name' => 'Agent VAT',
            'credit_limit' => 100000,
            'withholding_rate' => 5,
            'is_active' => true,
        ]);

        $tax = TaxClass::create([
            'name' => 'Standard VAT',
            'rate' => 15,
        ]);

        $product = Product::create([
            'sku' => 'SKU-1L',
            'name' => 'Bottle 1L',
            'tax_class_id' => $tax->id,
            'base_price' => 20,
        ]);

        $order = Order::create([
            'agent_id' => $agent->id,
            'order_type' => 'regular',
            'status' => 'delivered',
            'total' => 0,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'unit_price' => 20,
            'order_type' => 'regular',
            'commission_amount' => 0,
        ]);

        $controller = app(FinanceController::class);
        $controller->createFromOrder($order->fresh());

        $invoice = Invoice::where('order_id', $order->id)->firstOrFail();

        $this->assertEquals(200.0, (float) $invoice->net_total);
        $this->assertEquals(30.0, (float) $invoice->vat_amount);
        $this->assertEquals(11.5, (float) $invoice->withholding);
        $this->assertEquals('issued', $invoice->status);

        $this->assertDatabaseHas('ledger_entries', [
            'invoice_id' => $invoice->id,
            'account' => 'Accounts Receivable',
            'debit' => 230,
            'credit' => 0,
        ]);
        $this->assertDatabaseHas('ledger_entries', [
            'invoice_id' => $invoice->id,
            'account' => 'Sales Revenue',
            'debit' => 0,
            'credit' => 200,
        ]);
        $this->assertDatabaseHas('ledger_entries', [
            'invoice_id' => $invoice->id,
            'account' => 'VAT Payable',
            'debit' => 0,
            'credit' => 30,
        ]);
    }

    public function test_invoice_outstanding_status_and_finance_alert_calculation(): void
    {
        $invoice = Invoice::create([
            'number' => 'INV-TEST-001',
            'issued_at' => now()->toDateString(),
            'net_total' => 1000,
            'vat_amount' => 150,
            'withholding' => 100,
            'status' => 'issued',
        ]);

        Receipt::create([
            'invoice_id' => $invoice->id,
            'amount' => 200,
            'received_at' => now()->toDateString(),
        ]);

        CreditNote::create([
            'invoice_id' => $invoice->id,
            'number' => 'CN-TEST-001',
            'issued_at' => now()->toDateString(),
            'amount' => 300,
        ]);

        $invoice->recalculateStatus();
        $invoice->refresh();

        $this->assertEquals(1150.0, $invoice->gross_total);
        $this->assertEquals(1050.0, $invoice->cash_total);
        $this->assertEquals(200.0, $invoice->receipts_total);
        $this->assertEquals(300.0, $invoice->credits_total);
        $this->assertEquals(550.0, $invoice->outstanding);
        $this->assertEquals('adjusted', $invoice->status);

        // Add one more receipt to settle outstanding and verify paid status.
        Receipt::create([
            'invoice_id' => $invoice->id,
            'amount' => 550,
            'received_at' => now()->toDateString(),
        ]);
        $invoice->recalculateStatus();
        $invoice->refresh();

        $this->assertEquals(0.0, $invoice->outstanding);
        $this->assertEquals('paid', $invoice->status);

        // Finance alert should include outstanding of other open invoices.
        $open = Invoice::create([
            'number' => 'INV-TEST-002',
            'issued_at' => now()->toDateString(),
            'net_total' => 300,
            'vat_amount' => 45,
            'withholding' => 15,
            'status' => 'issued',
        ]);
        Receipt::create([
            'invoice_id' => $open->id,
            'amount' => 30,
            'received_at' => now()->toDateString(),
        ]);

        $alerts = app(ErpNotificationService::class)->buildSystemAlerts();
        $financeAlert = collect($alerts)->first(fn (array $alert) => ($alert['source'] ?? null) === 'Finance');

        $this->assertNotNull($financeAlert);
        $this->assertSame('Outstanding receivables of BDT 300.00', $financeAlert['message']);
    }

    public function test_start_to_end_calculation_pipeline(): void
    {
        // 1) Master + procurement stock ready (simulated by available stock entries).
        $agent = Agent::create([
            'name' => 'Agent EndToEnd',
            'credit_limit' => 200000,
            'withholding_rate' => 5,
            'is_active' => true,
        ]);

        $user = User::create([
            'name' => 'Agent EndToEnd User',
            'email' => 'agent-e2e@example.test',
            'password' => 'secret',
            'role' => 'sales_officer',
            'agent_id' => $agent->id,
        ]);

        $tax = TaxClass::create([
            'name' => 'BD Standard VAT',
            'rate' => 15,
        ]);

        $product = Product::create([
            'sku' => 'SKU-E2E-1',
            'name' => 'Bottle 1L E2E',
            'tax_class_id' => $tax->id,
            'base_price' => 100,
        ]);

        AgentPriceList::create([
            'agent_id' => $agent->id,
            'product_id' => $product->id,
            'price' => 120,
        ]);

        AgentCommissionRule::create([
            'agent_id' => $agent->id,
            'sku' => null,
            'type' => 'percentage',
            'value' => 5,
            'order_type' => 'regular',
            'frequency' => 'per_order',
        ]);

        StockEntry::create([
            'warehouse_id' => 1,
            'product_id' => $product->id,
            'quantity' => 50,
            'status' => 'available',
        ]);

        // 2) Sales order with commission + stock reservation.
        $orderResponse = $this->actingAs($user, 'sanctum')
            ->postJson('/api/agent/orders', [
                'order_type' => 'regular',
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 20],
                ],
            ]);
        $orderResponse->assertCreated();

        $order = Order::findOrFail((int) $orderResponse->json('id'));
        $this->assertEquals(2400.0, (float) $order->total);
        $this->assertEquals(120.0, (float) $order->commission_total);

        $this->assertEquals(30.0, (float) StockEntry::where('product_id', $product->id)->where('status', 'available')->sum('quantity'));
        $this->assertEquals(20.0, (float) StockEntry::where('order_id', $order->id)->where('status', 'reserved')->sum('quantity'));

        // 3) Delivery finalized (simulate delivered), then invoice from delivered qty.
        $order->update(['status' => 'delivered']);
        app(FinanceController::class)->createFromOrder($order->fresh());
        $invoice = Invoice::where('order_id', $order->id)->firstOrFail();

        // Invoice math:
        // net = 20 * 120 = 2400
        // vat = 15% = 360
        // gross = 2760
        // withholding = 5% of gross = 138
        // cash total = 2622
        $this->assertEquals(2400.0, (float) $invoice->net_total);
        $this->assertEquals(360.0, (float) $invoice->vat_amount);
        $this->assertEquals(138.0, (float) $invoice->withholding);
        $this->assertEquals(2760.0, (float) $invoice->gross_total);
        $this->assertEquals(2622.0, (float) $invoice->cash_total);

        // 4) Collection phase: receipt + adjustment via credit note.
        Receipt::create([
            'invoice_id' => $invoice->id,
            'amount' => 1000,
            'received_at' => now()->toDateString(),
        ]);
        CreditNote::create([
            'invoice_id' => $invoice->id,
            'order_id' => $order->id,
            'number' => 'CN-E2E-1',
            'issued_at' => now()->toDateString(),
            'amount' => 200,
        ]);
        $invoice->recalculateStatus();
        $invoice->refresh();

        // outstanding = 2622 - 1000 - 200 = 1422
        $this->assertEquals(1422.0, (float) $invoice->outstanding);
        $this->assertEquals('adjusted', $invoice->status);

        // 5) Commission settlement generation (month-end cycle).
        app(CommissionSettlementController::class)->generate(
            new Request(['month' => now()->format('Y-m')])
        );

        $settlement = AgentCommissionSettlement::where('agent_id', $agent->id)->firstOrFail();
        $this->assertEquals(2400.0, (float) $settlement->sales_total);
        $this->assertEquals(120.0, (float) $settlement->commission_total);
        $this->assertEquals('open', $settlement->status);

        // 6) Final settlement of receivable.
        Receipt::create([
            'invoice_id' => $invoice->id,
            'amount' => 1422,
            'received_at' => now()->toDateString(),
        ]);
        $invoice->recalculateStatus();
        $invoice->refresh();

        $this->assertEquals(0.0, (float) $invoice->outstanding);
        $this->assertEquals('paid', $invoice->status);
    }

    protected function bootInMemorySqlite(): void
    {
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        config()->set('database.connections.sqlite.foreign_key_constraints', false);

        DB::purge('sqlite');
        DB::reconnect('sqlite');
    }

    protected function createMinimalSchema(): void
    {
        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('credit_limit', 15, 2)->default(0);
            $table->decimal('withholding_rate', 5, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role')->default('admin');
            $table->unsignedBigInteger('agent_id')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('tax_classes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('rate', 5, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('name');
            $table->unsignedBigInteger('tax_class_id')->nullable();
            $table->decimal('base_price', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('agent_price_lists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_id');
            $table->unsignedBigInteger('product_id');
            $table->decimal('price', 12, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('agent_commission_rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_id');
            $table->string('sku')->nullable();
            $table->string('type')->default('percentage');
            $table->decimal('value', 10, 2)->default(0);
            $table->string('order_type')->nullable();
            $table->string('frequency')->default('per_order');
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_id');
            $table->string('order_type')->default('regular');
            $table->date('delivery_date')->nullable();
            $table->string('status')->default('draft');
            $table->decimal('total', 14, 2)->default(0);
            $table->decimal('commission_total', 14, 2)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_credit_used')->default(false);
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->string('order_type')->nullable();
            $table->decimal('commission_rate', 6, 2)->nullable();
            $table->decimal('commission_amount', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('order_status_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('status');
            $table->timestamp('changed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('agent_commission_settlements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_id');
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('sales_total', 14, 2);
            $table->decimal('commission_total', 14, 2);
            $table->string('status')->default('open');
            $table->timestamps();
        });

        Schema::create('stock_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_bill_id')->nullable();
            $table->unsignedBigInteger('goods_receipt_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->unsignedBigInteger('warehouse_location_id')->nullable();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('batch_id')->nullable();
            $table->decimal('quantity', 12, 2);
            $table->string('status')->default('available');
            $table->timestamps();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('number')->unique();
            $table->date('issued_at');
            $table->date('due_at')->nullable();
            $table->decimal('net_total', 14, 2);
            $table->decimal('vat_amount', 14, 2)->default(0);
            $table->decimal('withholding', 14, 2)->default(0);
            $table->string('status')->default('issued');
            $table->timestamps();
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('description');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('line_total', 14, 2);
            $table->timestamps();
        });

        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->decimal('amount', 14, 2);
            $table->string('payment_method')->nullable();
            $table->date('received_at')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('reconciled')->default(false);
            $table->timestamps();
        });

        Schema::create('credit_notes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('number')->unique();
            $table->date('issued_at');
            $table->decimal('amount', 14, 2);
            $table->text('reason')->nullable();
            $table->timestamps();
        });

        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->string('account');
            $table->text('description')->nullable();
            $table->decimal('debit', 14, 2)->default(0);
            $table->decimal('credit', 14, 2)->default(0);
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->timestamps();
        });
    }
}
