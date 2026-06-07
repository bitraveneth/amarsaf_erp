<?php

namespace App\Support\Documents;

use App\Models\Delivery;
use App\Models\GoodsReceipt;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\ProductionRun;
use App\Models\PurchaseOrder;
use InvalidArgumentException;

class DocumentDataFactory
{
    public static function build(string $type, object $model): array
    {
        return match ($type) {
            'invoice' => self::invoice(self::asInvoice($model)),
            'sales-order' => self::salesOrder(self::asOrder($model)),
            'picking-list' => self::pickingList(self::asOrder($model)),
            'purchase-order' => self::purchaseOrder(self::asPurchaseOrder($model)),
            'grn' => self::grn(self::asGoodsReceipt($model)),
            'production-order' => self::productionOrder(self::asProductionRun($model)),
            'delivery-challan' => self::deliveryChallan(self::asDelivery($model)),
            'packing-slip' => self::packingSlip(self::asDelivery($model)),
            'pod' => self::pod(self::asDelivery($model)),
            default => throw new InvalidArgumentException("Unsupported document type [{$type}]."),
        };
    }

    protected static function base(string $title, string $number, ?string $status = null, ?string $subtitle = null): array
    {
        return [
            'title' => $title,
            'subtitle' => $subtitle,
            'number' => $number,
            'status' => $status,
            'generated_at' => now(),
            'parties' => [],
            'meta' => [],
            'columns' => [],
            'rows' => [],
            'totals' => [],
            'notes' => null,
            'signatures' => [],
            'footer' => null,
        ];
    }

    protected static function invoice(Invoice $invoice): array
    {
        $invoice->load(['order.agent', 'items.product', 'receipts', 'creditNotes', 'advanceApplications']);

        $currency = config('app.currency', 'BDT');
        $grossTotal = (float) $invoice->net_total + (float) $invoice->vat_amount;
        $cashTotal = $grossTotal - (float) $invoice->withholding;
        $creditsTotal = (float) $invoice->creditNotes->sum('amount');
        $receiptsTotal = (float) $invoice->receipts->sum('amount');
        $advancesTotal = (float) $invoice->advanceApplications->sum('amount');
        $outstanding = max(0, $cashTotal - $creditsTotal - $receiptsTotal - $advancesTotal);

        $agent = $invoice->order?->agent;
        $agentLines = array_values(array_filter([
            $agent?->phone,
            $agent?->email,
            $agent?->area,
        ]));

        $payload = self::base(
            __('documents.types.invoice'),
            $invoice->number,
            ucfirst($invoice->status ?? 'draft'),
            __('documents.copy.customer')
        );

        $payload['parties'] = [[
            'label' => __('documents.labels.bill_to'),
            'name' => $agent?->name ?? '—',
            'lines' => $agentLines,
        ]];

        $payload['meta'] = [
            [__('documents.labels.document_no'), $invoice->number],
            [__('documents.labels.issue_date'), optional($invoice->issued_at)->format('d M Y') ?? '—'],
            [__('documents.labels.due_date'), optional($invoice->due_at)->format('d M Y') ?? '—'],
            [__('documents.labels.order_ref'), $invoice->order ? '#' . $invoice->order->id : '—'],
            [__('documents.labels.status'), ucfirst($invoice->status ?? 'draft')],
        ];

        $payload['columns'] = ['#', __('documents.columns.description'), __('documents.columns.qty'), __('documents.columns.unit'), __('documents.columns.amount')];

        foreach ($invoice->items as $index => $item) {
            $payload['rows'][] = [
                (string) ($index + 1),
                $item->description ?: ($item->product->name ?? '—'),
                number_format((float) $item->quantity, 2),
                number_format((float) $item->unit_price, 2),
                number_format((float) $item->line_total, 2),
            ];
        }

        $payload['totals'] = [
            [__('documents.totals.net'), number_format((float) $invoice->net_total, 2) . ' ' . $currency],
            [__('documents.totals.vat'), number_format((float) $invoice->vat_amount, 2) . ' ' . $currency],
            [__('documents.totals.gross'), number_format($grossTotal, 2) . ' ' . $currency],
        ];

        if ((float) $invoice->withholding > 0) {
            $payload['totals'][] = [__('documents.totals.withholding'), '- ' . number_format((float) $invoice->withholding, 2) . ' ' . $currency];
            $payload['totals'][] = [__('documents.totals.cash_due'), number_format($cashTotal, 2) . ' ' . $currency];
        }

        if ($creditsTotal > 0) {
            $payload['totals'][] = [__('documents.totals.credit_notes'), '- ' . number_format($creditsTotal, 2) . ' ' . $currency];
        }

        if ($receiptsTotal > 0) {
            $payload['totals'][] = [__('documents.totals.receipts'), '- ' . number_format($receiptsTotal, 2) . ' ' . $currency];
        }

        if ($advancesTotal > 0) {
            $payload['totals'][] = [__('documents.totals.advances'), '- ' . number_format($advancesTotal, 2) . ' ' . $currency];
        }

        $payload['totals'][] = [__('documents.totals.outstanding'), number_format($outstanding, 2) . ' ' . $currency];

        $payload['signatures'] = [
            [__('documents.signatures.authorized'), ''],
            [__('documents.signatures.received'), ''],
        ];

        $payload['footer'] = __('documents.footer.tax_invoice');

        return $payload;
    }

