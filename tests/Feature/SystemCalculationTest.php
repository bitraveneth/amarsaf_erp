<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\FinanceController;
use App\Http\Controllers\Admin\CommissionReportController;
use App\Http\Controllers\Admin\CommissionSettlementController;
use App\Http\Controllers\Admin\ReportController;
use App\Models\Agent;
use App\Models\AgentCommissionSettlement;
use App\Models\AgentCommissionRule;
use App\Models\AgentPriceList;
use App\Models\Campaign;
use App\Models\CreditNote;
use App\Models\CustomerGift;
use App\Models\Delivery;
use App\Models\DeliveryItem;
use App\Models\Expense;
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

    public function test_invoice_generation_uses_delivered_quantity_when_delivery_items_exist(): void
    {
        $agent = Agent::create([
            'name' => 'Agent Delivered Qty',
            'credit_limit' => 100000,
            'withholding_rate' => 0,
            'is_active' => true,
        ]);

        $tax = TaxClass::create([
            'name' => 'Standard VAT',
            'rate' => 15,
        ]);

        $product = Product::create([
            'sku' => 'SKU-DLV-1',
            'name' => 'Bottle Delivered Qty',
            'tax_class_id' => $tax->id,
            'base_price' => 20,
        ]);

        $order = Order::create([
            'agent_id' => $agent->id,
            'order_type' => 'regular',
            'status' => 'delivered',
            'total' => 0,
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'unit_price' => 20,
            'order_type' => 'regular',
            'commission_amount' => 0,
        ]);

        $delivery = Delivery::create([
            'order_id' => $order->id,
            'status' => 'delivered',
        ]);

        DeliveryItem::create([
            'delivery_id' => $delivery->id,
            'order_item_id' => $orderItem->id,
            'product_id' => $product->id,
            'qty_dispatched' => 10,
            'qty_delivered' => 7,
            'qty_short' => 3,
            'qty_damaged' => 0,
        ]);

        app(FinanceController::class)->createFromOrder($order->fresh());

        $invoice = Invoice::where('order_id', $order->id)->firstOrFail();
        $invoiceItem = $invoice->items()->firstOrFail();

        $this->assertEquals(140.0, (float) $invoice->net_total);
        $this->assertEquals(21.0, (float) $invoice->vat_amount);
        $this->assertEquals(7, (int) $invoiceItem->quantity);
        $this->assertEquals(140.0, (float) $invoiceItem->line_total);
    }

    public function test_commission_settlement_only_counts_delivered_and_invoiced_orders(): void
    {
        $agent = Agent::create([
            'name' => 'Agent Settled',
            'credit_limit' => 100000,
            'withholding_rate' => 0,
            'is_active' => true,
        ]);

        $product = Product::create([
            'sku' => 'SKU-COM-1',
            'name' => 'Commission Product',
            'base_price' => 10,
        ]);

        $deliveredOrder = Order::create([
            'agent_id' => $agent->id,
            'order_type' => 'regular',
            'status' => 'delivered',
            'total' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        OrderItem::create([
            'order_id' => $deliveredOrder->id,
            'product_id' => $product->id,
            'quantity' => 5,
            'unit_price' => 10,
            'order_type' => 'regular',
            'commission_amount' => 5,
        ]);

        app(FinanceController::class)->createFromOrder($deliveredOrder->fresh());

        $openOrder = Order::create([
            'agent_id' => $agent->id,
            'order_type' => 'regular',
            'status' => 'confirmed',
            'total' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        OrderItem::create([
            'order_id' => $openOrder->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'unit_price' => 10,
            'order_type' => 'regular',
            'commission_amount' => 20,
        ]);

        app(CommissionSettlementController::class)->generate(
            new Request(['month' => now()->format('Y-m')])
        );

        $settlement = AgentCommissionSettlement::where('agent_id', $agent->id)->firstOrFail();

        $this->assertEquals(50.0, (float) $settlement->sales_total);
        $this->assertEquals(5.0, (float) $settlement->commission_total);
    }

    public function test_commission_reports_use_realized_delivery_quantities(): void
    {
        $agent = Agent::create([
            'name' => 'Agent Realized',
            'credit_limit' => 100000,
            'withholding_rate' => 0,
            'is_active' => true,
        ]);

        $product = Product::create([
            'sku' => 'SKU-REALIZED-1',
            'name' => 'Realized Quantity Product',
            'product_type' => 'finished',
            'is_active' => true,
            'base_price' => 20,
        ]);

        $order = Order::create([
            'agent_id' => $agent->id,
            'order_type' => 'regular',
            'status' => 'delivered',
            'total' => 0,
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'unit_price' => 20,
            'order_type' => 'regular',
            'commission_rate' => 10,
            'commission_amount' => 20,
        ]);

        $delivery = Delivery::create([
            'order_id' => $order->id,
            'status' => 'delivered',
        ]);

        DeliveryItem::create([
            'delivery_id' => $delivery->id,
            'order_item_id' => $orderItem->id,
            'product_id' => $product->id,
            'qty_dispatched' => 10,
            'qty_delivered' => 7,
            'qty_short' => 3,
            'qty_damaged' => 0,
        ]);

        app(FinanceController::class)->createFromOrder($order->fresh());
        app(CommissionSettlementController::class)->generate(
            new Request(['month' => now()->format('Y-m')])
        );

        $settlement = AgentCommissionSettlement::where('agent_id', $agent->id)->firstOrFail();

        $this->assertEquals(140.0, (float) $settlement->sales_total);
        $this->assertEquals(14.0, (float) $settlement->commission_total);

        $response = app(CommissionReportController::class)->export(
            Request::create('/admin/commissions/export', 'GET', ['month' => now()->format('Y-m')])
        );

        ob_start();
        $response->sendContent();
        $csv = ob_get_clean();

        $this->assertStringContainsString('140.00', $csv);
        $this->assertStringContainsString('14.00', $csv);
    }

    public function test_agent_pricing_validation_failure_preserves_existing_configuration(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-agent-pricing@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);

        $agent = Agent::create([
            'name' => 'Agent Pricing Guard',
            'credit_limit' => 100000,
            'withholding_rate' => 0,
            'is_active' => true,
        ]);

        $sellableProduct = Product::create([
            'sku' => 'SKU-SELLABLE-1',
            'name' => 'Finished Product',
            'product_type' => 'finished',
            'is_active' => true,
            'base_price' => 50,
        ]);

        $serviceProduct = Product::create([
            'sku' => 'SKU-SERVICE-1',
            'name' => 'Service Product',
            'product_type' => 'service',
            'is_active' => true,
            'base_price' => 25,
        ]);

        AgentPriceList::create([
            'agent_id' => $agent->id,
            'product_id' => $sellableProduct->id,
            'price' => 75,
        ]);

        AgentCommissionRule::create([
            'agent_id' => $agent->id,
            'sku' => $sellableProduct->sku,
            'type' => 'percentage',
            'value' => 5,
            'order_type' => 'regular',
            'frequency' => 'per_order',
        ]);

        $response = $this->actingAs($admin)
            ->from(route('admin.agents.pricing.edit', $agent))
            ->patch(route('admin.agents.pricing.update', $agent), [
                'prices' => [
                    (string) $serviceProduct->id => 99,
                ],
                'commissions' => [
                    [
                        'sku' => 'MISSING-SKU',
                        'type' => 'percentage',
                        'value' => 5,
                        'order_type' => 'regular',
                        'frequency' => 'per_order',
                    ],
                ],
            ]);

        $response->assertSessionHasErrors([
            'prices.' . $serviceProduct->id,
            'commissions.0.sku',
        ]);

        $this->assertDatabaseHas('agent_price_lists', [
            'agent_id' => $agent->id,
            'product_id' => $sellableProduct->id,
            'price' => 75,
        ]);
        $this->assertDatabaseHas('agent_commission_rules', [
            'agent_id' => $agent->id,
            'sku' => $sellableProduct->sku,
            'type' => 'percentage',
            'value' => 5,
        ]);
        $this->assertDatabaseCount('agent_price_lists', 1);
        $this->assertDatabaseCount('agent_commission_rules', 1);
    }

    public function test_planned_campaign_and_gift_spend_are_excluded_from_ledger_and_profit_reports(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-spend@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);

        $agent = Agent::create([
            'name' => 'Gift Agent',
            'credit_limit' => 100000,
            'withholding_rate' => 0,
            'is_active' => true,
        ]);

        $campaignResponse = $this->actingAs($admin)
            ->from(route('admin.campaigns.index'))
            ->post(route('admin.campaigns.store'), [
                'name' => 'Planned Summer Campaign',
                'platform' => 'facebook',
                'start_date' => now()->toDateString(),
                'cost' => 100,
                'status' => Campaign::STATUS_PLANNED,
            ]);

        $campaignResponse->assertSessionHasNoErrors();

        $giftResponse = $this->actingAs($admin)
            ->from(route('admin.gifts.index'))
            ->post(route('admin.gifts.store'), [
                'agent_id' => $agent->id,
                'date' => now()->toDateString(),
                'amount' => 50,
                'status' => CustomerGift::STATUS_PLANNED,
            ]);

        $giftResponse->assertSessionHasNoErrors();

        $expenseResponse = $this->actingAs($admin)
            ->from(route('admin.expenses.index'))
            ->post(route('admin.expenses.store'), [
                'date' => now()->toDateString(),
                'category' => 'general',
                'amount' => 30,
                'status' => Expense::STATUS_RECORDED,
            ]);

        $expenseResponse->assertSessionHasNoErrors();

        $campaign = Campaign::firstOrFail();
        $gift = CustomerGift::firstOrFail();
        $expense = Expense::firstOrFail();

        $this->assertDatabaseMissing('ledger_entries', [
            'description' => 'Campaign expense #' . $campaign->id,
        ]);
        $this->assertDatabaseMissing('ledger_entries', [
            'description' => 'Customer gift #' . $gift->id,
        ]);
        $this->assertDatabaseHas('ledger_entries', [
            'description' => 'Expense #' . $expense->id,
            'account' => 'Selling & Distribution Expense',
            'debit' => 30,
            'credit' => 0,
        ]);
        $this->assertDatabaseCount('ledger_entries', 2);

        $view = app(ReportController::class)->profitAndLoss(
            Request::create('/admin/reports/profit-and-loss', 'GET', [
                'from' => now()->startOfMonth()->toDateString(),
                'to' => now()->endOfMonth()->toDateString(),
            ])
        );

        $this->assertEquals(30.0, (float) $view->getData()['otherExpenses']);
    }

    public function test_supplier_creation_rejects_duplicate_name_email_and_tax_id(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-supplier@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);

        DB::table('suppliers')->insert([
            'name' => 'Supplier Prime',
            'contact_person' => 'Owner One',
            'email' => 'supplier@example.test',
            'phone' => '0123456789',
            'address' => 'Test Address',
            'tax_id' => 'TAX-001',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->from(route('admin.suppliers.index'))
            ->post(route('admin.suppliers.store'), [
                'name' => 'Supplier Prime',
                'contact_person' => 'Owner Two',
                'email' => 'SUPPLIER@example.test',
                'phone' => '9876543210',
                'address' => 'Another Address',
                'tax_id' => ' TAX-001 ',
            ]);

        $response->assertSessionHasErrors(['name', 'email', 'tax_id']);
        $this->assertDatabaseCount('suppliers', 1);
    }

    public function test_receipt_cannot_exceed_invoice_outstanding(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);

        $invoice = Invoice::create([
            'number' => 'INV-OVERPAY-001',
            'issued_at' => now()->toDateString(),
            'net_total' => 100,
            'vat_amount' => 0,
            'withholding' => 0,
            'status' => 'issued',
        ]);

        $response = $this->actingAs($admin)
            ->post(route('admin.finance.receipts.store', $invoice), [
                'amount' => 150,
            ]);

        $response->assertSessionHasErrors('amount');
        $this->assertDatabaseCount('receipts', 0);
    }

    public function test_employee_login_creation_requires_system_settings_permission(): void
    {
        $employeeId = DB::table('employees')->insertGetId([
            'name' => 'Employee One',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $hrUser = User::create([
            'name' => 'HR User',
            'email' => 'hr@example.test',
            'password' => 'secret',
            'role' => 'hr_officer',
        ]);

        DB::table('role_permissions')->insert([
            'role' => 'hr_officer',
            'permission_name' => 'control.employees',
        ]);

        $response = $this->actingAs($hrUser)->post(route('admin.employees.user.store', $employeeId), [
            'name' => 'Linked User',
            'email' => 'linked@example.test',
            'role' => 'sales_officer',
            'password' => 'secret123',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('users', [
            'email' => 'linked@example.test',
        ]);
    }

    public function test_stock_audit_store_and_delete_respect_warehouse_scope(): void
    {
        $warehouseOne = DB::table('warehouses')->insertGetId([
            'name' => 'Main Warehouse',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $warehouseTwo = DB::table('warehouses')->insertGetId([
            'name' => 'Remote Warehouse',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $product = Product::create([
            'sku' => 'SKU-AUDIT-1',
            'name' => 'Audit Product',
            'product_type' => 'finished',
            'is_active' => true,
            'base_price' => 10,
        ]);

        $user = User::create([
            'name' => 'Scoped Auditor',
            'email' => 'auditor@example.test',
            'password' => 'secret',
            'role' => 'warehouse_officer',
        ]);

        DB::table('role_permissions')->insert([
            'role' => 'warehouse_officer',
            'permission_name' => 'inventory.manage',
        ]);
        DB::table('user_warehouse_scopes')->insert([
            'user_id' => $user->id,
            'warehouse_id' => $warehouseOne,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $storeResponse = $this->actingAs($user)->post(route('admin.stock.audit.store'), [
            'warehouse_id' => $warehouseTwo,
            'product_id' => $product->id,
            'counted_quantity' => 5,
        ]);

        $storeResponse->assertForbidden();
        $this->assertDatabaseCount('stock_audits', 0);

        $auditId = DB::table('stock_audits')->insertGetId([
            'warehouse_id' => $warehouseTwo,
            'product_id' => $product->id,
            'system_quantity' => 0,
            'counted_quantity' => 1,
            'variance' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $deleteResponse = $this->actingAs($user)->delete(route('admin.stock.audit.destroy', $auditId));

        $deleteResponse->assertForbidden();
        $this->assertDatabaseHas('stock_audits', [
            'id' => $auditId,
        ]);
    }

    public function test_purchase_order_creation_rejects_non_stock_products(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-po@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);

        $supplierId = DB::table('suppliers')->insertGetId([
            'name' => 'Supplier One',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $serviceProduct = Product::create([
            'sku' => 'SKU-SVC-1',
            'name' => 'Service Item',
            'product_type' => 'service',
            'is_active' => true,
            'base_price' => 50,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.purchase-orders.store'), [
            'supplier_id' => $supplierId,
            'order_date' => now()->toDateString(),
            'items' => [
                [
                    'description' => 'Non stock service',
                    'product_id' => $serviceProduct->id,
                    'quantity' => 2,
                    'unit_price' => 50,
                ],
            ],
        ]);

        $response->assertSessionHasErrors('items.0.product_id');
        $this->assertDatabaseCount('purchase_orders', 0);
    }

    public function test_batch_creation_rejects_non_finished_products(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-batch@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);

        $serviceProduct = Product::create([
            'sku' => 'SKU-BATCH-SVC',
            'name' => 'Service Batch Product',
            'product_type' => 'service',
            'is_active' => true,
            'base_price' => 20,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.batches.store'), [
            'product_id' => $serviceProduct->id,
            'batch_code' => 'BATCH-SVC-001',
            'production_date' => now()->toDateString(),
            'qc_status' => 'pending',
        ]);

        $response->assertSessionHasErrors('product_id');
        $this->assertDatabaseCount('batches', 0);
    }

    public function test_bank_reconciliation_only_updates_receipts_within_selected_period(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-recon@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);

        $invoiceJan = Invoice::create([
            'number' => 'INV-RECON-001',
            'issued_at' => '2026-01-05',
            'net_total' => 100,
            'vat_amount' => 0,
            'withholding' => 0,
            'status' => 'issued',
        ]);
        $invoiceFeb = Invoice::create([
            'number' => 'INV-RECON-002',
            'issued_at' => '2026-02-05',
            'net_total' => 100,
            'vat_amount' => 0,
            'withholding' => 0,
            'status' => 'issued',
        ]);

        $janReceipt = Receipt::create([
            'invoice_id' => $invoiceJan->id,
            'amount' => 50,
            'received_at' => '2026-01-10',
            'reconciled' => false,
        ]);
        $febReceipt = Receipt::create([
            'invoice_id' => $invoiceFeb->id,
            'amount' => 50,
            'received_at' => '2026-02-10',
            'reconciled' => false,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.finance.reconciliation.update'), [
            'from' => '2026-01-01',
            'to' => '2026-01-31',
            'reconciled' => [$janReceipt->id, $febReceipt->id],
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertTrue($janReceipt->fresh()->reconciled);
        $this->assertFalse($febReceipt->fresh()->reconciled);
    }

    public function test_warehouse_location_delete_preserves_goods_receipt_history(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-location@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);

        $warehouseId = DB::table('warehouses')->insertGetId([
            'name' => 'History Warehouse',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $locationId = DB::table('warehouse_locations')->insertGetId([
            'warehouse_id' => $warehouseId,
            'code' => 'A-01',
            'description' => 'Rack A1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('goods_receipt_items')->insert([
            'warehouse_location_id' => $locationId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.warehouse-locations.destroy', $locationId));

        $response->assertSessionHas('status');
        $this->assertDatabaseHas('warehouse_locations', [
            'id' => $locationId,
        ]);
    }

    public function test_delivery_creation_rejects_route_vehicle_mismatch_and_duplicate_order_delivery(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-delivery@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);

        $agent = Agent::create([
            'name' => 'Delivery Agent',
            'credit_limit' => 10000,
            'withholding_rate' => 0,
            'is_active' => true,
        ]);

        $order = Order::create([
            'agent_id' => $agent->id,
            'order_type' => 'regular',
            'status' => 'picked',
            'total' => 0,
        ]);

        $routeVehicleId = DB::table('vehicles')->insertGetId([
            'name' => 'Truck A',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $otherVehicleId = DB::table('vehicles')->insertGetId([
            'name' => 'Truck B',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $routeId = DB::table('delivery_routes')->insertGetId([
            'name' => 'North Route',
            'vehicle_id' => $routeVehicleId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $mismatchResponse = $this->actingAs($admin)->post(route('admin.deliveries.store'), [
            'order_id' => $order->id,
            'route_id' => $routeId,
            'vehicle_id' => $otherVehicleId,
            'status' => 'scheduled',
        ]);

        $mismatchResponse->assertSessionHasErrors('vehicle_id');
        $this->assertDatabaseCount('deliveries', 0);

        $createResponse = $this->actingAs($admin)->post(route('admin.deliveries.store'), [
            'order_id' => $order->id,
            'route_id' => $routeId,
            'status' => 'scheduled',
        ]);

        $createResponse->assertSessionHasNoErrors();
        $this->assertDatabaseHas('deliveries', [
            'order_id' => $order->id,
            'route_id' => $routeId,
            'vehicle_id' => $routeVehicleId,
        ]);

        $duplicateResponse = $this->actingAs($admin)->post(route('admin.deliveries.store'), [
            'order_id' => $order->id,
            'route_id' => $routeId,
            'status' => 'scheduled',
        ]);

        $duplicateResponse->assertSessionHasErrors('order_id');
        $this->assertDatabaseCount('deliveries', 1);
    }

    public function test_vehicle_schedule_uses_route_default_vehicle_and_rejects_mismatch(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-schedule@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);

        $routeVehicleId = DB::table('vehicles')->insertGetId([
            'name' => 'Route Truck',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $otherVehicleId = DB::table('vehicles')->insertGetId([
            'name' => 'Spare Truck',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $routeId = DB::table('delivery_routes')->insertGetId([
            'name' => 'East Route',
            'vehicle_id' => $routeVehicleId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $mismatchResponse = $this->actingAs($admin)->post(route('admin.vehicle-schedule.store'), [
            'route_id' => $routeId,
            'vehicle_id' => $otherVehicleId,
            'scheduled_date' => '2026-03-20',
        ]);

        $mismatchResponse->assertSessionHasErrors('vehicle_id');
        $this->assertDatabaseCount('vehicle_schedules', 0);

        $createResponse = $this->actingAs($admin)->post(route('admin.vehicle-schedule.store'), [
            'route_id' => $routeId,
            'scheduled_date' => '2026-03-21',
        ]);

        $createResponse->assertSessionHasNoErrors();
        $this->assertDatabaseHas('vehicle_schedules', [
            'route_id' => $routeId,
            'vehicle_id' => $routeVehicleId,
            'scheduled_date' => '2026-03-21 00:00:00',
        ]);
    }

    public function test_stock_transfer_requires_destination_rules_and_preserves_location(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-transfer@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);

        $warehouseOne = DB::table('warehouses')->insertGetId([
            'name' => 'Source Warehouse',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $warehouseTwo = DB::table('warehouses')->insertGetId([
            'name' => 'Destination Warehouse',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $sourceLocationId = DB::table('warehouse_locations')->insertGetId([
            'warehouse_id' => $warehouseOne,
            'code' => 'SRC-01',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $destinationLocationId = DB::table('warehouse_locations')->insertGetId([
            'warehouse_id' => $warehouseTwo,
            'code' => 'DST-01',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $product = Product::create([
            'sku' => 'SKU-TRANSFER-1',
            'name' => 'Transfer Product',
            'product_type' => 'finished',
            'is_active' => true,
            'base_price' => 25,
        ]);

        $entry = StockEntry::create([
            'warehouse_id' => $warehouseOne,
            'warehouse_location_id' => $sourceLocationId,
            'product_id' => $product->id,
            'quantity' => 10,
            'status' => 'available',
        ]);

        $sameWarehouseResponse = $this->actingAs($admin)
            ->from(route('admin.stock.transfers'))
            ->post(route('admin.stock.transfers.store'), [
                'entry_id' => $entry->id,
                'destination_warehouse_id' => $warehouseOne,
                'destination_warehouse_location_id' => $sourceLocationId,
                'quantity' => 2,
            ]);

        $sameWarehouseResponse->assertSessionHasErrors('destination_warehouse_id');

        $missingLocationResponse = $this->actingAs($admin)
            ->from(route('admin.stock.transfers'))
            ->post(route('admin.stock.transfers.store'), [
                'entry_id' => $entry->id,
                'destination_warehouse_id' => $warehouseTwo,
                'quantity' => 4,
            ]);

        $missingLocationResponse->assertSessionHasErrors('destination_warehouse_location_id');

        $transferResponse = $this->actingAs($admin)->post(route('admin.stock.transfers.store'), [
            'entry_id' => $entry->id,
            'destination_warehouse_id' => $warehouseTwo,
            'destination_warehouse_location_id' => $destinationLocationId,
            'quantity' => 4,
            'notes' => 'Move to overflow storage',
        ]);

        $transferResponse->assertSessionHasNoErrors();
        $this->assertEquals(6.0, (float) $entry->fresh()->quantity);
        $this->assertDatabaseHas('stock_entries', [
            'warehouse_id' => $warehouseTwo,
            'warehouse_location_id' => $destinationLocationId,
            'product_id' => $product->id,
            'quantity' => 4,
            'status' => 'available',
        ]);
        $this->assertDatabaseCount('stock_movements', 2);
    }

    public function test_order_deletion_rejects_unlinked_legacy_reserved_stock(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-order-delete@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);

        $agent = Agent::create([
            'name' => 'Delete Agent',
            'credit_limit' => 10000,
            'withholding_rate' => 0,
            'is_active' => true,
        ]);

        $product = Product::create([
            'sku' => 'SKU-DELETE-1',
            'name' => 'Reserved Product',
            'product_type' => 'finished',
            'is_active' => true,
            'base_price' => 10,
        ]);

        $order = Order::create([
            'agent_id' => $agent->id,
            'order_type' => 'regular',
            'status' => 'confirmed',
            'total' => 0,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 5,
            'unit_price' => 10,
            'order_type' => 'regular',
            'commission_amount' => 0,
        ]);

        $legacyReservedEntry = StockEntry::create([
            'warehouse_id' => 1,
            'product_id' => $product->id,
            'quantity' => 5,
            'status' => 'reserved',
        ]);

        $response = $this->actingAs($admin)
            ->from(route('admin.orders.index'))
            ->delete(route('admin.orders.destroy', $order));

        $response->assertSessionHasErrors('order');
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
        ]);
        $this->assertDatabaseHas('stock_entries', [
            'id' => $legacyReservedEntry->id,
            'order_id' => null,
            'quantity' => 5,
            'status' => 'reserved',
        ]);
        $this->assertDatabaseMissing('stock_entries', [
            'product_id' => $product->id,
            'status' => 'available',
        ]);
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

        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('work_email')->nullable();
            $table->string('work_phone')->nullable();
            $table->string('work_mobile')->nullable();
            $table->string('department')->nullable();
            $table->string('job_position')->nullable();
            $table->string('work_zone')->nullable();
            $table->text('tags')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('cv_path')->nullable();
            $table->timestamps();
        });

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('role');
            $table->string('permission_name');
            $table->timestamps();
        });

        Schema::create('menu_groups', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('key')->nullable();
            $table->integer('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('menu_group_id')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('name');
            $table->string('key')->nullable();
            $table->string('icon')->nullable();
            $table->string('path')->nullable();
            $table->string('permission')->nullable();
            $table->integer('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('user_warehouse_scopes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('warehouse_id');
            $table->timestamps();
        });

        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('type')->nullable();
            $table->timestamps();
        });

        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('truck');
            $table->string('license_plate')->nullable();
            $table->string('driver')->nullable();
            $table->timestamps();
        });

        Schema::create('delivery_routes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('zone')->nullable();
            $table->string('day')->nullable();
            $table->unsignedBigInteger('vehicle_id')->nullable();
            $table->string('driver')->nullable();
            $table->timestamps();
        });

        Schema::create('vehicle_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vehicle_id');
            $table->unsignedBigInteger('route_id')->nullable();
            $table->date('scheduled_date');
            $table->string('driver')->nullable();
            $table->text('notes')->nullable();
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
            $table->string('product_type')->nullable();
            $table->unsignedBigInteger('tax_class_id')->nullable();
            $table->decimal('base_price', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('batch_code');
            $table->date('production_date');
            $table->date('expiry_date')->nullable();
            $table->string('qc_status')->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('warehouse_locations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('warehouse_id');
            $table->string('code');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('goods_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('warehouse_location_id')->nullable();
            $table->timestamps();
        });

        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('tax_id')->nullable();
            $table->timestamps();
        });

        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('platform');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->unsignedInteger('reach')->nullable();
            $table->unsignedInteger('impressions')->nullable();
            $table->decimal('cost', 14, 2)->nullable();
            $table->string('status')->default('planned');
            $table->string('campaign_code')->nullable();
            $table->string('attachment_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('customer_gifts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_id');
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->date('date');
            $table->string('occasion')->nullable();
            $table->string('gift_type')->nullable();
            $table->string('description')->nullable();
            $table->decimal('amount', 14, 2)->nullable();
            $table->string('campaign_code')->nullable();
            $table->string('status')->default('planned');
            $table->timestamps();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('category');
            $table->string('description')->nullable();
            $table->decimal('amount', 14, 2);
            $table->string('reference')->nullable();
            $table->string('status')->default('recorded');
            $table->timestamps();
        });

        Schema::create('salary_distributions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->date('period_start');
            $table->date('period_end')->nullable();
            $table->decimal('base_salary', 14, 2)->default(0);
            $table->decimal('bonus', 14, 2)->default(0);
            $table->decimal('ta_allowances', 14, 2)->default(0);
            $table->decimal('da_allowances', 14, 2)->default(0);
            $table->decimal('commission', 14, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('production_runs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->decimal('quantity', 14, 2)->default(0);
            $table->decimal('material_unit_cost', 14, 2)->nullable();
            $table->string('qc_status')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_id');
            $table->string('number')->unique();
            $table->date('order_date');
            $table->date('expected_date')->nullable();
            $table->string('status')->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_order_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('description');
            $table->decimal('quantity', 12, 2)->default(0);
            $table->decimal('unit_price', 12, 2)->nullable();
            $table->decimal('line_total', 14, 2)->nullable();
            $table->decimal('received_quantity', 12, 2)->default(0);
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

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stock_entry_id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('type');
            $table->decimal('quantity', 12, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('stock_audits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('warehouse_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('batch_id')->nullable();
            $table->decimal('system_quantity', 12, 2)->default(0);
            $table->decimal('counted_quantity', 12, 2)->default(0);
            $table->decimal('variance', 12, 2)->default(0);
            $table->text('notes')->nullable();
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

        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->unique();
            $table->unsignedBigInteger('route_id')->nullable();
            $table->unsignedBigInteger('vehicle_id')->nullable();
            $table->string('status')->default('scheduled');
            $table->integer('sequence')->nullable();
            $table->string('pod_photo')->nullable();
            $table->text('exception_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('delivery_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('delivery_id');
            $table->unsignedBigInteger('order_item_id')->nullable();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('batch_id')->nullable();
            $table->decimal('qty_dispatched', 12, 2)->default(0);
            $table->decimal('qty_delivered', 12, 2)->default(0);
            $table->decimal('qty_short', 12, 2)->default(0);
            $table->decimal('qty_damaged', 12, 2)->default(0);
            $table->text('notes')->nullable();
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
