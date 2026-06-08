@extends('layouts.app')

@section('content')
@php
    $currencyCode = config('app.currency', 'BDT');
    $currentType = strtoupper(old('type', $allowance->type ?? 'TA'));
    $currentStatus = old('status', $allowance->status ?? 'submitted');
@endphp

<div class="erp-order-page erp-order-page--index screen-allowance-create">
    <header class="po-create__page-head">
        <div>
            <h1 class="po-create__page-title">Submit allowance claim</h1>
            <p class="po-create__page-desc">Record TA, DA, bonus, or other employee reimbursement with optional slip.</p>
        </div>
        <a href="{{ route('admin.allowances.index') }}" class="po-create__back-btn">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to allowances
        </a>
    </header>

    <form action="{{ route('admin.allowances.store') }}" method="POST" enctype="multipart/form-data" class="po-create">
        @csrf

        <div class="po-create__grid">
            <section class="po-create__card">
                <header class="po-create__card-head">
                    <div>
                        <h2 class="po-create__card-title">Employee</h2>
                        <p class="po-create__card-desc">Who is submitting this claim.</p>
                    </div>
                </header>
                <div class="po-create__fields">
                    <div class="po-create__field po-create__field--wide">
                        <label for="employee_id" class="po-create__label">Employee <span class="po-create__req">*</span></label>
                        <select id="employee_id" name="employee_id" required class="po-create__input">
                            <option value="">Select employee…</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" {{ (string) old('employee_id') === (string) $employee->id ? 'selected' : '' }}>
                                    {{ $employee->name }}@if($employee->department) · {{ $employee->department }}@endif
                                </option>
                            @endforeach
                        </select>
                        @error('employee_id')<p class="po-create__error">{{ $message }}</p>@enderror
                    </div>
                </div>
            </section>

            <section class="po-create__card po-create__card--lines">
                <header class="po-create__card-head po-create__card-head--border">
                    <div>
                        <h2 class="po-create__card-title">Claim details</h2>
                        <p class="po-create__card-desc">Type, amount, and approval status.</p>
                    </div>
                </header>
                <div class="po-create__fields">
                    <div class="po-create__field">
                        <label for="date" class="po-create__label">Date <span class="po-create__req">*</span></label>
                        <input type="date" id="date" name="date" required
                               value="{{ old('date', optional($allowance->date)->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                               class="po-create__input">
                        @error('date')<p class="po-create__error">{{ $message }}</p>@enderror
                    </div>
                    <div class="po-create__field">
                        <label for="type" class="po-create__label">Type <span class="po-create__req">*</span></label>
                        <select id="type" name="type" required class="po-create__input">
                            <option value="TA" {{ $currentType === 'TA' ? 'selected' : '' }}>TA — Travel allowance</option>
                            <option value="DA" {{ $currentType === 'DA' ? 'selected' : '' }}>DA — Dearness allowance</option>
                            <option value="BONUS" {{ $currentType === 'BONUS' ? 'selected' : '' }}>Bonus</option>
                            <option value="OTHER" {{ $currentType === 'OTHER' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('type')<p class="po-create__error">{{ $message }}</p>@enderror
                    </div>
                    <div class="po-create__field">
                        <label for="amount" class="po-create__label">Amount ({{ $currencyCode }}) <span class="po-create__req">*</span></label>
                        <input type="number" id="amount" name="amount" step="0.01" min="0" required
                               value="{{ old('amount', $allowance->amount ?? '') }}"
                               placeholder="0.00" class="po-create__input po-create__input--num">
                        @error('amount')<p class="po-create__error">{{ $message }}</p>@enderror
                    </div>
                    <div class="po-create__field">
                        <label for="status" class="po-create__label">Status <span class="po-create__req">*</span></label>
                        <select id="status" name="status" required class="po-create__input">
                            <option value="submitted" {{ $currentStatus === 'submitted' ? 'selected' : '' }}>Submitted</option>
                            <option value="approved" {{ $currentStatus === 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="paid" {{ $currentStatus === 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="rejected" {{ $currentStatus === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                        @error('status')<p class="po-create__error">{{ $message }}</p>@enderror
                    </div>
                    <div class="po-create__field">
                        <label for="reference" class="po-create__label">Reference</label>
                        <input type="text" id="reference" name="reference"
                               value="{{ old('reference', $allowance->reference ?? '') }}"
                               placeholder="INV-2026-001" class="po-create__input">
                        @error('reference')<p class="po-create__error">{{ $message }}</p>@enderror
                    </div>
                    <div class="po-create__field po-create__field--wide">
                        <label for="description" class="po-create__label">Description</label>
                        <textarea id="description" name="description" rows="3" class="po-create__input"
                                  placeholder="Trip purpose, route, or approval notes…">{{ old('description', $allowance->description ?? '') }}</textarea>
                        @error('description')<p class="po-create__error">{{ $message }}</p>@enderror
                    </div>
                    <div class="po-create__field po-create__field--wide">
                        <label for="attachment" class="po-create__label">Slip / attachment</label>
                        <input type="file" id="attachment" name="attachment" class="po-create__input">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">PDF, JPG, or PNG — max 5 MB</p>
                        @error('attachment')<p class="po-create__error">{{ $message }}</p>@enderror
                    </div>
                </div>
            </section>
        </div>

        <x-admin.order-footer
            submit-label="Submit allowance"
            :cancel-url="route('admin.allowances.index')"
            hint="Saved as submitted unless you choose another status"
        />
    </form>
</div>
@endsection
