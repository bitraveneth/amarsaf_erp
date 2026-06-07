@extends('layouts.app')

@section('content')
<div class="erp-page">
    <x-admin.page-header
        icon="calendar"
        title="Accounting Periods"
        subtitle="Close a month to block new postings into that period."
    />

    @foreach($fiscalYears as $year)
        <x-admin.table-card :title="'Fiscal year ' . $year->name" :description="$year->start_date->format('d M Y') . ' – ' . $year->end_date->format('d M Y')">
            <table class="erp-table">
                <thead>
                    <tr>
                        <th>Period</th>
                        <th>Dates</th>
                        <th>Status</th>
                        <th class="is-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($year->periods as $period)
                        <tr>
                            <td class="font-medium text-gray-900 dark:text-white">{{ $period->name }}</td>
                            <td>{{ $period->start_date->format('d M') }} – {{ $period->end_date->format('d M Y') }}</td>
                            <td>
                                @if($period->is_closed)
                                    <span class="erp-badge-neutral">Closed</span>
                                @else
                                    <span class="erp-badge-success">Open</span>
                                @endif
                            </td>
                            <td class="is-right">
                                @if($period->is_closed)
                                    <form method="POST" action="{{ route('admin.accounting-periods.open', $period) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="erp-link">Reopen</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.accounting-periods.close', $period) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="erp-btn-danger">Close period</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x-admin.table-card>
    @endforeach
</div>
@endsection
