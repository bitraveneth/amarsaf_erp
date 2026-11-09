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

<div class="erp-page">
    <x-admin.page-header
        title="Chart of accounts"
        :subtitle="$isBalanceView
            ? 'Closing and period balances. Only ledger rows accept postings.'
            : 'Account hierarchy. Switch to Balances to see amounts.'"
    >
        <x-slot:actions>
            <a href="{{ route('admin.accounts.index', $structureQuery) }}" class="{{ $isBalanceView ? 'erp-btn-secondary' : 'erp-btn-primary' }}">Structure</a>
            <a href="{{ route('admin.accounts.index', $balanceQuery) }}" class="{{ $isBalanceView ? 'erp-btn-primary' : 'erp-btn-secondary' }}">Balances</a>
            @include('admin.finance.partials.accounts-page-export', [
                'module' => $isBalanceView ? 'coa-balance' : 'coa-structure',
                'label' => '',
                'exportQuery' => $filterQuery,
            ])
            <a href="{{ route('admin.accounts.create') }}" class="erp-btn-primary">Add account</a>
            @if($isBalanceView)
                <x-dashboard.period-filter
                    variant="compact"
                    :action="route('admin.accounts.index')"
                    :range="$range"
                    :from="$from->toDateString()"
                    :to="$to->toDateString()"
                    :range-options="$rangeOptions"
                    :period-label="$periodLabel"
                />
            @endif
        </x-slot:actions>
    </x-admin.page-header>

    @if(!$isBalanceView)
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <x-admin.stat-card label="Accounts" :value="number_format($stats['total'])" />
            <x-admin.stat-card label="Groups" :value="number_format($stats['groups'])" />
            <x-admin.stat-card label="Ledgers" :value="number_format($stats['ledgers'])" tone="success" />
        </div>
    @endif

    @include('admin.finance.partials.accounts-type-cards')

    <div class="erp-table-card"
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
        <div class="erp-table-card-header flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 class="erp-h3">Account tree</h2>
                <p class="erp-caption">
                    @if($isBalanceView)
                        {{ $periodLabel }}
                        · Net assets {{ $currencyCode }} {{ number_format($netAssets, 0) }}
                    @else
                        Parent and child hierarchy
                    @endif
                    @if($selectedType)
                        · {{ $typeLabels[$selectedType] ?? ucfirst($selectedType) }}
                    @endif
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" class="erp-btn-secondary !px-3 !py-1.5 !text-sm" @click="expandAll()">Expand all</button>
                <button type="button" class="erp-btn-secondary !px-3 !py-1.5 !text-sm" @click="collapseAll()">Collapse all</button>
            </div>
        </div>
        <div class="erp-table-wrap">
            <table class="erp-table w-full" style="table-layout: fixed; width: 100%;">
                <colgroup>
                    <col style="width: 6rem">
                    <col>
                    @if(!$isBalanceView)
                        <col style="width: 11rem">
                    @endif
                    <col style="width: 7rem">
                    <col style="width: 6rem">
                    @if(!$isBalanceView)
                        <col style="width: 9rem">
                    @endif
                    @if($isBalanceView)
                        <col style="width: 16rem">
                    @endif
                    <col style="width: 23rem">
                </colgroup>
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Account</th>
                        @if(!$isBalanceView)
                            <th>Parent</th>
                        @endif
                        <th>Type</th>
                        <th>Kind</th>
                        @if(!$isBalanceView)
                            <th>Slug</th>
                        @endif
                        @if($isBalanceView)
                            <th class="is-right">Balance</th>
                        @endif
                        <th class="is-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse($roots as $root)
                        @include('admin.finance.partials.accounts-tree', [
                            'nodes' => collect([$root]),
                            'showBalance' => $isBalanceView,
                            'showParent' => !$isBalanceView,
                            'showSlug' => !$isBalanceView,
                        ])
                    @empty
                        <tr>
                            <td colspan="{{ $isBalanceView ? 6 : 7 }}" class="px-6 py-12 text-center text-sm text-gray-500">
                                No accounts match this filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <x-admin.quick-guide
        title="How the chart of accounts works"
        lead="Groups organise the tree. Only ledger (non-group) accounts can be posted to by journals and documents."
        :steps="[
            ['title' => 'Structure vs Balances', 'body' => 'Structure shows hierarchy. Balances shows amounts for the selected period.'],
            ['title' => 'Filter by type', 'body' => 'Assets, liabilities, equity, income, expense — or a report root.'],
            ['title' => 'Add account', 'body' => 'Create a group or a postable ledger under the right parent.'],
            ['title' => 'Expand the tree', 'body' => 'Open groups to reach ledgers like Bank – BRAC or Trade debtors.'],
        ]"
        :examples="[
            [
                'eyebrow' => 'Remember',
                'title' => 'Where amounts come from',
                'bullets' => [
                    'Balances are from posted journal lines',
                    'Expenses, invoices, bills, and payroll post into these ledgers',
                    'Manual journals are for openings and rare adjustments',
                ],
            ],
        ]"
        note="Do not post to group accounts. If AccountResolver cannot find a slug, check the ledger slug on the account."
        :open="false"
    />
</div>
@endsection
