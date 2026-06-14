<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FleetExpense;
use App\Models\LogisticsBill;
use App\Models\TransportCarrier;
use App\Models\Vehicle;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class LogisticsDashboardController extends Controller
{
    public function index()
    {
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        $fleetTotal = FleetExpense::whereBetween('expense_date', [$monthStart, $monthEnd])->sum('amount');
        $fleetByType = FleetExpense::query()
            ->whereBetween('expense_date', [$monthStart, $monthEnd])
            ->selectRaw('expense_type, SUM(amount) as total')
            ->groupBy('expense_type')
            ->pluck('total', 'expense_type');

        $carrierBilled = LogisticsBill::whereBetween('bill_date', [$monthStart, $monthEnd])
            ->selectRaw('SUM(net_total + vat_amount) as gross')
            ->value('gross') ?? 0;

        $unpaidCarrier = LogisticsBill::query()
            ->with('payments')
            ->whereIn('status', ['open', 'part_paid'])
            ->get()
            ->sum(fn (LogisticsBill $bill) => $bill->outstanding);

        $activeVehicles = Vehicle::where('is_active', true)->count();
        $carriers = TransportCarrier::active()->count();

        $topVehicles = FleetExpense::query()
            ->whereBetween('expense_date', [$monthStart, $monthEnd])
            ->select('vehicle_id', DB::raw('SUM(amount) as total'))
            ->groupBy('vehicle_id')
            ->orderByDesc('total')
            ->limit(5)
            ->with('vehicle')
            ->get();

        $recentFleet = FleetExpense::with('vehicle')->orderByDesc('expense_date')->limit(5)->get();
        $recentBills = LogisticsBill::with('transportCarrier')->orderByDesc('bill_date')->limit(5)->get();

        return view('admin.logistics.dashboard', compact(
            'fleetTotal',
            'fleetByType',
            'carrierBilled',
            'unpaidCarrier',
            'activeVehicles',
            'carriers',
            'topVehicles',
            'recentFleet',
            'recentBills',
        ));
    }
}
