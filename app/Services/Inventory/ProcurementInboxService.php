<?php

namespace App\Services\Inventory;

use App\Helpers\Permission;
use App\Models\GoodsReceipt;
use App\Models\User;
use Illuminate\Support\Collection;

class ProcurementInboxService
{
    public function __construct(
        protected GrnWorkflowService $grnWorkflow
    ) {
    }

    /**
     * @return array{
     *     awaiting_receipt: int,
     *     pending_grn: int,
     *     needs_my_warehouse_approval: int,
     *     needs_my_procurement_approval: int,
     *     needs_my_approval: int,
     *     can_create_grn: bool,
     *     can_approve_warehouse: bool,
     *     can_approve_procurement: bool,
     *     grns_needing_my_approval: \Illuminate\Support\Collection
     * }
     */
    public function forUser(?User $user): array
    {
        $empty = [
            'awaiting_receipt' => 0,
            'pending_grn' => 0,
            'needs_my_warehouse_approval' => 0,
            'needs_my_procurement_approval' => 0,
            'needs_my_approval' => 0,
            'can_create_grn' => false,
            'can_approve_warehouse' => false,
            'can_approve_procurement' => false,
            'grns_needing_my_approval' => collect(),
        ];

        if (! $user) {
            return $empty;
        }

        $canCreate = Permission::can($user, 'inventory.grn.create')
            || Permission::can($user, 'control.suppliers');
        $canWarehouse = Permission::can($user, 'inventory.grn.approve.warehouse');
        $canProcurement = Permission::can($user, 'purchase.grn.approve');

        $warehouseIds = $user->accessibleWarehouseIds();

        $awaitingReceipt = $canCreate
            ? $this->grnWorkflow->pendingReceiptPurchaseOrders($warehouseIds)->count()
            : 0;

        $pendingQuery = GoodsReceipt::query()
            ->where('status', 'pending_approval')
            ->when($warehouseIds !== null, fn ($q) => $q->whereIn('warehouse_id', $warehouseIds));

        $pendingGrn = (clone $pendingQuery)->count();

        $needsWarehouse = 0;
        $needsProcurement = 0;
        $grnsForMe = collect();

        if ($canWarehouse || $canProcurement) {
            $pendingList = (clone $pendingQuery)
                ->with(['purchaseOrder', 'supplier'])
                ->latest('submitted_at')
                ->limit(8)
                ->get();

            foreach ($pendingList as $receipt) {
                $needsWh = $canWarehouse && ! $receipt->warehouse_approved_at;
                $needsProc = $canProcurement && ! $receipt->procurement_approved_at;

                if ($needsWh) {
                    $needsWarehouse++;
                }
                if ($needsProc) {
                    $needsProcurement++;
                }
                if ($needsWh || $needsProc) {
                    $grnsForMe->push($receipt);
                }
            }
        }

        return [
            'awaiting_receipt' => $awaitingReceipt,
            'pending_grn' => $pendingGrn,
            'needs_my_warehouse_approval' => $needsWarehouse,
            'needs_my_procurement_approval' => $needsProcurement,
            'needs_my_approval' => $needsWarehouse + $needsProcurement,
            'can_create_grn' => $canCreate,
            'can_approve_warehouse' => $canWarehouse,
            'can_approve_procurement' => $canProcurement,
            'grns_needing_my_approval' => $grnsForMe->unique('id')->values(),
        ];
    }
}
