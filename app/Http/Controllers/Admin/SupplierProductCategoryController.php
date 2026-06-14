<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupplierProductCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupplierProductCategoryController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:supplier_product_categories,name',
            'description' => 'nullable|string|max:500',
        ]);

        $data['name'] = trim($data['name']);
        $data['description'] = trim((string) ($data['description'] ?? '')) ?: null;
        $data['sort_order'] = (int) (SupplierProductCategory::max('sort_order') ?? 0) + 10;

        SupplierProductCategory::create($data);

        return back()->with('status', 'Purchase category added.');
    }

    public function update(Request $request, SupplierProductCategory $supplierProductCategory)
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('supplier_product_categories', 'name')->ignore($supplierProductCategory->id),
            ],
            'description' => 'nullable|string|max:500',
        ]);

        $supplierProductCategory->update([
            'name' => trim($data['name']),
            'description' => trim((string) ($data['description'] ?? '')) ?: null,
        ]);

        return back()->with('status', 'Purchase category updated.');
    }

    public function destroy(SupplierProductCategory $supplierProductCategory)
    {
        $supplierProductCategory->suppliers()->detach();
        $supplierProductCategory->delete();

        return back()->with('status', 'Purchase category removed.');
    }
}
