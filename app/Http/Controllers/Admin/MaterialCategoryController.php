<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MaterialCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MaterialCategoryController extends Controller
{
    public function index()
    {
        $categories = MaterialCategory::withCount('products')
            ->ordered()
            ->paginate(20);

        $groups = MaterialCategory::query()
            ->whereNotNull('group')
            ->distinct()
            ->orderBy('group')
            ->pluck('group');

        return view('admin.material-categories.index', compact('categories', 'groups'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        MaterialCategory::create($data);

        return back()->with('status', 'Material category added.');
    }

    public function update(Request $request, MaterialCategory $materialCategory)
    {
        $data = $this->validated($request, $materialCategory);

        $materialCategory->update($data);

        return back()->with('status', 'Material category updated.');
    }

    public function destroy(MaterialCategory $materialCategory)
    {
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
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('material_categories', 'name')->ignore($categoryId),
            ],
            'group' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer|min:0|max:9999',
        ]);

        $data['name'] = trim($data['name']);
        $data['group'] = trim((string) ($data['group'] ?? '')) ?: null;
        $data['description'] = trim((string) ($data['description'] ?? '')) ?: null;
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        return $data;
    }
}
