<?php

namespace App\Services\Sales;

use App\Helpers\Permission;
use App\Models\Order;
use App\Models\User;

class SalesFulfillmentGuide
{
    public const WORKFLOW = ['draft', 'confirmed', 'picked', 'packed', 'dispatched', 'delivered'];

    /**
     * @return array{
     *     guide_steps: list<array<string, mixed>>,
     *     fulfillment_percent: float,
     *     line_fulfillment_percent: int,
     *     workflow_step: int,
     *     in_progress: bool,
     *     show_your_action: bool,
     *     can_confirm: bool,
     *     can_pick: bool,
     *     can_pack: bool,
     *     can_dispatch: bool,
     *     status_label: string,
     *     status_tone: string,
     *     picked_at: ?\Illuminate\Support\Carbon,
     *     packed_at: ?\Illuminate\Support\Carbon,
     *     dispatched_at: ?\Illuminate\Support\Carbon,
     *     delivered_at: ?\Illuminate\Support\Carbon,
     * }
     */
    public function forOrder(Order $order, ?User $user): array
    {
        $isReturn = ($order->order_type ?? null) === 'return';
        $status = $order->status ?? 'draft';

        $history = $order->relationLoaded('statusHistory')
            ? $order->statusHistory
            : collect();

        $pickedAt = $history->firstWhere('status', 'picked')?->changed_at;
        $packedAt = $history->firstWhere('status', 'packed')?->changed_at;
        $dispatchedAt = $history->firstWhere('status', 'dispatched')?->changed_at;
        $deliveredAt = $history->firstWhere('status', 'delivered')?->changed_at;

        $statusLabels = [
            'draft' => 'Draft',
            'confirmed' => 'Confirmed',
            'picked' => 'Picked',
            'packed' => 'Packed',
            'dispatched' => 'Dispatched',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
            'canceled' => 'Cancelled',
        ];

        $statusTones = [
            'draft' => 'neutral',
            'confirmed' => 'brand',
            'picked' => 'brand',
            'packed' => 'warning',
            'dispatched' => 'warning',
            'delivered' => 'success',
            'cancelled' => 'neutral',
            'canceled' => 'neutral',
        ];

        $metrics = $order->relationLoaded('items') && $order->items->isNotEmpty()
            ? SalesFulfillmentMetrics::summarize($order)
            : null;

        return [
            'guide_steps' => $isReturn ? [] : $this->guideSteps($order),
            'fulfillment_percent' => $metrics['fulfillment_percent'] ?? $this->fulfillmentPercent($status),
            'line_fulfillment_percent' => (int) round($metrics['fulfillment_percent'] ?? $this->lineFulfillmentPercent($status)),
            'metrics' => $metrics,
            'workflow_step' => $this->workflowStep($status),
            'in_progress' => $status === 'dispatched',
            'show_your_action' => ! $isReturn && ! in_array($status, ['delivered', 'cancelled', 'canceled'], true),
            'can_confirm' => $this->canConfirm($user),
            'can_pick' => $this->canPick($user),
            'can_pack' => $this->canPack($user),
            'can_dispatch' => $this->canDispatch($user),
            'status_label' => $statusLabels[$status] ?? ucfirst(str_replace('_', ' ', $status)),
            'status_tone' => $statusTones[$status] ?? 'neutral',
            'picked_at' => $pickedAt,
            'packed_at' => $packedAt,
            'dispatched_at' => $dispatchedAt,
            'delivered_at' => $deliveredAt,
        ];
    }

    public function fulfillmentPercent(string $status): float
    {
        $index = array_search($status, self::WORKFLOW, true);
        if ($index === false) {
            return 0.0;
        }

        $max = count(self::WORKFLOW) - 1;

        return $max > 0 ? round(($index / $max) * 100, 1) : 0.0;
    }

    public function lineFulfillmentPercent(string $status): int
    {
        return (int) round($this->fulfillmentPercent($status));
    }

    public function workflowStep(string $status): int
    {
        return match ($status) {
            'draft' => 2,
            'confirmed' => 3,
            'picked' => 4,
            'packed' => 5,
            'dispatched' => 5,
            'delivered' => 6,
            default => 1,
        };
    }

    public function canConfirm(?User $user): bool
    {
        return Permission::can($user, 'sales.manage');
    }

    public function canPick(?User $user): bool
    {
        return $user?->hasAnyRole(['admin', 'super_admin', 'warehouse_officer']) ?? false;
    }

