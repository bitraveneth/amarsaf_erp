<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Product;
use App\Models\StockAudit;
use App\Models\StockEntry;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class StockAuditController extends Controller
{
    public function index()
    {
        $audits = StockAudit::with('warehouse', 'product', 'batch')->latest()->paginate(20);
        $warehouses = Warehouse::orderBy('name')->get();
        $products = Product::orderBy('name')->get();
        $batches = Batch::orderBy('production_date', 'desc')->get();

        return view('admin.stock.audit', compact('audits', 'warehouses', 'products', 'batches'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'product_id' => 'required|exists:products,id',
            'batch_id' => 'nullable|exists:batches,id',
            'counted_quantity' => 'required|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        $query = StockEntry::where('warehouse_id', $data['warehouse_id'])
            ->where('product_id', $data['product_id']);

        if (!empty($data['batch_id'])) {
            $query->where('batch_id', $data['batch_id']);
        }

        $systemQty = (int) $query->sum('quantity');
        $variance = $data['counted_quantity'] - $systemQty;

        StockAudit::create([
            'warehouse_id' => $data['warehouse_id'],
            'product_id' => $data['product_id'],
            'batch_id' => $data['batch_id'] ?? null,
            'system_quantity' => $systemQty,
            'counted_quantity' => $data['counted_quantity'],
            'variance' => $variance,
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()->route('admin.stock.audit')->with('status', 'Stock audit recorded.');
    }
}

