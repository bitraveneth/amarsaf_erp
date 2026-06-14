@extends('layouts.app')

@section('content')
@php
    $snapshotCards = [
        [
            'label' => 'Own fleet (MTD)',
            'numeric' => number_format($fleetTotal, 0),
            'currency' => 'BDT',
            'caption' => now()->format('F Y') . ' fuel, maintenance, rent',
            'href' => route('admin.fleet-expenses.index'),
            'tone' => 'brand',
            'valueTone' => 'neutral',
            'icon' => 'delivery',
        ],
        [
            'label' => 'Carrier billed (MTD)',
            'numeric' => number_format($carrierBilled, 0),
            'currency' => 'BDT',
            'caption' => 'Hired transport invoices',
            'href' => route('admin.logistics-bills.index'),
            'tone' => 'blue',
            'valueTone' => 'neutral',
            'icon' => 'orders',
        ],
        [
            'label' => 'Unpaid carrier bills',
            'numeric' => number_format($unpaidCarrier, 0),
            'currency' => 'BDT',
            'caption' => 'Open logistics payables',
            'href' => route('admin.logistics-bills.index'),
            'tone' => 'orange',
            'valueTone' => $unpaidCarrier > 0 ? 'warning' : 'neutral',
            'icon' => 'receivables',
        ],
        [
            'label' => 'Fleet / carriers',
            'numeric' => $activeVehicles . ' / ' . $carriers,
            'caption' => 'Active vehicles · transport carriers',
            'href' => route('admin.logistics.carriers.index'),
            'tone' => 'success',
            'valueTone' => 'neutral',
            'icon' => 'production',
        ],
    ];
@endphp

<div class="dash-page">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <x-dashboard.page-header
            title="Logistics dashboard"
            subtitle="Fleet costs, carrier bills, and unpaid transport payables this month."
        />
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.fleet-expenses.create') }}" class="erp-btn-secondary">Add fleet expense</a>
            <a href="{{ route('admin.logistics-bills.create') }}" class="erp-btn-primary">New logistics bill</a>
        </div>
    </div>

    <x-dashboard.snapshot-kpis
        class="mt-2"
        eyebrow="Logistics snapshot"
        title="Month at a glance"
        description="Own-truck spend, carrier billing, payables, and fleet capacity."
        :cards="$snapshotCards"
    />

    <div class="mt-2 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <x-dashboard.section-header
                title="Fleet cost mix (MTD)"
                description="Breakdown of own-truck expenses by type."
                class="mb-4"
            />
            <dl class="space-y-2">
                @foreach(\App\Models\FleetExpense::types() as $type => $label)
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500 dark:text-gray-400">{{ $label }}</dt>
                        <dd class="font-medium text-gray-900 dark:text-white">BDT {{ number_format($fleetByType[$type] ?? 0, 0) }}</dd>
                    </div>
                @endforeach
            </dl>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <x-dashboard.section-header
                title="Top vehicles by cost"
                description="Highest fleet spend this month."
                class="mb-4"
            />
            <ul class="space-y-2">
                @forelse($topVehicles as $row)
                    <li class="flex justify-between text-sm">
                        <a href="{{ route('admin.vehicles.show', $row->vehicle) }}" class="font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400">{{ $row->vehicle->name }}</a>
                        <span class="font-medium text-gray-900 dark:text-white">BDT {{ number_format($row->total, 0) }}</span>
                    </li>
                @empty
                    <li class="text-sm text-gray-500 dark:text-gray-400">No fleet expenses yet.</li>
                @endforelse
            </ul>
        </section>
    </div>

    <div class="mt-2 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <section>
            <x-dashboard.section-header
                title="Recent fleet expenses"
                description="Latest fuel, maintenance, and other own-truck costs."
                class="mb-4"
            >
                <x-slot:actions>
                    <a href="{{ route('admin.fleet-expenses.index') }}" class="erp-btn-secondary !px-3 !py-1.5 !text-xs">View all</a>
                </x-slot:actions>
            </x-dashboard.section-header>
            <div class="erp-table-card">
                <div class="erp-table-wrap">
                    <table class="erp-table w-full">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Vehicle</th>
                                <th class="is-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentFleet as $expense)
                                <tr>
                                    <td>{{ $expense->expense_date->format('d M') }}</td>
                                    <td>{{ $expense->vehicle->name }}</td>
                                    <td class="is-right erp-table-num">BDT {{ number_format($expense->amount, 0) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="py-6 text-center text-sm text-gray-500">No fleet expenses yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section>
            <x-dashboard.section-header
                title="Recent logistics bills"
                description="Latest carrier and hired transport invoices."
                class="mb-4"
            >
                <x-slot:actions>
                    <a href="{{ route('admin.logistics-bills.index') }}" class="erp-btn-secondary !px-3 !py-1.5 !text-xs">View all</a>
                </x-slot:actions>
            </x-dashboard.section-header>
            <div class="erp-table-card">
                <div class="erp-table-wrap">
                    <table class="erp-table w-full">
                        <thead>
                            <tr>
                                <th>Bill</th>
                                <th>Carrier</th>
                                <th class="is-right">Gross</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentBills as $bill)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.logistics-bills.show', $bill) }}" class="text-brand-600 hover:text-brand-700 dark:text-brand-400">{{ $bill->document_number }}</a>
                                    </td>
                                    <td>{{ $bill->transportCarrier->name }}</td>
                                    <td class="is-right erp-table-num">BDT {{ number_format($bill->gross_total, 0) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="py-6 text-center text-sm text-gray-500">No logistics bills yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection
