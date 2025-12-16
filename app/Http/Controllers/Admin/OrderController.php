<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\AgentPriceList;
use App\Models\AgentCommissionRule;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Invoice;
use App\Models\Delivery;
use App\Models\StockMovement;
use App\Models\Product;
use App\Models\StockEntry;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with('agent')->latest()->paginate(10);
        return view('admin.orders.index', compact('orders'));
    }

    public function pickingOverview()
    {
        $orders = Order::with('agent')
            ->where('status', '!=', 'draft')
            ->latest()
            ->paginate(10);

        return view('admin.orders.picking_overview', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load(['agent', 'items.product.taxClass', 'statusHistory']);
        return view('admin.orders.show', compact('order'));
    }

    public function edit(Order $order)
    {
        $order->load(['agent', 'items.product']);
        return view('admin.orders.edit', compact('order'));
    }

    public function pickingList(Order $order)
    {
        $order->load('agent', 'items.product', 'delivery');

        $lines = [];

        foreach ($order->items as $item) {
            if (!$item->product) {
                continue;
            }

            $remaining = $item->quantity;

            $entries = StockEntry::with('warehouse', 'batch')
                ->where('product_id', $item->product_id)
                ->where('status', 'available')
                ->get()
                ->sortBy(function ($entry) {
                    if ($entry->batch && $entry->batch->expiry_date) {
                        return $entry->batch->expiry_date;
                    }
                    if ($entry->batch && $entry->batch->production_date) {
                        return $entry->batch->production_date;
                    }
                    return $entry->created_at;
                });

            foreach ($entries as $entry) {
                if ($remaining <= 0) {
                    break;
                }

                $pickQty = min($remaining, (int) $entry->quantity);
                if ($pickQty <= 0) {
                    continue;
                }

                $lines[] = [
                    'sku' => $item->product->sku ?? '',
                    'name' => $item->product->name ?? '',
                    'quantity' => $pickQty,
                    'warehouse' => $entry->warehouse->name ?? null,
                    'batch' => $entry->batch->batch_code ?? null,
                ];

                $remaining -= $pickQty;
            }

            if ($remaining > 0) {
                $lines[] = [
                    'sku' => $item->product->sku ?? '',
                    'name' => $item->product->name ?? '',
                    'quantity' => $remaining,
                    'warehouse' => 'Unassigned (shortage)',
                    'batch' => null,
                ];
            }
        }

        return view('admin.orders.picking_list', compact('order', 'lines'));
    }

    public function create()
    {
        $agents = Agent::orderBy('name')->get();
        $products = Product::orderBy('name')->get();

        $priceLists = AgentPriceList::all()
            ->groupBy('agent_id')
            ->map(function ($rows) {
                return $rows->pluck('price', 'product_id');
            });

        return view('admin.orders.create', [
            'agents' => $agents,
            'products' => $products,
            'priceLists' => $priceLists,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'agent_id' => 'required|exists:agents,id',
            'order_type' => 'required|in:regular,bulk,sample,return',
            'delivery_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $order = Order::create([
            'agent_id' => $data['agent_id'],
            'order_type' => $data['order_type'],
            'delivery_date' => $data['delivery_date'],
            'status' => 'confirmed',
            'total' => 0,
            'notes' => $data['notes'] ?? null,
        ]);

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'status' => $order->status,
            'changed_at' => now(),
        ]);

        $agentPrices = AgentPriceList::where('agent_id', $data['agent_id'])
            ->pluck('price', 'product_id');
        $products = Product::whereIn('id', collect($data['items'])->pluck('product_id'))
            ->get()
            ->keyBy('id');
        $commissionRules = AgentCommissionRule::where('agent_id', $data['agent_id'])->get();

        $total = 0;
        $commissionTotal = 0;

        foreach ($data['items'] as $item) {
            $productId = $item['product_id'];
            $quantity = $item['quantity'];
            $unitPrice = $agentPrices[$productId] ?? $item['unit_price'];
            $lineTotal = $quantity * $unitPrice;
            $total += $lineTotal;

            $product = $products[$productId] ?? null;
            $sku = $product ? $product->sku : null;

            $matchingRules = $commissionRules->filter(function ($rule) use ($sku, $data) {
                if ($rule->frequency !== 'per_order') {
                    return false;
                }

                if ($rule->sku && $sku && $rule->sku !== $sku) {
                    return false;
                }

                if ($rule->order_type && $rule->order_type !== $data['order_type']) {
                    return false;
                }

                return true;
            });

            $lineCommission = 0;
            foreach ($matchingRules as $rule) {
                if ($rule->type === 'percentage') {
                    $lineCommission += ($lineTotal * ($rule->value / 100));
                } elseif ($rule->type === 'fixed') {
                    $lineCommission += $rule->value;
                }
            }

            $commissionTotal += $lineCommission;

            $commissionRate = $lineTotal > 0 ? ($lineCommission / $lineTotal) * 100 : null;

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $productId,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'order_type' => $data['order_type'],
                'commission_rate' => $commissionRate,
                'commission_amount' => $lineCommission,
            ]);

            $toReserve = $item['quantity'];
            $entries = StockEntry::where('product_id', $item['product_id'])
                ->where('status', 'available')
                ->orderBy('created_at')
                ->get();
            foreach ($entries as $entry) {
                if ($toReserve <= 0) {
                    break;
                }

                $reserved = min($entry->quantity, $toReserve);
                $entry->quantity = $entry->quantity - $reserved;
                if ($entry->quantity == 0) {
                    $entry->status = 'reserved';
                }
                $entry->save();
                StockEntry::create([
                    'warehouse_id' => $entry->warehouse_id,
                    'product_id' => $entry->product_id,
                    'batch_id' => $entry->batch_id,
                    'quantity' => $reserved,
                    'status' => 'reserved',
                ]);
                $toReserve -= $reserved;
            }
        }

        $order->update(['total' => $total]);

        if ($commissionTotal > 0) {
            $order->update(['commission_total' => $commissionTotal]);
        }

        return redirect()->route('admin.orders.index')->with('status', 'Order created.');
    }

    public function updateStatus(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => 'required|in:draft,confirmed,packed,dispatched,delivered',
        ]);

        $order->update(['status' => $data['status']]);

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'status' => $order->status,
            'changed_at' => now(),
        ]);

        return redirect()->route('admin.orders.show', $order)->with('status', 'Order status updated.');
    }

    public function update(Request $request, Order $order)
    {
        $data = $request->validate([
            'delivery_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $order->update($data);

        return redirect()->route('admin.orders.index')->with('status', 'Order updated.');
    }

    public function destroy(Order $order)
    {
        $hasInvoice = Invoice::where('order_id', $order->id)->exists();

        if ($hasInvoice) {
            return redirect()->route('admin.orders.index')
                ->with('status', 'Order has an invoice and cannot be deleted. Use credit notes instead.');
        }

        // Delete dependent records first to satisfy foreign key constraints
        Delivery::where('order_id', $order->id)->delete();
        StockMovement::where('order_id', $order->id)->delete();
        $order->items()->delete();
        $order->statusHistory()->delete();

        $order->delete();

        return redirect()->route('admin.orders.index')->with('status', 'Order deleted.');
    }
}
