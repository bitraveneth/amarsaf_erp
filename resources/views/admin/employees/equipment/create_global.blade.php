@extends('layouts.app')

@section('content')
@php
    $currentStatus = old('status', $item->status ?? 'active');
@endphp

<div class="erp-order-page erp-order-page--index screen-equipment-create">
    <header class="po-create__page-head">
        <div>
            <h1 class="po-create__page-title">Assign equipment</h1>
            <p class="po-create__page-desc">Record a phone, tablet, laptop, or other device issued to an employee.</p>
        </div>
        <a href="{{ route('admin.equipment.index') }}" class="po-create__back-btn">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to equipment
        </a>
    </header>

    <form action="{{ route('admin.equipment.store') }}" method="POST" class="po-create">
        @csrf

        <div class="po-create__grid">
            <section class="po-create__card">
                <header class="po-create__card-head">
                    <div>
                        <h2 class="po-create__card-title">Employee</h2>
                        <p class="po-create__card-desc">Who receives this device or asset.</p>
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
                        <h2 class="po-create__card-title">Device details</h2>
                        <p class="po-create__card-desc">Product name, identifier, and assignment status.</p>
                    </div>
                </header>
                <div class="po-create__fields">
                    <div class="po-create__field">
                        <label for="effective_date" class="po-create__label">Effective date <span class="po-create__req">*</span></label>
                        <input type="date" id="effective_date" name="effective_date" required
                               value="{{ old('effective_date', optional($item->effective_date)->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                               class="po-create__input">
                        @error('effective_date')<p class="po-create__error">{{ $message }}</p>@enderror
                    </div>
                    <div class="po-create__field">
                        <label for="status" class="po-create__label">Status <span class="po-create__req">*</span></label>
                        <select id="status" name="status" required class="po-create__input">
                            <option value="active" {{ $currentStatus === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="returned" {{ $currentStatus === 'returned' ? 'selected' : '' }}>Returned</option>
                            <option value="lost" {{ $currentStatus === 'lost' ? 'selected' : '' }}>Lost</option>
                        </select>
                        @error('status')<p class="po-create__error">{{ $message }}</p>@enderror
                    </div>
                    <div class="po-create__field po-create__field--wide">
                        <label for="product_name" class="po-create__label">Product name <span class="po-create__req">*</span></label>
                        <input type="text" id="product_name" name="product_name" required
                               value="{{ old('product_name', $item->product_name ?? '') }}"
                               placeholder="Samsung Galaxy Tab, iPhone 14, Dell Laptop…"
                               class="po-create__input">
                        @error('product_name')<p class="po-create__error">{{ $message }}</p>@enderror
                    </div>
                    <div class="po-create__field po-create__field--wide">
                        <label for="device_identifier" class="po-create__label">Device ID / IMEI</label>
                        <input type="text" id="device_identifier" name="device_identifier"
                               value="{{ old('device_identifier', $item->device_identifier ?? '') }}"
                               placeholder="Serial number or IMEI for tracking"
                               class="po-create__input">
                        @error('device_identifier')<p class="po-create__error">{{ $message }}</p>@enderror
                    </div>
                    <div class="po-create__field po-create__field--wide">
                        <label for="notes" class="po-create__label">Notes</label>
                        <textarea id="notes" name="notes" rows="3" class="po-create__input"
                                  placeholder="Condition, accessories issued, return instructions…">{{ old('notes', $item->notes ?? '') }}</textarea>
                        @error('notes')<p class="po-create__error">{{ $message }}</p>@enderror
                    </div>
                </div>
            </section>
        </div>

        <x-admin.order-footer
            submit-label="Assign equipment"
            :cancel-url="route('admin.equipment.index')"
            hint="Status defaults to active — update to returned or lost when collected"
        />
    </form>
</div>
@endsection
