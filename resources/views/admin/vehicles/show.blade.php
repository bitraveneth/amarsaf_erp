@extends('layouts.app')

@section('content')
@php
    $monthTotal = $monthTotals->sum();
    $expenseCount = $vehicle->fleet_expenses_count ?? $recentExpenses->count();

    $snapshotCards = [
        [
            'label' => 'MTD fleet cost',
            'numeric' => number_format($monthTotal, 0),
            'currency' => 'BDT',
            'caption' => now()->format('F Y') . ' expenses',
            'href' => route('admin.fleet-expenses.index', ['vehicle_id' => $vehicle->id]),
            'tone' => 'brand',
            'valueTone' => 'neutral',
            'icon' => 'revenue',
        ],
        [
            'label' => 'Expense entries',
            'numeric' => number_format($expenseCount),
            'caption' => 'All-time fleet cost rows',
            'href' => route('admin.fleet-expenses.index', ['vehicle_id' => $vehicle->id]),
            'tone' => 'blue',
            'valueTone' => 'neutral',
            'icon' => 'orders',
        ],
        [
            'label' => 'Capacity',
            'numeric' => number_format($vehicle->capacity_crates ?? 0),
            'caption' => 'Crates load limit',
            'href' => route('admin.vehicle-load.index'),
            'tone' => 'success',
            'valueTone' => 'neutral',
            'icon' => 'production',
        ],
        [
            'label' => 'Status',
            'numeric' => $vehicle->is_active ? 'Active' : 'Inactive',
            'caption' => ($vehicle->ownership ? (\App\Models\Vehicle::ownershipOptions()[$vehicle->ownership] ?? $vehicle->ownership) : 'Own fleet'),
            'href' => route('admin.vehicles.index'),
            'tone' => $vehicle->is_active ? 'success' : 'orange',
            'valueTone' => $vehicle->is_active ? 'neutral' : 'warning',
            'icon' => 'delivery',
        ],
    ];
@endphp

<div class="dash-page space-y-6">
    <x-admin.page-header :title="$vehicle->name" subtitle="Vehicle profile, capacity, and fleet cost history.">
        <x-slot:actions>
            <a href="{{ route('admin.vehicles.index') }}" class="erp-btn-secondary">Back to registry</a>
            <a href="{{ route('admin.fleet-expenses.create', ['vehicle_id' => $vehicle->id]) }}" class="erp-btn-primary">Add fleet cost</a>
        </x-slot:actions>
    </x-admin.page-header>

    <x-dashboard.snapshot-kpis :show-header="false" :cards="$snapshotCards" />

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900 lg:col-span-1">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">Vehicle details</h2>
            <dl class="mt-4 space-y-4">
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Type</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $vehicle->type ? ucfirst($vehicle->type) : '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">License plate</dt>
                    <dd class="mt-1 font-mono text-sm font-medium text-gray-900 dark:text-white">{{ $vehicle->license_plate ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Driver</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $vehicle->driver ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Fuel type</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $vehicle->fuel_type ? (\App\Models\Vehicle::fuelTypeOptions()[$vehicle->fuel_type] ?? $vehicle->fuel_type) : '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Odometer</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $vehicle->odometer_km ? number_format($vehicle->odometer_km) . ' km' : '—' }}</dd>
                </div>
            </dl>
        </div>

        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Cost mix (MTD)</h2>
                <dl class="mt-4 space-y-2">
                    @forelse(\App\Models\FleetExpense::types() as $type => $label)
                        @if(($monthTotals[$type] ?? 0) > 0)
                            <div class="flex justify-between text-sm">
                                <dt class="text-gray-500">{{ $label }}</dt>
                                <dd class="font-medium text-gray-900 dark:text-white">BDT {{ number_format($monthTotals[$type] ?? 0, 0) }}</dd>
                            </div>
                        @endif
                    @empty
                    @endforelse
                    @if($monthTotal <= 0)
                        <p class="text-sm text-gray-500 dark:text-gray-400">No fleet expenses recorded this month.</p>
                    @endif
                </dl>
            </div>

            <div class="erp-table-card">
                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                    <h2 class="erp-table-card-title">Recent fleet expenses</h2>
                    <a href="{{ route('admin.fleet-expenses.index', ['vehicle_id' => $vehicle->id]) }}" class="text-xs font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400">View all</a>
                </div>
                <div class="erp-table-wrap">
                    <table class="erp-table w-full">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th class="is-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentExpenses as $expense)
                                <tr>
                                    <td>{{ $expense->expense_date->format('d M Y') }}</td>
                                    <td>{{ $expense->typeLabel() }}</td>
                                    <td>{{ $expense->description ?: ($expense->reference ?: '—') }}</td>
                                    <td class="is-right erp-table-num">BDT {{ number_format($expense->amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-8 text-center text-sm text-gray-500">No expenses recorded for this vehicle yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
