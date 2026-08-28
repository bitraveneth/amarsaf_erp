<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\BillOfMaterial;
use App\Models\ProductionMaterialIssue;
use App\Models\ProductionMaterialIssueItem;
use App\Models\ProductionRun;
use App\Models\Product;
use App\Models\StockEntry;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Models\Employee;
use App\Services\Accounting\InventoryAccountingService;
use App\Services\Manufacturing\BatchFactory;
use App\Services\Manufacturing\ManufacturingFlow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductionController extends Controller
{
    public function index()
    {
        $warehouseIds = auth()->user()?->accessibleWarehouseIds();
        $runs = ProductionRun::with('product', 'batch', 'warehouse', 'supervisor', 'approver')
            ->when($warehouseIds !== null, function ($query) use ($warehouseIds) {
                $query->whereIn('warehouse_id', $warehouseIds);
            })
            ->latest()
            ->paginate(10);

        $today = now()->toDateString();
        $todayRuns = ProductionRun::whereDate('created_at', $today)->get();

        $byLineShift = $todayRuns
            ->groupBy(fn ($run) => ($run->line ?: 'Unassigned') . '|' . ($run->shift ?: 'All'))
            ->map(function ($group) {
                return $group->sum('quantity');
            });

        // Capacity map keyed in a normalised "line|shift" (lowercase) form so that
        // free-text input like "Line 1" / "morning" still matches.
        $lineCapacities = [
            'line 1|morning' => 50000,
            'line 1|evening' => 50000,
            'line 2|morning' => 40000,
            'line 2|evening' => 40000,
        ];

        return view('admin.production.index', compact('runs', 'byLineShift', 'lineCapacities', 'today'));
    }

    /**
     * List QC-approved production runs that have not yet been confirmed to stock.
     * This is mainly for the warehouse manager to process goods receipts.
     */
    public function pendingReceipts()
    {
        $warehouseIds = auth()->user()?->accessibleWarehouseIds();
        $runs = ProductionRun::with(['product', 'batch', 'warehouse', 'approver'])
            ->whereIn('qc_status', ['approved', 'partial'])
            ->whereNull('stock_confirmed_at')
            ->when($warehouseIds !== null, function ($query) use ($warehouseIds) {
                $query->whereIn('warehouse_id', $warehouseIds);
            })
            ->latest()
            ->get();

        return view('admin.production.pending_receipts', compact('runs'));
    }

    public function create(Request $request)
    {
        $warehouseIds = auth()->user()?->accessibleWarehouseIds();
        // Only finished products should be selectable for production runs
        $products = Product::where(function ($q) {
                $q->whereNull('product_type')
                    ->orWhere('product_type', 'finished');
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Pre-compute estimated material unit cost per finished unit for each product
        $productUnitCosts = [];
        $productIds = $products->pluck('id')->all();

        if (!empty($productIds)) {
            $boms = BillOfMaterial::whereIn('product_id', $productIds)
                ->where('is_active', true)
                ->with(['items.component'])
                ->orderByDesc('id')
                ->get()
                ->keyBy('product_id');

            foreach ($products as $product) {
                $bom = $boms->get($product->id);
                if (! $bom || $bom->items->isEmpty()) {
                    continue;
                }

                $unitCost = null;

                if ($bom->material_unit_cost !== null && $bom->material_unit_cost > 0) {
                    // Explicit override on BOM
                    $unitCost = (float) $bom->material_unit_cost;
                } else {
                    // Derive from components
                    $accumulator = 0.0;
                    foreach ($bom->items as $item) {
                        $component = $item->component;
                        $baseCost = null;
                        if ($item->unit_cost !== null) {
                            $baseCost = (float) $item->unit_cost;
                        } elseif ($component && $component->standard_cost !== null) {
                            $baseCost = (float) $component->standard_cost;
                        }
                        if ($baseCost !== null) {
                            $accumulator += $baseCost * (float) $item->quantity;
                        }
                    }
                    if ($accumulator > 0) {
                        $unitCost = $accumulator;
                    }
                }

                if ($unitCost !== null) {
                    $productUnitCosts[$product->id] = $unitCost;
                }
            }
        }
        $batches = Batch::orderBy('production_date', 'desc')->get();
        $warehouses = Warehouse::query()
            ->when($warehouseIds !== null, function ($query) use ($warehouseIds) {
                $query->whereIn('id', $warehouseIds);
            })
            ->orderBy('name')
            ->get();

        // Choose a sensible default warehouse for production. We prefer any
        // warehouse marked as "factory"; if none exists, we leave it null and
        // let the select fall back to the placeholder.
        $defaultWarehouseId = optional(
            $warehouses->firstWhere('type', 'factory')
        )->id;

        $employees = Employee::orderBy('name')->get();

        // Pre-compute required materials per product and warehouse stock
        $materialRequirements = [];
        $warehouseStock = [];

        $productIds = $products->pluck('id')->all();
        if (! empty($productIds)) {
            $boms = BillOfMaterial::whereIn('product_id', $productIds)
                ->where('is_active', true)
                ->with(['items.component'])
                ->orderByDesc('id')
                ->get()
                ->keyBy('product_id');

            foreach ($products as $product) {
                $bom = $boms->get($product->id);
                if (! $bom || $bom->items->isEmpty()) {
                    continue;
                }

                $components = [];
                foreach ($bom->items as $item) {
                    if (! $item->component_product_id || ! $item->quantity) {
                        continue;
                    }
                    $components[] = [
                        'product_id' => $item->component_product_id,
                        'name'       => $item->component?->name,
                        'sku'        => $item->component?->sku,
                        'uom'        => $item->component?->uom,
                        'type'       => $item->component?->product_type,
                        'quantity_per_unit' => (float) $item->quantity,
                    ];
                }

                if (! empty($components)) {
                    $materialRequirements[$product->id] = $components;
                }
            }

            // Simple stock snapshot per warehouse+product
            $stockEntries = StockEntry::selectRaw('warehouse_id, product_id, SUM(quantity) as qty')
                ->where('status', 'available')
                ->when($warehouseIds !== null, function ($query) use ($warehouseIds) {
                    $query->whereIn('warehouse_id', $warehouseIds);
                })
                ->groupBy('warehouse_id', 'product_id')
                ->get();

            foreach ($stockEntries as $entry) {
                $warehouseStock[$entry->warehouse_id][$entry->product_id] = (float) $entry->qty;
            }
        }

        $bomCatalog = empty($productIds) ? [] : BillOfMaterial::query()
            ->whereIn('product_id', $productIds)
            ->where('is_active', true)
            ->withCount('items')
            ->orderByDesc('id')
            ->get()
            ->unique('product_id')
            ->mapWithKeys(fn (BillOfMaterial $bom) => [
                (string) $bom->product_id => [
                    'id' => $bom->id,
                    'name' => $bom->displayName(),
                    'items_count' => $bom->items_count,
                    'show_url' => route('admin.boms.show', $bom),
                    'edit_url' => route('admin.boms.edit', $bom),
                    'create_url' => route('admin.boms.create', ['product_id' => $bom->product_id]),
                ],
            ])
            ->all();

        $prefillProductId = $request->query('product_id');
        $repeatFromRun = null;
        $prefill = [];

        if ($request->filled('repeat')) {
            $repeatFromRun = ProductionRun::with(['product', 'batch'])
                ->when($warehouseIds !== null, function ($query) use ($warehouseIds) {
                    $query->whereIn('warehouse_id', $warehouseIds);
                })
                ->find($request->query('repeat'));

            if ($repeatFromRun) {
                $prefill = [
                    'product_id' => (string) $repeatFromRun->product_id,
                    'quantity' => (int) $repeatFromRun->quantity,
                    'warehouse_id' => $repeatFromRun->warehouse_id,
                    'line' => $repeatFromRun->line ?: 'Line 1',
                    'shift' => $repeatFromRun->shift ?: 'Morning',
                    'supervisor_id' => $repeatFromRun->supervisor_id,
                    'status' => 'confirmed',
                    'notes' => $repeatFromRun->notes,
                    'materials_reserved' => $repeatFromRun->materials_reserved,
                ];
                $prefillProductId = $prefill['product_id'];
            }
        }

        $productSkus = $products->mapWithKeys(fn (Product $product) => [
            (string) $product->id => $product->sku ?: ('P' . $product->id),
        ])->all();

        $openBatchesByProduct = Batch::query()
            ->whereIn('product_id', $productIds)
            ->where('qc_status', 'pending')
            ->orderByDesc('production_date')
            ->get()
            ->groupBy(fn (Batch $batch) => (string) $batch->product_id)
            ->map(fn ($group) => $group->take(10)->map(fn (Batch $batch) => [
                'id' => $batch->id,
                'code' => $batch->batch_code,
                'production_date' => $batch->production_date?->format('d M Y') ?? '',
            ])->values()->all())
            ->all();

        $previousRunsByProduct = empty($productIds) ? [] : ProductionRun::query()
            ->with(['batch', 'warehouse', 'supervisor'])
            ->when($warehouseIds !== null, function ($query) use ($warehouseIds) {
                $query->whereIn('warehouse_id', $warehouseIds);
            })
            ->whereIn('product_id', $productIds)
            ->where('status', '!=', 'cancelled')
            ->orderByDesc('id')
            ->get()
            ->groupBy(fn (ProductionRun $run) => (string) $run->product_id)
            ->map(fn ($runs) => $runs->take(20)->map(fn (ProductionRun $run) => [
                'id' => $run->id,
                'code' => $run->order_number ?: ('Run #' . $run->id),
                'quantity' => (int) $run->quantity,
                'line' => $run->line ?: 'Line 1',
                'shift' => $run->shift ?: 'Morning',
                'warehouse_id' => $run->warehouse_id,
                'warehouse_name' => $run->warehouse?->name ?? '',
                'supervisor_id' => $run->supervisor_id,
                'supervisor_name' => $run->supervisor?->name ?? '',
                'batch_code' => $run->batch?->batch_code ?? '',
                'status' => $run->status,
                'qc_status' => $run->qc_status ?? '',
                'notes' => $run->notes ?? '',
                'materials_reserved' => $run->materials_reserved ?? '',
                'material_unit_cost' => $run->material_unit_cost !== null ? (float) $run->material_unit_cost : null,
                'material_total_cost' => $run->material_total_cost !== null ? (float) $run->material_total_cost : null,
                'created_at' => $run->created_at?->format('d M Y') ?? '',
                'show_url' => route('admin.production.show', $run),
            ])->values()->all())
            ->all();

        $formState = [
            'products' => $products->map(fn (Product $product) => [
                'id' => $product->id,
                'sku' => $product->sku,
                'name' => $product->name,
            ])->values()->all(),
            'previousRunsByProduct' => $previousRunsByProduct,
            'materialRequirements' => collect($materialRequirements)
                ->mapWithKeys(fn ($components, $productId) => [(string) $productId => $components])
                ->all(),
            'productUnitCosts' => collect($productUnitCosts)
                ->mapWithKeys(fn ($cost, $productId) => [(string) $productId => $cost])
                ->all(),
            'productSkus' => $productSkus,
            'bomCatalog' => $bomCatalog,
            'defaultWarehouseId' => $defaultWarehouseId,
            'selectedProductId' => (string) old('product_id', $prefillProductId ?? ''),
            'quantity' => old('quantity', $prefill['quantity'] ?? ''),
            'warehouseId' => old('warehouse_id', $prefill['warehouse_id'] ?? $defaultWarehouseId),
            'line' => old('line', $prefill['line'] ?? 'Line 1'),
            'shift' => old('shift', $prefill['shift'] ?? 'Morning'),
            'supervisorId' => old('supervisor_id', $prefill['supervisor_id'] ?? ''),
            'status' => old('status', $prefill['status'] ?? 'confirmed'),
            'notes' => old('notes', $prefill['notes'] ?? ''),
            'materialsReserved' => old('materials_reserved', $prefill['materials_reserved'] ?? ''),
            'orderNumber' => old('order_number', $prefill['order_number'] ?? ''),
            'initialRepeatRunId' => $repeatFromRun?->id,
            'appliedRunId' => $repeatFromRun ? (string) $repeatFromRun->id : '',
            'previewRunId' => $repeatFromRun ? (string) $repeatFromRun->id : '',
            'useExistingBatch' => old('batch_mode') === 'existing',
            'currencyCode' => config('app.currency', 'BDT'),
        ];

        // Build widget payload in PHP — inline @json([ ... route() ... ]) breaks Blade (unclosed '[').
        // Bit raven — https://github.com/bitraveneth
        $productionWidgets = [
            'productUnitCosts' => $productUnitCosts,
            'materialRequirements' => $materialRequirements,
            'warehouseStock' => $warehouseStock,
            'warehouseNames' => $warehouses->pluck('name', 'id'),
            'bomCatalog' => $bomCatalog,
            'openBatchesByProduct' => $openBatchesByProduct,
            'defaultWarehouseId' => $defaultWarehouseId ?? '',
            'oldBatchId' => old('batch_id'),
            'bomCreateUrl' => route('admin.boms.create'),
        ];

        return view('admin.production.create', [
            'products'           => $products,
            'batches'            => $batches,
            'warehouses'         => $warehouses,
            'employees'          => $employees,
            'defaultWarehouseId' => $defaultWarehouseId,
            'productUnitCosts'   => $productUnitCosts,
            'materialRequirements' => $materialRequirements,
            'warehouseStock'       => $warehouseStock,
            'bomCatalog'           => $bomCatalog,
            'prefillProductId'     => $prefillProductId,
            'prefill'              => $prefill,
            'repeatFromRun'        => $repeatFromRun,
            'productSkus'          => $productSkus,
            'openBatchesByProduct' => $openBatchesByProduct,
            'formState'            => $formState,
            'productionWidgets'    => $productionWidgets,
        ]);
    }

    public function edit(ProductionRun $production)
    {
        $this->ensureWarehouseAccess($production->warehouse_id);
        $production->load('product', 'batch', 'warehouse');
        return view('admin.production.edit', ['run' => $production]);
    }

    public function show(ProductionRun $production)
    {
        $this->ensureWarehouseAccess($production->warehouse_id);
        $production->load('product', 'batch', 'warehouse', 'supervisor', 'approver', 'stockConfirmer', 'materialIssues.items.component', 'materialIssues.issuer');

        // Load the active BOM for this product (if any) so the view can show
        // a simple summary of components required for this run and an
        // estimated material cost based on component standard_cost.
        $bom = BillOfMaterial::where('product_id', $production->product_id)
            ->where('is_active', true)
            ->orderByDesc('id')
            ->with(['items.component'])
            ->first();

        $estimatedUnitCost = null;
        $estimatedTotalCost = null;

        if ($bom && $bom->items->isNotEmpty()) {
            $unitCostAccumulator = 0.0;

            foreach ($bom->items as $item) {
                $component = $item->component;
                $componentUnitCost = 0.0;

                if ($component && $component->standard_cost !== null) {
                    // Standard cost is per 1 unit of the component; multiply by
                    // quantity required for a single finished unit.
                    $componentUnitCost = (float) $component->standard_cost * (float) $item->quantity;
                }

                // Attach helper attributes so the Blade view can display a
                // per-component cost breakdown without re-doing the maths.
                $item->calculated_unit_cost = $componentUnitCost;
                $item->calculated_total_cost = $componentUnitCost * (float) $production->quantity;

                $unitCostAccumulator += $componentUnitCost;
            }

            $estimatedUnitCost = $unitCostAccumulator;
            $estimatedTotalCost = $estimatedUnitCost * (float) $production->quantity;
        }

        // If we already have a persisted costing snapshot, prefer that for the
        // header summary while still using the BOM-derived breakdown table.
        if (! is_null($production->material_unit_cost)) {
            $estimatedUnitCost = (float) $production->material_unit_cost;
        }
        if (! is_null($production->material_total_cost)) {
            $estimatedTotalCost = (float) $production->material_total_cost;
        }

        // If stock has been confirmed we can try to locate the finished-goods
        // stock entry so that the UI can offer a quick write-off shortcut.
        $stockEntry = null;
        if ($production->stock_confirmed_at && $production->warehouse_id) {
            $stockEntry = StockEntry::where('warehouse_id', $production->warehouse_id)
                ->where('product_id', $production->product_id)
                ->where('batch_id', $production->batch_id)
                ->orderByDesc('updated_at')
                ->first();
        }

        return view('admin.production.show', [
            'run'                => $production,
            'bom'                => $bom,
            'stockEntry'         => $stockEntry,
            'estimatedUnitCost'  => $estimatedUnitCost,
            'estimatedTotalCost' => $estimatedTotalCost,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'order_number' => 'nullable|string|max:255',
            'product_id' => 'required|exists:products,id',
            'batch_id' => 'nullable|exists:batches,id',
            'batch_mode' => 'nullable|in:auto,existing',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'line' => 'nullable|string',
            'shift' => 'nullable|string',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
            'status' => 'nullable|string|max:50',
            'supervisor_id' => 'nullable|exists:employees,id',
            'materials_reserved' => 'nullable|string',
        ]);

        $this->ensureWarehouseAccess($data['warehouse_id'] ?? null);

        $product = Product::findOrFail($data['product_id']);

        if (! ManufacturingFlow::productHasActiveBom((int) $product->id)) {
            throw ValidationException::withMessages([
                'product_id' => 'This product has no active BOM. Set up and activate a recipe first.',
            ]);
        }

        $batchMode = $data['batch_mode'] ?? 'auto';
        unset($data['batch_mode']);

        if ($batchMode === 'existing' && ! empty($data['batch_id'])) {
            $batch = Batch::findOrFail($data['batch_id']);
            if ((int) $batch->product_id !== (int) $product->id) {
                throw ValidationException::withMessages([
                    'batch_id' => 'Selected batch does not belong to this product.',
                ]);
            }
        } else {
            $batch = BatchFactory::createForProduction($product);
            $data['batch_id'] = $batch->id;
        }

        // Auto-generate production order number if not provided
        if (empty($data['order_number'])) {
            $data['order_number'] = $this->generateOrderNumber($batch);
        }

        // New runs always start as pending; QC officer will approve later
        if (! isset($data['status'])) {
            $data['status'] = 'confirmed';
        }
        $data['qc_status'] = 'pending';
        $run = ProductionRun::create($data);

        return redirect()
            ->route('admin.production.show', $run)
            ->with('status', 'Production run recorded. Batch ' . ($batch->batch_code ?? '') . ' created — complete QC then post to stock.');
    }

    public function update(Request $request, ProductionRun $production)
    {
        $this->ensureWarehouseAccess($production->warehouse_id);
        $user = auth()->user();

        $rules = [
            'line' => 'nullable|string',
            'shift' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'nullable|string|max:50',
            'supervisor_id' => 'nullable|exists:employees,id',
            'materials_reserved' => 'nullable|string',
        ];

        // Only admin/super admin and QC officer can change QC status
        if ($user?->hasAnyRole(['admin', 'super_admin', 'qc_officer'])) {
            $rules['qc_status'] = 'required|in:pending,approved,rejected,partial';
            $rules['qc_passed_quantity'] = 'nullable|integer|min:0|max:' . max(0, (int) $production->quantity);
            $rules['qc_rejected_quantity'] = 'nullable|integer|min:0|max:' . max(0, (int) $production->quantity);
            $rules['qc_notes'] = 'nullable|string|max:2000';
        }

        $data = $request->validate($rules);

        if (array_key_exists('qc_status', $data)) {
            $data = $this->normalizeQcPayload($data, $production);
        }

        $previousQcStatus = $production->qc_status;

        $production->update($data);

        // If QC just moved to approved for the first time, stamp approver + time
        if (array_key_exists('qc_status', $data)
            && $previousQcStatus !== 'approved'
            && in_array($production->qc_status, ['approved', 'partial'], true)) {
            if (! $production->approved_at) {
                $production->approved_at = now();
            }

            if (! $production->approved_by && auth()->check()) {
                $production->approved_by = auth()->id();
            }

            $production->save();
        }

        return redirect()->route('admin.production.index')->with('status', 'Production run updated.');
    }

    public function submitQcReview(Request $request, ProductionRun $production)
    {
        $this->ensureWarehouseAccess($production->warehouse_id);

        if ($production->stock_confirmed_at) {
            return redirect()
                ->route('admin.production.show', $production)
                ->withErrors(['qc' => 'Stock is already confirmed for this run. QC cannot be changed.']);
        }

        $user = auth()->user();
        if (! $user?->hasAnyRole(['admin', 'super_admin', 'qc_officer'])) {
            abort(403, 'Only QC staff can submit batch quality reviews.');
        }

        $data = $request->validate([
            'qc_decision' => 'required|in:approve_all,partial,reject_all',
            'qc_passed_quantity' => 'nullable|integer|min:0|max:' . max(0, (int) $production->quantity),
            'qc_rejected_quantity' => 'nullable|integer|min:0|max:' . max(0, (int) $production->quantity),
            'qc_notes' => 'nullable|string|max:2000',
            'rejected_disposition' => 'nullable|in:scrap,rework,hold',
        ]);

        $payload = match ($data['qc_decision']) {
            'approve_all' => [
                'qc_status' => 'approved',
                'qc_passed_quantity' => (int) $production->quantity,
                'qc_rejected_quantity' => 0,
            ],
            'reject_all' => [
                'qc_status' => 'rejected',
                'qc_passed_quantity' => 0,
                'qc_rejected_quantity' => (int) $production->quantity,
            ],
            'partial' => [
                'qc_status' => 'partial',
                'qc_passed_quantity' => (int) ($data['qc_passed_quantity'] ?? 0),
                'qc_rejected_quantity' => (int) ($data['qc_rejected_quantity'] ?? 0),
            ],
        };

        $passed = (int) $payload['qc_passed_quantity'];
        $rejected = (int) $payload['qc_rejected_quantity'];

        if ($payload['qc_status'] === 'partial' && ($passed + $rejected !== (int) $production->quantity)) {
            throw ValidationException::withMessages([
                'qc_passed_quantity' => 'Passed and rejected quantities must add up to the run quantity (' . (int) $production->quantity . ').',
            ]);
        }

        if ($payload['qc_status'] === 'partial' && $passed <= 0) {
            throw ValidationException::withMessages([
                'qc_passed_quantity' => 'Enter how many units passed QC, or choose Reject all instead.',
            ]);
        }

        $notes = trim((string) ($data['qc_notes'] ?? ''));
        if (! empty($data['rejected_disposition']) && $rejected > 0) {
            $notes = trim($notes . ' Rejected units disposition: ' . $data['rejected_disposition'] . '.');
        }

        $payload['qc_notes'] = $notes !== '' ? $notes : null;
        $previousQcStatus = $production->qc_status;

        $production->update($payload);

        if ($previousQcStatus !== $production->qc_status
            && in_array($production->qc_status, ['approved', 'partial'], true)) {
            $production->approved_at = now();
            $production->approved_by = auth()->id();
            $production->save();
        }

        if ($production->batch && in_array($production->qc_status, ['approved', 'partial', 'rejected'], true)) {
            $batchStatus = match ($production->qc_status) {
                'approved' => 'approved',
                'partial' => 'approved',
                'rejected' => 'rejected',
                default => $production->batch->qc_status,
            };

            $production->batch->update([
                'qc_status' => $batchStatus,
                'notes' => trim(($production->batch->notes ? $production->batch->notes . ' ' : '') . ($payload['qc_notes'] ?? '')),
            ]);
        }

        return redirect()
            ->route('admin.production.show', $production)
            ->with('status', 'QC review saved. ' . number_format($passed) . ' units cleared for stock, ' . number_format($rejected) . ' rejected.');
    }

    protected function normalizeQcPayload(array $data, ProductionRun $production): array
    {
        $quantity = max(0, (int) $production->quantity);

        if (($data['qc_status'] ?? '') === 'approved') {
            $data['qc_passed_quantity'] = $quantity;
            $data['qc_rejected_quantity'] = 0;
        } elseif (($data['qc_status'] ?? '') === 'rejected') {
            $data['qc_passed_quantity'] = 0;
            $data['qc_rejected_quantity'] = $quantity;
        } elseif (($data['qc_status'] ?? '') === 'partial') {
            $passed = (int) ($data['qc_passed_quantity'] ?? 0);
            $rejected = (int) ($data['qc_rejected_quantity'] ?? max(0, $quantity - $passed));

            if ($passed + $rejected !== $quantity) {
                throw ValidationException::withMessages([
                    'qc_passed_quantity' => 'Passed and rejected quantities must add up to ' . $quantity . '.',
                ]);
            }

            $data['qc_passed_quantity'] = $passed;
            $data['qc_rejected_quantity'] = $rejected;
        } else {
            $data['qc_passed_quantity'] = null;
            $data['qc_rejected_quantity'] = null;
        }

        return $data;
    }

    public function destroy(ProductionRun $production)
    {
        $this->ensureWarehouseAccess($production->warehouse_id);

        if ($production->stock_confirmed_at || $production->materialIssues()->exists()) {
            return redirect()
                ->route('admin.production.index')
                ->withErrors([
                    'production' => 'This production run has already posted inventory activity and cannot be deleted.',
                ]);
        }

        $production->delete();

        return redirect()->route('admin.production.index')->with('status', 'Production run deleted.');
    }

    /**
     * Confirm that stock from a QC-approved production run has been received
     * into the selected warehouse. Only admin / warehouse officer should do this.
     */
    public function confirmStock(Request $request, ProductionRun $production)
    {
        $this->ensureWarehouseAccess($production->warehouse_id);
        $user = auth()->user();

        if (! $user?->hasAnyRole(['admin', 'super_admin', 'warehouse_officer'])) {
            return redirect()->route('admin.production.index')
                ->with('status', 'Only admin or warehouse officer can confirm stock.');
        }

        if (! $production->isQcReadyForStock()) {
            return redirect()->route('admin.production.index')
                ->with('status', 'QC must approve sellable quantity before confirming stock.');
        }

        if ($production->stock_confirmed_at) {
            return redirect()->route('admin.production.index')
                ->with('error', 'Stock already confirmed for this production run.');
        }

        DB::transaction(function () use ($production) {
            $this->postStockForApprovedRun($production);

            $production->stock_confirmed_at = now();
            if (auth()->check()) {
                $production->stock_confirmed_by = auth()->id();
            }

            if (! in_array($production->status, ['cancelled'], true)) {
                $production->status = 'completed';
            }

            $production->save();

            app(InventoryAccountingService::class)->postProductionRun($production->fresh());
        });

        return redirect()->route('admin.production.index')
            ->with('status', 'Stock confirmed and posted to warehouse.');
    }

    protected function ensureWarehouseAccess($warehouseId): void
    {
        if ($warehouseId === null) {
            return;
        }

        if (! auth()->user()?->canAccessWarehouse((int) $warehouseId)) {
            abort(403, 'You do not have access to this warehouse.');
        }
    }

    /**
     * Once a production run is QC approved, post finished goods stock and consume
     * BOM components from the selected warehouse.
     */
    protected function postStockForApprovedRun(ProductionRun $run): void
    {
        if ($run->quantity <= 0 || ! $run->warehouse_id) {
            return;
        }

        $sellableQty = $run->sellableQuantity();
        if ($sellableQty <= 0) {
            return;
        }

        // Consume raw materials based on active BOM, if any
        $bom = BillOfMaterial::where('product_id', $run->product_id)
            ->where('is_active', true)
            ->orderByDesc('id')
            ->with(['items.component'])
            ->first();

        if (! $bom || $bom->items->isEmpty()) {
            $entry = StockEntry::create([
                'warehouse_id' => $run->warehouse_id,
                'product_id' => $run->product_id,
                'batch_id' => $run->batch_id,
                'quantity' => $sellableQty,
                'status' => 'available',
            ]);

            StockMovement::recordFor(
                $entry,
                'production-output',
                (float) $sellableQty,
                'Confirmed from production run ' . ($run->order_number ?? ('#' . $run->id))
            );

            return;
        }

        $requirements = [];
        foreach ($bom->items as $item) {
            $component = $item->component;
            $totalRequired = (float) $item->quantity * (float) $run->quantity;

            if ($totalRequired <= 0) {
                continue;
            }

            if (! $component || ! $component->isStockTracked()) {
                throw ValidationException::withMessages([
                    'materials' => ['The active BOM contains a non-stock or missing component and cannot be confirmed to stock.'],
                ]);
            }

            if ((int) $component->id === (int) $run->product_id) {
                throw ValidationException::withMessages([
                    'materials' => ['The active BOM contains the same product as both finished good and component.'],
                ]);
            }

            $entries = StockEntry::where('warehouse_id', $run->warehouse_id)
                ->where('product_id', $item->component_product_id)
                ->where('status', 'available')
                ->orderBy('created_at')
                ->lockForUpdate()
                ->get();

            $available = (float) $entries->sum('quantity');
            if ($available < $totalRequired) {
                $warehouseName = $run->warehouse?->name
                    ?? Warehouse::query()->whereKey($run->warehouse_id)->value('name')
                    ?? ('Warehouse #' . $run->warehouse_id);
                $uom = $component?->uom ? ' ' . $component->uom : '';

                throw ValidationException::withMessages([
                    'materials' => [
                        'Insufficient available stock for component '
                        . ($component->name ?? ('#' . $item->component_product_id))
                        . ' in '
                        . $warehouseName
                        . '. Required '
                        . number_format($totalRequired, 2)
                        . $uom
                        . ', available '
                        . number_format($available, 2)
                        . $uom
                        . '. Receive or transfer more stock before confirming production.',
                    ],
                ]);
            }

            $requirements[] = [
                'bom_item' => $item,
                'component' => $component,
                'entries' => $entries,
                'required' => $totalRequired,
            ];
        }

        $materialIssue = ProductionMaterialIssue::create([
            'production_run_id' => $run->id,
            'warehouse_id' => $run->warehouse_id,
            'issued_by' => auth()->id(),
            'issued_at' => now(),
            'notes' => 'Auto-issued during stock confirmation for production order ' . ($run->order_number ?? ('#' . $run->id)),
        ]);

        // If BOM has an explicit override material_unit_cost, use that directly.
        if ($bom->material_unit_cost !== null && $bom->material_unit_cost > 0) {
            $unitCostAccumulator = (float) $bom->material_unit_cost;
        } else {
            $unitCostAccumulator = 0.0;
        }

        foreach ($requirements as $requirement) {
            $item = $requirement['bom_item'];
            $component = $requirement['component'];
            $remaining = $requirement['required'];

            foreach ($requirement['entries'] as $entry) {
                if ($remaining <= 0) {
                    break;
                }

                $consume = min($remaining, (float) $entry->quantity);
                if ($consume <= 0) {
                    continue;
                }

                $entry->quantity = (float) $entry->quantity - $consume;
                if ((float) $entry->quantity <= 0) {
                    $entry->quantity = 0;
                    $entry->status = 'sold'; // treated as consumed in production
                }
                $entry->save();

                StockMovement::recordFor(
                    $entry,
                    'production-consumption',
                    $consume * -1,
                    'Consumed for production run ' . ($run->order_number ?? ('#' . $run->id))
                );

                $baseCost = null;
                if ($item->unit_cost !== null) {
                    $baseCost = (float) $item->unit_cost;
                } elseif ($component && $component->standard_cost !== null) {
                    $baseCost = (float) $component->standard_cost;
                }

                ProductionMaterialIssueItem::create([
                    'production_material_issue_id' => $materialIssue->id,
                    'component_product_id' => $item->component_product_id,
                    'batch_id' => $entry->batch_id,
                    'quantity' => $consume,
                    'unit_cost' => $baseCost,
                    'line_total' => $baseCost !== null ? ($consume * $baseCost) : null,
                ]);

                $remaining -= $consume;
            }

            if ($remaining > 0.00001) {
                throw ValidationException::withMessages([
                    'materials' => ['Unable to consume all required stock for component ' . ($component->name ?? ('#' . $item->component_product_id)) . '.'],
                ]);
            }

            // If BOM-level override not set, accumulate from components
            if ($bom->material_unit_cost === null || $bom->material_unit_cost <= 0) {
                // Cost contribution from this component for ONE finished unit
                // Prefer BOM-level custom unit_cost if provided; fall back to product standard_cost
                $baseCost = null;
                if ($item->unit_cost !== null) {
                    $baseCost = (float) $item->unit_cost;
                } elseif ($component && $component->standard_cost !== null) {
                    $baseCost = (float) $component->standard_cost;
                }
                if ($baseCost !== null) {
                    $unitCostAccumulator += $baseCost * (float) $item->quantity;
                }
            }
        }

        $entry = StockEntry::create([
            'warehouse_id' => $run->warehouse_id,
            'product_id' => $run->product_id,
            'batch_id' => $run->batch_id,
            'quantity' => $sellableQty,
            'status' => 'available',
        ]);

        StockMovement::recordFor(
            $entry,
            'production-output',
            (float) $sellableQty,
            'Confirmed from production run ' . ($run->order_number ?? ('#' . $run->id))
            . ($run->qc_rejected_quantity ? ' (' . (int) $run->qc_rejected_quantity . ' units rejected at QC)' : '')
        );

        // Persist material cost snapshot on the production run so that future
        // reports / COGS calculations can use a stable value.
        if ($unitCostAccumulator > 0) {
            $run->material_unit_cost = $unitCostAccumulator;
            $run->material_total_cost = $unitCostAccumulator * (float) $run->quantity;
            $run->save();
        }
    }

    /**
     * HTTP endpoint: generate a new production order number for the given batch.
     * Used by the "Generate" button on the create form.
     */
    public function orderNumber(Request $request)
    {
        $batch = null;
        if ($request->filled('batch_id')) {
            $batch = Batch::find($request->input('batch_id'));
        }

        $orderNumber = $this->generateOrderNumber($batch);

        return response()->json(['order_number' => $orderNumber]);
    }

    /**
     * Generate a simple production order number like PO-YYYYMMDD-001
     * based on the batch production date (or today if not set).
     */
    protected function generateOrderNumber(?Batch $batch): string
    {
        $date = $batch && $batch->production_date
            ? $batch->production_date
            : now()->toDateString();

        $dateKey = \Illuminate\Support\Carbon::parse($date)->format('Ymd');
        $prefix = "PO-{$dateKey}-";

        $lastOrder = ProductionRun::whereDate('created_at', $date)
            ->whereNotNull('order_number')
            ->where('order_number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('order_number');

        $nextSeq = 1;
        if ($lastOrder && preg_match('/(\d+)$/', $lastOrder, $m)) {
            $nextSeq = ((int) $m[1]) + 1;
        }

        $suffix = str_pad((string) $nextSeq, 3, '0', STR_PAD_LEFT);

        return $prefix.$suffix;
    }
}
