@extends('layouts.app')

@section('content')
@php
    $currencyCode = config('app.currency', 'BDT');
    $totalContracts = $contracts instanceof \Illuminate\Pagination\LengthAwarePaginator ? $contracts->total() : $contracts->count();
    $activeContracts = $contracts->where('status', 'active')->count();
    $uniqueEmployees = $contracts->pluck('employee.id')->filter()->unique()->count();
    $totalMbBill = $contracts->sum(fn ($c) => $c->monthlyBill());
    $totalBonus = $contracts->sum('bonus');

    $statusColors = [
        'active' => 'success',
        'on_hold' => 'warning',
        'ended' => 'neutral',
    ];
    $statusLabels = [
        'active' => 'Active',
        'on_hold' => 'On hold',
        'ended' => 'Ended',
    ];
@endphp

<div class="erp-order-page erp-order-page--index screen-employee-contracts">
    <x-admin.order-toolbar
        title="Employee contracts"
        subtitle="Employment terms, salary packages, and contract status across the workforce."
        :back-url="route('admin.employees.index')"
        back-label="Employees"
    >
        <x-slot:actions>
            <a href="{{ route('admin.contracts.create') }}" class="erp-order-btn erp-order-btn--primary">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add contract
            </a>
        </x-slot:actions>
    </x-admin.order-toolbar>

    @if(session('status'))
        <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200">
            {{ session('status') }}
        </div>
    @endif

    @if($contracts->isNotEmpty())
        <div class="erp-po-index-stats">
            <div class="erp-po-index-stat">
                <div class="erp-po-index-stat__head">
                    <span class="erp-po-index-stat__label">Contracts</span>
                    <span class="erp-po-index-stat__icon erp-po-index-stat__icon--brand">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                    </span>
                </div>
                <p class="erp-po-index-stat__value">{{ number_format($totalContracts) }}</p>
                <p class="erp-po-index-stat__hint">{{ $activeContracts }} active on this page</p>
            </div>

            <div class="erp-po-index-stat">
                <div class="erp-po-index-stat__head">
                    <span class="erp-po-index-stat__label">Employees</span>
                    <span class="erp-po-index-stat__icon erp-po-index-stat__icon--brand">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </span>
                </div>
                <p class="erp-po-index-stat__value">{{ number_format($uniqueEmployees) }}</p>
                <p class="erp-po-index-stat__hint">With contract records</p>
            </div>

            <div class="erp-po-index-stat">
                <div class="erp-po-index-stat__head">
                    <span class="erp-po-index-stat__label">MB bill total</span>
                    <span class="erp-po-index-stat__icon erp-po-index-stat__icon--success">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                </div>
                <p class="erp-po-index-stat__value">{{ $currencyCode }} {{ number_format($totalMbBill, 0) }}</p>
                <p class="erp-po-index-stat__hint">Salary + TA + DA (this page)</p>
            </div>

            <div class="erp-po-index-stat">
                <div class="erp-po-index-stat__head">
                    <span class="erp-po-index-stat__label">Bonus</span>
                    <span class="erp-po-index-stat__icon erp-po-index-stat__icon--warning">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 11.25v8.25a1.5 1.5 0 01-1.5 1.5H5.25a1.5 1.5 0 01-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 1014.625 7.5 2.625 2.625 0 0012 4.875z"/></svg>
                    </span>
                </div>
                <p class="erp-po-index-stat__value">{{ $currencyCode }} {{ number_format($totalBonus, 0) }}</p>
                <p class="erp-po-index-stat__hint">Annual bonus commitments</p>
            </div>
        </div>
    @endif

    <div class="erp-order-list-card erp-po-index-table-card">
        <div class="erp-po-index-table-card__head">
            <div>
                <h2 class="erp-po-index-table-card__title">Contract register</h2>
                <p class="erp-po-index-table-card__desc">Monthly bill (MB) is salary plus TA and DA — open a contract for full period and breakdown.</p>
            </div>
            @if($totalContracts > 0)
                <span class="erp-po-index-table-card__badge">{{ $contracts->count() }} of {{ number_format($totalContracts) }}</span>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="erp-order-list-table erp-so-index-table">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Reference</th>
                        <th class="is-right">MB bill</th>
                        <th class="is-right">Bonus</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($contracts as $contract)
                        @php
                            $employee = $contract->employee;
                            $employeeName = $employee->name ?? '—';
                            $employeeInitial = strtoupper(substr(trim($employeeName), 0, 1)) ?: '?';
                            $tone = $statusColors[$contract->status] ?? 'neutral';
                        @endphp
                        <tr>
                            <td>
                                <div class="erp-po-index-supplier">
                                    <span class="erp-po-index-supplier__avatar">{{ $employeeInitial }}</span>
                                    <div>
                                        @if($employee)
                                            <a href="{{ route('admin.contracts.show', $contract) }}" class="erp-po-index-supplier__name hover:text-brand-600 dark:hover:text-brand-400">
                                                {{ $employeeName }}
                                            </a>
                                            <p class="erp-po-index-po-meta">{{ $employee->department ?? '—' }}@if($employee->job_position) · {{ $employee->job_position }}@endif</p>
                                        @else
                                            <span class="erp-po-index-supplier__name">—</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="font-mono text-sm font-medium text-gray-900 dark:text-white">{{ $contract->reference }}</span>
                                @if($contract->status === 'active')
                                    <p class="erp-po-index-po-meta text-success-600 dark:text-success-400">Current contract</p>
                                @endif
                            </td>
                            <td class="is-right whitespace-nowrap">
                                <span class="erp-table-num font-semibold text-gray-900 dark:text-white">
                                    {{ $currencyCode }} {{ number_format($contract->monthlyBill(), 0) }}
                                </span>
                                <p class="erp-po-index-po-meta">Sal {{ number_format($contract->salary_amount ?? 0, 0) }} + TA/DA</p>
                            </td>
                            <td class="is-right whitespace-nowrap">
                                <span class="erp-table-num font-medium text-gray-700 dark:text-gray-300">
                                    {{ $currencyCode }} {{ number_format($contract->bonus ?? 0, 0) }}
                                </span>
                            </td>
                            <td>
                                <span class="erp-po-status erp-po-status--{{ $tone }}">
                                    {{ $statusLabels[$contract->status] ?? ucfirst($contract->status) }}
                                </span>
                            </td>
                            <td class="text-right">
                                @if($employee)
                                    <x-admin.action-group>
                                        <x-admin.action-view :href="route('admin.contracts.show', $contract)" />
                                        <x-admin.action-edit :href="route('admin.employees.contracts.edit', [$employee, $contract])" />
                                    </x-admin.action-group>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="!px-6 !py-16 text-center">
                                <div class="erp-po-index-empty">
                                    <div class="erp-po-index-empty__icon" aria-hidden="true">
                                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                                    </div>
                                    <p class="text-base font-semibold text-gray-900 dark:text-white">No contracts yet</p>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Add the first employment contract to track salary and allowances.</p>
                                    <a href="{{ route('admin.contracts.create') }}" class="erp-order-btn erp-order-btn--brand mt-5">Add contract</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($contracts, 'hasPages') && $contracts->hasPages())
            <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">
                {{ $contracts->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
