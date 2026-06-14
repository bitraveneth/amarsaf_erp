<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\SupplierProductCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::with('productCategories')
            ->orderBy('name')
            ->paginate(15);

        $productCategories = SupplierProductCategory::withCount('suppliers')
            ->ordered()
            ->get();

        $totalSuppliers = Supplier::count();
        $categorizedSuppliers = Supplier::whereHas('productCategories')->count();
        $uncategorizedSuppliers = max(0, $totalSuppliers - $categorizedSuppliers);

        return view('admin.suppliers.index', compact(
            'suppliers',
            'productCategories',
            'totalSuppliers',
            'categorizedSuppliers',
            'uncategorizedSuppliers',
        ));
    }

    public function create()
    {
        $productCategories = SupplierProductCategory::active()->ordered()->get();

        return view('admin.suppliers.create', compact('productCategories'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $categoryIds = $data['product_category_ids'] ?? [];
        unset($data['product_category_ids']);

        $supplier = Supplier::create($data);
        $this->syncProductCategories($supplier, $categoryIds);

        return redirect()->route('admin.suppliers.index')->with('status', 'Supplier created.');
    }

    public function show(Supplier $supplier)
    {
        $supplier->loadCount(['purchaseOrders', 'goodsReceipts', 'bills']);
        $supplier->load('productCategories');

        return view('admin.suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier)
    {
        $supplier->load('productCategories');
        $productCategories = SupplierProductCategory::active()->ordered()->get();

        return view('admin.suppliers.edit', compact('supplier', 'productCategories'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $data = $this->validated($request, $supplier);
        $categoryIds = $data['product_category_ids'] ?? [];
        unset($data['product_category_ids']);

        $supplier->update($data);
        $this->syncProductCategories($supplier, $categoryIds);

        return redirect()
            ->route('admin.suppliers.show', $supplier)
            ->with('status', 'Supplier updated.');
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->purchaseOrders()->exists()) {
            return back()->with('error', 'Supplier has purchase orders and cannot be deleted.');
        }

        if ($supplier->goodsReceipts()->exists()) {
            return back()->with('error', 'Supplier has goods receipts and cannot be deleted.');
        }

        if ($supplier->bills()->exists()) {
            return back()->with('error', 'Supplier has purchase bills and cannot be deleted.');
        }

        $supplier->delete();

        return redirect()
            ->route('admin.suppliers.index')
            ->with('status', 'Supplier deleted.');
    }

    public function attachCategory(Request $request, Supplier $supplier)
    {
        $data = $request->validate([
            'supplier_product_category_id' => 'required|exists:supplier_product_categories,id',
        ]);

        $supplier->productCategories()->syncWithoutDetaching([$data['supplier_product_category_id']]);

        return back()->with('status', 'Category added to supplier.');
    }

    public function detachCategory(Supplier $supplier, SupplierProductCategory $supplierProductCategory)
    {
        $supplier->productCategories()->detach($supplierProductCategory->id);

        return back()->with('status', 'Category removed from supplier.');
    }

    protected function syncProductCategories(Supplier $supplier, array $categoryIds): void
    {
        $ids = SupplierProductCategory::query()
            ->whereIn('id', $categoryIds)
            ->pluck('id')
            ->all();

        $supplier->productCategories()->sync($ids);
    }

    protected function validated(Request $request, ?Supplier $supplier = null): array
    {
        $supplierId = $supplier?->id;

        $request->merge([
            'name' => trim((string) $request->input('name')),
            'email' => $request->filled('email') ? strtolower(trim((string) $request->input('email'))) : null,
            'tax_id' => $request->filled('tax_id') ? trim((string) $request->input('tax_id')) : null,
        ]);

        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('suppliers', 'name')->ignore($supplierId),
            ],
            'contact_person' => 'nullable|string|max:255',
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('suppliers', 'email')->ignore($supplierId),
            ],
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:1000',
            'tax_id' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('suppliers', 'tax_id')->ignore($supplierId),
            ],
            'product_category_ids' => 'nullable|array',
            'product_category_ids.*' => 'integer|exists:supplier_product_categories,id',
        ]);
    }
}
