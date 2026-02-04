<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockMovement;
use Illuminate\Http\Request;

class SupplierReturnController extends Controller
{
    public function index()
    {
        $returns = StockMovement::with(['stockEntry.product', 'stockEntry.warehouse'])
            ->where('type', 'supplier-return')
            ->latest()
            ->paginate(15);

        return view('admin.returns.supplier.index', compact('returns'));
    }
}
