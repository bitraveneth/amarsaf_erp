@extends('layouts.app')

@section('content')
@php
    $currency = config('app.currency', 'BDT');
    $total = (float) $bill->gross_total;
    $paid = (float) $bill->paid_total;
    $due = (float) $bill->outstanding;
    $isOverdue = $bill->due_date && $bill->due_date->isPast() && $due > 0;
    $statusLabels = [
        'open' => 'Open',
        'part_paid' => 'Part paid',
        'paid' => 'Paid',
    ];
    $badge = match ($bill->status) {
        'paid' => 'erp-badge-success',
        'part_paid' => 'erp-badge-warning',
        default => 'erp-badge-brand',
    };
    $methods = [
        'bank_transfer' => 'Bank transfer',
        'cash' => 'Cash',
        'bkash' => 'bKash',
        'cheque' => 'Cheque',
    ];
@endphp
<div class="erp-page">
    <x-admin.page-header
        :title="$bill->number"
        :subtitle="'Purchase bill · ' . ($bill->supplier->name ?? '—')"
    >
        <x-slot:actions>
            <span class="{{ $badge }}">{{ $statusLabels[$bill->status] ?? ucfirst(str_replace('_', ' ', $bill->status)) }}</span>
            <a href="{{ route('admin.bills.index') }}" class="erp-btn-secondary">All bills</a>
            <x-admin.document-actions type="purchase-bill" :id="$bill->id" compact />
            @if($bill->payments->isEmpty())
                <a href="{{ route('admin.bills.edit', $bill) }}" class="erp-btn-secondary">Edit</a>
            @endif
        </x-slot:actions>
    </x-admin.page-header>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-admin.stat-card label="Total" :value="$currency . ' ' . number_format($total, 2)" :hint="'Net ' . number_format($bill->net_total, 2) . ' · VAT ' . number_format($bill->vat_amount, 2)" />
        <x-admin.stat-card label="Bill date" :value="$bill->bill_date->format('d M Y')" :hint="$bill->due_date ? 'Due ' . $bill->due_date->format('d M Y') . ($isOverdue ? ' · overdue' : '') : 'No due date'" />
        <x-admin.stat-card label="Paid" :value="$currency . ' ' . number_format($paid, 2)" :hint="$bill->payments->count() . ' ' . Str::plural('payment', $bill->payments->count())" tone="success" />
        <x-admin.stat-card label="Outstanding" :value="$currency . ' ' . number_format($due, 2)" :tone="$due > 0 ? 'warning' : 'success'" />
    </div>

    <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
        <div class="space-y-5 xl:col-span-2">
            <div class="erp-table-card">
                <div class="erp-table-card-header">
                    <div>
                        <h2 class="erp-h3">Bill lines</h2>
                        <p class="erp-caption mt-1">
                            {{ $bill->items->count() }} {{ Str::plural('line', $bill->items->count()) }}
                            @if($bill->warehouse)
                                · received into
                                <span class="font-medium text-gray-800 dark:text-gray-200">{{ $bill->warehouse->name }}</span>
                            @endif
                        </p>
                    </div>
                    @if($bill->warehouse)
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-gray-200 bg-gray-50 px-3 py-1 text-xs font-medium text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                            <svg class="h-3.5 w-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/>
                            </svg>
                            {{ $bill->warehouse->name }}
                        </span>
                    @endif
                </div>
                <div class="erp-table-wrap">
                    <table class="erp-table w-full">
                        <thead>
                            <tr>
                                <th class="w-10">#</th>
                                <th>Item</th>
                                <th class="is-right">Qty</th>
                                <th class="is-right">Unit price</th>
                                <th class="is-right">VAT</th>
                                <th class="is-right">Line total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bill->items as $index => $item)
                                <tr>
                                    <td class="erp-caption">{{ $index + 1 }}</td>
                                    <td>
                                        <p class="erp-body-strong">{{ $item->description }}</p>
                                        @if($item->product)
                                            <p class="erp-caption mt-0.5">
                                                @if($item->product->sku)
                                                    <span class="font-mono">{{ $item->product->sku }}</span>
                                                    <span class="mx-1 text-gray-300 dark:text-gray-600">·</span>
                                                @endif
                                                {{ $item->product->name }}
                                            </p>
                                        @endif
                                    </td>
                                    <td class="is-right"><span class="erp-table-num">{{ number_format($item->quantity) }}</span></td>
                                    <td class="is-right"><span class="erp-table-num">{{ number_format($item->unit_price, 2) }}</span></td>
                                    <td class="is-right">
                                        <span class="erp-table-num">{{ number_format($item->vat_amount, 2) }}</span>
                                        @if((float) $item->vat_rate > 0)
                                            <span class="erp-caption block">{{ number_format($item->vat_rate, 1) }}%</span>
                                        @endif
                                    </td>
                                    <td class="is-right"><span class="erp-table-num font-semibold">{{ number_format($item->line_total, 2) }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="erp-caption">No lines on this bill.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($bill->items->isNotEmpty())
                            <tfoot>
                                <tr class="border-t border-gray-200 dark:border-gray-700">
                                    <td colspan="4" class="erp-caption pt-3">Totals</td>
                                    <td class="is-right pt-3"><span class="erp-table-num">{{ number_format($bill->vat_amount, 2) }}</span></td>
                                    <td class="is-right pt-3"><span class="erp-table-num font-semibold">{{ number_format($bill->net_total, 2) }}</span></td>
                                </tr>
                                <tr>
                                    <td colspan="5" class="is-right erp-caption">Gross (net + VAT)</td>
                                    <td class="is-right"><span class="erp-table-num text-base font-bold text-gray-900 dark:text-white">{{ $currency }} {{ number_format($total, 2) }}</span></td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>

            <div class="erp-table-card">
                <div class="erp-table-card-header">
                    <div>
                        <h2 class="erp-h3">Payments</h2>
                        <p class="erp-caption mt-1">Cash out against this supplier bill</p>
                    </div>
                    <p class="erp-caption">{{ $currency }} {{ number_format($paid, 2) }} paid</p>
                </div>
                <div class="erp-table-wrap">
                    <table class="erp-table w-full">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Method</th>
                                <th>Reference</th>
                                <th class="is-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bill->payments as $payment)
                                <tr>
                                    <td class="erp-table-cell">{{ $payment->paid_at?->format('d M Y') }}</td>
                                    <td class="erp-table-cell">{{ $payment->method ? ucfirst(str_replace('_', ' ', $payment->method)) : '—' }}</td>
                                    <td class="erp-table-cell">{{ $payment->batch_reference ?: '—' }}</td>
                                    <td class="is-right"><span class="erp-table-num">{{ $currency }} {{ number_format($payment->amount, 2) }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="erp-caption">No payments recorded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="space-y-5">
            <div class="erp-form-card">
                <div class="px-5 py-4 sm:px-6">
                    <p class="erp-body-strong">Supplier</p>
                    <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ $bill->supplier->name ?? '—' }}</p>
                    @if($bill->supplier?->phone || $bill->supplier?->tax_id || $bill->supplier?->address)
                        <dl class="mt-4 space-y-2 text-sm text-gray-600 dark:text-gray-400">
                            @if($bill->supplier?->phone)
                                <div class="flex justify-between gap-3">
                                    <dt>Phone</dt>
                                    <dd class="text-right text-gray-900 dark:text-gray-200">{{ $bill->supplier->phone }}</dd>
                                </div>
                            @endif
                            @if($bill->supplier?->tax_id)
                                <div class="flex justify-between gap-3">
                                    <dt>BIN / tax ID</dt>
                                    <dd class="text-right font-mono text-gray-900 dark:text-gray-200">{{ $bill->supplier->tax_id }}</dd>
                                </div>
                            @endif
                            @if($bill->supplier?->address)
                                <div>
                                    <dt class="mb-1">Address</dt>
                                    <dd class="text-gray-900 dark:text-gray-200">{{ $bill->supplier->address }}</dd>
                                </div>
                            @endif
                        </dl>
                    @endif
                </div>
            </div>

            @if($due > 0)
                <form action="{{ route('admin.bills.pay', $bill) }}" method="POST" class="erp-form-card">
                    @csrf
                    <input type="hidden" name="redirect" value="show">
                    <x-admin.form.section title="Record payment" description="Pays against the remaining balance.">
                        <x-admin.form.field label="Amount" name="amount" :hint="'Remaining ' . $currency . ' ' . number_format($due, 2)">
                            <input type="number" id="amount" name="amount" step="0.01" min="0.01" max="{{ $due }}" value="{{ old('amount', number_format($due, 2, '.', '')) }}" class="erp-input" required>
                        </x-admin.form.field>
                        <x-admin.form.field label="Paid on" name="paid_at">
                            <input type="date" id="paid_at" name="paid_at" value="{{ old('paid_at', now()->toDateString()) }}" class="erp-input">
                        </x-admin.form.field>
                        <x-admin.form.field label="Method" name="method">
                            <select id="method" name="method" class="erp-select">
                                @foreach($methods as $value => $label)
                                    <option value="{{ $value }}" @selected(old('method', 'bank_transfer') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </x-admin.form.field>
                    </x-admin.form.section>
                    <div class="erp-form-actions">
                        <button type="submit" class="erp-btn-primary">Record payment</button>
                    </div>
                </form>
            @endif

            @if(Route::has('admin.bills.destroy'))
                <form action="{{ route('admin.bills.destroy', $bill) }}" method="POST" onsubmit="return confirm('Delete this bill?');" class="erp-form-card">
                    @csrf
                    @method('DELETE')
                    <div class="flex items-center justify-between gap-3 px-5 py-4 sm:px-6">
                        <div>
                            <p class="erp-body-strong">Delete bill</p>
                            <p class="erp-caption">Removes the bill when it has no payments or stock.</p>
                        </div>
                        <button type="submit" class="erp-btn-danger">Delete</button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
