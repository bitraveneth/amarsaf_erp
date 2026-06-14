<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryRoute;
use App\Models\FleetExpense;
use App\Models\LogisticsBill;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class LogisticsRouteReportController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->query('from')
            ? Carbon::parse($request->query('from'))
            : Carbon::now()->startOfMonth();
        $to = $request->query('to')
            ? Carbon::parse($request->query('to'))
            : Carbon::now()->endOfMonth();

        $routes = DeliveryRoute::orderBy('name')->get();

        $fleetByRoute = FleetExpense::query()
            ->whereBetween('expense_date', [$from, $to])
            ->whereNotNull('delivery_route_id')
            ->select('delivery_route_id', DB::raw('SUM(amount) as total'))
            ->groupBy('delivery_route_id')
            ->pluck('total', 'delivery_route_id');

        $carrierByRoute = LogisticsBill::query()
            ->whereBetween('bill_date', [$from, $to])
            ->whereNotNull('delivery_route_id')
            ->select('delivery_route_id', DB::raw('SUM(net_total + vat_amount) as total'))
            ->groupBy('delivery_route_id')
            ->pluck('total', 'delivery_route_id');

        $revenueByZone = Order::query()
            ->whereBetween('created_at', [$from, $to->copy()->endOfDay()])
            ->whereHas('agent', fn ($q) => $q->whereNotNull('zone'))
            ->join('agents', 'orders.agent_id', '=', 'agents.id')
            ->select('agents.zone as zone', DB::raw('SUM(orders.total) as revenue'))
            ->groupBy('agents.zone')
            ->pluck('revenue', 'zone');

        $rows = $routes->map(function (DeliveryRoute $route) use ($fleetByRoute, $carrierByRoute, $revenueByZone) {
            $fleet = (float) ($fleetByRoute[$route->id] ?? 0);
            $carrier = (float) ($carrierByRoute[$route->id] ?? 0);
            $logistics = $fleet + $carrier;
            $zoneStats = $route->zone ? ($revenueByZone[$route->zone] ?? null) : null;
            $revenue = (float) ($zoneStats ?? 0);

            return [
                'route' => $route,
                'fleet_cost' => $fleet,
                'carrier_cost' => $carrier,
                'logistics_cost' => $logistics,
                'zone_revenue' => $revenue,
                'margin_after_logistics' => $revenue - $logistics,
            ];
        })->sortByDesc('logistics_cost')->values();

        return view('admin.logistics.route_report', compact('rows', 'from', 'to'));
    }
}
