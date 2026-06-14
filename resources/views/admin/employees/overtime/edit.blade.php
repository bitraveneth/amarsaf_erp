@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-admin.page-header :title="'Edit overtime — ' . $employee->name" :subtitle="$record->work_date?->format('d M Y')">
        <x-slot:actions>
            <a href="{{ route('admin.employees.overtime.index', $employee) }}" class="erp-btn-secondary">Back</a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
        <form action="{{ route('admin.employees.overtime.update', [$employee, $record]) }}" method="POST" class="space-y-6">
            @csrf
            @method('PATCH')
            @include('admin.employees.overtime.partials.form', ['record' => $record, 'hourlyRate' => $hourlyRate])
            <div class="flex justify-end gap-3 border-t border-gray-100 pt-6 dark:border-gray-800">
                <button type="submit" class="erp-btn-primary">Update overtime</button>
            </div>
        </form>
    </div>
</div>
@endsection