    protected static function salesOrder(Order $order): array
    {
        $order->load(['agent', 'items.product']);

        $currency = config('app.currency', 'BDT');
        $isReturn = ($order->order_type ?? null) === 'return';

        $payload = self::base(
            $isReturn ? __('documents.types.return_order') : __('documents.types.sales_order'),
            '#' . $order->id,
            ucfirst($order->status ?? 'draft'),
            $order->agent_reference ? __('documents.labels.customer_po', ['ref' => $order->agent_reference]) : null
        );

        $agent = $order->agent;
        $shipLines = array_values(array_filter([
            $order->delivery_contact_phone ?: $agent?->phone,
            $order->delivery_address,
            $agent?->area,
        ]));

        $payload['parties'] = [
            [
                'label' => __('documents.labels.customer'),
                'name' => $agent?->name ?? '—',
                'lines' => array_values(array_filter([$agent?->phone, $agent?->email, $agent?->area])),
            ],
            [
                'label' => __('documents.labels.ship_to'),
                'name' => $order->delivery_contact_name ?: ($agent?->name ?? '—'),
                'lines' => $shipLines,
            ],
        ];

        $payload['meta'] = [
            [__('documents.labels.order_no'), '#' . $order->id],
            [__('documents.labels.order_date'), optional($order->created_at)->format('d M Y') ?? '—'],
            [__('documents.labels.delivery_date'), optional($order->delivery_date)->format('d M Y') ?? __('documents.labels.tbd')],
            [__('documents.labels.payment_mode'), ucfirst(str_replace('_', ' ', $order->payment_mode ?? 'cash'))],
            [__('documents.labels.status'), ucfirst($order->status ?? 'draft')],
        ];

        $payload['columns'] = ['#', __('documents.columns.product'), __('documents.columns.sku'), __('documents.columns.qty'), __('documents.columns.unit'), __('documents.columns.line_total')];

        $subtotal = 0.0;

        foreach ($order->items as $index => $item) {
            $lineTotal = (float) $item->quantity * (float) $item->unit_price;
            $subtotal += $lineTotal;

            $payload['rows'][] = [
                (string) ($index + 1),
                $item->product->name ?? '—',
                $item->product->sku ?? '—',
                number_format((float) $item->quantity, 0),
                number_format((float) $item->unit_price, 2),
                number_format($lineTotal, 2),
            ];
        }

        $payload['totals'] = [
            [__('documents.totals.subtotal'), number_format($subtotal, 2) . ' ' . $currency],
            [__('documents.totals.order_total'), number_format((float) $order->total, 2) . ' ' . $currency],
        ];

        if ((float) $order->commission_total > 0) {
            $payload['totals'][] = [__('documents.totals.commission'), number_format((float) $order->commission_total, 2) . ' ' . $currency];
        }

        $payload['notes'] = $order->notes;
        $payload['signatures'] = [
            [__('documents.signatures.prepared'), ''],
            [__('documents.signatures.customer'), ''],
        ];
        $payload['footer'] = __('documents.footer.sales_order');

        return $payload;
    }

