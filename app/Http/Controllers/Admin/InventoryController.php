<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class InventoryController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $expiringSoon = StockEntry::with('product', 'batch')
            ->whereNotNull('batch_id')
            ->whereHas('batch', function ($query) use ($today) {
                $query->where('expiry_date', '<=', $today->copy()->addDays(30));
            })
            ->orderBy('batch_id')
            ->get();

        $summary = StockEntry::selectRaw('warehouse_id, status, SUM(quantity) as total')
            ->groupBy('warehouse_id', 'status')
            ->with('warehouse')
            ->get();

        return view('admin.inventory.index', compact('summary', 'expiringSoon'));
    }
}
