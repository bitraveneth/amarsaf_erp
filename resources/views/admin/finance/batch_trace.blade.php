@extends('layouts.app')

@section('content')
<x-report.page
    eyebrow="Inventory reports"
    :title="'Batch trace: ' . $batch->batch_code"
    :subtitle="$batch->product?->name"
>
    <x-slot:actions>
        <x-report.header-actions>
            <x-report.hub-link category="operations" />
            <a href="{{ route('admin.reports.batch-trace') }}" class="erp-btn-secondary">Back to lookup</a>
        </x-report.header-actions>
    </x-slot:actions>

    @php
        $sections = [
            'Production runs' => [
                ['label' => 'Run', 'key' => 'order_number'],
                ['label' => 'Qty', 'key' => 'quantity', 'align' => 'right'],
                ['label' => 'QC', 'key' => 'qc_status'],
                ['label' => 'Warehouse', 'key' => 'warehouse'],
                ['label' => 'Material cost', 'key' => 'material_cost', 'align' => 'right', 'format' => 'number'],
                ['label' => 'Confirmed', 'key' => 'confirmed_at'],
            ],
            'GRN receipts' => [
                ['label' => 'GRN', 'key' => 'grn_number'],
                ['label' => 'Supplier', 'key' => 'supplier'],
                ['label' => 'Product', 'key' => 'product'],
                ['label' => 'Qty', 'key' => 'quantity', 'align' => 'right', 'format' => 'number'],
                ['label' => 'Received', 'key' => 'received_at'],
            ],
            'Deliveries' => [
                ['label' => 'Delivery', 'key' => 'delivery_id'],
                ['label' => 'Order', 'key' => 'order_id'],
                ['label' => 'Agent', 'key' => 'agent'],
                ['label' => 'Product', 'key' => 'product'],
                ['label' => 'Qty', 'key' => 'quantity', 'align' => 'right', 'format' => 'number'],
                ['label' => 'Status', 'key' => 'status'],
            ],
            'Invoices' => [
                ['label' => 'Invoice', 'key' => 'invoice_number'],
                ['label' => 'Agent', 'key' => 'agent'],
                ['label' => 'Product', 'key' => 'product'],
                ['label' => 'Qty', 'key' => 'quantity', 'align' => 'right', 'format' => 'number'],
                ['label' => 'Line total', 'key' => 'line_total', 'align' => 'right', 'format' => 'money'],
            ],
        ];
        $datasets = [
            'Production runs' => $productionRuns,
            'GRN receipts' => $grnItems,
            'Deliveries' => $deliveryItems,
            'Invoices' => $invoiceItems,
        ];
    @endphp

    @foreach($sections as $title => $columns)
        <x-dashboard.panel :title="$title">
            <div class="overflow-x-auto">
                <table class="erp-dash-statement__table">
                <thead>
                    <tr>
                        @foreach($columns as $column)
                            <th @class(['is-right' => ($column['align'] ?? null) === 'right'])>{{ $column['label'] }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($datasets[$title] as $row)
                        <tr>
                            @foreach($columns as $column)
                                @php
                                    $value = data_get($row, $column['key']);
                                    if (($column['format'] ?? null) === 'number') {
                                        $value = number_format((float) $value, 2);
                                    } elseif (($column['format'] ?? null) === 'money') {
                                        $value = number_format((float) $value, 2);
                                    }
                                @endphp
                                <td @class(['is-right' => ($column['align'] ?? null) === 'right'])>{{ $value ?: '—' }}</td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($columns) }}">
                                <x-admin.empty-state title="No records" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                </table>
            </div>
        </x-dashboard.panel>
    @endforeach
</x-report.page>
@endsection
