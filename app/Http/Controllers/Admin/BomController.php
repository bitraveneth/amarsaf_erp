<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BillOfMaterial;
use App\Models\BomItem;
use App\Models\Product;
use App\Services\Manufacturing\ManufacturingNotificationService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BomController extends Controller
{
    public function index(Request $request)
    {
        $statusFilter = $request->query('status');
        $allowed = ['active', 'inactive'];

        $bomsQuery = BillOfMaterial::with(['product', 'items.component'])
            ->withCount('items')
            ->orderByDesc('id');

        if (in_array($statusFilter, $allowed, true)) {
            $bomsQuery->where('is_active', $statusFilter === 'active');
        } else {
            $statusFilter = null;
        }

        $boms = $bomsQuery->paginate(15)->withQueryString();

        $stats = [
            'total' => BillOfMaterial::count(),
            'active' => BillOfMaterial::where('is_active', true)->count(),
            'products' => BillOfMaterial::distinct('product_id')->count('product_id'),
            'components' => (int) BomItem::count(),
        ];

        return view('admin.boms.index', compact('boms', 'stats', 'statusFilter'));
    }

    public function show(BillOfMaterial $bom)
    {
        $bom->load(['product', 'items.component']);
        $bomUnitCost = $bom->computedMaterialUnitCost();

        return view('admin.boms.show', compact('bom', 'bomUnitCost'));
    }

    public function create(Request $request)
    {
        return view('admin.boms.create', $this->formViewData(null, $request));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, creating: true);
        $product = Product::findOrFail((int) $data['product_id']);
        $items = $this->normalizeItems($data['items']);

        if ($items->isEmpty()) {
            return back()->withInput()->withErrors(['items' => 'Add at least one component with a quantity greater than zero.']);
        }

        $this->validateBomItems((int) $data['product_id'], $items->all());

        $isDraft = $request->input('save_mode') === 'draft';
        $isActive = $isDraft ? false : $request->boolean('is_active');

        $defaultName = BillOfMaterial::activeForProduct((int) $product->id)
            ? BillOfMaterial::generateDefaultName($product, versioned: true)
            : BillOfMaterial::generateDefaultName($product);

        $bom = BillOfMaterial::create([
            'product_id' => $data['product_id'],
            'name' => filled($data['name'] ?? null) ? $data['name'] : $defaultName,
            'is_active' => $isActive,
            'notes' => $data['notes'] ?? null,
            'material_unit_cost' => $data['material_unit_cost'] ?? null,
        ]);

        $this->syncActiveBom($bom);
        $this->syncItems($bom, $items->all());

        app(ManufacturingNotificationService::class)->notifyBomCreated($bom->fresh(['product', 'items']), $isActive);

        $message = $isDraft
            ? 'BOM saved as draft. Activate it when the recipe is ready for production.'
            : 'BOM created and activated for production.';

        return redirect()->route('admin.boms.show', $bom)->with('status', $message);
    }

    public function edit(BillOfMaterial $bom)
    {
        $bom->load('items.component');

        return view('admin.boms.edit', array_merge(
            $this->formViewData($bom),
            compact('bom'),
        ));
    }

    public function update(Request $request, BillOfMaterial $bom)
    {
        $data = $this->validated($request, creating: false);
        $items = $this->normalizeItems($data['items']);
        $wasActive = (bool) $bom->is_active;

        if ($items->isEmpty()) {
            return back()->withInput()->withErrors(['items' => 'Add at least one component with a quantity greater than zero.']);
        }

        $this->validateBomItems((int) $bom->product_id, $items->all());

        $bom->update([
            'name' => filled($data['name'] ?? null) ? $data['name'] : BillOfMaterial::generateDefaultName($bom->product),
            'is_active' => $request->boolean('is_active'),
            'notes' => $data['notes'] ?? null,
            'material_unit_cost' => $data['material_unit_cost'] ?? null,
        ]);

        $this->syncActiveBom($bom);
        $this->syncItems($bom, $items->all());

        $bom->refresh();
        if (! $wasActive && $bom->is_active) {
            app(ManufacturingNotificationService::class)->notifyBomActivated($bom);
        }

        return redirect()->route('admin.boms.show', $bom)->with('status', 'BOM updated.');
    }

    public function destroy(BillOfMaterial $bom)
    {
        $bom->delete();

        return redirect()->route('admin.boms.index')->with('status', 'BOM deleted.');
    }

    protected function formViewData(?BillOfMaterial $bom = null, ?Request $request = null): array
    {
        $products = Product::where(function ($q) {
            $q->whereNull('product_type')->orWhere('product_type', 'finished');
        })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $materials = Product::whereIn('product_type', ['raw', 'service', 'inhouse'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $initialRows = old('items');
        if ($initialRows === null && $bom) {
            $initialRows = $bom->items->map(fn (BomItem $item) => [
                'component_product_id' => (string) $item->component_product_id,
                'quantity' => (float) $item->quantity,
                'unit_cost' => $item->unit_cost !== null ? (float) $item->unit_cost : '',
                'unit' => $item->unit ?: ($item->component?->uom ?? ''),
            ])->values()->all();
        }
        if (empty($initialRows)) {
            $initialRows = [['component_product_id' => '', 'quantity' => 1, 'unit_cost' => '', 'unit' => '']];
        }

        $prefillProductId = $request?->query('product_id');
        $selectedProductId = (string) old('product_id', $bom?->product_id ?? $prefillProductId ?? '');
        $selectedProduct = $products->firstWhere('id', (int) $selectedProductId);

        $hasActiveForProduct = $selectedProduct
            ? BillOfMaterial::activeForProduct((int) $selectedProduct->id) !== null
            : false;

        $defaultName = $selectedProduct
            ? BillOfMaterial::generateDefaultName($selectedProduct, versioned: $hasActiveForProduct)
            : BillOfMaterial::generateDefaultName(new Product(['sku' => 'SKU', 'name' => 'Product']));

        $activeBomsByProduct = BillOfMaterial::query()
            ->where('is_active', true)
            ->with(['product'])
            ->withCount('items')
            ->get()
            ->mapWithKeys(fn (BillOfMaterial $activeBom) => [
                (string) $activeBom->product_id => [
                    'id' => $activeBom->id,
                    'name' => $activeBom->displayName(),
                    'items_count' => $activeBom->items_count,
                    'show_url' => route('admin.boms.show', $activeBom),
                    'edit_url' => route('admin.boms.edit', $activeBom),
                ],
            ])
            ->all();

        $productRecipesByProduct = BillOfMaterial::query()
            ->with(['product', 'items.component'])
            ->has('items')
            ->orderByDesc('id')
            ->get()
            ->groupBy(fn (BillOfMaterial $bom) => (string) $bom->product_id)
            ->map(fn ($boms) => $boms->map(fn (BillOfMaterial $template) => [
                'id' => $template->id,
                'code' => 'BOM #' . $template->id,
                'name' => $template->displayName(),
                'is_active' => (bool) $template->is_active,
                'items_count' => $template->items->count(),
                'material_unit_cost' => $template->material_unit_cost,
                'updated_at' => $template->updated_at?->format('d M Y') ?? '',
                'show_url' => route('admin.boms.show', $template),
                'items' => $template->items->map(fn (BomItem $item) => [
                    'component_product_id' => (string) $item->component_product_id,
                    'component_sku' => $item->component?->sku ?? '',
                    'component_name' => $item->component?->name ?? '',
                    'component_type' => strtoupper($item->component?->product_type ?? 'raw'),
                    'quantity' => (float) $item->quantity,
                    'unit_cost' => $item->unit_cost !== null ? (float) $item->unit_cost : '',
                    'unit' => $item->unit ?: ($item->component?->uom ?? ''),
                ])->values()->all(),
            ])->values()->all())
            ->all();

        $prefillSourceBomId = $request?->query('from_bom');
        if ($prefillSourceBomId && $selectedProductId === '') {
            $sourceBom = BillOfMaterial::find($prefillSourceBomId);
            if ($sourceBom) {
                $selectedProductId = (string) $sourceBom->product_id;
            }
        }

        $referencedComponentIds = collect($productRecipesByProduct)
            ->flatten(1)
            ->pluck('items')
            ->flatten(1)
            ->pluck('component_product_id')
            ->merge(collect($initialRows)->pluck('component_product_id'))
            ->filter()
            ->unique()
            ->map(fn ($id) => (int) $id);

        $extraMaterials = Product::query()
            ->whereIn('id', $referencedComponentIds)
            ->whereNotIn('id', $materials->pluck('id'))
            ->orderBy('name')
            ->get();

        $materialsForForm = $materials->concat($extraMaterials)->sortBy('name')->values();

        return [
            'products' => $products,
            'materials' => $materialsForForm,
            'formState' => [
                'products' => $products->map(fn (Product $p) => [
                    'id' => $p->id,
                    'sku' => $p->sku,
                    'name' => $p->name,
                ])->values()->all(),
                'materials' => $materialsForForm->map(fn (Product $p) => [
                    'id' => $p->id,
                    'sku' => $p->sku,
                    'name' => $p->name,
                    'uom' => $p->uom ?: 'piece',
                    'standard_cost' => $p->standard_cost !== null ? (float) $p->standard_cost : null,
                    'type' => strtoupper($p->product_type ?? 'raw'),
                ])->values()->all(),
                'activeBomsByProduct' => $activeBomsByProduct,
                'productRecipesByProduct' => $productRecipesByProduct,
                'productBomCounts' => BillOfMaterial::query()
                    ->selectRaw('product_id, COUNT(*) as total')
                    ->groupBy('product_id')
                    ->pluck('total', 'product_id')
                    ->map(fn ($count) => (int) $count)
                    ->all(),
                'selectedProductId' => $selectedProductId,
                'bomName' => old('name', $bom?->name ?? $defaultName),
                'autoName' => old('auto_name', $bom ? '0' : '1') === '1',
                'notes' => old('notes', $bom?->notes ?? ''),
                'isActive' => (bool) old('is_active', $bom?->is_active ?? true),
                'materialUnitCost' => old('material_unit_cost', $bom?->material_unit_cost ?? ''),
                'lockProduct' => (bool) $bom,
                'materialSearch' => old('material_search', ''),
                'copyFromBomId' => old('copy_from_bom_id', $prefillSourceBomId ?? ''),
                'initialRows' => collect($initialRows)->map(fn ($row) => [
                    'component_product_id' => (string) ($row['component_product_id'] ?? ''),
                    'quantity' => $row['quantity'] ?? 1,
                    'unit_cost' => $row['unit_cost'] ?? '',
                    'unit' => $row['unit'] ?? '',
                ])->values()->all(),
                'currencyCode' => config('app.currency', 'BDT'),
            ],
        ];
    }

    protected function validated(Request $request, bool $creating): array
    {
        $rules = [
            'name' => 'nullable|string|max:255',
            'is_active' => 'sometimes|boolean',
            'notes' => 'nullable|string',
            'material_unit_cost' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.component_product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit_cost' => 'nullable|numeric|min:0',
            'items.*.unit' => 'nullable|string|max:50',
        ];

        if ($creating) {
            $rules['product_id'] = 'required|exists:products,id';
        }

        return $request->validate($rules);
    }

    protected function normalizeItems(array $items)
    {
        $components = Product::whereIn('id', collect($items)->pluck('component_product_id')->filter())
            ->get()
            ->keyBy('id');

        return collect($items)
            ->map(function ($item) use ($components) {
                $component = $components->get((int) $item['component_product_id']);

                return [
                    'component_product_id' => (int) $item['component_product_id'],
                    'quantity' => (float) $item['quantity'],
                    'unit_cost' => array_key_exists('unit_cost', $item) && $item['unit_cost'] !== '' && $item['unit_cost'] !== null
                        ? (float) $item['unit_cost']
                        : ($component?->standard_cost !== null ? (float) $component->standard_cost : null),
                    'unit' => filled($item['unit'] ?? null)
                        ? $item['unit']
                        : ($component?->uom ?: 'piece'),
                ];
            })
            ->filter(fn ($item) => $item['quantity'] > 0)
            ->values();
    }

    protected function syncActiveBom(BillOfMaterial $bom): void
    {
        if (! $bom->is_active) {
            return;
        }

        BillOfMaterial::where('product_id', $bom->product_id)
            ->where('id', '!=', $bom->id)
            ->update(['is_active' => false]);
    }

    protected function syncItems(BillOfMaterial $bom, array $items): void
    {
        $bom->items()->delete();

        foreach ($items as $item) {
            $bom->items()->create($item);
        }
    }

    protected function validateBomItems(int $productId, array $items): void
    {
        $components = Product::whereIn('id', collect($items)->pluck('component_product_id')->all())
            ->get()
            ->keyBy('id');

        foreach ($items as $index => $item) {
            $componentId = (int) $item['component_product_id'];
            $component = $components->get($componentId);

            if ($componentId === $productId) {
                throw ValidationException::withMessages([
                    'items.' . $index . '.component_product_id' => 'A BOM cannot use the finished product itself as a component.',
                ]);
            }

            if (! $component || ! in_array($component->product_type, ['raw', 'service', 'inhouse'], true)) {
                throw ValidationException::withMessages([
                    'items.' . $index . '.component_product_id' => 'BOM components must be active material products.',
                ]);
            }

            if (! $component->is_active) {
                throw ValidationException::withMessages([
                    'items.' . $index . '.component_product_id' => 'Inactive products cannot be used as BOM components.',
                ]);
            }
        }
    }
}
