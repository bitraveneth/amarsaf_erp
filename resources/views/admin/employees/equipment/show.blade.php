@extends('layouts.app')

@section('content')
@php
    $employee = $equipment->employee;
    $statusTones = ['active' => 'success', 'returned' => 'warning', 'lost' => 'neutral'];
    $tone = $statusTones[$equipment->status] ?? 'neutral';
@endphp

<div class="erp-order-page erp-order-page--index erp-po-show">
    <x-admin.order-toolbar
        :title="$equipment->product_name"
        :subtitle="'Equipment assignment · ' . ($employee->name ?? '—')"
        :back-url="route('admin.equipment.index')"
        back-label="All equipment"
    >
        <x-slot:actions>
            <span class="erp-po-status erp-po-status--{{ $tone }}">{{ ucfirst($equipment->status) }}</span>
            @if($employee)
                <x-admin.action-group>
                    <x-admin.action-edit :href="route('admin.employees.equipment.edit', [$employee, $equipment])" />
                </x-admin.action-group>
                <a href="{{ route('admin.employees.show', $employee) }}" class="erp-order-btn erp-order-btn--secondary">Employee profile</a>
            @endif
        </x-slot:actions>
    </x-admin.order-toolbar>

    <div class="erp-po-index-stats">
        <div class="erp-po-index-stat">
            <div class="erp-po-index-stat__head">
                <span class="erp-po-index-stat__label">Product</span>
                <span class="erp-po-index-stat__icon erp-po-index-stat__icon--brand">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17.25v1.5a1.5 1.5 0 001.5 1.5h3a1.5 1.5 0 001.5-1.5v-1.5M9 11.25v4.5M15 11.25v4.5"/></svg>
                </span>
            </div>
            <p class="erp-po-index-stat__value !text-xl">{{ $equipment->product_name }}</p>
            <p class="erp-po-index-stat__hint">Assigned asset</p>
        </div>

        <div class="erp-po-index-stat">
            <div class="erp-po-index-stat__head">
                <span class="erp-po-index-stat__label">Effective date</span>
                <span class="erp-po-index-stat__icon erp-po-index-stat__icon--brand">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </span>
            </div>
            <p class="erp-po-index-stat__value !text-xl">{{ $equipment->effective_date?->format('d M Y') ?? '—' }}</p>
            <p class="erp-po-index-stat__hint">Assignment start</p>
        </div>

        <div class="erp-po-index-stat">
            <div class="erp-po-index-stat__head">
                <span class="erp-po-index-stat__label">Device ID</span>
                <span class="erp-po-index-stat__icon erp-po-index-stat__icon--warning">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 10-3 0M3.75 18H7.5"/></svg>
                </span>
            </div>
            <p class="erp-po-index-stat__value !text-lg font-mono">{{ $equipment->device_identifier ?: '—' }}</p>
            <p class="erp-po-index-stat__hint">IMEI or serial number</p>
        </div>

        <div class="erp-po-index-stat">
            <div class="erp-po-index-stat__head">
                <span class="erp-po-index-stat__label">Status</span>
                <span class="erp-po-index-stat__icon erp-po-index-stat__icon--success">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <p class="erp-po-index-stat__value !text-xl">{{ ucfirst($equipment->status) }}</p>
            <p class="erp-po-index-stat__hint">Assignment state</p>
        </div>
    </div>

    <div class="erp-po-show-layout">
        <div class="erp-po-show-main space-y-5">
            @if($equipment->notes)
                <div class="erp-po-show-panel">
                    <h3 class="erp-po-show-panel__title">Notes</h3>
                    <p class="erp-po-show-panel__body">{{ $equipment->notes }}</p>
                </div>
            @endif

            <div class="erp-order-list-card erp-po-index-table-card">
                <div class="erp-po-index-table-card__head">
                    <h2 class="erp-po-index-table-card__title">Assignment details</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="erp-order-list-table erp-po-show-table">
                        <tbody>
                            <tr>
                                <td class="text-gray-500 dark:text-gray-400">Product</td>
                                <td class="font-medium">{{ $equipment->product_name }}</td>
                            </tr>
                            <tr>
                                <td class="text-gray-500 dark:text-gray-400">Device ID / IMEI</td>
                                <td class="font-mono">{{ $equipment->device_identifier ?: '—' }}</td>
                            </tr>
                            <tr>
                                <td class="text-gray-500 dark:text-gray-400">Effective date</td>
                                <td>{{ $equipment->effective_date?->format('d M Y') ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="text-gray-500 dark:text-gray-400">Status</td>
                                <td><span class="erp-po-status erp-po-status--{{ $tone }}">{{ ucfirst($equipment->status) }}</span></td>
                            </tr>
                            <tr>
                                <td class="text-gray-500 dark:text-gray-400">Recorded</td>
                                <td>{{ $equipment->created_at?->format('d M Y H:i') ?? '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <aside class="erp-po-show-side space-y-5">
            @if($employee)
                <div class="erp-po-show-panel">
                    <h3 class="erp-po-show-panel__title">Assigned to</h3>
                    <div class="erp-po-index-supplier mt-3">
                        <span class="erp-po-index-supplier__avatar !h-10 !w-10">{{ strtoupper(substr($employee->name, 0, 1)) }}</span>
                        <div class="min-w-0">
                            <a href="{{ route('admin.employees.show', $employee) }}" class="erp-po-index-supplier__name !text-base hover:text-brand-600 dark:hover:text-brand-400">{{ $employee->name }}</a>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $employee->department ?? '—' }}</p>
                        </div>
                    </div>
                    <dl class="erp-po-show-meta mt-4">
                        @if($employee->work_zone)
                            <div class="erp-po-show-meta__row">
                                <dt>Zone</dt>
                                <dd>{{ $employee->work_zone }}</dd>
                            </div>
                        @endif
                        @if($employee->work_email)
                            <div class="erp-po-show-meta__row">
                                <dt>Email</dt>
                                <dd class="break-all">{{ $employee->work_email }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            @endif

            <div class="erp-po-show-panel">
                <h3 class="erp-po-show-panel__title">Related</h3>
                <div class="mt-3 flex flex-col gap-2">
                    <a href="{{ route('admin.contracts.index') }}" class="erp-order-btn erp-order-btn--secondary w-full justify-center">Contracts</a>
                    <a href="{{ route('admin.allowances.index') }}" class="erp-order-btn erp-order-btn--secondary w-full justify-center">Allowances</a>
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection
