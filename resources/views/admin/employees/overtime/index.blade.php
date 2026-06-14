@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <x-admin.page-header :title="$employee->name . ' — Overtime'" subtitle="Track extra hours and OT pay for this employee.">
        <x-slot:actions>
            <a href="{{ route('admin.employees.show', $employee) }}" class="erp-btn-secondary">Profile</a>
            <a href="{{ route('admin.employees.overtime.create', $employee) }}" class="erp-btn-primary">Add overtime</a>
        </x-slot:actions>
    </x-admin.page-header>

    @if(session('status'))
        <div class="rounded-xl border border-success-200 bg-success-50 px-4 py-3 text-sm text-success-800 dark:border-success-500/30 dark:bg-success-500/10 dark:text-success-300">{{ session('status') }}</div>
    @endif

    <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
        <p class="text-sm text-gray-500 dark:text-gray-400">Calculated hourly rate from active contract</p>
        <p class="text-xl font-semibold text-gray-900 dark:text-white">BDT {{ number_format($hourlyRate, 2) }} / hour</p>
    </div>

    <div class="erp-table-card">
        <div class="erp-table-wrap">
            <table class="erp-table w-full">
                <thead>
                    <tr>
                        <th>Work date</th>
                        <th class="is-right">Hours</th>
                        <th class="is-right">Rate</th>
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
                            <td class="is-right erp-table-num">{{ number_format($record->hours, 2) }}</td>
                            <td class="is-right erp-table-num">BDT {{ number_format($record->hourly_rate, 2) }}</td>
                            <td class="is-right erp-table-num">{{ number_format($record->rate_multiplier, 2) }}×</td>
                            <td class="is-right erp-table-num">BDT {{ number_format($record->amount, 2) }}</td>
                            <td>{{ ucfirst($record->status) }}</td>
                            <td class="is-right">
                                @if($record->status !== 'paid')
                                    <a href="{{ route('admin.employees.overtime.edit', [$employee, $record]) }}" class="erp-btn-action">Edit</a>
                                @else
                                    <span class="text-xs text-gray-500">Paid</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-8 text-center text-sm text-gray-500">No overtime logged for this employee.</td></tr>
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
