<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PackagingConversion;
use App\Models\PackagingType;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PackagingTypeController extends Controller
{
    public function index()
    {
        $systemTypes = PackagingType::where('is_system', true)->count();
        $customTypes = PackagingType::where('is_system', false)->count();
        $linkedProducts = Product::query()
            ->whereNotNull('packaging_type_id')
            ->where(function ($q) {
                $q->whereNull('product_type')->orWhere('product_type', 'finished');
            })
            ->count();

        $packagingTypes = PackagingType::query()
            ->withCount('products')
            ->orderByDesc('is_system')
            ->orderBy('code')
            ->get();

        return view('admin.packaging.index', compact(
            'systemTypes',
            'customTypes',
            'linkedProducts',
            'packagingTypes'
        ));
    }

    public function show(PackagingType $packagingType)
    {
        return redirect()->route('admin.packaging.index');
    }

    public function edit(PackagingType $packagingType)
    {
        return redirect()->to(route('admin.packaging.index') . '#packaging-types');
    }

    public function update(Request $request, PackagingType $packagingType)
    {
        $rules = [
            'unit' => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ];

        if (! $packagingType->is_system) {
            $rules['code'] = 'required|string|max:32|unique:packaging_types,code,' . $packagingType->id . '|regex:/^[A-Z0-9-]+$/i';
            $rules['name'] = 'required|string|max:255|unique:packaging_types,name,' . $packagingType->id;
        }

        $data = $request->validate($rules);

        if ($packagingType->is_system) {
            unset($data['name'], $data['code']);
        } else {
            $data['code'] = Str::upper($data['code']);
        }

        $packagingType->update($data);

        return redirect()
            ->to(route('admin.packaging.index') . '#packaging-types')
            ->with('status', 'Packaging type updated.');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:32|unique:packaging_types,code|regex:/^[A-Z0-9-]+$/i',
            'name' => 'required|string|max:255|unique:packaging_types,name',
            'unit' => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);

        PackagingType::create(array_merge($data, [
            'code' => Str::upper($data['code']),
            'is_system' => false,
        ]));

        return redirect()
            ->to(route('admin.packaging.index') . '#packaging-types')
            ->with('status', 'Packaging type added.');
    }

    public function destroy(PackagingType $packagingType)
    {
        if ($packagingType->is_system) {
            return redirect()->route('admin.packaging.index')
                ->with('error', 'Standard SAF packaging types cannot be deleted.');
        }

        if ($packagingType->products()->exists()) {
            return redirect()->route('admin.packaging.index')
                ->with('error', 'This packaging type is linked to products and cannot be deleted.');
        }

        if (
            PackagingConversion::where('from_packaging_type_id', $packagingType->id)->exists()
            || PackagingConversion::where('to_packaging_type_id', $packagingType->id)->exists()
        ) {
            return redirect()->route('admin.packaging.index')
                ->with('error', 'This packaging type is still referenced and cannot be deleted.');
        }

        $packagingType->delete();

        return redirect()->route('admin.packaging.index')
            ->with('status', 'Packaging type deleted.');
    }
}
