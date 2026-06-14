@extends('layouts.app')

@section('content')
@php
    $fuelTotal = (float) ($totalsByType[\App\Models\FleetExpense::TYPE_FUEL] ?? 0);
    $maintenanceTotal = (float) ($totalsByType[\App\Models\FleetExpense::TYPE_MAINTENANCE] ?? 0);
    $rentTotal = (float) ($totalsByType[\App\Models\FleetExpense::TYPE_RENT] ?? 0);
    $otherTotal = (float) ($totalsByType[\App\Models\FleetExpense::TYPE_TOLL] ?? 0)
        + (float) ($totalsByType[\App\Models\FleetExpense::TYPE_OTHER] ?? 0);
    $avgPerEntry = $entryCount > 0 ? $total / $entryCount : 0;

    $periodLabel = $from->format('d M Y') . ' – ' . $to->format('d M Y');

    $snapshotCards = [
        [
            'label' => 'Total spend',
            'numeric' => number_format($total, 0),
            'currency' => 'BDT',
            'caption' => $periodLabel,
            'href' => route('admin.fleet-expenses.index', request()->only(['from', 'to', 'vehicle_id', 'expense_type'])),
            'tone' => 'brand',
            'valueTone' => 'neutral',
            'icon' => 'revenue',
        ],
        [
            'label' => 'Fuel',
            'numeric' => number_format($fuelTotal, 0),
            'currency' => 'BDT',
            'caption' => $total > 0 ? round(($fuelTotal / $total) * 100) . '% of period spend' : 'No fuel logged',
            'href' => route('admin.fleet-expenses.index', array_merge(request()->only(['from', 'to', 'vehicle_id']), ['expense_type' => 'fuel'])),
            'tone' => 'orange',
            'valueTone' => 'neutral',
            'icon' => 'delivery',
        ],
        [
            'label' => 'Maintenance',
            'numeric' => number_format($maintenanceTotal, 0),
            'currency' => 'BDT',
            'caption' => 'Repairs & servicing',
            'href' => route('admin.fleet-expenses.index', array_merge(request()->only(['from', 'to', 'vehicle_id']), ['expense_type' => 'maintenance'])),
            'tone' => 'blue',
            'valueTone' => 'neutral',
            'icon' => 'production',
        ],
        [
            'label' => 'Entries',
            'numeric' => number_format($entryCount),
            'caption' => $vehicleCount . ' vehicles · avg BDT ' . number_format($avgPerEntry, 0),
            'href' => route('admin.fleet-expenses.index', request()->only(['from', 'to', 'vehicle_id', 'expense_type'])),
            'tone' => 'success',
            'valueTone' => 'neutral',
            'icon' => 'orders',
        ],
    ];

    $typeBadgeClasses = [
        'fuel' => 'bg-orange-50 text-orange-700 dark:bg-orange-500/20 dark:text-orange-300',
        'maintenance' => 'bg-blue-50 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300',
        'rent' => 'bg-purple-50 text-purple-700 dark:bg-purple-500/20 dark:text-purple-300',
        'toll' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
        'other' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
    ];
@endphp

