<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\OrderItem;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    protected function requireEmployee(Request $request)
    {
        $user = $request->user();

        if (! $user->employee) {
            abort(403, 'Employee access required.');
        }

        return $user->employee;
    }

    public function deliveries(Request $request)
    {
        $this->requireEmployee($request);

        $date = $request->query('date')
            ? now()->parse($request->query('date'))
            : now();

        $vehicleId = $request->query('vehicle_id');

        $query = Delivery::with(['order.agent', 'route', 'vehicle'])
            ->whereDate('created_at', $date->toDateString());

        if ($vehicleId) {
            $query->where('vehicle_id', $vehicleId);
        }

        $deliveries = $query->orderBy('sequence')->orderBy('id')->paginate(20);

        return response()->json($deliveries);
    }

    public function show(Request $request, Delivery $delivery)
    {
        $this->requireEmployee($request);

        $delivery->loadMissing('order.items.product.packagingType', 'route', 'vehicle', 'order.agent');

        return response()->json($delivery);
    }

    public function updateStatus(Request $request, Delivery $delivery)
    {
        $this->requireEmployee($request);

        $data = $request->validate([
            'status' => 'required|in:scheduled,in_transit,delivered,exception',
            'exception_notes' => 'nullable|string',
        ]);

        $delivery->status = $data['status'];
        if (! empty($data['exception_notes'])) {
            $delivery->exception_notes = $data['exception_notes'];
        }
        $delivery->save();

        return response()->json($delivery);
    }

    public function uploadPod(Request $request, Delivery $delivery)
    {
        $this->requireEmployee($request);

        $data = $request->validate([
            'pod_photo' => 'required|image|max:8192',
            'exception_notes' => 'nullable|string',
        ]);

        $path = $request->file('pod_photo')->store('deliveries', 'public');

        $delivery->pod_photo = $path;

        if (! empty($data['exception_notes'])) {
            $delivery->status = 'exception';
            $delivery->exception_notes = $data['exception_notes'];
        } else {
            $delivery->status = 'delivered';
        }

        $delivery->save();

        return response()->json($delivery);
    }

    public function vehicleLoad(Request $request)
    {
        $this->requireEmployee($request);

        $date = $request->query('date')
            ? now()->parse($request->query('date'))
            : now();

        $vehicleId = $request->query('vehicle_id');

        if (! $vehicleId) {
            return response()->json([
                'message' => 'vehicle_id query parameter is required',
            ], 422);
        }

        $vehicle = Vehicle::findOrFail($vehicleId);

        $deliveries = Delivery::with('order.items')
            ->whereDate('created_at', $date->toDateString())
            ->where('vehicle_id', $vehicleId)
            ->get();

        $crateLoad = 0;

        foreach ($deliveries as $delivery) {
            foreach ($delivery->order->items as $item) {
                /** @var OrderItem $item */
                $crateLoad += (int) ceil($item->quantity / 12);
            }
        }

        $capacity = $vehicle->capacity_crates ?? null;
        $utilization = $capacity && $capacity > 0
            ? round(($crateLoad / $capacity) * 100, 2)
            : null;

        return response()->json([
            'date' => $date->toDateString(),
            'vehicle' => [
                'id' => $vehicle->id,
                'name' => $vehicle->name,
                'capacity_crates' => $vehicle->capacity_crates,
            ],
            'planned_crates' => $crateLoad,
            'utilization_percent' => $utilization,
        ]);
    }
}

