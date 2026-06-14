<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\UnitOfMeasure;
use App\Support\ProductUnits;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UnitOfMeasureController extends Controller
{
    public function index()
    {
        $systemUnits = UnitOfMeasure::where('is_system', true)->count();
        $customUnits = UnitOfMeasure::where('is_system', false)->count();
        $linkedMaterials = Product::query()
            ->whereIn('product_type', ['raw', 'service', 'inhouse'])
            ->whereNotNull('uom')
            ->pluck('uom')
            ->unique()
            ->count();

        $units = UnitOfMeasure::query()
            ->withCount(['linkedProducts' => function ($query) {
                $query->whereIn('product_type', ['raw', 'service', 'inhouse']);
            }])
            ->ordered()
            ->get();

        return view('admin.units.index', compact('systemUnits', 'customUnits', 'linkedMaterials', 'units'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:32|unique:units_of_measure,code|regex:/^[a-z0-9_-]+$/i',
            'name' => 'required|string|max:255',
            'symbol' => 'nullable|string|max:16',
        ]);

        UnitOfMeasure::create([
            'code' => Str::lower($data['code']),
            'name' => trim($data['name']),
            'symbol' => trim((string) ($data['symbol'] ?? '')) ?: null,
            'is_system' => false,
            'sort_order' => (int) UnitOfMeasure::max('sort_order') + 10,
        ]);

        ProductUnits::flushCache();

        return redirect()
            ->to(route('admin.units.index') . '#units-list')
            ->with('status', 'Unit of measure added.');
    }

    public function update(Request $request, UnitOfMeasure $unit)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'symbol' => 'nullable|string|max:16',
        ];

        if (! $unit->is_system) {
            $rules['code'] = 'required|string|max:32|unique:units_of_measure,code,' . $unit->id . '|regex:/^[a-z0-9_-]+$/i';
        }

        $data = $request->validate($rules);

        if ($unit->is_system) {
            unset($data['code']);
        } else {
            $data['code'] = Str::lower($data['code']);
        }

        $data['name'] = trim($data['name']);
        $data['symbol'] = trim((string) ($data['symbol'] ?? '')) ?: null;

        $unit->update($data);
        ProductUnits::flushCache();

        return redirect()
            ->to(route('admin.units.index') . '#units-list')
            ->with('status', 'Unit updated.');
    }

    public function destroy(UnitOfMeasure $unit)
    {
        if ($unit->is_system) {
            return redirect()->route('admin.units.index')
                ->with('error', 'Standard units cannot be deleted.');
        }

        if (Product::where('uom', $unit->code)->exists()) {
            return redirect()->route('admin.units.index')
                ->with('error', 'This unit is linked to materials and cannot be deleted.');
        }

        $unit->delete();
        ProductUnits::flushCache();

        return redirect()->route('admin.units.index')
            ->with('status', 'Unit deleted.');
    }
}
