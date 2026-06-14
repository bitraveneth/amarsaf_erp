@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-admin.page-header title="Add overtime" subtitle="Record overtime for any employee.">
        <x-slot:actions>
            <a href="{{ route('admin.overtime.index') }}" class="erp-btn-secondary">Back</a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
        <form action="{{ route('admin.overtime.store') }}" method="POST" class="space-y-6">
            @csrf
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Employee <span class="text-error-500">*</span></label>
                <select name="employee_id" required class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    <option value="">Select employee</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" @selected(old('employee_id') == $employee->id)>{{ $employee->name }}</option>
                    @endforeach
                </select>
                @error('employee_id')<p class="mt-1 text-sm text-error-600">{{ $message }}</p>@enderror
            </div>
            @include('admin.employees.overtime.partials.form', ['record' => $record, 'hourlyRate' => 0])
            <div class="flex justify-end gap-3 border-t border-gray-100 pt-6 dark:border-gray-800">
                <button type="submit" class="erp-btn-primary">Save overtime</button>
            </div>
        </form>
    </div>
</div>
@endsection
