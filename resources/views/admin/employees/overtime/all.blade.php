@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-admin.page-header title="Overtime" subtitle="All overtime requests across employees.">
        <x-slot:actions>
            <a href="{{ route('admin.employees.index') }}" class="erp-btn-secondary">Employees</a>
            <a href="{{ route('admin.overtime.create') }}" class="erp-btn-primary">New overtime</a>
        </x-slot:actions>
    </x-admin.page-header>

    @if(session('status'))
        <div class="rounded-xl border border-success-200 bg-success-50 px-4 py-3 text-sm text-success-800 dark:border-success-500/30 dark:bg-success-500/10 dark:text-success-300">{{ session('status') }}</div>
    @endif

    <form method="GET" class="erp-filter-panel flex flex-wrap items-end gap-3">
        <div>
            <label class="mb-1 block text-xs font-medium text-gray-500">Status</label>
            <select name="status" class="rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                <option value="">All</option>
                @foreach(\App\Models\EmployeeOvertime::statuses() as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="erp-btn-secondary">Filter</button>
    </form>

    <div class="erp-table-card">
        <div class="erp-table-wrap">
            <table class="erp-table w-full">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Employee</th>
                        <th class="is-right">Hours</th>
                        <th class="is-right">Multiplier</th>
                        <th class="is-right">Amount</th>
                        <th>Status</th>
                        <th class="is-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $record)
                        <tr>
                            <td>{{ $record->work_date->format('d M Y') }}</td>
                            <td>{{ $record->employee->name }}</td>
                            <td class="is-right erp-table-num">{{ number_format($record->hours, 2) }}</td>
                            <td class="is-right erp-table-num">{{ number_format($record->rate_multiplier, 2) }}×</td>
                            <td class="is-right erp-table-num">BDT {{ number_format($record->amount, 2) }}</td>
                            <td>{{ ucfirst($record->status) }}</td>
                            <td class="is-right">
                                <a href="{{ route('admin.employees.overtime.edit', [$record->employee, $record]) }}" class="erp-btn-action">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-8 text-center text-sm text-gray-500">No overtime records yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($records->hasPages())
            <div class="border-t border-gray-100 px-6 py-4 dark:border-gray-800">{{ $records->links() }}</div>
        @endif
    </div>
</div>
@endsection