    protected static function pickingList(Order $order): array
    {
        $order->load(['agent', 'items.product']);
        $lines = PickingListBuilder::linesForOrder($order);

        $payload = self::base(
            __('documents.types.picking_list'),
            '#' . $order->id,
            ucfirst($order->status ?? 'confirmed')
        );

        $payload['parties'] = [[
            'label' => __('documents.labels.customer'),
            'name' => $order->agent->name ?? '—',
            'lines' => array_values(array_filter([
                $order->agent->phone ?? null,
                $order->agent_reference ? __('documents.labels.customer_po', ['ref' => $order->agent_reference]) : null,
            ])),
        ]];

        $payload['meta'] = [
            [__('documents.labels.order_no'), '#' . $order->id],
            [__('documents.labels.delivery_date'), optional($order->delivery_date)->format('d M Y') ?? __('documents.labels.tbd')],
            [__('documents.labels.status'), ucfirst($order->status ?? 'confirmed')],
        ];

        $payload['columns'] = [
            __('documents.columns.sku'),
            __('documents.columns.product'),
            __('documents.columns.qty'),
            __('documents.columns.warehouse'),
            __('documents.columns.batch'),
        ];

        foreach ($lines as $line) {
            $payload['rows'][] = [
                $line['sku'] ?: '—',
                $line['name'] ?: '—',
                number_format((float) $line['quantity'], 0),
                $line['warehouse'] ?? '—',
                $line['batch'] ?? '—',
            ];
        }

        $payload['signatures'] = [
            [__('documents.signatures.picker'), ''],
            [__('documents.signatures.supervisor'), ''],
        ];
        $payload['footer'] = __('documents.footer.picking_list');

        return $payload;
    }

    protected static function purchaseOrder(PurchaseOrder $order): array
    {
        $order->load(['supplier', 'items.product']);

        $currency = config('app.currency', 'BDT');
        $total = (float) $order->items->sum(fn ($item) => (float) ($item->line_total ?: ($item->quantity * $item->unit_price)));

        $payload = self::base(
            __('documents.types.purchase_order'),
            $order->number,
            ucfirst($order->status ?? 'draft')
        );

        $supplier = $order->supplier;
        $payload['parties'] = [[
            'label' => __('documents.labels.supplier'),
            'name' => $supplier?->name ?? '—',
            'lines' => array_values(array_filter([$supplier?->email, $supplier?->phone, $supplier?->tax_id])),
        ]];

        $payload['meta'] = [
            [__('documents.labels.po_no'), $order->number],
            [__('documents.labels.order_date'), optional($order->order_date)->format('d M Y') ?? '—'],
            [__('documents.labels.expected_date'), optional($order->expected_date)->format('d M Y') ?? '—'],
            [__('documents.labels.status'), ucfirst($order->status ?? 'draft')],
        ];

        $payload['columns'] = ['#', __('documents.columns.description'), __('documents.columns.qty'), __('documents.columns.unit'), __('documents.columns.amount')];

        foreach ($order->items as $index => $item) {
            $lineTotal = (float) ($item->line_total ?: ($item->quantity * $item->unit_price));

            $payload['rows'][] = [
                (string) ($index + 1),
                $item->description ?: ($item->product->name ?? '—'),
                number_format((float) $item->quantity, 2),
                number_format((float) $item->unit_price, 2),
                number_format($lineTotal, 2),
            ];
        }

        $payload['totals'] = [
            [__('documents.totals.po_total'), number_format($total, 2) . ' ' . $currency],
        ];

        $payload['notes'] = $order->notes;
        $payload['signatures'] = [
            [__('documents.signatures.prepared'), ''],
            [__('documents.signatures.approved'), ''],
            [__('documents.signatures.supplier'), ''],
        ];
        $payload['footer'] = __('documents.footer.purchase_order');

        return $payload;
    }

