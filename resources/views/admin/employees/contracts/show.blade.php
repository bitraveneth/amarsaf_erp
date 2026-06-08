@extends('layouts.app')

@section('content')
@php
    $currencyCode = config('app.currency', 'BDT');
    $employee = $contract->employee;
    $statusTones = ['active' => 'success', 'on_hold' => 'warning', 'ended' => 'neutral'];
    $statusLabels = ['active' => 'Active', 'on_hold' => 'On hold', 'ended' => 'Ended'];
    $tone = $statusTones[$contract->status] ?? 'neutral';
    $mbBill = $contract->monthlyBill();
@endphp

<div class="erp-order-page erp-order-page--index erp-po-show">
    <x-admin.order-toolbar
        :title="$contract->reference"
        :subtitle="'Employment contract · ' . ($employee->name ?? '—')"
        :back-url="route('admin.contracts.index')"
        back-label="All contracts"
    >
        <x-slot:actions>
            <span class="erp-po-status erp-po-status--{{ $tone }}">{{ $statusLabels[$contract->status] ?? ucfirst($contract->status) }}</span>
            @if($employee)
                <x-admin.action-group>
                    <x-admin.action-edit :href="route('admin.employees.contracts.edit', [$employee, $contract])" />
                </x-admin.action-group>
                <a href="{{ route('admin.employees.show', $employee) }}" class="erp-order-btn erp-order-btn--secondary">Employee profile</a>
            @endif
        </x-slot:actions>
    </x-admin.order-toolbar>

    @if(session('status'))
        <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200">
            {{ session('status') }}
        </div>
    @endif

    <div class="erp-po-index-stats">
        <div class="erp-po-index-stat">
            <div class="erp-po-index-stat__head">
                <span class="erp-po-index-stat__label">MB bill</span>
                <span class="erp-po-index-stat__icon erp-po-index-stat__icon--success">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <p class="erp-po-index-stat__value">{{ $currencyCode }} {{ number_format($mbBill, 0) }}</p>
            <p class="erp-po-index-stat__hint">Monthly package (salary + TA + DA)</p>
        </div>

        <div class="erp-po-index-stat">
            <div class="erp-po-index-stat__head">
                <span class="erp-po-index-stat__label">Base salary</span>
                <span class="erp-po-index-stat__icon erp-po-index-stat__icon--brand">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33"/></svg>
                </span>
            </div>
            <p class="erp-po-index-stat__value">{{ $currencyCode }} {{ number_format($contract->salary_amount ?? 0, 0) }}</p>
            <p class="erp-po-index-stat__hint">TA {{ number_format($contract->travel_allowance ?? 0, 0) }} · DA {{ number_format($contract->dearness_allowance ?? 0, 0) }}</p>
        </div>

        <div class="erp-po-index-stat">
            <div class="erp-po-index-stat__head">
                <span class="erp-po-index-stat__label">Annual bonus</span>
                <span class="erp-po-index-stat__icon erp-po-index-stat__icon--warning">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 11.25v8.25a1.5 1.5 0 01-1.5 1.5H5.25a1.5 1.5 0 01-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 1014.625 7.5 2.625 2.625 0 0012 4.875z"/></svg>
                </span>
            </div>
            <p class="erp-po-index-stat__value">{{ $currencyCode }} {{ number_format($contract->bonus ?? 0, 0) }}</p>
            <p class="erp-po-index-stat__hint">Separate from monthly bill</p>
        </div>

        <div class="erp-po-index-stat">
            <div class="erp-po-index-stat__head">
                <span class="erp-po-index-stat__label">Contract period</span>
                <span class="erp-po-index-stat__icon erp-po-index-stat__icon--brand">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </span>
            </div>
            <p class="erp-po-index-stat__value text-lg">{{ $contract->start_date?->format('d M Y') }}</p>
            <p class="erp-po-index-stat__hint">to {{ $contract->end_date?->format('d M Y') ?? 'Open-ended' }}</p>
        </div>
    </div>

    <div class="erp-po-show-layout">
        <div class="erp-po-show-main space-y-5">
            <div class="erp-order-list-card erp-po-index-table-card">
                <div class="erp-po-index-table-card__head">
                    <div>
                        <h2 class="erp-po-index-table-card__title">Compensation breakdown</h2>
                        <p class="erp-po-index-table-card__desc">Fixed monthly components that make up the MB bill.</p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="erp-order-list-table erp-po-show-table">
                        <thead>
                            <tr>
                                <th>Component</th>
                                <th class="is-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Base salary</td>
                                <td class="is-right font-medium">{{ $currencyCode }} {{ number_format($contract->salary_amount ?? 0, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Travel allowance (TA)</td>
                                <td class="is-right font-medium">{{ $currencyCode }} {{ number_format($contract->travel_allowance ?? 0, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Dearness allowance (DA)</td>
                                <td class="is-right font-medium">{{ $currencyCode }} {{ number_format($contract->dearness_allowance ?? 0, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="font-semibold text-gray-900 dark:text-white">MB bill (monthly)</td>
                                <td class="is-right font-bold text-gray-900 dark:text-white">{{ $currencyCode }} {{ number_format($mbBill, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Annual bonus</td>
                                <td class="is-right font-medium">{{ $currencyCode }} {{ number_format($contract->bonus ?? 0, 2) }}</td>
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
                    <dl class="erp-po-show-meta mt-4">
                        @if($employee->work_email)
                            <div class="erp-po-show-meta__row">
                                <dt>Email</dt>
                                <dd class="break-all">{{ $employee->work_email }}</dd>
                            </div>
                        @endif
                        @if($employee->work_zone)
                            <div class="erp-po-show-meta__row">
                                <dt>Zone</dt>
                                <dd>{{ $employee->work_zone }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            @endif

            <div class="erp-po-show-panel">
                <h3 class="erp-po-show-panel__title">Schedule</h3>
                <dl class="erp-po-show-meta mt-4">
                    <div class="erp-po-show-meta__row">
                        <dt>Working schedule</dt>
                        <dd>{{ $contract->working_schedule ?: '—' }}</dd>
                    </div>
                    <div class="erp-po-show-meta__row">
                        <dt>Start date</dt>
                        <dd>{{ $contract->start_date?->format('d M Y') ?? '—' }}</dd>
                    </div>
                    <div class="erp-po-show-meta__row">
                        <dt>End date</dt>
                        <dd>{{ $contract->end_date?->format('d M Y') ?? 'Open-ended' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="erp-po-show-panel">
                <h3 class="erp-po-show-panel__title">Related</h3>
                <div class="mt-3 flex flex-col gap-2">
                    <a href="{{ route('admin.allowances.index') }}" class="erp-order-btn erp-order-btn--secondary w-full justify-center">Allowances</a>
                    <a href="{{ route('admin.equipment.index') }}" class="erp-order-btn erp-order-btn--secondary w-full justify-center">Equipment</a>
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection
