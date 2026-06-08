@extends('layouts.app')

@section('content')
@php
    $currencyCode = config('app.currency', 'BDT');
    $startDefault = old('start_date', now()->format('Y-m-d'));
    $currentStatus = old('status', $contract->status ?? 'active');
@endphp

<div class="erp-order-page erp-order-page--index screen-contract-create">
    <header class="po-create__page-head">
        <div>
            <h1 class="po-create__page-title">Create employment contract</h1>
            <p class="po-create__page-desc">Define salary, allowances, and contract terms for an employee.</p>
        </div>
        <a href="{{ route('admin.contracts.index') }}" class="po-create__back-btn">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to contracts
        </a>
    </header>

    <form action="{{ route('admin.contracts.store') }}" method="POST"
          x-data="{
              salary: {{ (float) old('salary_amount', $contract->salary_amount ?? 0) }},
              ta: {{ (float) old('travel_allowance', $contract->travel_allowance ?? 0) }},
              da: {{ (float) old('dearness_allowance', $contract->dearness_allowance ?? 0) }},
              mbBill() { return (parseFloat(this.salary) || 0) + (parseFloat(this.ta) || 0) + (parseFloat(this.da) || 0); }
          }"
          class="po-create">
        @csrf

        <div class="po-create__grid">
            <section class="po-create__card">
                <header class="po-create__card-head">
                    <div>
                        <h2 class="po-create__card-title">Employee &amp; reference</h2>
                        <p class="po-create__card-desc">Who this contract belongs to and how it is identified.</p>
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
                    <div class="po-create__field">
                        <label for="reference" class="po-create__label">Contract reference <span class="po-create__req">*</span></label>
                        <input type="text" id="reference" name="reference" required
                               value="{{ old('reference', $contract->reference ?? '') }}"
                               placeholder="CT-2026-001" class="po-create__input">
                        @error('reference')<p class="po-create__error">{{ $message }}</p>@enderror
                    </div>
                    <div class="po-create__field">
                        <label for="status" class="po-create__label">Status <span class="po-create__req">*</span></label>
                        <select id="status" name="status" required class="po-create__input">
                            <option value="active" {{ $currentStatus === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="on_hold" {{ $currentStatus === 'on_hold' ? 'selected' : '' }}>On hold</option>
                            <option value="ended" {{ $currentStatus === 'ended' ? 'selected' : '' }}>Ended</option>
                        </select>
                        @error('status')<p class="po-create__error">{{ $message }}</p>@enderror
                    </div>
                </div>
            </section>

            <section class="po-create__card">
                <header class="po-create__card-head">
                    <div>
                        <h2 class="po-create__card-title">Period &amp; schedule</h2>
                        <p class="po-create__card-desc">Contract dates and working hours.</p>
                    </div>
                </header>
                <div class="po-create__fields">
                    <div class="po-create__field">
                        <label for="start_date" class="po-create__label">Start date <span class="po-create__req">*</span></label>
                        <input type="date" id="start_date" name="start_date" required value="{{ $startDefault }}" class="po-create__input">
                        @error('start_date')<p class="po-create__error">{{ $message }}</p>@enderror
                    </div>
                    <div class="po-create__field">
                        <label for="end_date" class="po-create__label">End date</label>
                        <input type="date" id="end_date" name="end_date"
                               value="{{ old('end_date', optional($contract->end_date)->format('Y-m-d')) }}"
                               class="po-create__input">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Leave blank for open-ended</p>
                        @error('end_date')<p class="po-create__error">{{ $message }}</p>@enderror
                    </div>
                    <div class="po-create__field po-create__field--wide">
                        <label for="working_schedule" class="po-create__label">Working schedule</label>
                        <input type="text" id="working_schedule" name="working_schedule" list="working-schedules"
                               value="{{ old('working_schedule', $contract->working_schedule ?? '') }}"
                               placeholder="Mon–Sat, 9:00–17:00" class="po-create__input">
                        @isset($workingSchedules)
                            <datalist id="working-schedules">
                                @foreach($workingSchedules as $schedule)
                                    <option value="{{ $schedule }}"></option>
                                @endforeach
                            </datalist>
                        @endisset
                        @error('working_schedule')<p class="po-create__error">{{ $message }}</p>@enderror
                    </div>
                </div>
            </section>

            <section class="po-create__card po-create__card--lines">
                <header class="po-create__card-head po-create__card-head--border">
                    <div>
                        <h2 class="po-create__card-title">Salary &amp; allowances</h2>
                        <p class="po-create__card-desc">Monthly components — MB bill updates live as you type.</p>
                    </div>
                    <span class="po-create__badge">
                        MB bill: {{ $currencyCode }} <span x-text="mbBill().toLocaleString(undefined, {maximumFractionDigits: 0})">0</span>
                    </span>
                </header>
                <div class="po-create__fields">
                    <div class="po-create__field">
                        <label for="salary_amount" class="po-create__label">Base salary</label>
                        <input type="number" id="salary_amount" name="salary_amount" step="0.01" min="0"
                               x-model.number="salary" value="{{ old('salary_amount', $contract->salary_amount ?? '') }}"
                               placeholder="0" class="po-create__input po-create__input--num">
                        @error('salary_amount')<p class="po-create__error">{{ $message }}</p>@enderror
                    </div>
                    <div class="po-create__field">
                        <label for="travel_allowance" class="po-create__label">Travel allowance (TA)</label>
                        <input type="number" id="travel_allowance" name="travel_allowance" step="0.01" min="0"
                               x-model.number="ta" value="{{ old('travel_allowance', $contract->travel_allowance ?? '') }}"
                               placeholder="0" class="po-create__input po-create__input--num">
                        @error('travel_allowance')<p class="po-create__error">{{ $message }}</p>@enderror
                    </div>
                    <div class="po-create__field">
                        <label for="dearness_allowance" class="po-create__label">Dearness allowance (DA)</label>
                        <input type="number" id="dearness_allowance" name="dearness_allowance" step="0.01" min="0"
                               x-model.number="da" value="{{ old('dearness_allowance', $contract->dearness_allowance ?? '') }}"
                               placeholder="0" class="po-create__input po-create__input--num">
                        @error('dearness_allowance')<p class="po-create__error">{{ $message }}</p>@enderror
                    </div>
                    <div class="po-create__field">
                        <label for="bonus" class="po-create__label">Annual bonus</label>
                        <input type="number" id="bonus" name="bonus" step="0.01" min="0"
                               value="{{ old('bonus', $contract->bonus ?? '') }}"
                               placeholder="0" class="po-create__input po-create__input--num">
                        @error('bonus')<p class="po-create__error">{{ $message }}</p>@enderror
                    </div>
                </div>
            </section>
        </div>

        <x-admin.order-footer
            submit-label="Save contract"
            :cancel-url="route('admin.contracts.index')"
            hint="MB bill = salary + TA + DA — shown on the contract register"
        />
    </form>
</div>
@endsection
