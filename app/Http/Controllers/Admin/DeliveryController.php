<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\DeliveryRoute;
use App\Models\Order;
use App\Models\StockEntry;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DeliveryController extends Controller
{
    public function index()
    {
        $deliveries = Delivery::with(['order.agent', 'route', 'vehicle'])
            ->orderByDesc('created_at')
            ->paginate(12);
        return view('admin.deliveries.index', compact('deliveries'));
    }

    /**
     * Logistics‑focused Deliveries & POD view (Inventory menu).
     */
    public function podIndex()
    {
        $deliveries = Delivery::with(['order.agent', 'route', 'vehicle'])
            ->orderByDesc('created_at')
            ->paginate(12);

        return view('admin.deliveries.pod_index', compact('deliveries'));
    }

    public function packingIndex()
    {
        $deliveries = Delivery::with(['order.agent', 'route', 'vehicle'])
            ->orderByDesc('created_at')
            ->paginate(12);

        return view('admin.deliveries.packing_index', compact('deliveries'));
    }

    public function create()
    {
        $orders = Order::with('agent')->whereIn('status', ['confirmed', 'packed'])->get();
        $routes = DeliveryRoute::orderBy('name')->get();
        $vehicles = Vehicle::orderBy('name')->get();
        return view('admin.deliveries.create', compact('orders', 'routes', 'vehicles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'route_id' => 'nullable|exists:delivery_routes,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'status' => 'required|in:scheduled,in_transit,delivered,exception',
            'exception_notes' => 'nullable|string',
            'pod_photo' => 'nullable|image',
        ]);

        if ($request->hasFile('pod_photo')) {
            $data['pod_photo'] = $request->file('pod_photo')->store('deliveries', 'public');
        }

        Delivery::create($data);

        return redirect()->route('admin.deliveries.index')->with('status', 'Delivery scheduled.');
    }

    public function update(Request $request, Delivery $delivery)
    {
        $data = $request->validate([
            'status' => 'required|in:scheduled,in_transit,delivered,exception',
            'sequence' => 'nullable|integer|min:1',
            'exception_notes' => 'nullable|string',
            'pod_photo' => 'nullable|image',
        ]);

        $originalStatus = $delivery->status;

        if ($request->hasFile('pod_photo')) {
            $data['pod_photo'] = $request->file('pod_photo')->store('deliveries', 'public');
        }

        $delivery->update($data);

        // If the status has just changed to delivered, update the order and adjust stock.
        if ($originalStatus !== 'delivered' && $delivery->status === 'delivered' && $delivery->order) {
            if ($delivery->order->status !== 'delivered') {
                $delivery->order->update(['status' => 'delivered']);
            }

            $order = $delivery->order()->with('items')->first();

            foreach ($order->items as $item) {
                $toShip = (float) $item->quantity;

                if ($toShip <= 0) {
                    continue;
                }

                $reservedEntries = StockEntry::where('product_id', $item->product_id)
                    ->where('status', 'reserved')
                    ->orderBy('created_at')
                    ->get();

                foreach ($reservedEntries as $entry) {
                    if ($toShip <= 0) {
                        break;
                    }

                    $entryQty = (float) $entry->quantity;
                    if ($entryQty <= 0) {
                        continue;
                    }

                    $shipQty = min($toShip, $entryQty);

                    $remaining = $entryQty - $shipQty;

                    if ($remaining <= 0) {
                        // Entire reserved entry has been shipped; remove it.
                        $entry->delete();
                    } else {
                        $entry->quantity = $remaining;
                        $entry->save();
                    }

                    $toShip -= $shipQty;
                }
            }
        }

        return back()->with('status', 'Delivery updated.');
    }

    public function optimize(Request $request)
    {
        $data = $request->validate([
            'date' => 'required|date',
            'vehicle_id' => 'nullable|exists:vehicles,id',
        ]);

        $date = $data['date'];

        $query = Delivery::with('order.agent')
            ->whereDate('created_at', $date);

        if (!empty($data['vehicle_id'])) {
            $query->where('vehicle_id', $data['vehicle_id']);
        }

        $deliveries = $query->get();

        // Simple heuristic: sort by agent zone, then order id and assign sequence
        $sorted = $deliveries->sortBy(function (Delivery $d) {
            $zone = $d->order && $d->order->agent ? ($d->order->agent->zone ?? '') : '';
            return $zone . '|' . str_pad((string)$d->order_id, 6, '0', STR_PAD_LEFT);
        })->values();

        foreach ($sorted as $index => $delivery) {
            $delivery->sequence = $index + 1;
            $delivery->save();
        }

        return redirect()->route('admin.deliveries.index')
            ->with('status', 'Deliveries sequenced for ' . $date . '.');
    }

    public function destroy(Delivery $delivery)
    {
        if ($delivery->pod_photo) {
            Storage::disk('public')->delete($delivery->pod_photo);
        }

        $delivery->delete();

        return redirect()->route('admin.deliveries.index')->with('status', 'Delivery deleted.');
    }

    public function packingSlip(Delivery $delivery)
    {
        $delivery->load('order.items.product', 'route', 'vehicle', 'order.agent');
        return view('admin.deliveries.packing_slip', compact('delivery'));
    }
}