<div class="dash-page">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <x-dashboard.page-header
            title="Fleet expenses"
            subtitle="Fuel, maintenance, rent, toll, and other own-truck costs."
        />
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.logistics.dashboard') }}" class="erp-btn-secondary">Logistics dashboard</a>
            <a href="{{ route('admin.fleet-expenses.create') }}" class="erp-btn-primary">Add expense</a>
        </div>
    </div>

    @if(session('status'))
        <div class="mt-4 rounded-xl border border-success-200 bg-success-50 px-4 py-3 text-sm text-success-800 dark:border-success-500/30 dark:bg-success-500/10 dark:text-success-300">
            {{ session('status') }}
        </div>
    @endif

    <x-dashboard.snapshot-kpis
        class="mt-2"
        eyebrow="Fleet cost snapshot"
        title="Spending in selected period"
        :description="$selectedVehicle ? 'Filtered to ' . $selectedVehicle->name : 'All vehicles in your fleet.'"
        :cards="$snapshotCards"
    />

    <section class="mt-2 rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <x-dashboard.section-header
            title="Filters"
            description="Narrow by date range, vehicle, or expense type."
            class="mb-4"
        />
        <form method="GET" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5 lg:items-end">
            <div>
                <label for="from" class="mb-1.5 block text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">From</label>
                <input type="date" id="from" name="from" value="{{ $from->format('Y-m-d') }}"
                       class="h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-900 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            </div>
            <div>
                <label for="to" class="mb-1.5 block text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">To</label>
                <input type="date" id="to" name="to" value="{{ $to->format('Y-m-d') }}"
                       class="h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-900 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            </div>
            <div>
                <label for="vehicle_id" class="mb-1.5 block text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Vehicle</label>
                <select id="vehicle_id" name="vehicle_id"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-900 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    <option value="">All vehicles</option>
                    @foreach($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}" @selected(request('vehicle_id') == $vehicle->id)>{{ $vehicle->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="expense_type" class="mb-1.5 block text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Type</label>
                <select id="expense_type" name="expense_type"
                        class="h-11 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-900 shadow-theme-xs focus:border-brand-300 focus:outline-none focus:ring-4 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    <option value="">All types</option>
                    @foreach(\App\Models\FleetExpense::types() as $value => $label)
                        <option value="{{ $value }}" @selected(request('expense_type') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="submit" class="erp-btn-primary h-11 px-5">Apply filters</button>
                @if(request()->hasAny(['from', 'to', 'vehicle_id', 'expense_type']))
                    <a href="{{ route('admin.fleet-expenses.index') }}" class="erp-btn-secondary h-11 px-5">Clear</a>
                @endif
            </div>
        </form>
    </section>

    <div class="mt-2 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 lg:col-span-1">
            <x-dashboard.section-header
                title="Cost mix"
                description="Share of spend by category."
                class="mb-4"
            />
            <dl class="space-y-3">
                @foreach(\App\Models\FleetExpense::types() as $type => $label)
                    @php $typeTotal = (float) ($totalsByType[$type] ?? 0); @endphp
                    <div>
                        <div class="mb-1 flex items-center justify-between text-sm">
                            <dt class="text-gray-600 dark:text-gray-400">{{ $label }}</dt>
                            <dd class="font-medium text-gray-900 dark:text-white">BDT {{ number_format($typeTotal, 0) }}</dd>
                        </div>
                        <div class="h-1.5 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                            <div class="h-full rounded-full bg-brand-500 transition-all"
                                 style="width: {{ $total > 0 ? min(100, ($typeTotal / $total) * 100) : 0 }}%"></div>
                        </div>
                    </div>
                @endforeach
            </dl>
            @if($rentTotal + $otherTotal > 0)
                <p class="mt-4 text-xs text-gray-500 dark:text-gray-400">
                    Rent BDT {{ number_format($rentTotal, 0) }} · Other/toll BDT {{ number_format($otherTotal, 0) }}
                </p>
            @endif
        </section>

        <section class="lg:col-span-2">
            <x-dashboard.section-header
                title="Expense ledger"
                description="{{ number_format($entryCount) }} {{ Str::plural('entry', $entryCount) }} · BDT {{ number_format($total, 2) }} total"
                class="mb-4"
            >
                <x-slot:actions>
                    <a href="{{ route('admin.fleet-recurring.index') }}" class="erp-btn-secondary !px-3 !py-1.5 !text-xs">Recurring charges</a>
                </x-slot:actions>
            </x-dashboard.section-header>

            @if($expenses->isEmpty())
                <div class="erp-table-card">
                    <x-admin.empty-state
                        title="No fleet expenses in this period"
                        description="Record fuel, maintenance, or other own-truck costs to track fleet spend."
                    >
                        <x-slot:action>
                            <a href="{{ route('admin.fleet-expenses.create') }}" class="erp-btn-primary">Add expense</a>
                        </x-slot:action>
                    </x-admin.empty-state>
                </div>
            @else
                <div class="erp-table-card">
                    <div class="erp-table-wrap">
                        <table class="erp-table w-full min-w-[48rem]">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Vehicle</th>
                                    <th>Type</th>
                                    <th>Details</th>
                                    <th>Payment</th>
                                    <th class="is-right">Amount</th>
                                    <th class="is-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($expenses as $expense)
                                    @php
                                        $badgeClass = $typeBadgeClasses[$expense->expense_type] ?? $typeBadgeClasses['other'];
                                    @endphp
                                    <tr>
                                        <td class="whitespace-nowrap">
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $expense->expense_date->format('d M Y') }}</span>
                                            @if($expense->trip_date && ! $expense->trip_date->equalTo($expense->expense_date))
                                                <p class="text-xs text-gray-500 dark:text-gray-400">Trip {{ $expense->trip_date->format('d M') }}</p>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.vehicles.show', $expense->vehicle) }}"
                                               class="font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400">
                                                {{ $expense->vehicle->name }}
                                            </a>
                                            @if($expense->deliveryRoute)
                                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $expense->deliveryRoute->name }}</p>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badgeClass }}">
                                                {{ $expense->typeLabel() }}
                                            </span>
                                        </td>
                                        <td class="max-w-xs">
                                            <p class="truncate text-sm text-gray-900 dark:text-white" title="{{ $expense->description }}">
                                                {{ $expense->description ?: '—' }}
                                            </p>
                                            @if($expense->reference)
                                                <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $expense->reference }}</p>
                                            @endif
                                            @if($expense->expense_type === 'fuel' && $expense->fuel_litres)
                                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ number_format($expense->fuel_litres, 1) }} L @ BDT {{ number_format($expense->fuel_rate ?? 0, 2) }}</p>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="text-sm capitalize text-gray-700 dark:text-gray-300">{{ $expense->payment_type }}</span>
                                        </td>
                                        <td class="is-right erp-table-num whitespace-nowrap font-semibold">
                                            BDT {{ number_format($expense->amount, 2) }}
                                        </td>
                                        <td class="is-right whitespace-nowrap">
                                            <a href="{{ route('admin.fleet-expenses.edit', $expense) }}" class="erp-btn-action">Edit</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($expenses->hasPages())
                        <div class="border-t border-gray-100 px-6 py-4 dark:border-gray-800">
                            {{ $expenses->links() }}
                        </div>
                    @endif
                </div>
            @endif
        </section>
    </div>
</div>
@endsection