    protected static function grn(GoodsReceipt $receipt): array
    {
        $receipt->load(['supplier', 'warehouse', 'items.product', 'items.batch', 'purchaseOrder', 'creator']);

        $payload = self::base(
            __('documents.types.grn'),
            $receipt->grn_number,
            ucfirst($receipt->status ?? 'posted')
        );

        $payload['parties'] = [
            [
                'label' => __('documents.labels.supplier'),
                'name' => $receipt->supplier->name ?? '—',
                'lines' => [],
            ],
            [
                'label' => __('documents.labels.warehouse'),
                'name' => $receipt->warehouse->name ?? '—',
                'lines' => [],
            ],
        ];

        $payload['meta'] = [
            [__('documents.labels.grn_no'), $receipt->grn_number],
            [__('documents.labels.received_at'), optional($receipt->received_at)->format('d M Y H:i') ?? '—'],
            [__('documents.labels.po_ref'), $receipt->purchaseOrder->number ?? '—'],
            [__('documents.labels.received_by'), $receipt->creator->name ?? '—'],
            [__('documents.labels.status'), ucfirst($receipt->status ?? 'posted')],
        ];

        $payload['columns'] = [
            '#',
            __('documents.columns.product'),
            __('documents.columns.qty'),
            __('documents.columns.qc'),
            __('documents.columns.batch'),
        ];

        foreach ($receipt->items as $index => $item) {
            $payload['rows'][] = [
                (string) ($index + 1),
                $item->product->name ?? '—',
                number_format((float) $item->quantity, 2),
                ucfirst($item->qc_status ?? 'pending'),
                $item->batch->batch_code ?? '—',
            ];
        }

        $payload['notes'] = $receipt->notes;
        $payload['signatures'] = [
            [__('documents.signatures.received'), ''],
            [__('documents.signatures.qc'), ''],
            [__('documents.signatures.store'), ''],
        ];
        $payload['footer'] = __('documents.footer.grn');

        return $payload;
    }

    protected static function productionOrder(ProductionRun $run): array
    {
        $run->load(['product', 'batch', 'warehouse', 'supervisor', 'materialIssues.items.component']);

        $payload = self::base(
            __('documents.types.production_order'),
            $run->order_number ?? ('RUN-' . $run->id),
            ucfirst(str_replace('_', ' ', $run->status ?? 'planned'))
        );

        $payload['parties'] = [[
            'label' => __('documents.labels.product'),
            'name' => $run->product->name ?? '—',
            'lines' => array_values(array_filter([
                $run->batch?->batch_code ? __('documents.labels.batch', ['code' => $run->batch->batch_code]) : null,
                $run->warehouse?->name,
            ])),
        ]];

        $payload['meta'] = [
            [__('documents.labels.run_no'), $run->order_number ?? ('RUN-' . $run->id)],
            [__('documents.labels.line'), $run->line ?? '—'],
            [__('documents.labels.shift'), $run->shift ?? '—'],
            [__('documents.labels.planned_qty'), number_format((int) $run->quantity, 0)],
            [__('documents.labels.qc_status'), ucfirst($run->qc_status ?? 'pending')],
            [__('documents.labels.supervisor'), $run->supervisor->name ?? '—'],
        ];

        $materialRows = $run->materialIssues
            ->flatMap(fn ($issue) => $issue->items)
            ->values();

        if ($materialRows->isNotEmpty()) {
            $payload['columns'] = [
                '#',
                __('documents.columns.material'),
                __('documents.columns.qty'),
            ];

            foreach ($materialRows as $index => $item) {
                $payload['rows'][] = [
                    (string) ($index + 1),
                    $item->component->name ?? '—',
                    number_format((float) $item->quantity, 2),
                ];
            }
        }

        $payload['notes'] = $run->notes;

        if ((float) $run->material_total_cost > 0) {
            $currency = config('app.currency', 'BDT');
            $payload['totals'] = [
                [__('documents.totals.material_cost'), number_format((float) $run->material_total_cost, 2) . ' ' . $currency],
            ];
        }

        $payload['signatures'] = [
            [__('documents.signatures.supervisor'), ''],
            [__('documents.signatures.qc'), ''],
            [__('documents.signatures.production'), ''],
        ];
        $payload['footer'] = __('documents.footer.production_order');

        return $payload;
    }

    protected static function deliveryPayload(Delivery $delivery): array
    {
        $delivery->load([
            'order.agent',
            'order.items.product',
            'route',
            'vehicle',
            'items.product',
            'items.batch',
            'pod',
        ]);

        return [
            'delivery' => $delivery,
            'order' => $delivery->order,
            'agent' => $delivery->order?->agent,
        ];
    }

