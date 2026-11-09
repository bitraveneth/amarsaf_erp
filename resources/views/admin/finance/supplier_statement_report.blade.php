@extends('layouts.app')

@section('content')
@php
    $ccy = $currencyCode ?? config('app.currency', 'BDT');
    $summary = $summary ?? ['movements' => 0, 'billed' => 0, 'paid' => 0];
    $supplierOptions = ($suppliers ?? collect())->map(fn ($s) => [
        'id' => $s->id,
        'label' => $s->name,
        'meta' => trim(collect([$s->phone, $s->email])->filter()->implode(' · ')),
    ]);
    $typeBadge = [
        'bill' => 'erp-badge-brand',
        'payment' => 'erp-badge-success',
    ];
@endphp

<x-report.page
    eyebrow="Accounting reports"
    title="Supplier statement"
    subtitle="Opening balance, bills, and payments for one supplier."
    :period="$periodLabel"
>
    <x-slot:actions>
        <x-report.header-actions
            :range-action="route('admin.reports.supplier-statement')"
            :range="$range"
            :from="request('from', $from->toDateString())"
            :to="request('to', $to->toDateString())"
            :range-options="$rangeOptions"
            :period-label="$periodLabel"
        >
            <x-report.party-filter
                :action="route('admin.reports.supplier-statement')"
                name="supplier_id"
                label="Supplier"
                :options="$supplierOptions"
                :selected-id="$supplierId"
                :selected-label="$supplier?->name ?? 'Choose supplier'"
            />
            <x-report.hub-link category="accountant" />
            <x-report.export-actions module="supplier-statement" :from="$from" :to="$to" :export-query="array_filter(['supplier_id' => $supplierId])" />
        </x-report.header-actions>
    </x-slot:actions>

    @if($supplier)
        <x-slot:kpis>
            <x-dashboard.kpi label="Opening" :value="$ccy . ' ' . number_format($opening, 0)" hint="Due before period" />
            <x-dashboard.kpi label="Billed" :value="$ccy . ' ' . number_format($summary['billed'], 0)" :hint="$summary['movements'] . ' movements'" />
            <x-dashboard.kpi label="Paid" tone="success" :value="$ccy . ' ' . number_format($summary['paid'], 0)" hint="Payments in period" />
            <x-dashboard.kpi label="Closing" tone="warning" :value="$ccy . ' ' . number_format($closing, 0)" hint="Still payable" />
        </x-slot:kpis>

        <div class="erp-table-card">
            <div class="erp-table-card-header">
                <div>
                    <h2 class="erp-h3">{{ $supplier->name }}</h2>
                    <p class="erp-caption">
                        {{ collect([$supplier->phone, $supplier->email, $supplier->address ?? null])->filter()->implode(' · ') ?: 'Supplier statement' }}
                        · {{ $from->format('d M Y') }} – {{ $to->format('d M Y') }}
                    </p>
                </div>
                <p class="erp-caption">{{ number_format($summary['movements']) }} lines</p>
            </div>
            <div class="erp-table-wrap">
                <table class="erp-table w-full" style="table-layout: fixed; width: 100%;">
                    <colgroup>
                        <col style="width: 8rem">
                        <col style="width: 8.5rem">
                        <col style="width: 11rem">
                        <col>
                        <col style="width: 9.5rem">
                        <col style="width: 9.5rem">
                        <col style="width: 10rem">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Reference</th>
                            <th>Detail</th>
                            <th class="is-right">Debit</th>
                            <th class="is-right">Credit</th>
                            <th class="is-right">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="whitespace-nowrap">{{ $from->format('d M Y') }}</td>
                            <td><span class="erp-badge-brand">Opening</span></td>
                            <td colspan="2"><span class="erp-caption">Balance brought forward</span></td>
                            <td class="is-right"><span class="erp-caption">—</span></td>
                            <td class="is-right"><span class="erp-caption">—</span></td>
                            <td class="is-right"><span class="erp-table-num">{{ number_format($opening, 2) }}</span></td>
                        </tr>

                        @forelse($rows as $row)
                            @php
                                $badge = $typeBadge[$row['type_key'] ?? ''] ?? 'erp-badge-brand';
                            @endphp
                            <tr>
                                <td class="whitespace-nowrap">{{ $row['date'] instanceof \Illuminate\Support\Carbon ? $row['date']->format('d M Y') : '—' }}</td>
                                <td><span class="{{ $badge }}">{{ $row['type'] }}</span></td>
                                <td>
                                    @if(! empty($row['href']))
                                        <a href="{{ $row['href'] }}" class="erp-table-link font-mono">{{ $row['reference'] }}</a>
                                    @else
                                        <span class="font-mono text-sm">{{ $row['reference'] }}</span>
                                    @endif
                                </td>
                                <td class="min-w-0">
                                    <p class="truncate text-sm text-gray-700 dark:text-gray-200">{{ $row['detail'] ?: '—' }}</p>
                                </td>
                                <td class="is-right">
                                    <span class="erp-table-num">{{ $row['debit'] > 0 ? number_format($row['debit'], 2) : '—' }}</span>
                                </td>
                                <td class="is-right">
                                    <span class="erp-table-num">{{ $row['credit'] > 0 ? number_format($row['credit'], 2) : '—' }}</span>
                                </td>
                                <td class="is-right">
                                    <span class="erp-table-num">{{ number_format($row['balance'] ?? 0, 2) }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <x-admin.empty-state title="No activity in this period" description="Bills and payments for this supplier will appear here." />
                                </td>
                            </tr>
                        @endforelse

                        <tr>
                            <td class="whitespace-nowrap">{{ $to->format('d M Y') }}</td>
                            <td><span class="erp-badge-warning">Closing</span></td>
                            <td colspan="2"><span class="erp-caption">Balance carried forward</span></td>
                            <td class="is-right"><span class="erp-caption">—</span></td>
                            <td class="is-right"><span class="erp-caption">—</span></td>
                            <td class="is-right"><span class="erp-table-num">{{ number_format($closing, 2) }}</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="erp-report-prompt text-center">
            <p class="erp-report-prompt__title">Choose a supplier to open the statement</p>
            <p class="erp-report-prompt__text">
                Search below (or use Supplier in the header). The statement shows opening balance, bills, payments, and a running balance for the selected period.
            </p>

            <div class="mx-auto mt-6 max-w-xl text-left">
                <x-report.party-filter
                    variant="hero"
                    :action="route('admin.reports.supplier-statement')"
                    name="supplier_id"
                    label="Supplier"
                    :options="$supplierOptions"
                    :selected-id="$supplierId"
                    :selected-label="$supplier?->name ?? 'Choose supplier'"
                />
            </div>

            @if($suppliers->isNotEmpty())
                <div class="erp-report-prompt__picks">
                    @foreach($suppliers->take(8) as $quickSupplier)
                        <a
                            href="{{ route('admin.reports.supplier-statement', array_filter([
                                'supplier_id' => $quickSupplier->id,
                                'range' => $range,
                                'from' => request('from', $from->toDateString()),
                                'to' => request('to', $to->toDateString()),
                            ])) }}"
                            class="erp-report-prompt__pick"
                        >
                            <span class="truncate">{{ $quickSupplier->name }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    <x-admin.quick-guide
        title="How to read a supplier statement"
        lead="Pick one supplier, then review opening payable, bills, payments, and closing balance."
        :steps="[
            ['title' => 'Choose a supplier', 'body' => 'Search below or use Supplier in the header.'],
            ['title' => 'Set the period', 'body' => 'Opening is unpaid before From; lines are bills and payments in range.'],
            ['title' => 'Follow the running balance', 'body' => 'Bills increase what you owe; payments reduce it.'],
            ['title' => 'Export or print', 'body' => 'Use export with the same supplier and date range.'],
        ]"
        note="Outstanding bills report is a snapshot across all suppliers; this statement is one supplier’s ledger-style history."
        :open="false"
    />
</x-report.page>
@endsection
