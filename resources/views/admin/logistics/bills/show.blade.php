@extends('layouts.app')

@section('content')
@php
    $currencyCode = config('app.currency', 'BDT');
    $carrier = $bill->transportCarrier;
    $carrierInitial = strtoupper(substr(trim($carrier->name ?? '—'), 0, 1)) ?: '—';
    $grossTotal = (float) $bill->gross_total;
    $paidTotal = (float) $bill->paid_total;
    $outstanding = (float) $bill->outstanding;
    $paidPercent = $grossTotal > 0 ? min(100, round(($paidTotal / $grossTotal) * 100, 1)) : ($outstanding <= 0 ? 100 : 0);
    $isFullyPaid = $outstanding <= 0.00001;

    $statusLabels = [
        'draft' => 'Draft',
        'open' => 'Open',
        'part_paid' => 'Part paid',
        'paid' => 'Paid',
    ];
    $statusTones = [
        'draft' => 'neutral',
        'open' => 'brand',
        'part_paid' => 'warning',
        'paid' => 'success',
    ];
    $statusTone = $statusTones[$bill->status] ?? 'neutral';
    $statusLabel = $statusLabels[$bill->status] ?? ucfirst(str_replace('_', ' ', $bill->status));
@endphp

<div class="erp-order-page erp-order-page--index erp-po-show">
    <x-admin.order-toolbar
        :title="$bill->document_number"
        :subtitle="'Logistics bill · ' . ($carrier->name ?? '—')"
        :back-url="route('admin.logistics-bills.index')"
        back-label="All logistics bills"
    >
        <x-slot:actions>
            <span class="erp-po-status erp-po-status--{{ $statusTone }}">{{ $statusLabel }}</span>
            <x-admin.document-actions type="logistics-bill" :id="$bill->id" compact />
            @if($outstanding > 0)
                <a href="#record-payment" class="erp-order-btn erp-order-btn--brand">Record payment</a>
            @endif
            <x-admin.action-group>
                <x-admin.action-delete
                    :action="route('admin.logistics-bills.destroy', $bill)"
                    confirm="Delete logistics bill {{ $bill->document_number }} and all related payments?"
                />
            </x-admin.action-group>
        </x-slot:actions>
    </x-admin.order-toolbar>

    @if(session('status'))
        <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200">
            {{ session('status') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-5 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-100">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="erp-po-index-stats">
        <div class="erp-po-index-stat">
            <div class="erp-po-index-stat__head">
                <span class="erp-po-index-stat__label">Gross total</span>
                <span class="erp-po-index-stat__icon erp-po-index-stat__icon--brand">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <p class="erp-po-index-stat__value">{{ $currencyCode }} {{ number_format($grossTotal, 0) }}</p>
            <p class="erp-po-index-stat__hint">{{ $bill->lines->count() }} {{ Str::plural('line', $bill->lines->count()) }} · net {{ number_format($bill->net_total, 0) }}</p>
        </div>

        <div class="erp-po-index-stat">
            <div class="erp-po-index-stat__head">
                <span class="erp-po-index-stat__label">Bill date</span>
                <span class="erp-po-index-stat__icon erp-po-index-stat__icon--neutral">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </span>
            </div>
            <p class="erp-po-index-stat__value !text-xl">{{ $bill->bill_date->format('d M Y') }}</p>
            <p class="erp-po-index-stat__hint">Due {{ $bill->due_date?->format('d M Y') ?? '—' }}</p>
        </div>

        <div class="erp-po-index-stat">
            <div class="erp-po-index-stat__head">
                <span class="erp-po-index-stat__label">Paid</span>
                <span class="erp-po-index-stat__icon erp-po-index-stat__icon--warning">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <p class="erp-po-index-stat__value">{{ number_format($paidPercent, $paidPercent == floor($paidPercent) ? 0 : 1) }}%</p>
            <p class="erp-po-index-stat__hint">{{ $currencyCode }} {{ number_format($paidTotal, 0) }} of {{ number_format($grossTotal, 0) }}</p>
        </div>

        <div class="erp-po-index-stat">
            <div class="erp-po-index-stat__head">
                <span class="erp-po-index-stat__label">Outstanding</span>
                <span class="erp-po-index-stat__icon erp-po-index-stat__icon--{{ $isFullyPaid ? 'success' : 'warning' }}">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </span>
            </div>
            <p class="erp-po-index-stat__value">{{ $currencyCode }} {{ number_format($outstanding, 0) }}</p>
            <p class="erp-po-index-stat__hint">{{ $bill->payments->count() }} {{ Str::plural('payment', $bill->payments->count()) }}</p>
        </div>
    </div>

    <div class="erp-po-show-layout">
        <div class="erp-po-show-main space-y-5">
            <div class="erp-order-list-card erp-po-index-table-card">
                <div class="erp-po-index-table-card__head">
                    <div>
                        <h2 class="erp-po-index-table-card__title">Line items</h2>
                        <p class="erp-po-index-table-card__desc">Freight, courier, and transport charges on this bill.</p>
                    </div>
                    <span class="erp-po-index-table-card__badge">
                        {{ $bill->lines->count() }} {{ Str::plural('line', $bill->lines->count()) }}
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="erp-order-list-table erp-po-index-table erp-po-show-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Description</th>
                                <th class="is-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bill->lines as $index => $line)
                                <tr>
                                    <td class="whitespace-nowrap text-gray-500">{{ $index + 1 }}</td>
                                    <td>
                                        <p class="font-semibold text-gray-900 dark:text-white">{{ $line->description }}</p>
                                    </td>
                                    <td class="is-right whitespace-nowrap">
                                        <span class="font-semibold text-gray-900 dark:text-white">
                                            {{ $currencyCode }} {{ number_format($line->amount, 2) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2" class="!px-5 !py-3 text-right text-sm text-gray-600 dark:text-gray-300">Net total</td>
                                <td class="!px-5 !py-3 text-right text-sm font-medium">{{ $currencyCode }} {{ number_format($bill->net_total, 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="2" class="!px-5 !py-3 text-right text-sm text-gray-600 dark:text-gray-300">VAT</td>
                                <td class="!px-5 !py-3 text-right text-sm font-medium">{{ $currencyCode }} {{ number_format($bill->vat_amount, 2) }}</td>
                            </tr>
                            <tr class="erp-po-show-table__total">
                                <td colspan="2" class="!px-5 !py-4 text-right text-sm font-semibold text-gray-600 dark:text-gray-300">Gross total</td>
                                <td class="!px-5 !py-4 text-right">
                                    <span class="text-lg font-bold text-brand-600 dark:text-brand-400">{{ $currencyCode }} {{ number_format($grossTotal, 2) }}</span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            @if($bill->payments->isNotEmpty())
                <div class="erp-order-list-card erp-po-index-table-card">
                    <div class="erp-po-index-table-card__head">
                        <div>
                            <h2 class="erp-po-index-table-card__title">Payments</h2>
                            <p class="erp-po-index-table-card__desc">Bank and settlement entries against this bill.</p>
                        </div>
                        <span class="erp-po-index-table-card__badge">{{ $bill->payments->count() }} posted</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="erp-order-list-table erp-po-index-table erp-po-show-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Method</th>
                                    <th class="is-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($bill->payments as $payment)
                                    <tr>
                                        <td class="whitespace-nowrap">{{ $payment->paid_at->format('d M Y') }}</td>
                                        <td>{{ $payment->method ?: '—' }}</td>
                                        <td class="is-right whitespace-nowrap font-semibold">{{ $currencyCode }} {{ number_format($payment->amount, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            @if($bill->notes)
                <div class="erp-po-show-panel">
                    <h3 class="erp-po-show-panel__title">Notes</h3>
                    <p class="erp-po-show-panel__body">{{ $bill->notes }}</p>
                </div>
            @endif
        </div>

        <aside class="erp-po-show-side space-y-5">
            <div class="erp-po-show-panel">
                <h3 class="erp-po-show-panel__title">Transport carrier</h3>
                <div class="erp-po-index-supplier mt-3">
                    <span class="erp-po-index-supplier__avatar !h-10 !w-10">{{ $carrierInitial }}</span>
                    <div class="min-w-0">
                        <p class="erp-po-index-supplier__name !text-base">{{ $carrier->name ?? '—' }}</p>
                        @if($carrier?->contact_person)
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $carrier->contact_person }}</p>
                        @endif
                    </div>
                </div>
                <dl class="erp-po-show-meta mt-4">
                    @if($carrier?->phone)
                        <div class="erp-po-show-meta__row">
                            <dt>Phone</dt>
                            <dd>{{ $carrier->phone }}</dd>
                        </div>
                    @endif
                    @if($carrier?->email)
                        <div class="erp-po-show-meta__row">
                            <dt>Email</dt>
                            <dd class="break-all">{{ $carrier->email }}</dd>
                        </div>
                    @endif
                    @if($carrier?->tax_id)
                        <div class="erp-po-show-meta__row">
                            <dt>Tax ID</dt>
                            <dd>{{ $carrier->tax_id }}</dd>
                        </div>
                    @endif
                    <div class="erp-po-show-meta__row">
                        <dt>Directory</dt>
                        <dd>
                            <a href="{{ route('admin.logistics.carriers.edit', $carrier) }}" class="text-brand-600 hover:underline dark:text-brand-400">
                                View carrier
                            </a>
                        </dd>
                    </div>
                </dl>
            </div>

            <div class="erp-po-show-panel">
                <h3 class="erp-po-show-panel__title">Bill details</h3>
                <dl class="erp-po-show-meta mt-4">
                    <div class="erp-po-show-meta__row">
                        <dt>Service</dt>
                        <dd>{{ $bill->serviceTypeLabel() }}</dd>
                    </div>
                    <div class="erp-po-show-meta__row">
                        <dt>Route</dt>
                        <dd>{{ $bill->deliveryRoute->name ?? '—' }}</dd>
                    </div>
                    <div class="erp-po-show-meta__row">
                        <dt>Trip date</dt>
                        <dd>{{ $bill->trip_date?->format('d M Y') ?? '—' }}</dd>
                    </div>
                    @if($bill->number)
                        <div class="erp-po-show-meta__row">
                            <dt>Vendor ref.</dt>
                            <dd>{{ $bill->number }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            <div class="erp-po-show-panel">
                <h3 class="erp-po-show-panel__title">Payment progress</h3>
                <div class="mt-4">
                    <div class="flex items-end justify-between gap-3">
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($paidPercent, 0) }}%</p>
                        <span class="erp-po-status erp-po-status--{{ $statusTone }}">{{ $statusLabel }}</span>
                    </div>
                    <div class="erp-po-show-receive__bar mt-3 !h-2.5" aria-hidden="true">
                        <span @class([
                            'erp-po-show-receive__fill',
                            'is-complete' => $isFullyPaid,
                            'is-partial' => ! $isFullyPaid && $paidTotal > 0,
                        ]) style="width: {{ $paidPercent }}%"></span>
                    </div>
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                        {{ $currencyCode }} {{ number_format($paidTotal, 2) }} paid · {{ $currencyCode }} {{ number_format($outstanding, 2) }} outstanding
                    </p>
                </div>
            </div>

            <div class="erp-po-show-panel">
                <h3 class="erp-po-show-panel__title">Documents</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Print or download a PDF copy of this carrier bill.</p>
                <div class="mt-4">
                    <x-admin.document-actions type="logistics-bill" :id="$bill->id" />
                </div>
            </div>

            @if($outstanding > 0)
                <div class="erp-po-show-panel" id="record-payment">
                    <h3 class="erp-po-show-panel__title">Record payment</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Outstanding {{ $currencyCode }} {{ number_format($outstanding, 2) }}</p>
                    <form action="{{ route('admin.logistics-bills.pay', $bill) }}" method="POST" class="mt-4 space-y-3">
                        @csrf
                        <div>
                            <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">Amount</label>
                            <input type="number" name="amount" step="0.01" min="0.01" max="{{ $outstanding }}" value="{{ old('amount', $outstanding) }}"
                                   class="h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            @error('amount')<p class="mt-1 text-sm text-error-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">Paid on</label>
                            <input type="date" name="paid_at" value="{{ old('paid_at', now()->format('Y-m-d')) }}"
                                   class="h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">Method</label>
                            <input type="text" name="method" value="{{ old('method', 'Bank transfer') }}" placeholder="Bank transfer"
                                   class="h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        </div>
                        <button type="submit" class="erp-order-btn erp-order-btn--success w-full justify-center">Post payment</button>
                    </form>
                </div>
            @endif

            <div class="erp-po-show-panel">
                <h3 class="erp-po-show-panel__title">Ledger</h3>
                <p class="erp-po-show-panel__body mt-2">Posted to delivery/courier expense and trade creditors. Payments reduce payables and bank.</p>
                <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">Analytic: carrier:{{ $bill->transport_carrier_id }}</p>
            </div>
        </aside>
    </div>
</div>
@endsection
