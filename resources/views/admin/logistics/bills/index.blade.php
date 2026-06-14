@extends('layouts.app')

@section('content')
@php
    $currencyCode = config('app.currency', 'BDT');
    $statusFilter = $statusFilter ?? null;
    $stats = $stats ?? ['total' => 0, 'open' => 0, 'part_paid' => 0, 'paid' => 0, 'unpaid_value' => 0, 'period_billed' => 0];

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

    $filterTabs = [
        ['value' => null, 'label' => 'All bills'],
        ['value' => 'open', 'label' => 'Open'],
        ['value' => 'part_paid', 'label' => 'Part paid'],
        ['value' => 'paid', 'label' => 'Paid'],
    ];

    $filterQuery = array_filter([
        'status' => $statusFilter,
        'from' => $from?->format('Y-m-d'),
        'to' => $to?->format('Y-m-d'),
        'transport_carrier_id' => $carrierId,
    ], fn ($value) => filled($value));
@endphp

<div class="erp-order-page erp-order-page--index screen-logistics-bills">
    <x-admin.order-toolbar
        title="Logistics bills"
        subtitle="Carrier invoices for hired truck, courier, and external freight."
        :back-url="route('admin.logistics.dashboard')"
        back-label="Logistics dashboard"
    >
        <x-slot:actions>
            <a href="{{ route('admin.logistics.carriers.index') }}" class="erp-order-btn erp-order-btn--secondary">Carriers</a>
            <a href="{{ route('admin.logistics-bills.create') }}" class="erp-order-btn erp-order-btn--primary">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New logistics bill
            </a>
        </x-slot:actions>
    </x-admin.order-toolbar>

    @if(session('status'))
        <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200">
            {{ session('status') }}
        </div>
    @endif

    <div class="erp-po-index-stats">
        <div class="erp-po-index-stat">
            <div class="erp-po-index-stat__head">
                <span class="erp-po-index-stat__label">Total bills</span>
                <span class="erp-po-index-stat__icon erp-po-index-stat__icon--brand">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </span>
            </div>
            <p class="erp-po-index-stat__value">{{ number_format($stats['total']) }}</p>
            <p class="erp-po-index-stat__hint">All carrier bills</p>
        </div>

        <a href="{{ route('admin.logistics-bills.index', ['status' => 'open']) }}" class="erp-po-index-stat erp-po-index-stat--link">
            <div class="erp-po-index-stat__head">
                <span class="erp-po-index-stat__label">Open</span>
                <span class="erp-po-index-stat__icon erp-po-index-stat__icon--neutral">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <p class="erp-po-index-stat__value">{{ number_format($stats['open']) }}</p>
            <p class="erp-po-index-stat__hint">Awaiting payment →</p>
        </a>

        <div class="erp-po-index-stat">
            <div class="erp-po-index-stat__head">
                <span class="erp-po-index-stat__label">Unpaid value</span>
                <span class="erp-po-index-stat__icon erp-po-index-stat__icon--warning">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </span>
            </div>
            <p class="erp-po-index-stat__value">{{ $currencyCode }} {{ number_format($stats['unpaid_value'], 0) }}</p>
            <p class="erp-po-index-stat__hint">{{ number_format($stats['part_paid']) }} part paid</p>
        </div>

        <div class="erp-po-index-stat">
            <div class="erp-po-index-stat__head">
                <span class="erp-po-index-stat__label">Billed (MTD)</span>
                <span class="erp-po-index-stat__icon erp-po-index-stat__icon--success">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <p class="erp-po-index-stat__value">{{ $currencyCode }} {{ number_format($stats['period_billed'], 0) }}</p>
            <p class="erp-po-index-stat__hint">{{ number_format($stats['paid']) }} fully paid</p>
        </div>
    </div>

    <div class="erp-po-index-filters">
        @foreach($filterTabs as $tab)
            @php
                $tabQuery = $filterQuery;
                unset($tabQuery['status']);
                if ($tab['value']) {
                    $tabQuery['status'] = $tab['value'];
                }
            @endphp
            <a href="{{ route('admin.logistics-bills.index', $tabQuery) }}"
               @class([
                   'erp-po-index-filter',
                   'is-active' => $statusFilter === $tab['value'],
               ])>
                {{ $tab['label'] }}
            </a>
        @endforeach
    </div>

    <div class="erp-order-list-card erp-po-index-table-card">
        <div class="erp-po-index-table-card__head">
            <div>
                <h2 class="erp-po-index-table-card__title">Carrier bill register</h2>
                <p class="erp-po-index-table-card__desc">Track transport invoices from posting through payment.</p>
            </div>
            @if($bills->total() > 0)
                <span class="erp-po-index-table-card__badge">
                    {{ number_format($bills->total()) }} {{ Str::plural('bill', $bills->total()) }}
                </span>
            @endif
        </div>

        <form method="GET" class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
            @if($statusFilter)
                <input type="hidden" name="status" value="{{ $statusFilter }}">
            @endif
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5 lg:items-end">
                <div>
                    <label for="from" class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">From</label>
                    <input type="date" id="from" name="from" value="{{ $from?->format('Y-m-d') }}"
                           class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                </div>
                <div>
                    <label for="to" class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">To</label>
                    <input type="date" id="to" name="to" value="{{ $to?->format('Y-m-d') }}"
                           class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                </div>
                <div class="sm:col-span-2">
                    <label for="transport_carrier_id" class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">Carrier</label>
                    <select id="transport_carrier_id" name="transport_carrier_id"
                            class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <option value="">All carriers</option>
                        @foreach($carriers as $carrier)
                            <option value="{{ $carrier->id }}" @selected((string) $carrierId === (string) $carrier->id)>{{ $carrier->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="submit" class="erp-order-btn erp-order-btn--brand h-10 px-4">Apply</button>
                    @if(filled($from) || filled($to) || filled($carrierId))
                        <a href="{{ route('admin.logistics-bills.index', $statusFilter ? ['status' => $statusFilter] : []) }}" class="erp-order-btn erp-order-btn--secondary h-10 px-4">Clear</a>
                    @endif
                </div>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="erp-order-list-table erp-po-index-table">
                <thead>
                    <tr>
                        <th>Bill no.</th>
                        <th>Carrier</th>
                        <th>Bill date</th>
                        <th>Due</th>
                        <th>Service</th>
                        <th class="is-right">Gross</th>
                        <th>Status</th>
                        <th class="is-right">Outstanding</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bills as $bill)
                        @php
                            $tone = $statusTones[$bill->status] ?? 'neutral';
                            $carrierName = $bill->transportCarrier->name ?? '—';
                            $carrierInitial = strtoupper(substr(trim($carrierName), 0, 1)) ?: '—';
                            $gross = (float) $bill->gross_total;
                            $outstanding = (float) $bill->outstanding;
                            $paidPercent = $gross > 0 ? min(100, round(((float) $bill->paid_total / $gross) * 100)) : 0;
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('admin.logistics-bills.show', $bill) }}" class="erp-po-index-po-link">
                                    {{ $bill->document_number }}
                                </a>
                                <p class="erp-po-index-po-meta">{{ $bill->lines_count }} {{ Str::plural('line', $bill->lines_count) }}</p>
                            </td>
                            <td>
                                <div class="erp-po-index-supplier">
                                    <span class="erp-po-index-supplier__avatar">{{ $carrierInitial }}</span>
                                    <span class="erp-po-index-supplier__name">{{ $carrierName }}</span>
                                </div>
                            </td>
                            <td class="whitespace-nowrap">{{ $bill->bill_date->format('d M Y') }}</td>
                            <td class="whitespace-nowrap">
                                @if($bill->due_date)
                                    <span @class(['text-error-600 dark:text-error-400' => $outstanding > 0 && $bill->due_date->isPast()])>
                                        {{ $bill->due_date->format('d M Y') }}
                                    </span>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="whitespace-nowrap">{{ $bill->serviceTypeLabel() }}</td>
                            <td class="is-right whitespace-nowrap">
                                <span class="erp-table-num font-semibold text-gray-900 dark:text-white">
                                    {{ $currencyCode }} {{ number_format($gross, 0) }}
                                </span>
                            </td>
                            <td>
                                <span class="erp-po-status erp-po-status--{{ $tone }}">
                                    {{ $statusLabels[$bill->status] ?? ucfirst(str_replace('_', ' ', $bill->status)) }}
                                </span>
                                @if($bill->status === 'part_paid')
                                    <p class="mt-1 text-[11px] text-gray-500">{{ $paidPercent }}% paid</p>
                                @endif
                            </td>
                            <td class="is-right whitespace-nowrap">
                                @if($outstanding > 0)
                                    <span class="erp-table-num font-semibold text-amber-700 dark:text-amber-300">{{ $currencyCode }} {{ number_format($outstanding, 0) }}</span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <x-admin.action-group>
                                    <x-admin.action-view :href="route('admin.logistics-bills.show', $bill)" />
                                </x-admin.action-group>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="!px-6 !py-16 text-center">
                                <div class="erp-po-index-empty">
                                    <div class="erp-po-index-empty__icon" aria-hidden="true">
                                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </div>
                                    <p class="text-base font-semibold text-gray-900 dark:text-white">No logistics bills found</p>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        @if($statusFilter || filled($from) || filled($to) || filled($carrierId))
                                            No bills match these filters. Try another status or create a new bill.
                                        @else
                                            Record your first carrier invoice to track transport payables.
                                        @endif
                                    </p>
                                    <div class="mt-5 flex flex-wrap justify-center gap-2">
                                        <a href="{{ route('admin.logistics.carriers.index') }}" class="erp-order-btn erp-order-btn--secondary">Manage carriers</a>
                                        <a href="{{ route('admin.logistics-bills.create') }}" class="erp-order-btn erp-order-btn--brand">New logistics bill</a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($bills->hasPages())
            <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">
                {{ $bills->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
