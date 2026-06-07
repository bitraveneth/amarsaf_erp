<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::orderBy('name')->paginate(15);

        return view('admin.suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('admin.suppliers.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        Supplier::create($data);

        return redirect()->route('admin.suppliers.index')->with('status', 'Supplier created.');
    }

    public function show(Supplier $supplier)
    {
        $supplier->loadCount(['purchaseOrders', 'goodsReceipts', 'bills']);

        return view('admin.suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier)
    {
        return view('admin.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $data = $this->validated($request, $supplier);

        $supplier->update($data);

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
        ]);
    }
}
