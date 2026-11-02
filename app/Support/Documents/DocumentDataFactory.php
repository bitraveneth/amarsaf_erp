<?php

namespace App\Support\Documents;

use App\Models\Delivery;
use App\Models\GoodsReceipt;
use App\Models\Invoice;
use App\Models\LogisticsBill;
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
            'logistics-bill' => self::logisticsBill(self::asLogisticsBill($model)),
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
        $order = $invoice->order;
        $agentLines = array_values(array_filter([
            $agent?->phone,
            $agent?->email,
            $agent?->area,
            $agent?->zone,
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

        if ($order && ($order->delivery_address || $order->delivery_contact_name)) {
            $payload['parties'][] = [
                'label' => __('documents.labels.ship_to'),
                'name' => $order->delivery_contact_name ?: ($agent?->name ?? '—'),
                'lines' => array_values(array_filter([
                    $order->delivery_contact_phone,
                    $order->delivery_address,
                ])),
            ];
        }

        $payload['meta'] = [
            [__('documents.labels.document_no'), $invoice->number],
            [__('documents.labels.issue_date'), optional($invoice->issued_at)->format('d M Y') ?? '—'],
            [__('documents.labels.due_date'), optional($invoice->due_at)->format('d M Y') ?? '—'],
            [__('documents.labels.order_ref'), $invoice->order ? '#' . $invoice->order->id : '—'],
            [__('documents.labels.status'), ucfirst($invoice->status ?? 'draft')],
        ];

        $payload['columns'] = ['#', __('documents.columns.particulars'), __('documents.columns.qty'), __('documents.columns.rate'), __('documents.columns.amount')];
        $payload['aligns'] = ['center', 'left', 'right', 'right', 'right'];
        $payload['widths'] = ['6%', '46%', '12%', '18%', '18%'];

        foreach ($invoice->items as $index => $item) {
            $particulars = self::lineParticulars($item->description, $item->product);

            $payload['rows'][] = [
                (string) ($index + 1),
                $particulars,
                self::formatQty((float) $item->quantity),
                self::money((float) $item->unit_price),
                self::money((float) $item->line_total),
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

        $paymentLines = [];
        $paymentMode = $invoice->order?->payment_mode;
        if ($paymentMode) {
            $paymentLines[] = __('documents.labels.payment_mode').': '.ucfirst(str_replace('_', ' ', (string) $paymentMode));
        }
        foreach ($invoice->receipts as $receipt) {
            $method = $receipt->payment_method
                ? ucfirst(str_replace('_', ' ', (string) $receipt->payment_method))
                : __('documents.labels.receipt');
            $when = optional($receipt->received_at)->format('d M Y');
            $paymentLines[] = trim($method.' · '.self::money((float) $receipt->amount).($when ? ' · '.$when : ''));
        }
        if ($advancesTotal > 0) {
            $paymentLines[] = __('documents.totals.advances').': '.self::money($advancesTotal);
        }
        if ($paymentLines === []) {
            $paymentLines[] = __('documents.labels.no_receipt');
        }
        $payload['blocks'] = [[
            'label' => __('documents.labels.payment_details'),
            'lines' => $paymentLines,
        ]];

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
        $payload['aligns'] = ['center', 'left', 'left', 'right', 'right', 'right'];

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
        $payload['aligns'] = ['left', 'left', 'right', 'left', 'left'];

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
            'lines' => array_values(array_filter([
                $supplier?->contact_person,
                $supplier?->phone,
                $supplier?->email,
                $supplier?->address,
                $supplier?->tax_id ? 'BIN '.$supplier->tax_id : null,
            ])),
        ]];

        $payload['meta'] = [
            [__('documents.labels.po_no'), $order->number],
            [__('documents.labels.order_date'), optional($order->order_date)->format('d M Y') ?? '—'],
            [__('documents.labels.expected_date'), optional($order->expected_date)->format('d M Y') ?? '—'],
            [__('documents.labels.status'), ucfirst($order->status ?? 'draft')],
        ];

        $hasDiscount = $order->items->contains(fn ($item) => (float) $item->discount_percent > 0);
        $hasVat = $order->items->contains(fn ($item) => (float) $item->vat_rate > 0);
        $payload['columns'] = ['#', __('documents.columns.particulars'), __('documents.columns.qty'), __('documents.columns.uom'), __('documents.columns.rate')];
        $payload['aligns'] = ['center', 'left', 'right', 'left', 'right'];
        if ($hasDiscount) {
            $payload['columns'][] = __('documents.columns.discount');
            $payload['aligns'][] = 'right';
        }
        if ($hasVat) {
            $payload['columns'][] = __('documents.columns.vat_rate');
            $payload['aligns'][] = 'right';
        }
        $payload['columns'][] = __('documents.columns.amount');
        $payload['aligns'][] = 'right';

        $subtotal = 0.0;
        $discountTotal = 0.0;
        $vatTotal = 0.0;

        foreach ($order->items as $index => $item) {
            $qty = (float) $item->quantity;
            $unitPrice = $item->unit_price !== null ? (float) $item->unit_price : null;
            $discountPercent = (float) $item->discount_percent;
            $vatRate = (float) $item->vat_rate;
            $gross = $unitPrice !== null ? $qty * $unitPrice : 0.0;
            $discountAmount = $gross * (min(100, max(0, $discountPercent)) / 100);
            $net = $gross - $discountAmount;
            $vatAmount = $net * (min(100, max(0, $vatRate)) / 100);
            $lineTotal = $item->line_total !== null
                ? (float) $item->line_total
                : ($unitPrice !== null ? round($net + $vatAmount, 2) : 0.0);

            $subtotal += $gross;
            $discountTotal += $discountAmount;
            $vatTotal += $vatAmount;

            $particulars = self::lineParticulars($item->description, $item->product);

            $row = [
                (string) ($index + 1),
                $particulars,
                self::formatQty($qty),
                self::productUom($item->product, $item->uom),
                $unitPrice !== null ? self::money($unitPrice) : '—',
            ];
            if ($hasDiscount) {
                $row[] = self::formatQty($discountPercent).'%';
            }
            if ($hasVat) {
                $row[] = self::formatQty($vatRate).'%';
            }
            $row[] = self::money($lineTotal);
            $payload['rows'][] = $row;
        }

        $payload['totals'] = [
            [__('documents.totals.subtotal'), self::moneyLabel($subtotal)],
        ];
        if ($discountTotal > 0) {
            $payload['totals'][] = [__('documents.totals.discount'), '- '.self::moneyLabel($discountTotal)];
        }
        if ($vatTotal > 0) {
            $payload['totals'][] = [__('documents.totals.vat'), self::moneyLabel($vatTotal)];
        }
        $payload['totals'][] = [__('documents.totals.po_total'), self::moneyLabel($total > 0 ? $total : ($subtotal - $discountTotal + $vatTotal))];

        $payload['notes'] = $order->notes;
        $payload['signatures'] = [
            [__('documents.signatures.prepared'), ''],
            [__('documents.signatures.approved'), ''],
            [__('documents.signatures.supplier'), ''],
        ];
        $payload['footer'] = __('documents.footer.purchase_order');

        return $payload;
    }

    protected static function logisticsBill(LogisticsBill $bill): array
    {
        $bill->load(['transportCarrier', 'deliveryRoute', 'lines', 'payments']);

        $currency = config('app.currency', 'BDT');
        $grossTotal = (float) $bill->gross_total;
        $paidTotal = (float) $bill->paid_total;
        $outstanding = (float) $bill->outstanding;

        $carrier = $bill->transportCarrier;
        $carrierLines = array_values(array_filter([
            $carrier?->contact_person,
            $carrier?->phone,
            $carrier?->email,
            $carrier?->tax_id ? 'BIN ' . $carrier->tax_id : null,
            $carrier?->address,
        ]));

        $payload = self::base(
            __('documents.types.logistics_bill'),
            $bill->document_number,
            ucfirst(str_replace('_', ' ', $bill->status ?? 'open')),
            __('documents.copy.office')
        );

        $payload['parties'] = [[
            'label' => __('documents.labels.carrier'),
            'name' => $carrier?->name ?? '—',
            'lines' => $carrierLines,
        ]];

        $payload['meta'] = array_values(array_filter([
            [__('documents.labels.bill_no'), $bill->document_number],
            [__('documents.labels.bill_date'), optional($bill->bill_date)->format('d M Y') ?? '—'],
            $bill->due_date ? [__('documents.labels.due_date'), $bill->due_date->format('d M Y')] : null,
            [__('documents.labels.service_type'), $bill->serviceTypeLabel()],
            $bill->deliveryRoute ? [__('documents.labels.route'), $bill->deliveryRoute->name] : null,
            $bill->trip_date ? [__('documents.labels.trip_date'), $bill->trip_date->format('d M Y')] : null,
            [__('documents.labels.status'), ucfirst(str_replace('_', ' ', $bill->status ?? 'open'))],
        ]));

        $payload['columns'] = ['#', __('documents.columns.description'), __('documents.columns.amount')];
        $payload['aligns'] = ['center', 'left', 'right'];

        foreach ($bill->lines as $index => $line) {
            $payload['rows'][] = [
                (string) ($index + 1),
                $line->description,
                number_format((float) $line->amount, 2),
            ];
        }

        $payload['totals'] = [
            [__('documents.totals.net'), number_format((float) $bill->net_total, 2) . ' ' . $currency],
            [__('documents.totals.vat'), number_format((float) $bill->vat_amount, 2) . ' ' . $currency],
            [__('documents.totals.gross'), number_format($grossTotal, 2) . ' ' . $currency],
        ];

        if ($paidTotal > 0) {
            $payload['totals'][] = [__('documents.totals.paid'), '- ' . number_format($paidTotal, 2) . ' ' . $currency];
        }

        if ($outstanding > 0) {
            $payload['totals'][] = [__('documents.totals.outstanding'), number_format($outstanding, 2) . ' ' . $currency];
        }

        $payload['notes'] = $bill->notes;
        $payload['signatures'] = [
            [__('documents.signatures.prepared'), ''],
            [__('documents.signatures.approved'), ''],
            [__('documents.signatures.carrier'), ''],
        ];
        $payload['footer'] = __('documents.footer.logistics_bill');

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
        $payload['aligns'] = ['center', 'left', 'right', 'left', 'left'];

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
            $payload['aligns'] = ['center', 'left', 'right'];

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
            ucfirst(str_replace('_', ' ', $delivery->status ?? 'scheduled'))
        );
        $payload['copies'] = [
            __('documents.copy.customer'),
            __('documents.copy.office'),
            __('documents.copy.transport'),
        ];

        $deliverName = $order?->delivery_contact_name ?: ($agent?->name ?? '—');
        $payload['parties'] = [[
            'label' => __('documents.labels.deliver_to'),
            'name' => $deliverName,
            'lines' => self::addressLines(
                $order?->delivery_contact_phone ?: $agent?->phone,
                $order?->delivery_address,
                $agent?->area
            ),
        ]];
        if ($agent?->name && $agent->name !== $deliverName) {
            array_unshift($payload['parties'], [
                'label' => __('documents.labels.customer'),
                'name' => $agent->name,
                'lines' => array_values(array_filter([$agent->phone, $agent->area])),
            ]);
        }

        $payload['meta'] = [
            [__('documents.labels.challan_no'), $payload['number']],
            [__('documents.labels.delivery_no'), '#' . $delivery->id],
            [__('documents.labels.order_no'), $order ? '#' . $order->id : '—'],
            [__('documents.labels.delivery_date'), optional($order?->delivery_date ?? $delivery->created_at)->format('d M Y') ?? '—'],
            [__('documents.labels.status'), ucfirst(str_replace('_', ' ', $delivery->status ?? 'scheduled'))],
        ];

        $vehicle = $delivery->vehicle;
        $vehicleLabel = $vehicle?->license_plate ?: $vehicle?->name;
        $introLines = array_values(array_filter([
            $vehicle?->driver ? __('documents.labels.driver').': '.$vehicle->driver : null,
            $vehicleLabel ? __('documents.labels.vehicle').': '.$vehicleLabel : null,
            $delivery->route?->name ? __('documents.labels.route').': '.$delivery->route->name : null,
        ]));
        if ($introLines !== []) {
            $payload['intro'] = [[
                'label' => __('documents.labels.transport'),
                'lines' => $introLines,
            ]];
        }

        $payload['columns'] = [
            '#',
            __('documents.columns.particulars'),
            __('documents.columns.size'),
            __('documents.columns.uom'),
            __('documents.columns.qty_sent'),
            __('documents.columns.qty_received'),
            __('documents.columns.remarks'),
        ];
        $payload['aligns'] = ['center', 'left', 'left', 'left', 'right', 'right', 'left'];
        $payload['widths'] = ['6%', '28%', '12%', '10%', '12%', '14%', '18%'];

        $items = $delivery->items->isNotEmpty()
            ? $delivery->items
            : collect();

        if ($items->isEmpty() && $order) {
            foreach ($order->items as $index => $item) {
                $payload['rows'][] = [
                    (string) ($index + 1),
                    $item->product->name ?? '—',
                    self::productSize($item->product),
                    self::productUom($item->product),
                    self::formatQty((float) $item->quantity),
                    '________',
                    '—',
                ];
            }
        } else {
            foreach ($items as $index => $item) {
                $received = (float) $item->qty_delivered;
                $remarks = array_values(array_filter([
                    $item->batch?->batch_code ? __('documents.columns.batch').' '.$item->batch->batch_code : null,
                    $item->notes,
                ]));

                $payload['rows'][] = [
                    (string) ($index + 1),
                    $item->product->name ?? '—',
                    self::productSize($item->product),
                    self::productUom($item->product),
                    self::formatQty((float) $item->qty_dispatched),
                    $received > 0 ? self::formatQty($received) : '________',
                    $remarks !== [] ? implode(' · ', $remarks) : '—',
                ];
            }
        }

        $payload['notes'] = trim(__('documents.labels.challan_ack').' '.(string) $delivery->exception_notes);
        $payload['signatures'] = [
            [__('documents.signatures.prepared'), ''],
            [__('documents.signatures.authorized'), ''],
            [__('documents.signatures.received'), ''],
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
        $payload['aligns'] = ['center', 'left', 'left', 'right'];

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
        $payload['aligns'] = ['center', 'left', 'right', 'right', 'right'];

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

    protected static function money(float $amount): string
    {
        return number_format($amount, 2);
    }

    protected static function moneyLabel(float $amount): string
    {
        return self::money($amount).' '.config('app.currency', 'BDT');
    }

    protected static function formatQty(float $qty): string
    {
        $qty = round($qty, 2);

        if (abs($qty - round($qty)) < 0.001) {
            return number_format($qty, 0);
        }

        return number_format($qty, 2);
    }

    protected static function productSize(?object $product): string
    {
        if (! $product) {
            return '—';
        }

        $size = trim((string) ($product->size ?? ''));
        if ($size !== '') {
            return $size;
        }

        $volume = (float) ($product->volume_ml ?? 0);

        return $volume > 0 ? rtrim(rtrim(number_format($volume, 2), '0'), '.').' ml' : '—';
    }

    protected static function lineParticulars(?string $description, ?object $product): string
    {
        $name = trim((string) ($product?->name ?? ''));
        $written = trim((string) $description);
        $particulars = $written !== '' ? $written : ($name !== '' ? $name : '—');
        $size = self::productSize($product);
        $showingProduct = $name !== '' && ($written === '' || strcasecmp($written, $name) === 0);

        if ($showingProduct && $size !== '—' && ! str_contains($particulars, $size)) {
            $particulars .= ' · '.$size;
        }

        return $particulars;
    }

    protected static function addressLines(?string $phone, ?string $address, ?string $area): array
    {
        $address = trim((string) $address);
        $area = trim((string) $area);

        if ($area !== '' && $address !== '' && str_contains(mb_strtolower($address), mb_strtolower($area))) {
            $area = '';
        }

        return array_values(array_filter([
            trim((string) $phone) ?: null,
            $address !== '' ? $address : null,
            $area !== '' ? $area : null,
        ]));
    }

    protected static function productUom(?object $product, ?string $fallback = null): string
    {
        $uom = trim((string) ($fallback ?: ($product->uom ?? '')));

        return $uom !== '' ? $uom : '—';
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

    protected static function asLogisticsBill(object $model): LogisticsBill
    {
        if (! $model instanceof LogisticsBill) {
            throw new InvalidArgumentException('Expected LogisticsBill model.');
        }

        return $model;
    }
}
