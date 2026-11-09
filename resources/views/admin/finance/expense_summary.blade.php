@extends('layouts.app')

@section('content')
@php
    $ccy = $currencyCode ?? config('app.currency', 'BDT');
@endphp

<x-report.page
    eyebrow="Operating costs"
    title="Expense summary"
    subtitle="Operating spend grouped by category for the period."
    :period="$periodLabel"
>
    <x-slot:actions>
        <x-report.header-actions
            :range-action="route('admin.reports.expense-summary')"
            :range="$range"
            :from="request('from', $from->toDateString())"
            :to="request('to', $to->toDateString())"
            :range-options="$rangeOptions"
            :period-label="$periodLabel"
        >
            <x-report.hub-link category="costs" />
            <x-report.export-actions module="expense-summary" :from="$from" :to="$to" />
        </x-report.header-actions>
    </x-slot:actions>

    <x-slot:kpis>
        <x-dashboard.kpi
            label="Total spend"
            :value="$ccy . ' ' . number_format($total, 2)"
            :hint="$lineCount . ' expense ' . \Illuminate\Support\Str::plural('line', $lineCount)"
        />
        <x-dashboard.kpi
            label="Categories"
            :value="(string) $rows->count()"
            hint="Active categories in period"
        />
        <x-dashboard.kpi
            label="Period"
            :value="$periodLabel"
            hint="Change range in the header"
        />
    </x-slot:kpis>

    <div class="erp-table-card">
        <div class="erp-table-card-header">
            <div>
                <h2 class="erp-h3">By category</h2>
                <p class="erp-caption">Click a category to open the filtered expense list</p>
            </div>
            <p class="erp-caption">{{ number_format($rows->count()) }} categories</p>
        </div>
        <div class="erp-table-wrap">
            <table class="erp-table w-full">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th class="is-right">Lines</th>
                        <th class="is-right">Total</th>
                        <th class="is-right">Share</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        @php
                            $share = $total > 0 ? round(($row['total'] / $total) * 100, 1) : 0;
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ $row['drill_url'] }}" class="erp-table-link">{{ $row['name'] }}</a>
                                @if(! empty($row['code']))
                                    <span class="erp-caption ml-2">{{ $row['code'] }}</span>
                                @endif
                            </td>
                            <td class="is-right"><span class="erp-table-num">{{ $row['line_count'] }}</span></td>
                            <td class="is-right"><span class="erp-table-num">{{ number_format($row['total'], 2) }}</span></td>
                            <td class="is-right">
                                <div class="inline-flex min-w-[6.5rem] flex-col items-end gap-1">
                                    <span class="erp-caption">{{ number_format($share, 1) }}%</span>
                                    <span class="h-1.5 w-24 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                                        <span class="block h-full rounded-full bg-brand-500" style="width: {{ min(100, $share) }}%"></span>
                                    </span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <x-admin.empty-state
                                    title="No expenses in this period"
                                    description="Recorded expenses will group here by category. Widen the date range or add expenses first."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($rows->isNotEmpty())
                    <tfoot>
                        <tr>
                            <td colspan="2" class="is-right font-semibold text-gray-900 dark:text-white">Total</td>
                            <td class="is-right"><span class="erp-table-num font-semibold">{{ number_format($total, 2) }}</span></td>
                            <td class="is-right"><span class="erp-caption">100%</span></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
</x-report.page>
@endsection
