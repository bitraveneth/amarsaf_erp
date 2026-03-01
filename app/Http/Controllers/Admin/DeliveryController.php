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
use Illuminate\Support\Facades\DB;

class DeliveryController extends Controller
{
    public function index()
    {
        $deliveries = Delivery::with(['order.agent', 'route', 'vehicle', 'pod'])
            ->orderByDesc('created_at')
            ->paginate(12);
        return view('admin.deliveries.index', compact('deliveries'));
    }

    /**
     * Logistics‑focused Deliveries & POD view (Inventory menu).
     */
    public function podIndex()
    {
        $deliveries = Delivery::with(['order.agent', 'route', 'vehicle', 'pod'])
            ->orderByDesc('created_at')
            ->paginate(12);

        return view('admin.deliveries.pod_index', compact('deliveries'));
    }

    public function packingIndex()
    {
        $deliveries = Delivery::with(['order.agent', 'route', 'vehicle', 'pod'])
            ->whereIn('status', ['scheduled', 'in_transit'])
            ->orderByDesc('created_at')
            ->paginate(12);

        return view('admin.deliveries.packing_index', compact('deliveries'));
    }

    public function create()
    {
        // Deliveries are usually scheduled once picking is done. We allow both
        // "picked" (ready to be packed) and "packed" (fully packed) orders so
        // that packing can be confirmed from the packing slips screen.
        $orders = Order::with('agent')
            ->whereIn('status', ['picked', 'packed'])
            ->get();
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

        $delivery = Delivery::create($data);

        // Log a status event on the order timeline so users can see
        // when delivery scheduling happened in relation to picking/packing.
        $order = $delivery->order;
        if ($order) {
            \App\Models\OrderStatusHistory::create([
                'order_id'   => $order->id,
                'status'     => 'delivery_scheduled',
                'changed_at' => now(),
            ]);
        }

        return redirect()->route('admin.deliveries.pod-index')->with('status', 'Delivery scheduled.');
    }

    public function edit(Delivery $delivery)
    {
        $delivery->load('order.agent', 'order.items.product', 'route', 'vehicle', 'pod', 'items');
        $routes = DeliveryRoute::orderBy('name')->get();
        $vehicles = Vehicle::orderBy('name')->get();

        return view('admin.deliveries.edit', compact('delivery', 'routes', 'vehicles'));
    }

