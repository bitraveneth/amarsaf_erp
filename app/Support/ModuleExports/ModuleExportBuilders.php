<?php

namespace App\Support\ModuleExports;

use App\Models\Account;
use App\Models\Agent;
use App\Models\AgentAdvance;
use App\Models\AgentCommissionSettlement;
use App\Models\Badge;
use App\Models\Batch;
use App\Models\BillOfMaterial;
use App\Models\Campaign;
use App\Models\CustomerGift;
use App\Models\Delivery;
use App\Models\DeliveryRoute;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\FiscalYear;
use App\Models\GoodsReceipt;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\Order;
use App\Models\PackagingType;
use App\Models\Product;
use App\Models\ProductionRun;
use App\Models\PurchaseBill;
use App\Models\PurchaseOrder;
use App\Models\SalaryDistribution;
use App\Models\SalesTarget;
use App\Models\StockEntry;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\TaxClass;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Warehouse;
use App\Models\WebhookEndpoint;
use App\Services\MrpService;
use App\Support\ExportDateRange;
use App\Support\CommissionCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ModuleExportBuilders
{
    protected static function applyDateRange($query, Request $request, ?string $column)
    {
        if (! $column) {
            return $query;
        }

        return ExportDateRange::apply($query, $request, $column);
    }

    public static function products(Request $request): Collection
    {
        $search = $request->query('q');

        return Product::with(['packagingType', 'taxClass'])
            ->latest()
            ->where(fn ($q) => $q->whereNull('product_type')->orWhere('product_type', 'finished'))
            ->when($search, function ($q) use ($search) {
                $term = '%' . $search . '%';
                $q->where(fn ($inner) => $inner
                    ->where('sku', 'like', $term)
                    ->orWhere('name', 'like', $term)
                    ->orWhere('barcode', 'like', $term));
            })
            ->get()
            ->map(fn ($product) => [
                $product->sku,
                $product->name,
                ucfirst($product->product_type ?? 'finished'),
                $product->packagingType?->name ?? '—',
                ($product->taxClass?->rate ?? 0) . '%',
                number_format((float) ($product->base_price ?? 0), 2),
                ($product->is_active ?? true) ? 'Active' : 'Inactive',
            ]);
    }

    public static function materials(Request $request): Collection
    {
        $search = $request->query('q');

        return Product::whereIn('product_type', ['raw', 'service', 'inhouse'])
            ->with('materialCategory')
            ->orderBy('name')
            ->when($search, function ($q) use ($search) {
                $term = '%' . $search . '%';
                $q->where(fn ($inner) => $inner
                    ->where('sku', 'like', $term)
                    ->orWhere('name', 'like', $term)
                    ->orWhere('supplier_name', 'like', $term));
            })
            ->get()
            ->map(fn ($material) => [
                $material->sku,
                $material->name,
                $material->materialCategory?->name ?? '—',
                ucfirst($material->product_type ?? 'raw'),
                $material->uom ? ucfirst($material->uom) : '—',
                number_format((float) ($material->standard_cost ?? 0), 2),
                $material->supplier_name ?? '—',
                ($material->is_active ?? true) ? 'Active' : 'Inactive',
            ]);
    }

    public static function agents(Request $request): Collection
    {
        return Agent::with(['parent', 'commissions'])
            ->orderBy('name')
            ->get()
            ->map(function ($agent) {
                $rule = $agent->commissions->first();
                $commission = $rule
                    ? ($rule->type === 'percentage'
                        ? rtrim(rtrim(number_format((float) $rule->value, 2), '0'), '.') . '%'
                        : 'BDT ' . number_format((float) $rule->value, 2))
                    : '—';

                return [
                    $agent->name,
                    $agent->location_code ?? '—',
                    trim(($agent->area ?? '') . ($agent->zone ? ', ' . $agent->zone : ''), ', ') ?: '—',
                    $agent->parent?->name ?? '—',
                    number_format((float) ($agent->credit_limit ?? 0), 2),
                    ($agent->is_active ?? true) ? 'Active' : 'Suspended',
                    $commission,
                ];
            });
    }

    public static function orders(Request $request): Collection
    {
        $orderTypeFilter = $request->query('type', 'all');
        if (! in_array($orderTypeFilter, ['all', 'sales', 'return'], true)) {
            $orderTypeFilter = 'all';
        }

        $query = Order::with('agent')
            ->when($orderTypeFilter === 'sales', fn ($q) => $q->where('order_type', '!=', 'return'))
            ->when($orderTypeFilter === 'return', fn ($q) => $q->where('order_type', 'return'));

        self::applyDateRange($query, $request, 'created_at');

        return $query->latest()
            ->get()
            ->map(fn ($order) => [
                '#' . $order->id,
                $order->agent?->name ?? '—',
                $order->order_type === 'return' ? 'Customer return' : ($order->order_type ?? 'regular'),
                $order->delivery_date?->format('d M Y') ?? 'TBD',
                ucfirst(str_replace('_', ' ', $order->status ?? '')),
                number_format((float) ($order->total ?? 0), 2),
                number_format((float) ($order->commission_total ?? 0), 2),
            ]);
    }

    public static function suppliers(Request $request): Collection
    {
        return Supplier::orderBy('name')
            ->get()
            ->map(fn ($supplier) => [
                $supplier->name,
                $supplier->contact_person ?? '—',
                $supplier->phone ?? '—',
                $supplier->tax_id ?? '—',
            ]);
    }

    public static function warehouses(Request $request): Collection
    {
        $warehouseIds = auth()->user()?->accessibleWarehouseIds();

        return Warehouse::withCount('entries')
            ->when($warehouseIds !== null, fn ($q) => $q->whereIn('id', $warehouseIds))
            ->orderBy('name')
            ->get()
            ->map(fn ($warehouse) => [
                $warehouse->name,
                $warehouse->type ?? 'Depot',
                $warehouse->code ?? 'N/A',
                $warehouse->entries_count ?? 0,
                $warehouse->capacity ? number_format((float) $warehouse->capacity) : '—',
                $warehouse->address ?? '—',
                ($warehouse->is_active ?? true) ? 'Active' : 'Inactive',
            ]);
    }

    public static function employees(Request $request): Collection
    {
        return Employee::orderBy('name')
            ->get()
            ->map(fn ($employee) => [
                $employee->name,
                $employee->employee_id ?? '—',
                $employee->department ?? '—',
                $employee->job_position ?? '—',
                $employee->work_email ?? '—',
                $employee->work_zone ?? '—',
            ]);
    }

    public static function deliveries(Request $request): Collection
    {
        $query = Delivery::with(['order.agent', 'route', 'vehicle', 'pod']);

        self::applyDateRange($query, $request, 'created_at');

        return $query->orderByDesc('created_at')
            ->get()
            ->map(fn ($delivery) => [
                $delivery->sequence ?? '—',
                '#' . $delivery->order_id,
                $delivery->order?->agent?->name ?? '—',
                $delivery->route?->name ?? '—',
                $delivery->vehicle?->name ?? '—',
                ucfirst($delivery->order?->status ?? '—'),
                ucfirst(str_replace('_', ' ', $delivery->status ?? '')),
                $delivery->pod_photo ? 'Uploaded' : 'Pending',
            ]);
    }

    public static function invoices(Request $request): Collection
    {
        $query = Invoice::with(['order.agent', 'receipts']);

        self::applyDateRange($query, $request, 'issued_at');

        return $query->latest()
            ->get()
            ->map(fn ($invoice) => [
                $invoice->number,
                $invoice->issued_at?->format('d M Y') ?? '—',
                $invoice->order_id ? '#' . $invoice->order_id : '—',
                $invoice->order?->agent?->name ?? '—',
                number_format((float) $invoice->net_total, 2),
                number_format((float) $invoice->vat_amount, 2),
                number_format((float) $invoice->withholding, 2),
                number_format((float) $invoice->receipts->sum('amount'), 2),
                ucfirst($invoice->status ?? ''),
            ]);
    }

    public static function bills(Request $request): Collection
    {
        $query = PurchaseBill::with(['supplier', 'payments']);

        self::applyDateRange($query, $request, 'bill_date');

        return $query->latest('bill_date')
            ->get()
            ->map(fn ($bill) => [
                $bill->number,
                $bill->supplier?->name ?? '—',
                $bill->bill_date?->format('d M Y') ?? '—',
                number_format((float) $bill->net_total + (float) $bill->vat_amount, 2),
                ucfirst($bill->status ?? ''),
                number_format((float) $bill->payments->sum('amount'), 2),
            ]);
    }

    public static function production(Request $request): Collection
    {
        $warehouseIds = auth()->user()?->accessibleWarehouseIds();

        $query = ProductionRun::with(['product', 'batch', 'approver'])
            ->when($warehouseIds !== null, fn ($q) => $q->whereIn('warehouse_id', $warehouseIds));

        self::applyDateRange($query, $request, 'created_at');

        return $query->latest()
            ->get()
            ->map(fn ($run) => [
                $run->product?->name ?? '—',
                $run->batch?->batch_code ?? '—',
                $run->line ?: 'Unassigned',
                $run->shift ?: 'All',
                $run->quantity,
                $run->order_number ?? '—',
                ucfirst($run->status ?? '—'),
                ucfirst($run->qc_status ?? '—'),
                $run->approver?->name ?? '—',
                $run->stock_confirmed_at ? 'Confirmed' : 'Pending',
            ]);
    }

    public static function inventory(Request $request): Collection
    {
        $warehouseIds = auth()->user()?->accessibleWarehouseIds();
        $today = Carbon::today();

        return StockEntry::with(['product', 'batch', 'warehouse'])
            ->when($warehouseIds !== null, fn ($q) => $q->whereIn('warehouse_id', $warehouseIds))
            ->where('status', 'available')
            ->where('quantity', '>', 0)
            ->whereNotNull('batch_id')
            ->whereHas('batch', fn ($q) => $q->where('expiry_date', '<=', $today->copy()->addDays(30)))
            ->orderBy('batch_id')
            ->get()
            ->map(fn ($entry) => [
                $entry->product?->name ?? '—',
                $entry->batch?->batch_code ?? '—',
                $entry->batch?->expiry_date?->format('d M Y') ?? '—',
                $entry->warehouse?->name ?? '—',
                $entry->quantity,
            ]);
    }

    public static function lowStock(Request $request): Collection
    {
        $warehouseIds = auth()->user()?->accessibleWarehouseIds();

        $availableByProduct = StockEntry::query()
            ->selectRaw('product_id, SUM(quantity) as total')
            ->when($warehouseIds !== null, fn ($q) => $q->whereIn('warehouse_id', $warehouseIds))
            ->where('status', 'available')
            ->groupBy('product_id')
            ->pluck('total', 'product_id');

        return Product::stockTracked()
            ->where('is_active', true)
            ->whereNotNull('reorder_level')
            ->orderBy('name')
            ->get()
            ->map(function ($product) use ($availableByProduct) {
                $available = (float) ($availableByProduct[$product->id] ?? 0);
                $reorderLevel = (float) $product->reorder_level;
                $shortage = max(0.0, $reorderLevel - $available);

                return compact('product', 'available', 'reorderLevel', 'shortage');
            })
            ->filter(fn ($row) => $row['shortage'] > 0)
            ->sortByDesc('shortage')
            ->values()
            ->map(fn ($row) => [
                $row['product']->name,
                $row['product']->sku,
                $row['available'],
                $row['reorderLevel'],
                $row['shortage'],
            ]);
    }

    public static function boms(Request $request): Collection
    {
        return BillOfMaterial::with(['product', 'items.component'])
            ->orderByDesc('id')
            ->get()
            ->map(function ($bom) {
                $unitCost = $bom->material_unit_cost;
                if (is_null($unitCost)) {
                    $accumulated = $bom->items->sum(fn ($item) => is_null($item->unit_cost)
                        ? 0
                        : (float) $item->unit_cost * (float) $item->quantity);
                    $unitCost = $accumulated > 0 ? $accumulated : null;
                }

                return [
                    $bom->product?->name ?? '—',
                    $bom->product?->sku ?? '—',
                    $bom->name ?: 'Default',
                    $bom->items->count(),
                    is_null($unitCost) ? '—' : number_format((float) $unitCost, 2),
                    $bom->is_active ? 'Active' : 'Inactive',
                ];
            });
    }

    public static function batches(Request $request): Collection
    {
        $warehouseIds = auth()->user()?->accessibleWarehouseIds();

        return Batch::with('product')
            ->when($warehouseIds !== null, fn ($q) => $q->where(
                fn ($batchQuery) => $batchQuery
                    ->whereHas('stockEntries', fn ($sq) => $sq->whereIn('warehouse_id', $warehouseIds))
                    ->orWhereHas('productionRuns', fn ($rq) => $rq->whereIn('warehouse_id', $warehouseIds))
                    ->orWhere(fn ($uq) => $uq->whereDoesntHave('stockEntries')->whereDoesntHave('productionRuns'))
            ))
            ->latest('production_date')
            ->get()
            ->map(fn ($batch) => [
                $batch->product?->name ?? '—',
                $batch->batch_code,
                $batch->production_date?->format('d M Y') ?? '—',
                $batch->expiry_date?->format('d M Y') ?? '—',
                ucfirst($batch->qc_status ?? '—'),
            ]);
    }

    public static function purchaseOrders(Request $request): Collection
    {
        $query = PurchaseOrder::with(['supplier', 'items']);

        self::applyDateRange($query, $request, 'order_date');

        return $query->latest('order_date')
            ->get()
            ->map(fn ($order) => [
                $order->number,
                $order->supplier?->name ?? '—',
                $order->order_date?->format('d M Y') ?? '—',
                str_replace('_', ' ', ucfirst($order->status ?? '')),
                $order->items->count(),
            ]);
    }

    public static function goodsReceipts(Request $request): Collection
    {
        $warehouseIds = auth()->user()?->accessibleWarehouseIds();

        $query = GoodsReceipt::with(['supplier', 'warehouse'])
            ->when($warehouseIds !== null, fn ($q) => $q->whereIn('warehouse_id', $warehouseIds));

        self::applyDateRange($query, $request, 'received_at');

        return $query->latest('received_at')
            ->get()
            ->map(fn ($receipt) => [
                $receipt->number,
                $receipt->supplier?->name ?? '—',
                $receipt->warehouse?->name ?? '—',
                $receipt->received_at?->format('d M Y H:i') ?? '—',
                ucfirst(str_replace('_', ' ', $receipt->status ?? '')),
            ]);
    }

    public static function stockMovements(Request $request): Collection
    {
        $warehouseIds = auth()->user()?->accessibleWarehouseIds();

        $query = StockMovement::with(['stockEntry.product', 'stockEntry.warehouse', 'order'])
            ->when($warehouseIds !== null, fn ($q) => $q->whereHas(
                'stockEntry',
                fn ($sq) => $sq->whereIn('warehouse_id', $warehouseIds)
            ));

        self::applyDateRange($query, $request, 'created_at');

        return $query->latest()
            ->get()
            ->map(fn ($movement) => [
                $movement->stockEntry?->product?->name ?? '—',
                $movement->stockEntry?->warehouse?->name ?? '—',
                $movement->type,
                $movement->quantity,
                $movement->order_id ? 'Order #' . $movement->order_id : ($movement->notes ?? '—'),
                $movement->created_at?->format('d M Y H:i') ?? '—',
            ]);
    }

    public static function expenses(Request $request): Collection
    {
        $query = Expense::query();

        if ($request->query('range') || $request->filled('from') || $request->filled('to')) {
            self::applyDateRange($query, $request, 'date');
        } else {
            $query->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()]);
        }

        if ($category = $request->query('category')) {
            $query->where('category', $category);
        }

        return $query->orderByDesc('date')
            ->get()
            ->map(fn ($expense) => [
                $expense->date?->format('d M Y') ?? '—',
                $expense->category,
                $expense->description ?? '—',
                number_format((float) $expense->amount, 2),
                $expense->reference ?? '—',
                ucfirst($expense->status ?? ''),
            ]);
    }

    public static function accounts(Request $request): Collection
    {
        $selectedType = $request->query('type');
        $validTypes = ['asset', 'liability', 'equity', 'income', 'expense'];
        if (! in_array($selectedType, $validTypes, true)) {
            $selectedType = null;
        }

        return Account::query()
            ->when($selectedType, fn ($q) => $q->where('type', $selectedType))
            ->orderBy('code')
            ->get()
            ->map(fn ($account) => [
                $account->code,
                $account->name,
                ucfirst($account->type ?? ''),
                $account->is_active ? 'Active' : 'Inactive',
                $account->updated_at?->format('d M Y') ?? '—',
            ]);
    }

    public static function journals(Request $request): Collection
    {
        $query = JournalEntry::with(['lines.account']);

        if ($request->query('range') || $request->filled('from') || $request->filled('to')) {
            self::applyDateRange($query, $request, 'entry_date');
        } else {
            $query->whereBetween('entry_date', [
                now()->startOfMonth()->toDateString(),
                now()->endOfMonth()->toDateString(),
            ]);
        }

        return $query
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->query('type'), fn ($q, $type) => $q->where('journal_type', $type))
            ->latest('entry_date')
            ->latest('id')
            ->get()
            ->map(fn ($entry) => [
                $entry->number,
                $entry->entry_date->format('d M Y'),
                str_replace('_', ' ', $entry->journal_type ?? ''),
                $entry->description ?? '—',
                number_format((float) $entry->totalDebit(), 2),
                number_format((float) $entry->totalCredit(), 2),
                ucfirst($entry->status ?? ''),
            ]);
    }

    public static function salesTargets(Request $request): Collection
    {
        $query = SalesTarget::with(['employee', 'agent']);

        if ($request->query('range') || $request->filled('from') || $request->filled('to')) {
            self::applyDateRange($query, $request, 'period_start');
        } else {
            $month = $request->query('month')
                ? Carbon::parse($request->query('month') . '-01')->startOfMonth()
                : now()->startOfMonth();
            $query->whereDate('period_start', '<=', $month->copy()->endOfMonth()->toDateString())
                ->whereDate('period_end', '>=', $month->copy()->startOfMonth()->toDateString());
        }

        return $query->orderByDesc('period_start')
            ->get()
            ->map(fn ($target) => [
                $target->agent?->name ?? $target->employee?->name ?? '—',
                $target->agent_id ? 'Agent' : 'Employee',
                $target->period_start->format('d M Y'),
                $target->period_end->format('d M Y'),
                number_format((float) $target->target_value, 2),
            ]);
    }

    public static function commissions(Request $request): Collection
    {
        $month = $request->query('month')
            ? Carbon::parse($request->query('month') . '-01')
            : Carbon::now()->startOfMonth();
        $from = $month->copy()->startOfMonth();
        $to = $month->copy()->endOfMonth();
        $calculator = app(CommissionCalculator::class);

        $agents = Agent::query()
            ->where(function ($query) use ($from, $to) {
                $query->whereHas('orders', function ($orderQuery) use ($from, $to) {
                    $orderQuery->where('status', 'delivered')
                        ->whereHas('invoice', function ($invoiceQuery) use ($from, $to) {
                            $invoiceQuery
                                ->whereDate('issued_at', '>=', $from->toDateString())
                                ->whereDate('issued_at', '<=', $to->toDateString());
                        });
                })->orWhereHas('commissions', function ($commissionQuery) {
                    $commissionQuery->where('frequency', 'monthly');
                });
            })
            ->orderBy('name')
            ->get();

        $rows = collect();

        foreach ($agents as $agent) {
            $summary = $calculator->buildMonthlySummaryForAgent($agent, $from, $to);
            $sales = (float) ($summary['sales'] ?? 0);
            $commission = (float) ($summary['commission'] ?? 0);

            if ($sales <= 0 && $commission <= 0) {
                continue;
            }

            $rate = $sales > 0 ? round(($commission / $sales) * 100, 2) : 0;

            $rows->push([
                $agent->id,
                $agent->name,
                number_format($sales, 2, '.', ''),
                number_format($commission, 2, '.', ''),
                number_format($rate, 2, '.', ''),
            ]);
        }

        return $rows;
    }

    public static function settlements(Request $request): Collection
    {
        $query = AgentCommissionSettlement::with('agent');

        if ($request->query('range') || $request->filled('from') || $request->filled('to')) {
            self::applyDateRange($query, $request, 'period_start');
        } else {
            $month = $request->query('month')
                ? Carbon::parse($request->query('month') . '-01')
                : Carbon::now()->startOfMonth();
            $query->whereBetween('period_start', [
                $month->copy()->startOfMonth(),
                $month->copy()->endOfMonth(),
            ]);
        }

        return $query->orderBy('agent_id')
            ->get()
            ->map(function ($settlement) {
                $rate = (float) $settlement->sales_total > 0
                    ? ((float) $settlement->commission_total / (float) $settlement->sales_total) * 100
                    : 0;

                return [
                    $settlement->agent?->name ?? '—',
                    number_format((float) $settlement->sales_total, 2),
                    number_format((float) $settlement->commission_total, 2),
                    number_format($rate, 2),
                    ucfirst($settlement->status ?? ''),
                ];
            });
    }

    public static function agentAdvances(Request $request): Collection
    {
        $query = AgentAdvance::with('agent')
            ->when($request->query('agent_id'), fn ($q, $id) => $q->where('agent_id', $id));

        self::applyDateRange($query, $request, 'advanced_at');

        return $query->orderByDesc('advanced_at')
            ->get()
            ->map(fn ($advance) => [
                $advance->agent?->name ?? '—',
                $advance->advanced_at?->format('d M Y') ?? '—',
                number_format((float) $advance->amount, 2),
                number_format((float) $advance->applied_amount, 2),
                number_format(max(0, (float) $advance->amount - (float) $advance->applied_amount), 2),
                $advance->payment_method ?? '—',
                ucfirst($advance->status ?? ''),
            ]);
    }

    public static function vehicles(Request $request): Collection
    {
        return Vehicle::orderBy('name')
            ->get()
            ->map(fn ($vehicle) => [
                $vehicle->name,
                $vehicle->type ?? '—',
                $vehicle->license_plate ?? '—',
                $vehicle->driver ?? '—',
                $vehicle->capacity_crates ?? '—',
                $vehicle->is_active ? 'Yes' : 'No',
            ]);
    }

    public static function packaging(Request $request): Collection
    {
        return PackagingType::orderBy('name')
            ->get()
            ->map(fn ($packaging) => [
                $packaging->name,
                $packaging->unit ?? '—',
                $packaging->description ?? '—',
                $packaging->updated_at?->format('d M Y') ?? '—',
            ]);
    }

    public static function taxClasses(Request $request): Collection
    {
        return TaxClass::orderBy('name')
            ->get()
            ->map(fn ($taxClass) => [
                $taxClass->name,
                ($taxClass->rate ?? 0) . '%',
                $taxClass->hsn_sac ?? '—',
                $taxClass->updated_at?->format('d M Y') ?? '—',
            ]);
    }

    public static function users(Request $request): Collection
    {
        $filterRole = $request->input('role');
        $search = $request->input('q');

        return User::query()
            ->with('userRoles')
            ->withCount([
                'userPermissions as permission_overrides_count',
                'warehouseScopes as warehouse_scopes_count',
            ])
            ->orderBy('name')
            ->when($filterRole, fn ($q) => $q->where(
                fn ($inner) => $inner->where('role', $filterRole)
                    ->orWhereHas('userRoles', fn ($rq) => $rq->where('role_key', $filterRole))
            ))
            ->when($search, fn ($q) => $q->where(
                fn ($inner) => $inner->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
            ))
            ->get()
            ->map(fn ($user) => [
                $user->name,
                $user->email,
                $user->role ?? $user->userRoles->pluck('role_key')->join(', ') ?: '—',
                trim(($user->permission_overrides_count ?? 0) . ' overrides, ' . ($user->warehouse_scopes_count ?? 0) . ' warehouse scopes'),
                $user->employee?->name ?? '—',
            ]);
    }

    public static function webhooks(Request $request): Collection
    {
        return WebhookEndpoint::orderBy('name')
            ->get()
            ->map(fn ($webhook) => [
                $webhook->name,
                $webhook->url,
                is_array($webhook->events) ? implode(', ', $webhook->events) : ($webhook->events ?? '—'),
                $webhook->is_active ? 'Yes' : 'No',
            ]);
    }

    public static function campaigns(Request $request): Collection
    {
        $query = Campaign::query();

        self::applyDateRange($query, $request, 'start_date');

        return $query->orderByDesc('start_date')
            ->get()
            ->map(fn ($campaign) => [
                $campaign->name,
                $campaign->platform ?? '—',
                $campaign->start_date?->format('d M Y') ?? '—',
                $campaign->end_date?->format('d M Y') ?? '—',
                $campaign->reach ?? 0,
                $campaign->impressions ?? 0,
                number_format((float) ($campaign->cost ?? 0), 2),
                ucfirst($campaign->status ?? ''),
            ]);
    }

    public static function gifts(Request $request): Collection
    {
        $query = CustomerGift::with(['agent', 'employee']);

        if ($request->query('range') || $request->filled('from') || $request->filled('to')) {
            self::applyDateRange($query, $request, 'date');
        } else {
            $query->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()]);
        }

        if ($agentId = $request->query('agent_id')) {
            $query->where('agent_id', $agentId);
        }

        return $query->orderByDesc('date')
            ->get()
            ->map(fn ($gift) => [
                $gift->date?->format('d M Y') ?? '—',
                $gift->agent?->name ?? '—',
                $gift->employee?->name ?? '—',
                $gift->occasion ?? '—',
                $gift->gift_description ?? '—',
                number_format((float) ($gift->amount ?? 0), 2),
                $gift->campaign_name ?? '—',
                ucfirst($gift->status ?? ''),
            ]);
    }

    public static function customerReturns(Request $request): Collection
    {
        $query = StockMovement::with(['stockEntry.product', 'stockEntry.warehouse', 'order.agent'])
            ->where('type', 'customer-return');

        self::applyDateRange($query, $request, 'created_at');

        return $query->latest()
            ->get()
            ->map(fn ($movement) => [
                $movement->created_at?->format('d M Y') ?? '—',
                $movement->order_id ? '#' . $movement->order_id : '—',
                $movement->order?->agent?->name ?? '—',
                $movement->stockEntry?->product?->name ?? '—',
                $movement->stockEntry?->warehouse?->name ?? '—',
                $movement->quantity,
                $movement->notes ?? '—',
            ]);
    }

    public static function supplierReturns(Request $request): Collection
    {
        $warehouseIds = auth()->user()?->accessibleWarehouseIds();

        $query = StockMovement::with(['stockEntry.product', 'stockEntry.warehouse'])
            ->when($warehouseIds !== null, fn ($q) => $q->whereHas(
                'stockEntry',
                fn ($sq) => $sq->whereIn('warehouse_id', $warehouseIds)
            ))
            ->where('type', 'supplier-return');

        self::applyDateRange($query, $request, 'created_at');

        return $query->latest()
            ->get()
            ->map(fn ($movement) => [
                $movement->created_at?->format('d M Y') ?? '—',
                $movement->stockEntry?->product?->name ?? '—',
                $movement->stockEntry?->warehouse?->name ?? '—',
                abs((float) $movement->quantity),
                $movement->notes ?? '—',
            ]);
    }

    public static function accountingPeriods(Request $request): Collection
    {
        app(\App\Services\Accounting\FiscalPeriodService::class)->ensureCurrentYear();

        $fiscalYears = FiscalYear::with('periods')->orderByDesc('start_date')->get();
        $selectedYear = $request->query('year')
            ? $fiscalYears->firstWhere('name', $request->query('year'))
            : $fiscalYears->first();
        $periods = $selectedYear?->periods ?? collect();

        return $periods->map(fn ($period) => [
            $selectedYear->name,
            $period->name,
            $period->start_date->format('d M Y'),
            $period->end_date->format('d M Y'),
            $period->is_closed ? 'Closed' : 'Open',
        ]);
    }

    public static function salaryDistributions(Request $request): Collection
    {
        $query = SalaryDistribution::with('employee');

        if ($request->query('range') || $request->filled('from') || $request->filled('to')) {
            self::applyDateRange($query, $request, 'period_start');
        } else {
            $month = $request->query('month')
                ? Carbon::parse($request->query('month') . '-01')
                : now()->startOfMonth();
            $query->whereBetween('period_start', [
                $month->copy()->startOfMonth(),
                $month->copy()->endOfMonth(),
            ]);
        }

        if ($employeeId = $request->query('employee_id')) {
            $query->where('employee_id', $employeeId);
        }

        return $query->orderBy('employee_id')
            ->get()
            ->map(function ($row) {
                $total = (float) $row->base_salary
                    + (float) $row->bonus
                    + (float) $row->ta_allowances
                    + (float) $row->da_allowances
                    + (float) $row->commission;

                return [
                    $row->employee?->name ?? '—',
                    $row->period_start?->format('d M Y') ?? '—',
                    $row->period_end?->format('d M Y') ?? '—',
                    number_format((float) $row->base_salary, 2),
                    number_format((float) $row->bonus, 2),
                    number_format((float) $row->ta_allowances, 2),
                    number_format((float) $row->da_allowances, 2),
                    number_format((float) $row->commission, 2),
                    $row->payment_method ?? 'bank',
                    filled($row->document_path) ? 'Yes' : 'No',
                    number_format($total, 2),
                ];
            });
    }

    public static function warehouseLocations(Request $request): Collection
    {
        $warehouseIds = auth()->user()?->accessibleWarehouseIds();

        return Warehouse::with(['locations' => fn ($q) => $q->orderBy('code')])
            ->when($warehouseIds !== null, fn ($q) => $q->whereIn('id', $warehouseIds))
            ->orderBy('name')
            ->get()
            ->flatMap(fn ($warehouse) => $warehouse->locations->map(fn ($location) => [
                $warehouse->name,
                $location->code,
                $location->description ?? '—',
            ]));
    }

    public static function deliveryRoutes(Request $request): Collection
    {
        return DeliveryRoute::with('vehicle')
            ->orderBy('name')
            ->get()
            ->map(fn ($route) => [
                $route->name,
                $route->zone ?? '—',
                $route->day ?? '—',
                $route->vehicle?->name ?? '—',
                $route->driver ?? '—',
            ]);
    }

    public static function badges(Request $request): Collection
    {
        return Badge::orderBy('name')
            ->get()
            ->map(fn ($badge) => [
                $badge->name,
                $badge->code ?? '—',
                $badge->color ?? '—',
                $badge->is_active ? 'Active' : 'Inactive',
                $badge->description ?? '—',
            ]);
    }

    public static function mrp(Request $request): Collection
    {
        return app(MrpService::class)
            ->suggestions()
            ->map(fn ($row) => [
                $row['product']->name,
                $row['product']->sku,
                $row['available'],
                $row['reorder_level'],
                $row['suggested_qty'],
            ]);
    }
}
