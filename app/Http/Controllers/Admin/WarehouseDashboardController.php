<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\DeliveryRoute;
use App\Models\Order;
use App\Models\StockEntry;
use App\Models\Vehicle;
use App\Models\Warehouse;
use App\Models\WarehouseLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class WarehouseDashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $warehouseIds = auth()->user()?->accessibleWarehouseIds();

        $warehouseCount = Warehouse::query()
            ->when($warehouseIds !== null, fn ($q) => $q->whereIn('id', $warehouseIds))
            ->count();

        $locationCount = WarehouseLocation::query()
            ->when($warehouseIds !== null, fn ($q) => $q->whereIn('warehouse_id', $warehouseIds))
            ->count();

        $activeVehicles = Vehicle::where('is_active', true)->count();
        $routeCount = DeliveryRoute::count();
        $routesWithoutVehicle = DeliveryRoute::whereNull('vehicle_id')->count();

        $inTransit = Delivery::where('status', 'in_transit')->count();
        $scheduled = Delivery::where('status', 'scheduled')->count();
        $exceptions = Delivery::where('status', 'exception')->count();
        $activeDeliveries = $inTransit + $scheduled;
        $deliveredToday = Delivery::where('status', 'delivered')
            ->whereDate('updated_at', today())
            ->count();

        $totalStock = (float) StockEntry::query()
            ->where('status', 'available')
            ->when($warehouseIds !== null, fn ($q) => $q->whereIn('warehouse_id', $warehouseIds))
            ->sum('quantity');

        $snapshotCards = [
            [
                'label' => 'Warehouses',
                'numeric' => number_format($warehouseCount),
                'caption' => 'Registered sites and depots',
                'href' => route('admin.warehouses.index'),
                'tone' => 'brand',
                'valueTone' => 'neutral',
                'icon' => 'delivery',
            ],
            [
                'label' => 'Bin locations',
                'numeric' => number_format($locationCount),
                'caption' => 'Storage locations inside sites',
                'href' => route('admin.warehouse-locations.index'),
                'tone' => 'blue',
                'valueTone' => 'neutral',
                'icon' => 'production',
            ],
            [
                'label' => 'Active vehicles',
                'numeric' => number_format($activeVehicles),
                'caption' => 'Fleet ready for dispatch',
                'href' => route('admin.vehicles.index'),
                'tone' => 'success',
                'valueTone' => 'neutral',
                'icon' => 'delivery',
            ],
            [
                'label' => 'Delivery routes',
                'numeric' => number_format($routeCount),
                'caption' => 'Zones and default vehicles',
                'href' => route('admin.delivery-routes.index'),
                'tone' => 'purple',
                'valueTone' => 'neutral',
                'icon' => 'orders',
            ],
            [
                'label' => 'In transit',
                'numeric' => number_format($inTransit),
                'caption' => 'Deliveries on the road now',
                'href' => route('admin.deliveries.pod-index'),
                'tone' => 'blue',
                'valueTone' => $inTransit > 0 ? 'brand' : 'neutral',
                'icon' => 'delivery',
            ],
            [
                'label' => 'Scheduled',
                'numeric' => number_format($scheduled),
                'caption' => 'Waiting to depart',
                'href' => route('admin.deliveries.index'),
                'tone' => 'amber',
                'valueTone' => $scheduled > 0 ? 'warning' : 'neutral',
                'icon' => 'orders',
            ],
            [
                'label' => 'Exceptions',
                'numeric' => number_format($exceptions),
                'caption' => 'Deliveries needing review',
                'href' => route('admin.deliveries.pod-index'),
                'tone' => 'error',
                'valueTone' => $exceptions > 0 ? 'danger' : 'neutral',
                'icon' => 'alert',
            ],
            [
                'label' => 'Stock on hand',
                'numeric' => number_format($totalStock, 0),
                'caption' => 'Available units across sites',
                'href' => route('admin.warehouses.index'),
                'tone' => 'orange',
                'valueTone' => 'neutral',
                'icon' => 'production',
            ],
        ];

        $takeActionGroups = [
            [
                'label' => 'Dispatch & shipment',
                'actions' => [
                    ['label' => 'Schedule delivery', 'hint' => 'New run', 'href' => route('admin.deliveries.create'), 'primary' => true, 'icon' => 'schedule'],
                    ['label' => 'Deliveries & POD', 'hint' => 'Live board', 'href' => route('admin.deliveries.pod-index'), 'badge' => $activeDeliveries, 'icon' => 'delivery'],
                    ['label' => 'Vehicle load', 'hint' => 'Crate plan', 'href' => route('admin.vehicle-load.index'), 'icon' => 'load'],
                    ['label' => 'Packing slips', 'hint' => 'Print docs', 'href' => route('admin.deliveries.packing-index'), 'icon' => 'slip'],
                ],
            ],
            [
                'label' => 'Fleet & routes',
                'actions' => [
                    ['label' => 'Delivery routes', 'hint' => 'Zones', 'href' => route('admin.delivery-routes.index'), 'icon' => 'route'],
                    ['label' => 'Fleet registry', 'hint' => 'Vehicles', 'href' => route('admin.vehicles.index'), 'icon' => 'fleet'],
                    ['label' => 'Add vehicle', 'hint' => 'New truck', 'href' => route('admin.vehicles.create'), 'icon' => 'add'],
                ],
            ],
            [
                'label' => 'Sites & storage',
                'actions' => [
                    ['label' => 'New warehouse', 'hint' => 'Add site', 'href' => route('admin.warehouses.create'), 'icon' => 'warehouse'],
                    ['label' => 'All warehouses', 'hint' => 'Manage', 'href' => route('admin.warehouses.index'), 'icon' => 'sites'],
                    ['label' => 'Bin locations', 'hint' => 'Racks', 'href' => route('admin.warehouse-locations.index'), 'icon' => 'bins'],
                ],
            ],
        ];

        $recentDeliveries = Delivery::with(['order.agent', 'route', 'vehicle'])
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get();

        $warehouseStockBreakdown = $this->buildWarehouseStockBreakdown($warehouseIds);
        $todayDispatchBoard = $this->buildTodayDispatchBoard();

        $currencyCode = config('app.currency', 'BDT');

        return view('admin.warehouses.dashboard', compact(
            'snapshotCards',
            'takeActionGroups',
            'todayDispatchBoard',
            'recentDeliveries',
            'warehouseStockBreakdown',
            'totalStock',
            'warehouseCount',
            'currencyCode',
        ));
    }

    private function buildTodayDispatchBoard(): array
    {
        $today = today()->toDateString();

        $deliveries = Delivery::with(['order.agent', 'route.vehicle', 'vehicle'])
            ->whereIn('status', ['scheduled', 'in_transit', 'exception'])
            ->where(function ($builder) use ($today) {
                $builder->whereHas('order', function ($orderQuery) use ($today) {
                    $orderQuery->where(function ($dateQuery) use ($today) {
                        $dateQuery->whereDate('delivery_date', '<=', $today)
                            ->orWhereNull('delivery_date');
                    });
                })->orWhere(function ($legacyQuery) use ($today) {
                    $legacyQuery->whereDate('created_at', $today)
                        ->whereHas('order', function ($orderQuery) {
                            $orderQuery->whereNull('delivery_date');
                        });
                });
            })
            ->orderBy('sequence')
            ->orderBy('id')
            ->get();

        $groups = $deliveries
            ->groupBy(fn (Delivery $delivery) => ($delivery->route_id ?? 'none') . '|' . ($delivery->vehicle_id ?? 'none'))
            ->map(function (Collection $group) use ($today) {
                $first = $group->first();
                $statusCounts = $group->countBy('status');
                $vehicle = $first->vehicle ?? $first->route?->vehicle;

                return [
                    'route_name' => $first->route?->name ?? 'No route assigned',
                    'route_zone' => $first->route?->zone,
                    'vehicle_name' => $vehicle?->name ?? 'No vehicle assigned',
                    'vehicle_plate' => $vehicle?->license_plate,
                    'counts' => [
                        'scheduled' => (int) $statusCounts->get('scheduled', 0),
                        'in_transit' => (int) $statusCounts->get('in_transit', 0),
                        'delivered' => (int) $statusCounts->get('delivered', 0),
                        'exception' => (int) $statusCounts->get('exception', 0),
                    ],
                    'stops' => $group->map(function (Delivery $delivery) use ($today) {
                        $dueDate = $delivery->order?->delivery_date?->toDateString();

                        return [
                            'id' => $delivery->id,
                            'order_id' => $delivery->order_id,
                            'agent' => $delivery->order?->agent?->name,
                            'status' => $delivery->status,
                            'sequence' => $delivery->sequence,
                            'due_label' => $dueDate
                                ? ($dueDate < $today ? 'Overdue' : ($dueDate === $today ? 'Today' : $delivery->order->delivery_date->format('d M')))
                                : null,
                            'href' => route('admin.deliveries.show', $delivery),
                        ];
                    })->values()->all(),
                    'total' => $group->count(),
                ];
            })
            ->sortBy('route_name')
            ->values()
            ->all();

        $unscheduledQuery = Order::with('agent')
            ->whereDate('delivery_date', $today)
            ->whereDoesntHave('deliveries')
            ->where('order_type', '!=', 'return')
            ->whereIn('status', ['confirmed', 'picked', 'packed', 'dispatched']);

        $unscheduledTotal = (clone $unscheduledQuery)->count();

        $unscheduled = $unscheduledQuery
            ->orderBy('id')
            ->limit(8)
            ->get()
            ->map(fn (Order $order) => [
                'order_id' => $order->id,
                'agent' => $order->agent?->name,
                'status' => $order->status,
                'due_label' => 'Today',
                'href' => route('admin.deliveries.create'),
            ]);

        return [
            'date_label' => today()->format('l, j F Y'),
            'groups' => $groups,
            'unscheduled' => $unscheduled,
            'totals' => [
                'deliveries' => $deliveries->count(),
                'routes' => count($groups),
                'unscheduled' => $unscheduledTotal,
            ],
        ];
    }

    private function buildWarehouseStockBreakdown(?array $warehouseIds): Collection
    {
        $warehouses = Warehouse::query()
            ->when($warehouseIds !== null, fn ($q) => $q->whereIn('id', $warehouseIds))
            ->orderBy('name')
            ->get();

        $entries = StockEntry::query()
            ->with('product:id,product_type')
            ->where('status', 'available')
            ->where('quantity', '>', 0)
            ->when($warehouseIds !== null, fn ($q) => $q->whereIn('warehouse_id', $warehouseIds))
            ->get()
            ->groupBy('warehouse_id');

        return $warehouses->map(function (Warehouse $warehouse) use ($entries) {
            $warehouseEntries = $entries->get($warehouse->id, collect());

            $finishedQty = (float) $warehouseEntries
                ->filter(fn (StockEntry $entry) => $this->stockTypeBucket($entry->product?->product_type) === 'finished')
                ->sum('quantity');

            $rawQty = (float) $warehouseEntries
                ->filter(fn (StockEntry $entry) => $this->stockTypeBucket($entry->product?->product_type) === 'raw')
                ->sum('quantity');

            $otherQty = (float) $warehouseEntries
                ->filter(fn (StockEntry $entry) => $this->stockTypeBucket($entry->product?->product_type) === 'other')
                ->sum('quantity');

            $totalQty = $finishedQty + $rawQty + $otherQty;

            $type = $warehouse->type ?? 'depot';

            $tone = match ($type) {
                'factory' => 'brand',
                'depot' => 'blue',
                'consignment' => 'purple',
                'returns' => 'orange',
                default => 'brand',
            };

            $icon = match ($type) {
                'factory' => 'production',
                'depot' => 'delivery',
                'consignment' => 'orders',
                'returns' => 'returns',
                default => 'production',
            };

            return [
                'id' => $warehouse->id,
                'name' => $warehouse->name,
                'type' => $type,
                'tone' => $tone,
                'icon' => $icon,
                'href' => route('admin.warehouses.show', $warehouse),
                'total_qty' => $totalQty,
                'finished_qty' => $finishedQty,
                'raw_qty' => $rawQty,
                'other_qty' => $otherQty,
                'finished_pct' => $totalQty > 0 ? round(($finishedQty / $totalQty) * 100) : 0,
                'raw_pct' => $totalQty > 0 ? round(($rawQty / $totalQty) * 100) : 0,
                'other_pct' => $totalQty > 0 ? round(($otherQty / $totalQty) * 100) : 0,
                'materials_href' => $type === 'factory'
                    ? route('admin.inventory.materials', ['warehouse_id' => $warehouse->id])
                    : null,
            ];
        });
    }

    private function stockTypeBucket(?string $productType): string
    {
        return match ($productType) {
            'raw' => 'raw',
            'inhouse', 'service' => 'other',
            default => 'finished',
        };
    }

}
