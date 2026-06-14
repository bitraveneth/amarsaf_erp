@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-admin.page-header title="Add recurring charge" subtitle="e.g. monthly truck rent posted on the 1st.">
        <x-slot:actions><a href="{{ route('admin.fleet-recurring.index') }}" class="erp-btn-secondary">Back</a></x-slot:actions>
    </x-admin.page-header>
    <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
        <form action="{{ route('admin.fleet-recurring.store') }}" method="POST" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            @csrf
            <div>
                <label class="mb-2 block text-sm font-medium">Vehicle *</label>
                <select name="vehicle_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    @foreach($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}" @selected(old('vehicle_id', $charge->vehicle_id ?? '') == $vehicle->id)>{{ $vehicle->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium">Type *</label>
                <select name="expense_type" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    @foreach($types as $value => $label)
                        <option value="{{ $value }}" @selected(old('expense_type', $charge->expense_type ?? 'rent') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium">Amount (BDT) *</label>
                <input type="number" name="amount" step="0.01" min="0.01" value="{{ old('amount', $charge->amount ?? '') }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium">Day of month (1–28) *</label>
                <input type="number" name="day_of_month" min="1" max="28" value="{{ old('day_of_month', $charge->day_of_month ?? 1) }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
            </div>
            <div class="sm:col-span-2">
                <label class="mb-2 block text-sm font-medium">Description</label>
                <input type="text" name="description" value="{{ old('description', $charge->description ?? '') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
            </div>
            <input type="hidden" name="payment_type" value="bank">
            <input type="hidden" name="payment_account_key" value="bank_default">
            <input type="hidden" name="is_active" value="1">
            <div class="sm:col-span-2 flex justify-end gap-3">
                <a href="{{ route('admin.fleet-recurring.index') }}" class="erp-btn-secondary">Cancel</a>
                <button type="submit" class="erp-btn-primary">Save schedule</button>
            </div>
        </form>
    </div>
</div>
@endsection
