<?php

namespace App\Services\Sales;

use App\Models\Delivery;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Collection;

class FulfillmentInboxService
{
    public function __construct(
        protected SalesFulfillmentGuide $fulfillmentGuide
    ) {
    }

    /**
     * @return array{
     *     to_confirm: int,
     *     to_pick: int,
     *     to_pack: int,
     *     to_schedule: int,
     *     pod_pending: int,
     *     needs_my_confirm: int,
     *     needs_my_pick: int,
     *     needs_my_pack: int,
     *     needs_my_delivery: int,
     *     needs_my_action: int,
     *     can_confirm: bool,
     *     can_pick: bool,
     *     can_pack: bool,
     *     can_dispatch: bool,
     *     orders_for_me: \Illuminate\Support\Collection,
     * }
     */
    public function forUser(?User $user): array
    {
        $empty = [
            'to_confirm' => 0,
            'to_pick' => 0,
            'to_pack' => 0,
            'to_schedule' => 0,
            'pod_pending' => 0,
            'needs_my_confirm' => 0,
            'needs_my_pick' => 0,
            'needs_my_pack' => 0,
            'needs_my_delivery' => 0,
            'needs_my_action' => 0,
            'can_confirm' => false,
            'can_pick' => false,
            'can_pack' => false,
            'can_dispatch' => false,
            'orders_for_me' => collect(),
        ];

        if (! $user) {
            return $empty;
        }

        $salesQuery = fn () => Order::query()
            ->where('order_type', '!=', 'return');

        $toConfirm = (clone $salesQuery())->where('status', 'draft')->count();
        $toPick = (clone $salesQuery())->where('status', 'confirmed')->count();
        $toPack = (clone $salesQuery())->where('status', 'picked')->count();
        $toSchedule = (clone $salesQuery())
            ->where('status', 'packed')
            ->whereDoesntHave('deliveries')
            ->count();
        $podPending = Delivery::query()
            ->whereIn('status', ['scheduled', 'in_transit'])
            ->whereHas('order', fn ($query) => $query
                ->where('order_type', '!=', 'return')
                ->whereIn('status', ['packed', 'dispatched']))
            ->count();

        $canConfirm = $this->fulfillmentGuide->canConfirm($user);
        $canPick = $this->fulfillmentGuide->canPick($user);
        $canPack = $this->fulfillmentGuide->canPack($user);
        $canDispatch = $this->fulfillmentGuide->canDispatch($user);

        $needsConfirm = $canConfirm ? $toConfirm : 0;
        $needsPick = $canPick ? $toPick : 0;
        $needsPack = $canPack ? $toPack : 0;
        $needsDelivery = $canDispatch ? ($toSchedule + $podPending) : 0;

        $ordersForMe = $this->ordersNeedingAttention($user, $canConfirm, $canPick, $canPack, $canDispatch);

        return [
            'to_confirm' => $toConfirm,
            'to_pick' => $toPick,
            'to_pack' => $toPack,
            'to_schedule' => $toSchedule,
            'pod_pending' => $podPending,
            'needs_my_confirm' => $needsConfirm,
            'needs_my_pick' => $needsPick,
            'needs_my_pack' => $needsPack,
            'needs_my_delivery' => $needsDelivery,
            'needs_my_action' => $needsConfirm + $needsPick + $needsPack + $needsDelivery,
            'can_confirm' => $canConfirm,
            'can_pick' => $canPick,
            'can_pack' => $canPack,
            'can_dispatch' => $canDispatch,
            'orders_for_me' => $ordersForMe,
        ];
    }

    protected function ordersNeedingAttention(
        User $user,
        bool $canConfirm,
        bool $canPick,
        bool $canPack,
        bool $canDispatch
    ): Collection {
        $orders = collect();

        if ($canConfirm) {
            $orders = $orders->merge(
                Order::with('agent')
                    ->where('order_type', '!=', 'return')
                    ->where('status', 'draft')
                    ->latest()
                    ->limit(4)
                    ->get()
                    ->map(fn (Order $order) => ['order' => $order, 'action' => 'confirm'])
            );
        }

        if ($canPick) {
            $orders = $orders->merge(
                Order::with('agent')
                    ->where('order_type', '!=', 'return')
                    ->where('status', 'confirmed')
                    ->latest()
                    ->limit(4)
                    ->get()
                    ->map(fn (Order $order) => ['order' => $order, 'action' => 'pick'])
            );
        }

        if ($canPack) {
            $orders = $orders->merge(
                Order::with('agent')
                    ->where('order_type', '!=', 'return')
                    ->where('status', 'picked')
                    ->latest()
                    ->limit(4)
                    ->get()
                    ->map(fn (Order $order) => ['order' => $order, 'action' => 'pack'])
            );
        }

        if ($canDispatch) {
            $orders = $orders->merge(
                Order::with('agent')
                    ->where('order_type', '!=', 'return')
                    ->where('status', 'packed')
                    ->whereDoesntHave('deliveries')
                    ->latest()
                    ->limit(4)
                    ->get()
                    ->map(fn (Order $order) => ['order' => $order, 'action' => 'schedule'])
            );
        }

        return $orders->unique(fn ($row) => $row['order']->id . '-' . $row['action'])->values();
    }
}
