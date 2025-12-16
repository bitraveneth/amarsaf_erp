<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Product;
use Illuminate\Http\Request;

class BatchController extends Controller
{
    public function index()
    {
        $batches = Batch::with('product')->latest('production_date')->paginate(10);
        $products = Product::orderBy('name')->get();

        return view('admin.batches.index', compact('batches', 'products'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'batch_code' => 'required|string',
            'production_date' => 'required|date',
            'expiry_date' => 'nullable|date|after_or_equal:production_date',
            'qc_status' => 'required|in:pending,approved,rejected',
            'notes' => 'nullable|string',
        ]);

        Batch::create($data);

        return back()->with('status', 'Batch recorded.');
    }

    public function edit(Batch $batch)
    {
        $products = Product::orderBy('name')->get();
        return view('admin.batches.edit', compact('batch', 'products'));
    }

    public function update(Request $request, Batch $batch)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'batch_code' => 'required|string',
            'production_date' => 'required|date',
            'expiry_date' => 'nullable|date|after_or_equal:production_date',
            'qc_status' => 'required|in:pending,approved,rejected',
            'notes' => 'nullable|string',
        ]);

        $batch->update($data);
        return redirect()->route('admin.batches.index')->with('status', 'Batch updated.');
    }

    public function destroy(Batch $batch)
    {
        if ($batch->productionRuns()->exists()) {
            return redirect()->route('admin.batches.index')
                ->with('status', 'Batch is linked to production runs and cannot be deleted.');
        }

        if ($batch->stockEntries()->exists()) {
            return redirect()->route('admin.batches.index')
                ->with('status', 'Batch has stock entries and cannot be deleted.');
        }

        $batch->delete();

        return redirect()->route('admin.batches.index')->with('status', 'Batch deleted.');
    }
}
