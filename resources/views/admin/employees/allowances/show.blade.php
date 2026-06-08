@extends('layouts.app')

@section('content')
@php
    $currencyCode = config('app.currency', 'BDT');
    $employee = $allowance->employee;
    $statusTones = ['submitted' => 'warning', 'approved' => 'success', 'paid' => 'brand', 'rejected' => 'neutral'];
    $tone = $statusTones[$allowance->status] ?? 'neutral';
    $typeLabels = ['TA' => 'Travel allowance', 'DA' => 'Dearness allowance', 'BONUS' => 'Bonus', 'OTHER' => 'Other'];
    $typeLabel = $typeLabels[strtoupper($allowance->type)] ?? strtoupper($allowance->type);
@endphp

<div class="erp-order-page erp-order-page--index erp-po-show">
    <x-admin.order-toolbar
        :title="$allowance->reference ?: ('Allowance #' . $allowance->id)"
        :subtitle="$typeLabel . ' · ' . ($employee->name ?? '—')"
        :back-url="route('admin.allowances.index')"
        back-label="All allowances"
    >
        <x-slot:actions>
            <span class="erp-po-status erp-po-status--{{ $tone }}">{{ ucfirst($allowance->status) }}</span>
            @if($employee)
                <x-admin.action-group>
                    <x-admin.action-edit :href="route('admin.employees.allowances.edit', [$employee, $allowance])" />
                </x-admin.action-group>
                <a href="{{ route('admin.employees.show', $employee) }}" class="erp-order-btn erp-order-btn--secondary">Employee profile</a>
            @endif
        </x-slot:actions>
    </x-admin.order-toolbar>

    <div class="erp-po-index-stats">
        <div class="erp-po-index-stat">
            <div class="erp-po-index-stat__head">
                <span class="erp-po-index-stat__label">Amount</span>
                <span class="erp-po-index-stat__icon erp-po-index-stat__icon--success">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <p class="erp-po-index-stat__value">{{ $currencyCode }} {{ number_format($allowance->amount, 0) }}</p>
            <p class="erp-po-index-stat__hint">{{ strtoupper($allowance->type) }} claim</p>
        </div>

        <div class="erp-po-index-stat">
            <div class="erp-po-index-stat__head">
                <span class="erp-po-index-stat__label">Claim date</span>
                <span class="erp-po-index-stat__icon erp-po-index-stat__icon--brand">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </span>
            </div>
            <p class="erp-po-index-stat__value !text-xl">{{ $allowance->date?->format('d M Y') ?? '—' }}</p>
            <p class="erp-po-index-stat__hint">{{ $allowance->date?->diffForHumans() }}</p>
        </div>

        <div class="erp-po-index-stat">
            <div class="erp-po-index-stat__head">
                <span class="erp-po-index-stat__label">Status</span>
                <span class="erp-po-index-stat__icon erp-po-index-stat__icon--warning">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <p class="erp-po-index-stat__value !text-xl">{{ ucfirst($allowance->status) }}</p>
            <p class="erp-po-index-stat__hint">Approval workflow</p>
        </div>

        <div class="erp-po-index-stat">
            <div class="erp-po-index-stat__head">
                <span class="erp-po-index-stat__label">Slip</span>
                <span class="erp-po-index-stat__icon erp-po-index-stat__icon--brand">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                </span>
            </div>
            <p class="erp-po-index-stat__value !text-xl">{{ $allowance->attachment_path ? 'Yes' : 'No' }}</p>
            <p class="erp-po-index-stat__hint">Supporting document</p>
        </div>
    </div>

    <div class="erp-po-show-layout">
        <div class="erp-po-show-main space-y-5">
            @if($allowance->description)
                <div class="erp-po-show-panel">
                    <h3 class="erp-po-show-panel__title">Description</h3>
                    <p class="erp-po-show-panel__body">{{ $allowance->description }}</p>
                </div>
            @endif

            <div class="erp-order-list-card erp-po-index-table-card">
                <div class="erp-po-index-table-card__head">
                    <h2 class="erp-po-index-table-card__title">Claim details</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="erp-order-list-table erp-po-show-table">
                        <tbody>
                            <tr>
                                <td class="text-gray-500 dark:text-gray-400">Type</td>
                                <td class="font-medium">{{ $typeLabel }} ({{ strtoupper($allowance->type) }})</td>
                            </tr>
                            <tr>
                                <td class="text-gray-500 dark:text-gray-400">Reference</td>
                                <td class="font-mono">{{ $allowance->reference ?: '—' }}</td>
                            </tr>
                            <tr>
                                <td class="text-gray-500 dark:text-gray-400">Amount</td>
                                <td class="font-semibold">{{ $currencyCode }} {{ number_format($allowance->amount, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="text-gray-500 dark:text-gray-400">Date</td>
                                <td>{{ $allowance->date?->format('d M Y') ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="text-gray-500 dark:text-gray-400">Status</td>
                                <td><span class="erp-po-status erp-po-status--{{ $tone }}">{{ ucfirst($allowance->status) }}</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <aside class="erp-po-show-side space-y-5">
            @if($employee)
                <div class="erp-po-show-panel">
                    <h3 class="erp-po-show-panel__title">Employee</h3>
                    <div class="erp-po-index-supplier mt-3">
                        <span class="erp-po-index-supplier__avatar !h-10 !w-10">{{ strtoupper(substr($employee->name, 0, 1)) }}</span>
                        <div class="min-w-0">
                            <a href="{{ route('admin.employees.show', $employee) }}" class="erp-po-index-supplier__name !text-base hover:text-brand-600 dark:hover:text-brand-400">{{ $employee->name }}</a>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $employee->department ?? '—' }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if($allowance->attachment_path)
                <div class="erp-po-show-panel">
                    <h3 class="erp-po-show-panel__title">Attachment</h3>
                    <p class="erp-po-show-panel__body text-sm">{{ basename($allowance->attachment_path) }}</p>
                    <a href="{{ asset('storage/'.$allowance->attachment_path) }}" target="_blank"
                       class="erp-order-btn erp-order-btn--brand mt-3 w-full justify-center">View slip</a>
                </div>
            @endif
        </aside>
    </div>
</div>
@endsection
