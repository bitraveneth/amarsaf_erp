<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class VehicleLoadController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->query('date')
            ? Carbon::parse($request->query('date'))
            : Carbon::today();

        $deliveries = Delivery::with('order.items', 'vehicle')
            ->where(function ($query) use ($date) {
                $query->whereHas('order', function ($orderQuery) use ($date) {
                    $orderQuery->whereDate('delivery_date', $date);
                })->orWhere(function ($legacyQuery) use ($date) {
                    $legacyQuery->whereDate('created_at', $date)
                        ->whereHas('order', function ($orderQuery) {
                            $orderQuery->whereNull('delivery_date');
                        });
                });
            })
            ->get();

        $byVehicle = [];

        foreach ($deliveries as $delivery) {
            if (!$delivery->vehicle) {
                continue;
            }
            $vid = $delivery->vehicle->id;
            if (!isset($byVehicle[$vid])) {
                $byVehicle[$vid] = [
                    'vehicle' => $delivery->vehicle,
                    'crateLoad' => 0,
                    'deliveries' => [],
                ];
            }

            $crateEstimate = 0;
            foreach ($delivery->order->items as $item) {
                $crateEstimate += ceil($item->quantity / 12);
            }

            $byVehicle[$vid]['crateLoad'] += $crateEstimate;
            $byVehicle[$vid]['deliveries'][] = [
                'delivery' => $delivery,
                'crates' => $crateEstimate,
            ];
        }

        return view('admin.deliveries.load', [
            'date' => $date,
            'rows' => $byVehicle,
        ]);
    }
}
