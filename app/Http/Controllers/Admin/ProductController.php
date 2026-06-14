<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgentPriceList;
use App\Models\BomItem;
use App\Models\DeliveryItem;
use App\Models\GoodsReceiptItem;
use App\Models\InvoiceItem;
use App\Models\OrderItem;
use App\Models\Batch;
use App\Models\BillOfMaterial;
use App\Models\ProductionMaterialIssueItem;
use App\Models\StockEntry;
use App\Models\ProductionRun;
use App\Models\PurchaseBillItem;
use App\Models\PurchaseOrderItem;
use App\Models\MaterialCategory;
use App\Models\PackagingType;
use App\Models\Product;
use App\Models\TaxClass;
use App\Models\UnitOfMeasure;
use App\Support\RawMaterialLineCatalog;
use App\Support\SkuGenerator;
use App\Support\WaterProductLineCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    protected function packagingTypesForSelect()
    {
        return PackagingType::query()->orderedForSelect()->get();
    }

    /**
     * @return array{names: array<int, string>, presets: array<int, array<string, mixed>>}
     */
    protected function productNameOptionsForForm(?Product $exclude = null): array
    {
        $presets = WaterProductLineCatalog::namePresetsForForm();
        $presetNames = collect($presets)->pluck('name');

        $query = Product::query()->catalogFinishedGoods();
        if ($exclude) {
            $query->where('id', '!=', $exclude->id);
        }

        $extraNames = $query
            ->whereNotIn('name', $presetNames)
            ->distinct()
            ->orderBy('name')
            ->pluck('name');

        return [
            'presets' => $presets,
            'names' => $presetNames->merge($extraNames)->unique()->sort()->values()->all(),
        ];
    }

    public function index()
    {
        $search = request('q');
        $filter = request('filter');

        $baseQuery = Product::query()
            ->where(function ($q) {
                $q->whereNull('product_type')
                    ->orWhere('product_type', 'finished');
            });

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'active' => (clone $baseQuery)->where('is_active', true)->count(),
            'missing_barcode' => (clone $baseQuery)->where(function ($q) {
                $q->whereNull('barcode')->orWhere('barcode', '');
            })->count(),
            'incomplete_specs' => (clone $baseQuery)->where(function ($q) {
                $q->whereNull('volume_ml')
                    ->orWhereNull('uom')
                    ->orWhere('uom', '');
            })->count(),
        ];

        $query = (clone $baseQuery);

        if ($search) {
            $term = '%' . $search . '%';
            $query->where(function ($inner) use ($term) {
                $inner->where('sku', 'like', $term)
                    ->orWhere('name', 'like', $term)
                    ->orWhere('barcode', 'like', $term)
                    ->orWhere('brand', 'like', $term);
            });
        }

        if ($filter === 'active') {
            $query->where('is_active', true);
        } elseif ($filter === 'inactive') {
            $query->where('is_active', false);
        } elseif ($filter === 'missing_barcode') {
            $query->where(function ($q) {
                $q->whereNull('barcode')->orWhere('barcode', '');
            });
        } elseif ($filter === 'incomplete_specs') {
            $query->where(function ($q) {
                $q->whereNull('volume_ml')
                    ->orWhereNull('uom')
                    ->orWhere('uom', '');
            });
        }

        $products = $query
            ->orderBy('volume_ml')
            ->orderBy('sku')
            ->get(['id', 'sku', 'name', 'size', 'volume_ml', 'uom', 'barcode', 'base_price', 'is_active', 'packaging_type_id']);

        $hasActiveFilters = filled($search) || filled($filter);

        return view('admin.products.index', compact(
            'products',
            'stats',
            'hasActiveFilters',
            'search',
            'filter'
        ));
    }

    /**
     * List all materials (raw, service, in‑house) that are used in BOMs and costing.
     */
    public function materialsIndex()
    {
        $search = request('q');
        $categoryId = request('category');
        $typeFilter = request('type');

        $materialsQuery = Product::query()
            ->with(['materialCategory.parent'])
            ->whereIn('product_type', ['raw', 'service', 'inhouse']);

        if ($search) {
            $term = '%' . $search . '%';
            $materialsQuery->where(function ($q) use ($term) {
                $q->where('sku', 'like', $term)
                    ->orWhere('name', 'like', $term)
                    ->orWhere('supplier_name', 'like', $term)
                    ->orWhere('chemical_name', 'like', $term);
            });
        }

        if ($categoryId) {
            $materialsQuery->where('material_category_id', $categoryId);
        }

        if (in_array($typeFilter, ['raw', 'service', 'inhouse'], true)) {
            $materialsQuery->where('product_type', $typeFilter);
        } else {
            $typeFilter = null;
        }

        $filteredMaterialsCount = (clone $materialsQuery)->count();

        $materials = $materialsQuery
            ->orderBy('name')
            ->get();

        $baseMaterialsQuery = Product::query()
            ->whereIn('product_type', ['raw', 'service', 'inhouse']);

        $totalMaterials = (clone $baseMaterialsQuery)->count();
        $rawCount = (clone $baseMaterialsQuery)->where('product_type', 'raw')->count();
        $serviceCount = (clone $baseMaterialsQuery)->where('product_type', 'service')->count();
        $inhouseCount = (clone $baseMaterialsQuery)->where('product_type', 'inhouse')->count();
        $roGroup = 'RO & Water Treatment';
        $roCategoryIds = MaterialCategory::where('group', $roGroup)->pluck('id');
        $roChemicalCount = (clone $baseMaterialsQuery)
            ->where(function ($q) use ($roCategoryIds) {
                $q->whereIn('material_category_id', $roCategoryIds)
                    ->orWhereNotNull('chemical_name');
            })
            ->count();

        $materialSections = $this->groupMaterialsByCategorySection($materials);

        $hasActiveFilters = filled($search) || filled($categoryId) || filled($typeFilter);

        $autoExpandSections = [];
        if ($hasActiveFilters) {
            if ($typeFilter === 'inhouse') {
                $autoExpandSections = ['section-inhouse-production'];
            } elseif ($categoryId) {
                $category = MaterialCategory::find($categoryId);
                if ($category) {
                    $group = $category->group ?: 'Other';
                    $autoExpandSections = ['group-' . Str::slug($group)];
                }
            } else {
                $autoExpandSections = collect($materialSections)
                    ->filter(fn (array $section) => $section['items']->count() > 0)
                    ->pluck('key')
                    ->all();
            }
        }

        $materialCategories = MaterialCategory::ordered()->get();
        $groupedCategories = $materialCategories->groupBy(fn ($cat) => $cat->group ?: 'Other');
        $units = UnitOfMeasure::ordered()->get();
        $sectionKeys = collect($materialSections)->pluck('key')->all();

        return view('admin.products.materials_index', compact(
            'materials',
            'materialSections',
            'autoExpandSections',
            'sectionKeys',
            'hasActiveFilters',
            'totalMaterials',
            'rawCount',
            'serviceCount',
            'inhouseCount',
            'roChemicalCount',
            'filteredMaterialsCount',
            'search',
            'materialCategories',
            'groupedCategories',
            'categoryId',
            'typeFilter',
            'units'
        ));
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Product>  $materials
     * @return array<int, array{key: string, code: string|null, label: string, group: string, sort_order: int, items: \Illuminate\Support\Collection<int, Product>}>
     */
    protected function groupMaterialsByCategorySection($materials): array
    {
        $sections = [];

        foreach (RawMaterialLineCatalog::categoryGroups() as $groupName) {
            $key = 'group-' . Str::slug($groupName);
            $sections[$key] = [
                'key' => $key,
                'code' => null,
                'label' => $groupName,
                'group' => $groupName,
                'sort_order' => $this->groupSortOrder($groupName),
                'items' => collect(),
                'section_kind' => 'category',
            ];
        }

        $inhouseKey = 'section-inhouse-production';
        $sections[$inhouseKey] = [
            'key' => $inhouseKey,
            'code' => null,
            'label' => RawMaterialLineCatalog::inhouseSectionLabel(),
            'group' => '_inhouse',
            'sort_order' => 850,
            'items' => collect(),
            'section_kind' => 'inhouse',
        ];

        foreach ($materials as $material) {
            if ($material->product_type === 'inhouse') {
                $sections[$inhouseKey]['items']->push($material);

                continue;
            }

            if (! $material->material_category_id) {
                $key = 'group-uncategorized';

                if (! isset($sections[$key])) {
                    $sections[$key] = [
                        'key' => $key,
                        'code' => null,
                        'label' => 'Uncategorized',
                        'group' => 'Other',
                        'sort_order' => 9999,
                        'items' => collect(),
                        'section_kind' => 'category',
                    ];
                }

                $sections[$key]['items']->push($material);

                continue;
            }

            $group = $material->materialCategory->group ?: 'Other';
            $key = 'group-' . Str::slug($group);

            if (! isset($sections[$key])) {
                $sections[$key] = [
                    'key' => $key,
                    'code' => null,
                    'label' => $group,
                    'group' => $group,
                    'sort_order' => $this->groupSortOrder($group),
                    'items' => collect(),
                    'section_kind' => 'category',
                ];
            }

            $sections[$key]['items']->push($material);
        }

        return collect($sections)
            ->sortBy([
                ['sort_order', 'asc'],
                ['label', 'asc'],
            ])
            ->values()
            ->all();
    }

    protected function groupSortOrder(string $group): int
    {
        $order = array_flip(RawMaterialLineCatalog::categoryGroups());

        return ($order[$group] ?? 99) * 10;
    }

    public function suggestSku(Request $request)
    {
        $data = $request->validate([
            'product_type' => 'nullable|in:finished,raw,service,inhouse',
            'size' => 'nullable|string|max:40',
            'uom' => 'nullable|string|max:40',
            'packaging' => 'nullable|string|max:40',
            'material_kind' => 'nullable|string|max:40',
            'material_category_id' => 'nullable|exists:material_categories,id',
        ]);

        return response()->json([
            'sku' => SkuGenerator::suggest($data),
            'hint' => SkuGenerator::formatHint(),
            'pattern' => SkuGenerator::pattern(),
        ]);
    }

    public function create()
    {
        $packagingTypes = $this->packagingTypesForSelect();
        $taxClasses = TaxClass::orderBy('name')->get();
        $units = UnitOfMeasure::ordered()->get();

        $context = 'products';
        $productNameOptions = $this->productNameOptionsForForm();

        return view('admin.products.create', compact('packagingTypes', 'taxClasses', 'units', 'context', 'productNameOptions'));
    }

    /**
     * Material create form – reuses the product create view but with a different context.
     */
    public function materialsCreate()
    {
        $packagingTypes = $this->packagingTypesForSelect();
        $taxClasses = TaxClass::orderBy('name')->get();
        $materialCategories = MaterialCategory::ordered()->get();
        $groupedCategories = $materialCategories->groupBy(fn ($cat) => $cat->group ?: 'Other');
        $units = UnitOfMeasure::ordered()->get();
        $context = 'materials';

        return view('admin.products.create', compact(
            'packagingTypes',
            'taxClasses',
            'materialCategories',
            'groupedCategories',
            'units',
            'context'
        ));
    }

    public function store(Request $request)
    {
        $isMaterialsRoute = $request->routeIs('admin.materials.*');

        $data = $request->validate([
            'sku' => ['required', 'string', 'unique:products,sku', SkuGenerator::validationRule()],
            'name' => 'required|string',
            'brand' => $isMaterialsRoute ? 'prohibited' : 'nullable|string|max:80',
            // When creating products from the main catalog screen we now focus on
            // sellable SKUs. The form silently posts "finished" as the type, but
            // we still allow other values for legacy records and API usage.
            'product_type' => 'nullable|in:finished,raw,service,inhouse',
            'material_category_id' => $isMaterialsRoute ? 'nullable|exists:material_categories,id' : 'prohibited',
            'description' => 'nullable|string',
            'size' => 'nullable|string',
            'uom' => 'nullable|string',
            'volume_ml' => 'nullable|numeric|min:0',
            'weight_g' => $isMaterialsRoute ? 'prohibited' : 'nullable|integer|min:0',
            'shelf_life_months' => $isMaterialsRoute ? 'prohibited' : 'nullable|integer|min:1|max:120',
            'sku_code' => 'nullable|string',
            'packaging_type_id' => 'nullable|exists:packaging_types,id',
            'tax_class_id' => 'nullable|exists:tax_classes,id',
            'mineral_source' => 'nullable|string',
            'ph' => 'nullable|numeric|min:0|max:14',
            'tds' => 'nullable|integer|min:0',
            'certifications' => 'nullable|string',
            'barcode' => 'nullable|string',
            'qr_code' => 'nullable|string',
            'image_path' => 'nullable|string',
            'base_price' => $isMaterialsRoute ? 'nullable|numeric|min:0' : 'prohibited',
            'mrp' => 'nullable|numeric|min:0',
            'standard_cost' => 'nullable|numeric|min:0',
            'reorder_level' => 'nullable|integer|min:0',
            'supplier_name' => 'nullable|string',
            'chemical_name' => 'nullable|string|max:255',
            'sourcing' => 'nullable|in:purchased,inhouse,both',
            'is_active' => 'sometimes|boolean',
        ]);

        $data['sku'] = SkuGenerator::normalize($data['sku']);

        // Default type to "finished" if the form did not explicitly send it
        $data['product_type'] = $data['product_type'] ?? 'finished';

        if ($isMaterialsRoute) {
            $data['sourcing'] = in_array($data['sourcing'] ?? null, ['purchased', 'inhouse', 'both'], true)
                ? $data['sourcing']
                : 'purchased';
        } else {
            unset($data['sourcing'], $data['chemical_name'], $data['mrp'], $data['base_price']);
            $data['brand'] = filled($data['brand'] ?? null) ? $data['brand'] : 'SAF';
            $data['base_price'] = 0;
        }

        // Default standard_cost to base_price for finished SKUs if not provided
        if (! isset($data['standard_cost'])) {
            $data['standard_cost'] = $data['base_price'] ?? 0;
        }
        // For materials we treat missing base_price as 0 so later reports work
        if ($isMaterialsRoute && ! isset($data['base_price'])) {
            $data['base_price'] = 0;
        }
        $data['is_active'] = $request->boolean('is_active', true);

        $product = Product::create($data);

        if ($request->hasFile('product_image')) {
            $product->update(['image_path' => $request->file('product_image')->store('products', 'public')]);
        }

        if ($request->hasFile('qr_code_file')) {
            $product->update(['qr_code' => $request->file('qr_code_file')->store('qrcodes', 'public')]);
        }

        if ($isMaterialsRoute) {
            return redirect()
                ->route('admin.materials.index')
                ->with('status', 'Material added.');
        }

        return redirect()
            ->route('admin.products.show', $product)
            ->with('status', 'Product added to catalog.');
    }

    public function edit(Product $product)
    {
        $packagingTypes = $this->packagingTypesForSelect();
        $taxClasses = TaxClass::orderBy('name')->get();
        $units = UnitOfMeasure::ordered()->get();
        $context = 'products';

        $product->load(['taxClass', 'packagingType']);
        $productNameOptions = $this->productNameOptionsForForm($product);

        return view('admin.products.edit', compact('product', 'packagingTypes', 'taxClasses', 'units', 'context', 'productNameOptions'));
    }

    public function materialsEdit(Product $product)
    {
        $packagingTypes = $this->packagingTypesForSelect();
        $taxClasses = TaxClass::orderBy('name')->get();
        $materialCategories = MaterialCategory::ordered()->get();
        $groupedCategories = $materialCategories->groupBy(fn ($cat) => $cat->group ?: 'Other');
        $units = UnitOfMeasure::ordered()->get();
        $context = 'materials';

        return view('admin.products.edit', compact(
            'product',
            'packagingTypes',
            'taxClasses',
            'materialCategories',
            'groupedCategories',
            'units',
            'context'
        ));
    }

    public function materialsShow(Product $product)
    {
        if (! in_array($product->product_type, ['raw', 'service', 'inhouse'], true)) {
            abort(404);
        }

        $product->load(['materialCategory']);

        return view('admin.products.materials_show', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $isMaterialsRoute = $request->routeIs('admin.materials.*');

        $data = $request->validate([
            'sku' => ['required', 'string', 'unique:products,sku,' . $product->id, SkuGenerator::validationRule()],
            'name' => 'required|string',
            'brand' => $isMaterialsRoute ? 'prohibited' : 'nullable|string|max:80',
            'product_type' => 'nullable|in:finished,raw,service,inhouse',
            'material_category_id' => $isMaterialsRoute ? 'nullable|exists:material_categories,id' : 'prohibited',
            'description' => 'nullable|string',
            'size' => 'nullable|string',
            'uom' => 'nullable|string',
            'volume_ml' => 'nullable|numeric|min:0',
            'weight_g' => $isMaterialsRoute ? 'prohibited' : 'nullable|integer|min:0',
            'shelf_life_months' => $isMaterialsRoute ? 'prohibited' : 'nullable|integer|min:1|max:120',
            'sku_code' => 'nullable|string',
            'packaging_type_id' => 'nullable|exists:packaging_types,id',
            'tax_class_id' => 'nullable|exists:tax_classes,id',
            'mineral_source' => 'nullable|string',
            'ph' => 'nullable|numeric|min:0|max:14',
            'tds' => 'nullable|integer|min:0',
            'certifications' => 'nullable|string',
            'barcode' => 'nullable|string',
            'qr_code' => 'nullable|string',
            'image_path' => 'nullable|string',
            'base_price' => $isMaterialsRoute ? 'nullable|numeric|min:0' : 'prohibited',
            'mrp' => 'nullable|numeric|min:0',
            'standard_cost' => 'nullable|numeric|min:0',
            'reorder_level' => 'nullable|integer|min:0',
            'supplier_name' => 'nullable|string',
            'chemical_name' => 'nullable|string|max:255',
            'sourcing' => 'nullable|in:purchased,inhouse,both',
            'is_active' => 'sometimes|boolean',
        ]);

        $data['sku'] = SkuGenerator::normalize($data['sku']);

        $data['product_type'] = $data['product_type'] ?? $product->product_type ?? 'finished';

        if ($isMaterialsRoute) {
            $data['sourcing'] = in_array($data['sourcing'] ?? null, ['purchased', 'inhouse', 'both'], true)
                ? $data['sourcing']
                : ($product->sourcing ?? 'purchased');
        } else {
            unset($data['sourcing'], $data['chemical_name'], $data['mrp'], $data['base_price']);
            $data['brand'] = filled($data['brand'] ?? null) ? $data['brand'] : ($product->brand ?? 'SAF');
        }
        if (! isset($data['standard_cost'])) {
            $data['standard_cost'] = $product->standard_cost ?? 0;
        }
        if ($isMaterialsRoute && ! isset($data['base_price'])) {
            $data['base_price'] = 0;
        }
        $data['is_active'] = $request->boolean('is_active', true);

        $product->update($data);

        if ($request->hasFile('product_image')) {
            $product->update(['image_path' => $request->file('product_image')->store('products', 'public')]);
        }

        if ($request->hasFile('qr_code_file')) {
            $product->update(['qr_code' => $request->file('qr_code_file')->store('qrcodes', 'public')]);
        }

        if ($isMaterialsRoute) {
            return redirect()
                ->route('admin.materials.index')
                ->with('status', 'Material updated.');
        }

        return redirect()
            ->route('admin.products.show', $product)
            ->with('status', 'Product updated.');
    }

    public function show(Product $product)
    {
        $product->load(['packagingType', 'taxClass', 'batches']);

        $activeBom = null;
        if (in_array($product->product_type, [null, 'finished'], true)) {
            $activeBom = BillOfMaterial::activeForProduct((int) $product->id);
            if ($activeBom) {
                $activeBom->loadCount('items');
            }
        }

        return view('admin.products.show', compact('product', 'activeBom'));
    }

    public function export()
    {
        $products = Product::with(['packagingType', 'taxClass'])->orderBy('sku')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="product_catalog.csv"',
        ];

        $callback = static function () use ($products) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'SKU',
                'Name',
                'Size',
                'Volume (ml)',
                'Packaging',
                'Tax class',
                'Tax rate',
                'Base price',
                'Barcode',
                'QR path',
            ]);

            foreach ($products as $product) {
                fputcsv($handle, [
                    $product->sku,
                    $product->name,
                    $product->size,
                    $product->volume_ml,
                    optional($product->packagingType)->name,
                    optional($product->taxClass)->name,
                    optional($product->taxClass)->rate,
                    $product->base_price,
                    $product->barcode,
                    $product->qr_code,
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function priceList()
    {
        $search = request('q');
        $filter = request('filter');

        $stats = [
            'total' => Product::sellable()->count(),
            'with_mrp' => Product::sellable()->whereNotNull('mrp')->where('mrp', '>', 0)->count(),
            'missing_mrp' => Product::sellable()->where(function ($q) {
                $q->whereNull('mrp')->orWhere('mrp', '<=', 0);
            })->count(),
            'with_special_prices' => Product::sellable()->has('agentPriceLists')->count(),
            'special_price_rows' => AgentPriceList::query()
                ->whereIn('product_id', Product::sellable()->select('id'))
                ->count(),
        ];

        $query = Product::query()
            ->withCount('agentPriceLists')
            ->sellable()
            ->orderBy('sku');

        if ($search) {
            $term = '%' . $search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('sku', 'like', $term)
                    ->orWhere('name', 'like', $term);
            });
        }

        if ($filter === 'missing_mrp') {
            $query->where(function ($q) {
                $q->whereNull('mrp')->orWhere('mrp', '<=', 0);
            });
        } elseif ($filter === 'special_prices') {
            $query->has('agentPriceLists');
        } elseif ($filter === 'base_above_mrp') {
            $query->whereNotNull('mrp')
                ->where('mrp', '>', 0)
                ->whereColumn('base_price', '>', 'mrp');
        }

        $products = $query->get();
        $hasActiveFilters = filled($search) || filled($filter);

        return view('admin.products.price_list', compact(
            'products',
            'stats',
            'hasActiveFilters',
            'search',
            'filter'
        ));
    }

    public function updateProductPrices(Request $request, Product $product)
    {
        if (! $product->isSellable()) {
            abort(404);
        }

        $data = $request->validate([
            'mrp' => 'nullable|numeric|min:0',
            'base_price' => 'required|numeric|min:0',
        ]);

        $mrp = $data['mrp'] !== null && $data['mrp'] !== '' ? (float) $data['mrp'] : null;
        $basePrice = (float) $data['base_price'];

        if ($mrp !== null && $basePrice > $mrp) {
            return back()
                ->withInput()
                ->withErrors(['base_price' => 'Trade price cannot exceed MRP.']);
        }

        $product->update([
            'mrp' => $mrp,
            'base_price' => $basePrice,
        ]);

        return redirect()
            ->to(route('admin.products.prices.index', array_filter(request()->only(['q', 'filter']))))
            ->with('status', $product->sku . ' prices saved.');
    }

    public function showPriceList(Product $product)
    {
        if (! $product->isSellable()) {
            abort(404);
        }

        $product->load(['packagingType', 'agentPriceLists.agent']);

        $agentPrices = $product->agentPriceLists
            ->sortBy(fn ($row) => $row->agent->name ?? '');

        return view('admin.products.price_list_show', compact('product', 'agentPrices'));
    }

    public function updatePriceListOverride(Request $request, AgentPriceList $agentPriceList)
    {
        $agentPriceList->load('product');
        $product = $agentPriceList->product;

        if (! $product || ! $product->isSellable()) {
            abort(404);
        }

        $data = $request->validate([
            'price' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $agentPriceList->update([
            'price' => $data['price'],
            'notes' => trim((string) ($data['notes'] ?? '')) ?: null,
        ]);

        return redirect()
            ->route('admin.products.prices.show', $product)
            ->with('status', 'Special agent price updated.');
    }

    public function destroyPriceListOverride(AgentPriceList $agentPriceList)
    {
        $product = $agentPriceList->product;
        $agentPriceList->delete();

        return redirect()
            ->route('admin.products.prices.show', $product)
            ->with('status', 'Special agent price removed — agent will use base price.');
    }

    public function destroy(Request $request, Product $product)
    {
        $isMaterialsRoute = $request->routeIs('admin.materials.*');

        $reasons = [];

        if (OrderItem::where('product_id', $product->id)->exists()) {
            $reasons[] = 'sales orders';
        }

        if (InvoiceItem::where('product_id', $product->id)->exists()) {
            $reasons[] = 'customer invoices';
        }

        if (AgentPriceList::where('product_id', $product->id)->exists()) {
            $reasons[] = 'agent price lists';
        }

        if (Batch::where('product_id', $product->id)->exists()) {
            $reasons[] = 'production batches';
        }

        if (ProductionRun::where('product_id', $product->id)->exists()) {
            $reasons[] = 'production runs';
        }

        if (StockEntry::where('product_id', $product->id)->exists()) {
            $reasons[] = 'stock entries';
        }

        if (BomItem::where('component_product_id', $product->id)->exists()) {
            $reasons[] = 'bills of material';
        }

        if (DeliveryItem::where('product_id', $product->id)->exists()) {
            $reasons[] = 'delivery records';
        }

        if (ProductionMaterialIssueItem::where('component_product_id', $product->id)->exists()) {
            $reasons[] = 'production material issues';
        }

        if (GoodsReceiptItem::where('product_id', $product->id)->exists()) {
            $reasons[] = 'goods receipts';
        }

        if (PurchaseBillItem::where('product_id', $product->id)->exists()) {
            $reasons[] = 'purchase bills';
        }

        if (PurchaseOrderItem::where('product_id', $product->id)->exists()) {
            $reasons[] = 'purchase orders';
        }

        if (! empty($reasons)) {
            $reasonText = implode(', ', $reasons);

            $label = $isMaterialsRoute ? 'Material' : 'Product';

            return redirect()->route($isMaterialsRoute ? 'admin.materials.index' : 'admin.products.index')
                ->with('status', $label . ' cannot be deleted because it is linked to: ' . $reasonText . '.');
        }

        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }
        if ($product->qr_code) {
            Storage::disk('public')->delete($product->qr_code);
        }

        $product->delete();

        if ($isMaterialsRoute) {
            return redirect()
                ->route('admin.materials.index')
                ->with('status', 'Material deleted.');
        }

        return redirect()
            ->route('admin.products.index')
            ->with('status', 'Product deleted.');
    }
}
