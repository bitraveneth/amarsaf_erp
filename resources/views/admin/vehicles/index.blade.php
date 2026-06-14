@extends('layouts.app')

@section('content')
@php
    $totalVehicles = $stats['total'] ?? ($vehicles instanceof \Illuminate\Pagination\LengthAwarePaginator ? $vehicles->total() : $vehicles->count());
    $totalCapacity = $stats['capacity'] ?? $vehicles->sum('capacity_crates');
    $withDriver = $stats['with_driver'] ?? $vehicles->filter(fn ($v) => ! empty($v->driver))->count();
    $withPlate = $stats['with_plate'] ?? $vehicles->filter(fn ($v) => ! empty($v->license_plate))->count();
    $activeCount = $stats['active'] ?? $vehicles->filter(fn ($v) => $v->is_active)->count();

    $snapshotCards = [
        [
            'label' => 'Total vehicles',
            'numeric' => number_format($totalVehicles),
            'caption' => 'Registered fleet units',
            'href' => route('admin.vehicles.index') . '#fleet-directory',
            'tone' => 'brand',
            'valueTone' => 'neutral',
            'icon' => 'delivery',
        ],
        [
            'label' => 'Total capacity',
            'numeric' => number_format($totalCapacity),
            'caption' => 'Crates across fleet',
            'href' => route('admin.vehicles.index') . '#fleet-directory',
            'tone' => 'blue',
            'valueTone' => 'neutral',
            'icon' => 'production',
        ],
        [
            'label' => 'With driver',
            'numeric' => number_format($withDriver),
            'caption' => $totalVehicles > 0 ? round(($withDriver / $totalVehicles) * 100) . '% assigned' : 'No drivers set',
            'href' => route('admin.vehicles.index') . '#fleet-directory',
            'tone' => 'success',
            'valueTone' => 'neutral',
            'icon' => 'users',
        ],
        [
            'label' => 'Active fleet',
            'numeric' => number_format($activeCount),
            'caption' => number_format($withPlate) . ' with license plate',
            'href' => route('admin.vehicles.index') . '#fleet-directory',
            'tone' => 'orange',
            'valueTone' => ($totalVehicles - $activeCount) > 0 ? 'warning' : 'neutral',
            'icon' => 'alert',
        ],
    ];
@endphp

<div class="dash-page space-y-6">
    <x-admin.page-header
        title="Vehicle registry"
        subtitle="Maintain fleet trucks and vans for deliveries, load planning, and cost tracking."
    >
        <x-slot:actions>
            <a href="{{ route('admin.fleet-expenses.index') }}" class="erp-btn-secondary">Fleet expenses</a>
            <a href="{{ route('admin.vehicle-load.index') }}" class="erp-btn-secondary">Vehicle loads</a>
            <a href="{{ route('admin.vehicles.create') }}" class="erp-btn-primary">Add vehicle</a>
        </x-slot:actions>
    </x-admin.page-header>

    @if(session('status'))
        <div class="rounded-xl border border-success-200 bg-success-50 px-4 py-3 text-sm text-success-800 dark:border-success-500/30 dark:bg-success-500/10 dark:text-success-300">
            {{ session('status') }}
        </div>
    @endif

    @if($vehicles->isNotEmpty())
        <x-dashboard.snapshot-kpis
            :show-header="false"
            :cards="$snapshotCards"
        />

        <section id="fleet-directory" class="erp-table-card">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <div>
                    <h2 class="erp-table-card-title">Fleet directory</h2>
                    <p class="erp-table-card-description">Vehicles available for scheduling and logistics costs.</p>
                </div>
                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                    {{ number_format($totalVehicles) }}
                </span>
            </div>
            <div class="erp-table-wrap">
                <table class="erp-table w-full min-w-[56rem]">
                    <thead>
                        <tr>
                            <th>Vehicle</th>
                            <th>Type</th>
                            <th>Ownership</th>
                            <th>License plate</th>
                            <th>Driver</th>
                            <th class="is-right">Capacity</th>
                            <th>Status</th>
                            <th class="is-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($vehicles as $vehicle)
                            <tr>
                                <td>
                                    <div class="flex min-w-0 items-center gap-3">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-brand-100 text-sm font-semibold text-brand-700 dark:bg-brand-500/20 dark:text-brand-400">
                                            {{ strtoupper(substr($vehicle->name, 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <a href="{{ route('admin.vehicles.show', $vehicle) }}" class="erp-body-strong truncate text-brand-600 hover:text-brand-700 dark:text-brand-400">
                                                {{ $vehicle->name }}
                                            </a>
                                            @if($vehicle->model)
                                                <p class="erp-caption truncate">{{ $vehicle->model }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($vehicle->type)
                                        <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                            {{ ucfirst($vehicle->type) }}
                                        </span>
                                    @else
                                        <span class="text-sm text-gray-500 dark:text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="text-sm text-gray-700 dark:text-gray-300">
                                    {{ \App\Models\Vehicle::ownershipOptions()[$vehicle->ownership ?? 'own'] ?? 'Own fleet' }}
                                </td>
                                <td>
                                    @if($vehicle->license_plate)
                                        <span class="font-mono text-sm font-medium text-gray-900 dark:text-white">{{ $vehicle->license_plate }}</span>
                                    @else
                                        <span class="text-sm text-gray-500 dark:text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="text-sm text-gray-700 dark:text-gray-300">{{ $vehicle->driver ?: '—' }}</td>
                                <td class="is-right erp-table-num whitespace-nowrap">
                                    @if($vehicle->capacity_crates)
                                        {{ number_format($vehicle->capacity_crates) }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    @if($vehicle->is_active)
                                        <span class="inline-flex items-center rounded-full bg-success-50 px-2.5 py-0.5 text-xs font-medium text-success-700 dark:bg-success-500/20 dark:text-success-300">Active</span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">Inactive</span>
                                    @endif
                                </td>
                                <td class="is-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.fleet-expenses.create', ['vehicle_id' => $vehicle->id]) }}" class="erp-btn-secondary !px-2.5 !py-1.5 !text-xs">Add cost</a>
                                        <a href="{{ route('admin.vehicles.show', $vehicle) }}" class="erp-btn-action">View</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if(method_exists($vehicles, 'links') && $vehicles->hasPages())
                <div class="border-t border-gray-100 px-6 py-4 dark:border-gray-800">
                    {{ $vehicles->links() }}
                </div>
            @endif
        </section>
    @else
        <div class="erp-table-card">
            <x-admin.empty-state
                title="No vehicles added"
                description="Add your first vehicle to start managing deliveries, load planning, and fleet costs."
            >
                <x-slot:action>
                    <a href="{{ route('admin.vehicles.create') }}" class="erp-btn-primary">Add vehicle</a>
                </x-slot:action>
            </x-admin.empty-state>
        </div>
    @endif
</div>
@endsection