    protected static function deliveryChallan(Delivery $delivery): array
    {
        $context = self::deliveryPayload($delivery);
        $order = $context['order'];
        $agent = $context['agent'];

        $payload = self::base(
            __('documents.types.delivery_challan'),
            'DC-' . str_pad((string) $delivery->id, 5, '0', STR_PAD_LEFT),
            ucfirst(str_replace('_', ' ', $delivery->status ?? 'scheduled')),
            __('documents.copy.customer')
        );

        $payload['parties'] = [
            [
                'label' => __('documents.labels.customer'),
                'name' => $agent?->name ?? '—',
                'lines' => array_values(array_filter([$agent?->phone, $agent?->area])),
            ],
            [
                'label' => __('documents.labels.ship_to'),
                'name' => $order?->delivery_contact_name ?: ($agent?->name ?? '—'),
                'lines' => array_values(array_filter([$order?->delivery_contact_phone, $order?->delivery_address])),
            ],
        ];

        $payload['meta'] = [
            [__('documents.labels.challan_no'), $payload['number']],
            [__('documents.labels.delivery_no'), '#' . $delivery->id],
            [__('documents.labels.order_no'), $order ? '#' . $order->id : '—'],
            [__('documents.labels.delivery_date'), optional($order?->delivery_date ?? $delivery->created_at)->format('d M Y') ?? '—'],
            [__('documents.labels.route'), $delivery->route->name ?? '—'],
            [__('documents.labels.vehicle'), $delivery->vehicle->license_plate ?? $delivery->vehicle->name ?? '—'],
        ];

        $payload['columns'] = [
            '#',
            __('documents.columns.product'),
            __('documents.columns.batch'),
            __('documents.columns.dispatched'),
            __('documents.columns.delivered'),
        ];

        $items = $delivery->items->isNotEmpty()
            ? $delivery->items
            : collect();

        if ($items->isEmpty() && $order) {
            foreach ($order->items as $index => $item) {
                $payload['rows'][] = [
                    (string) ($index + 1),
                    $item->product->name ?? '—',
                    '—',
                    number_format((float) $item->quantity, 0),
                    '—',
                ];
            }
        } else {
            foreach ($items as $index => $item) {
                $payload['rows'][] = [
                    (string) ($index + 1),
                    $item->product->name ?? '—',
                    $item->batch->batch_code ?? '—',
                    number_format((float) $item->qty_dispatched, 0),
                    number_format((float) $item->qty_delivered, 0),
                ];
            }
        }

        $payload['signatures'] = [
            [__('documents.signatures.driver'), ''],
            [__('documents.signatures.receiver'), ''],
            [__('documents.signatures.security'), ''],
        ];
        $payload['footer'] = __('documents.footer.delivery_challan');

        return $payload;
    }

    protected static function packingSlip(Delivery $delivery): array
    {
        $context = self::deliveryPayload($delivery);
        $order = $context['order'];
        $agent = $context['agent'];

        $payload = self::base(
            __('documents.types.packing_slip'),
            'PS-' . str_pad((string) $delivery->id, 5, '0', STR_PAD_LEFT),
            ucfirst(str_replace('_', ' ', $delivery->status ?? 'scheduled'))
        );

        $payload['parties'] = [[
            'label' => __('documents.labels.ship_to'),
            'name' => $order?->delivery_contact_name ?: ($agent?->name ?? '—'),
            'lines' => array_values(array_filter([$order?->delivery_contact_phone, $order?->delivery_address, $agent?->area])),
        ]];

        $payload['meta'] = [
            [__('documents.labels.packing_no'), $payload['number']],
            [__('documents.labels.delivery_no'), '#' . $delivery->id],
            [__('documents.labels.order_no'), $order ? '#' . $order->id : '—'],
            [__('documents.labels.packed_date'), now()->format('d M Y')],
        ];

        $payload['columns'] = ['#', __('documents.columns.product'), __('documents.columns.sku'), __('documents.columns.qty')];

        $items = $delivery->items->isNotEmpty() ? $delivery->items : collect();

        if ($items->isEmpty() && $order) {
            foreach ($order->items as $index => $item) {
                $payload['rows'][] = [
                    (string) ($index + 1),
                    $item->product->name ?? '—',
                    $item->product->sku ?? '—',
                    number_format((float) $item->quantity, 0),
                ];
            }
        } else {
            foreach ($items as $index => $item) {
                $qty = (float) ($item->qty_dispatched ?: $item->qty_delivered);
                $payload['rows'][] = [
                    (string) ($index + 1),
                    $item->product->name ?? '—',
                    $item->product->sku ?? '—',
                    number_format($qty, 0),
                ];
            }
        }

        $payload['signatures'] = [
            [__('documents.signatures.packed_by'), ''],
            [__('documents.signatures.checked_by'), ''],
        ];
        $payload['footer'] = __('documents.footer.packing_slip');

        return $payload;
    }

