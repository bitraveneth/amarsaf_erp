@extends('layouts.app')

@section('content')
@php
    $typeLabels = [
        'asset' => 'Assets',
        'liability' => 'Liabilities',
        'equity' => 'Equity',
        'income' => 'Income',
        'expense' => 'Expenses',
    ];

    $isBalanceView = $viewMode === 'balance';
    $structureQuery = array_filter(['view' => 'structure', 'type' => $selectedType, 'root' => $selectedRoot]);
    $balanceQuery = array_filter(array_merge(['view' => 'balance', 'type' => $selectedType, 'root' => $selectedRoot], [
        'range' => $range,
        'from' => $from->toDateString(),
        'to' => $to->toDateString(),
    ]));
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Chart of accounts</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                @if($isBalanceView)
                    Ledger balances for the selected period. Only <strong>ledger</strong> rows accept postings.
                @else
                    Account hierarchy by type, parent, and child. Only <strong>ledger</strong> rows accept postings.
                @endif
            </p>
        </div>
        <a href="{{ route('admin.accounts.create') }}"
           class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-600">
            + Add account
        </a>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.accounts.index', $structureQuery) }}"
               class="rounded-lg px-4 py-2 text-sm font-semibold {{ !$isBalanceView ? 'bg-brand-500 text-white' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">
                Structure
            </a>
            <a href="{{ route('admin.accounts.index', $balanceQuery) }}"
               class="rounded-lg px-4 py-2 text-sm font-semibold {{ $isBalanceView ? 'bg-brand-500 text-white' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">
                Balances
            </a>
        </div>
        @if($isBalanceView)
            @include('admin.finance.partials.accounts-page-export', [
                'module' => 'coa-balance',
                'label' => 'Export balances',
                'exportQuery' => $filterQuery,
            ])
        @else
            @include('admin.finance.partials.accounts-page-export', [
                'module' => 'coa-structure',
                'label' => 'Export structure',
                'exportQuery' => $filterQuery,
            ])
        @endif
    </div>

    @if($isBalanceView)
        <form method="GET" action="{{ route('admin.accounts.index') }}" class="rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <input type="hidden" name="view" value="balance">
            @if($selectedType)
                <input type="hidden" name="type" value="{{ $selectedType }}">
            @endif
            @if($selectedRoot)
                <input type="hidden" name="root" value="{{ $selectedRoot }}">
            @endif
            <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Reporting period</p>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $periodLabel }}</p>
                </div>
                <span class="rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold text-brand-700 dark:bg-brand-500/10 dark:text-brand-300">
                    {{ $rangeOptions[$range] ?? 'Custom range' }}
                </span>
            </div>
            <div class="grid gap-3 md:grid-cols-4">
                <div class="erp-field">
                    <label class="erp-label" for="coa-range">Range</label>
                    <select id="coa-range" name="range" class="erp-input">
                        @foreach($rangeOptions as $value => $label)
                            <option value="{{ $value }}" @selected($range === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="erp-field">
                    <label class="erp-label" for="coa-from">From</label>
                    <input id="coa-from" type="date" name="from" value="{{ $from->toDateString() }}" class="erp-input">
                </div>
                <div class="erp-field">
                    <label class="erp-label" for="coa-to">To</label>
                    <input id="coa-to" type="date" name="to" value="{{ $to->toDateString() }}" class="erp-input">
                </div>
                <div class="flex items-end gap-2">
                    <a href="{{ route('admin.accounts.index', array_filter(['view' => 'balance', 'type' => $selectedType, 'root' => $selectedRoot])) }}" class="erp-btn-secondary">Reset</a>
                    <button type="submit" class="erp-btn-primary">Apply</button>
                </div>
            </div>
        </form>
    @else
        <div class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <p class="text-xs font-semibold uppercase text-gray-500">Total accounts</p>
                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <p class="text-xs font-semibold uppercase text-gray-500">Groups</p>
                <p class="mt-2 text-3xl font-bold text-brand-600">{{ $stats['groups'] }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <p class="text-xs font-semibold uppercase text-gray-500">Ledgers</p>
                <p class="mt-2 text-3xl font-bold text-emerald-600">{{ $stats['ledgers'] }}</p>
            </div>
        </div>
    @endif

    @include('admin.finance.partials.accounts-type-cards')

    @if($isBalanceView)
        <div class="rounded-2xl border border-gray-200 bg-white px-5 py-4 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-semibold uppercase text-gray-500">Net assets</p>
            <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ $currencyCode }} {{ number_format($netAssets, 0) }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">Assets minus liabilities · closing as of {{ $to->format('d M Y') }}</p>
        </div>
    @endif

    <div
        class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900"
        x-data="{
            expanded: {},
            groupIds: @js($groupIds ?? []),
            toggle(id) { this.expanded[id] = !this.expanded[id]; },
            isExpanded(id) { return !!this.expanded[id]; },
            isVisible(ancestorIds) {
                return !ancestorIds?.length || ancestorIds.every(id => this.expanded[id]);
            },
            expandAll() { this.groupIds.forEach(id => { this.expanded[id] = true; }); },
            collapseAll() { this.expanded = {}; },
        }"
    >
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-100 px-4 py-3 dark:border-gray-800">
            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                @if($isBalanceView)
                    Account tree · {{ $periodLabel }}
                @else
                    Account tree · parent / child hierarchy
                @endif
                @if($selectedType)
                    · {{ $typeLabels[$selectedType] ?? ucfirst($selectedType) }}
                @endif
            </p>
            <div class="flex flex-wrap gap-2">
                <button type="button" class="erp-btn-action" @click="expandAll()">Expand all</button>
                <button type="button" class="erp-btn-action" @click="collapseAll()">Collapse all</button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-800/60">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Code</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Account</th>
                        @if(!$isBalanceView)
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Parent</th>
                        @endif
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Type</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Kind</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Slug</th>
                        @if($isBalanceView)
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-gray-500">Balance</th>
                        @endif
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse($roots as $root)
                        @include('admin.finance.partials.accounts-tree', [
                            'nodes' => collect([$root]),
                            'showBalance' => $isBalanceView,
                            'showParent' => !$isBalanceView,
                        ])
                    @empty
                        <tr>
                            <td colspan="{{ $isBalanceView ? 7 : 7 }}" class="px-6 py-12 text-center text-sm text-gray-500">
                                No accounts match this filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