    public function update(Request $request, Delivery $delivery)
    {
        $data = $request->validate([
            'route_id' => 'nullable|exists:delivery_routes,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'status' => 'required|in:scheduled,in_transit,delivered,exception',
            'sequence' => 'nullable|integer|min:1',
            'exception_notes' => 'nullable|string',
            'pod_photo' => 'nullable|image',
            'pod_signed_by' => 'nullable|string|max:255',
            'pod_receiver_name' => 'nullable|string|max:255',
            'pod_receiver_phone' => 'nullable|string|max:50',
            'pod_notes' => 'nullable|string',
            'pod_delivered_at' => 'nullable|date',
            'pod_latitude' => 'nullable|numeric|between:-90,90',
            'pod_longitude' => 'nullable|numeric|between:-180,180',
            'items' => 'nullable|array',
            'items.*.order_item_id' => 'required_with:items|exists:order_items,id',
            'items.*.product_id' => 'required_with:items|exists:products,id',
            'items.*.batch_id' => 'nullable|exists:batches,id',
            'items.*.qty_dispatched' => 'nullable|numeric|min:0',
            'items.*.qty_delivered' => 'nullable|numeric|min:0',
            'items.*.qty_short' => 'nullable|numeric|min:0',
            'items.*.qty_damaged' => 'nullable|numeric|min:0',
            'items.*.notes' => 'nullable|string',
        ]);

        $originalStatus = $delivery->status;

        if ($request->hasFile('pod_photo')) {
            $data['pod_photo'] = $request->file('pod_photo')->store('deliveries', 'public');
        }

        DB::transaction(function () use ($delivery, $data, $originalStatus) {
            $delivery->update([
                'route_id' => $data['route_id'] ?? null,
                'vehicle_id' => $data['vehicle_id'] ?? null,
                'status' => $data['status'],
                'sequence' => $data['sequence'] ?? $delivery->sequence,
                'exception_notes' => $data['exception_notes'] ?? null,
                'pod_photo' => $data['pod_photo'] ?? $delivery->pod_photo,
            ]);

            $deliveryItems = $this->syncDeliveryItems($delivery, $data);
            $this->syncDeliveryPod($delivery, $data);

            $order = $delivery->order;

            // If the status has just changed to in_transit, mark the order as dispatched.
            if ($originalStatus !== 'in_transit'
                && $delivery->status === 'in_transit'
                && $order
                && $order->status !== 'dispatched') {

                $order->update(['status' => 'dispatched']);

                \App\Models\OrderStatusHistory::create([
                    'order_id'   => $order->id,
                    'status'     => 'dispatched',
                    'changed_at' => now(),
                ]);
            }

            // If the status has just changed to delivered, update the order and adjust stock.
            if ($originalStatus !== 'delivered' && $delivery->status === 'delivered' && $order) {
                if ($order->status !== 'delivered') {
                    $order->update(['status' => 'delivered']);
                }

                \App\Models\OrderStatusHistory::create([
                    'order_id'   => $order->id,
                    'status'     => 'delivered',
                    'changed_at' => now(),
                ]);

                $order = $delivery->order()->with('items')->first();

                foreach ($order->items as $item) {
                    $line = $deliveryItems->firstWhere('order_item_id', $item->id);
                    $toShip = $line
                        ? (float) $line->qty_dispatched
                        : (float) $item->quantity;

                    if ($toShip <= 0) {
                        continue;
                    }

                    $reservedEntries = StockEntry::where('order_id', $order->id)
                        ->where('product_id', $item->product_id)
                        ->where('status', 'reserved')
                        ->orderBy('created_at')
                        ->lockForUpdate()
                        ->get();

                    // Backward compatibility for older rows created before order_id was added.
                    if ($reservedEntries->isEmpty()) {
                        $reservedEntries = StockEntry::whereNull('order_id')
                            ->where('product_id', $item->product_id)
                            ->where('status', 'reserved')
                            ->orderBy('created_at')
                            ->lockForUpdate()
                            ->get();
                    }

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
        });

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
        $delivery->load('order.items.product', 'route', 'vehicle', 'order.agent', 'pod', 'items.product');
        return view('admin.deliveries.packing_slip', compact('delivery'));
    }

    protected function syncDeliveryItems(Delivery $delivery, array $data)
    {
        $rows = collect($data['items'] ?? [])->filter(function ($row) {
            return isset($row['order_item_id'], $row['product_id']);
        });

        if ($rows->isEmpty()) {
            $order = $delivery->order()->with('items')->first();
            $rows = collect($order?->items ?? [])->map(function ($item) {
                $qty = (float) $item->quantity;
                return [
                    'order_item_id' => $item->id,
                    'product_id' => $item->product_id,
                    'batch_id' => null,
                    'qty_dispatched' => $qty,
                    'qty_delivered' => $qty,
                    'qty_short' => 0,
                    'qty_damaged' => 0,
                    'notes' => null,
                ];
            });
        }

        $delivery->items()->delete();

        foreach ($rows as $row) {
            $qtyDelivered = isset($row['qty_delivered']) ? (float) $row['qty_delivered'] : 0;
            $qtyShort = isset($row['qty_short']) ? (float) $row['qty_short'] : 0;
            $qtyDamaged = isset($row['qty_damaged']) ? (float) $row['qty_damaged'] : 0;
            $qtyDispatched = isset($row['qty_dispatched'])
                ? (float) $row['qty_dispatched']
                : ($qtyDelivered + $qtyShort + $qtyDamaged);

            if ($qtyDispatched <= 0 && ($qtyDelivered + $qtyShort + $qtyDamaged) > 0) {
                $qtyDispatched = $qtyDelivered + $qtyShort + $qtyDamaged;
            }

            $delivery->items()->create([
                'order_item_id' => $row['order_item_id'],
                'product_id' => $row['product_id'],
                'batch_id' => $row['batch_id'] ?? null,
                'qty_dispatched' => $qtyDispatched,
                'qty_delivered' => $qtyDelivered,
                'qty_short' => $qtyShort,
                'qty_damaged' => $qtyDamaged,
                'notes' => $row['notes'] ?? null,
            ]);
        }

        return $delivery->items()->get();
    }

    protected function syncDeliveryPod(Delivery $delivery, array $data): void
    {
        $podPayload = [
            'signed_by' => $data['pod_signed_by'] ?? null,
            'signature_path' => $data['pod_photo'] ?? $delivery->pod?->signature_path,
            'receiver_name' => $data['pod_receiver_name'] ?? null,
            'receiver_phone' => $data['pod_receiver_phone'] ?? null,
            'notes' => $data['pod_notes'] ?? null,
            'delivered_at' => $data['pod_delivered_at'] ?? null,
            'latitude' => $data['pod_latitude'] ?? null,
            'longitude' => $data['pod_longitude'] ?? null,
        ];

        $shouldPersist = collect($podPayload)->filter(function ($value) {
            return $value !== null && $value !== '';
        })->isNotEmpty() || $delivery->status === 'delivered';

        if (! $shouldPersist) {
            return;
        }

        if (empty($podPayload['delivered_at']) && $delivery->status === 'delivered') {
            $podPayload['delivered_at'] = now();
        }

        $delivery->pod()->updateOrCreate([], $podPayload);
    }
}
