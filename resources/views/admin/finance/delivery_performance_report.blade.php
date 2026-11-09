@extends('layouts.app')

@section('content')
@php
    $statusLabels = [
        'delivered' => 'Delivered',
        'in_transit' => 'In transit',
        'scheduled' => 'Scheduled',
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'failed' => 'Failed',
        'cancelled' => 'Cancelled',
        'returned' => 'Returned',
    ];
@endphp

<x-report.page
    eyebrow="Inventory reports"
    title="Delivery performance"
    subtitle="How many deliveries were created in the period, and how many reached delivered."
    :period="$periodLabel"
>
    <x-slot:actions>
        <x-report.header-actions
            :range-action="route('admin.reports.delivery-performance')"
            :range="$range"
            :from="request('from', $from->toDateString())"
            :to="request('to', $to->toDateString())"
            :range-options="$rangeOptions"
            :period-label="$periodLabel"
        >
            <x-report.hub-link category="operations" />
            <a href="{{ route('admin.deliveries.index') }}" class="erp-btn-secondary">All deliveries</a>
            <x-report.export-actions module="delivery-performance" :from="$from" :to="$to" />
        </x-report.header-actions>
    </x-slot:actions>

    <x-slot:kpis>
        <x-dashboard.kpi
            label="Deliveries"
            :value="(string) $totals['count']"
            hint="Created in this period"
        />
        <x-dashboard.kpi
            label="Delivered"
            tone="success"
            :value="(string) $totals['pod_complete']"
            :hint="$totals['pod_rate'] . '% completion rate'"
        />
        <x-dashboard.kpi
            label="Still open"
            :tone="($totals['open'] ?? 0) > 0 ? 'warning' : 'success'"
            :value="(string) ($totals['open'] ?? 0)"
            :hint="($totals['in_transit'] ?? 0) . ' in transit · ' . ($totals['scheduled'] ?? 0) . ' scheduled'"
        />
        <x-dashboard.kpi
            label="Failed / returned"
            :tone="($totals['failed'] ?? 0) > 0 ? 'danger' : 'default'"
            :value="(string) ($totals['failed'] ?? 0)"
            hint="Cancelled, failed, or returned"
        />
    </x-slot:kpis>

    <div class="erp-table-card">
        <div class="erp-table-card-header">
            <div>
                <h2 class="erp-h3">Delivery register</h2>
                <p class="erp-caption">Latest {{ $rows->count() }} of {{ number_format($totals['count']) }} deliveries in {{ $periodLabel }}</p>
            </div>
            <a href="{{ route('admin.deliveries.index') }}" class="erp-link text-sm">Open delivery board</a>
        </div>

        @if($rows->isNotEmpty())
            <div class="erp-table-wrap">
                <table class="erp-table w-full" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Order</th>
                            <th>Agent</th>
                            <th>Route</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $delivery)
                            @php
                                $status = strtolower((string) $delivery->status);
                                $badge = match ($status) {
                                    'delivered' => 'erp-badge-success',
                                    'in_transit' => 'erp-badge-brand',
                                    'failed', 'cancelled', 'returned' => 'erp-badge-warning',
                                    default => 'erp-badge-neutral',
                                };
                                $href = route('admin.deliveries.show', $delivery);
                            @endphp
                            <tr
                                class="cursor-pointer"
                                onclick="if (!event.target.closest('a,button')) window.location = this.dataset.href"
                                data-href="{{ $href }}"
                            >
                                <td class="erp-table-cell whitespace-nowrap">{{ $delivery->created_at?->format('d M Y') ?? '—' }}</td>
                                <td>
                                    <a href="{{ $href }}" class="erp-table-link">{{ $delivery->order?->number ?? ('Order #' . $delivery->order_id) }}</a>
                                </td>
                                <td class="erp-table-cell">{{ $delivery->order?->agent?->name ?? '—' }}</td>
                                <td class="erp-table-cell">{{ $delivery->route?->name ?? '—' }}</td>
                                <td>
                                    <span class="{{ $badge }}">{{ $statusLabels[$status] ?? ucfirst(str_replace('_', ' ', $status)) }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <x-admin.empty-state
                title="No deliveries in this period"
                description="Try another date range, or create deliveries from confirmed sales orders."
            >
                <x-slot:action>
                    <a href="{{ route('admin.deliveries.index') }}" class="erp-btn-primary">Open deliveries</a>
                </x-slot:action>
            </x-admin.empty-state>
        @endif
    </div>
</x-report.page>
@endsection
