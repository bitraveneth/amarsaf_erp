@extends('layouts.app')

@section('content')
@php
    $currency = config('app.currency', 'BDT');
    $statusLabels = [
        'open' => 'Open',
        'part_paid' => 'Part paid',
        'paid' => 'Paid',
    ];
@endphp
<div class="erp-page">
    <x-admin.page-header
        title="Purchase bills"
        subtitle="Supplier bills and the balance still to pay."
    >
        <x-slot:actions>
            <a href="{{ route('admin.bills.create') }}" class="erp-btn-primary">Add bill</a>
        </x-slot:actions>
    </x-admin.page-header>

    <x-dashboard.snapshot-kpis
        :show-header="false"
        title="Purchase bills"
        :cards="[
            [
                'label' => 'Bills',
                'numeric' => number_format((int) $stats->bill_count),
                'caption' => 'Supplier bills',
                'href' => route('admin.bills.index'),
                'tone' => 'brand',
                'valueTone' => 'neutral',
                'icon' => 'orders',
            ],
            [
                'label' => 'Outstanding',
                'numeric' => number_format((float) $stats->outstanding, 2),
                'currency' => $currency,
                'caption' => ((int) $stats->due_count) . ' ' . Str::plural('bill', (int) $stats->due_count) . ' still due',
                'href' => route('admin.bills.index'),
                'tone' => 'orange',
                'valueTone' => 'warning',
                'icon' => 'receivables',
            ],
            [
                'label' => 'Paid',
                'numeric' => number_format((float) $stats->paid, 2),
                'currency' => $currency,
                'caption' => 'Settled on these bills',
                'href' => route('admin.bills.index'),
                'tone' => 'success',
                'valueTone' => 'neutral',
                'icon' => 'revenue',
            ],
            [
                'label' => 'Overdue',
                'numeric' => number_format((int) $stats->overdue),
                'caption' => 'Past the due date',
                'href' => route('admin.bills.index'),
                'tone' => ((int) $stats->overdue > 0) ? 'error' : 'blue',
                'valueTone' => ((int) $stats->overdue > 0) ? 'danger' : 'neutral',
                'icon' => 'returns',
            ],
        ]"
    />

    @if($bills->isNotEmpty())
            <div class="erp-table-card">
                <div class="erp-table-card-header">
                    <h2 class="erp-h3">Bill register</h2>
                    <p class="erp-caption">{{ $bills->total() }} {{ Str::plural('bill', $bills->total()) }}. Open a bill to see lines, or pay the balance from the row.</p>
                </div>
                <div class="erp-table-wrap">
                    <table class="erp-table w-full">
                        <thead>
                            <tr>
                                <th>Bill</th>
                                <th>Supplier</th>
                                <th>Date</th>
                                <th class="is-right">Total</th>
                                <th>Status</th>
                                <th class="is-right">Paid</th>
                                <th class="is-right">Pay</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bills as $bill)
                                @php
                                    $paid = (float) $bill->paid_total;
                                    $total = (float) $bill->gross_total;
                                    $due = (float) $bill->outstanding;
                                    $isOverdue = $bill->due_date && $bill->due_date->isPast() && $due > 0;
                                    $status = $bill->status;
                                    $badge = match ($status) {
                                        'paid' => 'erp-badge-success',
                                        'part_paid' => 'erp-badge-warning',
                                        default => 'erp-badge-brand',
                                    };
                                @endphp
                                <tr
                                    class="cursor-pointer {{ $isOverdue ? 'bg-error-50/40 dark:bg-error-500/5' : '' }}"
                                    onclick="if (!event.target.closest('a,button,input,label,select,form')) window.location = this.dataset.href"
                                    data-href="{{ route('admin.bills.show', $bill) }}"
                                >
                                    <td>
                                        <a href="{{ route('admin.bills.show', $bill) }}" class="erp-table-link">{{ $bill->number }}</a>
                                    </td>
                                    <td>
                                        <p class="erp-body-strong">{{ $bill->supplier->name }}</p>
                                        @if($bill->supplier->tax_id)
                                            <p class="erp-caption">Tax {{ $bill->supplier->tax_id }}</p>
                                        @endif
                                    </td>
                                    <td>
                                        <p class="erp-table-cell">{{ $bill->bill_date->format('d M Y') }}</p>
                                        @if($bill->due_date)
                                            <p class="erp-caption {{ $isOverdue ? 'text-error-600 dark:text-error-400' : '' }}">
                                                Due {{ $bill->due_date->format('d M Y') }}{{ $isOverdue ? ' · overdue' : '' }}
                                            </p>
                                        @endif
                                    </td>
                                    <td class="is-right">
                                        <span class="erp-table-num">{{ $currency }} {{ number_format($total, 2) }}</span>
                                    </td>
                                    <td>
                                        <span class="{{ $badge }}">{{ $statusLabels[$status] ?? ucfirst(str_replace('_', ' ', $status)) }}</span>
                                        @if($due > 0)
                                            <p class="erp-caption mt-1">Due {{ $currency }} {{ number_format($due, 2) }}</p>
                                        @endif
                                    </td>
                                    <td class="is-right">
                                        <span class="erp-table-num">{{ $currency }} {{ number_format($paid, 2) }}</span>
                                    </td>
                                    <td class="is-right">
                                        @if($due > 0)
                                            <form action="{{ route('admin.bills.pay', $bill) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="erp-btn-primary !px-3 !py-1.5 !text-sm">Pay bill</button>
                                            </form>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 rounded-lg border border-success-200 bg-success-50 px-2.5 py-1.5 text-sm font-medium text-success-700 dark:border-success-500/30 dark:bg-success-500/10 dark:text-success-300">
                                                <svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                    <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                                                </svg>
                                                Paid
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        <div>{{ $bills->links() }}</div>
    @else
        <x-admin.empty-state
            title="No purchase bills"
            description="Add a supplier bill to start tracking what is still unpaid."
        >
            <x-slot:action>
                <a href="{{ route('admin.bills.create') }}" class="erp-btn-primary">Add bill</a>
            </x-slot:action>
        </x-admin.empty-state>
    @endif

    <x-admin.quick-guide
        title="How purchase bills work"
        lead="Bills are what you owe suppliers. Outstanding = bill total minus payments."
        :steps="[
            ['title' => 'Add bill', 'body' => 'Record the supplier invoice with lines and VAT.'],
            ['title' => 'Review outstanding', 'body' => 'KPIs show still due, paid, and overdue counts.'],
            ['title' => 'Pay from the row', 'body' => 'Use Pay bill when money leaves the bank — no need to open the bill first.'],
            ['title' => 'Check statements', 'body' => 'Supplier statement shows running balance for one supplier.'],
        ]"
        note="Part-paid bills stay open until the remaining balance is settled."
        :open="false"
    />
</div>
@endsection