    public function canPack(?User $user): bool
    {
        return $user?->hasAnyRole(['admin', 'super_admin', 'warehouse_officer']) ?? false;
    }

    public function canDispatch(?User $user): bool
    {
        return Permission::can($user, 'sales.delivery.dispatch')
            || Permission::can($user, 'control.warehouses');
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function guideSteps(Order $order): array
    {
        $status = $order->status ?? 'draft';
        $delivery = $order->relationLoaded('deliveries')
            ? $order->deliveries->sortByDesc('created_at')->first()
            : $order->delivery;

        return match ($status) {
            'draft' => [
                ['label' => 'Confirm this order', 'hint' => 'Sales locks the order and reserves stock.', 'state' => 'current', 'action_url' => '#so-confirm', 'action_label' => 'Confirm below'],
                ['label' => 'Pick from warehouse', 'hint' => 'Warehouse pulls reserved stock using the picking list.', 'state' => 'upcoming'],
                ['label' => 'Pack for dispatch', 'hint' => 'Warehouse confirms goods are boxed and ready.', 'state' => 'upcoming'],
                ['label' => 'Deliver to agent', 'hint' => 'Delivery team dispatches and captures POD.', 'state' => 'upcoming'],
            ],
            'confirmed' => [
                ['label' => 'Order confirmed', 'hint' => 'Stock is reserved for this order.', 'state' => 'complete'],
                ['label' => 'Pick from warehouse', 'hint' => 'Open the picking list and confirm when items are pulled.', 'state' => 'current', 'action_url' => route('admin.orders.picking-list', $order), 'action_label' => 'Open picking list'],
                ['label' => 'Pack for dispatch', 'hint' => 'Confirm packing once goods are ready to ship.', 'state' => 'upcoming'],
                ['label' => 'Deliver to agent', 'hint' => 'Schedule delivery and complete POD.', 'state' => 'upcoming'],
            ],
            'picked' => [
                ['label' => 'Order confirmed', 'state' => 'complete'],
                ['label' => 'Items picked', 'hint' => 'Warehouse has pulled stock from the shelf.', 'state' => 'complete'],
                ['label' => 'Confirm packing', 'hint' => 'Mark the order packed when boxes are ready.', 'state' => 'current', 'action_url' => '#so-pack', 'action_label' => 'Confirm packing below'],
                ['label' => 'Deliver to agent', 'hint' => 'Create a delivery and dispatch to the agent.', 'state' => 'upcoming'],
            ],
            'packed' => array_values(array_filter([
                ['label' => 'Order confirmed', 'state' => 'complete'],
                ['label' => 'Items picked', 'state' => 'complete'],
                ['label' => 'Packed for dispatch', 'hint' => 'Goods are boxed and ready for the vehicle.', 'state' => 'complete'],
                $delivery
                    ? ['label' => 'Dispatch & POD', 'hint' => 'Open the linked delivery to dispatch or capture POD.', 'state' => 'current', 'action_url' => route('admin.deliveries.show', $delivery), 'action_label' => 'Open delivery']
                    : ['label' => 'Schedule delivery', 'hint' => 'Create a delivery record for this packed order.', 'state' => 'current', 'action_url' => route('admin.deliveries.create'), 'action_label' => 'Schedule delivery'],
            ])),
            'dispatched' => [
                ['label' => 'Order confirmed', 'state' => 'complete'],
                ['label' => 'Items picked', 'state' => 'complete'],
                ['label' => 'Packed for dispatch', 'state' => 'complete'],
                ['label' => 'Out for delivery', 'hint' => 'Vehicle has left — complete POD when the agent signs.', 'state' => 'complete'],
                ['label' => 'Complete POD', 'hint' => 'Mark delivery as delivered on the delivery screen.', 'state' => 'current', 'action_url' => $delivery ? route('admin.deliveries.show', $delivery) : route('admin.deliveries.pod-index'), 'action_label' => $delivery ? 'Open delivery' : 'Deliveries & POD'],
            ],
            'delivered' => [
                ['label' => 'Order confirmed', 'state' => 'complete'],
                ['label' => 'Items picked', 'state' => 'complete'],
                ['label' => 'Packed for dispatch', 'state' => 'complete'],
                ['label' => 'Delivered to agent', 'hint' => 'POD complete — invoice can be raised.', 'state' => 'complete'],
            ],
            default => [],
        };
    }
}
