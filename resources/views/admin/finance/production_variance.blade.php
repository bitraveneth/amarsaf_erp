@extends('layouts.app')

@section('content')
<div class="erp-page">
    <x-admin.page-header
        icon="variance"
        title="Production Variance"
        subtitle="Compare standard BOM unit cost against actual material cost for confirmed production runs."
    >
        <x-slot:actions>
            @include('admin.finance.partials.print_button')
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.filter-panel method="GET">
        <div class="erp-field">
            <label class="erp-label" for="variance-from">From</label>
            <input id="variance-from" type="date" name="from" value="{{ $from->toDateString() }}" class="erp-input">
        </div>
        <div class="erp-field">
            <label class="erp-label" for="variance-to">To</label>
            <input id="variance-to" type="date" name="to" value="{{ $to->toDateString() }}" class="erp-input">
        </div>
        <button type="submit" class="erp-btn-primary">Apply</button>
    </x-admin.filter-panel>

    <div class="erp-stat-grid">
        <x-admin.stat-card label="Standard cost" :value="number_format($totals['standard'], 2)" hint="Expected material cost from BOM standards" />
        <x-admin.stat-card label="Actual cost" :value="number_format($totals['actual'], 2)" hint="Recorded material cost on confirmed runs" />
        <x-admin.stat-card
            label="Variance"
            :value="number_format($totals['variance'], 2)"
            hint="Actual minus standard for the selected period"
            :tone="abs($totals['variance']) < 0.01 ? 'success' : 'warning'"
        />
    </div>

    <x-admin.table-card title="Run-level variance" description="Sorted by absolute variance impact.">
        <table class="erp-table">
            <thead>
                <tr>
                    <th>Run</th>
                    <th>Product</th>
                    <th class="is-right">Std unit</th>
                    <th class="is-right">Actual unit</th>
                    <th class="is-right">Variance total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td>{{ $row['run']->order_number ?? '#'.$row['run']->id }}</td>
                        <td>{{ $row['product']?->name ?? '—' }}</td>
                        <td class="is-right">{{ number_format($row['standard_unit_cost'], 4) }}</td>
                        <td class="is-right">{{ number_format($row['actual_unit_cost'], 4) }}</td>
                        <td class="is-right font-semibold {{ $row['variance_total'] >= 0 ? 'text-orange-600 dark:text-orange-400' : 'text-success-600 dark:text-success-400' }}">
                            {{ number_format($row['variance_total'], 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <x-admin.empty-state title="No confirmed production runs" description="Confirm production stock in the selected date range to see variance analysis." />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-admin.table-card>
</div>
@endsection
