<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MaterialCategory;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MaterialCategoryController extends Controller
{
    public function index()
    {
        $categories = MaterialCategory::query()
            ->withCount('products')
            ->ordered()
            ->get();

        $groupedCategories = $categories->groupBy(fn ($cat) => $cat->group ?: 'Other');
        $linkedMaterials = Product::query()
            ->whereIn('product_type', ['raw', 'service', 'inhouse'])
            ->whereNotNull('material_category_id')
            ->count();

        return view('admin.material-categories.index', compact(
            'categories',
            'groupedCategories',
            'linkedMaterials'
        ));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        MaterialCategory::create($data);

        $redirect = $request->input('_redirect');

        if ($redirect && str_starts_with($redirect, url('/'))) {
            return redirect($redirect)->with('status', 'Material category added.');
        }

        return back()->with('status', 'Material category added.');
    }

    public function update(Request $request, MaterialCategory $materialCategory)
    {
        $data = $this->validated($request, $materialCategory);

        if ($materialCategory->is_system) {
            unset($data['code'], $data['name']);
        }

        $data['parent_id'] = null;

        $materialCategory->update($data);

        return back()->with('status', 'Material category updated.');
    }

    public function destroy(MaterialCategory $materialCategory)
    {
        if ($materialCategory->is_system) {
            return back()->with('error', 'Standard SAF categories cannot be deleted.');
        }

        if ($materialCategory->children()->exists()) {
            return back()->with('error', 'This category still has linked sub-categories.');
        }

        if ($materialCategory->products()->exists()) {
            return back()->with('error', 'Category is linked to materials and cannot be deleted.');
        }

        $materialCategory->delete();

        return back()->with('status', 'Material category deleted.');
    }

    protected function validated(Request $request, ?MaterialCategory $category = null): array
    {
        $categoryId = $category?->id;

        $data = $request->validate([
            'code' => [
                'required',
                'string',
                'max:32',
                'regex:/^[A-Z0-9-]+$/i',
                Rule::unique('material_categories', 'code')->ignore($categoryId),
            ],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('material_categories', 'name')->ignore($categoryId),
            ],
            'parent_id' => 'nullable|exists:material_categories,id',
            'group' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer|min:0|max:9999',
        ]);

        $data['code'] = Str::upper(trim($data['code']));
        $data['name'] = trim($data['name']);
        $data['group'] = trim((string) ($data['group'] ?? '')) ?: null;
        $data['description'] = trim((string) ($data['description'] ?? '')) ?: null;
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['is_system'] = false;
        $data['parent_id'] = null;

        return $data;
    }
}
