@extends('layouts.app')

@section('content')
@php
    $currencyCode = config('app.currency', 'BDT');
    $selectedMonth = request('month', $month->format('Y-m'));
    $monthLabel = $month->format('F Y');
    $employeeQuery = array_filter(['month' => $selectedMonth, 'employee_id' => request('employee_id')]);
@endphp

<div class="erp-page">
    <x-admin.page-header
        title="Salary distributions"
        :subtitle="'Payroll for ' . $monthLabel . ($selectedEmployeeName ? ' · ' . $selectedEmployeeName : '')"
    >
        <x-slot:actions>
            <form
                method="GET"
                action="{{ route('admin.salary-distributions.index') }}"
                id="salary-filter-form"
                class="flex flex-wrap items-center gap-2"
                x-data="{
                    open: false,
                    month: @js($selectedMonth),
                    viewYear: {{ (int) $month->year }},
                    months: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
                    label() {
                        const [y, m] = this.month.split('-').map(Number);
                        return new Date(y, m - 1, 1).toLocaleDateString('en-GB', { month: 'long', year: 'numeric' });
                    },
                    shift(step) {
                        const [y, m] = this.month.split('-').map(Number);
                        const next = new Date(y, m - 1 + step, 1);
                        this.month = next.getFullYear() + '-' + String(next.getMonth() + 1).padStart(2, '0');
                        this.$nextTick(() => this.$refs.form.submit());
                    },
                    pick(monthIndex) {
                        this.month = this.viewYear + '-' + String(monthIndex + 1).padStart(2, '0');
                        this.open = false;
                        this.$nextTick(() => this.$refs.form.submit());
                    },
                }"
                x-ref="form"
                @keydown.escape.window="open = false"
            >
                <input type="hidden" name="month" :value="month">
                @if(request()->filled('employee_id'))
                    <input type="hidden" name="employee_id" value="{{ request('employee_id') }}">
                @endif

                <div class="relative flex items-center gap-1 rounded-xl border border-gray-200 bg-white p-1 shadow-theme-xs dark:border-gray-700 dark:bg-gray-900">
                    <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 transition hover:bg-gray-50 hover:text-gray-800 dark:hover:bg-white/[0.04] dark:hover:text-white" @click="shift(-1)" aria-label="Previous month">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                    </button>
                    <button
                        type="button"
                        class="inline-flex min-w-[9.5rem] items-center justify-center gap-2 rounded-lg px-2.5 py-1.5 text-sm font-semibold text-gray-800 transition hover:bg-gray-50 dark:text-white dark:hover:bg-white/[0.04]"
                        @click="open = !open; viewYear = Number(month.split('-')[0])"
                        :aria-expanded="open.toString()"
                    >
                        <svg class="h-4 w-4 text-brand-600 dark:text-brand-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span x-text="label()"></span>
                    </button>
                    <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 transition hover:bg-gray-50 hover:text-gray-800 dark:hover:bg-white/[0.04] dark:hover:text-white" @click="shift(1)" aria-label="Next month">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                    </button>

                    <div
                        x-show="open"
                        x-cloak
                        @click.outside="open = false"
                        class="absolute left-0 top-full z-30 mt-2 w-72 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-md dark:border-gray-700 dark:bg-gray-900"
                    >
                        <div class="flex items-center justify-between border-b border-gray-100 px-3 py-2.5 dark:border-gray-800">
                            <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-50 dark:hover:bg-white/[0.04]" @click="viewYear--" aria-label="Previous year">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                            </button>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white" x-text="viewYear"></span>
                            <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-50 dark:hover:bg-white/[0.04]" @click="viewYear++" aria-label="Next year">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                            </button>
                        </div>
                        <div class="grid grid-cols-3 gap-1.5 p-3">
                            <template x-for="(name, index) in months" :key="index">
                                <button
                                    type="button"
                                    class="rounded-xl px-2 py-2.5 text-sm font-medium transition"
                                    :class="(Number(month.split('-')[0]) === viewYear && Number(month.split('-')[1]) === index + 1)
                                        ? 'bg-brand-500 text-white shadow-theme-xs'
                                        : 'text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/[0.04]'"
                                    @click="pick(index)"
                                    x-text="name"
                                ></button>
                            </template>
                        </div>
                    </div>
                </div>
            </form>

            <form method="GET" action="{{ route('admin.salary-distributions.index') }}" class="flex items-center gap-2">
                <input type="hidden" name="month" value="{{ $selectedMonth }}">
                <select name="employee_id" class="erp-select erp-select--sm min-w-[11rem]" aria-label="Employee" onchange="this.form.submit()">
                    <option value="">All employees</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" @selected((string) request('employee_id') === (string) $employee->id)>
                            {{ $employee->name }}
                        </option>
                    @endforeach
                </select>
            </form>

            <a href="{{ route('admin.salary-distributions.create') }}" class="erp-btn-primary">Add distribution</a>
        </x-slot:actions>
    </x-admin.page-header>

    <x-dashboard.snapshot-kpis
        :show-header="false"
        title="Payroll"
        :cards="[
            [
                'label' => 'Total payroll',
                'numeric' => number_format($total, 2),
                'currency' => $currencyCode,
                'caption' => $monthLabel,
                'href' => route('admin.salary-distributions.index', $employeeQuery),
                'tone' => 'brand',
                'valueTone' => 'brand',
                'icon' => 'revenue',
            ],
            [
                'label' => 'Employees paid',
                'numeric' => number_format($employeeCount),
                'caption' => 'Unique employees this month',
                'href' => route('admin.salary-distributions.index', $employeeQuery),
                'tone' => 'success',
                'valueTone' => 'neutral',
                'icon' => 'users',
            ],
            [
                'label' => 'Average',
                'numeric' => number_format($averageDistribution, 2),
                'currency' => $currencyCode,
                'caption' => 'Per employee in this filter',
                'href' => route('admin.salary-distributions.index', $employeeQuery),
                'tone' => 'orange',
                'valueTone' => 'warning',
                'icon' => 'receivables',
            ],
            [
                'label' => 'Bank transfers',
                'numeric' => number_format($bankTransfers),
                'caption' => $withDocuments . ' with documents',
                'href' => route('admin.salary-distributions.index', $employeeQuery),
                'tone' => 'blue',
                'valueTone' => 'neutral',
                'icon' => 'orders',
            ],
        ]"
    />

    <div class="erp-table-card" x-data="{ openId: null }">
        <div class="erp-table-card-header">
            <div>
                <h2 class="erp-h3">Distribution register</h2>
                <p class="erp-caption">{{ $rows->total() }} {{ Str::plural('distribution', $rows->total()) }} · open a row for the pay breakdown</p>
            </div>
        </div>

        @if($rows->isNotEmpty())
            <div class="erp-table-wrap">
                <table class="erp-table w-full" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Period</th>
                            <th>Payment</th>
                            <th class="is-right">Total</th>
                            <th class="is-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $row)
                            @php
                                $totalRow = (float) $row->base_salary
                                    + (float) $row->bonus
                                    + (float) $row->ta_allowances
                                    + (float) $row->da_allowances
                                    + (float) $row->commission
                                    + (float) $row->overtime_pay;
                                $paymentMethod = strtolower((string) ($row->payment_method ?? $row->payment_type ?? 'bank'));
                                $badge = match ($paymentMethod) {
                                    'cash' => 'erp-badge-warning',
                                    'cheque', 'check' => 'erp-badge-brand',
                                    'online' => 'erp-badge-brand',
                                    default => 'erp-badge-success',
                                };
                            @endphp
                            <tr class="cursor-pointer" @click="if (!$event.target.closest('a,button,form')) openId = openId === {{ $row->id }} ? null : {{ $row->id }}">
                                <td>
                                    <div class="erp-body-strong">{{ $row->employee?->name ?? '—' }}</div>
                                    <p class="erp-caption">ID {{ $row->employee_id }}</p>
                                </td>
                                <td class="erp-table-cell whitespace-nowrap">
                                    {{ optional($row->period_start)->format('d M') }}
                                    –
                                    {{ optional($row->period_end)->format('d M Y') }}
                                </td>
                                <td>
                                    <span class="{{ $badge }}">{{ ucfirst($paymentMethod) }}</span>
                                </td>
                                <td class="is-right">
                                    <span class="erp-table-num">{{ $currencyCode }} {{ number_format($totalRow, 2) }}</span>
                                </td>
                                <td class="is-right" @click.stop>
                                    <x-admin.action-group class="justify-end">
                                        <button
                                            type="button"
                                            class="erp-btn-action"
                                            @click="openId = openId === {{ $row->id }} ? null : {{ $row->id }}"
                                            :aria-expanded="(openId === {{ $row->id }}).toString()"
                                        >
                                            <svg class="h-3.5 w-3.5 shrink-0 transition-transform" :class="openId === {{ $row->id }} ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                            </svg>
                                            Details
                                        </button>
                                        <x-admin.action-edit :href="route('admin.salary-distributions.edit', $row)" />
                                        <x-admin.action-delete
                                            :action="route('admin.salary-distributions.destroy', $row)"
                                            confirm="Delete this salary distribution? This cannot be undone."
                                        />
                                    </x-admin.action-group>
                                </td>
                            </tr>
                            <tr x-show="openId === {{ $row->id }}" x-cloak class="!bg-gray-50/80 dark:!bg-gray-800/40">
                                <td colspan="5" class="!py-4">
                                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
                                        <div>
                                            <p class="erp-caption">Base salary</p>
                                            <p class="erp-body-strong">{{ $currencyCode }} {{ number_format($row->base_salary, 2) }}</p>
                                        </div>
                                        <div>
                                            <p class="erp-caption">Bonus</p>
                                            <p class="erp-body-strong">{{ $currencyCode }} {{ number_format($row->bonus, 2) }}</p>
                                        </div>
                                        <div>
                                            <p class="erp-caption">TA / DA</p>
                                            <p class="erp-body-strong">{{ $currencyCode }} {{ number_format((float) $row->ta_allowances + (float) $row->da_allowances, 2) }}</p>
                                        </div>
                                        <div>
                                            <p class="erp-caption">Commission</p>
                                            <p class="erp-body-strong">{{ $currencyCode }} {{ number_format($row->commission, 2) }}</p>
                                        </div>
                                        <div>
                                            <p class="erp-caption">Overtime</p>
                                            <p class="erp-body-strong">{{ $currencyCode }} {{ number_format($row->overtime_pay, 2) }}</p>
                                        </div>
                                        <div>
                                            <p class="erp-caption">Document</p>
                                            @if($row->document_path)
                                                <a href="{{ asset('storage/'.$row->document_path) }}" target="_blank" class="erp-link text-sm">View file</a>
                                            @else
                                                <p class="text-sm text-gray-400 dark:text-gray-500">None</p>
                                            @endif
                                        </div>
                                    </div>
                                    @if($row->remarks)
                                        <p class="erp-caption mt-3">
                                            <span class="font-medium text-gray-700 dark:text-gray-200">Remarks:</span>
                                            {{ $row->remarks }}
                                        </p>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-gray-200 px-5 py-4 dark:border-gray-800">
                {{ $rows->links() }}
            </div>
        @else
            <x-admin.empty-state
                title="No salary distributions"
                description="Try another month or add the first payroll distribution for this period."
            >
                <x-slot:action>
                    <a href="{{ route('admin.salary-distributions.create') }}" class="erp-btn-primary">Add distribution</a>
                </x-slot:action>
            </x-admin.empty-state>
        @endif
    </div>
</div>
@endsection