    protected static function pod(Delivery $delivery): array
    {
        $context = self::deliveryPayload($delivery);
        $order = $context['order'];
        $agent = $context['agent'];
        $pod = $delivery->pod;

        $payload = self::base(
            __('documents.types.pod'),
            'POD-' . str_pad((string) $delivery->id, 5, '0', STR_PAD_LEFT),
            ucfirst(str_replace('_', ' ', $delivery->status ?? 'scheduled'))
        );

        $payload['parties'] = [[
            'label' => __('documents.labels.customer'),
            'name' => $agent?->name ?? '—',
            'lines' => array_values(array_filter([$agent?->phone, $agent?->area])),
        ]];

        $payload['meta'] = [
            [__('documents.labels.pod_no'), $payload['number']],
            [__('documents.labels.delivery_no'), '#' . $delivery->id],
            [__('documents.labels.order_no'), $order ? '#' . $order->id : '—'],
            [__('documents.labels.delivered_at'), optional($pod?->delivered_at)->format('d M Y H:i') ?? '—'],
            [__('documents.labels.receiver'), $pod?->receiver_name ?? '—'],
            [__('documents.labels.receiver_phone'), $pod?->receiver_phone ?? '—'],
            [__('documents.labels.signed_by'), $pod?->signed_by ?? '—'],
        ];

        $payload['columns'] = ['#', __('documents.columns.product'), __('documents.columns.delivered'), __('documents.columns.short'), __('documents.columns.damaged')];

        foreach ($delivery->items as $index => $item) {
            $payload['rows'][] = [
                (string) ($index + 1),
                $item->product->name ?? '—',
                number_format((float) $item->qty_delivered, 0),
                number_format((float) $item->qty_short, 0),
                number_format((float) $item->qty_damaged, 0),
            ];
        }

        $payload['notes'] = $pod?->notes;
        $payload['signatures'] = [
            [__('documents.signatures.driver'), ''],
            [__('documents.signatures.receiver'), ''],
        ];
        $payload['footer'] = __('documents.footer.pod');

        return $payload;
    }

    protected static function asInvoice(object $model): Invoice
    {
        if (! $model instanceof Invoice) {
            throw new InvalidArgumentException('Expected Invoice model.');
        }

        return $model;
    }

    protected static function asOrder(object $model): Order
    {
        if (! $model instanceof Order) {
            throw new InvalidArgumentException('Expected Order model.');
        }

        return $model;
    }

    protected static function asPurchaseOrder(object $model): PurchaseOrder
    {
        if (! $model instanceof PurchaseOrder) {
            throw new InvalidArgumentException('Expected PurchaseOrder model.');
        }

        return $model;
    }

    protected static function asGoodsReceipt(object $model): GoodsReceipt
    {
        if (! $model instanceof GoodsReceipt) {
            throw new InvalidArgumentException('Expected GoodsReceipt model.');
        }

        return $model;
    }

    protected static function asProductionRun(object $model): ProductionRun
    {
        if (! $model instanceof ProductionRun) {
            throw new InvalidArgumentException('Expected ProductionRun model.');
        }

        return $model;
    }

    protected static function asDelivery(object $model): Delivery
    {
        if (! $model instanceof Delivery) {
            throw new InvalidArgumentException('Expected Delivery model.');
        }

        return $model;
    }
}
