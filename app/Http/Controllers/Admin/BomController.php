<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BillOfMaterial;
use App\Models\BomItem;
use App\Models\Product;
use Illuminate\Http\Request;

class BomController extends Controller
{
    public function index()
    {
        $boms = BillOfMaterial::with('product', 'items.component')
            ->orderByDesc('id')
            ->paginate(15);

        return view('admin.boms.index', compact('boms'));
    }

    public function create()
    {
        $products = Product::orderBy('name')->get();

        return view('admin.boms.create', [
            'products' => $products,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'name' => 'nullable|string|max:255',
            'is_active' => 'sometimes|boolean',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.component_product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit' => 'nullable|string|max:50',
        ]);

        $bom = BillOfMaterial::create([
            'product_id' => $data['product_id'],
            'name' => $data['name'] ?? null,
            'is_active' => $request->boolean('is_active', true),
            'notes' => $data['notes'] ?? null,
        ]);

        foreach ($data['items'] as $item) {
            $bom->items()->create([
                'component_product_id' => $item['component_product_id'],
                'quantity' => $item['quantity'],
                'unit' => $item['unit'] ?? null,
            ]);
        }

        return redirect()->route('admin.boms.index')->with('status', 'BOM created.');
    }

    public function edit(BillOfMaterial $bom)
    {
        $bom->load('items.component');
        $products = Product::orderBy('name')->get();

        return view('admin.boms.edit', compact('bom', 'products'));
    }

    public function update(Request $request, BillOfMaterial $bom)
    {
        $data = $request->validate([
            'name' => 'nullable|string|max:255',
            'is_active' => 'sometimes|boolean',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.component_product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit' => 'nullable|string|max:50',
        ]);

        $bom->update([
            'name' => $data['name'] ?? null,
            'is_active' => $request->boolean('is_active', true),
            'notes' => $data['notes'] ?? null,
        ]);

        $bom->items()->delete();
        foreach ($data['items'] as $item) {
            $bom->items()->create([
                'component_product_id' => $item['component_product_id'],
                'quantity' => $item['quantity'],
                'unit' => $item['unit'] ?? null,
            ]);
        }

        return redirect()->route('admin.boms.index')->with('status', 'BOM updated.');
    }

    public function destroy(BillOfMaterial $bom)
    {
        $bom->delete();

        return redirect()->route('admin.boms.index')->with('status', 'BOM deleted.');
    }
}
