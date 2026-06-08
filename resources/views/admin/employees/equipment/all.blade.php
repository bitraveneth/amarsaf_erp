@extends('layouts.app')

@section('content')
@php
    $totalItems = $equipment instanceof \Illuminate\Pagination\LengthAwarePaginator ? $equipment->total() : $equipment->count();
    $activeItems = $equipment->where('status', 'active')->count();
    $uniqueEmployees = $equipment->pluck('employee.id')->filter()->unique()->count();
    $withIdentifier = $equipment->filter(fn ($item) => !empty($item->device_identifier))->count();

    $statusColors = [
        'active' => 'success',
        'returned' => 'warning',
        'lost' => 'neutral',
    ];
@endphp

<div class="erp-order-page erp-order-page--index screen-employee-equipment">
    <x-admin.order-toolbar
        title="Employee equipment"
        subtitle="Company devices and assets assigned to field and office staff."
        :back-url="route('admin.employees.index')"
        back-label="Employees"
    >
        <x-slot:actions>
            <a href="{{ route('admin.equipment.create') }}" class="erp-order-btn erp-order-btn--primary">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Assign equipment
            </a>
        </x-slot:actions>
    </x-admin.order-toolbar>

    @if(session('status'))
        <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200">
            {{ session('status') }}
        </div>
    @endif

    @if($equipment->isNotEmpty())
        <div class="erp-po-index-stats">
            <div class="erp-po-index-stat">
                <div class="erp-po-index-stat__head">
                    <span class="erp-po-index-stat__label">Total items</span>
                    <span class="erp-po-index-stat__icon erp-po-index-stat__icon--brand">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17.25v1.5a1.5 1.5 0 001.5 1.5h3a1.5 1.5 0 001.5-1.5v-1.5M9 11.25v4.5M15 11.25v4.5"/></svg>
                    </span>
                </div>
                <p class="erp-po-index-stat__value">{{ number_format($totalItems) }}</p>
                <p class="erp-po-index-stat__hint">Assignments on record</p>
            </div>

            <div class="erp-po-index-stat">
                <div class="erp-po-index-stat__head">
                    <span class="erp-po-index-stat__label">Active</span>
                    <span class="erp-po-index-stat__icon erp-po-index-stat__icon--success">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                </div>
                <p class="erp-po-index-stat__value">{{ $activeItems }}</p>
                <p class="erp-po-index-stat__hint">Currently assigned</p>
            </div>

            <div class="erp-po-index-stat">
                <div class="erp-po-index-stat__head">
                    <span class="erp-po-index-stat__label">Employees</span>
                    <span class="erp-po-index-stat__icon erp-po-index-stat__icon--brand">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </span>
                </div>
                <p class="erp-po-index-stat__value">{{ number_format($uniqueEmployees) }}</p>
                <p class="erp-po-index-stat__hint">With equipment</p>
            </div>

            <div class="erp-po-index-stat">
                <div class="erp-po-index-stat__head">
                    <span class="erp-po-index-stat__label">Tracked ID</span>
                    <span class="erp-po-index-stat__icon erp-po-index-stat__icon--warning">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 10-3 0M3.75 18H7.5"/></svg>
                    </span>
                </div>
                <p class="erp-po-index-stat__value">{{ $withIdentifier }}</p>
                <p class="erp-po-index-stat__hint">With IMEI / serial</p>
            </div>
        </div>
    @endif

    <div class="erp-order-list-card erp-po-index-table-card">
        <div class="erp-po-index-table-card__head">
            <div>
                <h2 class="erp-po-index-table-card__title">Equipment register</h2>
                <p class="erp-po-index-table-card__desc">Phones, tablets, laptops, and other assets issued to employees.</p>
            </div>
            @if($totalItems > 0)
                <span class="erp-po-index-table-card__badge">{{ $equipment->count() }} of {{ number_format($totalItems) }}</span>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="erp-order-list-table erp-so-index-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Employee</th>
                        <th>Equipment</th>
                        <th>ID / IMEI</th>
                        <th>Status</th>
                        <th>Notes</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($equipment as $item)
                        @php
                            $employee = $item->employee;
                            $employeeName = $employee->name ?? '—';
                            $employeeInitial = strtoupper(substr(trim($employeeName), 0, 1)) ?: '?';
                            $tone = $statusColors[$item->status] ?? 'neutral';
                        @endphp
                        <tr>
                            <td class="whitespace-nowrap">
                                <span class="font-medium text-gray-900 dark:text-white">{{ $item->effective_date?->format('d M Y') }}</span>
                            </td>
                            <td>
                                <div class="erp-po-index-supplier">
                                    <span class="erp-po-index-supplier__avatar">{{ $employeeInitial }}</span>
                                    <div>
                                        @if($employee)
                                            <a href="{{ route('admin.equipment.show', $item) }}" class="erp-po-index-supplier__name hover:text-brand-600 dark:hover:text-brand-400">{{ $employeeName }}</a>
                                            <p class="erp-po-index-po-meta">{{ $employee->department ?? '—' }}</p>
                                        @else
                                            <span class="erp-po-index-supplier__name">—</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="font-medium text-gray-900 dark:text-white">{{ $item->product_name }}</span>
                            </td>
                            <td>
                                @if($item->device_identifier)
                                    <span class="font-mono text-sm text-gray-900 dark:text-white">{{ $item->device_identifier }}</span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="erp-po-status erp-po-status--{{ $tone }}">{{ ucfirst($item->status) }}</span>
                            </td>
                            <td>
                                @if($item->notes)
                                    <span class="text-sm text-gray-600 dark:text-gray-400" title="{{ $item->notes }}">{{ \Illuminate\Support\Str::limit($item->notes, 40) }}</span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="text-right">
                                @if($employee)
                                    <x-admin.action-group>
                                        <x-admin.action-view :href="route('admin.equipment.show', $item)" />
                                        <x-admin.action-edit :href="route('admin.employees.equipment.edit', [$employee, $item])" />
                                    </x-admin.action-group>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="!px-6 !py-16 text-center">
                                <div class="erp-po-index-empty">
                                    <div class="erp-po-index-empty__icon" aria-hidden="true">
                                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 17.25v1.5a1.5 1.5 0 001.5 1.5h3a1.5 1.5 0 001.5-1.5v-1.5M9 11.25v4.5M15 11.25v4.5"/></svg>
                                    </div>
                                    <p class="text-base font-semibold text-gray-900 dark:text-white">No equipment assigned</p>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Assign the first device to start tracking company assets.</p>
                                    <a href="{{ route('admin.equipment.create') }}" class="erp-order-btn erp-order-btn--brand mt-5">Assign equipment</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($equipment, 'hasPages') && $equipment->hasPages())
            <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">
                {{ $equipment->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
