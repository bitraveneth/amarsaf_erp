@extends('layouts.app')

@section('content')
@php
    $currencyCode = config('app.currency', 'BDT');
    $totalAllowances = $allowances instanceof \Illuminate\Pagination\LengthAwarePaginator ? $allowances->total() : $allowances->count();
    $uniqueEmployees = $allowances->pluck('employee.id')->filter()->unique()->count();
    $totalAmount = $allowances->sum('amount');
    $submittedCount = $allowances->where('status', 'submitted')->count();
    $paidAmount = $allowances->where('status', 'paid')->sum('amount');

    $statusColors = [
        'submitted' => 'warning',
        'approved' => 'success',
        'paid' => 'brand',
        'rejected' => 'neutral',
    ];
    $typeColors = [
        'TA' => 'brand',
        'DA' => 'warning',
        'BONUS' => 'success',
        'OTHER' => 'neutral',
    ];
@endphp

<div class="erp-order-page erp-order-page--index screen-employee-allowances">
    <x-admin.order-toolbar
        title="Employee allowances"
        subtitle="TA, DA, bonus claims and reimbursement slips across the workforce."
        :back-url="route('admin.employees.index')"
        back-label="Employees"
    >
        <x-slot:actions>
            <a href="{{ route('admin.allowances.create') }}" class="erp-order-btn erp-order-btn--primary">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add allowance
            </a>
        </x-slot:actions>
    </x-admin.order-toolbar>

    @if(session('status'))
        <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200">
            {{ session('status') }}
        </div>
    @endif

    @if($allowances->isNotEmpty())
        <div class="erp-po-index-stats">
            <div class="erp-po-index-stat">
                <div class="erp-po-index-stat__head">
                    <span class="erp-po-index-stat__label">Claims</span>
                    <span class="erp-po-index-stat__icon erp-po-index-stat__icon--brand">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33"/></svg>
                    </span>
                </div>
                <p class="erp-po-index-stat__value">{{ number_format($totalAllowances) }}</p>
                <p class="erp-po-index-stat__hint">{{ $uniqueEmployees }} employees</p>
            </div>

            <div class="erp-po-index-stat">
                <div class="erp-po-index-stat__head">
                    <span class="erp-po-index-stat__label">Total amount</span>
                    <span class="erp-po-index-stat__icon erp-po-index-stat__icon--success">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                </div>
                <p class="erp-po-index-stat__value">{{ $currencyCode }} {{ number_format($totalAmount, 0) }}</p>
                <p class="erp-po-index-stat__hint">This page total</p>
            </div>

            <div class="erp-po-index-stat">
                <div class="erp-po-index-stat__head">
                    <span class="erp-po-index-stat__label">Submitted</span>
                    <span class="erp-po-index-stat__icon erp-po-index-stat__icon--warning">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    </span>
                </div>
                <p class="erp-po-index-stat__value">{{ $submittedCount }}</p>
                <p class="erp-po-index-stat__hint">Awaiting approval</p>
            </div>

            <div class="erp-po-index-stat">
                <div class="erp-po-index-stat__head">
                    <span class="erp-po-index-stat__label">Paid</span>
                    <span class="erp-po-index-stat__icon erp-po-index-stat__icon--brand">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                </div>
                <p class="erp-po-index-stat__value">{{ $currencyCode }} {{ number_format($paidAmount, 0) }}</p>
                <p class="erp-po-index-stat__hint">Settled on this page</p>
            </div>
        </div>
    @endif

    <div class="erp-order-list-card erp-po-index-table-card">
        <div class="erp-po-index-table-card__head">
            <div>
                <h2 class="erp-po-index-table-card__title">Allowance register</h2>
                <p class="erp-po-index-table-card__desc">Travel, dearness, and other employee reimbursement claims.</p>
            </div>
            @if($totalAllowances > 0)
                <span class="erp-po-index-table-card__badge">{{ $allowances->count() }} of {{ number_format($totalAllowances) }}</span>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="erp-order-list-table erp-so-index-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Employee</th>
                        <th>Type</th>
                        <th>Reference</th>
                        <th class="is-right">Amount</th>
                        <th>Status</th>
                        <th>Slip</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($allowances as $allowance)
                        @php
                            $employee = $allowance->employee;
                            $employeeName = $employee->name ?? '—';
                            $employeeInitial = strtoupper(substr(trim($employeeName), 0, 1)) ?: '?';
                            $statusTone = $statusColors[$allowance->status] ?? 'neutral';
                            $typeTone = $typeColors[strtoupper($allowance->type)] ?? 'neutral';
                        @endphp
                        <tr>
                            <td class="whitespace-nowrap">
                                <span class="font-medium text-gray-900 dark:text-white">{{ $allowance->date?->format('d M Y') }}</span>
                            </td>
                            <td>
                                <div class="erp-po-index-supplier">
                                    <span class="erp-po-index-supplier__avatar">{{ $employeeInitial }}</span>
                                    <div>
                                        @if($employee)
                                            <a href="{{ route('admin.allowances.show', $allowance) }}" class="erp-po-index-supplier__name hover:text-brand-600 dark:hover:text-brand-400">{{ $employeeName }}</a>
                                            <p class="erp-po-index-po-meta">{{ $employee->department ?? '—' }}</p>
                                        @else
                                            <span class="erp-po-index-supplier__name">—</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="erp-po-status erp-po-status--{{ $typeTone }}">{{ strtoupper($allowance->type) }}</span>
                            </td>
                            <td>
                                <span class="font-mono text-sm text-gray-900 dark:text-white">{{ $allowance->reference ?: '—' }}</span>
                            </td>
                            <td class="is-right whitespace-nowrap">
                                <span class="erp-table-num font-semibold text-gray-900 dark:text-white">
                                    {{ $currencyCode }} {{ number_format($allowance->amount, 0) }}
                                </span>
                            </td>
                            <td>
                                <span class="erp-po-status erp-po-status--{{ $statusTone }}">{{ ucfirst($allowance->status) }}</span>
                            </td>
                            <td>
                                @if($allowance->attachment_path)
                                    <a href="{{ asset('storage/'.$allowance->attachment_path) }}" target="_blank" class="text-xs font-semibold text-brand-600 hover:underline dark:text-brand-400">View slip</a>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="text-right">
                                @if($employee)
                                    <x-admin.action-group>
                                        <x-admin.action-view :href="route('admin.allowances.show', $allowance)" />
                                        <x-admin.action-edit :href="route('admin.employees.allowances.edit', [$employee, $allowance])" />
                                    </x-admin.action-group>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="!px-6 !py-16 text-center">
                                <div class="erp-po-index-empty">
                                    <div class="erp-po-index-empty__icon" aria-hidden="true">
                                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33"/></svg>
                                    </div>
                                    <p class="text-base font-semibold text-gray-900 dark:text-white">No allowances yet</p>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Submit the first TA/DA or bonus claim.</p>
                                    <a href="{{ route('admin.allowances.create') }}" class="erp-order-btn erp-order-btn--brand mt-5">Add allowance</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($allowances, 'hasPages') && $allowances->hasPages())
            <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">
                {{ $allowances->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
