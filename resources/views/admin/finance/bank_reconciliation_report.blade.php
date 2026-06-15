@extends('layouts.app')

@section('content')
<x-report.page eyebrow="Accountant tools" title="Bank reconciliation" subtitle="Customer receipts and supplier payments in the period." :period="$periodLabel">
    <x-slot:actions>
        <x-report.header-actions :range-action="route('admin.reports.bank-reconciliation')" :range="$range" :from="request('from', $from->toDateString())" :to="request('to', $to->toDateString())" :range-options="$rangeOptions" :period-label="$periodLabel">
            <x-report.hub-link category="accountant" />
            <a href="{{ route('admin.finance.reconciliation') }}" class="erp-btn-secondary">Reconcile</a>
            <x-report.export-actions module="bank-reconciliation" :from="$from" :to="$to" />
        </x-report.header-actions>
    </x-slot:actions>
    <x-slot:kpis>
        <x-dashboard.kpi label="Receipts in" tone="success" :value="$currencyCode . ' ' . number_format($totals['receipts_total'], 2)" :hint="$totals['receipts_count'] . ' receipts'" />
        <x-dashboard.kpi label="Payments out" tone="warning" :value="$currencyCode . ' ' . number_format($totals['payments_total'], 2)" :hint="$totals['payments_count'] . ' payments'" />
        <x-dashboard.kpi label="Net movement" :value="$currencyCode . ' ' . number_format($totals['net'], 2)" hint="Receipts minus payments" />
    </x-slot:kpis>
    <div class="grid gap-6 lg:grid-cols-2">
        <x-dashboard.panel title="Receipts" subtitle="Customer collections in period">
            <x-report.table>
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <x-report.th>Date</x-report.th>
                        <x-report.th>Reference</x-report.th>
                        <x-report.th>Customer</x-report.th>
                        <x-report.th align="right">Amount</x-report.th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($receipts as $receipt)
                        <tr class="border-b border-gray-50 dark:border-gray-800/60">
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ $receipt->received_at?->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $receipt->reference ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ $receipt->invoice?->order?->agent?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-right text-sm tabular-nums font-medium text-gray-900 dark:text-white">{{ number_format((float) $receipt->amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-8"><x-admin.empty-state title="No receipts in this period" /></td></tr>
                    @endforelse
                </tbody>
            </x-report.table>
        </x-dashboard.panel>
        <x-dashboard.panel title="Payments" subtitle="Supplier bill payments in period">
            <x-report.table>
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <x-report.th>Date</x-report.th>
                        <x-report.th>Reference</x-report.th>
                        <x-report.th>Supplier</x-report.th>
                        <x-report.th align="right">Amount</x-report.th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr class="border-b border-gray-50 dark:border-gray-800/60">
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ $payment->paid_at?->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $payment->reference ?? $payment->bill?->bill_number ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ $payment->bill?->supplier?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-right text-sm tabular-nums font-medium text-gray-900 dark:text-white">{{ number_format((float) $payment->amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-8"><x-admin.empty-state title="No payments in this period" /></td></tr>
                    @endforelse
                </tbody>
            </x-report.table>
        </x-dashboard.panel>
    </div>
</x-report.page>
@endsection
