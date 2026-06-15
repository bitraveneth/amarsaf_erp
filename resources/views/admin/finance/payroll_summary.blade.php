@extends('layouts.app')

@section('content')
@php
    $ccy = config('app.currency', 'BDT');
@endphp

<x-report.page
    eyebrow="Accountant tools"
    title="Payroll summary"
    subtitle="Salary and allowances per employee for the selected period."
    :period="$periodLabel"
>
    <x-slot:actions>
        <x-report.header-actions
            :range-action="route('admin.reports.payroll')"
            :range="$range"
            :from="request('from', $from->toDateString())"
            :to="request('to', $to->toDateString())"
            :range-options="$rangeOptions"
            :period-label="$periodLabel"
        >
            <x-report.hub-link category="accountant" />
            <x-report.export-actions module="payroll-summary" :from="$from" :to="$to" />
        </x-report.header-actions>
    </x-slot:actions>

    <x-slot:kpis>
        <x-dashboard.kpi label="Total salary" :value="$ccy . ' ' . number_format($totals['salary'], 0)" hint="Base salary" />
        <x-dashboard.kpi label="Total TA" tone="brand" :value="$ccy . ' ' . number_format($totals['ta'], 0)" hint="Travel allowance" />
        <x-dashboard.kpi label="Total DA" tone="warning" :value="$ccy . ' ' . number_format($totals['da'], 0)" hint="Dearness allowance" />
        <x-dashboard.kpi label="Grand total" tone="success" :value="$ccy . ' ' . number_format($totals['grand'], 0)" :hint="'Bonus: ' . $ccy . ' ' . number_format($totals['bonus'], 0) . ' · ' . $rows->count() . ' employees'" />
    </x-slot:kpis>

    <x-dashboard.panel title="Per-employee breakdown" subtitle="Base contract amounts plus TA/DA/bonus allowances in this period">
        @if($rows->isNotEmpty())
            <x-report.table>
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <x-report.th>Employee</x-report.th>
                        <x-report.th>Department</x-report.th>
                        <x-report.th align="right">Base salary</x-report.th>
                        <x-report.th align="right">Base TA</x-report.th>
                        <x-report.th align="right">Base DA</x-report.th>
                        <x-report.th align="right">Base bonus</x-report.th>
                        <x-report.th align="right">TA claims</x-report.th>
                        <x-report.th align="right">DA claims</x-report.th>
                        <x-report.th align="right">Bonus claims</x-report.th>
                        <x-report.th align="right">Total</x-report.th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalSalary = 0;
                        $totalBaseTA = 0;
                        $totalBaseDA = 0;
                        $totalBaseBonus = 0;
                        $totalTA = 0;
                        $totalDA = 0;
                        $totalBonus = 0;
                        $totalGrand = 0;
                    @endphp
                    @foreach($rows as $row)
                        @php
                            $employee = $row['employee'];
                            $totalSalary += $row['total_salary'];
                            $totalBaseTA += $row['base_ta'];
                            $totalBaseDA += $row['base_da'];
                            $totalBaseBonus += $row['base_bonus'];
                            $totalTA += $row['ta_allowances'];
                            $totalDA += $row['da_allowances'];
                            $totalBonus += $row['bonus_allowances'];
                            $totalGrand += $row['grand_total'];
                        @endphp
                        <tr class="border-b border-gray-50 dark:border-gray-800/60">
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                <span class="font-medium">{{ $employee->name }}</span>
                                @if($employee->employee_id)
                                    <span class="ml-2 text-xs text-gray-400">ID: {{ $employee->employee_id }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-200">{{ $employee->department ?? '—' }}</td>
                            <td class="px-4 py-3 text-right text-sm tabular-nums text-gray-900 dark:text-white">{{ number_format($row['total_salary'], 0) }}</td>
                            <td class="px-4 py-3 text-right text-sm tabular-nums text-gray-700 dark:text-gray-200">{{ number_format($row['base_ta'], 0) }}</td>
                            <td class="px-4 py-3 text-right text-sm tabular-nums text-gray-700 dark:text-gray-200">{{ number_format($row['base_da'], 0) }}</td>
                            <td class="px-4 py-3 text-right text-sm tabular-nums text-gray-700 dark:text-gray-200">{{ number_format($row['base_bonus'], 0) }}</td>
                            <td class="px-4 py-3 text-right text-sm tabular-nums text-blue-light-600 dark:text-blue-light-400">+{{ number_format($row['ta_allowances'], 0) }}</td>
                            <td class="px-4 py-3 text-right text-sm tabular-nums text-orange-600 dark:text-orange-400">+{{ number_format($row['da_allowances'], 0) }}</td>
                            <td class="px-4 py-3 text-right text-sm tabular-nums text-success-600 dark:text-success-400">+{{ number_format($row['bonus_allowances'], 0) }}</td>
                            <td class="px-4 py-3 text-right text-sm font-semibold tabular-nums text-gray-900 dark:text-white">{{ number_format($row['grand_total'], 0) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-gray-50 font-semibold dark:bg-gray-800/50">
                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white" colspan="2">Totals</td>
                        <td class="px-4 py-3 text-right text-sm tabular-nums text-gray-900 dark:text-white">{{ number_format($totalSalary, 0) }}</td>
                        <td class="px-4 py-3 text-right text-sm tabular-nums text-gray-700 dark:text-gray-200">{{ number_format($totalBaseTA, 0) }}</td>
                        <td class="px-4 py-3 text-right text-sm tabular-nums text-gray-700 dark:text-gray-200">{{ number_format($totalBaseDA, 0) }}</td>
                        <td class="px-4 py-3 text-right text-sm tabular-nums text-gray-700 dark:text-gray-200">{{ number_format($totalBaseBonus, 0) }}</td>
                        <td class="px-4 py-3 text-right text-sm tabular-nums text-blue-light-600 dark:text-blue-light-400">+{{ number_format($totalTA, 0) }}</td>
                        <td class="px-4 py-3 text-right text-sm tabular-nums text-orange-600 dark:text-orange-400">+{{ number_format($totalDA, 0) }}</td>
                        <td class="px-4 py-3 text-right text-sm tabular-nums text-success-600 dark:text-success-400">+{{ number_format($totalBonus, 0) }}</td>
                        <td class="px-4 py-3 text-right text-sm tabular-nums text-brand-600 dark:text-brand-400">{{ number_format($totalGrand, 0) }}</td>
                    </tr>
                </tfoot>
            </x-report.table>
        @else
            <x-admin.empty-state title="No payroll data" description="No payroll records were found for the selected period. Try adjusting the date range or add employee contracts and allowances." />
        @endif
    </x-dashboard.panel>
</x-report.page>
@endsection
