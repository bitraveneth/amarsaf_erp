<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TaxClass;
use Illuminate\Http\Request;

class TaxClassController extends Controller
{
    public function index()
    {
        $taxClasses = TaxClass::orderBy('name')->paginate(10);
        return view('admin.tax.index', compact('taxClasses'));
    }

    public function edit(TaxClass $taxClass)
    {
        return view('admin.tax.edit', compact('taxClass'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'hsn_code' => 'nullable|string',
            'local_tax_code' => 'nullable|string',
            'rate' => 'required|numeric|min:0|max:100',
        ]);

        TaxClass::create($data);
        return back()->with('status', 'Tax class created.');
    }

    public function update(Request $request, TaxClass $taxClass)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'hsn_code' => 'nullable|string',
            'local_tax_code' => 'nullable|string',
            'rate' => 'required|numeric|min:0|max:100',
        ]);

        $taxClass->update($data);

        return redirect()->route('admin.tax-classes.index')->with('status', 'Tax class updated.');
    }

    public function destroy(TaxClass $taxClass)
    {
        if ($taxClass->products()->exists()) {
            return redirect()->route('admin.tax-classes.index')
                ->with('status', 'Tax class is linked to products and cannot be deleted.');
        }

        $taxClass->delete();

        return redirect()->route('admin.tax-classes.index')->with('status', 'Tax class deleted.');
    }
}
